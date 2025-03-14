<?php
    require 'config.php';
    //talvez isto seja mudado mais tarde
    $db = new mysqli($db['servidor'], $db['user'], $db['password'], $db['db'], $db['porta']);
    echo $db->host_info . "\n";
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }
?>