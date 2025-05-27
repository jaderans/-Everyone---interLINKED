<?php
include('interlinkedDB.php');
$conn = connectToDatabase();

$search = $_GET['search'] ?? '';

$query = "SELECT p.*, u.USER_FSTNAME, u.USER_LSTNAME 
          FROM projects p 
          LEFT JOIN user u ON p.USER_ID = u.USER_ID";

if (!empty($search)) {
    $query .= " WHERE p.PRO_TITLE LIKE :search OR p.PRO_DESCRIPTION LIKE :search";
}

$query .= " ORDER BY p.PRO_END_DATE ASC";

$stmt = $conn->prepare($query);

if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%");
}

$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($results) {
    foreach ($results as $project) {
        echo "<tr>
                <td>" . htmlspecialchars($project['PRO_TITLE']) . "</td>
                <td>" . htmlspecialchars($project['PRO_DESCRIPTION']) . "</td>
                <td>" . htmlspecialchars($project['USER_FSTNAME'] . ' ' . $project['USER_LSTNAME']) . "</td>
                <td>" . htmlspecialchars($project['PRO_STATUS']) . "</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='8'>No matching projects found.</td></tr>";
}
?>
