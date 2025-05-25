<?php
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'Submit') {
            try {
                // Handle profile picture upload
                $profileImage = null;
                if (!empty($_FILES['profile_picture']['name'])) {
                    $targetDir = "uploads/";
                    if (!file_exists($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }

                    $fileExtension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

                    if (in_array($fileExtension, $allowedExtensions)) {
                        $newFileName = 'profile_' . uniqid() . '.' . $fileExtension;
                        $profileImage = $targetDir . $newFileName;

                        if (!move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $profileImage)) {
                            throw new Exception("Failed to upload profile picture");
                        }
                    } else {
                        throw new Exception("Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.");
                    }
                }

                // Collect form data
                $email = htmlspecialchars($_POST['email']);
                $firstName = htmlspecialchars($_POST['firstName']);
                $lastName = htmlspecialchars($_POST['lastName']);
                $birthday = htmlspecialchars($_POST['birthday']);
                $phone = htmlspecialchars($_POST['phone']);
                $userType = htmlspecialchars($_POST['userType']);
                $userName = htmlspecialchars($_POST['userName']);
                $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

                $userCategory = htmlspecialchars($_POST['user_categ']);
                $userSkills = htmlspecialchars($_POST['user_skills']);
                $userTitle = htmlspecialchars($_POST['user_title']);
                $userLanguage = htmlspecialchars($_POST['user_language']);
                $userBio = htmlspecialchars($_POST['user_bio']);

                $country = htmlspecialchars($_POST['country']);
                $city = htmlspecialchars($_POST['city'] ?? '');
                $timezone = htmlspecialchars($_POST['timezone'] ?? '');
                $remoteWork = isset($_POST['remote_work']) ? 1 : 0;

                $empCompany = htmlspecialchars($_POST['emp_company'] ?? '');
                $empPosition = htmlspecialchars($_POST['emp_position'] ?? '');
                $empStartDate = htmlspecialchars($_POST['emp_start_date'] ?? '');
                $empEndDate = htmlspecialchars($_POST['emp_end_date'] ?? '');
                $empDescription = htmlspecialchars($_POST['emp_description'] ?? '');
                $empIsCurrent = isset($_POST['emp_is_current']) ? 1 : 0;

                $edSchool = htmlspecialchars($_POST['ed_school'] ?? '');
                $edDegree = htmlspecialchars($_POST['ed_degree'] ?? '');
                $edField = htmlspecialchars($_POST['ed_field_of_study'] ?? '');
                $edStartYear = htmlspecialchars($_POST['ed_start_year'] ?? '');
                $edEndYear = htmlspecialchars($_POST['ed_end_year'] ?? '');
                $edGrade = htmlspecialchars($_POST['ed_grade'] ?? '');
                $edIsCurrent = isset($_POST['ed_is_current']) ? 1 : 0;

                // Begin transaction
                $master_con->beginTransaction();

                $stmt = $master_con->prepare("
                    INSERT INTO user (
                        USER_ID, USER_EMAIL, USER_TYPE, USER_FSTNAME, USER_LSTNAME, 
                        USER_BIRTHDAY, USER_CONTACT, USER_PASSWORD, USER_NAME, 
                        USER_COUNTRY, USER_IMG, USER_CATEG, USER_SKILLS, USER_TITLE, 
                        USER_LANGUAGE, USER_BIO, USER_TRIES, USER_STATUS, EMP_ID, ED_ID
                    ) VALUES (
                        :user_id, :email, :type, :fname, :lname, 
                        :birthday, :contact, :password, :username, 
                        :country, :img, :categ, :skills, :title, 
                        :language, :bio, :tries, :status, :emp_id, :ed_id
                    )
                ");

                $stmt->execute([
                    ':user_id' => $user_id,
                    ':email' => $email,
                    ':type' => $userType,
                    ':fname' => $firstName,
                    ':lname' => $lastName,
                    ':birthday' => $birthday,
                    ':contact' => $phone,
                    ':password' => $password,
                    ':username' => $userName,
                    ':country' => $country,
                    ':img' => $profileImage,
                    ':categ' => $userCategory,
                    ':skills' => $userSkills,
                    ':title' => $userTitle,
                    ':language' => $userLanguage,
                    ':bio' => $userBio,
                    ':tries' => 0,
                    ':status' => 'pending',
                    ':emp_id' => !empty($empCompany) ? $user_id . '_EMP' : null,
                    ':ed_id' => !empty($edSchool) ? $user_id . '_ED' : null
                ]);

                if (!empty($empCompany)) {
                    $emp_stmt = $master_con->prepare("
                        INSERT INTO employment (
                            EMP_ID, USER_ID, company, position, start_date, 
                            end_date, description, is_current
                        ) VALUES (
                            :emp_id, :user_id, :company, :position, :start_date, 
                            :end_date, :description, :is_current
                        )
                    ");

                    $emp_stmt->execute([
                        ':emp_id' => $user_id . '_EMP',
                        ':user_id' => $user_id,
                        ':company' => $empCompany,
                        ':position' => $empPosition,
                        ':start_date' => $empStartDate,
                        ':end_date' => $empIsCurrent ? null : $empEndDate,
                        ':description' => $empDescription,
                        ':is_current' => $empIsCurrent
                    ]);
                }

                if (!empty($edSchool)) {
                    $ed_stmt = $master_con->prepare("
                        INSERT INTO education (
                            ED_ID, USER_ID, school, degree, field_of_study, 
                            start_year, end_year, grade, is_current
                        ) VALUES (
                            :ed_id, :user_id, :school, :degree, :field_of_study, 
                            :start_year, :end_year, :grade, :is_current
                        )
                    ");

                    $ed_stmt->execute([
                        ':ed_id' => $user_id . '_ED',
                        ':user_id' => $user_id,
                        ':school' => $edSchool,
                        ':degree' => $edDegree,
                        ':field_of_study' => $edField,
                        ':start_year' => $edStartYear,
                        ':end_year' => $edIsCurrent ? null : $edEndYear,
                        ':grade' => $edGrade,
                        ':is_current' => $edIsCurrent
                    ]);
                }

                $master_con->commit();
                session_unset();

                $_SESSION['success_message'] = "Application submitted successfully! Your ID is: " . $user_id;
                header('Location: success.php');
                exit;

            } catch (Exception $e) {
                if ($master_con->inTransaction()) {
                    $master_con->rollback();
                }
                $error = "Error submitting application: " . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'Back') {
            header('Location: FormSignUser.php');
            exit;
        }
    }
}

// Session data fallback
$user_data = $_SESSION['application_data'] ?? [];
$employment_data = json_decode($user_data['employment_data'] ?? '{}', true);
$education_data = json_decode($user_data['education_data'] ?? '{}', true);
$location_data = json_decode($user_data['location_data'] ?? '{}', true);

// Default values
$userType = $_SESSION['type'] ?? $user_data['user_type'] ?? '';
$userName = $_SESSION['userName'] ?? $user_data['username'] ?? '';
$email = $_SESSION['email'] ?? $user_data['email'] ?? '';
$firstName = $_SESSION['firstName'] ?? $user_data['first_name'] ?? '';
$lastName = $_SESSION['lastName'] ?? $user_data['last_name'] ?? '';
$birthday = $_SESSION['birthday'] ?? $user_data['birthday'] ?? '';
$phone = $_SESSION['phone'] ?? $user_data['phone'] ?? '';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application | Review & Submit</title>
    <link rel="icon" href="../imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="authenticate.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f1f3f5;
            color: #333;
            line-height: 1.6;
            padding-top: 80px;
            padding-bottom: 100px;
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
    <div class="header-section">
        <h1>Review & Submit Application</h1>
        <p class="subtext">Please review all your information and make any necessary changes before submitting</p>
    </div>

    <div class="step-indicator">
        Step 8 of 8 - Final Review
    </div>

    <?php if (isset($error)): ?>
        <div class="form-container">
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
            </div>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="form-container">
        <!-- Personal Information Section -->
        <div class="form-section">
            <h3><i class="fas fa-user"></i> Personal Information</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="userName">Username <span class="required">*</span></label>
                    <input type="text" id="userName" name="userName" value="<?= htmlspecialchars($userName) ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="userType">User Type</label>
                    <input type="text" id="userType" name="userType" value="<?= htmlspecialchars($userType) ?>" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="firstName">First Name <span class="required">*</span></label>
                    <input type="text" id="firstName" name="firstName" value="<?= htmlspecialchars($firstName) ?>" required pattern="[A-Za-z\s]+">
                </div>
                <div class="form-group">
                    <label for="lastName">Last Name <span class="required">*</span></label>
                    <input type="text" id="lastName" name="lastName" value="<?= htmlspecialchars($lastName) ?>" required pattern="[A-Za-z\s]+">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number <span class="required">*</span></label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" required pattern="[0-9]{10,15}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="birthday">Date of Birth <span class="required">*</span></label>
                    <input type="date" id="birthday" name="birthday" value="<?= htmlspecialchars($birthday) ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" required minlength="6" placeholder="Enter your password">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group full-width">
                    <label for="profile_picture">Profile Picture</label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*" class="file-upload-input">
                        <label for="profile_picture" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Choose Profile Picture (JPG, PNG, GIF)</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Professional Information Section -->
        <div class="form-section">
            <h3><i class="fas fa-briefcase"></i> Professional Information</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="user_categ">Category <span class="required">*</span></label>
                    <input type="text" id="user_categ" name="user_categ" value="<?= htmlspecialchars($user_data['user_categ'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="user_title">Professional Title <span class="required">*</span></label>
                    <input type="text" id="user_title" name="user_title" value="<?= htmlspecialchars($user_data['user_title'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group full-width">
                    <label for="user_skills">Skills <span class="required">*</span></label>
                    <textarea id="user_skills" name="user_skills" rows="3" required placeholder="List your skills separated by commas"><?= htmlspecialchars($user_data['user_skills'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="user_language">Languages <span class="required">*</span></label>
                    <input type="text" id="user_language" name="user_language" value="<?= htmlspecialchars($user_data['user_language'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group full-width">
                    <label for="user_bio">Professional Bio <span class="required">*</span></label>
                    <textarea id="user_bio" name="user_bio" rows="4" required placeholder="Tell us about your professional background and experience"><?= htmlspecialchars($user_data['user_bio'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Location Information Section -->
        <div class="form-section">
            <h3><i class="fas fa-map-marker-alt"></i> Location Information</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="country">Country <span class="required">*</span></label>
                    <input type="text" id="country" name="country" value="<?= htmlspecialchars($location_data['country'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?= htmlspecialchars($location_data['city'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="timezone">Timezone</label>
                    <input type="text" id="timezone" name="timezone" value="<?= htmlspecialchars($location_data['timezone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="remote_work" name="remote_work" <?= ($location_data['remote_work'] ?? false) ? 'checked' : '' ?>>
                        <label for="remote_work">Available for remote work</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employment Information Section -->
        <div class="form-section">
            <h3><i class="fas fa-building"></i> Employment Information</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="emp_company">Company Name</label>
                    <input type="text" id="emp_company" name="emp_company" value="<?= htmlspecialchars($employment_data['company'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="emp_position">Position</label>
                    <input type="text" id="emp_position" name="emp_position" value="<?= htmlspecialchars($employment_data['position'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="emp_start_date">Start Date</label>
                    <input type="date" id="emp_start_date" name="emp_start_date" value="<?= htmlspecialchars($employment_data['start_date'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="emp_end_date">End Date</label>
                    <input type="date" id="emp_end_date" name="emp_end_date" value="<?= htmlspecialchars($employment_data['end_date'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group full-width">
                    <label for="emp_description">Job Description</label>
                    <textarea id="emp_description" name="emp_description" rows="3" placeholder="Describe your role and responsibilities"><?= htmlspecialchars($employment_data['description'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="emp_is_current" name="emp_is_current" <?= ($employment_data['is_current'] ?? false) ? 'checked' : '' ?>>
                        <label for="emp_is_current">Currently working here</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Education Information Section -->
        <div class="form-section">
            <h3><i class="fas fa-building"></i> Education Information</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="emp_company">Company Name</label>
                    <input type="text" id="emp_company" name="emp_company" value="<?= htmlspecialchars($employment_data['company'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="emp_position">Position</label>
                    <input type="text" id="emp_position" name="emp_position" value="<?= htmlspecialchars($employment_data['position'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="emp_start_date">Start Date</label>
                    <input type="date" id="emp_start_date" name="emp_start_date" value="<?= htmlspecialchars($employment_data['start_date'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="emp_end_date">End Date</label>
                    <input type="date" id="emp_end_date" name="emp_end_date" value="<?= htmlspecialchars($employment_data['end_date'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group full-width">
                    <label for="emp_description">Job Description</label>
                    <textarea id="emp_description" name="emp_description" rows="3" placeholder="Describe your role and responsibilities"><?= htmlspecialchars($employment_data['description'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="emp_is_current" name="emp_is_current" <?= ($employment_data['is_current'] ?? false) ? 'checked' : '' ?>>
                        <label for="emp_is_current">Currently working here</label>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
</body>
</html>
