<?php
function logaction(string $loginfo, string $userid){
    require_once(__DIR__ . '/../func/genuuid.php');
    require_once(__DIR__ . "/../src/db.php");
    global $db;

    $id = uuid4();
    $stmt = $db->prepare("INSERT INTO logs (id, loginfo, userid) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $id, $loginfo, $userid);
    $stmt->execute();
    $stmt->close();
};
?>
