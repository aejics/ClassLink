<?php require 'config.php'; require 'db.php'; ?>
<!DOCTYPE html>
<html lang='pt'>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title><?php echo $info['nome']; ?></title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="/src/main.css" rel="stylesheet">
    <link rel='icon' href='/src/logo.png'>
</head>
<body>
    <?php
        if ($mensagem['ativada']){
            echo("<div class='alert alert-{$mensagem['tipo']} text-center' role='alert'>{$mensagem['mensagem']}</div>");
        }
    ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-success text-white justify-content-center">
        <a class="navbar-brand" href="/"><img src="/src/logo.png" style="max-width: 1.8em">  <span class="text-white"><?php echo $info['nome']; ?></span></a>
        <?php
            if ($_COOKIE['loggedin']) {
                $user = filter_var($_COOKIE['user'], FILTER_SANITIZE_STRING);
                require(__DIR__ .'/../login/giae.php');
                // temporariamente desativado para acelerar o desenvolvimento
                //checkValidSession($_COOKIE['session']);
                $isAdmin = $db->query("SELECT * FROM admins WHERE id = '$user' AND permitido = 1;")->num_rows;
                echo "<div class='dropdown'>
                        <button class='btn dropdown-toggle text-white' style='background-color:rgb(2, 152, 7);' type='button' id='areaMenuButton' data-bs-toggle='dropdown' aria-expanded='false'>
                        <img class='fotoutente' src='https://{$giae['servidor']}/{$_COOKIE['userpic']}'>  A Minha Área
                        </button>
                        <ul class='dropdown-menu' aria-labelledby='dropdownMenuButton'>";
                barraMenuLink("/", "As Minhas Reservas", false);
                barraMenuLink("/reservas", "Reservar uma Sala", false);
                barraMenuLink("/admin", "Painel Administrativo", true);
                barraMenuLink("/login?logout=1", "Terminar sessão", false);
                echo "</ul></div>";
            }

            function barraMenuLink($url, $nome, $admin){
                global $isAdmin;
                if ($admin){
                    if ($isAdmin) {
                        echo "<li><a class='dropdown-item' href='$url'>$nome</a></li>";
                    } else {
                        return;
                    }
                } else {
                    echo "<li><a class='dropdown-item' href='$url'>$nome</a></li>";
                }
            }
        ?>
    </nav></a>
    <br>
    <div class='h-100 d-flex align-items-center justify-content-center flex-column'>