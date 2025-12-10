<?php 
require '../index.php';
require_once(__DIR__ . '/../../func/email_helper.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
?>
<div style="margin-left: 20%; margin-right: 20%; text-align: center;">
<h1>Notificar por Email</h1>
<p>Este script permite enviar um email para todos os utilizadores com reservas de sala para esta semana.</p>
<p>O email será enviado em BCC (cópia oculta) para preservar a privacidade dos destinatários.</p>

<style>
    body {
        overflow-y: auto !important;
    }
    
    .preview-box {
        border: 2px solid #0d6efd;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
        background-color: #f8f9fa;
        text-align: left;
    }
    
    .recipient-list {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        background-color: #ffffff;
        text-align: left;
    }
    
    .recipient-item {
        padding: 0.5rem;
        margin-bottom: 0.5rem;
        border: 1px solid #e0e0e0;
        border-radius: 0.25rem;
        background-color: #f8f9fa;
    }
    
    @media (prefers-color-scheme: dark) {
        .preview-box {
            background-color: #343a40;
            border-color: #0d6efd;
        }
        
        .recipient-list {
            background-color: #212529;
        }
        
        .recipient-item {
            background-color: #343a40;
            border-color: #495057;
            color: #f8f9fa;
        }
    }
    
    .email-preview {
        background-color: #ffffff;
        border-radius: 8px;
        padding: 20px;
        margin-top: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    @media (prefers-color-scheme: dark) {
        .email-preview {
            background-color: #212529;
            color: #f8f9fa;
        }
    }
</style>

<script>
    function showPreview() {
        const subject = document.getElementById('subject').value;
        const message = document.getElementById('message').value;
        const identifySender = document.getElementById('identify_sender').checked;
        const senderName = document.getElementById('senderName').value;
        
        if (!subject || !message) {
            alert('Por favor, preencha o assunto e a mensagem antes de visualizar.');
            return;
        }
        
        // Build preview HTML
        let previewHTML = '<div class="preview-box">';
        previewHTML += '<h4>Pré-visualização do Email</h4>';
        previewHTML += '<hr>';
        
        // Show recipients
        const recipientCount = document.getElementById('recipientCount').value;
        const recipientList = document.getElementById('recipientListData').value;
        previewHTML += '<div class="mb-3">';
        previewHTML += '<strong>Destinatários (BCC):</strong> ' + recipientCount + ' utilizador(es)';
        if (recipientList) {
            previewHTML += '<div class="recipient-list mt-2">';
            const recipients = recipientList.split(',');
            recipients.forEach(recipient => {
                if (recipient.trim()) {
                    previewHTML += '<div class="recipient-item">' + escapeHtml(recipient.trim()) + '</div>';
                }
            });
            previewHTML += '</div>';
        }
        previewHTML += '</div>';
        previewHTML += '<hr>';
        
        // Email preview
        previewHTML += '<div class="email-preview">';
        previewHTML += '<p><strong>Assunto:</strong> ' + escapeHtml(subject) + '</p>';
        previewHTML += '<hr>';
        previewHTML += '<div style="white-space: pre-wrap;">' + escapeHtml(message) + '</div>';
        
        if (identifySender && senderName) {
            previewHTML += '<hr>';
            previewHTML += '<p><em>Enviado por: ' + escapeHtml(senderName) + '</em></p>';
        }
        previewHTML += '</div>';
        
        previewHTML += '</div>';
        
        document.getElementById('previewContainer').innerHTML = previewHTML;
        document.getElementById('previewContainer').style.display = 'block';
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function validateForm(event) {
        const subject = document.getElementById('subject').value;
        const message = document.getElementById('message').value;
        
        if (!subject || !message) {
            event.preventDefault();
            alert('Por favor, preencha o assunto e a mensagem.');
            return false;
        }
        
        const confirmed = confirm('Tem a certeza que deseja enviar este email para todos os destinatários?');
        if (!confirmed) {
            event.preventDefault();
            return false;
        }
        
        return true;
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', validateForm);
        }
    });
</script>

<?php
// Get users with reservations for this week
$startOfWeek = date('Y-m-d', strtotime('monday this week'));
$endOfWeek = date('Y-m-d', strtotime('sunday this week'));

$query = "SELECT DISTINCT c.id, c.nome, c.email 
          FROM cache c
          INNER JOIN reservas r ON c.id = r.requisitor
          WHERE r.data >= ? AND r.data <= ?
          ORDER BY c.nome ASC";

$stmt = $db->prepare($query);
$stmt->bind_param("ss", $startOfWeek, $endOfWeek);
$stmt->execute();
$result = $stmt->get_result();

$recipients = [];
$recipientListData = [];
while ($row = $result->fetch_assoc()) {
    $recipients[] = $row;
    $recipientListData[] = $row['nome'] . ' (' . $row['email'] . ')';
}
$stmt->close();

$recipientCount = count($recipients);
$recipientListString = implode(', ', $recipientListData);
?>

<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST" class="mt-4">
    <input type="hidden" id="recipientCount" value="<?php echo $recipientCount; ?>">
    <input type="hidden" id="recipientListData" value="<?php echo htmlspecialchars($recipientListString, ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" id="senderName" value="<?php echo htmlspecialchars($_SESSION['nome'], ENT_QUOTES, 'UTF-8'); ?>">
    
    <div class="alert alert-info">
        <strong>Informação:</strong> Foram encontrados <strong><?php echo $recipientCount; ?></strong> utilizador(es) com reservas para esta semana (<?php echo date('d/m/Y', strtotime($startOfWeek)); ?> - <?php echo date('d/m/Y', strtotime($endOfWeek)); ?>).
    </div>
    
    <?php if ($recipientCount == 0): ?>
        <div class="alert alert-warning">
            <strong>Aviso:</strong> Não existem utilizadores com reservas para esta semana. Não é possível enviar emails.
        </div>
    <?php else: ?>
    
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="form-floating">
                <input type="text" class="form-control" id="subject" name="subject" placeholder="Assunto" value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
                <label for="subject">Assunto do Email</label>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="form-floating">
                <textarea class="form-control" id="message" name="message" placeholder="Mensagem" style="height: 200px;" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                <label for="message">Mensagem do Email</label>
            </div>
            <small class="text-muted">Digite a mensagem que deseja enviar para todos os utilizadores com reservas esta semana.</small>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="identify_sender" name="identify_sender" value="1" <?php echo (isset($_POST['identify_sender']) && $_POST['identify_sender'] == '1') ? 'checked' : ''; ?>>
                <label class="form-check-label" for="identify_sender">
                    Identificar-me como remetente (o seu nome será adicionado no final da mensagem)
                </label>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2">
        <button type="button" class="btn btn-info btn-lg" onclick="showPreview()">
            Pré-visualizar Email
        </button>
        <button type="submit" class="btn btn-primary btn-lg">
            Enviar Email
        </button>
    </div>
    
    <?php endif; ?>
</form>

<div id="previewContainer" style="display: none; margin-top: 30px;"></div>

<?php
// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject']) && isset($_POST['message'])) {
    $subject = $_POST['subject'];
    $messageBody = $_POST['message'];
    $identifySender = isset($_POST['identify_sender']) && $_POST['identify_sender'] == '1';
    
    if (empty($subject) || empty($messageBody)) {
        echo "<div class='mt-3 alert alert-danger fade show' role='alert'>
            <strong>Erro:</strong> O assunto e a mensagem são obrigatórios.
        </div>";
    } elseif ($recipientCount == 0) {
        echo "<div class='mt-3 alert alert-danger fade show' role='alert'>
            <strong>Erro:</strong> Não existem destinatários para enviar o email.
        </div>";
    } else {
        // Build the email body
        $bodyContent = '<p>' . nl2br(htmlspecialchars($messageBody, ENT_QUOTES, 'UTF-8')) . '</p>';
        
        if ($identifySender) {
            $bodyContent .= '<hr>';
            $bodyContent .= '<p><em>Enviado por: ' . htmlspecialchars($_SESSION['nome'], ENT_QUOTES, 'UTF-8') . '</em></p>';
        }
        
        // Send email to all recipients in BCC
        try {
            global $mail;
            
            // Check if email is enabled
            if ($mail['ativado'] != true) {
                echo "<div class='mt-3 alert alert-danger fade show' role='alert'>
                    <strong>Erro:</strong> O sistema de email não está ativado.
                </div>";
            } else {
                $mailer = new PHPMailer(true);
                $mailer->isSMTP();
                $mailer->Host = $mail['servidor'];
                $mailer->SMTPAuth = $mail['autenticacao'];
                $mailer->Username = $mail['username'];
                $mailer->Password = $mail['password'];
                $mailer->SMTPSecure = $mail['tipodeseguranca'];
                $mailer->Port = $mail['porta'];
                $mailer->CharSet = 'UTF-8';
                $mailer->Encoding = 'base64';
                
                $mailer->setFrom($mail['mailfrom'], $mail['fromname']);
                
                // Add all recipients as BCC
                foreach ($recipients as $recipient) {
                    $mailer->addBCC($recipient['email']);
                }
                
                $mailer->isHTML(true);
                $mailer->Subject = $subject;
                
                // Build full HTML email
                $htmlBody = "
<!DOCTYPE html>
<html lang='pt'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>" . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . "</title>
</head>
<body style='margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, Helvetica, sans-serif;'>
    <table role='presentation' cellpadding='0' cellspacing='0' border='0' width='100%' style='background-color: #f4f4f4;'>
        <tr>
            <td align='center' style='padding: 40px 20px;'>
                <table role='presentation' cellpadding='0' cellspacing='0' border='0' width='600' style='max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);'>
                    
                    <tr>
                        <td style='background-color: #0d6efd; padding: 30px 40px; text-align: center;'>
                            <h1 style='margin: 0; color: #ffffff; font-size: 24px; font-weight: bold;'>ClassLink - Notificação</h1>
                        </td>
                    </tr>
                    
                    <tr>
                        <td style='padding: 40px; color: #333333; font-size: 16px; line-height: 1.6;'>
                            {$bodyContent}
                        </td>
                    </tr>
                    
                    <tr>
                        <td style='background-color: #f8f9fa; padding: 25px 40px; text-align: center; border-top: 1px solid #e9ecef;'>
                            <p style='margin: 0 0 10px 0; color: #6c757d; font-size: 14px;'>
                                Este email foi enviado automaticamente pelo sistema ClassLink. Não responda a este email.
                            </p>
                            <p style='margin: 0; color: #6c757d; font-size: 12px;'>
                                Agrupamento de Escolas Joaquim Inácio da Cruz Sobral
                            </p>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>";
                
                $mailer->Body = $htmlBody;
                
                // Plain text alternative
                $plainBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $bodyContent));
                $plainBody .= "\n\n---\nEste email foi enviado automaticamente pelo sistema ClassLink. Não responda a este email.\nAgrupamento de Escolas Joaquim Inácio da Cruz Sobral";
                $mailer->AltBody = $plainBody;
                
                $mailer->send();
                
                // Log the action
                require_once(__DIR__ . '/../../func/logaction.php');
                logaction("Email enviado para {$recipientCount} utilizadores com reservas esta semana. Assunto: {$subject}", $_SESSION['id']);
                
                echo "<div class='mt-3 alert alert-success fade show' role='alert'>
                    <strong>Sucesso!</strong> Email enviado com sucesso para {$recipientCount} destinatário(s) em BCC.
                </div>";
                
                echo "<div class='mt-3 alert alert-info fade show' role='alert'>
                    <strong>Resumo:</strong><br>
                    - Assunto: " . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . "<br>
                    - Destinatários: {$recipientCount}<br>
                    - Remetente identificado: " . ($identifySender ? 'Sim' : 'Não') . "
                </div>";
            }
        } catch (Exception $e) {
            echo "<div class='mt-3 alert alert-danger fade show' role='alert'>
                <strong>Erro ao enviar email:</strong> " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "
            </div>";
        }
    }
}
?>
</div>
