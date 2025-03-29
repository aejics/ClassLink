<?php
function checkValidSession($session){
    require_once(__DIR__ . "/../vendor/autoload.php");
    require(__DIR__ . '/../src/config.php');
    $giaeconnect = new \juoum\GiaeConnect\GiaeConnect($giae['servidor']);
    $giaeconnect->session=$session;
    if (str_contains($giaeconnect->getConfInfo(), 'Erro do Servidor')){
        setcookie("loggedin", "", time() - 3600, "/");
        die("</nav><div class='h-100 d-flex align-items-center justify-content-center flex-column'><div class='mt-2 alert alert-danger text-center' role='alert'>A sua sessão expirou.</div>
        <div class='text-center'>
        <a href='/login/'><button type='button' class='btn btn-success w-100'>Voltar a iniciar sessão</button></a></div></div>");
    }
}
?>