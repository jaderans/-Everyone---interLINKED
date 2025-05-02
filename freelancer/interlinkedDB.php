<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "interlinkdb";

try {
    $conn = new PDO("mysql:host=$servername;port=3308;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
}
?>
