<?php
include 'freelancer-navbar-template.php';
include_once  'interlinkedDB.php';
$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);

$userName = $_SESSION['userName'];
$stmt = $slave_con->prepare("SELECT * FROM user where USER_NAME = ?");
$stmt->execute([$userName]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as $res) {
    $_SESSION['USER_ID'] = $res['USER_ID'];
}

$id = $_SESSION['USER_ID'];

$stmt = $slave_con->prepare("SELECT * FROM `notifications` WHERE `USER_ID` = :id ORDER BY `NOTIF_DATE` DESC");
$stmt->bindParam(':id', $id);
$stmt->execute();
$notifResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($notifResult as $res) {
    $_SESSION['NOTIF_ID'] = $res['NOTIF_ID'];
}
$notifId = $_SESSION['NOTIF_ID'];

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
    <title>Notification</title>
</head>
<body>

<div class="container">
    <div class="content-notif">
        <div class="title">
            <h1>NOTIFICATION</h1>
            <h3><?=$id?></h3>
        </div>
        <div class="notif-content">
            <?php foreach ($notifResult as $row) { ?>
                <div class="notif">
                    <h3 style="font-weight: bolder">MESSAGE</h3>
                    <h3><?= htmlspecialchars($row['NOTIF_TYPE'])?></h3>
                    <p><?= htmlspecialchars($row['NOTIF_DESCRIPTION'])?></p>
                    <p><?= htmlspecialchars($row['NOTIF_DATE'])?></p>

                    <form action="freelancer-delete-notif.php" method="post"
                          onsubmit="return confirm('Are you sure you want to delete this notification?');">
                        <button class="btn-edit" name="notif_Id" value="<?= $row['NOTIF_ID'] ?>">Delete</button>
                    </form>
                </div>
            <?php } ?>
        </div>
    </div>

</div>
</body>
</html>

