<?php
// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin_data'])) {
    header("Location: ../loginSignup/logIn.php");
    exit();
}
