<?php
session_start();
include('interlinkedDB.php');
include_once 'checkIfSet.php';
$conn = connectToDatabase();
$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    // Not logged in – redirect to login
    header("Location: logIn.php");
    exit();
}

// Get session user ID and fetch current user details
$userId = $_SESSION['user_id'];
$name = $_SESSION['userName'] ?? 'Unknown';

$stmt = $slave_con->prepare("SELECT * FROM user WHERE USER_ID = ?");
$stmt->execute([$userId]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

// Set search/filter defaults
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'all';
$sortBy = $_GET['sort'] ?? 'priority';

$projectsResult = fetchProjects($slave_con, $filter, $search, $sortBy);

// Fetch projects
function fetchProjects($conn, $status = 'all', $search = '', $sortBy = 'priority') {
    $query = "SELECT p.*, u.USER_FSTNAME, u.USER_LSTNAME 
              FROM projects p 
              LEFT JOIN user u ON p.USER_ID = u.USER_ID";

    if ($status !== 'all') {
        $query .= " WHERE p.PRO_STATUS = :status";
        if (!empty($search)) {
            $query .= " AND (p.PRO_TITLE LIKE :search OR p.PRO_DESCRIPTION LIKE :search)";
        }
    } elseif (!empty($search)) {
        $query .= " WHERE (p.PRO_TITLE LIKE :search OR p.PRO_DESCRIPTION LIKE :search)";
    }

    // Updated sorting logic
    switch ($sortBy) {
        case 'priority':
            $query .= " ORDER BY CASE 
                        WHEN p.PRO_PRIORITY_LEVEL = 'High' THEN 1 
                        WHEN p.PRO_PRIORITY_LEVEL = 'Medium' THEN 2 
                        WHEN p.PRO_PRIORITY_LEVEL = 'Low' THEN 3 
                        ELSE 4 END, p.PRO_END_DATE ASC";
            break;
        case 'project_id':
            $query .= " ORDER BY p.PRO_ID ASC";
            break;
        case 'type':
            $query .= " ORDER BY p.PRO_TYPE ASC";
            break;
        case 'assigned_to':
            $query .= " ORDER BY u.USER_FSTNAME ASC, u.USER_LSTNAME ASC";
            break;
        case 'deadline':
            $query .= " ORDER BY p.PRO_END_DATE ASC";
            break;
        default:
            $query .= " ORDER BY CASE 
                        WHEN p.PRO_PRIORITY_LEVEL = 'High' THEN 1 
                        WHEN p.PRO_PRIORITY_LEVEL = 'Medium' THEN 2 
                        WHEN p.PRO_PRIORITY_LEVEL = 'Low' THEN 3 
                        ELSE 4 END, p.PRO_END_DATE ASC";
    }

    $stmt = $conn->prepare($query);

    if ($status !== 'all') {
        $stmt->bindValue(':status', $status);
    }
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%");
    }

    $stmt->execute();
    return $stmt;
}

// Fetch team members
function fetchUsers($conn) {
    $query = "SELECT USER_ID, USER_FSTNAME, USER_LSTNAME, USER_TYPE FROM user WHERE USER_TYPE IN ('Freelancer', 'Admin')";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    return $stmt;
}

// Selected project (if any)
$selectedProject = null;
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT p.*, u.USER_FSTNAME, u.USER_LSTNAME 
                            FROM projects p 
                            LEFT JOIN user u ON p.USER_ID = u.USER_ID 
                            WHERE p.PRO_ID = ?");
    $stmt->execute([$_GET['id']]);
    $selectedProject = $stmt->fetch(PDO::FETCH_ASSOC);
}

$projectsResult = fetchProjects($conn, $filter, $search);
$teamMembers = fetchUsers($conn);

// Project status counts
$stmt = $conn->prepare("SELECT PRO_STATUS, COUNT(*) as count FROM projects GROUP BY PRO_STATUS");
$stmt->execute();
$statusCounts = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $statusCounts[$row['PRO_STATUS']] = $row['count'];
}

$workingCount = $statusCounts['Working'] ?? 0;
$pendingCount = $statusCounts['Pending'] ?? 0;
$completedCount = $statusCounts['Completed'] ?? 0;
$canceledCount = $statusCounts['Canceled'] ?? 0;
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
        <li><a href="adminProj.php" class="active"><i class="fas fa-project-diagram"></i> Projects</a></li>
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
            <p>PROJECT MANAGEMENT</p>
        </div>
        <div class="navprofile">
            <div class="name">
                <h4><?=$name?></h4>
            </div>
            <div class="profile">
                <img src="../imgs/profile.png" alt="Admin Profile">
            </div>
        </div>
    </div>
