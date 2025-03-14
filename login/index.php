<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);


    require_once(__DIR__ . "/../vendor/autoload.php");
    require '../src/config.php';
    require '../src/db.php';
    require '../src/base.php';
    $giaeconnect = new \juoum\GiaeConnect\GiaeConnect($giae['servidor']);
    if ($_GET['logout'] == 'true'){
        $giaeconnect->session = $_COOKIE['session'];
        $giaeconnect->logout();
        setcookie("loggedin", "", time() - 3600, "/");
        die("<div class='alert alert-success text-center' role='alert'>A sua sessão foi terminada com sucesso.</div>
        <div class='text-center'>
        <a href='/login/'><button type='button' class='btn btn-success w-100'>Voltar a iniciar sessão</button></a></div>");
    }
    if ($_POST){
        $user = filter_input(INPUT_POST, 'user', FILTER_UNSAFE_RAW);
        $pass = filter_input(INPUT_POST, 'pass', FILTER_UNSAFE_RAW);
        $giaeconnect->session = $giaeconnect->getSession($user, $pass);
        $config = $giaeconnect->getConfInfo();
        if (strpos($config, 'Erro do Servidor') !== false){
            die("<div class='alert alert-danger text-center' role='alert'>A sua palavra-passe está errada.</div>
            <div class='text-center'>
            <button type='button' class='btn btn-success w-100' onclick='history.back()'>Voltar</button></div>");
        } else {
            $perfil = json_decode($giaeconnect->getPerfil(), true);
            $config = json_decode($config, true);
            setcookie("loggedin", true, time() + 3599, "/");
            setcookie("session", $giaeconnect->session, time() + 3599, "/");
            setcookie("user", $user, time() + 3599, "/");
            setcookie("userpic", $config['fotoutente'], time() + 3599, "/");
            $stmt = $db->prepare("INSERT IGNORE INTO cache_giae(id, nome, nomecompleto, email) VALUES (?, ?, ?, ?);");
            $stmt->bind_param("ssss", $user, $config['nomeutilizador'], $perfil['perfil']['nome'], $perfil['perfil']['email']);
            $stmt->execute();
            header('Location: /');
        }
    }
?>

<div class='h-100 d-flex align-items-center justify-content-center flex-column'>
    <p class='h2 mb-4'>Autentique-se via GIAE</p>
    <p class='mb-4'>Utilize as credenciais do GIAE para continuar para <b>ReservaSalas</b></p>
    <main class='form-signin w-100 m-auto'>
        <form action='/login/' method='POST' class='w-200' style='max-width: 600px;'>
            <div class='form-floating'>
                <input type='text' class='form-control' id='user' name='user' required placeholder='fxxxx ou axxxxx'>
                <label for='user' class='form-label'>Nome de utilizador</label>
            </div>
            <br>
            <div class='form-floating'>
                <input type='password' class='form-control' id='pass' name='pass' required placeholder='********'>
                <label for='pass' class='form-label'>Palavra-passe</label>
            </div>
            <br> 
            <button type='submit' class='btn btn-success w-100'>Iniciar sessão</button>
            <hr>
        </form>
    </main>
    <p class='h6'><i>Problemas a fazer login? Contacte o Apoio Informático.</i></p>
</div>