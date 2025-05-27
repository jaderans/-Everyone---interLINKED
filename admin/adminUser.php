<?php
session_start();
include('interlinkedDB.php');
$conn = connectToDatabase();
$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);
// Default search
$search = $_GET['search'] ?? '';

function fetchUsers($conn, $type, $search = '') {
    // Determine if we want applicants or non-applicants
    $typeCondition = $type === 'Applicant' ? "USER_TYPE = 'Applicant'" : "USER_TYPE != 'Applicant'";

    // Start building the query - ADD USER_STATUS to SELECT and ORDER BY to put fired/rejected at bottom
    $query = "SELECT *, COALESCE(USER_STATUS, 'PENDING') as USER_STATUS FROM user WHERE $typeCondition";

    // Add search filter if provided
    if (!empty($search)) {
        $query .= " AND (USER_FSTNAME LIKE :search OR USER_LSTNAME LIKE :search OR USER_EMAIL LIKE :search)";
    }

    // Order by status to put FIRED/REJECTED at bottom
    if ($type === 'Applicant') {
        $query .= " ORDER BY CASE WHEN USER_STATUS = 'REJECTED' THEN 1 ELSE 0 END, USER_ID";
    } else {
        $query .= " ORDER BY CASE WHEN USER_STATUS = 'FIRED' THEN 1 ELSE 0 END, USER_ID";
    }

    $stmt = $conn->prepare($query);

    // Bind parameters
    if (!empty($search)) {
        $stmt->execute(['search' => "%$search%"]);
    } else {
        $stmt->execute();
    }

    return $stmt;
}

$selectedUser = null;

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM user WHERE USER_ID = ?");
    $stmt->execute([$_GET['id']]);
    $selectedUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

$usersResult = fetchUsers($conn, 'User', $search);
$applicantsResult = fetchUsers($conn, 'Applicant', $search);

