<?php
require_once 'db_config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'Next') {
            $title = trim($_POST['title'] ?? '');

            if (!empty($title)) {
                $_SESSION['application_data']['user_title'] = $title;
                $_SESSION['application_data']['current_step'] = 3;

                header('Location: employment.php');
                exit;
            } else {
                $error = "Please enter your professional title.";
            }
        } elseif ($_POST['action'] === 'Back') {
            $_SESSION['application_data']['current_step'] = 1;
            header('Location: categories.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application | Title</title>
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
    <p class="subtext">2/8</p>
    <h1>What title best describes you?</h1>
    <p class="subtext">Choose a title that reflects your specialty</p>

    <?php if (isset($error)): ?>
        <div class="error-message" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="category-specialty-container">
            <div>
                <label for="title">Your Professional Role</label><br>
                <input class="jobTitle" type="text" name="title" id="title"
                       placeholder="Professional Title"
                       value="<?php echo isset($_SESSION['application_data']['user_title']) ? htmlspecialchars($_SESSION['application_data']['user_title']) : ''; ?>"
                       required>
            </div>
        </div>
    </form>
</div>
<div class="botvar">
    <div class="botconrow">
        <input type="submit" form="titleForm" name="action" value="Back">
        <input type="submit" form="titleForm" name="action" value="Next">
    </div>
</div>

<script>
    // Add form ID to inputs
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        form.id = 'titleForm';
    });
</script>
</body>
</html>