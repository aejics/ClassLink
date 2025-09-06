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
            <label for="requisitor">Requisitor</label>
        </div>
    </form>

    <?php
    if ($_GET['subaction']) {
        require '../vendor/phpmailer/phpmailer/src/Exception.php';
        require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require '../vendor/phpmailer/phpmailer/src/SMTP.php';

        switch ($_GET['subaction']) {
            case "aprovar":
                $requisitor = $db->query("SELECT requisitor FROM reservas WHERE sala='{$_GET['sala']}' AND tempo='{$_GET['tempo']}' AND data='{$_GET['data']}';")->fetch_assoc()['requisitor'];
                $db->query("UPDATE reservas SET aprovado=1 WHERE sala='{$_GET['sala']}' AND tempo='{$_GET['tempo']}' AND data='{$_GET['data']}';");
                echo "<div class='mt-2 alert alert-success fade show' role='alert'>Reserva aprovada com sucesso.</div>";
                echo "<a href='/admin/pedidos.php'><button class='btn btn-primary'>Voltar</button></a>";
                try {
                    $sala = $db->query("SELECT nome FROM salas WHERE id='{$_GET['sala']}';")->fetch_assoc()['nome'];
                    $maildapessoa = $db->query("SELECT email FROM cache WHERE id='{$requisitor}';")->fetch_assoc()['email'];
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
                    $enviarmail->addAddress($maildapessoa);
                    $enviarmail->isHTML(false);
                    $enviarmail->Subject = utf8_decode("Reserva da Sala {$sala} Aprovada");
                    $enviarmail->Body = utf8_decode("A sua reserva da sala {$sala} para a data de {$_GET['data']} às {$tempohumano} foi aprovada.\n\nObrigado.");
                    $enviarmail->send();
                } catch (Exception $e) {
                    echo "<div class='mt-2 alert alert-warning fade show' role='alert'>A reserva foi aprovada, mas o email de notificação não foi enviado. Contacte o Postmaster.\nErro do PHPMailer: {$enviarmail->ErrorInfo}</div>";
                }
                break;
            case "rejeitar":
                $requisitor = $db->query("SELECT requisitor FROM reservas WHERE sala='{$_GET['sala']}' AND tempo='{$_GET['tempo']}' AND data='{$_GET['data']}';")->fetch_assoc()['requisitor'];
                $db->query("DELETE FROM reservas WHERE sala='{$_GET['sala']}' AND tempo='{$_GET['tempo']}' AND data='{$_GET['data']}';");
                echo "<div class='mt-2 alert alert-danger fade show' role='alert'>Reserva rejeitada com sucesso.</div>";
                echo "<a href='/admin/pedidos.php'><button class='btn btn-primary'>Voltar</button></a>";
                try {
                    $sala = $db->query("SELECT nome FROM salas WHERE id='{$_GET['sala']}';")->fetch_assoc()['nome'];
                    $maildapessoa = $db->query("SELECT email FROM cache WHERE id='{$requisitor}';")->fetch_assoc()['email'];
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
                    $enviarmail->addAddress($maildapessoa);
                    $enviarmail->isHTML(false);
                    $enviarmail->Subject = utf8_decode("Reserva da Sala {$sala} Rejeitada");
                    $enviarmail->Body = utf8_decode("A sua reserva da sala {$sala} para a data de {$_GET['data']} às {$tempohumano} foi rejeitada.\n\nObrigado.");
                    $enviarmail->send();
                } catch (Exception $e) {
                    echo "<div class='mt-2 alert alert-warning fade show' role='alert'>A reserva foi rejeitada, mas o email de notificação não foi enviado. Contacte o Postmaster.\nErro do PHPMailer: {$enviarmail->ErrorInfo}</div>";
                }
                break;
            case "detalhes":
        }
    } elseif ($_POST['sala'] | $_GET['sala'] | isset($_POST['requisitor'])) {
        echo "<div style='max-height: 400px; overflow-y: auto; width: 100%;'>";
        echo "<table class='table'><tr><th scope='col'>Data</th><th scope='col'>Tempo</th><th scope='col'>Requisitor</th><th scope='col'>Motivo</th><th scope='col'>AÇÕES</th></tr>";
        if ($_POST['sala']) {
            $sala = $_POST['sala'];
        } else {
            $sala = $_GET['sala'];
        }
        if ($_POST['requisitor']) {
            $pedidos = $db->query("SELECT * FROM reservas WHERE requisitor='{$_POST['requisitor']}' ORDER BY data DESC;");
            while ($pedido = $pedidos->fetch_assoc()) {
                $salaextenso = $db->query("SELECT nome FROM salas WHERE id='{$pedido['sala']}';")->fetch_assoc()['nome'];
                $requisitorextenso = $db->query("SELECT nome FROM cache WHERE id='{$pedido['requisitor']}';")->fetch_assoc()['nome'];
                $horastempo = $db->query("SELECT horashumanos FROM tempos WHERE id='{$pedido['tempo']}';")->fetch_assoc()['horashumanos'];
                echo "<tr><td>{$pedido['data']}</td>
                <td>{$horastempo}</td>
                <td>{$requisitorextenso}</td>
                <td>{$pedido['motivo']}</td>";
                if ($pedido['aprovado'] == 0) {
                    echo "<td><a href='/admin/pedidos.php?subaction=aprovar&tempo={$pedido['tempo']}&data={$pedido['data']}&sala={$pedido['sala']}' class='btn btn-success' onclick='return confirm('Tem a certeza que pretende aprovar esta reserva?');'>Aprovar</a>
                 <a href='/admin/pedidos.php?subaction=rejeitar&tempo={$pedido['tempo']}&data={$pedido['data']}&sala={$pedido['sala']}' class='btn btn-danger' onclick='return confirm('Tem a certeza que pretende rejeitar esta reserva? Esta ação irá notificar o utilizador, e irá apagar a reserva para libertar o tempo.');'>Rejeitar</a>";
                } else {
                    echo "<td>";
                }
                echo " <a href='/reservar/manage.php?&tempo={$pedido['tempo']}&data={$pedido['data']}&sala={$pedido['sala']}' class='btn btn-secondary'>Detalhes</a></td></tr>";
            }
        } else {
            $pedidos = $db->query("SELECT * FROM reservas WHERE aprovado=0 AND sala='{$sala}';");
            while ($pedido = $pedidos->fetch_assoc()) {
                $salaextenso = $db->query("SELECT nome FROM salas WHERE id='{$pedido['sala']}';")->fetch_assoc()['nome'];
                $requisitorextenso = $db->query("SELECT nome FROM cache WHERE id='{$pedido['requisitor']}';")->fetch_assoc()['nome'];
                $horastempo = $db->query("SELECT horashumanos FROM tempos WHERE id='{$pedido['tempo']}';")->fetch_assoc()['horashumanos'];
                echo "<tr><td>{$pedido['data']}</td>
                <td>{$horastempo}</td>
                <td>{$requisitorextenso}</td>
                <td>{$pedido['motivo']}</td>";
                if ($pedido['aprovado'] == 0) {
                    echo "<td><a href='/admin/pedidos.php?subaction=aprovar&tempo={$pedido['tempo']}&data={$pedido['data']}&sala={$pedido['sala']}' class='btn btn-success' onclick='return confirm(\"Tem a certeza que pretende aprovar esta reserva?\");'>Aprovar</a>
                 <a href='/admin/pedidos.php?subaction=rejeitar&tempo={$pedido['tempo']}&data={$pedido['data']}&sala={$pedido['sala']}' class='btn btn-danger' onclick='return confirm(\"Tem a certeza que pretende rejeitar esta reserva? Esta ação irá notificar o utilizador, e irá apagar a reserva para libertar o tempo.\");'>Rejeitar</a>";
                } else {
                    echo "<td>";
                }
                echo " <a href='/reservar/manage.php?&tempo={$pedido['tempo']}&data={$pedido['data']}&sala={$pedido['sala']}' class='btn btn-secondary'>Detalhes</a></td></tr>";
            }
        }
        echo "</table>";
        echo "</div>";
    }
    ?>