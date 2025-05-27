<?php
include 'freelancer-navbar-template.php';
include_once 'interlinkedDB.php';
include_once 'SecurityCheck.php';

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


$notif = $slave_con->prepare("SELECT COUNT(*) as count FROM notifications WHERE NOTIF_STATUS = 'Unread' and USER_ID =:userId ;");
$notif->execute(['userId' => $id]);
$notif->execute();
$resNotif = $notif->fetch(PDO::FETCH_ASSOC);

$notif = $slave_con->prepare("SELECT COUNT(*) as countUnread FROM notifications WHERE NOTIF_STATUS = 'Read' and USER_ID =:userId ;");
$notif->execute(['userId' => $id]);
$notif->execute();
$unreadNotif = $notif->fetch(PDO::FETCH_ASSOC);
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
                
                <div class="sum">
                    <p>Unread <?=$resNotif['count']?></p>
                </div>

                <div class="sum">
                    <p>Read <?=$unreadNotif['countUnread']?></p>
                </div>
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
                    <!-- Delete Notification -->
                    <button class="btn-edit open-delete-modal" data-id="<?= $row['NOTIF_ID'] ?>">Delete</button>

                    <!-- Modal Structure -->
                    <div class="modal-overlay" id="deleteModal">
                        <div class="modal-box">
                            <h3>Confirm Deletion</h3>
                            <p>Are you sure you want to delete this notification?</p>
                            <form action="freelancer-delete-notif.php" method="post">
                                <input type="hidden" name="notif_Id" id="modalNotifId">
                                <div class="modal-actions">
                                    <button type="button" class="cancel-btn">Cancel</button>
                                    <button type="submit" class="confirm-btn">Delete</button>
                                </div>
                            </form>
                        </div>
                    </div>

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
<script>
    document.querySelectorAll('.open-delete-modal').forEach(button => {
        button.addEventListener('click', () => {
            const notifId = button.getAttribute('data-id');
            document.getElementById('modalNotifId').value = notifId;
            document.getElementById('deleteModal').style.display = 'flex';
        });
    });

    document.querySelector('.cancel-btn').addEventListener('click', () => {
        document.getElementById('deleteModal').style.display = 'none';
    });
</script>
</body>
</html>
