<?php
session_start();
include('interlinkedDB.php');
$master_con = connectToDatabase(3306);
$slave_con = connectToDatabase(3307);

$error = [];
$success = [];

function clean_text($data) {
    return htmlspecialchars(trim($data));
}


$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'psd', 'ai', 'mp4', 'mov', 'avi', 'mkv', 'doc', 'docx', 'ppt', 'pptx'];
$allowedMimeTypes = [
    'image/jpeg',
    'image/png',
    'application/pdf',
    'image/vnd.adobe.photoshop',       // PSD
    'application/postscript',          // AI (Illustrator may also return PDF or EPS)
    'video/mp4',
    'video/quicktime',                 // MOV
    'video/x-msvideo',                 // AVI
    'video/x-matroska',                 // MKV
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // DOCX
    'application/vnd.ms-powerpoint',   // PPT
    'application/vnd.openxmlformats-officedocument.presentationml.presentation' // PPTX
];

$uploadDir = 'uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        $fileType = mime_content_type($fileTmpPath);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Check extension and MIME type
        if (in_array($fileExt, $allowedExtensions) && in_array($fileType, $allowedMimeTypes)) {
            // Sanitize filename
            $safeFileName = uniqid() . '-' . basename($fileName);
            $destination = $uploadDir . $safeFileName;

            // Ensure upload directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Move the file
            if (move_uploaded_file($fileTmpPath, $destination)) {
                $success[] = "File uploaded successfully as: $safeFileName";
            } else {
                $error[] = "Error moving the uploaded file.";
            }
        } else {
            $error[] = "Invalid file type.";
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
            <div class="form-group">
                    <div>
                        <label for="userId">Project Name</label>
                        <input type="text" id="userId" name="userId" value="" readonly>
                    </div>

                    <div class="description">
                        <label for="userName">Description</label>
                        <input type="text" id="userName" name="userName" value="" readonly>
                    </div>

                </div>

                <div class="form-group">
                    <div>
                        <label for="email">Commissioned By</label>
                        <input type="email" id="email" name="email" value="" readonly>
                    </div>
                    <div>
                        <label for="file">Submit File</label>
                        <input class="attach" type="file" id="file" name="file">
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="email">Date Start</label>
                        <input type="email" id="text" name="date-start" value="" readonly>
                    </div>
                    <div>
                        <label for="file">Due Date</label>
                        <input class="attach" type="text" id="due" name="file" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <div>
                        <label for="email">Type</label>
                        <input type="text" id="email" name="email" value="" readonly>
                    </div>
                </div>

                <div class="form-group">
                    <div class="cus-select">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="Ongoing">Ongoing</option>
                            <option value="Drop">Drop</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    <div class="cus-select">
                        <label for="urgent">Priority Level</label>
                        <select name="priority" id="priority">
                            <option value="Moderate">Moderate</option>
                            <option value="Urgent">Urgent</option>
                            <option value="Flexible">Flexible</option>
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
