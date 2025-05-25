<?php
session_start();
include('interlinkedDB.php');
include_once 'SecurityCheck.php';
$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);

$error = [];
$success = [];

function clean_text($data) {
    return htmlspecialchars(trim($data));
}

$pro_Id = $_POST['PRO_ID'] ?? null;

if (!$pro_Id) {
    die("Project ID is missing.");
}

// Fetch existing data to validate readonly fields and for display
$stmt = $slave_con->prepare("SELECT * FROM projects WHERE PRO_ID = :id");
$stmt->execute(['id' => $pro_Id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    die("Project not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {

    // Validate readonly fields against DB values to prevent tampering
    $readonlyFields = [
        'userId' => $result['PRO_TITLE'],
        'userName' => $result['PRO_DESCRIPTION'],
        'commissionedBy' => $result['PRO_COMMISSIONED_BY'],
        'email' => $result['PRO_TYPE'],
        'date-start' => $result['PRO_START_DATE'],
        'due' => $result['PRO_END_DATE']
    ];

    foreach ($readonlyFields as $field => $expected) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) !== trim($expected)) {
            $error[] = "Invalid or modified data detected in field: $field.";
        }
    }

    // Validate Status - must be one of these
    $validStatuses = ['Submitted', 'Pending', 'Completed', 'Cancelled'];
    $status = $_POST['status'] ?? '';
    if (!in_array($status, $validStatuses)) {
        $error[] = "Invalid status selected.";
    }

    // Validate Priority - must be one of these
    $validPriorities = ['High', 'Medium', 'Low'];
    $priority = $_POST['priority'] ?? '';
    if (!in_array($priority, $validPriorities)) {
        $error[] = "Invalid priority level selected.";
    }

    if (empty($error)) {
        // Update only status and priority level, as other fields are readonly and validated
        $updateStmt = $master_con->prepare("UPDATE projects SET PRO_STATUS = :status, PRO_PRIORITY_LEVEL = :priority WHERE PRO_ID = :id");
        $updated = $updateStmt->execute([
            ':status' => $status,
            ':priority' => $priority,
            ':id' => $pro_Id
        ]);

        if ($updated) {
            $success[] = "Project updated successfully.";
            // Refresh $result to show updated values in the form
            $stmt = $slave_con->prepare("SELECT * FROM projects WHERE PRO_ID = :id");
            $stmt->execute(['id' => $pro_Id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error[] = "Failed to update project.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | INTERLINKED</title>
    <link rel="icon" href="../imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="freelancer-FormSignStyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
</head>
<body>

<div class="rectangle2"></div>
<div class="rectangle3"></div>

<div class="topvar">
    <div class="logo">
        <img src="../imgs/inl2Logo.png" alt="INTERLINKED Logo">
    </div>
</div>

<div class="container2">
    <div class="content2">
        <h1>UPDATE TASK</h1>
        <p class="credentials">Please update your task</p>
        <div class="form-container">
            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="PRO_ID" value="<?= htmlspecialchars($result['PRO_ID']) ?>">

                <div class="form-group">
                    <div>
                        <label for="userId">Project Name</label>
                        <input type="text" id="userId" name="userId" value="<?=$result['PRO_TITLE']?>" readonly>
                    </div>

                    <div class="description">
                        <label for="userName">Description</label>
                        <textarea id="userName" name="userName" readonly rows="5" cols="40"><?= htmlspecialchars($result['PRO_DESCRIPTION']) ?></textarea>
                    </div>

                </div>

                <div class="form-group">
                    <div>
                        <label for="email">Commissioned By</label>
                        <input type="email" id="commissioned" name="commissionedBy" value="<?=$result['PRO_COMMISSIONED_BY']?>" readonly>
                    </div>
                    <div>
                        <label for="email">Type</label>
                        <input type="text" id="email" name="email" value="<?=$result['PRO_TYPE']?>" readonly>
                    </div>

                </div>

                <div class="form-group">
                    <div>
                        <label for="date">Date Start</label>
                        <input type="text" id="text" name="date-start" value="<?=$result['PRO_START_DATE']?>" readonly>
                    </div>
                    <div>
                        <label for="due">Due Date</label>
                        <input type="text" id="text" name="due" value="<?=$result['PRO_END_DATE']?>" readonly>
                    </div>
                </div>


                <div class="form-group">
                    <div class="cus-select">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="Submitted" <?= ($result['PRO_STATUS'] === 'Submitted') ? 'selected' : '' ?>>Submitted</option>
                            <option value="Pending" <?= ($result['PRO_STATUS'] === 'Pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="Completed" <?= ($result['PRO_STATUS'] === 'Completed') ? 'selected' : '' ?>>Completed</option>
                            <option value="Cancelled" <?= ($result['PRO_STATUS'] === 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="cus-select">
                        <label for="urgent">Priority Level</label>
                        <select name="priority" id="priority">
                            <option value="High" <?= ($result['PRO_PRIORITY_LEVEL'] === 'High') ? 'selected' : '' ?>>High</option>
                            <option value="Medium" <?= ($result['PRO_PRIORITY_LEVEL'] === 'Medium') ? 'selected' : '' ?>>Medium</option>
                            <option value="Low" <?= ($result['PRO_PRIORITY_LEVEL'] === 'Low') ? 'selected' : '' ?>>Low</option>
                        </select>
                    </div>

                </div>



                <div class="form-buttons">
                    <button type="submit" name="action" value="update">Update</button>
                    <button type="button" onclick="window.location.href='freelancer-project-page.php';" value="goBack">◄ Go Back</button><br>
                    <span style="color: red"><?php
                        foreach ($error as $errorMsg) {
                            echo $errorMsg . "<br>";
                        }
                        ?>
                    </span>

                    <span style="color: #88bb80">
                        <?php foreach ($success as $suc) {
                            echo $suc . "<br>";
                        }
                        ?>
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
