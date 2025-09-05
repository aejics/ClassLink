<?php
function logaction(string $loginfo, string $tokenid){
    require_once(__DIR__ . '/../func/genuuid.php');
    require_once(__DIR__ . "/../src/db.php");
    global $db;

    $id = uuid4();
    // prepare statement
    $horaatual = time();
    $tempoatual > $validotill;
    $whosetoken = $db->query("SELECT id FROM tokens WHERE validotill > '". $horaatual ."' AND token = '" . $tokenid . "';");
    $userid = null;
    if ($whosetoken && $whosetoken->num_rows > 0) {
        $row = $whosetoken->fetch_assoc();
        $userid = $row['id'];
    }

    // enviar o statement
    $stmt = $db->prepare("INSERT INTO logs (id, loginfo, userid) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $id, $loginfo, $userid);
    $stmt->execute();
    $stmt->close();
};
?>
