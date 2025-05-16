<?php
session_start();
include('interlinkedDB.php');
$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);

$user = $_SESSION['userName'];
$id = $_SESSION['user_id'];
$error = [];



if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient = $_POST['keyword'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    $hasError = false;

    if (empty($recipient)) {
        $error[] = "Recipient name is required";
        $hasError = true;
    }

    $stmt = $slave_con->prepare("SELECT * FROM `user` WHERE USER_NAME = :recipient");
    $stmt->execute(['recipient' => $recipient]);
    $recipientUser = $stmt->fetch();

    if (!$recipientUser) {
        $error[] = "User does not exist";
        $hasError = true;
    }

    if (empty($subject)) {
        $error[] = "Subject is required";
        $hasError = true;
    }
    if (empty($message)) {
        $error[] = "Message is required";
        $hasError = true;
    }
//
//    if (!$hasError) {
//
//    }




}


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

<!-- Floating message button -->
<div class="float-message" id="float-message">
    <h3><i class="fa-solid fa-message"></i></h3>
</div>

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
            <li><a href="freelancer-notification-page.php"><i class="fa-solid fa-bell"></i> Notification</a></li>
            <!-- <li><a href="freelancer-message-page.php"><i class="fa-solid fa-envelope"></i> Message</a></li> -->
            <li><a href="freelancer-profile-page.php"><i class="fa-solid fa-circle-user"></i> Profile</a></li>
        </ul>

        <div class="lower-content">
            <button class="btn-top" onclick="myFunction()"><a href="#"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></button>
        </div>

        <!-- Message Modal -->
        <div id="message" class="modal-msg" style="display:none;">
            <div class="modal-msg-content">
                <span class="close">&times;</span>
                <h1 style="font-weight: bold">MESSAGE</h1>

                <div class="message">
                    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post" class="message-form" enctype="multipart/form-data">
                        <div class="info">
                            <label for="">To </label>
                            <input type="text" name="keyword" placeholder="Admin" onkeyup="search(this.value)" ><br>
                            <label for="">Subject </label>
                            <input type="text" name="subject" placeholder="Add Subject" ><br>
                        </div>

                        <div id="search-results" class="result"></div>

                        <label for="">Message</label>
                        <textarea name="message"  placeholder="Type here..."></textarea><br>

                        <div class="info">
                            <button class="send" type="submit" name="action" value="login"><i class="fa-regular fa-paper-plane"></i> Send</button>
                            <span style="color: red">
                                <?php
                                foreach ($error as $error) {
                                    echo $error . "<br>";
                                }
                                ?>
                            </span>

                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="help">
            <h4><a href="#"><i class="fa-solid fa-circle-info"></i> Help & Support</a></h4>
        </div>
    </div>
</div>

<script>
    function search(input){
        if(input.length==0){
            document.getElementById("search-results").innerHTML = "";
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange=function() {
            if(this.readyState==4 && this.status==200){
                document.getElementById("search-results").innerHTML = this.responseText;
            } else {
                document.getElementById("search-results").innerHTML = "No Results Found";
            }
        }
        xhr.open("GET","search.php?keyword=" + input,true);
        xhr.send();
    }

    function selectUser(userName) {
        document.querySelector('input[name="keyword"]').value = userName;
        document.getElementById("search-results").innerHTML = "";
    }


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
