<?php
include 'freelancer-navbar-template.php';
include_once 'interlinkedDB.php';

$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);

// Fetch user ID from session
$userName = $_SESSION['userName'];
$stmt = $slave_con->prepare("SELECT * FROM user WHERE USER_NAME = ?");
$stmt->execute([$userName]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as $res) {
    $_SESSION['USER_ID'] = $res['USER_ID'];
}

$id = $_SESSION['USER_ID'];

// Sort order logic
$sortOrder = 'DESC'; // default
if (isset($_GET['sort']) && in_array(strtoupper($_GET['sort']), ['ASC', 'DESC'])) {
    $sortOrder = strtoupper($_GET['sort']);
}

// Fetch notifications with sorting
$stmt = $slave_con->prepare("SELECT * FROM `notifications` WHERE `USER_ID` = :id ORDER BY `NOTIF_DATE` $sortOrder");
$stmt->bindParam(':id', $id);
$stmt->execute();
$notifResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Store latest NOTIF_ID in session (optional)
foreach ($notifResult as $res) {
    $_SESSION['NOTIF_ID'] = $res['NOTIF_ID'];
}
$notifId = $_SESSION['NOTIF_ID'];

// Handle mark-as-read action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['notifId'])) {
    $notifId = $_POST['notifId'];

    $stmt = $master_con->prepare("UPDATE notifications SET NOTIF_STATUS = 'Read' WHERE NOTIF_ID = :notifId");
    $stmt->bindParam(':notifId', $notifId);
    $stmt->execute();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
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
            <div class="sort-buttons" style="margin-bottom: 20px;">
                <form method="get" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                    <input type="hidden" name="sort" value="ASC">
                    <button type="submit" class="btn-sort">Oldest</button>
                </form>
                <form method="get" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
                    <input type="hidden" name="sort" value="DESC">
                    <button type="submit" class="btn-sort">Newest</button>
                </form>
            </div>
        </div>

        <!-- Sorting Buttons -->


        <!-- Notification List -->
        <div class="notif-content">
            <?php foreach ($notifResult as $row) { ?>
                <div class="notif">
                    <h3><?= htmlspecialchars($row['NOTIF_TYPE']) ?></h3>
                    <p><?= htmlspecialchars($row['NOTIF_DESCRIPTION']) ?></p>
                    <p><?= htmlspecialchars($row['NOTIF_DATE']) ?></p>

                    <!-- Delete Notification -->
                    <form action="freelancer-delete-notif.php" method="post"
                          onsubmit="return confirm('Are you sure you want to delete this notification?');">
                        <button class="btn-edit" name="notif_Id" value="<?= $row['NOTIF_ID'] ?>">Delete</button>
                    </form>

                    <!-- Mark As Read -->
                    <?php if ($row['NOTIF_STATUS'] !== 'Read') { ?>
                        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post">
                            <button class="btn-edit" name="notifId" value="<?= $row['NOTIF_ID'] ?>">Mark As Read</button>
                        </form>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
</body>
</html>
