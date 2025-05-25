<?php
require_once 'db_config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'Next') {
            $education_data = [
                'school' => trim($_POST['school'] ?? ''),
                'degree' => trim($_POST['degree'] ?? ''),
                'field_of_study' => trim($_POST['field_of_study'] ?? ''),
                'start_year' => $_POST['start_year'] ?? '',
                'end_year' => $_POST['end_year'] ?? '',
                'grade' => trim($_POST['grade'] ?? ''),
                'is_current' => isset($_POST['is_current']) ? 1 : 0,
                'country' => trim($_POST['country'] ?? '')
            ];

            $_SESSION['application_data']['education_data'] = json_encode($education_data);
            $_SESSION['application_data']['current_step'] = 5;

            header('Location: language.php');
            exit;
        } elseif ($_POST['action'] === 'Back') {
            $_SESSION['application_data']['current_step'] = 3;
            header('Location: employment.php');
            exit;
        }
    }
}

// Decode existing education data
$education_data = [];
if (!empty($_SESSION['application_data']['education_data'])) {
    $education_data = json_decode($_SESSION['application_data']['education_data'], true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application | Education</title>
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
    <p class="subtext">4/8</p>
    <h1>Tell us about your education.</h1>
    <p class="subtext">Share your educational background and qualifications.</p>

    <form method="POST" id="educationForm">
        <div class="category-specialty-container">
            <div style="width: 100%; max-width: 600px;">

                <div class="form-group">
                    <div>
                        <label for="countrySelect">Country</label>
                        <select name="country" id="countrySelect">
                            <option value="">Select Country</option>
                            <!-- Add more countries as needed -->
                            <?php
                            $countries = ['United States', 'Canada', 'United Kingdom', 'Australia', 'Germany', 'Philippines', 'India'];
                            foreach ($countries as $country) {
                                $selected = (isset($education_data['country']) && $education_data['country'] === $country) ? 'selected' : '';
                                echo "<option value=\"$country\" $selected>$country</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="schoolSelect">School/University</label>
                        <select id="schoolSelect">
                            <option value="">Select a country first</option>
                        </select>
                        <input type="hidden" name="school" id="schoolHidden" value="<?php echo htmlspecialchars($education_data['school'] ?? ''); ?>">
                    </div>
                </div>

                <div class="bio-field">

                    <div>
                        <label for="degree">Degree</label>
                        <select name="degree" id="degree">
                            <option value="">Select Degree</option>
                            <?php
                            $degrees = ['High School', 'Associate', 'Bachelor', 'Master', 'PhD', 'Other'];
                            foreach ($degrees as $degree) {
                                $selected = (isset($education_data['degree']) && $education_data['degree'] === $degree) ? 'selected' : '';
                                echo "<option value=\"$degree\" $selected>$degree</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <label for="field_of_study">Field of Study</label>
                    <input type="text" name="field_of_study" id="field_of_study" class="jobTitle"
                           value="<?php echo htmlspecialchars($education_data['field_of_study'] ?? ''); ?>"
                           placeholder="e.g. Computer Science, Graphic Design, Art">
                </div>


                <div class="form-group">
                    <div>
                        <label for="start_year">Start Year</label>
                        <input type="number" name="start_year" id="start_year"
                               min="1980" max="<?php echo date('Y'); ?>"
                               value="<?php echo htmlspecialchars($education_data['start_year'] ?? ''); ?>"
                               placeholder="2020">
                    </div>
                    <div>
                        <label for="end_year">End Year</label>
                        <input type="number" name="end_year" id="end_year"
                               min="1980" max="<?php echo date('Y') + 10; ?>"
                               value="<?php echo htmlspecialchars($education_data['end_year'] ?? ''); ?>"
                               placeholder="2024"
                            <?php echo (isset($education_data['is_current']) && $education_data['is_current']) ? 'disabled' : ''; ?>>
                    </div>
                </div>

                <div style="margin-bottom: 15px;">
                    <label>
                        <input type="checkbox" name="is_current" id="is_current"
                            <?php echo (isset($education_data['is_current']) && $education_data['is_current']) ? 'checked' : ''; ?>>
                        I am currently studying here
                    </label>
                </div>

                <div class="bio-field">
                    <label for="grade">Grade/GPA (Optional)</label>
                    <input type="text" name="grade" id="grade" class="jobTitle"
                           value="<?php echo htmlspecialchars($education_data['grade'] ?? ''); ?>"
                           placeholder="e.g. 3.8/4.0, First Class, A">
                </div>
            </div>
        </div>
    </form>
</div>
<div class="botvar">
    <div class="botconrow">
        <input type="submit" form="educationForm" name="action" value="Back">
        <input type="submit" form="educationForm" name="action" value="Next">
    </div>
</div>
<script>
    // Enable/disable end year based on checkbox
    document.getElementById('is_current').addEventListener('change', function () {
        const endYearInput = document.getElementById('end_year');
        endYearInput.disabled = this.checked;
        if (this.checked) endYearInput.value = '';
    });

    const countrySelect = document.getElementById('countrySelect');
    const schoolSelect = document.getElementById('schoolSelect');
    const schoolHidden = document.getElementById('schoolHidden');

    let universitiesByCountry = {};

    // Fetch university data from universities.php
    fetch('worldUniversities.php')
        .then(response => response.json())
        .then(data => {
            universitiesByCountry = data;

            // Populate universities if a country is already selected (edit mode)
            const selectedCountry = countrySelect.value;
            if (selectedCountry && universitiesByCountry[selectedCountry]) {
                populateUniversities(selectedCountry);
            }
        })
        .catch(error => {
            console.error('Error fetching universities:', error);
        });

    countrySelect.addEventListener('change', function () {
        const selectedCountry = this.value;
        populateUniversities(selectedCountry);
    });

    function populateUniversities(country) {
        const schools = universitiesByCountry[country] || [];
        schoolSelect.innerHTML = `<option value="">Select University</option>`;

        schools.forEach(name => {
            const option = document.createElement('option');
            option.value = name;
            option.textContent = name;
            schoolSelect.appendChild(option);
        });

        if (schoolHidden.value) {
            schoolSelect.value = schoolHidden.value;
        }
    }

    schoolSelect.addEventListener('change', function () {
        schoolHidden.value = this.value;
    });

    // Degree dropdown styling
    document.getElementById('degree').style.cssText = `
        width: 100%;
        margin-bottom: 5px;
        padding: 9px;
        border: 2px solid #4a8c8a;
        border-radius: 10px;
        box-sizing: border-box;
        background: white;
    `;
</script>

</body>
</html>
