<?php
    require 'config.php';
    $db = new mysqli($db['servidor'], $db['user'], $db['password'], $db['db'], $db['porta']);
    if ($db->connect_error) {
        die("Ligação ao servidor falhou: " . $db->connect_error);
    }
    $db->set_charset("utf8");

?>