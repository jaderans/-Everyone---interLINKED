<?php
include 'freelancer-navbar-template.php' ;
include_once 'interlinkedDB.php';

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="freelancer-style.css">
    <link rel="icon" type="image/x-icon" href="../imgs/inlFavicon@4x.png">
    <title>Dashboard</title>
</head>
<body>



<div class="container">
    <div class="content">
        <h1>Dashboard</h1><br><br>
        <h1>My Commissions</h1>
        <p>Date</p><br>

        <h1>Statics</h1>

    </div>

    <div class="card-container">
        <div class="card">
            <h1><i class="fa-solid fa-database"></i>ONGOING</h1>
            <div class="card-content">
                <h1 class="num">4</h1>
                <h1 class="label">Tasks</h1>
            </div>
        </div>
        <div class="card">
            <h1><i class="fa-solid fa-database"></i>PENDING</h1>
            <div class="card-content">
                <h1 class="num">9</h1>
                <h1 class="label">Tasks</h1>
            </div>
        </div>
        <div class="card">
            <h1><i class="fa-solid fa-database"></i>COMPLETED</h1>
            <div class="card-content">
                <h1 class="num">20</h1>
                <h1 class="label">Tasks</h1>
            </div>
        </div>
        <div class="card">
            <h1><i class="fa-solid fa-database"></i>Summary</h1>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Adipisci beatae cupiditate dolore dolores harum incidunt itaque iusto molestiae, nesciunt pariatur quia, quos repellat totam veniam, voluptates? Maiores omnis quibusdam rerum!</p>
            <a href="freelancer-project-page.php">Check</a>
        </div>

    </div>


</div>
</body>
</html>
