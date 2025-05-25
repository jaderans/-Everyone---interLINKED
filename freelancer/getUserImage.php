<?php
include_once 'interlinkedDB.php';

$slave_con = connectToDatabase(3307);

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $slave_con->prepare("SELECT USER_IMG FROM user WHERE USER_ID = :id");
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['USER_IMG']) {
        header("Content-Type: image/jpeg");  // Adjust this if image type differs
        echo $user['USER_IMG'];
        exit;
    }
}

http_response_code(404);
echo "Image not found.";
?>
