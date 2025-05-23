<?php
session_start();
include('interlinkedDB.php');
$conn = connectToDatabase();
$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);

// Default search
$search = $_GET['search'] ?? '';
$userName = $_SESSION['userName'];
$id = $_SESSION['user_id'];

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

$name = $_SESSION['userName'];

//-------------------------------------------------------------------------------- Notif Code
$stmt = $slave_con->prepare("SELECT * FROM user WHERE USER_ID = ?");
$stmt->execute([$id]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as $res) {
    $_SESSION['USER_ID'] = $res['USER_ID'];
}

$id = $_SESSION['USER_ID'];

// Sort order logic
$sortOrder = 'DESC'; // default
if (isset($_GET['sort']) && in_array(strtoupper($_GET['sort']), ['ASC', 'DESC'])) {
    $sortOrder = strtoupper($_GET['sort']);
}

// Fetch notifications with sorting
$stmt = $slave_con->prepare("SELECT * FROM `notifications` WHERE `USER_ID` = :id ORDER BY `NOTIF_DATE` $sortOrder");
$stmt->bindParam(':id', $id);
$stmt->execute();
$notifResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Store latest NOTIF_ID in session (optional)
foreach ($notifResult as $res) {
    $_SESSION['NOTIF_ID'] = $res['NOTIF_ID'];
}
$notifId = $_SESSION['NOTIF_ID'];

// Handle mark-as-read action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['notifId'])) {
    $notifId = $_POST['notifId'];

    $stmt = $master_con->prepare("UPDATE notifications SET NOTIF_STATUS = 'Read' WHERE NOTIF_ID = :notifId");
    $stmt->bindParam(':notifId', $notifId);
    $stmt->execute();
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="notif.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Notification</title>
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
        <li><a href="adminDash.php"><i class="fas fa-database"></i> Dashboard</a></li>
        <li><a href="adminProj.php"><i class="fas fa-project-diagram"></i> Projects</a></li>
        <li><a href="adminPay.php"><i class="fas fa-dollar-sign"></i> Salary</a></li>
        <li><a href="adminUser.php"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="adminNotif.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li><a href="adminMes.php"><i class="fas fa-envelope"></i> Message</a></li>
        <li><a href="adminProf.php"><i class="fas fa-user"></i> Profile</a></li>
        <div class="btm-content">
            <button class="logout-button" onclick="window.location.href='../loginSignup/logIn.php';">
                <i class="fas fa-sign-out"></i> Log Out
            </button>
        </div>
    </ul>
</div>

<!-- Navbar -->
<div class="navbar">
    <div class="topvar">
        <div class="navtitle">
            <h1>Admin | </h1>
            <p>NOTIFICATION</p>
        </div>
        <div class="navprofile">
            <div class="name">
                <h4><?=$name?></h4>
            </div>
            <div class="profile">
                <img src="../../imgs/profile.png" alt="Admin Profile">
            </div>
        </div>
    </div>
</div>

<div class="notif-container">
    <div class="title">
        <h1>NOTIFICATION</h1>
        <div class="sort-buttons" style="margin-bottom: 20px;">
            <form method="get" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                <input type="hidden" name="sort" value="ASC">
                <button type="submit" class="btn-sort">Oldest</button>
            </form>
            <form method="get" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                <input type="hidden" name="sort" value="DESC">
                <button type="submit" class="btn-sort">Newest</button>
            </form>
        </div>
    </div>

    <div class="notif-content">
        <?php foreach ($notifResult as $row) { ?>
            <div class="notif">
                <h3><?= htmlspecialchars($row['NOTIF_TYPE']) ?></h3>
                <p><?= htmlspecialchars($row['NOTIF_DESCRIPTION']) ?></p>
                <p><?= htmlspecialchars($row['NOTIF_DATE']) ?></p>

                <!-- Delete Notification -->
                <form action="admin-delete-notif.php" method="post"
                      onsubmit="return confirm('Are you sure you want to delete this notification?');">
                    <button class="btn-edit" name="notif_Id" value="<?= $row['NOTIF_ID'] ?>">Delete</button>
                </form>

                <!-- Mark As Read -->
                <?php if ($row['NOTIF_STATUS'] !== 'Read') { ?>
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
                        <button class="btn-edit" name="notifId" value="<?= $row['NOTIF_ID'] ?>">Mark As Read</button>
                    </form>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
</body>
</html>
