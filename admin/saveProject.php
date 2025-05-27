<?php
session_start();
header('Content-Type: application/json');
include('interlinkedDB.php');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Function to create payment entry
function createPaymentEntry($conn, $projectId, $userId, $endDate, $amount = 0) {
    // Only create payment entry if there's an assigned user (freelancer)
    if ($userId === null) {
        return;
    }

//    // Check if freelancer has a bank account
//    $bankStmt = $conn->prepare("SELECT BNK_ID FROM user WHERE USER_ID = ? AND BNK_ID IS NOT NULL");
//    $bankStmt->execute([$userId]);
//    $bankResult = $bankStmt->fetch(PDO::FETCH_ASSOC);
//
//    $payStatus = $bankResult ? 'Pending' : 'No Bank';
//    $bankId = $bankResult ? $bankResult['BNK_ID'] : null;

    // Insert payment entry
    $paymentStmt = $conn->prepare("INSERT INTO payment (USER_ID, PRO_ID) 
                                   VALUES (?, ?)");
    $paymentStmt->execute([$userId, $projectId]);
}

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
    $projectAmount = $_POST['project_amount'] ?? 0; // Project amount
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
        // Update existing project
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
        // Insert new project
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
        $stmt->bindParam(':created_by', $userId, PDO::PARAM_STR);
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
        $stmt->bindParam(':assignee', $assignee, PDO::PARAM_STR);
    }

    // Execute the project save
    $stmt->execute();

    // Handle payment entry creation/update
    if (!$projectId) {
        // This is a new project insertion
        $lastProjectId = $conn->lastInsertId();

        // Create payment entry if project has an assignee
        if ($assignee !== null) {
            createPaymentEntry($conn, $lastProjectId, $assignee, $endDate, $projectAmount);
        }
    } else {
        // This is an update - check if assignee changed and create/update payment entry
        if ($assignee !== null) {
            // Check if payment entry already exists
            $checkPayment = $conn->prepare("SELECT PAY_ID FROM payment WHERE PRO_ID = ?");
            $checkPayment->execute([$projectId]);

            if (!$checkPayment->fetch()) {
                // No payment entry exists, create one
                createPaymentEntry($conn, $projectId, $assignee, $endDate, $projectAmount);
            } else {
                // Update existing payment entry
                $bankStmt = $conn->prepare("SELECT BNK_ID FROM user WHERE USER_ID = ? AND BNK_ID IS NOT NULL");
                $bankStmt->execute([$assignee]);
                $bankResult = $bankStmt->fetch(PDO::FETCH_ASSOC);

                $payStatus = $bankResult ? 'Pending' : 'No Bank';
                $bankId = $bankResult ? $bankResult['BNK_ID'] : null;

                $updatePayment = $conn->prepare("UPDATE payment SET USER_ID = ?, BNK_ID = ?, PAY_STATUS = ?, PAY_DATE = ?, PAY_AMOUNT = ? WHERE PRO_ID = ?");
                $updatePayment->execute([$assignee, $bankId, $payStatus, $endDate, $projectAmount, $projectId]);
            }
        }
    }

    echo json_encode(['success' => true, 'message' => 'Project saved successfully.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>