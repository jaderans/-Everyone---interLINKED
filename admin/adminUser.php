<?php
include('interlinkedDB.php');

// Default search
$search = $_GET['search'] ?? '';

function fetchUsers($conn, $type, $search) {
    $typeCondition = $type === 'Applicant' ? "= 'Applicant'" : "!= 'Applicant'";
    $searchCondition = $search ? "AND (USER_FSTNAME LIKE '%$search%' OR USER_LSTNAME LIKE '%$search%' OR USER_EMAIL LIKE '%$search%')" : "";
    return mysqli_query($conn, "SELECT * FROM user WHERE USER_TYPE $typeCondition $searchCondition");
}

$usersResult = fetchUsers($conn, 'User', $search);
$applicantsResult = fetchUsers($conn, 'Applicant', $search);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | interLINKED</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/x-icon" href="../imgs/inlFaviconwhite.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .admin-panel {
            display: flex;
            height: 85vh;
            padding: 20px;
            margin-left: 250px;
        }
        .left-pane {
            width: 55%;
            overflow-y: auto;
            padding-right: 20px;
        }
        .right-pane {
            flex: 1;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0px 0px 30px rgba(32, 32, 32, 0.37);
        }
        .search-box {
            margin-bottom: 20px;
        }
        .search-box input {
            padding: 10px;
            width: 100%;
            border: 2px solid #4a8c8a;
            border-radius: 10px;
            font-size: 14px;
        }
        table {
            width: 100%;
            font-size: 12px;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 8px;
            border: 1px solid #ccc;
        }
        .highlight {
            background-color: #f3f3f3;
        }
    </style>
</head>
<body>
<div class="navbar">
    <div class="topvar">
        <div class="navtitle">
            <h1>Admin | <br></h1><p style="padding-left: 8px">User Management</p>
        </div>
        <div class="navprofile">
            <div class="name"><h4>Furina</h4><p style="font-size: 12px">Admin</p></div>
            <div class="profile"><img src="../imgs/profile.png" alt=""></div>
        </div>
    </div>
</div>
<div class="sidebar">
    <div class="topvar2">
        <div class="logo"><img src="../imgs/inl2LogoWhite.png" alt="Logo"></div>
    </div>
    <div class="navbuttons">
        <ul class="side-content">
            <li><a href="adminDash.php"><i class="fas fa-database"></i> Dashboard</a></li>
            <li><a href="adminProj.php"><i class="fas fa-project-diagram"></i> Projects</a></li>
            <li><a href="adminPay.php"><i class="fas fa-dollar-sign"></i> Salary</a></li>
            <li><a href="adminUser.php"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="adminMes.php"><i class="fas fa-envelope"></i> Message</a></li>
            <li><a href="adminProf.php"><i class="fas fa-user"></i> Profile</a></li>
        </ul>
    </div>
</div>

<div class="admin-panel">
    <div class="left-pane">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search users/applicants by name or email...">
        </div>

        <div class="content highlight">
            <h2>Users</h2>
            <table id="userTable">
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Type</th><th>Contact</th><th>Actions</th>
                </tr>
                <?php while($row = mysqli_fetch_assoc($usersResult)): ?>
                    <tr>
                        <td><?= $row['USER_ID'] ?></td>
                        <td><?= $row['USER_FSTNAME'] . " " . $row['USER_LSTNAME'] ?></td>
                        <td><?= $row['USER_EMAIL'] ?></td>
                        <td><?= $row['USER_TYPE'] ?></td>
                        <td><?= $row['USER_CONTACT'] ?></td>
                        <td>
                            <a href="editUser.php?id=<?= $row['USER_ID'] ?>">Edit</a> |
                            <a href="deleteUser.php?id=<?= $row['USER_ID'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <div class="content" style="margin-top: 30px;">
            <h2>Applicants</h2>
            <table id="applicantTable">
                <tr>
                    <th>ID</th><th>Name</th><th>Email</th><th>Type</th><th>Contact</th>
                </tr>
                <?php while($row = mysqli_fetch_assoc($applicantsResult)): ?>
                    <tr>
                        <td><?= $row['USER_ID'] ?></td>
                        <td><?= $row['USER_FSTNAME'] . " " . $row['USER_LSTNAME'] ?></td>
                        <td><?= $row['USER_EMAIL'] ?></td>
                        <td><?= $row['USER_TYPE'] ?></td>
                        <td><?= $row['USER_CONTACT'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>

    <div class="right-pane" id="profileDetails">
        <h2>User Profile</h2>
        <p>Select a user to view/edit profile.</p>
    </div>
</div>

<script>
    $('#searchInput').on('input', function () {
        const query = $(this).val();
        window.location.href = `adminUser.php?search=${encodeURIComponent(query)}`;
    });

    $('a[href^="editUser.php"]').on('click', function (e) {
        e.preventDefault();
        const url = $(this).attr('href');
        $('#profileDetails').html('Loading...');
        $.get(url, function (data) {
            $('#profileDetails').html(data);
        });
    });
</script>
</body>
</html>
