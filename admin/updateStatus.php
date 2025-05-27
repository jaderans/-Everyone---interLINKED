<?php
session_start();
include('interlinkedDB.php');
include_once 'checkIfSet.php';
$conn = connectToDatabase();
$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gather POST data
    $projectId = $_POST['project_id'] ?? null;
    $newStatus = $_POST['new_status'] ?? null;
    $reason = $_POST['status_reason'] ?? null;
    $updatedAt = date('Y-m-d H:i:s');

    // Validate inputs
    if (!$projectId || !$newStatus) {
        die("Missing required fields.");
    }

    // Optional: get user info from session
    $userName = $_SESSION['userName'] ?? 'System';

    try {
        // Optionally: log the reason for status change in another table or audit log

        // Update status in projects table
        $stmt = $conn->prepare("UPDATE projects 
                                SET PRO_STATUS = :status, UPDATED_AT = :updated_at 
                                WHERE PRO_ID = :project_id");
        $stmt->bindParam(':status', $newStatus);
        $stmt->bindParam(':updated_at', $updatedAt);
        $stmt->bindParam(':project_id', $projectId);
        $stmt->execute();

        // Optionally log reason in a separate table
        if (!empty($reason)) {
            $logStmt = $conn->prepare("INSERT INTO project_status_log (PRO_ID, NEW_STATUS, REASON, CHANGED_BY, CHANGED_AT)
                                       VALUES (:project_id, :status, :reason, :changed_by, :changed_at)");
            $logStmt->bindParam(':project_id', $projectId);
            $logStmt->bindParam(':status', $newStatus);
            $logStmt->bindParam(':reason', $reason);
            $logStmt->bindParam(':changed_by', $userName);
            $logStmt->bindParam(':changed_at', $updatedAt);
            $logStmt->execute();
        }

        // Redirect or return success
        header("Location: adminProj.php?status_update=success");
        exit();

    } catch (PDOException $e) {
        echo "Error updating project status: " . $e->getMessage();
    }

} else {
    echo "Invalid request.";
}
?>
