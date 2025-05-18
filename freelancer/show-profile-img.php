<?php
include('interlinkedDB.php');
$slave_con = connectToDatabase(3307);

$id = $_GET['id'] ?? null;
if ($id) {
    $stmt = $slave_con->prepare("SELECT USER_IMG FROM user WHERE USER_ID = :id");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['USER_IMG']) {
        header("Content-Type: image/jpeg"); // adjust if necessary
        echo $row['USER_IMG'];
        exit;
    }
}
http_response_code(404);
?>
