<?php
require_once 'db_config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'Next') {
            $languages = $_POST['languages'] ?? [];
            $otherLang = trim($_POST['other_language'] ?? '');

            if (!empty($otherLang)) {
                $languages[] = $otherLang;
            }

            if (!empty($languages)) {
                $_SESSION['application_data']['user_language'] = implode(',', $languages);
                $_SESSION['application_data']['current_step'] = 6;

                header('Location: location.php');
                exit;
            } else {
                $error = "Please select at least one language.";
            }
        } elseif ($_POST['action'] === 'Back') {
            $_SESSION['application_data']['current_step'] = 4;
            header('Location: education.php');
            exit;
        }
    }
}

// Retrieve selected languages
$selected_languages = explode(',', $_SESSION['application_data']['user_language'] ?? '');
$selected_languages = array_filter($selected_languages);

// Define language options
$languages = [
    'English', 'Spanish', 'French', 'German', 'Italian', 'Portuguese',
    'Chinese (Mandarin)', 'Japanese', 'Korean', 'Arabic', 'Russian',
    'Hindi','Thai', 'Vietnamese', 'Indonesian', 'Malay', 'Filipino'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application | Languages</title>
    <link rel="icon" href="../imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="authenticate.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .language-container {
            max-width: 700px;
            margin: auto;
            display: flex;
            flex-wrap: wrap;
            gap: 10px 50px;
        }
        .language-item {
            width: calc(50% - 25px);
            display: flex;
            align-items: center;
            font-size: 15px;
        }
        .language-item input {
            margin-right: 10px;
        }
        .other-language-input {
            margin-top: 10px;
        }
        #clearSelections {
            margin-top: 15px;
            padding: 5px 10px;
            font-size: 14px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="topvar">
    <div class="logo">
        <img src="../imgs/inl2Logo.png" alt="INTERLINKED Logo">
    </div>
</div>
<div class="container">
    <p class="subtext">5/8</p>
    <h1>What languages do you speak?</h1>
    <p class="subtext">Select all languages you can communicate in.</p>

    <?php if (isset($error)): ?>
        <div class="error-message" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" id="languageForm">
        <div class="language-container">
            <?php
            foreach ($languages as $language) {
                $checked = in_array($language, $selected_languages) ? 'checked' : '';
                echo "<label class='language-item'>
                        <input type='checkbox' name='languages[]' value='{$language}' {$checked}> {$language}
                    </label>";
            }

            // Handle 'Other' if previously selected
            $otherLanguage = '';
            foreach ($selected_languages as $lang) {
                if (!in_array($lang, $languages)) {
                    $otherLanguage = htmlspecialchars($lang);
                }
            }
            ?>
            <label class='language-item'>
                <input type='checkbox' id='otherCheck' name='languages[]' value='Other' <?php echo $otherLanguage ? 'checked' : ''; ?>>
                Other:
            </label>
            <input type='text' name='other_language' class='other-language-input' id='otherText' placeholder='Specify other language'
                   value="<?php echo $otherLanguage; ?>" style="width: 100%; max-width: 300px; display: <?php echo $otherLanguage ? 'block' : 'none'; ?>;">
        </div>
        <button type="button" id="clearSelections">✕ Clear selections</button>
    </form>
</div>
<div class="botvar">
    <div class="botconrow">
        <input type="submit" form="languageForm" name="action" value="Back">
        <input type="submit" form="languageForm" name="action" value="Next">
    </div>
</div>

<script>
    // Show/hide the Other input field
    document.getElementById('otherCheck').addEventListener('change', function () {
        document.getElementById('otherText').style.display = this.checked ? 'block' : 'none';
        if (!this.checked) {
            document.getElementById('otherText').value = '';
        }
    });

    // Clear selections
    document.getElementById("clearSelections").addEventListener("click", function () {
        const checkboxes = document.querySelectorAll("input[name='languages[]']");
        checkboxes.forEach(cb => cb.checked = false);
        document.getElementById('otherText').style.display = 'none';
        document.getElementById('otherText').value = '';
    });
</script>
</body>
</html>
