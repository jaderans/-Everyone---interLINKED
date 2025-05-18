<?php
session_start();
header('Content-Type: application/json');
include('interlinkedDB.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $conn = connectToDatabase();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        exit;
    }

    // Collect form data
    $projectId = $_POST['project_id'] ?? null;
    $title = trim($_POST['project_title'] ?? '');
    $description = trim($_POST['project_description'] ?? '');
    $startDate = $_POST['project_start_date'] ?? '';
    $endDate = $_POST['project_end_date'] ?? '';
    $status = trim($_POST['project_status'] ?? 'Pending');
    $type = trim($_POST['project_type'] ?? '');
    $priority = trim($_POST['project_priority'] ?? '');
    $commissionedBy = trim($_POST['project_commissioned_by'] ?? '');
    $assignee = isset($_POST['project_assignee']) && $_POST['project_assignee'] !== '' ? $_POST['project_assignee'] : null;
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User not logged in.']);
        exit;
    }

    // Validate assignee if provided
    if ($assignee !== null) {
        $checkStmt = $conn->prepare("SELECT USER_ID FROM user WHERE USER_ID = ?");
        $checkStmt->execute([$assignee]);
        if (!$checkStmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Invalid assignee ID.']);
            exit;
        }
    }

    if ($projectId) {
        // Update
        $sql = "UPDATE projects SET 
                    PRO_TITLE = :title,
                    PRO_DESCRIPTION = :description,
                    PRO_START_DATE = :start_date,
                    PRO_END_DATE = :end_date,
                    PRO_STATUS = :status,
                    PRO_TYPE = :type,
                    PRO_PRIORITY_LEVEL = :priority,
                    PRO_COMMISSIONED_BY = :commissioned_by,
                    USER_ID = :assignee
                WHERE PRO_ID = :project_id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    } else {
        // Insert
        $sql = "INSERT INTO projects (
                    PRO_TITLE,
                    PRO_DESCRIPTION,
                    PRO_START_DATE,
                    PRO_END_DATE,
                    PRO_STATUS,
                    PRO_TYPE,
                    PRO_PRIORITY_LEVEL,
                    PRO_COMMISSIONED_BY,
                    CREATED_BY,
                    CREATED_AT,
                    USER_ID
                ) VALUES (
                    :title,
                    :description,
                    :start_date,
                    :end_date,
                    :status,
                    :type,
                    :priority,
                    :commissioned_by,
                    :created_by,
                    NOW(),
                    :assignee
                )";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':created_by', $userId, PDO::PARAM_STR); // now correctly binding as string
    }

    // Shared bindings
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date', $endDate);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':priority', $priority);
    $stmt->bindParam(':commissioned_by', $commissionedBy);

    // Assignee bind
    if ($assignee === null) {
        $stmt->bindValue(':assignee', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindParam(':assignee', $assignee, PDO::PARAM_STR); // also string
    }

    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Project saved successfully.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
