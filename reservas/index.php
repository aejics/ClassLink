<?php 
    // MUDAR DADOS PARA SESSION
    $dados = $db->query("SELECT * FROM cache_giae WHERE id = '{$user}'")->fetch_assoc();
    $isadmin = $db->query("SELECT * FROM admins WHERE id = '{$user}' AND permitido = 1;")->num_rows;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>As suas reservas | Reserva Salas</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/reservar.css">
    <link href="/assets/index.css" rel="stylesheet">
    <script src="/assets/tooltips.js"></script>
    <link rel='icon' href='/assets/logo.png'>
</head>
<body>
<nav>
    <a href="/"><img src="/assets/logo.png" class="logo"></a>
    <div class="list">
        <ul>
            <li><a href="/reservas">As minhas reservas</a></li>
            <li><a href="/reservar">Reservar sala</a></li>
            <?php
                if ($isadmin) {
                echo "<li><a href='/admin'>Painel Administrativo</a></li>";
                }
            ?>
            <li><a href="/login/?action=logout">Terminar sessão</a></li>
        </ul> 
    </div>
</nav>
<div class="d-flex justify-content-center align-items-center flex-column" style="height: calc(100vh - 120px); width: 100%; padding: 20px; box-sizing: border-box; overflow: hidden;">
    <div style="width: 80%; max-width: 1000px; height: 100%; overflow-y: auto; border: 1px solid #ddd; border-radius: 8px; padding: 10px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
<?php     
    echo "<p class='h2 fw-light text-center'>As suas reservas:</p>";
    $requisitor = $dados['id'];
    $reservas = $db->query("SELECT * FROM reservas WHERE requisitor='{$requisitor}' ORDER BY data DESC;");
    if ($reservas->num_rows == 0) {
        echo "<div class='alert alert-danger fade show' role='alert'>Não tem reservas no seu nome atualmente.</div>";
        exit;
    } else {
        echo "<div class='mt-3 text-center'>";
        echo "<table class='mt-2 table table-bordered'><thead><tr><th scope='col'>Sala</th><th scope='col'>Data</th><th scope='col'>Tempo</th><th scope='col'>Estado</th><th scope='col'>Ações</th></tr></thead><tbody>";
        while ($reserva = $reservas->fetch_assoc()) {
            $sala = $db->query("SELECT nome FROM salas WHERE id='{$reserva['sala']}';")->fetch_assoc();
            $tempo = $db->query("SELECT horashumanos FROM tempos WHERE id='{$reserva['tempo']}';")->fetch_assoc();
            if ($reserva['aprovado'] == 1) {
                echo "<tr><td>{$sala['nome']}</td><td>{$reserva['data']}</td><td>{$tempo['horashumanos']}</td><td><span class='badge bg-success' data-bs-toggle='tooltip' data-placement='top' title='A sua reserva foi aprovada! Um email foi lhe enviado com mais informações.'>Aprovado</span></td><td><div class='btn-group'><a class='btn' href='/reservar/manage.php?tempo={$reserva['tempo']}&sala={$reserva['sala']}&data={$reserva['data']}'>Detalhes</a> <a class='btn btn-danger' href='/reservar/manage.php?subaction=apagar&tempo={$reserva['tempo']}&sala={$reserva['sala']}&data={$reserva['data']}' onclick='return confirm('Tem a certeza que pretende apagar esta reserva?');'>Apagar</a></div></td></tr>";
            } else {
                echo "<tr><td>{$sala['nome']}</td><td>{$reserva['data']}</td><td>{$tempo['horashumanos']}</td><td><span class='badge bg-warning' data-bs-toggle='tooltip' data-placement='top' title='A sua reserva foi enviada e está a ser revista. Irá receber um email com mais informações em breve'>Pendente</span></td><td><div class='btn-group'><a class='btn' href='/reservar/manage.php?tempo={$reserva['tempo']}&sala={$reserva['sala']}&data={$reserva['data']}'>Detalhes</a> <a class='btn btn-danger' href='/reservar/manage.php?subaction=apagar&tempo={$reserva['tempo']}&sala={$reserva['sala']}&data={$reserva['data']}' onclick='return confirm('Tem a certeza que pretende apagar esta reserva?');'>Apagar</a></div></td></tr>";
            }
        }
        echo "</table></div>";
    }
?>
</div>
</div>
</body>
</html>