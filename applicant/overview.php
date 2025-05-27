<?php
require_once 'db_config.php';

// Make sure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure session data array exists
if (!isset($_SESSION['application_data'])) {
    $_SESSION['application_data'] = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'Next') {
            $bio = trim($_POST['bio'] ?? '');

            if (!empty($bio)) {
                $_SESSION['application_data']['user_bio'] = $bio;
                $_SESSION['application_data']['current_step'] = 8;

                header('Location: submit.php');
                exit;
            } else {
                $error = "Please write a short bio about yourself.";
            }
        } elseif ($_POST['action'] === 'Back') {
            $_SESSION['application_data']['current_step'] = 6;
            header('Location: location.php');
            exit;
        }
    }
}

// Safe access to the bio
$userBio = $_SESSION['application_data']['user_bio'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application | Bio</title>
    <link rel="icon" href="../imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="authenticate.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body>
<div class="topvar">
    <div class="logo">
        <img src="../imgs/inl2Logo.png" alt="INTERLINKED Logo">
    </div>
</div>
<div class="container">
    <p class="subtext">7/8</p>
    <h1>Write a short bio for your profile.</h1>
    <p class="subtext">Tell us a bit about yourself.</p>

    <?php if (isset($error)): ?>
        <div class="error-message" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" id="bioForm">
        <div class="category-specialty-container">
            <div class="bio-field">
                <label for="bio">Bio for your profile</label>
                <textarea id="bio" name="bio" class="jobTitle"
                          placeholder="Write something about yourself..."
                          maxlength="450" rows="10" required><?php echo htmlspecialchars($userBio); ?></textarea>
                <div style="font-size: 12px; color: #888; margin-top: 5px;">
                    <span id="charCount">0</span>/450 characters
                </div>
            </div>
        </div>
    </form>
</div>
<div class="botvar">
    <div class="botconrow">
        <input type="submit" form="bioForm" name="action" value="Back">
        <input type="submit" form="bioForm" name="action" value="Next">
    </div>
</div>

<script>
    // Character counter
    const bioTextarea = document.getElementById('bio');
    const charCount = document.getElementById('charCount');

    function updateCharCount() {
        const currentLength = bioTextarea.value.length;
        charCount.textContent = currentLength;

        if (currentLength > 400) {
            charCount.style.color = '#ff6b6b';
        } else if (currentLength > 350) {
            charCount.style.color = '#ffa500';
        } else {
            charCount.style.color = '#888';
        }
    }

    bioTextarea.addEventListener('input', updateCharCount);
    bioTextarea.addEventListener('keyup', updateCharCount);

    // Initial count
    updateCharCount();
</script>
</body>
</html>