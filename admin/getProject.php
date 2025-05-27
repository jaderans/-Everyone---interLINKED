<?php
session_start();
include('interlinkedDB.php');

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(403);
    exit();
}

$slave_con = connectToDatabase(3307);
$projectId = $_GET['id'];

$stmt = $slave_con->prepare("SELECT p.*, u.USER_ID 
                             FROM projects p 
                             LEFT JOIN user u ON p.USER_ID = u.USER_ID 
                             WHERE p.PRO_ID = ?");
$stmt->execute([$projectId]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if ($project) {
    header('Content-Type: application/json');
    echo json_encode($project);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Project not found']);
}
?>