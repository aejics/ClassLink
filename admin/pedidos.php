<?php
require 'index.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
?>
<div class="h-100 d-flex align-items-center justify-content-center flex-column">
    <h3>Gestão de Pedidos de Salas</h3>
    <form action="/admin/pedidos.php" method="POST" class="d-flex align-items-center">
        <div class="form-floating me-2">
            <select class="form-select" id="sala" name="sala" required onchange="this.form.submit();">
                <?php if ($_POST['sala'] == "0" | !$_POST['sala']) {
                    echo "<option value='0' selected disabled>Escolha uma sala</option>";
                } else {
                    echo "<option value='0' disabled>Escolha uma sala</option>";
                }
                $salas = $db->query("SELECT * FROM salas;");
                while ($sala = $salas->fetch_assoc()) {
                    if ($_POST['sala'] == $sala['id'] || $_GET['sala'] == $sala['id']) {
                        echo "<option value='{$sala['id']}' selected>{$sala['nome']}</option>";
                    } else {
                        echo "<option value='{$sala['id']}'>{$sala['nome']}</option>";
                    }
                }
                ?>
            </select>
            <label for="sala" class="form-label">Escolha uma sala</label>
        </div>
        <div class="form-floating me-2">
            <input type="text" class="form-control form-control-sm" id="requisitor" name="requisitor" placeholder="Requisitor" value="">
            <label for="requisitor">Requisitor (id)</label>
        </div>
    </form>

    <?php
    if (isset($_GET['subaction'])) {
        if (!isset($_GET['sala']) || !isset($_GET['tempo']) || !isset($_GET['data'])) {
            echo "<div class='alert alert-danger fade show' role='alert'>Parâmetros inválidos.</div>";
            echo "<a href='/admin/pedidos.php'><button class='btn btn-primary'>Voltar</button></a>";
        } else {
        require '../vendor/phpmailer/phpmailer/src/Exception.php';
        require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require '../vendor/phpmailer/phpmailer/src/SMTP.php';

        switch ($_GET['subaction']) {
            case "aprovar":
                $stmt = $db->prepare("SELECT requisitor FROM reservas WHERE sala=? AND tempo=? AND data=?");
                $stmt->bind_param("sss", $_GET['sala'], $_GET['tempo'], $_GET['data']);
                $stmt->execute();
                $requisitor = $stmt->get_result()->fetch_assoc()['requisitor'];
                $stmt->close();
                
                $stmt = $db->prepare("UPDATE reservas SET aprovado=1 WHERE sala=? AND tempo=? AND data=?");
                $stmt->bind_param("sss", $_GET['sala'], $_GET['tempo'], $_GET['data']);
                $stmt->execute();
                $stmt->close();
                
                echo "<div class='mt-2 alert alert-success fade show' role='alert'>Reserva aprovada com sucesso.</div>";
                $reservaUrl = "/reservar/manage.php?sala=" . urlencode($_GET['sala']) . "&tempo=" . urlencode($_GET['tempo']) . "&data=" . urlencode($_GET['data']);
                echo "<a href='{$reservaUrl}' class='btn btn-info me-2' target='_blank'>Ver Detalhes da Reserva</a>";
                echo "<a href='/admin/pedidos.php'><button class='btn btn-primary'>Voltar</button></a>";
                try {
                    $stmt = $db->prepare("SELECT nome FROM salas WHERE id=?");
                    $stmt->bind_param("s", $_GET['sala']);
                    $stmt->execute();
                    $sala = $stmt->get_result()->fetch_assoc()['nome'];
                    $stmt->close();
                    
                    $stmt = $db->prepare("SELECT email FROM cache WHERE id=?");
                    $stmt->bind_param("s", $requisitor);
                    $stmt->execute();
                    $maildapessoa = $stmt->get_result()->fetch_assoc()['email'];
                    $stmt->close();
                    
                    $stmt = $db->prepare("SELECT horashumanos FROM tempos WHERE id=?");
                    $stmt->bind_param("s", $_GET['tempo']);
                    $stmt->execute();
                    $tempohumano = $stmt->get_result()->fetch_assoc()['horashumanos'];
                    $stmt->close();
                    
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
                    $enviarmail->addAddress($maildapessoa);
                    $enviarmail->isHTML(false);
                    $enviarmail->Subject = utf8_decode("Reserva da Sala {$sala} Aprovada");
                    $reservaUrl = "https://" . $_SERVER['HTTP_HOST'] . "/reservar/manage.php?sala=" . urlencode($_GET['sala']) . "&tempo=" . urlencode($_GET['tempo']) . "&data=" . urlencode($_GET['data']);
                    $enviarmail->Body = utf8_decode("A sua reserva da sala {$sala} para a data de {$_GET['data']} às {$tempohumano} foi aprovada.\n\nPode ver os detalhes e informações importantes da sua reserva em:\n{$reservaUrl}\n\nObrigado.");
                    $enviarmail->send();
                } catch (Exception $e) {
                    echo "<div class='mt-2 alert alert-warning fade show' role='alert'>A reserva foi aprovada, mas o email de notificação não foi enviado. Contacte o Postmaster.\nErro do PHPMailer: " . htmlspecialchars($enviarmail->ErrorInfo, ENT_QUOTES, 'UTF-8') . "</div>";
                }
                break;
            case "rejeitar":
                $stmt = $db->prepare("SELECT requisitor FROM reservas WHERE sala=? AND tempo=? AND data=?");
                $stmt->bind_param("sss", $_GET['sala'], $_GET['tempo'], $_GET['data']);
                $stmt->execute();
                $requisitor = $stmt->get_result()->fetch_assoc()['requisitor'];
                $stmt->close();
                
                $stmt = $db->prepare("DELETE FROM reservas WHERE sala=? AND tempo=? AND data=?");
                $stmt->bind_param("sss", $_GET['sala'], $_GET['tempo'], $_GET['data']);
                $stmt->execute();
                $stmt->close();
                
                echo "<div class='mt-2 alert alert-danger fade show' role='alert'>Reserva rejeitada com sucesso.</div>";
                echo "<a href='/admin/pedidos.php'><button class='btn btn-primary'>Voltar</button></a>";
                try {
                    $stmt = $db->prepare("SELECT nome FROM salas WHERE id=?");
                    $stmt->bind_param("s", $_GET['sala']);
                    $stmt->execute();
                    $sala = $stmt->get_result()->fetch_assoc()['nome'];
                    $stmt->close();
                    
                    $stmt = $db->prepare("SELECT email FROM cache WHERE id=?");
                    $stmt->bind_param("s", $requisitor);
                    $stmt->execute();
                    $maildapessoa = $stmt->get_result()->fetch_assoc()['email'];
                    $stmt->close();
                    
                    $stmt = $db->prepare("SELECT horashumanos FROM tempos WHERE id=?");
                    $stmt->bind_param("s", $_GET['tempo']);
                    $stmt->execute();
                    $tempohumano = $stmt->get_result()->fetch_assoc()['horashumanos'];
                    $stmt->close();
                    
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
                    $enviarmail->addAddress($maildapessoa);
                    $enviarmail->isHTML(false);
                    $enviarmail->Subject = utf8_decode("Reserva da Sala {$sala} Rejeitada");
                    $enviarmail->Body = utf8_decode("A sua reserva da sala {$sala} para a data de {$_GET['data']} às {$tempohumano} foi rejeitada.\n\nObrigado.");
                    $enviarmail->send();
                } catch (Exception $e) {
                    echo "<div class='mt-2 alert alert-warning fade show' role='alert'>A reserva foi rejeitada, mas o email de notificação não foi enviado. Contacte o Postmaster.\nErro do PHPMailer: " . htmlspecialchars($enviarmail->ErrorInfo, ENT_QUOTES, 'UTF-8') . "</div>";
                }
                break;
            case "detalhes":
        }
        }
    } elseif (isset($_POST['sala']) | isset($_GET['sala']) | isset($_POST['requisitor'])) {
        echo "<div style='max-height: 400px; overflow-y: auto; width: 100%;'>";
        echo "<table class='table'><tr><th scope='col'>Data</th><th scope='col'>Tempo</th><th scope='col'>Requisitor</th><th scope='col'>Motivo</th><th scope='col'>AÇÕES</th></tr>";
        if ($_POST['sala']) {
            $sala = $_POST['sala'];
        } else {
            $sala = $_GET['sala'];
        }
        if ($_POST['requisitor']) {
            $stmt = $db->prepare("SELECT * FROM reservas WHERE requisitor=? ORDER BY data DESC");
            $stmt->bind_param("s", $_POST['requisitor']);
            $stmt->execute();
            $pedidos = $stmt->get_result();
            
            while ($pedido = $pedidos->fetch_assoc()) {
                $stmt2 = $db->prepare("SELECT nome FROM salas WHERE id=?");
                $stmt2->bind_param("s", $pedido['sala']);
                $stmt2->execute();
                $salaextenso = $stmt2->get_result()->fetch_assoc()['nome'];
                $stmt2->close();
                
                $stmt2 = $db->prepare("SELECT nome FROM cache WHERE id=?");
                $stmt2->bind_param("s", $pedido['requisitor']);
                $stmt2->execute();
                $requisitorextenso = $stmt2->get_result()->fetch_assoc()['nome'];
                $stmt2->close();
                
                $stmt2 = $db->prepare("SELECT horashumanos FROM tempos WHERE id=?");
                $stmt2->bind_param("s", $pedido['tempo']);
                $stmt2->execute();
                $horastempo = $stmt2->get_result()->fetch_assoc()['horashumanos'];
                $stmt2->close();
                
                $tempoEnc = urlencode($pedido['tempo']);
                $dataEnc = urlencode($pedido['data']);
                $salaEnc = urlencode($pedido['sala']);
                
                echo "<tr><td>" . htmlspecialchars($pedido['data'], ENT_QUOTES, 'UTF-8') . "</td>
                <td>" . htmlspecialchars($horastempo, ENT_QUOTES, 'UTF-8') . "</td>
                <td>" . htmlspecialchars($requisitorextenso, ENT_QUOTES, 'UTF-8') . "</td>
                <td>" . htmlspecialchars($pedido['motivo'], ENT_QUOTES, 'UTF-8') . "</td>";
                if ($pedido['aprovado'] == 0) {
                    echo "<td><a href='/admin/pedidos.php?subaction=aprovar&tempo={$tempoEnc}&data={$dataEnc}&sala={$salaEnc}' class='btn btn-success' onclick='return confirm(\"Tem a certeza que pretende aprovar esta reserva?\");'>Aprovar</a>
                 <a href='/admin/pedidos.php?subaction=rejeitar&tempo={$tempoEnc}&data={$dataEnc}&sala={$salaEnc}' class='btn btn-danger' onclick='return confirm(\"Tem a certeza que pretende rejeitar esta reserva? Esta ação irá notificar o utilizador, e irá apagar a reserva para libertar o tempo.\");'>Rejeitar</a>";
                } else {
                    echo "<td>";
                }
                echo " <a href='/reservar/manage.php?&tempo={$tempoEnc}&data={$dataEnc}&sala={$salaEnc}' class='btn btn-secondary'>Detalhes</a></td></tr>";
            }
            $stmt->close();
        } else {
            $stmt = $db->prepare("SELECT * FROM reservas WHERE aprovado=0 AND sala=?");
            $stmt->bind_param("s", $sala);
            $stmt->execute();
            $pedidos = $stmt->get_result();
            
            while ($pedido = $pedidos->fetch_assoc()) {
                $stmt2 = $db->prepare("SELECT nome FROM salas WHERE id=?");
                $stmt2->bind_param("s", $pedido['sala']);
                $stmt2->execute();
                $salaextenso = $stmt2->get_result()->fetch_assoc()['nome'];
                $stmt2->close();
                
                $stmt2 = $db->prepare("SELECT nome FROM cache WHERE id=?");
                $stmt2->bind_param("s", $pedido['requisitor']);
                $stmt2->execute();
                $requisitorextenso = $stmt2->get_result()->fetch_assoc()['nome'];
                $stmt2->close();
                
                $stmt2 = $db->prepare("SELECT horashumanos FROM tempos WHERE id=?");
                $stmt2->bind_param("s", $pedido['tempo']);
                $stmt2->execute();
                $horastempo = $stmt2->get_result()->fetch_assoc()['horashumanos'];
                $stmt2->close();
                
                $tempoEnc = urlencode($pedido['tempo']);
                $dataEnc = urlencode($pedido['data']);
                $salaEnc = urlencode($pedido['sala']);
                
                echo "<tr><td>" . htmlspecialchars($pedido['data'], ENT_QUOTES, 'UTF-8') . "</td>
                <td>" . htmlspecialchars($horastempo, ENT_QUOTES, 'UTF-8') . "</td>
                <td>" . htmlspecialchars($requisitorextenso, ENT_QUOTES, 'UTF-8') . "</td>
                <td>" . htmlspecialchars($pedido['motivo'], ENT_QUOTES, 'UTF-8') . "</td>";
                if ($pedido['aprovado'] == 0) {
                    echo "<td><a href='/admin/pedidos.php?subaction=aprovar&tempo={$tempoEnc}&data={$dataEnc}&sala={$salaEnc}' class='btn btn-success' onclick='return confirm(\"Tem a certeza que pretende aprovar esta reserva?\");'>Aprovar</a>
                 <a href='/admin/pedidos.php?subaction=rejeitar&tempo={$tempoEnc}&data={$dataEnc}&sala={$salaEnc}' class='btn btn-danger' onclick='return confirm(\"Tem a certeza que pretende rejeitar esta reserva? Esta ação irá notificar o utilizador, e irá apagar a reserva para libertar o tempo.\");'>Rejeitar</a>";
                } else {
                    echo "<td>";
                }
                echo " <a href='/reservar/manage.php?&tempo={$tempoEnc}&data={$dataEnc}&sala={$salaEnc}' class='btn btn-secondary'>Detalhes</a></td></tr>";
            }
            $stmt->close();
        }
        echo "</table>";
        echo "</div>";
    }
    ?>