<?php
        require 'login/index.php';
    if (!isset($_COOKIE['token'])){
        http_response_code(403);
        header("Location: /login");
        die("Não tem a sessão iniciada.");
    } else {
        $dados = $db->query("SELECT * FROM cache_giae WHERE id = '{$user}'")->fetch_assoc();
        $isadmin = $db->query("SELECT * FROM admins WHERE id = '{$user}' AND permitido = 1;")->num_rows;
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
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
    <link rel="stylesheet" href="/assets/index.css">
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='icon' href='/assets/logo.png'>
</head>
<body>
    <nav>
        <a href=""><img src="/assets/logo.png" class="logo"></a>
        <div class="list">
            <ul>
                <li><a href="/reservas">As minhas reservas</a></li>
                <li><a href="/reservar">Reservar sala</a></li>
                <?php
                    if ($isadmin) {
                    echo "<li><a href='/admin/'>Painel Administrativo</a></li>";
                    }
                ?>
                <li><a href="/login/?action=logout">Terminar sessão</a></li>
            </ul> 
        </div>
    </nav>
    <div class="text">
        <h3>Seja bem vindo, <?php echo "{$dados['nome']}"; ?>, à <br> <span>Reserva De Salas</span> </h3>
        <p>Projeto elaborado pelas turmas 2ºE e 1ºD 2024/25</p>
        <a href="/reservar">
            <button class="btn">
                Reservar uma Sala
            </button>
        </a>
        <div class="bottom" style="position: fixed; bottom: 0; width:100%; margin-bottom: 10px;">
            <img src="/assets/poch.png" class="img-thumbnail" alt="Pessoas 2030 | Portugal 2030 | Cofinanciado pela União Europeia" style="max-width: 15%">
            <img src="/assets/rep_edu.png" class="img-thumbnail" alt="República Portuguesa Educação Ciência e Inovação" style="max-width: 6%">
        </div>
    </div>
    <div class="circle"></div>
</body>
</html>