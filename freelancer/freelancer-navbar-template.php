<?php
session_start();
include('interlinkedDB.php');
include_once 'SecurityCheck.php';
$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);

$user = $_SESSION['userName'];
$id = $_SESSION['user_id'];

$stmt = $slave_con->prepare("SELECT * FROM user where USER_NAME = ?");
$stmt->execute([$user]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as $res) {
    $_SESSION['USER_ID'] = $res['USER_ID'];
}

$id = $_SESSION['user_id'];
$notif = $slave_con->prepare("SELECT COUNT(*) as count FROM notifications WHERE NOTIF_STATUS = 'Unread' and USER_ID =:userId ;");
$notif->execute(['userId' => $id]);
$notif->execute();
$resNotif = $notif->fetch(PDO::FETCH_ASSOC);


$mes = $slave_con->prepare("SELECT COUNT(*) as countMes FROM email WHERE EM_STATUS = 'Unread' and USER_ID =:userId ;");
$mes->execute(['userId' => $id]);
$mes->execute();
$mesNotif = $mes->fetch(PDO::FETCH_ASSOC);

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $slave_con->prepare("SELECT USER_IMG FROM user WHERE USER_ID = :id");
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['USER_IMG']) {
        header("Content-Type: image/jpeg"); // Adjust if you're supporting other formats
        echo $user['USER_IMG'];
        exit;
    }
}

http_response_code(404);
//echo "Image not found.";

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/x-icon" href="../imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="freelancer-nav-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.0/css/font-awesome.min.css">
    <title>Navigation Template</title>
</head>
<body>
<div class="topvar">
    <div class="logo">
        <img src="../imgs/inl2Logo.png" alt="Company Logo">
    </div>

    <!-- Mobile menu toggle button -->
    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
        <i class="fa-solid fa-bars"></i>
    </button>

    <div class="top-right">
        <div class="profile">
            <div class="img">
                <img src="getUserImage.php?id=<?= htmlspecialchars($id) ?>" alt="Profile Image">
            </div>
        </div>
        <div class="name">
            <a href="freelancer-profile-page.php"><h4 style="font-weight: 700"><?= htmlspecialchars($user) ?></h4></a>
            <p style="font-size: 12px">Freelancer</p>
        </div>
    </div>
</div>

<!-- Mobile menu overlay -->
<div class="navbar-overlay" onclick="closeMobileMenu()"></div>

<div class="navbar" id="navbar">
    <div class="sidebar-frame">
        <ul class="side-content">
            <li><a href="freelancer-dashboard-page.php"><i class="fa-solid fa-database"></i> Dashboard</a></li>
            <li><a href="freelancer-project-page.php"><i class="fa-solid fa-chart-simple"></i> Projects</a></li>
            <li><a href="salary.php"><i class="fa-solid fa-dollar-sign"></i> Salary</a></li>
            <li><a href="freelancer-notification-page.php">
                    <?php if ($resNotif['count'] > 0) { ?>
                        <i class="fa-solid fa-bell" style="color: #9d3a3a"></i>
                    <?php } else{ ?>
                        <i class="fa-solid fa-bell"></i>
                    <?php  } ?>
                    Notifications</a>
            </li>
            <li><a href="freelancer-message-page.php"><i class="fa-solid fa-envelope"></i> Message</a></li>
            <li><a href="freelancer-profile-page.php"><i class="fa-solid fa-circle-user"></i> Profile</a></li>
        </ul>

        <div class="lower-content">
            <button class="btn-top" ><a href="https://drive.google.com/drive/folders/1Nr1mkELXDzzGG6DfkXlGW76BUxgBfa8f?usp=sharing" target="_blank"><i class="fa-solid fa-file-import"></i> Submit</a> </button>

            <button class="btn-top" style="margin-top: 10px" id="logoutBtn"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>

            <div class="help" style="text-align: left; margin-top: -15px">
                <h4><a href="#"><i class="fa-solid fa-circle-info"></i> Help & Support</a></h4>
            </div>
        </div>
    </div>
</div>

<!-- Logout Modal -->
<div id="logoutModal" class="logout-modal">
    <div class="logout-modal-content">
        <div class="logout-modal-header">
            <div class="logout-icon">
                <i class="fa-solid fa-right-from-bracket"></i>
            </div>
            <h3 class="logout-modal-title">Confirm Logout</h3>
            <p class="logout-modal-text">Are you sure you want to log out? You'll need to log in again to access your account.</p>
        </div>
        <div class="logout-modal-actions">
            <button class="logout-btn logout-btn-cancel" id="cancelLogout">Cancel</button>
            <button class="logout-btn logout-btn-confirm" id="confirmLogout">Log Out</button>
        </div>
    </div>
</div>

<script>

    // Logout modal functionality
    const logoutModal = document.getElementById('logoutModal');
    const logoutBtn = document.getElementById('logoutBtn');
    const cancelLogout = document.getElementById('cancelLogout');
    const confirmLogout = document.getElementById('confirmLogout');

    logoutBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        logoutModal.style.display = 'block';
    });

    cancelLogout.addEventListener('click', function() {
        logoutModal.style.display = 'none';
    });

    confirmLogout.addEventListener('click', function() {
        window.location.replace("../loginSignup/logIn.php");
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target === logoutModal) {
            logoutModal.style.display = 'none';
        }
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && logoutModal.style.display === 'block') {
            logoutModal.style.display = 'none';
        }
    });

    // Existing message modal functionality
    var modalMessage = document.getElementById("message");
    var btnMessage = document.getElementById("float-message");
    var spanmsg = document.querySelector("#message .close");

    if (btnMessage) {
        btnMessage.onclick = function () {
            modalMessage.style.display = "block";
        }
    }

    if (spanmsg) {
        spanmsg.onclick = function () {
            modalMessage.style.display = "none";
        }
    }

    // Mobile menu toggle functions
    function toggleMobileMenu() {
        const navbar = document.getElementById('navbar');
        const overlay = document.querySelector('.navbar-overlay');

        navbar.classList.toggle('mobile-open');
        overlay.classList.toggle('show');
    }

    function closeMobileMenu() {
        const navbar = document.getElementById('navbar');
        const overlay = document.querySelector('.navbar-overlay');

        navbar.classList.remove('mobile-open');
        overlay.classList.remove('show');
    }

    // Close mobile menu when clicking on menu items
    document.querySelectorAll('.side-content a, .btn-top').forEach(link => {
        link.addEventListener('click', closeMobileMenu);
    });

    // Close mobile menu on window resize if screen becomes larger
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeMobileMenu();
        }
    });

</script>

</body>
</html>