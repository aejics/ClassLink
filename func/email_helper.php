<?php
/**
 * Email Helper for ClassLink
 * Provides centralized email functionality with HTML templates
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once(__DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php');
require_once(__DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php');
require_once(__DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php');
require_once(__DIR__ . '/../src/config.php');

/**
 * Send a styled HTML email using the ClassLink template
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $heading Main heading text
 * @param string $bodyContent HTML content for the email body
 * @param string $type Email type: 'success', 'warning', 'danger', 'info' (affects header color)
 * @param string|null $buttonUrl Optional CTA button URL
 * @param string|null $buttonText Optional CTA button text
 * @return array ['success' => bool, 'error' => string|null]
 */
function sendStyledEmail($to, $subject, $heading, $bodyContent, $type = 'info', $buttonUrl = null, $buttonText = null) {
    global $mail;
    
    // Check if email is enabled
    if ($mail['ativado'] != true) {
        return ['success' => false, 'error' => 'Email not enabled'];
    }
    
    // Define colors based on type
    $colors = [
        'success' => ['header' => '#28a745', 'headerText' => '#ffffff', 'accent' => '#28a745'],
        'warning' => ['header' => '#ffc107', 'headerText' => '#212529', 'accent' => '#ffc107'],
        'danger' => ['header' => '#dc3545', 'headerText' => '#ffffff', 'accent' => '#dc3545'],
        'info' => ['header' => '#17a2b8', 'headerText' => '#ffffff', 'accent' => '#17a2b8'],
        'primary' => ['header' => '#0d6efd', 'headerText' => '#ffffff', 'accent' => '#0d6efd']
    ];
    
    $color = $colors[$type] ?? $colors['info'];
    
    // Build button HTML if provided
    $buttonHtml = '';
    if ($buttonUrl && $buttonText) {
        $buttonHtml = "
        <table cellpadding='0' cellspacing='0' border='0' style='margin: 25px auto;'>
            <tr>
                <td align='center' bgcolor='{$color['accent']}' style='border-radius: 6px;'>
                    <a href='{$buttonUrl}' target='_blank' style='display: inline-block; padding: 14px 30px; font-family: Arial, sans-serif; font-size: 16px; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;'>{$buttonText}</a>
                </td>
            </tr>
        </table>";
    }
    
    // Build the HTML email template
    $htmlBody = "
<!DOCTYPE html>
<html lang='pt'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>{$subject}</title>
</head>
<body style='margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, Helvetica, sans-serif;'>
    <table role='presentation' cellpadding='0' cellspacing='0' border='0' width='100%' style='background-color: #f4f4f4;'>
        <tr>
            <td align='center' style='padding: 40px 20px;'>
                <table role='presentation' cellpadding='0' cellspacing='0' border='0' width='600' style='max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);'>
                    
                    <!-- Header -->
                    <tr>
                        <td style='background-color: {$color['header']}; padding: 30px 40px; text-align: center;'>
                            <h1 style='margin: 0; color: {$color['headerText']}; font-size: 24px; font-weight: bold;'>{$heading}</h1>
                        </td>
                    </tr>
                    
                    <!-- Body Content -->
                    <tr>
                        <td style='padding: 40px; color: #333333; font-size: 16px; line-height: 1.6;'>
                            {$bodyContent}
                            {$buttonHtml}
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style='background-color: #f8f9fa; padding: 25px 40px; text-align: center; border-top: 1px solid #e9ecef;'>
                            <p style='margin: 0 0 10px 0; color: #6c757d; font-size: 14px;'>
                                Este email foi enviado automaticamente pelo sistema ClassLink.
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

    // Plain text alternative
    $plainBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $bodyContent));
    if ($buttonUrl) {
        $plainBody .= "\n\nAceder: {$buttonUrl}";
    }
    $plainBody .= "\n\n---\nEste email foi enviado automaticamente pelo sistema ClassLink.\nAgrupamento de Escolas Joaquim Inácio da Cruz Sobral";

    try {
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
        $mailer->addAddress($to);
        
        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $htmlBody;
        $mailer->AltBody = $plainBody;
        
        $mailer->send();
        return ['success' => true, 'error' => null];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $mailer->ErrorInfo];
    }
}

/**
 * Build reservation details HTML block for emails
 * 
 * @param string $roomName Room name
 * @param string $date Date of reservation
 * @param string $time Time slot
 * @param string|null $reason Reservation reason
 * @param string|null $requesterName Name of person who made the reservation
 * @return string HTML content
 */
