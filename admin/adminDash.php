<?php
session_start();
include('interlinkedDB.php');
$conn = connectToDatabase();

// Fetch the current admin's data and store in session if not already present
if (!isset($_SESSION['admin_data']) || !isset($_SESSION['userName'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM user WHERE USER_ID = :user_id AND USER_TYPE = 'Admin'");
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
        $admin_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin_data) {
            // Store admin data in session for use across pages
            $_SESSION['admin_data'] = $admin_data;
            $_SESSION['userName'] = $admin_data['USER_FSTNAME'] . ' ' . $admin_data['USER_LSTNAME'];
            $_SESSION['name'] = $_SESSION['userName']; // For compatibility

            // Set profile image path if exists, otherwise use default
            if (!empty($admin_data['USER_IMG']) && file_exists($admin_data['USER_IMG'])) {
                $_SESSION['profile_img'] = $admin_data['USER_IMG'];
            } else {
                $_SESSION['profile_img'] = '../../imgs/profile.png';
            }
        } else {
            // User is not an admin, redirect to login
            session_destroy();
            header("Location: ../loginSignup/logIn.php");
            exit();
        }
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header("Location: ../loginSignup/logIn.php");
        exit();
    }
}

// Default search
$search = $_GET['search'] ?? '';

function fetchUsers($conn, $type, $search = '') {
    // Determine if we want applicants or non-applicants
    $typeCondition = $type === 'Applicant' ? "USER_TYPE = 'Applicant'" : "USER_TYPE != 'Applicant'";
    // Start building the query
    $query = "SELECT * FROM user WHERE $typeCondition";
    // Add search filter if provided
    if (!empty($search)) {
        $query .= " AND (USER_FSTNAME LIKE :search OR USER_LSTNAME LIKE :search OR USER_EMAIL LIKE :search)";
    }
    $stmt = $conn->prepare($query);
    // Bind parameters
    if (!empty($search)) {
        $stmt->execute(['search' => "%$search%"]);
    } else {
        $stmt->execute();
    }
    return $stmt;
}

$selectedUser = null;
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM user WHERE USER_ID = ?");
    $stmt->execute([$_GET['id']]);
    $selectedUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

$usersResult = fetchUsers($conn, 'User', $search);
$applicantsResult = fetchUsers($conn, 'Applicant', $search);

// Get variables from session
$name = $_SESSION['userName'];
$profile_img = $_SESSION['profile_img'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | interLINKED</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" type="image/x-icon" href="../imgs/inlFaviconwhite.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
    <div class="topvar2">
        <div class="logo">
            <img src="../../imgs/inl2LogoWhite.png" alt="Logo">
        </div>
    </div>
    <ul class="side-content">
        <li><a href="adminDash.php" class="active"><i class="fas fa-database"></i> Dashboard</a></li>
        <li><a href="adminProj.php"><i class="fas fa-project-diagram"></i> Projects</a></li>
        <li><a href="adminPay.php"><i class="fas fa-dollar-sign"></i> Salary</a></li>
        <li><a href="adminUser.php"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="adminNotif.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li><a href="adminMes.php"><i class="fas fa-envelope"></i> Message</a></li>
        <li><a href="adminProf.php"><i class="fas fa-user"></i> Profile</a></li>
        <div class="btm-content">
            <button class="logout-button" onclick="window.location.href='../loginSignup/logOut.php';">
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
                <h4><?=$name?></h4>
            </div>
            <div class="profile">
                <img src="<?=$profile_img?>" alt="Admin Profile">
            </div>
        </div>
    </div>
</div>

<!-- Main Content would go here -->
<div class="main-container">
    <!-- Dashboard content -->
</div>

<script>
    // Any dashboard specific JavaScript
</script>
</body>
</html>