<?php
session_start();
include('interlinkedDB.php');

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Access denied. Please <a href='logIn.php'>log in</a>.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = connectToDatabase();

    // Validate project ID
    $projectId = $_POST['project_id'] ?? null;
    if (!$projectId || !is_numeric($projectId)) {
        die("Invalid project ID.");
    }

    // Delete project
    $stmt = $conn->prepare("DELETE FROM projects WHERE PRO_ID = ?");
    $stmt->execute([$projectId]);

    if ($stmt->rowCount() > 0) {
        echo "<p>Project deleted successfully.</p>";
    } else {
        echo "<p>Project not found or already deleted.</p>";
    }

    echo "<p><a href='adminProj.php'>Return to Project List</a></p>";
} else {
    // Prevent direct GET access
    echo "<p>Invalid request method.</p>";
    echo "<p><a href='adminProj.php'>Back to Project List</a></p>";
}
?>