function buildReservationDetailsHtml($roomName, $date, $time, $reason = null, $requesterName = null) {
    $html = "
    <table cellpadding='0' cellspacing='0' border='0' width='100%' style='background-color: #f8f9fa; border-radius: 8px; margin: 20px 0;'>
        <tr>
            <td style='padding: 20px;'>
                <table cellpadding='0' cellspacing='0' border='0' width='100%'>
                    <tr>
                        <td style='padding: 8px 0; border-bottom: 1px solid #e9ecef;'>
                            <strong style='color: #495057;'>Sala:</strong>
                            <span style='color: #212529; float: right;'>" . htmlspecialchars($roomName, ENT_QUOTES, 'UTF-8') . "</span>
                        </td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; border-bottom: 1px solid #e9ecef;'>
                            <strong style='color: #495057;'>Data:</strong>
                            <span style='color: #212529; float: right;'>" . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . "</span>
                        </td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0;" . ($reason || $requesterName ? " border-bottom: 1px solid #e9ecef;" : "") . "'>
                            <strong style='color: #495057;'>Horário:</strong>
                            <span style='color: #212529; float: right;'>" . htmlspecialchars($time, ENT_QUOTES, 'UTF-8') . "</span>
                        </td>
                    </tr>";
    
    if ($requesterName) {
        $html .= "
                    <tr>
                        <td style='padding: 8px 0;" . ($reason ? " border-bottom: 1px solid #e9ecef;" : "") . "'>
                            <strong style='color: #495057;'>Requisitado por:</strong>
                            <span style='color: #212529; float: right;'>" . htmlspecialchars($requesterName, ENT_QUOTES, 'UTF-8') . "</span>
                        </td>
                    </tr>";
    }
    
    if ($reason) {
        $html .= "
                    <tr>
                        <td style='padding: 8px 0;'>
                            <strong style='color: #495057;'>Motivo:</strong>
                            <span style='color: #212529; float: right;'>" . htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') . "</span>
                        </td>
                    </tr>";
    }
    
    $html .= "
                </table>
            </td>
        </tr>
    </table>";
    
    return $html;
}

/**
 * Send reservation creation confirmation email
 */
function sendReservationCreatedEmail($db, $requisitorId, $salaId, $tempoId, $data, $motivo, $isAutonomous = false) {
    // Get requisitor email
    $stmt = $db->prepare("SELECT email, nome FROM cache WHERE id = ?");
    $stmt->bind_param("s", $requisitorId);
    $stmt->execute();
    $requisitor = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$requisitor) {
        return ['success' => false, 'error' => 'Requisitor not found'];
    }
    
    // Get room name
    $stmt = $db->prepare("SELECT nome FROM salas WHERE id = ?");
    $stmt->bind_param("s", $salaId);
    $stmt->execute();
    $sala = $stmt->get_result()->fetch_assoc()['nome'];
    $stmt->close();
    
    // Get time slot
    $stmt = $db->prepare("SELECT horashumanos FROM tempos WHERE id = ?");
    $stmt->bind_param("s", $tempoId);
    $stmt->execute();
    $tempo = $stmt->get_result()->fetch_assoc()['horashumanos'];
    $stmt->close();
    
    $reservaUrl = "https://" . $_SERVER['HTTP_HOST'] . "/reservar/manage.php?sala=" . urlencode($salaId) . "&tempo=" . urlencode($tempoId) . "&data=" . urlencode($data);
    
    if ($isAutonomous) {
        $heading = "Reserva Aprovada Automaticamente";
        $type = 'success';
        $bodyContent = "
            <p>Olá <strong>" . htmlspecialchars($requisitor['nome'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p>A sua reserva foi criada e <strong>aprovada automaticamente</strong> pois esta sala é de reserva autónoma.</p>
            " . buildReservationDetailsHtml($sala, $data, $tempo, $motivo) . "
            <p>Pode ver todos os detalhes e informações importantes sobre a sua reserva clicando no botão abaixo.</p>";
    } else {
        $heading = "Reserva Submetida";
        $type = 'info';
        $bodyContent = "
            <p>Olá <strong>" . htmlspecialchars($requisitor['nome'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p>A sua reserva foi submetida com sucesso e está <strong>aguardando aprovação</strong>.</p>
            " . buildReservationDetailsHtml($sala, $data, $tempo, $motivo) . "
            <p>Irá receber um email assim que a sua reserva for aprovada ou rejeitada.</p>";
    }
    
    return sendStyledEmail(
        $requisitor['email'],
        "ClassLink - {$heading}: {$sala}",
        $heading,
        $bodyContent,
        $type,
        $reservaUrl,
        "Ver Detalhes da Reserva"
    );
}

