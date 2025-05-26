<?php
require_once 'db_config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'Next') {
            $employment_data = [
                'company' => trim($_POST['company'] ?? ''),
                'position' => trim($_POST['position'] ?? ''),
                'start_date' => $_POST['start_date'] ?? '',
                'end_date' => $_POST['end_date'] ?? '',
                'description' => trim($_POST['description'] ?? ''),
                'is_current' => isset($_POST['is_current']) ? 1 : 0
            ];

            $_SESSION['application_data']['employment_data'] = json_encode($employment_data);
            $_SESSION['application_data']['current_step'] = 4;

            header('Location: education.php');
            exit;
        } elseif ($_POST['action'] === 'Back') {
            $_SESSION['application_data']['current_step'] = 2;
            header('Location: title.php');
            exit;
        }
    }
}

// Decode existing employment data
$employment_data = [];
if (!empty($_SESSION['application_data']['employment_data'])) {
    $employment_data = json_decode($_SESSION['application_data']['employment_data'], true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application | Employment</title>
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
    <p class="subtext">3/8</p>
    <h1>Tell us about your work experience.</h1>
    <p class="subtext">Share your previous jobs, roles, and relevant experiences.</p>

    <form method="POST" id="employmentForm">
        <div class="category-specialty-container">
            <div style="width: 100%; max-width: 600px;">
                <div class="form-group">
                    <div>
                        <label for="company">Company Name</label>
                        <input type="text" name="company" id="company"
                               value="<?php echo htmlspecialchars($employment_data['company'] ?? ''); ?>"
                               placeholder="Enter company name">
                    </div>
                    <div>
                        <label for="position">Position</label>
                        <input type="text" name="position" id="position"
                               value="<?php echo htmlspecialchars($employment_data['position'] ?? ''); ?>"
                               placeholder="Enter your position">
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="start_date">Start Date</label>
                        <input type="month" name="start_date" id="start_date"
                               value="<?php echo htmlspecialchars($employment_data['start_date'] ?? ''); ?>">
                    </div>
                    <div>
                        <label for="end_date">End Date</label>
                        <input type="month" name="end_date" id="end_date"
                               value="<?php echo htmlspecialchars($employment_data['end_date'] ?? ''); ?>"
                            <?php echo (isset($employment_data['is_current']) && $employment_data['is_current']) ? 'disabled' : ''; ?>>
                    </div>
                </div>

                <div style="margin-bottom: 15px;">
                    <label>
                        <input type="checkbox" name="is_current" id="is_current"
                            <?php echo (isset($employment_data['is_current']) && $employment_data['is_current']) ? 'checked' : ''; ?>>
                        I currently work here
                    </label>
                </div>

                <div class="bio-field">
                    <label for="description">Job Description</label>
                    <textarea name="description" id="description" class="jobTitle"
                              placeholder="Describe your responsibilities and achievements..."
                              rows="5"><?php echo htmlspecialchars($employment_data['description'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="botvar">
    <div class="botconrow">
        <input type="submit" form="employmentForm" name="action" value="Back">
        <input type="submit" form="employmentForm" name="action" value="Next">
    </div>
</div>

<script>
    document.getElementById('is_current').addEventListener('change', function() {
        const endDateInput = document.getElementById('end_date');
        if (this.checked) {
            endDateInput.disabled = true;
            endDateInput.value = '';
        } else {
            endDateInput.disabled = false;
        }
    });
</script>
</body>
</html>