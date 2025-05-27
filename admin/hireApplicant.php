<?php
session_start();
include('interlinkedDB.php');

$conn = connectToDatabase();

// Check if the user ID is provided
session_start();
include('interlinkedDB.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];

    try {
        $conn = connectToDatabase();
        $stmt = $conn->prepare("UPDATE user SET USER_TYPE = 'Freelancer' WHERE USER_ID = ?");
        $stmt->execute([$userId]);

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        error_log("Hire error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}


// Redirect back to admin dashboard
header("Location: adminDashboard.php"); // Change to your actual admin page
exit;
