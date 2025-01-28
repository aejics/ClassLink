<?php
    require_once(__DIR__ . "/vendor/autoload.php");
    if (isset($_COOKIE["loggedin"])){
        $giae = new \juoum\GiaeConnect\GiaeConnect("giae.aejics.org");
        $giae->session=$_COOKIE["session"];
        if (str_contains($giae->getConfInfo(), 'Erro do Servidor')){
            header('Location: /logout.php');
        }
    }
?>