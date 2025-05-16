    <?php
    session_start();
    include_once 'interlinkedDB.php';
    $master_con = connectToDatabase(3006);
    $slave_con = connectToDatabase(3007);

    var_dump($_SESSION['user_id']);

    $id = 1;

    $stmt = $slave_con->prepare("SELECT * FROM projects where PRO_ID = ?");
    $stmt->execute([$id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $project) {
        echo $project['PRO_TITLE'] . "<br>";
        echo $project['PRO_DESCRIPTION'] . "<br>";
        echo $project['PRO_ID'] . "<br>";
        echo $project['PRO_COMISSIONED_BY'] . "<br>";
        echo $project['PRO_TYPE'] . "<br>";
        echo $project['PRO_START_DATE'] . "<br>";
        echo $project['PRO_END_DATE'] . "<br>";
    }

    if (empty($result)) {
        echo "No projects found or user ID doesn't match any project.";
    }