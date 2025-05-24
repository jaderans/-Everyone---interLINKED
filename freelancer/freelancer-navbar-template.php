<?php
session_start();
include('interlinkedDB.php');
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

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
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
        <img src="../imgs/inl2Logo.png" alt="">
    </div>
    <div class="top-right">
        <div class="right-btn">
            <!-- Optional Home Button -->
            <!-- <button class="btn-top"><a href="../index.php"><i class="fa-solid fa-magnifying-glass"></i> Home</a></button> -->
        </div>
        <div class="profile">
            <a href="freelancer-profile-page.php"><img src="../imgs/profile.png" alt=""></a>
        </div>
        <div class="name">
            <a href="freelancer-profile-page.php"><h4 style="font-weight: 700"><?= htmlspecialchars($user) ?></h4></a>
            <p style="font-size: 12px">Freelancer</p>
        </div>
    </div>
</div>

<div class="navbar">
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
            <button class="btn-top" onclick="myFunction()"><a href="#"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></button>
        </div>

        <div class="help">
            <h4><a href="#"><i class="fa-solid fa-circle-info"></i> Help & Support</a></h4>
        </div>
    </div>
</div>

<script>
    function myFunction() {
        let text = "Do you want to log-out?";
        if (confirm(text) === true){
            window.location.replace("../loginSignup/logIn.php");
        }
    }

    var modalMessage = document.getElementById("message");
    var btnMessage = document.getElementById("float-message");
    var spanmsg = document.querySelector("#message .close");

    btnMessage.onclick = function () {
        modalMessage.style.display = "block";
    }

    spanmsg.onclick = function () {
        modalMessage.style.display = "none";
    }

    window.onclick = function (event) {
        if (event.target === modalMessage) {
            modalMessage.style.display = "none";
        }
    }
</script>

</body>
</html>
