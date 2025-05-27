<?php
session_start();
include('interlinkedDB.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];

    try {
        $master_con = connectToDatabase(3306); // Master for writes

        // Begin transaction
        $master_con->beginTransaction();

        // Update status to REJECTED
        $stmt = $master_con->prepare("UPDATE user SET USER_STATUS = 'REJECTED' WHERE USER_ID = ? AND USER_TYPE = 'Applicant'");
        $result = $stmt->execute([$userId]);

        if ($stmt->rowCount() > 0) {
            $master_con->commit();
            echo json_encode(['success' => true, 'message' => 'Applicant rejected successfully']);
        } else {
            $master_con->rollback();
            echo json_encode(['success' => false, 'message' => 'No applicant found with that ID']);
        }

    } catch (Exception $e) {
        if (isset($master_con)) {
            $master_con->rollback();
        }
        error_log("Reject error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method or missing user ID']);
}
?>