<?php
session_start();
include('interlinkedDB.php');
include_once 'checkIfSet.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    exit();
}

$master_con = connectToDatabase(3306);

try {
    $projectId = $_POST['project_id'];
    $title = $_POST['project_title'];
    $type = $_POST['project_type'];
    $priority = $_POST['project_priority'];
    $startDate = $_POST['project_start_date'];
    $endDate = $_POST['project_end_date'];
    $assignee = !empty($_POST['project_assignee']) ? $_POST['project_assignee'] : null;
    $commissionedBy = $_POST['project_commissioned_by'];
    $description = $_POST['project_description'];
    $status = $_POST['project_status'];

    $stmt = $master_con->prepare("UPDATE projects SET 
                                  PRO_TITLE = ?, 
                                  PRO_TYPE = ?, 
                                  PRO_PRIORITY_LEVEL = ?, 
                                  PRO_START_DATE = ?, 
                                  PRO_END_DATE = ?, 
                                  USER_ID = ?, 
                                  PRO_COMMISSIONED_BY = ?, 
                                  PRO_DESCRIPTION = ?, 
                                  PRO_STATUS = ? 
                                  WHERE PRO_ID = ?");

    $stmt->execute([
        $title, $type, $priority, $startDate, $endDate,
        $assignee, $commissionedBy, $description, $status, $projectId
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Project updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made or project not found']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error updating project: ' . $e->getMessage()]);
}
?>