<?php
require_once(__DIR__ . '/../func/logaction.php');
require_once(__DIR__ . '/../src/db.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
session_start();
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
                if ($_SESSION['admin']) {
                    echo "<li><a href='/admin'>Painel Administrativo</a></li>";
                }
                ?>
                <li><a href="/login/?action=logout">Terminar sessão</a></li>
            </ul>
        </div>
    </nav>
    <div class="d-flex justify-content-center align-items-center vh-100 flex-column" style="margin-top: -50px;">
        <?php

        if ($_GET['tempo'] && $_GET['data'] && $_GET['sala']) {
            $tempo = $_GET['tempo'];
            $data = $_GET['data'];
            $sala = $_GET['sala'];
            $motivo = $_POST['motivo'] ?? '';
            $extra = $_POST['extra'] ?? '';
            $id = $_SESSION['id'];
            switch ($_GET['subaction']) {
                case "reservar":
                    $stmt = $db->prepare("INSERT INTO reservas (sala, tempo, requisitor, data, aprovado, motivo, extra) VALUES (?, ?, ?, ?, 0, ?, ?);");
                    $stmt->bind_param("ssssss", $sala, $tempo, $id, $data, $motivo, $extra);
                    if (!$stmt->execute()) {
                        http_response_code(500);
                        die("Houve um problema a reservar a sala. Contacte um administrador, ou tente novamente mais tarde.");
                    }
                    header("Location: /reservar/?sala={$sala}&tempo={$tempo}");
                    break;
                case "apagar":
                    $reserva = $db->query("SELECT * FROM reservas WHERE sala='{$sala}' AND tempo='{$tempo}' AND data='{$data}';")->fetch_assoc();
                    if (!($_SESSION['admin']) && ($_SESSION['id'] != $reserva['requisitor'])) {
                        http_response_code(403);
                        die("Não tem permissão para apagar esta reserva.");
                    } else {
                        $stmt = $db->prepare("DELETE FROM reservas WHERE sala=? AND tempo=? AND data=?;");
                        $stmt->bind_param("sss", $sala, $tempo, $data);
                        logAction("Apagou a reserva da sala {$salaextenso} no dia {$data} no tempo com ID {$tempo}.", $_SESSION['id'],);
                        try {
                            $salaextenso = $db->query("SELECT nome FROM salas WHERE id='{$_GET['sala']}';")->fetch_assoc()['nome'];
                            $requisitor = $db->query("SELECT email FROM cache WHERE id='{$id}';")->fetch_assoc()['email'];
                            $tempohumano = $db->query("SELECT horashumanos FROM tempos WHERE id='{$_GET['tempo']}';")->fetch_assoc()['horashumanos'];
                            if ($mail['ativado'] != true) {
                                break;
                            }
                            $enviarmail = new PHPMailer(true);
                            $enviarmail->isSMTP();
                            $enviarmail->Host       = $mail['servidor'];
                            $enviarmail->SMTPAuth   = $mail['autenticacao'];
                            $enviarmail->Username   = $mail['username'];
                            $enviarmail->Password   = $mail['password'];
                            $enviarmail->SMTPSecure = $mail['tipodeseguranca'];
                            $enviarmail->Port       = $mail['porta'];
                            $enviarmail->setFrom($mail['mailfrom'], $mail['fromname']);
                            $enviarmail->addAddress($requisitor);
                            $enviarmail->isHTML(false);
                            $enviarmail->Subject = utf8_decode("Reserva da Sala {$salaextenso} Removida");
                            $enviarmail->Body = utf8_decode("A sua reserva da sala {$salaextenso} para a data de {$_GET['data']} às {$tempohumano} foi removida.\nEsta ação pode ser realizada por administradores, ou por si mesmo.\n\nObrigado.");
                            $enviarmail->send();
                        } catch (Exception $e) {
                            echo("<div class='mt-2 alert alert-warning fade show' role='alert'>A reserva foi rejeitada, mas o email de notificação não foi enviado. Contacte o Postmaster.\nErro do PHPMailer: {$enviarmail->ErrorInfo}</div>");
                        }

                        if (!$stmt->execute()) {
                            http_response_code(500);
                            die("Houve um problema a apagar a reserva. Contacte um administrador, ou tente novamente mais tarde.");
                        }
                        header("Location: /reservar/?sala={$sala}");
                        break;
                    }
                case null:
                    $detalhesreserva = $db->query("SELECT * FROM reservas WHERE sala='{$sala}' AND tempo='{$tempo}' AND data='{$data}' AND aprovado!=-1;")->fetch_assoc();
                    if (!$detalhesreserva) {
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
                        $requisitorextenso = $db->query("SELECT nome FROM cache WHERE id='{$detalhesreserva['requisitor']}';")->fetch_assoc()['nome'];
                        echo "<p class='fw-bold'>Requisitada por: <span class='fw-normal'>{$requisitorextenso}</span></p>";
                        $horastempo = $db->query("SELECT horashumanos FROM tempos WHERE id='{$tempo}';")->fetch_assoc()['horashumanos'];
                        echo "<p class='fw-bold'>Tempo: <span class='fw-normal'>{$horastempo}</span></p>";
                        echo "<p class='fw-bold'>Data: <span class='fw-normal'>{$data}</span></p>";
                        echo "<p class='fw-bold'>Estado: ";
                        if ($detalhesreserva['aprovado'] == 1) {
                            echo "<span class='badge bg-success'>Aprovado</span></p>";
                        } else {
                            echo "<span class='badge bg-warning text-dark'>Pendente</span></p>";
                        }
                        echo "<p class='fw-bold'>Motivo: <span class='fw-normal'>{$detalhesreserva['motivo']}</span></p>";
                        echo "<p class='fw-bold'>Informação Extra:</p>
                        <textarea rows='4' cols='50' class='fw-normal' disabled>{$detalhesreserva['extra']}</textarea>";
                        if ($_SESSION['id'] == $detalhesreserva['requisitor'] | $_SESSION['admin']) {
                            echo "<a href='/reservar/manage.php?subaction=apagar&tempo={$tempo}&data={$data}&sala={$sala}' class='btn btn-danger mt-2' onclick='return confirm(\"Tem a certeza que pretende apagar esta reserva?\");'>Apagar Reserva</a>";
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