<?php
include 'freelancer-navbar-template.php';
include_once 'interlinkedDB.php';

$name = $_SESSION['userName'];

//code to display
$stmt = $conn->prepare("SELECT * FROM user where USER_NAME = ?");
$stmt->execute([$name]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="freelancer-nav-style.css">
    <title>Profile</title>
</head>
<body>


<div class="header">
    <h1>Plaveholder</h1>
    <img src="" alt="">
</div>

<div class="container">
    <div class="content">
        <div class="profile">
            <div class="img">
                <img src="imgs/profile.png" alt="">
            </div>

            <div class="profile-name">
                <h1><?=$name?></h1>
                <h2>Freelancer</h2>
            </div>

        </div>
        <div class="profile-details">
            <div class="details">
                <h1>Details</h1>
                <?php foreach ($result as $res) { ?>
                    <table style="width:100%" class="table">
                        <tr>
                            <th>User Name:</th>
                            <td><?=$res['USER_NAME']?></td>
                        </tr>
                        <tr>
                            <th>First Name:</th>
                            <td><?=$res['USER_FSTNAME']?></td>
                        </tr>
                        <tr>
                            <th>Last Name:</th>
                            <td><?=$res['USER_LSTNAME']?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?=$res['USER_EMAIL']?></td>
                        </tr>
                        <tr>
                            <th>ID:</th>
                            <td><?=$res['USER_ID']?></td>
                        </tr>
                        <tr>
                            <th>Password (Remove*):</th>
                            <td><?=$res['USER_PASSWORD']?></td>
                        </tr>
                        <tr>
                            <th>Birthday:</th>
                            <td><?=$res['USER_BIRTHDAY']?></td>
                        </tr>
                        <tr>
                            <th>Contact Details:</th>
                            <td><?=$res['USER_CONTACT']?></td>
                        </tr>
                        <tr>
                            <td>
                                <form action="freelancer-edit-profile.php" method="post">
                                    <button class="btn-edit" id="btn-edit" name="user_id" value="<?=$res['USER_ID']?>">Edit</button>
                                </form>
                            </td>
                        </tr>

                    </table>
                <?php } ?>

            </div>
        </div>

</body>
</html>
