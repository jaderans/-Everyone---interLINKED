<?php
include('interlinkedDB.php');

$id = $_POST['user_id'];

$stmt = $conn->prepare("SELECT * FROM user where user_id=:id");
$stmt->execute(['id' => $id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

foreach ($row as $res ){
    echo $res. "<br>";
}