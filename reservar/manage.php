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
    <title>Detalhes do Tempo | ClassLink</title>
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

        if (isset($_GET['tempo']) && isset($_GET['data']) && isset($_GET['sala'])) {
            $tempo = $_GET['tempo'];
            $data = $_GET['data'];
            $sala = $_GET['sala'];
            $motivo = $_POST['motivo'] ?? '';
            $extra = $_POST['extra'] ?? '';
            $id = $_SESSION['id'];
            switch (isset($_GET['subaction']) ? $_GET['subaction'] : null) {
                case "reservar":
                    if (!isset($_POST['motivo'])) {
                        echo "<div class='alert alert-danger fade show' role='alert'>Motivo é obrigatório.</div>";
                        break;
                    }
                    $stmt = $db->prepare("INSERT INTO reservas (sala, tempo, requisitor, data, aprovado, motivo, extra) VALUES (?, ?, ?, ?, 0, ?, ?);");
                    $stmt->bind_param("ssssss", $sala, $tempo, $id, $data, $motivo, $extra);
                    if (!$stmt->execute()) {
                        http_response_code(500);
                        die("Houve um problema a reservar a sala. Contacte um administrador, ou tente novamente mais tarde.");
                    }
                    $stmt->close();
                    header("Location: /reservar/?sala=" . urlencode($sala) . "&tempo=" . urlencode($tempo));
                    exit();
                    break;
                case "apagar":
                    $stmt = $db->prepare("SELECT * FROM reservas WHERE sala=? AND tempo=? AND data=?");
                    $stmt->bind_param("sss", $sala, $tempo, $data);
                    $stmt->execute();
                    $reserva = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    
                    if (!($_SESSION['admin']) && ($_SESSION['id'] != $reserva['requisitor'])) {
                        http_response_code(403);
                        die("Não tem permissão para apagar esta reserva.");
                    } else {
                        try {
                            $stmt = $db->prepare("SELECT nome FROM salas WHERE id=?");
                            $stmt->bind_param("s", $sala);
                            $stmt->execute();
                            $salaextenso = $stmt->get_result()->fetch_assoc()['nome'];
                            $stmt->close();
                            
                            $stmt = $db->prepare("SELECT email FROM cache WHERE id=?");
                            $stmt->bind_param("s", $id);
                            $stmt->execute();
                            $requisitor = $stmt->get_result()->fetch_assoc()['email'];
                            $stmt->close();
                            
                            $stmt = $db->prepare("SELECT horashumanos FROM tempos WHERE id=?");
                            $stmt->bind_param("s", $tempo);
                            $stmt->execute();
                            $tempohumano = $stmt->get_result()->fetch_assoc()['horashumanos'];
                            $stmt->close();
                            
                            logAction("Apagou a reserva da sala {$salaextenso} no dia {$data} no tempo com ID {$tempo}.", $_SESSION['id']);
                            
                            if ($mail['ativado'] != true) {
                                $stmt = $db->prepare("DELETE FROM reservas WHERE sala=? AND tempo=? AND data=?");
                                $stmt->bind_param("sss", $sala, $tempo, $data);
                                if (!$stmt->execute()) {
                                    http_response_code(500);
                                    die("Houve um problema a apagar a reserva. Contacte um administrador, ou tente novamente mais tarde.");
                                }
                                $stmt->close();
                                header("Location: /reservar/?sala=" . urlencode($sala));
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
                            $enviarmail->Body = utf8_decode("A sua reserva da sala {$salaextenso} para a data de {$data} às {$tempohumano} foi removida.\nEsta ação pode ser realizada por administradores, ou por si mesmo.\n\nObrigado.");
                            $enviarmail->send();
                        } catch (Exception $e) {
                            echo("<div class='mt-2 alert alert-warning fade show' role='alert'>A reserva foi rejeitada, mas o email de notificação não foi enviado. Contacte o Postmaster.\nErro do PHPMailer: {$enviarmail->ErrorInfo}</div>");
                        }

                        $stmt = $db->prepare("DELETE FROM reservas WHERE sala=? AND tempo=? AND data=?");
                        $stmt->bind_param("sss", $sala, $tempo, $data);
                        if (!$stmt->execute()) {
                            http_response_code(500);
                            die("Houve um problema a apagar a reserva. Contacte um administrador, ou tente novamente mais tarde.");
                        }
                        $stmt->close();
                        header("Location: /reservar/?sala=" . urlencode($sala));
                        break;
                    }
                case null:
                    $stmt = $db->prepare("SELECT * FROM reservas WHERE sala=? AND tempo=? AND data=? AND aprovado!=-1");
                    $stmt->bind_param("sss", $sala, $tempo, $data);
                    $stmt->execute();
                    $detalhesreserva = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    
                    if (!$detalhesreserva) {
                        $stmt = $db->prepare("SELECT nome FROM salas WHERE id=?");
                        $stmt->bind_param("s", $sala);
                        $stmt->execute();
                        $salaextenso = $stmt->get_result()->fetch_assoc()['nome'];
                        $stmt->close();
                        
                        echo "<h2>Reservar Sala</h2>";
                        echo "<form class='form w-50' action='/reservar/manage.php?subaction=reservar&tempo=" . urlencode($tempo) . "&data=" . urlencode($data) . "&sala=" . urlencode($sala) . "' method='POST'>
                    <div class='form-floating me-2'>
                    <input type='text' class='form-control form-control-sm' id='sala' name='sala' placeholder='Sala' value='" . htmlspecialchars($salaextenso, ENT_QUOTES, 'UTF-8') . "' disabled>
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
                        echo "<a href='" . htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8') . "' class='mt-2 btn btn-primary'>Voltar</a>";
                    } else {
                        echo "<h2>Detalhes da Reserva:</h2>";
                        
                        $stmt = $db->prepare("SELECT nome FROM salas WHERE id=?");
                        $stmt->bind_param("s", $sala);
                        $stmt->execute();
                        $salaextenso = $stmt->get_result()->fetch_assoc()['nome'];
                        $stmt->close();
                        
                        echo "<p class='fw-bold'>Sala: <span class='fw-normal'>" . htmlspecialchars($salaextenso, ENT_QUOTES, 'UTF-8') . "</span></p>";
                        
                        $stmt = $db->prepare("SELECT nome FROM cache WHERE id=?");
                        $stmt->bind_param("s", $detalhesreserva['requisitor']);
                        $stmt->execute();
                        $requisitorextenso = $stmt->get_result()->fetch_assoc()['nome'];
                        $stmt->close();
                        
                        echo "<p class='fw-bold'>Requisitada por: <span class='fw-normal'>" . htmlspecialchars($requisitorextenso, ENT_QUOTES, 'UTF-8') . "</span></p>";
                        
                        $stmt = $db->prepare("SELECT horashumanos FROM tempos WHERE id=?");
                        $stmt->bind_param("s", $tempo);
                        $stmt->execute();
                        $horastempo = $stmt->get_result()->fetch_assoc()['horashumanos'];
                        $stmt->close();
                        
                        echo "<p class='fw-bold'>Tempo: <span class='fw-normal'>" . htmlspecialchars($horastempo, ENT_QUOTES, 'UTF-8') . "</span></p>";
                        echo "<p class='fw-bold'>Data: <span class='fw-normal'>" . htmlspecialchars($data, ENT_QUOTES, 'UTF-8') . "</span></p>";
                        echo "<p class='fw-bold'>Estado: ";
                        if ($detalhesreserva['aprovado'] == 1) {
                            echo "<span class='badge bg-success'>Aprovado</span></p>";
                        } else {
                            echo "<span class='badge bg-warning text-dark'>Pendente</span></p>";
                        }
                        echo "<p class='fw-bold'>Motivo: <span class='fw-normal'>" . htmlspecialchars($detalhesreserva['motivo'], ENT_QUOTES, 'UTF-8') . "</span></p>";
                        echo "<p class='fw-bold'>Informação Extra:</p>
                        <textarea rows='4' cols='50' class='fw-normal' disabled>" . htmlspecialchars($detalhesreserva['extra'], ENT_QUOTES, 'UTF-8') . "</textarea>";
                        if ($_SESSION['id'] == $detalhesreserva['requisitor'] | $_SESSION['admin']) {
                            echo "<a href='/reservar/manage.php?subaction=apagar&tempo=" . urlencode($tempo) . "&data=" . urlencode($data) . "&sala=" . urlencode($sala) . "' class='btn btn-danger mt-2' onclick='return confirm(\"Tem a certeza que pretende apagar esta reserva?\");'>Apagar Reserva</a>";
                        } else {
                            echo "<p class='fw-bold'>Requisitada por: <span class='fw-normal'>" . htmlspecialchars($requisitorextenso, ENT_QUOTES, 'UTF-8') . "</span></p>";
                        }
                        if (strpos($_SERVER['HTTP_REFERER'], '/admin/pedidos.php') !== false) {
                            echo "<a href='/admin/pedidos.php' class='btn btn-primary mt-2'>Voltar</a>";
                        } else {
                            echo "<a href='/reservar/?sala=" . urlencode($sala) . "' class='btn btn-primary mt-2'>Voltar</a>";
                        }
                    }
            }
        }
        ?>
    </div>
</body>

</html>