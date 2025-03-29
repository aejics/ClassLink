<?php
    require '../giaeconnect/module.php';
    if (isset($_POST['username']) && isset($_POST['password'])){
        loginGiae($_POST['username'], $_POST['password']);
    }
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN</title>
    <!-- CSS -->
    <link rel="stylesheet" href="logincss.css">
    <!-- Fontawsome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- jquery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>

<body>
    <div class="container">
        <div class="cardLogin">
            <svg xmlns="http://www.w3.org/2000/svg" width="270" height="387" viewBox="0 0 270 387" fill="none">
                <path d="M0 20C0 8.9543 8.95431 0 20 0H250C261.046 0 270 8.95431 270 20V366.417C270 381.573 253.799 
                    391.222 240.473 384.002L10.4726 259.388C4.01969 255.892 0 249.143 0 241.803V20Z" fill="white" />
            </svg>
                <h2 class="heading">Iniciar Sessão</h2>
            <form action="/login/" method="POST">
                <div class="input-grup">
                    <input type="text" name="username" placeholder="Nome de Utilizador" id="username" required>
                    <span class="border"></span>
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="input-grup">
                    <input type="password" name="password" placeholder="Palavra-Passe" id="password" required>
                    <span class="border"></span>
                    <i class="fa-solid fa-key"></i>
                </div>
                <button type="submit">Iniciar Sessão</button>
            </form>
        </div>
        <div class="sosmed">
                <img src="/assets/aejics.png" class="logoaejics">
        </div>
        <div class="cardRegis">
            <svg xmlns="http://www.w3.org/2000/svg" width="270" height="447" viewBox="0 0 270 447" fill="none">
                <path d="M270 427C270 438.046 261.046 447 250 447L19.9999 447C8.95424 447 -6.02523e-05 438.046
                     -5.92867e-05 427L-2.37629e-05 20.6546C-2.24466e-05 5.598 16.0091 -4.05922 29.3278 
                     2.96307L259.328 124.23C265.892 127.691 270 134.501 270 141.922L270 427Z" fill="white" />
            </svg>
            <script src="loginjs.js"></script>    
</body>

</html>