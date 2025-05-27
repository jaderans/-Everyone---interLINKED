<?php
session_start();
include('interlinkedDB.php');

// Connect to master DB for write operations
$master_con = connectToDatabase(3306);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['userId'])) {
    $userId = $_POST['userId'];

    // Validate userId format if necessary (e.g., length, pattern)

    // Prepare update statement to change USER_TYPE and USER_STATUS
    $query = "UPDATE user SET USER_TYPE = 'Freelancer', USER_STATUS = 'ACTIVE' 
              WHERE USER_ID = :userId AND USER_TYPE = 'Applicant' AND (USER_STATUS = 'PENDING' OR USER_STATUS IS NULL)";

    $stmt = $master_con->prepare($query);
    $stmt->execute(['userId' => $userId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Applicant hired successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to hire applicant. User may not exist or is not eligible.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
