<?php
session_start();
include('interlinkedDB.php');
include_once 'checkIfSet.php';

// Database connections
try {
    $conn = connectToDatabase();
    $master_con = connectToDatabase(3306);
    $slave_con = connectToDatabase(3307);
} catch(Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    $error = "Database connection failed. Please try again later.";
}

$user_id = $_SESSION['user_id'];
$admin = $_SESSION['admin_data'];
$name = $_SESSION['userName'];
$profile_img = $_SESSION['profile_img'] ?? '../../imgs/default-avatar.png';

// Initialize variables with defaults
$userCounts = [
    'Admin' => 0,
    'Client' => 0,
    'Freelancer' => 0,
    'Applicant' => 0
];
$totalUsers = 0;

$projectCounts = [
    'Working' => 0,
    'Pending' => 0,
    'Completed' => 0,
    'Canceled' => 0
];
$totalProjects = 0;
$overdueProjects = 0;
$recentUsers = 0;
$recentProjects = 0;

$budgetData = [
    'completed_budget' => 0,
    'active_budget' => 0,
    'total_budget' => 0
];

$monthlyUsers = [];
$monthlyProjects = [];
$latestUsers = [];
$latestProjects = [];
$dbSize = 'N/A';

if (!isset($error)) {
    try {
        // === SYSTEM STATISTICS ===

        // Total users by type - with error handling
        $userStatsStmt = $slave_con->prepare("
            SELECT USER_TYPE, COUNT(*) as count 
            FROM user 
            WHERE USER_TYPE IS NOT NULL
            GROUP BY USER_TYPE
        ");
        $userStatsStmt->execute();
        $userStats = $userStatsStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($userStats as $stat) {
            if (isset($userCounts[$stat['USER_TYPE']])) {
                $userCounts[$stat['USER_TYPE']] = $stat['count'];
            }
        }

        $totalUsers = array_sum($userCounts);

        // === PROJECT STATISTICS ===

        // Project status counts - with null check
        $projectStatsStmt = $slave_con->prepare("
            SELECT PRO_STATUS, COUNT(*) as count 
            FROM projects 
            WHERE PRO_STATUS IS NOT NULL
            GROUP BY PRO_STATUS
        ");
        $projectStatsStmt->execute();
        $projectStats = $projectStatsStmt->fetchAll(PDO::FETCH_ASSOC);

        foreach($projectStats as $stat) {
            if(isset($projectCounts[$stat['PRO_STATUS']])) {
                $projectCounts[$stat['PRO_STATUS']] = $stat['count'];
            }
        }

        $totalProjects = array_sum($projectCounts);

        // Overdue projects - with proper date handling
        $overdueStmt = $slave_con->prepare("
            SELECT COUNT(*) as count 
            FROM projects 
            WHERE PRO_END_DATE < CURDATE() 
            AND PRO_STATUS IN ('Working', 'Pending')
            AND PRO_END_DATE IS NOT NULL
        ");
        $overdueStmt->execute();
        $result = $overdueStmt->fetch(PDO::FETCH_ASSOC);
        $overdueProjects = $result ? $result['count'] : 0;

        // === RECENT ACTIVITY ===

        // Recent users (last 30 days) - with date column check
        $recentUsersStmt = $slave_con->prepare("
            SELECT COUNT(*) as count 
            FROM user 
            WHERE USER_BIRTHDAY >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND USER_BIRTHDAY IS NOT NULL
        ");
        $recentUsersStmt->execute();
        $result = $recentUsersStmt->fetch(PDO::FETCH_ASSOC);
        $recentUsers = $result ? $result['count'] : 0;

        // Recent projects (last 30 days) - with date column check
        $recentProjectsStmt = $slave_con->prepare("
            SELECT COUNT(*) as count 
            FROM projects 
            WHERE CREATED_AT >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            AND CREATED_AT IS NOT NULL
        ");
        $recentProjectsStmt->execute();
        $result = $recentProjectsStmt->fetch(PDO::FETCH_ASSOC);
        $recentProjects = $result ? $result['count'] : 0;

        // === FINANCIAL OVERVIEW ===

        // Total project budget - with null handling
// === FINANCIAL OVERVIEW ===
// Total project budget - now using payment table
        $budgetStmt = $slave_con->prepare("
    SELECT 
        COALESCE(SUM(CASE WHEN p.PRO_STATUS = 'Completed' THEN pay.PAY_AMOUNT END), 0) as completed_budget,
        COALESCE(SUM(CASE WHEN p.PRO_STATUS = 'Working' THEN pay.PAY_AMOUNT END), 0) as active_budget,
        COALESCE(SUM(CASE WHEN p.PRO_STATUS = 'Pending' THEN pay.PAY_AMOUNT END), 0) as pending_budget,
        COALESCE(SUM(pay.PAY_AMOUNT), 0) as total_budget
    FROM projects p
    INNER JOIN payment pay ON p.PRO_ID = pay.PRO_ID
    WHERE pay.PAY_AMOUNT IS NOT NULL
");


        $budgetStmt->execute();
        $budgetData = $budgetStmt->fetch(PDO::FETCH_ASSOC);

        // Ensure we have valid numbers
        $budgetData['completed_budget'] = $budgetData['completed_budget'] ?? 0;
        $budgetData['active_budget'] = $budgetData['active_budget'] ?? 0;
        $budgetData['total_budget'] = $budgetData['total_budget'] ?? 0;

        // === MONTHLY GROWTH DATA ===

        // Users registered per month (last 6 months) - with better error handling
        try {
            $monthlyUsersStmt = $slave_con->prepare("
                SELECT 
                    DATE_FORMAT(USER_BIRTHDAY, '%Y-%m') as month,
                    COUNT(*) as count
                FROM user 
                WHERE USER_BIRTHDAY >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                AND USER_BIRTHDAY IS NOT NULL
                GROUP BY DATE_FORMAT(USER_BIRTHDAY, '%Y-%m')
                ORDER BY month DESC
            ");
            $monthlyUsersStmt->execute();
            $monthlyUsers = $monthlyUsersStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Monthly users query error: " . $e->getMessage());
            $monthlyUsers = [];
        }

        // Projects created per month (last 6 months)
        try {
            $monthlyProjectsStmt = $slave_con->prepare("
                SELECT 
                    DATE_FORMAT(CREATED_AT, '%Y-%m') as month,
                    COUNT(*) as count
                FROM projects 
                WHERE PRO_CREATED_AT >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                AND CREATED_AT IS NOT NULL
                GROUP BY DATE_FORMAT(CREATED_AT, '%Y-%m')
                ORDER BY month DESC
            ");
            $monthlyProjectsStmt->execute();
            $monthlyProjects = $monthlyProjectsStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Monthly projects query error: " . $e->getMessage());
            $monthlyProjects = [];
        }

        // === RECENT ACTIVITY LOGS ===

        // Latest users - with image fallback
        try {
            $latestUsersStmt = $slave_con->prepare("
                SELECT USER_FSTNAME, USER_LSTNAME, USER_TYPE, USER_BIRTHDAY, USER_IMG
                FROM user 
                WHERE USER_BIRTHDAY IS NOT NULL
                ORDER BY USER_BIRTHDAYT DESC 
                LIMIT 5
            ");
            $latestUsersStmt->execute();
            $latestUsers = $latestUsersStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Latest users query error: " . $e->getMessage());
            $latestUsers = [];
        }

        // Latest projects - with better JOIN handling
        try {
            $latestProjectsStmt = $slave_con->prepare("
                SELECT p.PRO_TITLE, p.PRO_STATUS, p.CREATED_AT, 
                       COALESCE(p.PRO_PRIORITY_LEVEL, 'Medium') as PRO_PRIORITY_LEVEL,
                       u.USER_FSTNAME, u.USER_LSTNAME
                FROM projects p
                LEFT JOIN user u ON p.USER_ID = u.USER_ID
                WHERE p.CREATED_AT IS NOT NULL
                ORDER BY p.CREATED_AT DESC 
                LIMIT 5
            ");
            $latestProjectsStmt->execute();
            $latestProjects = $latestProjectsStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Latest projects query error: " . $e->getMessage());
            $latestProjects = [];
        }

        // === SYSTEM HEALTH METRICS ===
    } catch(PDOException $e) {
        error_log("Dashboard error: " . $e->getMessage());
        $error = "An error occurred while loading dashboard data. Error: " . $e->getMessage();
    } catch(Exception $e) {
        error_log("General dashboard error: " . $e->getMessage());
        $error = "An unexpected error occurred while loading dashboard data.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | interLINKED</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" type="image/x-icon" href="../imgs/inlFaviconwhite.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<style>
    /* Main container: fills the space to the right of the sidebar */
    .main-container {
        margin-left: 220px; /* same as sidebar width */
        padding: 90px 0 32px 0; /* top padding for navbar, no side padding */
        min-height: 100vh;
        background: #f5f6fa;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: center; /* center the content horizontally */
    }

    /* Dashboard content: set a max width and center */
    .dashboard-content {
        width: 100%;
        max-width: 900px; /* or whatever fits your design */
        display: flex;
        flex-direction: column;
        gap: 18px; /* space between cards */
        align-items: stretch;
    }

    /* Card styles: make sure they fill the container width */
    .welcome-card,
    .stat-card {
        width: 100%;
        margin: 0;
        box-sizing: border-box;
    }

    /* Status badges: make them compact and close together */
    .status-badges {
        display: flex;
        gap: 12px;
        margin-top: 8px;
    }

    /* Stats grid: stack cards vertically with small gaps */
    .stats-grid {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    /* Responsive: stack everything on small screens */
    @media (max-width: 900px) {
        .main-container {
            margin-left: 60px;
            padding: 80px 0 16px 0;
        }
        .dashboard-content {
            max-width: 100%;
            padding: 0 8px;
        }
    }
    .stats-grid {
        display: flex;
        flex-direction: column;
        gap: 28px;
        width: 100%;
    }

    .stat-card {
        display: flex;
        align-items: center;
        width: 100%;
        min-height: 170px;
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 2px 10px rgba(21,96,100,0.06);
        padding: 0 38px;
        gap: 30px;
        transition: box-shadow 0.2s;
    }

    .stat-card:hover {
        box-shadow: 0 4px 18px rgba(21,96,100,0.13);
    }

    /* Left: Big Icon */
    .stat-icon {
        flex: 0 0 90px;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 90px;
        width: 90px;
        border-radius: 50%;
        background: #f5f6fa;
        font-size: 2.6rem;
        color: #fff;
        margin-right: 0;
        position: relative;
    }
    .users-card .stat-icon { background: #1e90ff; }
    .projects-card .stat-icon { background: #e74c3c; }
    .budget-card .stat-icon { background: #27ae60; }

    /* Right: Stat Content */
    .stat-content-group {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        gap: 32px;
    }

    .stat-main {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
        min-width: 180px;
    }

    .stat-main h3 {
        margin: 0;
        font-size: 2.6rem;
        font-weight: 900;
        color: #222;
        letter-spacing: -1px;
    }

    .stat-main p {
        margin: 0;
        font-size: 1.13rem;
        color: #666;
        font-weight: 600;
    }

    .stat-trend {
        margin-top: 8px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #eafbe7;
        color: #1bc47d;
        border-radius: 8px;
        padding: 5px 14px;
        font-size: 1rem;
        font-weight: 600;
    }

    .stat-breakdown {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px 24px;
        min-width: 200px;
    }

    .breakdown-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 1.05rem;
        color: #333;
        background: #f7f8fa;
        border-radius: 6px;
        padding: 5px 14px;
    }

    .breakdown-label {
        font-weight: 600;
        color: #156064;
    }

    .breakdown-value {
        font-weight: 700;
        color: #222;
    }

    /* Responsive for mobile */
    @media (max-width: 900px) {
        .stat-card {
            flex-direction: column;
            align-items: flex-start;
            padding: 18px 10px;
            gap: 18px;
            min-height: unset;
        }
        .stat-icon {
            margin-bottom: 10px;
            height: 60px;
            width: 60px;
            font-size: 2rem;
        }
        .stat-content-group {
            flex-direction: column;
            gap: 10px;
            width: 100%;
        }
        .stat-breakdown {
            grid-template-columns: 1fr;
            min-width: 0;
            width: 100%;
        }
    }


</style>

<body>
<!-- Sidebar -->
<div class="sidebar">
    <div class="topvar2">
        <div class="logo">
            <img src="../../imgs/inl2LogoWhite.png" alt="Logo">
        </div>
    </div>
    <ul class="side-content">
        <li><a href="adminDash.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="adminProj.php"><i class="fas fa-project-diagram"></i> Projects</a></li>
        <li><a href="adminPay.php"><i class="fas fa-dollar-sign"></i> Salary</a></li>
        <li><a href="adminUser.php"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="adminNotif.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li><a href="adminMes.php"><i class="fas fa-envelope"></i> Message</a></li>
        <li><a href="adminProf.php"><i class="fas fa-user"></i> Profile</a></li>
        <div class="btm-content">
            <button class="logout-button" onclick="window.location.href='../loginSignup/logIn.php';">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </button>
        </div>
    </ul>
</div>

<!-- Navbar -->
<div class="navbar">
    <div class="topvar">
        <div class="navtitle">
            <h1>Admin | </h1>
            <p>DASHBOARD</p>
        </div>
        <div class="navprofile">
            <div class="name">
                <h4><?= htmlspecialchars($name) ?></h4>
            </div>
            <div class="profile">
                <img src="<?= htmlspecialchars($profile_img) ?>" alt="Admin Profile">
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="main-container">
    <div class="dashboard-content">

        <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-card">
                <div class="welcome-content">
                    <h2>Welcome back, <?= htmlspecialchars($admin['USER_FSTNAME'] ?? 'Admin') ?>!</h2>
                    <p>Here's what's happening with your platform today.</p>
                    <div class="status-badges">
                        <div class="status-badge status-working" onclick="filterProjects('Working')">
                            <div class="count"><?=$totalUsers ?></div>
                            <div class="label">Working</div>
                        </div>
                        <div class="status-badge status-pending" onclick="filterProjects('Pending')">
                            <div class="count"><?=$totalProjects?></div>
                            <div class="label">Pending</div>
                        </div>
                        <div class="status-badge status-completed" onclick="filterProjects('Completed')">
                            <div class="count"><?=$overdueProjects?></div>
                            <div class="label">Completed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="stats-overview">
            <div class="stats-grid">
                <!-- User Stats -->
                <div class="stat-card users-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content-group">
                        <div class="stat-main">
                            <h3>12</h3>
                            <p>Total Users</p>
                            <div class="stat-trend positive">
                                <i class="fas fa-arrow-up"></i>
                                +2 this month
                            </div>
                        </div>
                        <div class="stat-breakdown">
                            <div class="breakdown-item">
                                <span class="breakdown-label">Admins</span>
                                <span class="breakdown-value">2</span>
                            </div>
                            <div class="breakdown-item">
                                <span class="breakdown-label">Clients</span>
                                <span class="breakdown-value">0</span>
                            </div>
                            <div class="breakdown-item">
                                <span class="breakdown-label">Freelancers</span>
                                <span class="breakdown-value">6</span>
                            </div>
                            <div class="breakdown-item">
                                <span class="breakdown-label">Applicants</span>
                                <span class="breakdown-value">4</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Project Stats -->
                        <div class="stat-card projects-card">
                            <div class="stat-icon">
                                <i class="fas fa-project-diagram"></i>
                            </div>
                            <div class="stat-content-group">
                                <div class="stat-main">
                                    <h3>12</h3>
                                    <p>Total Users</p>
                                    <div class="stat-trend positive">
                                        <i class="fas fa-arrow-up"></i>
                                        +<?= $recentProjects ?> this month
                                    </div>
                                </div>
                                <div class="stat-breakdown">
                                    <div class="breakdown-item">
                                        <span class="breakdown-label">Working</span>
                                        <span class="breakdown-value status-working"><?= $projectCounts['Working'] ?></span>
                                    </div>
                                    <div class="breakdown-item">
                                        <span class="breakdown-label">Pending</span>
                                        <span class="breakdown-value status-pending"><?= $projectCounts['Pending'] ?></span>
                                    </div>
                                    <div class="breakdown-item">
                                        <span class="breakdown-label">Completed</span>
                                        <span class="breakdown-value status-completed"><?= $projectCounts['Completed'] ?></span>
                                    </div>
                                    <div class="breakdown-item">
                                        <span class="breakdown-label">Canceled</span>
                                        <span class="breakdown-value status-canceled"><?= $projectCounts['Canceled'] ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                <!-- Financial Stats -->
                <div class="stat-card financial-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content-group">
                        <div class="stat-main">
                            <h3>12</h3>
                            <p>Total Users</p>
                            <div class="stat-trend positive">
                                <h3>₱<?= number_format($budgetData['total_budget']) ?></h3>
                                <p>Total Project Budget</p>
                            </div>
                            <br>
                        </div>
                        <div class="stat-breakdown">
                            <div class="breakdown-item">
                                <span class="breakdown-label">Completed</span>
                                <span class="breakdown-value">₱<?= number_format($budgetData['completed_budget']) ?></span>
                            </div>
                            <div class="breakdown-item">
                                <span class="breakdown-label">Active</span>
                                <span class="breakdown-value">₱<?= number_format($budgetData['active_budget']) ?></span>
                            </div>
                            <div class="breakdown-item">
                                <span class="breakdown-label">Pending</span>
                                <span class="breakdown-value">₱<?= number_format($budgetData['pending_budget']) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
        <!-- Quick Actions -->
        <div class="quick-actions-section">
            <div class="quick-actions-card">
                <h3>Quick Actions</h3>
                <div class="quick-actions-grid">
                    <button class="quick-action-btn" onclick="window.location.href='adminUser.php'">
                        <i class="fas fa-user-plus"></i>
                        <span>Add User</span>
                    </button>
                    <button class="quick-action-btn" onclick="window.location.href='adminProj.php'">
                        <i class="fas fa-plus-circle"></i>
                        <span>New Project</span>
                    </button>
                    <button class="quick-action-btn" onclick="window.location.href='adminNotif.php'">
                        <i class="fas fa-bell"></i>
                        <span>Send Notification</span>
                    </button>
                    <button class="quick-action-btn" onclick="window.location.href='adminPay.php'">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Manage Salary</span>
                    </button>
                    <button class="quick-action-btn" onclick="window.location.href='adminMes.php'">
                        <i class="fas fa-envelope"></i>
                        <span>Messages</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section">
            <div class="charts-grid">
                <!-- User Growth Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>User Growth (Last 6 Months)</h3>
                        <div class="chart-actions">
                            <button class="chart-btn" onclick="refreshChart('userChart')">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="userChart"></canvas>
                    </div>
                </div>

                <!-- Project Status Distribution -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Project Status Distribution</h3>
                        <div class="chart-actions">
                            <button class="chart-btn" onclick="refreshChart('projectChart')">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="projectChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="activity-section">
            <div class="activity-grid">
                <!-- Recent Users -->
                <div class="activity-card">
                    <div class="activity-header">
                        <h3>Recent Users</h3>
                        <a href="adminUser.php" class="view-all-link">View All</a>
                    </div>
                    <div class="activity-content">
                        <?php if(!empty($latestUsers)): ?>
                            <?php foreach($latestUsers as $user): ?>
                                <div class="activity-item">
                                    <div class="activity-avatar">
                                        <img src="<?= htmlspecialchars($user['USER_IMG'] ?? '../../imgs/default-avatar.png') ?>" alt="User">
                                    </div>
                                    <div class="activity-info">
                                        <div class="activity-name">
                                            <?= htmlspecialchars(($user['USER_FSTNAME'] ?? '') . ' ' . ($user['USER_LSTNAME'] ?? '')) ?>
                                        </div>
                                        <div class="activity-meta">
                                            <span class="user-type-tag <?= strtolower($user['USER_TYPE'] ?? 'user') ?>-tag">
                                                <?= htmlspecialchars($user['USER_TYPE'] ?? 'User') ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-activity">No recent users</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Projects -->
                <div class="activity-card">
                    <div class="activity-header">
                        <h3>Recent Projects</h3>
                        <a href="adminProj.php" class="view-all-link">View All</a>
                    </div>
                    <div class="activity-content">
                        <?php if(!empty($latestProjects)): ?>
                            <?php foreach($latestProjects as $project): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-project-diagram"></i>
                                    </div>
                                    <div class="activity-info">
                                        <div class="activity-name">
                                            <?= htmlspecialchars($project['PRO_TITLE'] ?? 'Untitled Project') ?>
                                        </div>
                                        <div class="activity-meta">
                                            <span class="status-tag status-<?= strtolower($project['PRO_STATUS'] ?? 'pending') ?>-tag">
                                                <?= htmlspecialchars($project['PRO_STATUS'] ?? 'Pending') ?>
                                            </span>
                                            <span class="priority-tag priority-<?= strtolower($project['PRO_PRIORITY_LEVEL'] ?? 'medium') ?>">
                                                <?= htmlspecialchars($project['PRO_PRIORITY_LEVEL'] ?? 'Medium') ?>
                                            </span>
                                        </div>
                                        <?php if($project['USER_FSTNAME']): ?>
                                            <div class="activity-assignee">
                                                Assigned to: <?= htmlspecialchars($project['USER_FSTNAME'] . ' ' . $project['USER_LSTNAME']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-activity">No recent projects</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Chart data preparation
    const monthlyUserData = <?= json_encode(array_reverse($monthlyUsers)) ?>;
    const monthlyProjectData = <?= json_encode(array_reverse($monthlyProjects)) ?>;
    const projectStatusData = <?= json_encode(array_values($projectCounts)) ?>;
    const userTypeData = <?= json_encode(array_values($userCounts)) ?>;

    // User Growth Chart
    const userCtx = document.getElementById('userChart').getContext('2d');
    const userChart = new Chart(userCtx, {
        type: 'line',
        data: {
            labels: monthlyUserData.map(item => {
                const date = new Date(item.month + '-01');
                return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
            }),
            datasets: [{
                label: 'New Users',
                data: monthlyUserData.map(item => item.count),
                borderColor: '#105D63',
                backgroundColor: 'rgba(16, 93, 99, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    // Project Status Chart
    const projectCtx = document.getElementById('projectChart').getContext('2d');
    const projectChart = new Chart(projectCtx, {
        type: 'doughnut',
        data: {
            labels: ['Working', 'Pending', 'Completed', 'Canceled'],
            datasets: [{
                data: projectStatusData,
                backgroundColor: [
                    '#3498db',
                    '#f39c12',
                    '#2ecc71',
                    '#e74c3c'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });

    // Refresh chart function
    function refreshChart(chartName) {
        // Add refresh animation
        const chartElement = document.getElementById(chartName);
        chartElement.style.opacity = '0.5';

        setTimeout(() => {
            chartElement.style.opacity = '1';
            // Here you would typically fetch new data and update the chart
            console.log('Refreshing ' + chartName);
        }, 1000);
    }

    // Generate report function
    function generateReport() {
        // This would typically open a modal or redirect to a report generation page
        alert('Report generation feature would be implemented here');
    }

    // Auto-refresh dashboard every 5 minutes
    setInterval(() => {
        // You could implement auto-refresh of certain data here
        console.log('Auto-refreshing dashboard data...');
    }, 300000);

    // Welcome section animations
    document.addEventListener('DOMContentLoaded', function() {
        const welcomeCard = document.querySelector('.welcome-card');
        const statCards = document.querySelectorAll('.stat-card');

        // Animate welcome card
        setTimeout(() => {
            welcomeCard.style.opacity = '1';
            welcomeCard.style.transform = 'translateY(0)';
        }, 100);

        // Animate stat cards with stagger
        statCards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 200 + (index * 100));
        });
    });
</script>
</body>
</html>