<?php
include 'freelancer-navbar-template.php';
include_once 'interlinkedDB.php';
$master_con = connectToDatabase(3006);
$slave_con = connectToDatabase(3007);

$userName = $_SESSION['userName'];
$stmt = $slave_con->prepare("SELECT * FROM user where USER_NAME = ?");
$stmt->execute([$userName]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as $res) {
    $_SESSION['USER_ID'] = $res['USER_ID'];
}

$id = $_SESSION['USER_ID'];


$stmt = $slave_con->prepare("SELECT * FROM projects where USER_ID = ?");
$stmt->execute([$id]);
$pro = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
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

<div class="container">
    <div class="content">
        <div class="project-card">
            <div class="project-card-content">
                <div class="label">
                    <h1>PROJECTS</h1>
<!--                    <h1>--><?php //=$id?><!--</h1>-->
                    <button id="sortPriorityBtn" class="btn-sort"><i class="fa-solid fa-sort"></i></button>
                </div>


                <table style="width:100%; margin-top: 15px;" class="table" id="projectsTable">
                    <tr>
                        <th>Project Title</th>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Date Start</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Priority Level</th>
                        <th>Commissioned By</th>
                        <th>Edit/Update</th>
                    </tr>
                    <?php foreach ($pro as $res) { ?>
                        <tr>
                            <td><?= htmlspecialchars($res['PRO_TITLE']) ?></td>
                            <td><?= htmlspecialchars($res['PRO_DESCRIPTION']) ?></td>
                            <td><?= htmlspecialchars($res['PRO_TYPE']) ?></td>
                            <td><?= htmlspecialchars($res['PRO_START_DATE']) ?></td>
                            <td><?= htmlspecialchars($res['PRO_END_DATE']) ?></td>
                            <td><?= htmlspecialchars($res['PRO_STATUS']) ?></td>
                            <td>
                                <?php
                                $priority = strtolower($res['PRO_PRIORITY_LEVEL']);
                                $class = '';
                                if ($priority === 'high') {
                                    $class = 'priority-high';
                                } elseif ($priority === 'medium') {
                                    $class = 'priority-medium';
                                } elseif ($priority === 'low') {
                                    $class = 'priority-low';
                                }
                                ?>
                                <span class="priority-badge <?= $class ?>"><?= htmlspecialchars($res['PRO_PRIORITY_LEVEL']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($res['PRO_COMMISSIONED_BY']) ?></td>
                            <td>
                                <form action="freelancer-edit-project.php" method="post">
                                    <button class="btn-project" id="btn-edit" name="PRO_ID" value="<?= $res['PRO_ID'] ?>">Edit</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </table>

                <script>
                    const sortBtn = document.getElementById('sortPriorityBtn');
                    const table = document.getElementById('projectsTable');
                    let asc = true; // toggle order

                    // Priority mapping to sort easily
                    const priorityMap = {
                        'high': 3,
                        'medium': 2,
                        'low': 1
                    };

                    sortBtn.addEventListener('click', () => {
                        const rowsArray = Array.from(table.rows).slice(1); // skip header row

                        rowsArray.sort((a, b) => {
                            // Get the priority text (7th cell index = 6)
                            const priorityA = a.cells[6].innerText.trim().toLowerCase();
                            const priorityB = b.cells[6].innerText.trim().toLowerCase();

                            // Use priorityMap to compare numeric values
                            const valA = priorityMap[priorityA] || 0;
                            const valB = priorityMap[priorityB] || 0;

                            return asc ? valB - valA : valA - valB; // Desc or asc
                        });

                        // Append sorted rows back to the table body
                        rowsArray.forEach(row => table.appendChild(row));

                        asc = !asc; // toggle sorting order for next click
                    });
                </script>

            </div>
        </div>
    </div>
</div>
</body>
</html>

