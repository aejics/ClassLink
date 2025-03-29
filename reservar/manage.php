
<?php 
    require '../login/index.php';
    $dados = $db->query("SELECT * FROM cache_giae WHERE email = '{$perfil['perfil']['email']}';")->fetch_assoc();
    $isadmin = $db->query("SELECT * FROM admins WHERE id = '{$dados['id']}' AND permitido = 1;")->num_rows;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Tempo | Reserva Salas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="/assets/index.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/reservar.css">
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
<div class="d-flex justify-content-center align-items-center vh-100 flex-column" style="margin-top: -50px;">
<?php

    if ($_GET['tempo'] && $_GET['data'] && $_GET['sala']){
        $tempo = filter_var($_GET['tempo'], FILTER_SANITIZE_NUMBER_INT);
        $data = filter_var($_GET['data'], FILTER_SANITIZE_STRING);
        $sala = filter_var($_GET['sala'], FILTER_SANITIZE_NUMBER_INT);
        $requisitor = $dados['id'];
        switch ($_GET['subaction']){
            case "reservar":
                $motivo = filter_var($_POST['motivo'], FILTER_SANITIZE_STRING);
                $extra = filter_var($_POST['extra'], FILTER_SANITIZE_STRING);
                $db->query("INSERT INTO reservas (sala, tempo, requisitor, data, aprovado, motivo, extra) VALUES ('{$sala}', '{$tempo}', '{$requisitor}', '{$data}', 0, '{$motivo}', '{$extra}');");
                header("Location: /reservar/?sala={$sala}&tempo={$tempo}");
                break;
            case "apagar":
                $reserva = $db->query("SELECT * FROM reservas WHERE sala='{$sala}' AND tempo='{$tempo}' AND data='{$data}';")->fetch_assoc();
                if (!$isadmin | $dados['id'] != $reserva['requisitor']){
                    http_response_code(403);
                    die("Não tem permissão para apagar esta reserva.");
                } else {
                    $db->query("DELETE FROM reservas WHERE sala='{$sala}' AND tempo='{$tempo}' AND data='{$data}';");
                    header("Location: /reservar/?sala={$sala}");
                    break;
                }
            case null:
                $detalhesreserva = $db->query("SELECT * FROM reservas WHERE sala='{$sala}' AND tempo='{$tempo}' AND data='{$data}' AND aprovado!=-1;")->fetch_assoc();
                if (!$detalhesreserva){
                    $salaextenso = $db->query("SELECT nome FROM salas WHERE id='{$sala}';")->fetch_assoc()['nome'];
                    echo "<h2>Reservar Sala</h2>";
                    echo "<form class='form w-50' action='/reservar/manage.php?subaction=reservar&tempo={$tempo}&data={$data}&sala={$_GET['sala']}' method='POST'>
                    <div class='form-floating me-2'>
                    <input type='text' class='form-control form-control-sm' id='sala' name='sala' placeholder='Sala' value='{$salaextenso}' disabled>
                    <label for='sala'>Sala</label>
                    </div>
                    <div class='form-floating me-2 mt-2'>
                    <input type='text' class='form-control form-control-sm' id='motivo' name='motivo' placeholder='Motivo da Reserva' required>
                    <label for='motivo'>Motivo da Reserva</label>
                    </div>
                    <div class='form-floating me-2 mt-2'>
                    <textarea class='form-control form-control-sm' id='extra' name='extra' placeholder='Informação Extra' rows='6' style='height: 150px;'></textarea>
                    <label for='extra'>Informação Extra</label>
                    <p class='mt-1 text-center'>Nota: A reserva será submetida para aprovação.</p>
                    </div>
                    <button type='submit' class='btn btn-success w-100'>Reservar</button>
                    </form>";
                    echo "<a href='{$_SERVER['HTTP_REFERER']}' class='mt-2 btn btn-primary'>Voltar</a>";
                } else {
                    echo "<h2>Detalhes da Reserva:</h2>";
                    $salaextenso = $db->query("SELECT nome FROM salas WHERE id='{$sala}';")->fetch_assoc()['nome'];
                    echo "<p class='fw-bold'>Sala: <span class='fw-normal'>{$salaextenso}</span></p>";
                    $requisitorextenso = $db->query("SELECT nomecompleto FROM cache_giae WHERE id='{$requisitor}';")->fetch_assoc()['nomecompleto'];
                    echo "<p class='fw-bold'>Requisitada por: <span class='fw-normal'>{$requisitorextenso}</span></p>";
                    $horastempo = $db->query("SELECT horashumanos FROM tempos WHERE id='{$tempo}';")->fetch_assoc()['horashumanos'];
                    echo "<p class='fw-bold'>Tempo: <span class='fw-normal'>{$horastempo}</span></p>";
                    echo "<p class='fw-bold'>Data: <span class='fw-normal'>{$data}</span></p>";
                    echo "<p class='fw-bold'>Estado: ";
                    if ($detalhesreserva['aprovado'] == 1){
                        echo "<span class='badge bg-success'>Aprovado</span></p>";
                    } else if ($detalhesreserva['aprovado'] == -1) {
                        echo "<span class='badge bg-danger'>Rejeitado</span></p>";
                    } else {
                        echo "<span class='badge bg-warning text-dark'>Pendente</span></p>";
                    }
                    echo "<p class='fw-bold'>Motivo: <span class='fw-normal'>{$detalhesreserva['motivo']}</span></p>";
                    echo "<p class='fw-bold'>Informação Extra:</p>
                        <textarea rows='4' cols='50' class='fw-normal' disabled>{$detalhesreserva['extra']}</textarea>";
                    if ($dados['id'] == $detalhesreserva['requisitor'] | $isadmin){
                        echo "<a href='/reservar/manage.php?subaction=apagar&tempo={$tempo}&data={$data}&sala={$sala}' class='btn btn-danger mt-2'>Apagar Reserva</a>";
                    } else {
                        echo "<p class='fw-bold'>Requisitada por: <span class='fw-normal'>{$requisitorextenso}</span></p>";
                    }
                    if (strpos($_SERVER['HTTP_REFERER'], '/admin/pedidos.php') !== false) {
                        echo "<a href='/admin/pedidos.php' class='btn btn-primary mt-2'>Voltar</a>";
                    } else {
                        echo "<a href='/reservar/?sala={$sala}' class='btn btn-primary mt-2'>Voltar</a>";
                    }
                }
        }
    }
?>
</div>
</body>
</html>