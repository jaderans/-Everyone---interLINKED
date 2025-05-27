<?php
session_start();
require_once 'db_config.php';

$user_id = generateUserId();
function generateUserId() {
    global $master_con; // Use master_con instead of slave_con

    $stmt = $master_con->prepare("
        SELECT USER_ID 
        FROM user 
        ORDER BY CAST(SUBSTRING(USER_ID, 10) AS UNSIGNED) DESC 
        LIMIT 1
    ");
    $stmt->execute();
    $lastUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($lastUser) {
        $lastNumber = intval(substr($lastUser['USER_ID'], -5));
        $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    } else {
        $newNumber = '00001';
    }

    return '25-INTL-' . $newNumber;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'Submit') {
            try {
                // Collect form data with proper sanitization
                $email = htmlspecialchars($_POST['email']);
                $firstName = htmlspecialchars($_POST['firstName']);
                $lastName = htmlspecialchars($_POST['lastName']);
                $birthday = htmlspecialchars($_POST['birthday']);
                $phone = htmlspecialchars($_POST['phone']);
                $userType = htmlspecialchars($_POST['userType']);
                $userName = htmlspecialchars($_POST['userName']);
                $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

                // Professional information
                $userCategory = htmlspecialchars($_POST['user_categ']);
                $userSkills = htmlspecialchars($_POST['user_skills']);
                $userTitle = htmlspecialchars($_POST['user_title']);
                $userLanguage = htmlspecialchars($_POST['user_language']);
                $userBio = htmlspecialchars($_POST['user_bio']);

                // Location information
                $country = htmlspecialchars($_POST['country']);
                $city = htmlspecialchars($_POST['city'] ?? '');
                $timezone = htmlspecialchars($_POST['timezone'] ?? '');
                $remoteWork = isset($_POST['remote_work']) ? 1 : 0;

                // Employment information
                $empCompany = htmlspecialchars($_POST['emp_company'] ?? '');
                $empPosition = htmlspecialchars($_POST['emp_position'] ?? '');
                $empStartDate = htmlspecialchars($_POST['emp_start_date'] ?? '');
                $empEndDate = htmlspecialchars($_POST['emp_end_date'] ?? '');
                $empDescription = htmlspecialchars($_POST['emp_description'] ?? '');
                $empIsCurrent = isset($_POST['emp_is_current']) ? 1 : 0;

                // Education information
                $edSchool = htmlspecialchars($_POST['ed_school'] ?? '');
                $edDegree = htmlspecialchars($_POST['ed_degree'] ?? '');
                $edField = htmlspecialchars($_POST['ed_field_of_study'] ?? '');
                $edStartYear = htmlspecialchars($_POST['ed_start_year'] ?? '');
                $edEndYear = htmlspecialchars($_POST['ed_end_year'] ?? '');
                $edGrade = htmlspecialchars($_POST['ed_grade'] ?? '');
                $edIsCurrent = isset($_POST['ed_is_current']) ? 1 : 0;

                // Begin transaction
                $master_con->beginTransaction();

                $errors = [];

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Invalid email format.";
                }
                if (empty($firstName)) {
                    $errors[] = "First name is required.";
                }
                if (empty($lastName)) {
                    $errors[] = "Last name is required.";
                }
                if (empty($userName)) {
                    $errors[] = "Username is required.";
                }
                if (empty($_POST['password'])) {
                    $errors[] = "Password is required.";
                }

                if (!empty($errors)) {
                    // Combine errors into a single message
                    $error = implode(" ", $errors);
                    // Optionally store errors in session to show on form
                    $_SESSION['form_errors'] = $errors;
                    // Stop further processing
                    throw new Exception($error);
                }

                // Insert main user record
                $stmt = $master_con->prepare("
                    INSERT INTO user (
                        USER_ID, USER_EMAIL, USER_TYPE, USER_FSTNAME, USER_LSTNAME, 
                        USER_BIRTHDAY, USER_CONTACT, USER_PASSWORD, USER_NAME, 
                        USER_COUNTRY, USER_CATEG, USER_SKILLS, USER_TITLE, 
                        USER_LANGUAGE, USER_BIO, USER_TRIES, USER_STATUS, EMP_ID, ED_ID
                    ) VALUES (
                        :user_id, :email, :type, :fname, :lname, 
                        :birthday, :contact, :password, :username, 
                        :country, :categ, :skills, :title, 
                        :language, :bio, :tries, :status, :emp_id, :ed_id
                    )
                ");


                // Check if user already exists by USER_ID
                $stmtCheck = $master_con->prepare("SELECT USER_TRIES FROM user WHERE USER_ID = :user_id");
                $stmtCheck->execute([':user_id' => $user_id]);
                $existingUser = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                $tries = 0;
                if ($existingUser) {
                    // Increment tries count
                    $tries = $existingUser['USER_TRIES'] + 1;

                    // Optionally update USER_TRIES in DB if you want to keep track before insert/update
                    $updateTriesStmt = $master_con->prepare("UPDATE user SET USER_TRIES = :tries WHERE USER_ID = :user_id");
                    $updateTriesStmt->execute([':tries' => $tries, ':user_id' => $user_id]);
                }


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
                    ':categ' => $userCategory,
                    ':skills' => $userSkills,
                    ':title' => $userTitle,
                    ':language' => $userLanguage,
                    ':bio' => $userBio,
                    ':tries' => $tries,
                    ':status' => 'pending',
                    ':emp_id' => !empty($empCompany) ? $user_id . '_EMP' : null,
                    ':ed_id' => !empty($edSchool) ? $user_id . '_ED' : null
                ]);

                // Insert employment data if provided
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

                // Insert education data if provided
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

                // Clear session data
                unset($_SESSION['application_data']);

                $_SESSION['success_message'] = "Application submitted successfully! Please log in.";
                header('Location: ../loginSignup/logIn.php');
                exit;

            } catch (Exception $e) {
                if ($master_con->inTransaction()) {
                    $master_con->rollback();
                }
                $error = "Error submitting application: " . $e->getMessage();
            }
        } elseif ($_POST['action'] === 'Save') {
            // Handle Save Changes functionality
            try {
                // Update session data with new values
                $_SESSION['application_data'] = [
                    'user_type' => $_POST['userType'],
                    'username' => $_POST['userName'],
                    'email' => $_POST['email'],
                    'first_name' => $_POST['firstName'],
                    'last_name' => $_POST['lastName'],
                    'birthday' => $_POST['birthday'],
                    'phone' => $_POST['phone'],
                    'user_categ' => $_POST['user_categ'],
                    'user_skills' => $_POST['user_skills'],
                    'user_title' => $_POST['user_title'],
                    'user_language' => $_POST['user_language'],
                    'user_bio' => $_POST['user_bio'],
                    'location_data' => json_encode([
                        'country' => $_POST['country'],
                        'city' => $_POST['city'] ?? '',
                        'timezone' => $_POST['timezone'] ?? '',
                        'remote_work' => isset($_POST['remote_work'])
                    ]),
                    'employment_data' => json_encode([
                        'company' => $_POST['emp_company'] ?? '',
                        'position' => $_POST['emp_position'] ?? '',
                        'start_date' => $_POST['emp_start_date'] ?? '',
                        'end_date' => $_POST['emp_end_date'] ?? '',
                        'description' => $_POST['emp_description'] ?? '',
                        'is_current' => isset($_POST['emp_is_current'])
                    ]),
                    'education_data' => json_encode([
                        'school' => $_POST['ed_school'] ?? '',
                        'degree' => $_POST['ed_degree'] ?? '',
                        'field_of_study' => $_POST['ed_field_of_study'] ?? '',
                        'start_year' => $_POST['ed_start_year'] ?? '',
                        'end_year' => $_POST['ed_end_year'] ?? '',
                        'grade' => $_POST['ed_grade'] ?? '',
                        'is_current' => isset($_POST['ed_is_current'])
                    ])
                ];

                // Also update individual session variables for backward compatibility
                $_SESSION['email'] = $_POST['email'];
                $_SESSION['firstName'] = $_POST['firstName'];
                $_SESSION['lastName'] = $_POST['lastName'];
                $_SESSION['birthday'] = $_POST['birthday'];
                $_SESSION['phone'] = $_POST['phone'];
                $_SESSION['userName'] = $_POST['userName'];
                $_SESSION['type'] = $_POST['userType'];

                $_SESSION['success_message'] = "Changes saved successfully! Please review your information below.";

                // Return JSON response for AJAX
                if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Changes saved successfully!',
                        'data' => $_SESSION['application_data']
                    ]);
                    exit;
                }

                // Refresh the page to show updated data
                header('Location: ' . $_SERVER['PHP_SELF']);
                exit;

            } catch (Exception $e) {
                if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error saving changes: ' . $e->getMessage()
                    ]);
                    exit;
                }
                $error = "Error saving changes: " . $e->getMessage();
            }
        }
    }
}

