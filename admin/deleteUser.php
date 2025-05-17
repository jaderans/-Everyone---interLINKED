<?php
include('interlinkedDB.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Optional: confirm if user exists before deleting
    $deleteQuery = "DELETE FROM user WHERE USER_ID = $id";
    mysqli_query($conn, $deleteQuery);
}

header("Location: adminUser.php");
exit;
?>
