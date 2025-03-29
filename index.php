<?php
    // Redirecionar a login caso não exista token
    if (!isset($_COOKIE['token'])){
        // Assumida página de login, depende da localização definida
        // pela equipa login.
        header("Location: /login");
        die("Não tem a sessão iniciada.");
    } else {
        // mostrar texto de baixo.
        // até agora não temos mais desenvolvido pois
        // falta o trabalho da equipa login.
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Inicial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <a href=""><img src="https://avatars.githubusercontent.com/u/181112077?s=200&v=4" class="logo"></a>
        <div class="list">
            <ul>
                <li><a href="/iniciar_sessao">Login</a></li>
                <li><a href="/perfil">Perfil</a></li>
                <li><a href="/suporte">Suporte</a></li>
            </ul> 
        </div>
    </nav>
    <div class="text">
        <h2>BEM VINDO à<br> <span>Reserva De Salas</span> </h2>
        <p>feito pela turma 2ºE e 1ºD 2024/25</p>
        <button  class="btn">Reservar sala
        <a href="/reserva_prof"></a>
        </button>
    </div>
    <img src="coloca a imagem" alt="" class="img">
    <div class="circle"></div>
</body>
</html>