/**
 * Send reservation approval email
 */
function sendReservationApprovedEmail($db, $requisitorId, $salaId, $tempoId, $data) {
    // Get requisitor email
    $stmt = $db->prepare("SELECT email, nome FROM cache WHERE id = ?");
    $stmt->bind_param("s", $requisitorId);
    $stmt->execute();
    $requisitor = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$requisitor) {
        return ['success' => false, 'error' => 'Requisitor not found'];
    }
    
    // Get room name
    $stmt = $db->prepare("SELECT nome FROM salas WHERE id = ?");
    $stmt->bind_param("s", $salaId);
    $stmt->execute();
    $sala = $stmt->get_result()->fetch_assoc()['nome'];
    $stmt->close();
    
    // Get time slot
    $stmt = $db->prepare("SELECT horashumanos FROM tempos WHERE id = ?");
    $stmt->bind_param("s", $tempoId);
    $stmt->execute();
    $tempo = $stmt->get_result()->fetch_assoc()['horashumanos'];
    $stmt->close();
    
    $reservaUrl = "https://" . $_SERVER['HTTP_HOST'] . "/reservar/manage.php?sala=" . urlencode($salaId) . "&tempo=" . urlencode($tempoId) . "&data=" . urlencode($data);
    
    $bodyContent = "
        <p>Olá <strong>" . htmlspecialchars($requisitor['nome'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
        <p>Temos boas notícias! A sua reserva foi <strong style='color: #28a745;'>aprovada</strong>.</p>
        " . buildReservationDetailsHtml($sala, $data, $tempo) . "
        <p>Clique no botão abaixo para ver todos os detalhes e informações importantes sobre a sua reserva.</p>";
    
    return sendStyledEmail(
        $requisitor['email'],
        "ClassLink - Reserva Aprovada: {$sala}",
        "🎉 Reserva Aprovada",
        $bodyContent,
        'success',
        $reservaUrl,
        "Ver Detalhes da Reserva"
    );
}

/**
 * Send reservation rejection email
 */
function sendReservationRejectedEmail($db, $requisitorId, $salaId, $tempoId, $data) {
    // Get requisitor email
    $stmt = $db->prepare("SELECT email, nome FROM cache WHERE id = ?");
    $stmt->bind_param("s", $requisitorId);
    $stmt->execute();
    $requisitor = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$requisitor) {
        return ['success' => false, 'error' => 'Requisitor not found'];
    }
    
    // Get room name
    $stmt = $db->prepare("SELECT nome FROM salas WHERE id = ?");
    $stmt->bind_param("s", $salaId);
    $stmt->execute();
    $sala = $stmt->get_result()->fetch_assoc()['nome'];
    $stmt->close();
    
    // Get time slot
    $stmt = $db->prepare("SELECT horashumanos FROM tempos WHERE id = ?");
    $stmt->bind_param("s", $tempoId);
    $stmt->execute();
    $tempo = $stmt->get_result()->fetch_assoc()['horashumanos'];
    $stmt->close();
    
    $reservarUrl = "https://" . $_SERVER['HTTP_HOST'] . "/reservar";
    
    $bodyContent = "
        <p>Olá <strong>" . htmlspecialchars($requisitor['nome'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
        <p>Lamentamos informar que a sua reserva foi <strong style='color: #dc3545;'>rejeitada</strong>.</p>
        " . buildReservationDetailsHtml($sala, $data, $tempo) . "
        <p>Se tiver dúvidas sobre esta decisão, por favor contacte a administração.</p>
        <p>Pode efetuar uma nova reserva clicando no botão abaixo.</p>";
    
    return sendStyledEmail(
        $requisitor['email'],
        "ClassLink - Reserva Rejeitada: {$sala}",
        "Reserva Rejeitada",
        $bodyContent,
        'danger',
        $reservarUrl,
        "Fazer Nova Reserva"
    );
}

/**
 * Send reservation deletion email (to the person who made the reservation)
 */