</div>

<!-- Main Container -->
<div class="main-container">
    <div class="admin-panel">
        <!-- Left Pane -->
        <div class="left-pane">
            <!-- Status Badges -->
            <div class="status-badges">
                <div class="status-badge status-working" onclick="filterProjects('Working')">
                    <div class="count"><?=$workingCount?></div>
                    <div class="label">Working</div>
                </div>
                <div class="status-badge status-pending" onclick="filterProjects('Pending')">
                    <div class="count"><?=$pendingCount?></div>
                    <div class="label">Pending</div>
                </div>
                <div class="status-badge status-completed" onclick="filterProjects('Completed')">
                    <div class="count"><?=$completedCount?></div>
                    <div class="label">Completed</div>
                </div>
                <div class="status-badge status-canceled" onclick="filterProjects('Canceled')">
                    <div class="count"><?=$canceledCount?></div>
                    <div class="label">Canceled</div>
                </div>
            </div>

            <!-- Search & Action Bar -->
            <div class="search-sort-bar">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search projects...">
                </div>
                <div class="action-buttons">
                    <button class="action-button" id="addProjectBtn"><i class="fas fa-plus"></i> Add Project</button>
                    <button class="fire-button" id="bulkDeleteProjects"><i class="fas fa-trash"></i> Delete</button>
                    <div class="sort-dropdown">
                        <button class="sort-button" id="sortBtn"><i class="fas fa-sort"></i> Sort</button>
                        <div class="sort-options" id="sortOptions">
                            <a href="?filter=<?= $filter ?>&search=<?= $search ?>&sort=priority">Priority</a>
                            <a href="?filter=<?= $filter ?>&search=<?= $search ?>&sort=project_id">Project ID</a>
                            <a href="?filter=<?= $filter ?>&search=<?= $search ?>&sort=type">Type</a>
                            <a href="?filter=<?= $filter ?>&search=<?= $search ?>&sort=assigned_to">Assigned To</a>
                            <a href="?filter=<?= $filter ?>&search=<?= $search ?>&sort=deadline">Deadline</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters">
                <button class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>" onclick="filterProjects('all')">All</button>
                <button class="filter-btn <?php echo $filter === 'Working' ? 'active' : ''; ?>" onclick="filterProjects('Working')">Working</button>
                <button class="filter-btn <?php echo $filter === 'Pending' ? 'active' : ''; ?>" onclick="filterProjects('Pending')">Pending</button>
                <button class="filter-btn <?php echo $filter === 'Completed' ? 'active' : ''; ?>" onclick="filterProjects('Completed')">Completed</button>
                <button class="filter-btn <?php echo $filter === 'Canceled' ? 'active' : ''; ?>" onclick="filterProjects('Canceled')">Canceled</button>
            </div>

            <!-- Projects Table -->
            <div class="content">
                <h2>PROJECTS</h2>
                <div class="table-container">
                    <table>
                        <thead>
                        <tr>
                            <th class="checkbox-cell"><input type="checkbox" id="selectAll"></th>
                            <th>Project ID</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Type</th>
                            <th>Assigned To</th>
                            <th>Deadline</th>
                            <th></th>
                        </tr>
                        </thead>
                    </table>
                    <div class="scrollable-body">
                        <table>
                            <tbody id="projectTableBody">
                            <?php while ($row = $projectsResult->fetch(PDO::FETCH_ASSOC)):
                                // Determine priority styling
                                $priorityClass = '';
                                switch ($row['PRO_PRIORITY_LEVEL']) {
                                    case 'High': $priorityClass = 'priority-high'; break;
                                    case 'Medium': $priorityClass = 'priority-medium'; break;
                                    case 'Low': $priorityClass = 'priority-low'; break;
                                }

                                // Determine status tag styling and check for overdue
                                $statusClass = '';
                                $displayStatus = $row['PRO_STATUS'];

                                // Check if project is overdue
                                $today = new DateTime();
                                $deadline = new DateTime($row['PRO_END_DATE']);
                                $isOverdue = ($today > $deadline && $row['PRO_STATUS'] !== 'Completed' && $row['PRO_STATUS'] !== 'Canceled');

                                if ($isOverdue) {
                                    $displayStatus = 'Overdue';
                                    $statusClass = 'status-overdue-tag';
                                } else {
                                    switch ($row['PRO_STATUS']) {
                                        case 'Working': $statusClass = 'status-working-tag'; break;
                                        case 'Pending': $statusClass = 'status-pending-tag'; break;
                                        case 'Completed': $statusClass = 'status-completed-tag'; break;
                                        case 'Canceled': $statusClass = 'status-canceled-tag'; break;
                                    }
                                }

                                $daysDiff = $today->diff($deadline)->days;
                                $dueDateClass = '';

                                if ($isOverdue) {
                                    $dueDateClass = 'overdue';
                                } else if ($daysDiff <= 3 && $row['PRO_STATUS'] !== 'Completed') {
                                    $dueDateClass = 'deadline-close';
                                }
                                ?>
                                <tr class="<?= $priorityClass ?>">
                                    <td class="checkbox-cell"><input type="checkbox" class="project-checkbox" value="<?= $row['PRO_ID'] ?>"></td>
                                    <td><?= htmlspecialchars($row['PRO_ID']) ?></td>
                                    <td><?= htmlspecialchars($row['PRO_TITLE']) ?></td>
                                    <td><span class="status-tag <?= $statusClass ?>"><?= htmlspecialchars($displayStatus) ?></span></td>
                                    <td><?= htmlspecialchars($row['PRO_PRIORITY_LEVEL']) ?></td>
                                    <td><?= htmlspecialchars($row['PRO_TYPE']) ?></td>
                                    <td><?= $row['USER_FSTNAME'] ? htmlspecialchars($row['USER_FSTNAME'] . ' ' . $row['USER_LSTNAME']) : 'Unassigned' ?></td>
                                    <td class="<?= $dueDateClass ?>"><?= date('M d, Y', strtotime($row['PRO_END_DATE'])) ?></td>
                                    <td class="actions">
                                        <a href="?id=<?= $row['PRO_ID'] ?>"><i class="fas fa-eye action-icon"></i></a>
                                        <a href="#" onclick="editProject(<?= $row['PRO_ID'] ?>)"><i class="fas fa-edit action-icon"></i></a>
                                        <form class="delete-form" data-id="<?= $row['PRO_ID'] ?>" style="display:inline;">
                                            <button type="button" class="delete-btn action-icon" title="Pay" style="border:none; background:none; cursor:pointer;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Pane (Project Details) -->
        <div id="right-pane">
            <?php if ($selectedProject): ?>
                <div class="profile-header">
                    <div class="profile-name"><?= htmlspecialchars($selectedProject['PRO_TITLE']) ?></div>
                    <?php
                    $statusClass = '';
                    $displayStatus = $selectedProject['PRO_STATUS'];

                    // Check if project is overdue
                    $today = new DateTime();
                    $deadline = new DateTime($selectedProject['PRO_END_DATE']);
                    $isOverdue = ($today > $deadline && $selectedProject['PRO_STATUS'] !== 'Completed' && $selectedProject['PRO_STATUS'] !== 'Canceled');

                    if ($isOverdue) {
                        $displayStatus = 'Overdue';
                        $statusClass = 'status-overdue-tag';
                    } else {
                        switch ($selectedProject['PRO_STATUS']) {
                            case 'Working': $statusClass = 'status-working-tag'; break;
                            case 'Pending': $statusClass = 'status-pending-tag'; break;
                            case 'Completed': $statusClass = 'status-completed-tag'; break;
                            case 'Canceled': $statusClass = 'status-canceled-tag'; break;
                        }
                    }
                    ?>
                    <div class="profile-title">
                        <span class="status-tag <?= $statusClass ?>"><?= htmlspecialchars($displayStatus) ?></span>
                    </div>
                    <div class="profile-location">
                        <i class="fas fa-calendar-alt"></i>
                        <?= date('M d, Y', strtotime($selectedProject['PRO_START_DATE'])) ?> -
                        <?= date('M d, Y', strtotime($selectedProject['PRO_END_DATE'])) ?>
                    </div>
                </div>

                <div class="profile-actions">
                    <button class="profile-action-btn update-btn" onclick="editProject(<?= $selectedProject['PRO_ID'] ?>)">Edit Project</button>
                </div>

                <div class="profile-section">
                    <div class="profile-section-title">Project Details</div>

                    <!-- Progress Bar -->
                    <?php
                    $today = new DateTime();
                    $startDate = new DateTime($selectedProject['PRO_START_DATE']);
                    $endDate = new DateTime($selectedProject['PRO_END_DATE']);

                    $totalDays = $startDate->diff($endDate)->days;
                    $daysElapsed = $startDate->diff($today)->days;

                    if ($totalDays > 0) {
                        $progress = min(100, ($daysElapsed / $totalDays) * 100);
                    } else {
                        $progress = 100;
                    }
                    ?>
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $progress ?>%;"></div>
                        </div>
                        <div class="progress-text">
                            <span>Start: <?= date('M d, Y', strtotime($selectedProject['PRO_START_DATE'])) ?></span>
                            <span>Progress: <?= round($progress) ?>%</span>
                            <span>End: <?= date('M d, Y', strtotime($selectedProject['PRO_END_DATE'])) ?></span>
                        </div>
                    </div>

                    <div class="profile-info-row">
                        <div class="profile-info-label">Project ID:</div>
                        <div class="profile-info-value"><?= htmlspecialchars($selectedProject['PRO_ID']) ?></div>
                    </div>
                    <div class="profile-info-row">
                        <div class="profile-info-label">Type:</div>
                        <div class="profile-info-value"><?= htmlspecialchars($selectedProject['PRO_TYPE']) ?></div>
                    </div>
                    <div class="profile-info-row">
                        <div class="profile-info-label">Priority:</div>
                        <div class="profile-info-value"><?= htmlspecialchars($selectedProject['PRO_PRIORITY_LEVEL']) ?></div>
                    </div>
                    <div class="profile-info-row">
                        <div class="profile-info-label">Commissioned By:</div>
                        <div class="profile-info-value"><?= htmlspecialchars($selectedProject['PRO_COMMISSIONED_BY']) ?></div>
                    </div>
                    <div class="profile-info-row">
                        <div class="profile-info-label">Assigned To:</div>
                        <div class="profile-info-value">
                            <?= $selectedProject['USER_FSTNAME'] ? htmlspecialchars($selectedProject['USER_FSTNAME'] . ' ' . $selectedProject['USER_LSTNAME']) : 'Unassigned' ?>
                        </div>
                    </div>
                </div>

                <div class="profile-section">
                    <div class="profile-section-title">Description</div>
                    <p><?= nl2br(htmlspecialchars($selectedProject['PRO_DESCRIPTION'])) ?></p>
                </div>
            <?php else: ?>
                <div class="profile-header">
                    <a style="width: 100%; height: 100%;">
                        <i class="fas fa-project-diagram" style="font-size:6rem;"></i>
                    </a>
                    <p style="padding: 10px">Select a project from the table to view details.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add/Edit Project Modal -->
