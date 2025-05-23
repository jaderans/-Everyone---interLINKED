<?php
session_start();
include_once 'interlinkedDB.php';
$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);

$id = $_POST['notif_Id'];
echo $id;

$stmt = $master_con->prepare("DELETE FROM notifications WHERE NOTIF_ID = :id");
$stmt->bindParam(':id', $id);
$result = $stmt->execute();

if ($result) {
    header("Location: adminNotif.php");
    exit();
} else {
    echo "Error deleting record";
}