$name = $_SESSION['userName'];
$stmt = $slave_con->prepare("SELECT * FROM user where USER_NAME = ?");
$stmt->execute([$name]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin | interLINKED</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" type="image/x-icon" href="../imgs/inlFaviconwhite.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<style>

</style>

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
        <li><a href="adminProf.php"><i class="fas fa-user"></i> Profile</a></li>
        <div class="btm-content">
            <button class="logout-button" onclick="window.location.href='../loginSignup/logIn.php';">
                <i class="fas fa-sign-out"></i> Log Out
            </button>
        </div>

    </ul>
</div>

<!-- Navbar -->
<div class="navbar">
    <div class="topvar">
        <div class="navtitle">
            <h1>Admin | </h1>
            <p>USER MANAGEMENT</p>
        </div>
        <div class="navprofile">
            <div class="name">
                <h4><?=$name?></h4>
            </div>
            <div class="profile">
                <img src="../../imgs/profile.png" alt="Admin Profile">
            </div>
        </div>
    </div>
</div>

<!-- Main Container -->
<div class="main-container">
    <div class="admin-panel">

        <!-- Left Pane -->
        <div class="left-pane">
            <!-- Search & Sort Bar -->
            <div class="search-sort-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search users...">
                </div>
                <div class="action-buttons">
                    <button class="fire-button" onclick="bulkFireUsers()"><i class="fas fa-fire"></i> Fire</button>
                    <button class="sort-button"><i class="fas fa-sort"></i> Sort</button>
                </div>
            </div>

            <div class="content">
                <h2>USERS</h2>
                <div class="table-container">
                    <table>
                        <thead>
                        <tr>
                            <th class="checkbox-cell"><input type="checkbox"></th>
                            <th></th>
                            <th>User ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                    </table>
                    <div class="scrollable-body">
                        <table>
                            <tbody id="userTableBody">
                            <?php while ($row = $usersResult->fetch(PDO::FETCH_ASSOC)): ?>
                                <?php
                                $imageData = $row['USER_IMG'];
                                $imageSrc = $imageData ? 'data:image/jpeg;base64,' . base64_encode($imageData) : 'default.jpg';
                                ?>
                                <tr>
                                    <td class="checkbox-cell"><input type="checkbox" class="user-checkbox" value="<?= $row['USER_ID'] ?>"></td>
                                    <td>
                                        <div class="user-row">
                                            <img src="<?= $imageSrc ?>" class="user-avatar" alt="User Avatar">
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($row['USER_ID']) ?></td>
                                    <td><?= htmlspecialchars($row['USER_FSTNAME'] . ' ' . $row['USER_LSTNAME']) ?></td>
                                    <td><?= htmlspecialchars($row['USER_EMAIL']) ?></td>
                                    <td>
                                        <?php
                                        switch ($row['USER_TYPE']) {
                                            case 'Admin': $tagClass = 'admin-tag'; break;
                                            case 'Client': $tagClass = 'client-tag'; break;
                                            case 'Freelancer': $tagClass = 'freelancer-tag'; break;
                                            case 'Applicant': $tagClass = 'applicant-tag'; break;
                                            default: $tagClass = '';
                                        }
                                        ?>
                                        <span class="<?= $tagClass ?> user-type"><?= htmlspecialchars($row['USER_TYPE']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($row['USER_CONTACT']) ?></td>
                                    <td>
                                        <?php
                                        $status = $row['USER_STATUS'] ?? 'PENDING';
                                        $statusClass = '';
                                        switch ($status) {
                                            case 'FIRED': $statusClass = 'status-fired'; break;
                                            case 'ACTIVE': $statusClass = 'status-active'; break;
                                            case 'PENDING': $statusClass = 'status-pending'; break;
                                            default: $statusClass = 'status-pending';
                                        }
                                        ?>
                                        <span class="<?= $statusClass ?> status-tag"><?= htmlspecialchars($status) ?></span>
                                    </td>
                                    <td class="actions">
                                        <a href="?id=<?= $row['USER_ID'] ?>"><i class="fas fa-eye action-icon"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Applicant Section -->
            <div class="search-sort-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="applicantSearchInput" placeholder="Search applicants...">
                </div>
                <div class="action-buttons">
                    <button class="fire-button" onclick="bulkRejectApplicants()"><i class="fas fa-user-times"></i> Reject</button>
                    <button class="sort-button"><i class="fas fa-sort"></i> Sort</button>
                </div>
            </div>
            <div class="content">
                <h2>APPLICANTS</h2>

                <div class="table-container">
                    <table>
                        <thead>
                        <tr>
                            <th class="checkbox-cell"><input type="checkbox"></th>
                            <th></th>
                            <th>Applicant ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>User Type</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                    </table>
                    <div class="scrollable-body">
                        <table>
                            <tbody id="applicantTableBody">
                            <?php while ($row = $applicantsResult->fetch(PDO::FETCH_ASSOC)): ?>
                                <?php
                                $imageData = $row['USER_IMG'];
                                $imageSrc = $imageData ? 'data:image/jpeg;base64,' . base64_encode($imageData) : 'default.jpg';
                                ?>
                                <tr>
                                    <td class="checkbox-cell"><input type="checkbox" class="applicant-checkbox" value="<?= $row['USER_ID'] ?>"></td>
                                    <td>
                                        <div class="user-row">
                                            <img src="<?= $imageSrc ?>" class="user-avatar" alt="Applicant Avatar">
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($row['USER_ID']) ?></td>
                                    <td><?= htmlspecialchars($row['USER_FSTNAME'] . ' ' . $row['USER_LSTNAME']) ?></td>
                                    <td><?= htmlspecialchars($row['USER_EMAIL']) ?></td>
                                    <td>
                                        <span class="applicant-tag user-type"><?= htmlspecialchars($row['USER_TYPE']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($row['USER_CONTACT']) ?></td>
                                    <td>
                                        <?php
                                        $status = $row['USER_STATUS'] ?? 'PENDING';
                                        $statusClass = '';
                                        switch ($status) {
                                            case 'REJECTED': $statusClass = 'status-rejected'; break;
                                            case 'ACTIVE': $statusClass = 'status-active'; break;
                                            case 'PENDING': $statusClass = 'status-pending'; break;
                                            default: $statusClass = 'status-pending';
                                        }
                                        ?>
                                        <span class="<?= $statusClass ?> status-tag"><?= htmlspecialchars($status) ?></span>
                                    </td>
                                    <td class="actions">
                                        <button class="hire-button" onclick="hireApplicant(<?= $row['USER_ID'] ?>)">Hire</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


        <!-- Right Pane (Profile Details) -->
        <div id="right-pane">
            <?php if ($selectedUser): ?>
                <?php
                $imageData = $selectedUser['USER_IMG'];
                $imageSrc = $imageData ? 'data:image/jpeg;base64,' . base64_encode($imageData) : 'default.jpg';
                ?>
                <div class="profile-header">
                    <img src="<?= $imageSrc ?>" alt="User Profile" class="profile-avatar">
                    <div class="profile-name"><?= htmlspecialchars($selectedUser['USER_NAME'] ?? 'No Username') ?></div>
                    <div class="profile-title"><?= htmlspecialchars($selectedUser['USER_TYPE']) ?></div>
                    <div class="profile-location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($selectedUser['USER_COUNTRY'] ?? 'No Location') ?></div>
                </div>

                <div class="profile-actions">
                    <button class="profile-action-btn update-btn">Update Info</button>
                    <button class="profile-action-btn promote-btn">Promote</button>
                </div>

                <div class="profile-section">
                    <div class="profile-section-title">User Details</div>

                    <div class="profile-info-row">
                        <div class="profile-info-label">Status:</div>
                        <div class="profile-info-value"><?= htmlspecialchars($selectedUser['USER_STATUS']) ?></div>
                    </div>

                    <div class="profile-info-row">
                        <div class="profile-info-label">User ID:</div>
                        <div class="profile-info-value"><?= htmlspecialchars($selectedUser['USER_ID']) ?></div>
                    </div>
                    <div class="profile-info-row">
                        <div class="profile-info-label">First Name:</div>
                        <div class="profile-info-value"><?= htmlspecialchars($selectedUser['USER_FSTNAME']) ?></div>
                    </div>
                    <div class="profile-info-row">
                        <div class="profile-info-label">Last Name:</div>
                        <div class="profile-info-value"><?= htmlspecialchars($selectedUser['USER_LSTNAME']) ?></div>
                    </div>
                    <div class="profile-info-row">
                        <div class="profile-info-label">Email:</div>
                        <div class="profile-info-value"><?= htmlspecialchars($selectedUser['USER_EMAIL']) ?></div>
                    </div>
                    <div class="profile-info-row">
                        <div class="profile-info-label">Phone:</div>
                        <div class="profile-info-value"><?= htmlspecialchars($selectedUser['USER_CONTACT']) ?></div>
                    </div>
                    <div class="profile-info-row">
                        <div class="profile-info-label">Birthday:</div>
                        <div class="profile-info-value"><?= htmlspecialchars($selectedUser['USER_BIRTHDAY'] ?? 'Not set') ?></div>
                    </div>
                </div>

                <button class="portfolio-btn"><i class="fas fa-folder-open"></i> View Portfolio</button>
            <?php else: ?>
                <div class="profile-header">
                    <a style="width: 100%; height: 100%;">
                        <i class="far fa-address-card" style="font-size:6rem;"></i>
                    </a>
                    <p style="padding: 10px">Select a user from the table to view details.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let debounceTimer;

        $('a[href^="editUser.php"]').on('click', function (e) {
            e.preventDefault();
            const url = $(this).attr('href');
            $('#profileDetails').html('Loading...');
            $.get(url, function (data) {
                $('#profileDetails').html(data);
            });
        });
        document.getElementById('applicantSearchInput').addEventListener('keyup', function () {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#applicantTableBody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });

        document.getElementById('searchInput').addEventListener('keyup', function () {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#userTableBody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
        // Load profile into right pane
        function loadUserProfile(userID) {
            const rightPane = document.getElementById("right-pane");
            fetch('getUserProfile.php?id=' + userID)
                .then(response => response.text())
                .then(data => {
                    rightPane.innerHTML = data;
                })
                .catch(error => console.error('Error loading profile:', error));
        }

        document.getElementById("searchInput").addEventListener("input", function () {
            const searchTerm = this.value;
            clearTimeout(debounceTimer);

            debounceTimer = setTimeout(() => {
                const tbody = document.getElementById("userTableBody");
                tbody.innerHTML = '<tr><td colspan="8">Searching...</td></tr>';

                fetch(`searchUsers.php?search=${encodeURIComponent(searchTerm)}`)
                    .then(response => response.text())
                    .then(data => {
                        tbody.innerHTML = data;
                    })
                    .catch(error => {
                        console.error("Error fetching search results:", error);
                        tbody.innerHTML = '<tr><td colspan="8">Error loading users.</td></tr>';
                    });
            }, 300);
        });
        document.getElementById('bulkFireBtn').addEventListener('click', function() {
            const selectedUsers = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);

            if (selectedUsers.length === 0) {
                alert('Please select users to fire.');
                return;
            }

            if (confirm(`Are you sure you want to fire ${selectedUsers.length} selected user(s)?`)) {
                bulkFireUsers(selectedUsers);
            }
        });

        document.getElementById('bulkRejectBtn').addEventListener('click', function() {
            const selectedApplicants = Array.from(document.querySelectorAll('.applicant-checkbox:checked')).map(cb => cb.value);

            if (selectedApplicants.length === 0) {
                alert('Please select applicants to reject.');
                return;
            }

            if (confirm(`Are you sure you want to reject ${selectedApplicants.length} selected applicant(s)?`)) {
                bulkRejectApplicants(selectedApplicants);
            }
        });

        document.getElementById('hireApplicant').addEventListener('click', function() {
            const selectedApplicants = Array.from(document.querySelectorAll('.applicant-checkbox:checked')).map(cb => cb.value);

            if (selectedApplicants.length === 0) {
                alert('Please select applicants to hire.');
                return;
            }

            if (confirm(`Are you sure you want to reject ${selectedApplicants.length} selected applicant(s)?`)) {
                hireApplicant(selectedApplicants);
            }
        });

        // Select all checkboxes functionality
        document.querySelector('#userTableBody').closest('.table-container').querySelector('thead input[type="checkbox"]').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });

        document.querySelector('#applicantTableBody').closest('.table-container').querySelector('thead input[type="checkbox"]').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.applicant-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });

        function bulkFireUsers() {
            const selectedUsers = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);

            if (selectedUsers.length === 0) {
                alert('Please select users to fire.');
                return;
            }

            if (confirm(`Are you sure you want to fire ${selectedUsers.length} selected user(s)?`)) {
                // Call the existing bulkFireUsers function with the selected IDs
                fetch('bulkFireUsers.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({user_ids: selectedUsers})
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(`Successfully fired ${data.count} user(s)`);
                            location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to fire users'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error firing users');
                    });
            }
        }

        function bulkRejectApplicants() {
            const selectedApplicants = Array.from(document.querySelectorAll('.applicant-checkbox:checked')).map(cb => cb.value);

            if (selectedApplicants.length === 0) {
                alert('Please select applicants to reject.');
                return;
            }

            if (confirm(`Are you sure you want to reject ${selectedApplicants.length} selected applicant(s)?`)) {
                fetch('bulkRejectApplicants.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({user_ids: selectedApplicants})
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(`Successfully rejected ${data.count} applicant(s)`);
                            location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to reject applicants'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error rejecting applicants');
                    });
            }
        }

        function hireApplicant(userId) {
            console.log("Attempting to hire user with ID:", userId);
            if (confirm('Are you sure you want to hire this applicant?')) {
                fetch('hireApplicant.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'user_id=' + userId
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log("Server response:", data);
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to hire applicant'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error hiring applicant');
                    });
            }
        }


        function rejectApplicant(userId) {
            if (confirm('Are you sure you want to reject this applicant?')) {
                fetch('rejectApplicant.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'user_id=' + userId
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + (data.message || 'Failed to reject applicant'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error rejecting applicant');
                    });
            }
        }
    </script>
</body>
</html>