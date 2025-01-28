<?php
    if (isset($_COOKIE["loggedin"])){
        $giae = new \juoum\GiaeConnect\GiaeConnect("giae.aejics.org", $_POST["user"], $_POST["pass"]);
        $config = json_decode($giae->getConfInfo(), true);
        if (str_contains($giae->getConfInfo(), 'Erro do Servidor')){
            header('Location: /logout.php');
        }
    }
?>