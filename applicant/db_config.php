<?php
// Database configuration
function connectToDatabase($port) {
    $host = 'localhost';
    $dbname = 'finals';
    $username = 'root';
    $password = '';

    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Initialize connections
$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);

// Session management
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize session data if not exists
if (!isset($_SESSION['application_data'])) {
    $_SESSION['application_data'] = [
        'user_categ' => '',
        'user_skills' => '',
        'user_title' => '',
        'employment_data' => '',
        'education_data' => '',
        'user_language' => '',
        'location_data' => '',
        'user_bio' => '',
        'current_step' => 1
    ];
}

function generateUserId() {
    global $slave_con;

    // Get the last user ID from database
    $stmt = $slave_con->prepare("SELECT USER_ID FROM user ORDER BY USER_ID DESC LIMIT 1");
    $stmt->execute();
    $lastUser = $stmt->fetch();

    if ($lastUser) {
        // Extract the number part and increment
        $lastNumber = intval(substr($lastUser['USER_ID'], -5));
        $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '00001';
    }

    return '25-INTL-' . $newNumber;
}


// Navigation helper
function getNextPage($current_step) {
    $pages = [
        1 => 'title.php',
        2 => 'employment.php',
        3 => 'education.php',
        4 => 'language.php',
        5 => 'location.php',
        6 => 'overview.php',
        7 => 'submit.php',
        8 => 'complete.php'
    ];

    return isset($pages[$current_step]) ? $pages[$current_step] : 'categories.php';
}

function getPreviousPage($current_step) {
    $pages = [
        2 => 'categories.php',
        3 => 'title.php',
        4 => 'employment.php',
        5 => 'education.php',
        6 => 'language.php',
        7 => 'location.php',
        8 => 'overview.php'
    ];

    return isset($pages[$current_step]) ? $pages[$current_step] : 'categories.php';
}
?>