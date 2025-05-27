<?php
session_start();
include('interlinkedDB.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['user_ids']) || !is_array($input['user_ids']) || empty($input['user_ids'])) {
        echo json_encode(['success' => false, 'message' => 'No user IDs provided']);
        exit;
    }

    $userIds = $input['user_ids'];

    try {
        $master_con = connectToDatabase(3306); // Master for writes

        // Begin transaction
        $master_con->beginTransaction();

        // Create placeholders for the IN clause
        $placeholders = str_repeat('?,', count($userIds) - 1) . '?';

        // Update status to REJECTED
        $stmt = $master_con->prepare("UPDATE user SET USER_STATUS = 'REJECTED' WHERE USER_ID IN ($placeholders) AND USER_TYPE = 'Applicant'");
        $result = $stmt->execute($userIds);

        $affectedRows = $stmt->rowCount();

        if ($affectedRows > 0) {
            $master_con->commit();
            echo json_encode(['success' => true, 'message' => 'Applicants rejected successfully', 'count' => $affectedRows]);
        } else {
            $master_con->rollback();
            echo json_encode(['success' => false, 'message' => 'No eligible applicants found to reject']);
        }

    } catch (Exception $e) {
        if (isset($master_con)) {
            $master_con->rollback();
        }
        error_log("Bulk reject error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>