<?php
// .....::.............
// ....:.:.............
// ....:=:.............
// ....:-..............
// .::==-=-::==:=-..... AEJICS GIAE CONNECT
// ::...-:.::-+*+:..:.. INTEGRATION
// .....-:....-+:..:-:. 
// .....-:..:---:..:-.. V2.0 (sim, em módulo desta vez)
// ..::..:-..:::::....: by marco pisco - marco@marcopisco.com - @marpisco
// ..:.....::....:.....
// ..:::..........::...

// Este módulo depende da versão mais recente do GIAEConnect do itsjuoum.

function loginGiae($user, $pass){
    require 'giaeConnect.php';
    $giaeConnect = new \juoum\GiaeConnect\GiaeConnect("giae.aejics.org");
    $sessao = $giaeConnect->getSession($user, $pass);
    $giaeConnect->session=$sessao;
    if (str_contains($giaeConnect->getConfInfo(), 'Erro do Servidor')){
        setcookie("token", "", time() - 3600, "/");
        die("A sua sessão é inválida.");
    } else {
        setcookie("token", $sessao, time() + 3600, "/");
        header("Location: /");
    }
}

function validateSessaoValida($token){
    $giaeconnect->session=$token;
    if (str_contains($giaeconnect->getConfInfo(), 'Erro do Servidor')){
        // Esta ação pode levar trigger com um login noutro separador do GIAE.
        setcookie("token", "", time() - 3600, "/");
        die("A sua sessão é inválida.");
    }
}

function getDataFromUser($token){
    // buscar dados ao GIAE
    // adicionar à "cache do giae"
}
?>