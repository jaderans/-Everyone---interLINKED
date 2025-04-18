<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/x-icon" href="../imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="freelancer-nav-style.css">
    <link rel="stylesheet" href="freelancer-style.css">
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
            <button class="btn-top"><a href="../index.php"><i class="fa-solid fa-magnifying-glass"></i> Browse</a></button>
            <button class="btn-top"><a href="../loginSignup/logIn.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></button>
        </div>
        <div class="profile">
            <a href="freelancer-profile-page.php"><img src="../imgs/profile.png" alt=""></a>
        </div>
        <div class="name">
            <a href="freelancer-profile-page.php"><h4 style="font-weight: 700">Furina</h4></a>
            <p style="font-size: 12px">Freelancer</p>
        </div>
    </div>

</div>
    <div class="navbar">
        <div class="sidebar-frame">
            <ul class="side-content">
                <li><a href="freelancer-dashboard-page.php"><i class="fa-solid fa-database"></i> Dashboard</a></li>
                <li><a href="freelancer-project-page.php"><i class="fa-solid fa-chart-simple"></i> Projects</a></li>
                <li><a href="freelancer-salary-page.php"><i class="fa-solid fa-dollar-sign"></i> Salary</a></li>
                <li><a href="freelancer-notification-page.php"><i class="fa-solid fa-bell"></i> Notification</a></li>
                <li><a href="freelancer-message-page.php"><i class="fa-solid fa-envelope"></i> Message</a></li>
                <li><a href="freelancer-profile-page.php"><i class="fa-solid fa-circle-user"></i> Profile</a></li>
            </ul>

            <div class="lower-content">
                <button id="myBtn"><i class="fa-regular fa-paper-plane"></i> Post work</a></button>
            </div>

            <div id="myModal" class="modal">

                <!-- Modal content -->
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h1>POST WORK</h1>
                    <p>Some text in the Modal</p>
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Facere id impedit in mollitia nesciunt quibusdam quis, quisquam quos veniam voluptates? A ab debitis earum magni nulla rerum, sequi? Numquam, sit!</p>


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

            <div class="help">
                <h4><a href="#"><i class="fa-solid fa-circle-info"></i> Help & Support</a></h4>
            </div>

        </div>
    </div>

<script>
    // Get the modal
    var modal = document.getElementById("myModal");

    // Get the button that opens the modal
    var btn = document.getElementById("myBtn");

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks on the button, open the modal
    btn.onclick = function() {
        modal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
</script>
</body>
</html>
