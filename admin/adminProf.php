<?php
session_start();
include('interlinkedDB.php');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin_data'])) {
    header("Location: ../loginSignup/logIn.php");
    exit();
}

$conn = connectToDatabase();
$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);

$user_id = $_SESSION['user_id'];
$admin = $_SESSION['admin_data']; // Get admin data from session
$name = $_SESSION['userName'];
$profile_img = $_SESSION['profile_img'];

try {
    // Fetch all admin users for the list
    $adminStmt = $slave_con->prepare("SELECT * FROM user WHERE USER_TYPE = 'Admin' ORDER BY USER_FSTNAME ASC");
    $adminStmt->execute();
    $adminUsers = $adminStmt->fetchAll(PDO::FETCH_ASSOC);

    // Count total admins
    $countStmt = $slave_con->prepare("SELECT COUNT(*) as total FROM user WHERE USER_TYPE = 'Admin'");
    $countStmt->execute();
    $totalAdmins = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

} catch(PDOException $e) {
    // Log error and display friendly message
    error_log("Database error: " . $e->getMessage());
    $error = "An error occurred while fetching user data. Please try again later.";
}

// Handle profile update
if(isset($_POST['update_profile'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $contact = $_POST['contact'];
    $country = $_POST['country'];
    $birthday = $_POST['birthday'];

    try {
        $updateStmt = $master_con->prepare("UPDATE user SET 
            USER_FSTNAME = :first_name,
            USER_LSTNAME = :last_name,
            USER_EMAIL = :email,
            USER_NAME = :username, 
            USER_CONTACT = :contact,
            USER_COUNTRY = :country,
            USER_BIRTHDAY = :birthday
            WHERE USER_ID = :user_id");

        $updateStmt->bindParam(':first_name', $first_name);
        $updateStmt->bindParam(':last_name', $last_name);
        $updateStmt->bindParam(':email', $email);
        $updateStmt->bindParam(':username', $username);
        $updateStmt->bindParam(':contact', $contact);
        $updateStmt->bindParam(':country', $country);
        $updateStmt->bindParam(':birthday', $birthday);
        $updateStmt->bindParam(':user_id', $user_id);

        if($updateStmt->execute()) {
            // Update session data
            $_SESSION['userName'] = $first_name . ' ' . $last_name;
            $_SESSION['name'] = $_SESSION['userName'];
            $name = $_SESSION['userName'];
            $success = "Profile updated successfully!";

            // Refresh admin data in session
            $stmt = $master_con->prepare("SELECT * FROM user WHERE USER_ID = :user_id");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['admin_data'] = $admin;
        }
    } catch(PDOException $e) {
        error_log("Update error: " . $e->getMessage());
        $error = "An error occurred while updating your profile. Please try again.";
    }
}

// Handle profile image upload
if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
    $allowed = array('jpg', 'jpeg', 'png', 'gif');
    $filename = $_FILES['profile_image']['name'];
    $ext = pathinfo($filename, PATHINFO_EXTENSION);

    if(in_array(strtolower($ext), $allowed)) {
        // Create uploads directory if it doesn't exist
        $upload_dir = '../../uploads/profile_images/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $new_filename = 'admin_' . $user_id . '_' . time() . '.' . $ext;
        $upload_path = $upload_dir . $new_filename;

        if(move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
            try {
                $imgStmt = $master_con->prepare("UPDATE user SET USER_IMG = :image WHERE USER_ID = :user_id");
                $imgStmt->bindParam(':image', $upload_path);
                $imgStmt->bindParam(':user_id', $user_id);

                if($imgStmt->execute()) {
                    $profile_img = $upload_path;
                    $_SESSION['profile_img'] = $upload_path;
                    // Update admin data in session
                    $_SESSION['admin_data']['USER_IMG'] = $upload_path;
                    $admin['USER_IMG'] = $upload_path;
                    $success = "Profile image updated successfully!";
                }
            } catch(PDOException $e) {
                error_log("Image update error: " . $e->getMessage());
                $error = "An error occurred while updating your profile image.";
            }
        } else {
            $error = "Failed to upload image. Please try again.";
        }
    } else {
        $error = "Invalid file type. Please upload JPG, JPEG, PNG or GIF.";
    }
}

// Handle password change
if(isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    if(password_verify($current_password, $admin['USER_PASSWORD'])) {
        if($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            try {
                $passStmt = $master_con->prepare("UPDATE user SET USER_PASSWORD = :password WHERE USER_ID = :user_id");
                $passStmt->bindParam(':password', $hashed_password);
                $passStmt->bindParam(':user_id', $user_id);

                if($passStmt->execute()) {
                    // Update session data
                    $_SESSION['admin_data']['USER_PASSWORD'] = $hashed_password;
                    $admin['USER_PASSWORD'] = $hashed_password;
                    $success = "Password changed successfully!";
                }
            } catch(PDOException $e) {
                error_log("Password update error: " . $e->getMessage());
                $error = "An error occurred while updating your password.";
            }
        } else {
            $error = "New passwords do not match.";
        }
    } else {
        $error = "Current password is incorrect.";
    }
}

// Get system statistics
try {
    // Get total users
    $userCountStmt = $slave_con->prepare("SELECT COUNT(*) as total FROM user");
    $userCountStmt->execute();
    $totalUsers = $userCountStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get users by type
    $userTypeStmt = $slave_con->prepare("SELECT USER_TYPE, COUNT(*) as count FROM user GROUP BY USER_TYPE");
    $userTypeStmt->execute();
    $usersByType = $userTypeStmt->fetchAll(PDO::FETCH_ASSOC);

    // Format user types for easy access
    $userTypeCounts = [];
    foreach($usersByType as $type) {
        $userTypeCounts[$type['USER_TYPE']] = $type['count'];
    }

} catch(PDOException $e) {
    error_log("Stats error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile | interLINKED</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="adminProf.css">
    <link rel="icon" type="image/x-icon" href="../imgs/inlFaviconwhite.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .admin-panel {
            display: flex;
            gap: 20px;
        }
        .left-pane {
            flex: 1;
        }
        .right-pane {
            width: 300px;
            min-width: 300px;
            position: sticky;
            top: 20px;
            align-self: flex-start;
        }
        @media (max-width: 992px) {
            .admin-panel {
                flex-direction: column;
            }
            .right-pane {
                width: 100%;
                position: static;
            }
        }
    </style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
    <div class="topvar2">
        <div class="logo">
            <img src="../../imgs/inl2LogoWhite.png" alt="Logo">
        </div>
    </div>
    <ul class="side-content">
        <li><a href="adminDash.php"><i class="fas fa-database"></i> Dashboard</a></li>
        <li><a href="adminProj.php"><i class="fas fa-project-diagram"></i> Projects</a></li>
        <li><a href="adminPay.php"><i class="fas fa-dollar-sign"></i> Salary</a></li>
        <li><a href="adminUser.php"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="adminNotif.php"><i class="fas fa-bell"></i> Notifications</a></li>
        <li><a href="adminMes.php"><i class="fas fa-envelope"></i> Message</a></li>
        <li><a href="adminProf.php" class="active"><i class="fas fa-user"></i> Profile</a></li>
        <div class="btm-content">
            <button class="logout-button" onclick="window.location.href='../loginSignup/logOut.php';">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </button>
        </div>
    </ul>
</div>

<!-- Navbar -->
<div class="navbar">
    <div class="topvar">
        <div class="navtitle">
            <h1>Admin | </h1>
            <p>PROFILE</p>
        </div>
        <div class="navprofile">
            <div class="name">
                <h4><?=$name?></h4>
            </div>
            <div class="profile">
                <img src="<?=$profile_img?>" alt="Admin Profile">
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="main-container">
    <div class="admin-panel">
        <!-- Left Pane -->
        <div class="left-pane">
            <?php if(isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?=$error?>
                </div>
            <?php endif; ?>

            <?php if(isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?=$success?>
                </div>
            <?php endif; ?>

            <!-- Profile Info Section -->
            <div class="content profile-content">
                <h2><i class="fas fa-id-card"></i> My Profile Information</h2>

                <div class="profile-card">
                    <div class="profile-header-card">
                        <div class="profile-image-container">
                            <img src="<?=$profile_img?>" alt="Profile Image" id="profile-image">
                            <button id="change-photo-btn" class="action-button"><i class="fas fa-camera"></i> Change Photo</button>
                        </div>
                        <div class="profile-header-info">
                            <h3><?=$name?></h3>
                            <span class="user-type admin-tag">Administrator</span>
                            <p><i class="fas fa-envelope"></i> <?=$admin['USER_EMAIL']?></p>
                            <p><i class="fas fa-phone"></i> <?=$admin['USER_CONTACT']?></p>
                            <p><i class="fas fa-map-marker-alt"></i> <?=$admin['USER_COUNTRY']?></p>
                        </div>
                    </div>

                    <div class="profile-tabs">
                        <button class="tab-button active" data-tab="profile-details">Profile Details</button>
                        <button class="tab-button" data-tab="change-password">Change Password</button>
                        <button class="tab-button" data-tab="system-stats">System Statistics</button>
                    </div>

                    <div class="tab-content" id="profile-details">
                        <form action="" method="POST" id="profile-form">
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="first_name">First Name</label>
                                        <input type="text" id="first_name" name="first_name" value="<?=$admin['USER_FSTNAME']?>" required>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="last_name">Last Name</label>
                                        <input type="text" id="last_name" name="last_name" value="<?=$admin['USER_LSTNAME']?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="email">Email Address</label>
                                        <input type="email" id="email" name="email" value="<?=$admin['USER_EMAIL']?>" required>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="username">Username</label>
                                        <input type="text" id="username" name="username" value="<?=$admin['USER_NAME']?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="contact">Contact Number</label>
                                        <input type="text" id="contact" name="contact" value="<?=$admin['USER_CONTACT']?>">
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="country">Country</label>
                                        <input type="text" id="country" name="country" value="<?=$admin['USER_COUNTRY']?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="birthday">Birthday</label>
                                <input type="date" id="birthday" name="birthday" value="<?=$admin['USER_BIRTHDAY']?>">
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="update_profile" class="submit-btn">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-content" id="change-password" style="display: none;">
                        <form action="" method="POST" id="password-form">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>

                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="new_password">New Password</label>
                                        <input type="password" id="new_password" name="new_password" required>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="confirm_password">Confirm New Password</label>
                                        <input type="password" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>
                            </div>

                            <div class="password-requirements">
                                <p><i class="fas fa-info-circle"></i> Password Requirements:</p>
                                <ul>
                                    <li>Minimum 8 characters</li>
                                    <li>At least one uppercase letter</li>
                                    <li>At least one lowercase letter</li>
                                    <li>At least one number</li>
                                    <li>At least one special character</li>
                                </ul>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="change_password" class="submit-btn">
                                    <i class="fas fa-key"></i> Update Password
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-content" id="system-stats" style="display: none;">
                        <div class="stats-cards">
                            <!-- Admin Card -->
                            <div class="stat-card">
                                <div class="stat-icon admin-icon">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div class="stat-info">
                                    <h4>Administrators</h4>
                                    <span class="stat-count"><?= isset($userTypeCounts['Admin']) ? $userTypeCounts['Admin'] : 0 ?></span>
                                </div>
                            </div>

                            <!-- Client Card -->
                            <div class="stat-card">
                                <div class="stat-icon client-icon">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div class="stat-info">
                                    <h4>Clients</h4>
                                    <span class="stat-count"><?= isset($userTypeCounts['Client']) ? $userTypeCounts['Client'] : 0 ?></span>
                                </div>
                            </div>

                            <!-- Freelancer Card -->
                            <div class="stat-card">
                                <div class="stat-icon freelancer-icon">
                                    <i class="fas fa-laptop-code"></i>
                                </div>
                                <div class="stat-info">
                                    <h4>Freelancers</h4>
                                    <span class="stat-count"><?= isset($userTypeCounts['Freelancer']) ? $userTypeCounts['Freelancer'] : 0 ?></span>
                                </div>
                            </div>

                            <!-- Applicant Card -->
                            <div class="stat-card">
                                <div class="stat-icon applicant-icon">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="stat-info">
                                    <h4>Applicants</h4>
                                    <span class="stat-count"><?= isset($userTypeCounts['Applicant']) ? $userTypeCounts['Applicant'] : 0 ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Pane -->
        <div class="right-pane" id="right-pane">
            <div class="profile-header">
                <img src="<?=$profile_img?>" alt="Profile Image" class="profile-avatar">
                <div class="status-indicator"></div>
                <h3 class="profile-name"><?=$name?></h3>
                <p class="profile-title">System Administrator</p>
                <div class="profile-location">
                    <i class="fas fa-map-marker-alt"></i> <?=$admin['USER_COUNTRY']?>
                </div>
            </div>

            <div class="profile-actions">
                <button class="profile-action-btn update-btn" id="edit-profile-btn">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
            </div>

            <div class="profile-section">
                <h4 class="profile-section-title">Account Information</h4>
                <div class="profile-info-row">
                    <div class="profile-info-label">Email:</div>
                    <div class="profile-info-value"><?=$admin['USER_EMAIL']?></div>
                </div>
                <div class="profile-info-row">
                    <div class="profile-info-label">Username:</div>
                    <div class="profile-info-value"><?=$admin['USER_NAME']?></div>
                </div>
                <div class="profile-info-row">
                    <div class="profile-info-label">Contact:</div>
                    <div class="profile-info-value"><?=$admin['USER_CONTACT']?></div>
                </div>
                <div class="profile-info-row">
                    <div class="profile-info-label">Birthday:</div>
                    <div class="profile-info-value"><?=$admin['USER_BIRTHDAY']?></div>
                </div>
            </div>

            <div class="profile-section">
                <h4 class="profile-section-title">Account Activity</h4>
                <div class="profile-info-row">
                    <div class="profile-info-label">Last Login:</div>
                    <div class="profile-info-value"><?=date('M d, Y H:i')?></div>
                </div>
                <div class="profile-info-row">
                    <div class="profile-info-label">Account Created:</div>
                    <div class="profile-info-value">
                        <?=date('M d, Y', strtotime('-6 months'))?>
                    </div>
                </div>
                <div class="profile-info-row">
                    <div class="profile-info-label">Status:</div>
                    <div class="profile-info-value"><span class="status-active">Active</span></div>
                </div>
            </div>

            <div class="profile-section">
                <h4 class="profile-section-title">Access Level</h4>
                <div class="access-level">
                    <div class="access-level-item">
                        <i class="fas fa-user-shield"></i>
                        <span>Full System Access</span>
                    </div>
                    <div class="access-level-item">
                        <i class="fas fa-users-cog"></i>
                        <span>User Management</span>
                    </div>
                    <div class="access-level-item">
                        <i class="fas fa-cogs"></i>
                        <span>System Configuration</span>
                    </div>
                    <div class="access-level-item">
                        <i class="fas fa-database"></i>
                        <span>Database Management</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Profile Image Modal -->
<div class="modal" id="upload-image-modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h3><i class="fas fa-image"></i> Update Profile Image</h3>

        <form action="" method="POST" enctype="multipart/form-data" id="image-form">
            <div class="form-group">
                <label for="profile_image">Select Image</label>
                <input type="file" id="profile_image" name="profile_image" accept="image/*" required>
            </div>

            <div class="image-preview-container">
                <img id="image-preview" src="#" alt="Preview" style="display: none; max-width: 100%; max-height: 200px;">
            </div>

            <div class="modal-footer">
                <button type="button" class="cancel-btn" id="cancel-upload">Cancel</button>
                <button type="submit" class="submit-btn">Upload Image</button>
            </div>
        </form>
    </div>
</div>
<script src="projScript.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
</body>
</html>