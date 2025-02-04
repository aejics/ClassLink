<?php
    include 'src/login.php';
    require_once(__DIR__ . "/vendor/autoload.php");
    $session = filter_input(INPUT_COOKIE, 'session', FILTER_UNSAFE_RAW);
    $giae = new \juoum\GiaeConnect\GiaeConnect("giae.aejics.org");
    $giae->session=$session;
    echo mb_convert_encoding($giae->getHorario(), 'UTF-8', 'auto');
?>