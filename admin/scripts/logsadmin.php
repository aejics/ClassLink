<?php require '../index.php'; ?>
<?php
$sql = "SELECT * FROM logs ORDER BY timestamp DESC";
$result = $db->query($sql);
echo '<div><h2>Logs Administrativos</h2></div>';
echo '<div style="max-height:100vh; overflow-y:auto; border:1px solid #ccc;">';
echo '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse:collapse;">';
echo '<tr><th>ID</th><th>User</th><th>Action</th><th>Timestamp</th></tr>';

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['id']) . '</td>';
        echo '<td>' . htmlspecialchars($row['userid']) . '</td>';
        echo '<td>' . htmlspecialchars($row['loginfo']) . '</td>';
        echo '<td>' . htmlspecialchars($row['timestamp']) . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="4">No logs found.</td></tr>';
}

echo '</table>';
echo '</div>';
?>
