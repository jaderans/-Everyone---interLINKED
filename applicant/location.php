<?php
require_once 'db_config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'Next') {
            $location_data = [
                'country' => trim($_POST['country'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'timezone' => $_POST['timezone'] ?? '',
                'remote_work' => isset($_POST['remote_work']) ? 1 : 0
            ];

            if (!empty($location_data['country'])) {
                $_SESSION['application_data']['location_data'] = json_encode($location_data);
                $_SESSION['application_data']['user_country'] = $location_data['country'];
                $_SESSION['application_data']['current_step'] = 7;

                header('Location: overview.php');
                exit;
            } else {
                $error = "Please select your country.";
            }
        } elseif ($_POST['action'] === 'Back') {
            $_SESSION['application_data']['current_step'] = 5;
            header('Location: language.php');
            exit;
        }
    }
}

// Decode existing location data
$location_data = [];
if (!empty($_SESSION['application_data']['location_data'])) {
    $location_data = json_decode($_SESSION['application_data']['location_data'], true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Application | Location</title>
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
    <p class="subtext">6/8</p>
    <h1>Where are you located?</h1>
    <p class="subtext">This helps us match you with relevant opportunities.</p>

    <?php if (isset($error)): ?>
        <div class="error-message" style="color: red; margin-bottom: 15px;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" id="locationForm">
        <div class="category-specialty-container">
            <div style="width: 100%; max-width: 600px;">
                <div class="form-group">
                    <div>
                        <label for="country">Country *</label>
                        <select name="country" id="country" required>
                            <option value="">Select Country</option>
                            <?php
                            $countries = [
                                'United States', 'Canada', 'United Kingdom', 'Australia', 'Germany',
                                'France', 'Italy', 'Spain', 'Netherlands', 'Sweden', 'Norway',
                                'Denmark', 'Finland', 'Switzerland', 'Austria', 'Belgium',
                                'Portugal', 'Ireland', 'Poland', 'Czech Republic', 'Hungary',
                                'Greece', 'Turkey', 'Russia', 'Ukraine', 'Romania', 'Bulgaria',
                                'Croatia', 'Serbia', 'Slovenia', 'Slovakia', 'Lithuania',
                                'Latvia', 'Estonia', 'Japan', 'South Korea', 'China', 'India',
                                'Singapore', 'Malaysia', 'Thailand', 'Philippines', 'Indonesia',
                                'Vietnam', 'Taiwan', 'Hong Kong', 'Brazil', 'Argentina',
                                'Chile', 'Colombia', 'Mexico', 'Peru', 'Uruguay', 'Venezuela',
                                'South Africa', 'Nigeria', 'Kenya', 'Egypt', 'Morocco',
                                'Israel', 'UAE', 'Saudi Arabia', 'Qatar', 'Kuwait', 'Other'
                            ];

                            foreach ($countries as $country) {
                                $selected = (isset($location_data['country']) && $location_data['country'] === $country) ? 'selected' : '';
                                echo "<option value='{$country}' {$selected}>{$country}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label for="city">City</label>
                        <input type="text" name="city" id="city"
                               value="<?php echo htmlspecialchars($location_data['city'] ?? ''); ?>"
                               placeholder="Enter your city">
                    </div>
                </div>

                <div class="bio-field">
                    <label for="timezone">Timezone</label>
                    <select name="timezone" id="timezone">
                        <option value="">Select Timezone</option>
                        <?php
                        $timezones = [
                            'UTC-12' => 'UTC-12:00',
                            'UTC-11' => 'UTC-11:00',
                            'UTC-10' => 'UTC-10:00',
                            'UTC-9' => 'UTC-09:00',
                            'UTC-8' => 'UTC-08:00',
                            'UTC-7' => 'UTC-07:00',
                            'UTC-6' => 'UTC-06:00',
                            'UTC-5' => 'UTC-05:00',
                            'UTC-4' => 'UTC-04:00',
                            'UTC-3' => 'UTC-03:00',
                            'UTC-2' => 'UTC-02:00',
                            'UTC-1' => 'UTC-01:00',
                            'UTC+0' => 'UTC+00:00',
                            'UTC+1' => 'UTC+01:00',
                            'UTC+2' => 'UTC+02:00',
                            'UTC+3' => 'UTC+03:00',
                            'UTC+4' => 'UTC+04:00',
                            'UTC+5' => 'UTC+05:00',
                            'UTC+6' => 'UTC+06:00',
                            'UTC+7' => 'UTC+07:00',
                            'UTC+8' => 'UTC+08:00',
                            'UTC+9' => 'UTC+09:00',
                            'UTC+10' => 'UTC+10:00',
                            'UTC+11' => 'UTC+11:00',
                            'UTC+12' => 'UTC+12:00'
                        ];

                        foreach ($timezones as $value => $display) {
                            $selected = (isset($location_data['timezone']) && $location_data['timezone'] === $value) ? 'selected' : '';
                            echo "<option value='{$value}' {$selected}>{$display}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div style="margin-top: 20px;">
                    <label>
                        <input type="checkbox" name="remote_work" id="remote_work"
                            <?php echo (isset($location_data['remote_work']) && $location_data['remote_work']) ? 'checked' : ''; ?>>
                        I am open to remote work
                    </label>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="botvar">
    <div class="botconrow">
        <input type="submit" form="locationForm" name="action" value="Back">
        <input type="submit" form="locationForm" name="action" value="Next">
    </div>
</div>

<script>
    // Style the select elements
    const selectElements = document.querySelectorAll('select');
    selectElements.forEach(select => {
        select.style.cssText = `
        width: 120%;
        margin-bottom: 5px;
        padding: 9px;
        border: 2px solid #4a8c8a;
        border-radius: 10px;
        box-sizing: border-box;
        background: white;
    `;
    });

    // Special styling for timezone select
    document.getElementById('timezone').style.width = '100%';
</script>
</body>
</html>