function sendReservationDeletedEmail($db, $requisitorId, $salaId, $tempoId, $data, $deletedByAdmin = false) {
    // Get requisitor email
    $stmt = $db->prepare("SELECT email, nome FROM cache WHERE id = ?");
    $stmt->bind_param("s", $requisitorId);
    $stmt->execute();
    $requisitor = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$requisitor) {
        return ['success' => false, 'error' => 'Requisitor not found'];
    }
    
    // Get room name
    $stmt = $db->prepare("SELECT nome FROM salas WHERE id = ?");
    $stmt->bind_param("s", $salaId);
    $stmt->execute();
    $sala = $stmt->get_result()->fetch_assoc()['nome'];
    $stmt->close();
    
    // Get time slot
    $stmt = $db->prepare("SELECT horashumanos FROM tempos WHERE id = ?");
    $stmt->bind_param("s", $tempoId);
    $stmt->execute();
    $tempo = $stmt->get_result()->fetch_assoc()['horashumanos'];
    $stmt->close();
    
    $reservarUrl = "https://" . $_SERVER['HTTP_HOST'] . "/reservar";
    
    if ($deletedByAdmin) {
        $bodyContent = "
            <p>Olá <strong>" . htmlspecialchars($requisitor['nome'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p>A sua reserva foi <strong>removida</strong> por um administrador.</p>
            " . buildReservationDetailsHtml($sala, $data, $tempo) . "
            <p>Se tiver dúvidas sobre esta ação, por favor contacte a administração.</p>";
    } else {
        $bodyContent = "
            <p>Olá <strong>" . htmlspecialchars($requisitor['nome'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p>A sua reserva foi <strong>removida</strong> com sucesso.</p>
            " . buildReservationDetailsHtml($sala, $data, $tempo) . "
            <p>Pode efetuar uma nova reserva a qualquer momento.</p>";
    }
    
    return sendStyledEmail(
        $requisitor['email'],
        "ClassLink - Reserva Removida: {$sala}",
        "Reserva Removida",
        $bodyContent,
        'warning',
        $reservarUrl,
        "Fazer Nova Reserva"
    );
}

/**
 * Send bulk reservation confirmation email
 */
function sendBulkReservationsEmail($db, $requisitorId, $successCount, $failedCount, $salaId = null, $isAutonomous = false) {
    // Get requisitor email
    $stmt = $db->prepare("SELECT email, nome FROM cache WHERE id = ?");
    $stmt->bind_param("s", $requisitorId);
    $stmt->execute();
    $requisitor = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$requisitor) {
        return ['success' => false, 'error' => 'Requisitor not found'];
    }
    
    // Get room name if provided
    $salaName = '';
    if ($salaId) {
        $stmt = $db->prepare("SELECT nome FROM salas WHERE id = ?");
        $stmt->bind_param("s", $salaId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($result) {
            $salaName = $result['nome'];
        }
    }
    
    $reservasUrl = "https://" . $_SERVER['HTTP_HOST'] . "/reservas";
    
    if ($isAutonomous) {
        $heading = "Reservas Aprovadas Automaticamente";
        $type = 'success';
        $statusText = "aprovadas automaticamente";
    } else {
        $heading = "Reservas Submetidas";
        $type = 'info';
        $statusText = "submetidas para aprovação";
    }
    
    $bodyContent = "
        <p>Olá <strong>" . htmlspecialchars($requisitor['nome'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
        <p>As suas reservas em massa foram processadas.</p>
        
        <table cellpadding='0' cellspacing='0' border='0' width='100%' style='background-color: #f8f9fa; border-radius: 8px; margin: 20px 0;'>
            <tr>
                <td style='padding: 20px;'>
                    <table cellpadding='0' cellspacing='0' border='0' width='100%'>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #e9ecef;'>
                                <strong style='color: #495057;'>Reservas {$statusText}:</strong>
                                <span style='color: #28a745; font-weight: bold; float: right;'>{$successCount}</span>
                            </td>
                        </tr>" .
        ($failedCount > 0 ? "
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #e9ecef;'>
                                <strong style='color: #495057;'>Reservas falhadas:</strong>
                                <span style='color: #dc3545; font-weight: bold; float: right;'>{$failedCount}</span>
                            </td>
                        </tr>" : "") .
        ($salaName ? "
                        <tr>
                            <td style='padding: 8px 0;'>
                                <strong style='color: #495057;'>Sala:</strong>
                                <span style='color: #212529; float: right;'>" . htmlspecialchars($salaName, ENT_QUOTES, 'UTF-8') . "</span>
                            </td>
                        </tr>" : "") . "
                    </table>
                </td>
            </tr>
        </table>
        
        <p>Clique no botão abaixo para ver todas as suas reservas.</p>";
    
    return sendStyledEmail(
        $requisitor['email'],
        "ClassLink - {$heading}",
        $heading,
        $bodyContent,
        $type,
        $reservasUrl,
        "Ver Minhas Reservas"
    );
}