if ($user_id) {
    $stmt = $master_con->prepare("SELECT USER_CONTACT, USER_EMAIL, USER_FSTNAME, USER_LSTNAME, USER_BIRTHDAY FROM user WHERE USER_ID = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {

        // Set session data from database if not already set
        if (empty($_SESSION['phone']) && $row['USER_CONTACT']) {
            $_SESSION['phone'] = $row['USER_CONTACT'];
        }
        if (empty($_SESSION['email']) && $row['USER_EMAIL']) {
            $_SESSION['email'] = $row['USER_EMAIL'];
        }
        if (empty($_SESSION['firstName']) && $row['USER_FSTNAME']) {
            $_SESSION['firstName'] = $row['USER_FSTNAME'];
        }
        if (empty($_SESSION['lastName']) && $row['USER_LSTNAME']) {
            $_SESSION['lastName'] = $row['USER_LSTNAME'];
        }
        if (empty($_SESSION['birthday']) && $row['USER_BIRTHDAY']) {
            $_SESSION['birthday'] = $row['USER_BIRTHDAY'];
        }
    }
}

// Get session data with fallbacks
$user_data = $_SESSION['application_data'] ?? [];
$employment_data = json_decode($user_data['employment_data'] ?? '{}', true) ?: [];
$education_data = json_decode($user_data['education_data'] ?? '{}', true) ?: [];
$location_data = json_decode($user_data['location_data'] ?? '{}', true) ?: [];

