<?php
    require_once(__DIR__ . "/vendor/autoload.php");
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);  
    $giae = new \juoum\GiaeConnect\GiaeConnect("giae.aejics.org");
    $giae->session=$_COOKIE["session"];
    print($giae->getConfInfo());
?>