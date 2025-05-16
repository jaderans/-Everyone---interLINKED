<?php
include_once 'interlinkedDB.php';
$master_con = connectToDatabase(3006);
$slave_con = connectToDatabase(3007);

$name = $_GET['keyword'];

$stmt = $slave_con->prepare("SELECT * FROM `user` WHERE `USER_NAME` LIKE :name OR `USER_TYPE` LIKE :name ");
$stmt->execute(array('name' => "%$name%"));
$result = $stmt->fetchAll();

if(empty($result)){
    echo "User not found!";
}

ob_start();
foreach ($result as $data) {
    $name = $data['USER_NAME'];
    $type = $data['USER_TYPE'];
    include 'result.php';
    echo ob_get_clean();
}
