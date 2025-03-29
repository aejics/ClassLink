<?php
    // Redirecionar a login caso não exista token
    if (!isset($_COOKIE['token']) && $_SERVER['REMOTE_ADDR'] != "127.0.0.1"){
        http_response_code(403);
        header("Location: /login");
        die("Não tem a sessão iniciada.");
    } else {

    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva Salas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/assets/index.css">
</head>
<body>
    <nav>
        <a href=""><img src="/assets/logo.png" class="logo"></a>
        <div class="list">
            <ul>
                <li><a href="/iniciar_sessao">Login</a></li>
                <li><a href="/perfil">Perfil</a></li>
                <li><a href="/suporte">Suporte</a></li>
            </ul> 
        </div>
    </nav>
    <div class="text">
        <h2>BEM VINDO, <?php echo "{$_COOKIE['nome_pessoa']}"; ?> ,à<br> <span>Reserva De Salas</span> </h2>
        <p>Projeto elaborado pela turma 2ºE e 1ºD 2024/25</p>
        <button class="btn">Reservar Sala
        <a href="/reserva_prof"></a>
        </button>
    </div>
    <img src="coloca a imagem" alt="" class="img">
    <div class="circle"></div>
</body>
</html>