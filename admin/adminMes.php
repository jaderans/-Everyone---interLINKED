<?php
session_start();
include('interlinkedDB.php');
$conn = connectToDatabase();

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

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | interLINKED</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="email.css">
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
            <li><a href="adminDash.php"><i class="fas fa-database"></i> Dashboard</a></li>
            <li><a href="adminProj.php"><i class="fas fa-project-diagram"></i> Projects</a></li>
            <li><a href="adminPay.php"><i class="fas fa-dollar-sign"></i> Salary</a></li>
            <li><a href="adminUser.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="adminUser.php"><i class="fas fa-bell"></i> Notifications</a></li>
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
                <p>MESSAGES</p>
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

    <div class="message-container">
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aspernatur assumenda aut consequuntur debitis deserunt, dicta dolores ea in ipsam iusto laborum molestias obcaecati perferendis porro sint tempore unde veniam voluptatum.</p>
        <p><?=$userName?></p>
        <p><?=$id?></p>
    </div>

    <script>
    </script>
</body>
</html>
