
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/x-icon" href="../imgs/inlFavicon@4x.png">
    <link rel="stylesheet" href="freelancer-style.css">
    <title>Project</title>
</head>
<body>
<?php include 'freelancer-navbar-template.php' ?>

<div class="container">
    <div class="content">
        <div class="project-card">
            <div class="project-card-content">
                <h1>Projects</h1>
                <table style="width:100%; margin-top: 15px;" class="table">
                    <tr>
                        <th>Project Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Urgency</th>
                        <th>Commissioned By</th>
                        <th>Edit/Update</th>
                    </tr>
                    <tr>
                        <td>Placeholder</td>
                        <td>Placeholder</td>
                        <td>Placeholder</td>
                        <td>Placeholder</td>
                        <td>Placeholder</td>
                        <td>
                            <form action="freelancer-edit-project.php" method="post">
                                <button class="btn" id="btn-edit" name="user_id" value="">Edit</button>
                            </form>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

</div>
</body>
</html>

