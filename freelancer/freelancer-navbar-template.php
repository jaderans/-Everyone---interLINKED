<?php
session_start();
$user = $_SESSION['userName'];
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
<div class="float-message" id="float-message">
    <h3><i class="fa-solid fa-message"></i></h3>
</div>


<div class="topvar">
    <div class="logo">
        <img src="../imgs/inl2Logo.png" alt="">
    </div>
    <div class="top-right">
        <div class="right-btn">
<!--            <button class="btn-top"><a href="../index.php"><i class="fa-solid fa-magnifying-glass"></i> Home</a></button>-->
            <button class="btn-top" onclick="myFunction()"><a href="#"><i class="fa-solid fa-right-from-bracket"></i>Logout</a></button>
        </div>
        <div class="profile">
            <a href="freelancer-profile-page.php"><img src="../imgs/profile.png" alt=""></a>
        </div>
        <div class="name">
            <a href="freelancer-profile-page.php"><h4 style="font-weight: 700"><?= $user?></h4></a>
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
<!--                <li><a href="freelancer-message-page.php"><i class="fa-solid fa-envelope"></i> Message</a></li>-->
                <li><a href="freelancer-profile-page.php"><i class="fa-solid fa-circle-user"></i> Profile</a></li>
            </ul>

            <div class="lower-content">
                <button id="myBtn"><i class="fa-regular fa-paper-plane"></i> Submit</a></button>
            </div>

            <div id="myModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h1 style="font-weight: bold">UPLOAD</h1>

                    <form action="#" method="post" class="message-form">
                        <label for="">Title: </label>
                        <input type="text" id="title" name="title" required>
                        <label for="">Description: </label>
                        <textarea id="" name="message" required placeholder="Type here..."></textarea><br>
                        <input class="attach" type="file" id="" name="myfile" accept="image/*"><br><br>
                        <button class="send" type="submit" name="action" value="login"><a href=""><i class="fa-regular fa-paper-plane"></i>Post</a></button>
                    </form>
                </div>

            </div>

            <div id="message" class="modal-msg">

                <!-- Modal Message content -->
                <div class="modal-msg-content">
                    <span class="close">&times;</span>
                    <h1 style="font-weight: bold">MESSAGE</h1>

                    <div class="message">
                        <form action="#" method="post" class="message-form">
                            <div class="info">
                                <label for="">To </label>
                                <input type="text" name="admin" placeholder="@Admin eg." required><br>
                                <label for="">Subject </label>
                                <input type="text" name="subject" placeholder="Add Subject" required><br>
                            </div>

                            <label for="">Message</label>
                            <textarea name="message" required placeholder="Type here..."></textarea><br>

                            <div class="info">
                                <input class="attach" type="file" id="" name="myFile" multiple><br><br>
                                <button class="send" type="submit" name="action" value="login"><a href=""><i class="fa-regular fa-paper-plane"></i>Send</a></button>
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

        function myFunction() {
          let text = "Do you want to log-out?";

          if (confirm(text)== true){
              window.location.replace("../loginSignup/logIn.php");
          }
        }


    // Get the modal
    var modal = document.getElementById("myModal");
    var modalMessage = document.getElementById("message");

    // Get the button that opens the modal
    var btn = document.getElementById("myBtn");
    var btnMessage = document.getElementById("float-message");

    // Get the <span> element that closes the modal
    var span = document.querySelector("#myModal .close");
    var spanmsg = document.querySelector("#message .close");


    // When the user clicks on the button, open the modal
    btn.onclick = function() {
        modal.style.display = "block";
    }

    btnMessage.onclick = function() {
        modalMessage.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }
    spanmsg.onclick = function() {
        modalMessage.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
        if (event.target == modalMessage) {
            modalMessage.style.display = "none";
        }
    }

</script>
</body>
</html>
