<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/x-icon" href="../imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="freelancer-style.css">
    <title>Message</title>
</head>
<body>
<?php include 'client-navbar-template.php' ?>

<div class="container">
    <div class="content">
        <div class="parent">
            <div class="contact">
                <h1>Message</h1>

                <div class="selection">
                    <div class="sel">
                        <button class="btn-left"><a href="">Compose</a></button>
                    </div>
                    <div class="sel">
                        <button class="btn-left"><a href="">Inbox</a></button>
                    </div>
                    <div class="sel">
                        <button class="btn-left"><a href="">Sent</a></button>
                    </div>
                </div>

            </div>

            <div class="receiver-details">
                <div class="profile">
                    <a href="client-profile-page.php"><img src="../imgs/profile.png" alt=""></a>
                </div>
                <div class="name">
                    <a href="client-profile-page.php"><h4 style="font-weight: 700">Client</h4></a>
                    <p style="font-size: 12px">Client</p>
                </div>
            </div>

            <div class="message">
                <form action="#" method="post" class="message-form">
                    <label for="">To: </label>
                    <input type="text" name="admin" placeholder="@Admin eg." required><br>
                    <label for="">Subject: </label>
                    <input type="text" name="subject" placeholder="Add Subject" required><br>
                    <label for="">Message: </label>
                    <textarea id="" name="message" required placeholder="Type here..."></textarea><br>
                    <input class="attach" type="file" id="" name="myfile" multiple><br><br>
                    <button class="btn-1" type="submit" name="action" value="login"><a href=""><i class="fa-regular fa-paper-plane"></i>Send</a></button>
                </form>

            </div>
        </div>

    </div>

</div>
</body>
</html>