// Get basic user information from session
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
    <link rel="stylesheet" href="submit.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="topvar">
    <div class="logo">
        <img src="../imgs/inl2Logo.png" alt="INTERLINKED Logo">
    </div>
</div>

<div class="main-container">
    <div class="header-section">
        <h1>Review & Submit Application</h1>
        <p class="subtext">Please review all your information and make any necessary changes before submitting</p>
    </div>

    <div class="step-indicator">
        Step 8 of 8 - Final Review
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <div class="review-content">
            <!-- Account Information Section -->
            <div class="info-section">
                <h3><i class="fas fa-user-circle"></i> Account Information</h3>
                <div class="info-grid info-grid-2x2">
                    <div class="info-item">
                        <label>Username</label>
                        <div class="value" id="display_userName"><?= htmlspecialchars($userName) ?></div>
                    </div>
                    <div class="info-item">
                        <label>User Type</label>
                        <div class="value" id="display_userType"><?= htmlspecialchars($userType) ?></div>
                    </div>
                    <div class="info-item">
                        <label>Email Address</label>
                        <div class="value" id="display_email"><?= htmlspecialchars($email) ?></div>
                    </div>
                    <div class="info-item">
                        <label>User ID</label>
                        <div class="value"><?= htmlspecialchars($user_id) ?></div>
                    </div>
                    <div class="info-item">
                        <label for="password">Password <span class="required">*</span></label>
                        <div class="form-group">
                            <input type="password" id="password" name="password" required placeholder="Enter your password">
                        </div>
                    </div>
                    <div class="info-item">
                        <label for="password">Confirm Password<span class="required">*</span></label>
                        <div class="form-group">
                            <input type="password" id="password" name="password" required placeholder="Enter your password">
                        </div>
                    </div>                </div>
            </div>

            <!-- Personal Information Section -->
            <div class="info-section">
                <h3><i class="fas fa-user"></i> Personal Information</h3>
                <div class="info-grid info-grid-2x2">
                    <div class="info-item">
                        <label>First Name</label>
                        <div class="value" id="display_firstName"><?= htmlspecialchars($firstName) ?></div>
                    </div>
                    <div class="info-item">
                        <label>Last Name</label>
                        <div class="value" id="display_lastName"><?= htmlspecialchars($lastName) ?></div>
                    </div>
                    <div class="info-item">
                        <label>Date of Birth</label>
                        <div class="value" id="display_birthday"><?= htmlspecialchars($birthday) ?></div>
                    </div>
                    <div class="info-item">
                        <label>Phone Number</label>
                        <div class="value" id="display_phone"><?= htmlspecialchars($phone) ?></div>
                    </div>
                </div>
            </div>

            <!-- Professional Information Section -->
            <div class="info-section">
                <h3><i class="fas fa-briefcase"></i> Professional Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Category</label>
                        <div class="value" id="display_user_categ"><?= htmlspecialchars($user_data['user_categ'] ?? 'Not specified') ?></div>
                    </div>
                    <div class="info-item">
                        <label>Professional Title</label>
                        <div class="value" id="display_user_title"><?= htmlspecialchars($user_data['user_title'] ?? 'Not specified') ?></div>
                    </div>
                    <div class="info-item">
                        <label>Languages</label>
                        <div class="value" id="display_user_language"><?= htmlspecialchars($user_data['user_language'] ?? 'Not specified') ?></div>
                    </div>
                </div>
                <br>
                <div class="info-item bio-section">
                    <label>Skills</label>
                    <div class="value" id="display_user_skills"><?= htmlspecialchars($user_data['user_skills'] ?? 'Not specified') ?></div>
                </div>
                <br>
                <div class="info-item bio-section">
                    <label>Professional Bio</label>
                    <div class="value" id="display_user_bio"><?= htmlspecialchars($user_data['user_bio'] ?? 'Not specified') ?></div>
                </div>
            </div>

            <!-- Location Information Section -->
            <div class="info-section">
                <h3><i class="fas fa-map-marker-alt"></i> Location Information</h3>
                <div class="info-grid info-grid-2x2">
                    <div class="info-item">
                        <label>Country</label>
                        <div class="value" id="display_country"><?= htmlspecialchars($location_data['country'] ?? 'Not specified') ?></div>
                    </div>
                    <div class="info-item">
                        <label>City</label>
                        <div class="value" id="display_city"><?= htmlspecialchars($location_data['city'] ?? 'Not specified') ?></div>
                    </div>
                    <div class="info-item">
                        <label>Timezone</label>
                        <div class="value" id="display_timezone"><?= htmlspecialchars($location_data['timezone'] ?? 'Not specified') ?></div>
                    </div>
                    <div class="info-item">
                        <label>Remote Work Available</label>
                        <div class="value" id="display_remote_work"><?= ($location_data['remote_work'] ?? false) ? 'Yes' : 'No' ?></div>
                    </div>
                </div>
            </div>

            <!-- Employment Information Section -->
            <div class="info-section">
                <h3><i class="fas fa-building"></i> Employment Information</h3>
                <div class="info-grid info-grid-2x2">
                    <div class="info-item">
                        <label>Company Name</label>
                        <div class="value" id="display_emp_company"><?= htmlspecialchars($employment_data['company'] ?? 'Not specified') ?></div>
                    </div>
                    <div class="info-item">
                        <label>Position</label>
                        <div class="value" id="display_emp_position"><?= htmlspecialchars($employment_data['position'] ?? 'Not specified') ?></div>
                    </div>
                    <div class="info-item">
                        <label>Start Date</label>
                        <div class="value" id="display_emp_start_date"><?= htmlspecialchars($employment_data['start_date'] ?? 'Not specified') ?></div>
                    </div>
                    <div class="info-item">
                        <label>End Date</label>
                        <div class="value" id="display_emp_end_date"><?= ($employment_data['is_current'] ?? false) ? 'Currently Working' : htmlspecialchars($employment_data['end_date'] ?? 'Not specified') ?></div>
                    </div>
                </div>
                <br>
                <div class="info-grid">
                    <div class="info-item bio-section">
                        <label>Job Description</label>
                        <div class="value" id="display_emp_description"><?= htmlspecialchars($employment_data['description'] ?? 'Not specified') ?></div>
                    </div>
                </div>
            </div>

            <!-- Education Information Section -->
            <div class="info-section">
                <h3><i class="fas fa-graduation-cap"></i> Education Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label>School/Institution</label>
                        <div class="value" id="display_ed_school"><?= htmlspecialchars($education_data['school'] ?? 'Not specified') ?></div>
                    </div>
                    <div class="info-item">
                        <label>Degree</label>
                        <div class="value" id="display_ed_degree"><?= htmlspecialchars($education_data['degree'] ?? 'Not specified') ?></div>
                    </div>
                    <div class="info-item">
                        <label>Field of Study</label>
                        <div class="value" id="display_ed_field_of_study"><?= htmlspecialchars($education_data['field_of_study'] ?? 'Not specified') ?></div>
                    </div>
                    <div class="info-item">
                        <label>Start Year</label>
                        <div class="value" id="display_ed_start_year"><?= htmlspecialchars($education_data['start_year'] ?? 'Not specified') ?></div>
                    </div>
                    <div class="info-item">
                        <label>End Year</label>
                        <div class="value" id="display_ed_end_year"><?= ($education_data['is_current'] ?? false) ? 'Currently Studying' : htmlspecialchars($education_data['end_year'] ?? 'Not specified') ?></div>
                    </div>
                    <div class="info-item">
                        <label>Grade/GPA</label>
                        <div class="value" id="display_ed_grade"><?= htmlspecialchars($education_data['grade'] ?? 'Not specified') ?></div>
                    </div>
                </div>
            </div>

            <!-- Edit Form Section -->
            <div class="info-section">
                <h3><i class="fas fa-edit"></i> Make Changes</h3>
                <div class="toggle-edit">
                    <button type="button" onclick="toggleEditForm()" id="editToggleBtn">
                        <i class="fas fa-edit"></i> Edit Information
                    </button>
                </div>

                <form method="POST" enctype="multipart/form-data" class="edit-form" id="editForm">
                    <!-- Personal Information -->
                    <h4>Personal Information</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First Name <span class="required">*</span></label>
                            <input type="text" id="firstName" name="firstName" value="<?= htmlspecialchars($firstName) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name <span class="required">*</span></label>
                            <input type="text" id="lastName" name="lastName" value="<?= htmlspecialchars($lastName) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number <span class="required">*</span></label>
                            <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="birthday">Date of Birth <span class="required">*</span></label>
                            <input type="date" id="birthday" name="birthday" value="<?= htmlspecialchars($birthday) ?>" required>
                        </div>
                    </div>

                    <!-- Professional Information -->
                    <h4>Professional Information</h4>
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
                        <div class="form-group">
                            <label for="user_language">Languages <span class="required">*</span></label>
                            <input type="text" id="user_language" name="user_language" value="<?= htmlspecialchars($user_data['user_language'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="user_skills">Skills <span class="required">*</span></label>
                            <textarea id="user_skills" name="user_skills" rows="3" required><?= htmlspecialchars($user_data['user_skills'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group full-width">
                            <label for="user_bio">Professional Bio <span class="required">*</span></label>
                            <textarea id="user_bio" name="user_bio" rows="4" required><?= htmlspecialchars($user_data['user_bio'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <!-- Location Information -->
                    <h4>Location Information</h4>
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

                    <!-- Employment Information -->
                    <h4>Employment Information</h4>
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
                            <textarea id="emp_description" name="emp_description" rows="3"><?= htmlspecialchars($employment_data['description'] ?? '') ?></textarea>
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

                    <!-- Education Information -->
                    <h4>Education Information</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ed_school">School/Institution</label>
                            <input type="text" id="ed_school" name="ed_school" value="<?= htmlspecialchars($education_data['school'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="ed_degree">Degree</label>
                            <input type="text" id="ed_degree" name="ed_degree" value="<?= htmlspecialchars($education_data['degree'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="ed_field_of_study">Field of Study</label>
                            <input type="text" id="ed_field_of_study" name="ed_field_of_study" value="<?= htmlspecialchars($education_data['field_of_study'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="ed_grade">Grade/GPA</label>
                            <input type="text" id="ed_grade" name="ed_grade" value="<?= htmlspecialchars($education_data['grade'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="ed_start_year">Start Year</label>
                            <input type="number" id="ed_start_year" name="ed_start_year" value="<?= htmlspecialchars($education_data['start_year'] ?? '') ?>" min="1900" max="2030">
                        </div>
                        <div class="form-group">
                            <label for="ed_end_year">End Year</label>
                            <input type="number" id="ed_end_year" name="ed_end_year" value="<?= htmlspecialchars($education_data['end_year'] ?? '') ?>" min="1900" max="2030">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="ed_is_current" name="ed_is_current" <?= ($education_data['is_current'] ?? false) ? 'checked' : '' ?>>
                                <label for="ed_is_current">Currently studying here</label>
                            </div>
                        </div>
                    </div>

                    <!-- Hidden fields for readonly data -->
                    <input type="hidden" name="userName" value="<?= htmlspecialchars($userName) ?>">
                    <input type="hidden" name="userType" value="<?= htmlspecialchars($userType) ?>">
                </form>
            </div>
        </div>

        <div class="action-buttons">
            <form method="POST" style="display: inline;">
                <button type="submit" name="action" value="Back" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Overview
                </button>
            </form>

            <!-- Updated submit form with proper file handling -->
            <form method="POST" enctype="multipart/form-data" style="display: inline;" id="submitForm">
                <!-- Include all form data as hidden fields when submitting -->
                <input type="hidden" name="userName" value="<?= htmlspecialchars($userName) ?>">
                <input type="hidden" name="userType" value="<?= htmlspecialchars($userType) ?>">
                <input type="hidden" name="firstName" id="hidden_firstName" value="<?= htmlspecialchars($firstName) ?>">
                <input type="hidden" name="lastName" id="hidden_lastName" value="<?= htmlspecialchars($lastName) ?>">
                <input type="hidden" name="email" id="hidden_email" value="<?= htmlspecialchars($email) ?>">
                <input type="hidden" name="phone" id="hidden_phone" value="<?= htmlspecialchars($phone) ?>">
                <input type="hidden" name="birthday" id="hidden_birthday" value="<?= htmlspecialchars($birthday) ?>">
                <input type="hidden" name="password" id="hidden_password" value="" required>
                <input type="hidden" name="user_categ" id="hidden_user_categ" value="<?= htmlspecialchars($user_data['user_categ'] ?? '') ?>">
                <input type="hidden" name="user_title" id="hidden_user_title" value="<?= htmlspecialchars($user_data['user_title'] ?? '') ?>">
                <input type="hidden" name="user_language" id="hidden_user_language" value="<?= htmlspecialchars($user_data['user_language'] ?? '') ?>">
                <input type="hidden" name="user_skills" id="hidden_user_skills" value="<?= htmlspecialchars($user_data['user_skills'] ?? '') ?>">
                <input type="hidden" name="user_bio" id="hidden_user_bio" value="<?= htmlspecialchars($user_data['user_bio'] ?? '') ?>">
                <input type="hidden" name="country" id="hidden_country" value="<?= htmlspecialchars($location_data['country'] ?? '') ?>">
                <input type="hidden" name="city" id="hidden_city" value="<?= htmlspecialchars($location_data['city'] ?? '') ?>">
                <input type="hidden" name="timezone" id="hidden_timezone" value="<?= htmlspecialchars($location_data['timezone'] ?? '') ?>">
                <input type="hidden" name="remote_work" id="hidden_remote_work" value="">
                <input type="hidden" name="emp_company" id="hidden_emp_company" value="<?= htmlspecialchars($employment_data['company'] ?? '') ?>">
                <input type="hidden" name="emp_position" id="hidden_emp_position" value="<?= htmlspecialchars($employment_data['position'] ?? '') ?>">
                <input type="hidden" name="emp_start_date" id="hidden_emp_start_date" value="<?= htmlspecialchars($employment_data['start_date'] ?? '') ?>">
                <input type="hidden" name="emp_end_date" id="hidden_emp_end_date" value="<?= htmlspecialchars($employment_data['end_date'] ?? '') ?>">
                <input type="hidden" name="emp_description" id="hidden_emp_description" value="<?= htmlspecialchars($employment_data['description'] ?? '') ?>">
                <input type="hidden" name="emp_is_current" id="hidden_emp_is_current" value="">
                <input type="hidden" name="ed_school" id="hidden_ed_school" value="<?= htmlspecialchars($education_data['school'] ?? '') ?>">
                <input type="hidden" name="ed_degree" id="hidden_ed_degree" value="<?= htmlspecialchars($education_data['degree'] ?? '') ?>">
                <input type="hidden" name="ed_field_of_study" id="hidden_ed_field_of_study" value="<?= htmlspecialchars($education_data['field_of_study'] ?? '') ?>">
                <input type="hidden" name="ed_grade" id="hidden_ed_grade" value="<?= htmlspecialchars($education_data['grade'] ?? '') ?>">
                <input type="hidden" name="ed_start_year" id="hidden_ed_start_year" value="<?= htmlspecialchars($education_data['start_year'] ?? '') ?>">
                <input type="hidden" name="ed_end_year" id="hidden_ed_end_year" value="<?= htmlspecialchars($education_data['end_year'] ?? '') ?>">
                <input type="hidden" name="ed_is_current" id="hidden_ed_is_current" value="">

                <button type="button" class="btn btn-primary2" onclick="saveChanges()">Save Changes</button>
                <button type="submit" name="action" value="Submit" class="btn btn-primary" onclick="return validateForm()">
                    <i class="fas fa-check-circle"></i> Submit Application
                </button>
            </form>
        </div>
    </div>
</div>
<script>
    function toggleEditForm() {
        const editForm = document.getElementById('editForm');
        const toggleBtn = document.getElementById('editToggleBtn');

        if (editForm.classList.contains('active')) {
            editForm.classList.remove('active');
            toggleBtn.innerHTML = '<i class="fas fa-edit"></i> Edit Information';
        } else {
            editForm.classList.add('active');
            toggleBtn.innerHTML = '<i class="fas fa-eye"></i> Hide Edit Form';
        }
    }


    function saveChanges() {
        // Get the edit form data
        const formData = new FormData();

        // Add all form fields
        formData.append('action', 'Save');
        formData.append('ajax', '1');
        formData.append('userName', document.getElementById('userName')?.value || '<?= htmlspecialchars($userName) ?>');
        formData.append('userType', document.getElementById('userType')?.value || '<?= htmlspecialchars($userType) ?>');
        formData.append('firstName', document.getElementById('firstName').value);
        formData.append('lastName', document.getElementById('lastName').value);
        formData.append('email', document.getElementById('email').value);
        formData.append('phone', document.getElementById('phone').value);
        formData.append('birthday', document.getElementById('birthday').value);
        formData.append('password', document.getElementById('password').value);
        formData.append('user_categ', document.getElementById('user_categ').value);
        formData.append('user_title', document.getElementById('user_title').value);
        formData.append('user_language', document.getElementById('user_language').value);
        formData.append('user_skills', document.getElementById('user_skills').value);
        formData.append('user_bio', document.getElementById('user_bio').value);
        formData.append('country', document.getElementById('country').value);
        formData.append('city', document.getElementById('city').value);
        formData.append('timezone', document.getElementById('timezone').value);

        // Handle checkboxes
        if (document.getElementById('remote_work').checked) {
            formData.append('remote_work', '1');
        }
        if (document.getElementById('emp_is_current').checked) {
            formData.append('emp_is_current', '1');
        }
        if (document.getElementById('ed_is_current').checked) {
            formData.append('ed_is_current', '1');
        }

        // Employment data
        formData.append('emp_company', document.getElementById('emp_company').value);
        formData.append('emp_position', document.getElementById('emp_position').value);
        formData.append('emp_start_date', document.getElementById('emp_start_date').value);
        formData.append('emp_end_date', document.getElementById('emp_end_date').value);
        formData.append('emp_description', document.getElementById('emp_description').value);

        // Education data
        formData.append('ed_school', document.getElementById('ed_school').value);
        formData.append('ed_degree', document.getElementById('ed_degree').value);
        formData.append('ed_field_of_study', document.getElementById('ed_field_of_study').value);
        formData.append('ed_grade', document.getElementById('ed_grade').value);
        formData.append('ed_start_year', document.getElementById('ed_start_year').value);
        formData.append('ed_end_year', document.getElementById('ed_end_year').value);
        // Send AJAX request
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Changes saved successfully!');
                    // Update display values in real-time
                    updateDisplayValues();
                    updateHiddenFields();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving changes.');
            });
    }

    function updateDisplayValues() {
        // Update all display values with form values
        const updates = [
            ['display_firstName', 'firstName'],
            ['display_lastName', 'lastName'],
            ['display_email', 'email'],
            ['display_phone', 'phone'],
            ['display_birthday', 'birthday'],
            ['display_user_categ', 'user_categ'],
            ['display_user_title', 'user_title'],
            ['display_user_language', 'user_language'],
            ['display_user_skills', 'user_skills'],
            ['display_user_bio', 'user_bio'],
            ['display_country', 'country'],
            ['display_city', 'city'],
            ['display_timezone', 'timezone'],
            ['display_emp_company', 'emp_company'],
            ['display_emp_position', 'emp_position'],
            ['display_emp_start_date', 'emp_start_date'],
            ['display_emp_description', 'emp_description'],
            ['display_ed_school', 'ed_school'],
            ['display_ed_degree', 'ed_degree'],
            ['display_ed_field_of_study', 'ed_field_of_study'],
            ['display_ed_grade', 'ed_grade'],
            ['display_ed_start_year', 'ed_start_year']
        ];

        updates.forEach(([displayId, inputId]) => {
            const displayEl = document.getElementById(displayId);
            const inputEl = document.getElementById(inputId);
            if (displayEl && inputEl) {
                displayEl.textContent = inputEl.value || 'Not specified';
            }
        });

        // Handle special cases
        const remoteWork = document.getElementById('remote_work');
        const displayRemoteWork = document.getElementById('display_remote_work');
        if (displayRemoteWork && remoteWork) {
            displayRemoteWork.textContent = remoteWork.checked ? 'Yes' : 'No';
        }

        const empCurrent = document.getElementById('emp_is_current');
        const displayEmpEnd = document.getElementById('display_emp_end_date');
        if (displayEmpEnd && empCurrent) {
            if (empCurrent.checked) {
                displayEmpEnd.textContent = 'Currently Working';
            } else {
                const endDate = document.getElementById('emp_end_date').value;
                displayEmpEnd.textContent = endDate || 'Not specified';
            }
        }

        const edCurrent = document.getElementById('ed_is_current');
        const displayEdEnd = document.getElementById('display_ed_end_year');
        if (displayEdEnd && edCurrent) {
            if (edCurrent.checked) {
                displayEdEnd.textContent = 'Currently Studying';
            } else {
                const endYear = document.getElementById('ed_end_year').value;
                displayEdEnd.textContent = endYear || 'Not specified';
            }
        }
    }

    function validateForm() {
        const password = document.getElementById('password').value;
        if (!password) {
            alert('Please enter your password to submit the application.');
            return false;
        }

        updateHiddenFields();
        return confirm('Are you sure you want to submit your application? Please review all information carefully as this cannot be undone.');
    }

    function updateHiddenFields() {
        const getVal = (id) => document.getElementById(id)?.value || '';
        const getCheck = (id) => document.getElementById(id)?.checked ? '1' : '0';

        // Update all hidden fields
        document.getElementById('hidden_firstName').value = getVal('firstName');
        document.getElementById('hidden_lastName').value = getVal('lastName');
        document.getElementById('hidden_email').value = getVal('email');
        document.getElementById('hidden_phone').value = getVal('phone');
        document.getElementById('hidden_birthday').value = getVal('birthday');
        document.getElementById('hidden_password').value = getVal('password');
        document.getElementById('hidden_user_categ').value = getVal('user_categ');
        document.getElementById('hidden_user_title').value = getVal('user_title');
        document.getElementById('hidden_user_language').value = getVal('user_language');
        document.getElementById('hidden_user_skills').value = getVal('user_skills');
        document.getElementById('hidden_user_bio').value = getVal('user_bio');
        document.getElementById('hidden_country').value = getVal('country');
        document.getElementById('hidden_city').value = getVal('city');
        document.getElementById('hidden_timezone').value = getVal('timezone');
        document.getElementById('hidden_remote_work').value = getCheck('remote_work');
        document.getElementById('hidden_emp_company').value = getVal('emp_company');
        document.getElementById('hidden_emp_position').value = getVal('emp_position');
        document.getElementById('hidden_emp_start_date').value = getVal('emp_start_date');
        document.getElementById('hidden_emp_end_date').value = getVal('emp_end_date');
        document.getElementById('hidden_emp_description').value = getVal('emp_description');
        document.getElementById('hidden_emp_is_current').value = getCheck('emp_is_current');
        document.getElementById('hidden_ed_school').value = getVal('ed_school');
        document.getElementById('hidden_ed_degree').value = getVal('ed_degree');
        document.getElementById('hidden_ed_field_of_study').value = getVal('ed_field_of_study');
        document.getElementById('hidden_ed_grade').value = getVal('ed_grade');
        document.getElementById('hidden_ed_start_year').value = getVal('ed_start_year');
        document.getElementById('hidden_ed_end_year').value = getVal('ed_end_year');
        document.getElementById('hidden_ed_is_current').value = getCheck('ed_is_current');
    }

    // Auto-update display when form fields change
    document.addEventListener('DOMContentLoaded', function() {
        const formInputs = document.querySelectorAll('#editForm input, #editForm textarea, #editForm select');
        formInputs.forEach(input => {
            input.addEventListener('input', function() {
                // Small delay to allow for smooth typing
                setTimeout(updateDisplayValues, 100);
            });

            if (input.type === 'checkbox') {
                input.addEventListener('change', updateDisplayValues);
            }
        });
    });
</script>
</body>
</html>