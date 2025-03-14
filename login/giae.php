<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function checkValidSession($session){
    require_once(__DIR__ . "/../vendor/autoload.php");
    require(__DIR__ . '/../src/config.php');
    $giaeconnect = new \juoum\GiaeConnect\GiaeConnect($giae['servidor']);
    $giaeconnect->session=$session;
    if (str_contains($giaeconnect->getConfInfo(), 'Erro do Servidor')){
        setcookie("loggedin", "", time() - 3600, "/");
        die("<div class='alert alert-danger text-center' role='alert'>A sua sessão expirou.</div>
        <div class='text-center'>
        <a href='/login/'><button type='button' class='btn btn-success w-100'>Voltar a iniciar sessão</button></a></div>");
    }
}
?>