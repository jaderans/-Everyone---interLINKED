<?php
session_start();
header('Content-Type: application/json');
include('interlinkedDB.php');

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $conn = connectToDatabase();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
        exit;
    }

    // Collect form data safely
    $projectId = $_POST['project_id'] ?? null;
    $title = trim($_POST['project_title'] ?? '');
    $type = trim($_POST['project_type'] ?? '');
    $priority = trim($_POST['project_priority'] ?? '');
    $startDate = $_POST['project_start_date'] ?? '';
    $endDate = $_POST['project_end_date'] ?? '';
    $assignee = $_POST['project_assignee'] ?? null;
    $commissionedBy = trim($_POST['project_commissioned_by'] ?? '');
    $description = trim($_POST['project_description'] ?? '');
    $status = trim($_POST['project_status'] ?? 'Pending');

    // Get username from session
    $userName = $_SESSION['userName'] ?? null;

    if (!$userName) {
        echo json_encode(['success' => false, 'message' => 'User not logged in.']);
        exit;
    }

    // Retrieve user ID
    $stmt = $conn->prepare("SELECT USER_ID FROM user WHERE USER_NAME = ?");
    $stmt->execute([$userName]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $userId = $user['USER_ID'] ?? null;

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    if ($projectId) {
        // Update existing project
        $sql = "UPDATE projects SET 
                    PRO_TITLE = :title,
                    PRO_TYPE = :type,
                    PRO_PRIORITY_LEVEL = :priority,
                    PRO_START_DATE = :start_date,
                    PRO_END_DATE = :end_date,
                    USER_ID = :assignee,
                    PRO_COMMISSIONED_BY = :commissioned_by,
                    PRO_DESCRIPTION = :description,
                    PRO_STATUS = :status
                WHERE PRO_ID = :project_id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':project_id', $projectId, PDO::PARAM_INT);
    } else {
        // Insert new project
        $sql = "INSERT INTO projects (
                    PRO_TITLE, PRO_TYPE, PRO_PRIORITY_LEVEL, PRO_START_DATE,
                    PRO_END_DATE, USER_ID, PRO_COMMISSIONED_BY,
                    PRO_DESCRIPTION, PRO_STATUS, CREATED_BY, CREATED_AT
                ) VALUES (
                    :title, :type, :priority, :start_date, :end_date,
                    :assignee, :commissioned_by, :description, :status, :created_by, NOW()
                )";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':created_by', $userId, PDO::PARAM_INT);
    }

    // Bind common parameters
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':priority', $priority);
    $stmt->bindParam(':start_date', $startDate);
    $stmt->bindParam(':end_date', $endDate);

    // Optional assignee: set NULL if empty
    if (empty($assignee)) {
        $stmt->bindValue(':assignee', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindParam(':assignee', $assignee, PDO::PARAM_INT);
    }

    $stmt->bindParam(':commissioned_by', $commissionedBy);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':status', $status);

    // Execute query
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Project saved successfully.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
