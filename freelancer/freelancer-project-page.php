<?php
include 'freelancer-navbar-template.php';
include_once 'interlinkedDB.php';
include_once 'SecurityCheck.php';

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
                </div>


                <table style="width:100%; margin-top: 15px;" class="table" id="projectsTable">
                    <thead>
                    <tr>
                        <th onclick="sortTable(0)">Project Title</th>
                        <th onclick="sortTable(1)">Description</th>
                        <th onclick="sortTable(2)">Type</th>
                        <th onclick="sortTable(3)">Date Start</th>
                        <th onclick="sortTable(4)">Due Date</th>
                        <th onclick="sortTable(5)">Status</th>
                        <th onclick="sortTable(6)">Priority Level</th>
                        <th onclick="sortTable(7)">Commissioned By</th>
                        <th>Edit/Update</th>
                    </tr>
                    </thead>
                    <tbody>
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
                    </tbody>
                </table>


                <script>
                    let sortDirection = [];

                    function sortTable(columnIndex) {
                        const table = document.getElementById("projectsTable");
                        const tbody = table.tBodies[0];
                        const rows = Array.from(tbody.rows);
                        const isPriority = columnIndex === 6;

                        // Toggle sort direction
                        sortDirection[columnIndex] = !sortDirection[columnIndex];

                        const priorityMap = {
                            'high': 3,
                            'medium': 2,
                            'low': 1
                        };

                        rows.sort((a, b) => {
                            let valA = a.cells[columnIndex].innerText.trim().toLowerCase();
                            let valB = b.cells[columnIndex].innerText.trim().toLowerCase();

                            if (isPriority) {
                                valA = priorityMap[valA] || 0;
                                valB = priorityMap[valB] || 0;
                            } else if (!isNaN(Date.parse(valA)) && !isNaN(Date.parse(valB))) {
                                valA = new Date(valA);
                                valB = new Date(valB);
                            }

                            if (valA < valB) return sortDirection[columnIndex] ? -1 : 1;
                            if (valA > valB) return sortDirection[columnIndex] ? 1 : -1;
                            return 0;
                        });

                        rows.forEach(row => tbody.appendChild(row));
                    }
                </script>


            </div>
        </div>
    </div>
</div>
</body>
</html>

