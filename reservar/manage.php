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
                if ($_SESSION['admin']) {
                    echo "<li><a href='/admin'>Painel Administrativo</a></li>";
                }
                ?>
                <li><a href="/login/?action=logout">Terminar sessão</a></li>
            </ul>
        </div>
    </nav>
    <div class="container mt-5 mb-5">
        <?php
        $id = $_SESSION['id'];
        
        // Handle bulk reservation separately since it doesn't require tempo/data/sala in GET
        if (isset($_GET['subaction']) && $_GET['subaction'] === 'bulk') {
            if (!isset($_POST['motivo']) || empty($_POST['motivo'])) {
                echo "<div class='alert alert-danger show' role='alert'>Motivo é obrigatório.</div>";
                echo "<a href='" . htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8') . "' class='btn btn-primary'>Voltar</a>";
            } elseif (!isset($_POST['slots']) || !is_array($_POST['slots']) || count($_POST['slots']) == 0) {
                echo "<div class='alert alert-danger show' role='alert'>Nenhum tempo foi selecionado.</div>";
                echo "<a href='" . htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8') . "' class='btn btn-primary'>Voltar</a>";
            } else {
                $motivo = $_POST['motivo'];
                $extra = $_POST['extra'] ?? '';
                $successCount = 0;
                $failedSlots = [];
                
                foreach ($_POST['slots'] as $slot) {
                    $parts = explode('|', $slot);
                    if (count($parts) !== 3) continue;
                    
                    $slotTempo = urldecode($parts[0]);
                    $slotSala = urldecode($parts[1]);
                    $slotData = urldecode($parts[2]);
                    
                    // Check if slot is still available
                    $checkStmt = $db->prepare("SELECT * FROM reservas WHERE sala=? AND tempo=? AND data=? AND aprovado!=-1");
                    $checkStmt->bind_param("sss", $slotSala, $slotTempo, $slotData);
                    $checkStmt->execute();
                    $existing = $checkStmt->get_result()->fetch_assoc();
                    $checkStmt->close();
                    
                    if (!$existing) {
                        $stmt = $db->prepare("INSERT INTO reservas (sala, tempo, requisitor, data, aprovado, motivo, extra) VALUES (?, ?, ?, ?, 0, ?, ?);");
                        $stmt->bind_param("ssssss", $slotSala, $slotTempo, $id, $slotData, $motivo, $extra);
                        if ($stmt->execute()) {
                            $successCount++;
                        } else {
                            $failedSlots[] = htmlspecialchars($slotData, ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($slotTempo, ENT_QUOTES, 'UTF-8');
                        }
                        $stmt->close();
                    } else {
                        $failedSlots[] = htmlspecialchars($slotData, ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($slotTempo, ENT_QUOTES, 'UTF-8') . " (já reservado)";
                    }
                }
                
                echo "<div class='row justify-content-center'>";
                echo "<div class='col-md-10 col-lg-8'>";
                
                echo "<div class='alert alert-success'><h4 class='alert-heading'>Reservas Submetidas!</h4><p class='mb-0'>{$successCount} reserva(s) criada(s) com sucesso e submetidas para aprovação.</p></div>";
                if (count($failedSlots) > 0) {
                    echo "<div class='alert alert-warning'><strong>Algumas reservas falharam:</strong><br>" . implode('<br>', $failedSlots) . "</div>";
                }
                
                // Get the room info and post-reservation content for the first successful reservation
                if ($successCount > 0 && isset($slotSala)) {
                    $stmt = $db->prepare("SELECT nome, post_reservation_content FROM salas WHERE id=?");
                    $stmt->bind_param("s", $slotSala);
                    $stmt->execute();
                    $salaData = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    
                    // Display post-reservation content if available
                    if (!empty($salaData['post_reservation_content'])) {
                        echo "<div class='card mb-3'>";
                        echo "<div class='card-body'>";
                        echo "<h5 class='card-title'>Informações Importantes - " . htmlspecialchars($salaData['nome'], ENT_QUOTES, 'UTF-8') . "</h5>";
                        echo "<div class='post-reservation-content'>";
                        echo $salaData['post_reservation_content']; // Content is already HTML from CKEditor
                        echo "</div>";
                        echo "</div>";
                        echo "</div>";
                    }
                }
                
                echo "<div class='d-grid gap-2 d-md-block'>";
                echo "<a href='/reservar' class='btn btn-success me-md-2 mb-2 mb-md-0'>Voltar à página de reserva de salas</a> ";
                echo "<a href='/reservas' class='btn btn-primary'>Ver as minhas reservas</a>";
                echo "</div>";
                
                echo "</div></div>";
            }
        } elseif (isset($_GET['tempo']) && isset($_GET['data']) && isset($_GET['sala'])) {
            $tempo = $_GET['tempo'];
            $data = $_GET['data'];
            $sala = $_GET['sala'];
            $motivo = $_POST['motivo'] ?? '';
            $extra = $_POST['extra'] ?? '';
            switch (isset($_GET['subaction']) ? $_GET['subaction'] : null) {
                case "reservar":
                    if (!isset($_POST['motivo'])) {
                        echo "<div class='alert alert-danger show' role='alert'>Motivo é obrigatório.</div>";
                        break;
                    }
                    $stmt = $db->prepare("INSERT INTO reservas (sala, tempo, requisitor, data, aprovado, motivo, extra) VALUES (?, ?, ?, ?, 0, ?, ?);");
                    $stmt->bind_param("ssssss", $sala, $tempo, $id, $data, $motivo, $extra);
                    if (!$stmt->execute()) {
                        http_response_code(500);
                        die("Houve um problema a reservar a sala. Contacte um administrador, ou tente novamente mais tarde.");
                    }
                    $stmt->close();
                    header("Location: /reservar/manage.php?sala=" . urlencode($sala) . "&tempo=" . urlencode($tempo) . "&data=" . urlencode($data));
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
                            echo("<div class='mt-2 alert alert-warning show' role='alert'>A reserva foi rejeitada, mas o email de notificação não foi enviado. Contacte o Postmaster.\nErro do PHPMailer: {$enviarmail->ErrorInfo}</div>");
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
                        
                        echo "<div class='row justify-content-center'>";
                        echo "<div class='col-md-8 col-lg-6'>";
                        echo "<h2 class='mb-4'>Reservar Sala</h2>";
                        echo "<form action='/reservar/manage.php?subaction=reservar&tempo=" . urlencode($tempo) . "&data=" . urlencode($data) . "&sala=" . urlencode($sala) . "' method='POST'>
                    <div class='form-floating mb-3'>
                    <input type='text' class='form-control' id='sala' name='sala' placeholder='Sala' value='" . htmlspecialchars($salaextenso, ENT_QUOTES, 'UTF-8') . "' disabled>
                    <label for='sala'>Sala</label>
                    </div>
                    <div class='form-floating mb-3'>
                    <input type='text' class='form-control' id='motivo' name='motivo' placeholder='Motivo da Reserva' required>
                    <label for='motivo'>Motivo da Reserva</label>
                    </div>
                    <div class='form-floating mb-3'>
                    <textarea class='form-control' id='extra' name='extra' placeholder='Informação Extra' rows='6' style='height: 150px;'></textarea>
                    <label for='extra'>Informação Extra</label>
                    </div>
                    <p class='text-muted small mb-3'>Nota: A reserva será submetida para aprovação.</p>
                    <button type='submit' class='btn btn-success w-100 mb-2'>Reservar</button>
                    </form>";
                        echo "<a href='" . htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES, 'UTF-8') . "' class='btn btn-secondary w-100'>Voltar</a>";
                        echo "</div></div>";
                    } else {
                        echo "<div class='row justify-content-center'>";
                        echo "<div class='col-md-10 col-lg-8'>";
                        
                        // Display appropriate message based on approval status
                        if ($detalhesreserva['aprovado'] == 1) {
                            echo "<div class='alert alert-success'><h4 class='alert-heading mb-0'>Reserva Aprovada!</h4></div>";
                        } else if ($detalhesreserva['aprovado'] == 0) {
                            echo "<div class='alert alert-info'><h4 class='alert-heading'>Reserva Submetida!</h4><p class='mb-0'>A sua reserva foi submetida e está a aguardar aprovação.</p></div>";
                        } else {
                            echo "<div class='alert alert-warning'><h4 class='alert-heading mb-0'>Reserva Cancelada</h4></div>";
                        }
                        
                        echo "<div class='card mb-3'>";
                        echo "<div class='card-body'>";
                        echo "<h5 class='card-title'>Detalhes da Reserva</h5>";
                        
                        $stmt = $db->prepare("SELECT nome, post_reservation_content FROM salas WHERE id=?");
                        $stmt->bind_param("s", $sala);
                        $stmt->execute();
                        $salaData = $stmt->get_result()->fetch_assoc();
                        $stmt->close();
                        
                        echo "<p class='mb-2'><strong>Sala:</strong> " . htmlspecialchars($salaData['nome'], ENT_QUOTES, 'UTF-8') . "</p>";
                        
                        $stmt = $db->prepare("SELECT nome FROM cache WHERE id=?");
                        $stmt->bind_param("s", $detalhesreserva['requisitor']);
                        $stmt->execute();
                        $requisitorextenso = $stmt->get_result()->fetch_assoc()['nome'];
                        $stmt->close();
                        
                        echo "<p class='mb-2'><strong>Requisitada por:</strong> " . htmlspecialchars($requisitorextenso, ENT_QUOTES, 'UTF-8') . "</p>";
                        
                        $stmt = $db->prepare("SELECT horashumanos FROM tempos WHERE id=?");
                        $stmt->bind_param("s", $tempo);
                        $stmt->execute();
                        $horastempo = $stmt->get_result()->fetch_assoc()['horashumanos'];
                        $stmt->close();
                        
                        echo "<p class='mb-2'><strong>Tempo:</strong> " . htmlspecialchars($horastempo, ENT_QUOTES, 'UTF-8') . "</p>";
                        echo "<p class='mb-2'><strong>Data:</strong> " . htmlspecialchars($data, ENT_QUOTES, 'UTF-8') . "</p>";
                        echo "<p class='mb-2'><strong>Motivo:</strong> " . htmlspecialchars($detalhesreserva['motivo'], ENT_QUOTES, 'UTF-8') . "</p>";
                        
                        if (!empty($detalhesreserva['extra'])) {
                            echo "<p class='mb-2'><strong>Informação Extra:</strong></p>";
                            echo "<div class='border rounded p-3 bg-light'>" . nl2br(htmlspecialchars($detalhesreserva['extra'], ENT_QUOTES, 'UTF-8')) . "</div>";
                        }
                        echo "</div>";
                        echo "</div>";
                        
                        // Display post-reservation content if available
                        if (!empty($salaData['post_reservation_content'])) {
                            echo "<div class='card mb-3'>";
                            echo "<div class='card-body'>";
                            echo "<h5 class='card-title'>Informações Importantes</h5>";
                            echo "<div class='post-reservation-content'>";
                            echo $salaData['post_reservation_content']; // Content is already HTML from CKEditor
                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                        }
                        
                        echo "<div class='d-grid gap-2 d-md-block'>";
                        if ($_SESSION['id'] == $detalhesreserva['requisitor'] | $_SESSION['admin']) {
                            echo "<a href='/reservar/manage.php?subaction=apagar&tempo=" . urlencode($tempo) . "&data=" . urlencode($data) . "&sala=" . urlencode($sala) . "' class='btn btn-danger me-md-2 mb-2 mb-md-0' onclick='return confirm(\"Tem a certeza que pretende apagar esta reserva?\");'>Apagar Reserva</a> ";
                        }
                        echo "<a href='/reservar' class='btn btn-success me-md-2 mb-2 mb-md-0'>Voltar à página de reserva de salas</a> ";
                        if (strpos($_SERVER['HTTP_REFERER'], '/admin/pedidos.php') !== false) {
                            echo "<a href='/admin/pedidos.php' class='btn btn-primary'>Voltar aos pedidos</a>";
                        } else {
                            echo "<a href='/reservas' class='btn btn-primary'>Ver todas as minhas reservas</a>";
                        }
                        echo "</div>";
                        
                        echo "</div></div>";
                    }
            }
        }
        ?>
    </div>
</body>

</html>