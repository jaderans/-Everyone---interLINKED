<?php
function connectToDatabase($port)
{
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "finals";
    $port = "";

    try {
        $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
    }
}