<div id="projectModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" id="closeModal">&times;</span>
        <h3 id="modalTitle">Add New Project</h3>
        <form id="projectForm" action="saveProject.php" method="post" class="modal-form">
            <input type="hidden" id="projectId" name="project_id" value="">

            <div class="form-group">
                <label for="projectTitle">Project Title*</label>
                <input type="text" id="projectTitle" name="project_title" required>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="projectType">Project Type*</label>
                    <select id="projectType" name="project_type" required>
                        <option value="">Select Type</option>
                        <option value="Development">Development</option>
                        <option value="Design">Design</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Content">Content</option>
                        <option value="Research">Research</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-col">
                    <label for="projectPriority">Priority Level*</label>
                    <select id="projectPriority" name="project_priority" required>
                        <option value="">Select Priority</option>
                        <option value="High">High</option>
                        <option value="Medium">Medium</option>
                        <option value="Low">Low</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="projectStartDate">Start Date*</label>
                    <input type="date" id="projectStartDate" name="project_start_date" required>
                </div>
                <div class="form-col">
                    <label for="projectEndDate">End Date*</label>
                    <input type="date" id="projectEndDate" name="project_end_date" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-col">
                    <label for="projectAssignee">Assign To</label>
                    <select id="projectAssignee" name="project_assignee">
                        <option value="">Select Team Member</option>
                        <?php
                        $teamMembers->execute();
                        while ($member = $teamMembers->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <option value="<?= $member['USER_ID'] ?>"><?= htmlspecialchars($member['USER_FSTNAME'] . ' ' . $member['USER_LSTNAME']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-col">
                    <label for="projectCommissionedBy">Commissioned By*</label>
                    <input type="text" id="projectCommissionedBy" name="project_commissioned_by" required>
                </div>
            </div>

            <div class="form-group">
                <label for="projectDescription">Description*</label>
                <textarea id="projectDescription" name="project_description" rows="4" required placeholder="Describe the project details..."></textarea>
            </div>

            <div class="form-group">
                <label for="projectStatus">Status</label>
                <select id="projectStatus" name="project_status">
                    <option value="Pending">Pending</option>
                    <option value="Working">Working</option>
                    <option value="Completed">Completed</option>
                    <option value="Canceled">Canceled</option>
                </select>
            </div>

            <div class="modal-footer">
                <button type="button" class="cancel-btn" id="cancelBtn">Cancel</button>
                <button type="submit" class="submit-btn">Save Project</button>
            </div>
        </form>
    </div>
</div>

<!-- Status Update Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <span class="close-modal" id="closeStatusModal">&times;</span>
        <h3>Update Project Status</h3>
        <form id="statusForm" action="updateStatus.php" method="post" class="modal-form">
            <input type="hidden" id="statusProjectId" name="project_id" value="">

            <div class="form-group">
                <label for="newStatus">New Status*</label>
                <select id="newStatus" name="new_status" required>
                    <option value="Pending">Pending</option>
                    <option value="Working">Working</option>
                    <option value="Completed">Completed</option>
                    <option value="Canceled">Canceled</option>
                </select>
            </div>

            <div class="form-group">
                <label for="statusReason">Reason for Change</label>
                <textarea id="statusReason" name="status_reason" rows="3" placeholder="Optional: Explain the reason for status change..."></textarea>
            </div>

            <div class="modal-footer">
                <button type="button" class="cancel-btn" id="cancelStatusBtn">Cancel</button>
                <button type="submit" class="submit-btn">Update Status</button>
            </div>
        </form>
    </div>
</div>

<script src="projScript.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('.delete-btn').on('click', function () {
            if (!confirm("Are you sure you want to delete this project?")) return;

            const form = $(this).closest('.delete-form');
            const projectId = form.data('id');

            $.ajax({
                url: 'deleteProject.php',
                type: 'POST',
                data: { project_id: projectId },
                success: function (response) {
                    // Optional: show toast or alert here
                    location.reload(); // Reload the page to reflect changes
                },
                error: function () {
                    alert("Failed to delete project.");
                }
            });
        });
    });
    function editProject(projectId) {
        // Fetch project data using AJAX
        $.ajax({
            url: 'getProject.php',
            type: 'GET',
            data: { id: projectId },
            dataType: 'json',
            success: function(project) {
                // Populate the form with project data
                document.getElementById('projectId').value = project.PRO_ID;
                document.getElementById('projectTitle').value = project.PRO_TITLE;
                document.getElementById('projectType').value = project.PRO_TYPE;
                document.getElementById('projectPriority').value = project.PRO_PRIORITY_LEVEL;
                document.getElementById('projectStartDate').value = project.PRO_START_DATE;
                document.getElementById('projectEndDate').value = project.PRO_END_DATE;
                document.getElementById('projectAssignee').value = project.USER_ID || '';
                document.getElementById('projectCommissionedBy').value = project.PRO_COMMISSIONED_BY;
                document.getElementById('projectDescription').value = project.PRO_DESCRIPTION;
                document.getElementById('projectStatus').value = project.PRO_STATUS;

                // Update modal title
                document.getElementById('modalTitle').textContent = 'Edit Project';

                // Show the modal
                document.getElementById('projectModal').style.display = 'block';
            },
            error: function() {
                alert('Failed to load project data');
            }
        });
    }
    $(document).ready(function () {
        $('#projectForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const isEdit = document.getElementById('projectId').value !== '';

            $.ajax({
                url: isEdit ? 'updateProject.php' : 'saveProject.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    document.getElementById('projectModal').style.display = 'none';
                    location.reload();
                },
                error: function() {
                    alert('Failed to save project');
                }
            });
        });
        $('.delete-btn').on('click', function () {
            if (!confirm("Are you sure you want to delete this project?")) return;

            const form = $(this).closest('.delete-form');
            const projectId = form.data('id');

            $.ajax({
                url: 'deleteProject.php',
                type: 'POST',
                data: { project_id: projectId },
                success: function (response) {
                    location.reload();
                },
                error: function () {
                    alert("Failed to delete project.");
                }
            });
        });
    });
</script>

</body>
</html>