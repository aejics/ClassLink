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
 * Get the safe base URL for the application
 * Uses HTTP_HOST but validates it's a reasonable hostname to prevent Host Header Injection
 * 
 * @return string The base URL (e.g., https://example.com)
 */
function getBaseUrl() {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Validate host format - must be a valid hostname (alphanumeric, dots, hyphens, and optional port)
    if (!preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9\-\.]*[a-zA-Z0-9])?(:[0-9]+)?$/', $host)) {
        // Fall back to a safe default if host looks suspicious
        $host = 'localhost';
    }
    
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $protocol . '://' . $host;
}

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
        $escapedButtonUrl = htmlspecialchars($buttonUrl, ENT_QUOTES, 'UTF-8');
        $escapedButtonText = htmlspecialchars($buttonText, ENT_QUOTES, 'UTF-8');
        $buttonHtml = "
        <table cellpadding='0' cellspacing='0' border='0' style='margin: 25px auto;'>
            <tr>
                <td align='center' bgcolor='{$color['accent']}' style='border-radius: 6px;'>
                    <a href='{$escapedButtonUrl}' target='_blank' style='display: inline-block; padding: 14px 30px; font-family: Arial, sans-serif; font-size: 16px; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;'>{$escapedButtonText}</a>
                </td>
            </tr>
        </table>";
    }
    
    // Escape subject and heading for use in HTML
    $escapedSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    $escapedHeading = htmlspecialchars($heading, ENT_QUOTES, 'UTF-8');
    
    // Build the HTML email template
    $htmlBody = "
<!DOCTYPE html>
<html lang='pt'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>{$escapedSubject}</title>
</head>
<body style='margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, Helvetica, sans-serif;'>
    <table role='presentation' cellpadding='0' cellspacing='0' border='0' width='100%' style='background-color: #f4f4f4;'>
        <tr>
            <td align='center' style='padding: 40px 20px;'>
                <table role='presentation' cellpadding='0' cellspacing='0' border='0' width='600' style='max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);'>
                    
                    <!-- Header -->
                    <tr>
                        <td style='background-color: {$color['header']}; padding: 30px 40px; text-align: center;'>
                            <h1 style='margin: 0; color: {$color['headerText']}; font-size: 24px; font-weight: bold;'>{$escapedHeading}</h1>
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

    // Plain text alternative
    $plainBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $bodyContent));
    if ($buttonUrl) {
        $plainBody .= "\n\nAceder: {$buttonUrl}";
    }
    $plainBody .= "\n\n---\nEste email foi enviado automaticamente pelo sistema ClassLink. Não responda a este email.\nAgrupamento de Escolas Joaquim Inácio da Cruz Sobral";

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
    
    $baseUrl = getBaseUrl();
    $reservaUrl = $baseUrl . "/reservar/manage.php?sala=" . urlencode($salaId) . "&tempo=" . urlencode($tempoId) . "&data=" . urlencode($data);
    
    if ($isAutonomous) {
        $heading = "Confirmação de Reserva da Sala";
        $type = 'success';
        $bodyContent = "
            <p>Olá <strong>" . htmlspecialchars($requisitor['nome'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p>Informamos que a sua reserva foi criada com sucesso.</p>
            " . buildReservationDetailsHtml($sala, $data, $tempo, $motivo) . "
            <p>Pode ver todos os detalhes e informações importantes sobre a sua reserva através do botão em baixo.</p>";
    } else {
        $heading = "Reserva Submetida";
        $type = 'info';
        $bodyContent = "
            <p>Olá <strong>" . htmlspecialchars($requisitor['nome'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p>A sua reserva foi submetida com sucesso e está <strong>a aguardar aprovação</strong>.</p>
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
    
    $baseUrl = getBaseUrl();
    $reservaUrl = $baseUrl . "/reservar/manage.php?sala=" . urlencode($salaId) . "&tempo=" . urlencode($tempoId) . "&data=" . urlencode($data);
    
    $bodyContent = "
        <p>Olá <strong>" . htmlspecialchars($requisitor['nome'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
        <p>Temos boas notícias! A sua reserva foi <strong style='color: #28a745;'>aprovada</strong>.</p>
        " . buildReservationDetailsHtml($sala, $data, $tempo) . "
        <p>Carregue no botão em baixo para ver todos os detalhes e informações importantes sobre a sua reserva.</p>";
    
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
    
    $baseUrl = getBaseUrl();
    $reservarUrl = $baseUrl . "/reservar";
    
    $bodyContent = "
        <p>Olá <strong>" . htmlspecialchars($requisitor['nome'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
        <p>Lamentamos informar que a sua reserva foi <strong style='color: #dc3545;'>rejeitada</strong>.</p>
        " . buildReservationDetailsHtml($sala, $data, $tempo) . "
        <p>Pode efetuar um novo pedido através no botão em baixo.</p>";
    
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
    
    $baseUrl = getBaseUrl();
    $reservarUrl = $baseUrl . "/reservar";
    
    if ($deletedByAdmin) {
        $bodyContent = "
            <p>Olá <strong>" . htmlspecialchars($requisitor['nome'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p>Informamos que a sua reserva foi <strong>removida</strong> por um administrador.</p>
            " . buildReservationDetailsHtml($sala, $data, $tempo);
    } else {
        $bodyContent = "
            <p>Olá <strong>" . htmlspecialchars($requisitor['nome'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p>Informamos que a sua reserva foi <strong>removida</strong> com sucesso.</p>
            " . buildReservationDetailsHtml($sala, $data, $tempo) . "
            <p>Pode sempre efetuar uma nova reserva a qualquer momento.</p>";
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
    
    $baseUrl = getBaseUrl();
    $reservasUrl = $baseUrl . "/reservas";
    
    if ($isAutonomous) {
        $heading = "Reservas Aprovadas";
        $type = 'success';
        $statusText = "aprovadas automaticamente";
    } else {
        $heading = "Reservas Submetidas";
        $type = 'info';
        $statusText = "submetidas para aprovação";
    }
    
    $bodyContent = "
        <p>Olá <strong>" . htmlspecialchars($requisitor['nome'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
        <p>Informamos que as suas reservas em massa foram processadas.</p>
        
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
        
        <p>Carregue no botão em baixo para ver todas as suas reservas.</p>";
    
    return sendStyledEmail(
        $requisitor['email'],
        "ClassLink - {$heading}",
        $heading,
        $bodyContent,
        $type,
        $reservasUrl,
        "Ver as minhas reservas"
    );
}

/**
 * Send recurring weekly reservations confirmation email
 * Used by admin/scripts/semanasrepetidas.php for batch weekly reservations
 * 
 * @param mysqli $db Database connection
 * @param string $requisitorId ID of the user who will have the reservations
 * @param int $successCount Number of successful reservations
 * @param int $duplicateCount Number of duplicate/skipped reservations
 * @param string $salaId Room ID
 * @param string $diaSemana Day of week (0-6, 0=Sunday)
 * @param string $dataInicio Start date (Y-m-d)
 * @param string $dataFim End date (Y-m-d)
 * @param int $numSemanas Number of weeks covered
 * @param int $numTempos Number of time slots selected
 * @param string $motivo Reservation reason
 * @return array ['success' => bool, 'error' => string|null]
 */
function sendRecurringWeeklyReservationsEmail($db, $requisitorId, $successCount, $duplicateCount, $salaId, $diaSemana, $dataInicio, $dataFim, $numSemanas, $numTempos, $motivo) {
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
    $salaResult = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $salaName = $salaResult ? $salaResult['nome'] : 'Desconhecida';
    
    // Map day of week number to name
    $diasSemana = [
        '0' => 'Domingo',
        '1' => 'Segunda-feira',
        '2' => 'Terça-feira',
        '3' => 'Quarta-feira',
        '4' => 'Quinta-feira',
        '5' => 'Sexta-feira',
        '6' => 'Sábado'
    ];
    $diaSemanaName = $diasSemana[$diaSemana] ?? 'Desconhecido';
    
    // Ensure numeric values are integers to prevent XSS
    $successCount = (int)$successCount;
    $duplicateCount = (int)$duplicateCount;
    $numSemanas = (int)$numSemanas;
    $numTempos = (int)$numTempos;
    
    $baseUrl = getBaseUrl();
    $reservasUrl = $baseUrl . "/reservas";
    
    // These reservations are always auto-approved (aprovado = 1) by semanasrepetidas.php
    $heading = "Reservas Semanais Criadas";
    $type = 'success';
    
    $bodyContent = "
        <p>Olá <strong>" . htmlspecialchars($requisitor['nome'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
        <p>Informamos que foram adicionadas reservas semanais por um administrador.</p>
        
        <table cellpadding='0' cellspacing='0' border='0' width='100%' style='background-color: #f8f9fa; border-radius: 8px; margin: 20px 0;'>
            <tr>
                <td style='padding: 20px;'>
                    <table cellpadding='0' cellspacing='0' border='0' width='100%'>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #e9ecef;'>
                                <strong style='color: #495057;'>Sala:</strong>
                                <span style='color: #212529; float: right;'>" . htmlspecialchars($salaName, ENT_QUOTES, 'UTF-8') . "</span>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #e9ecef;'>
                                <strong style='color: #495057;'>Dia da semana:</strong>
                                <span style='color: #212529; float: right;'>" . htmlspecialchars($diaSemanaName, ENT_QUOTES, 'UTF-8') . "</span>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #e9ecef;'>
                                <strong style='color: #495057;'>Período:</strong>
                                <span style='color: #212529; float: right;'>" . htmlspecialchars($dataInicio, ENT_QUOTES, 'UTF-8') . " a " . htmlspecialchars($dataFim, ENT_QUOTES, 'UTF-8') . "</span>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #e9ecef;'>
                                <strong style='color: #495057;'>Semanas abrangidas:</strong>
                                <span style='color: #212529; float: right;'>{$numSemanas}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #e9ecef;'>
                                <strong style='color: #495057;'>Tempos por dia:</strong>
                                <span style='color: #212529; float: right;'>{$numTempos}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #e9ecef;'>
                                <strong style='color: #495057;'>Reservas criadas:</strong>
                                <span style='color: #28a745; font-weight: bold; float: right;'>{$successCount}</span>
                            </td>
                        </tr>" .
        ($duplicateCount > 0 ? "
                        <tr>
                            <td style='padding: 8px 0; border-bottom: 1px solid #e9ecef;'>
                                <strong style='color: #495057;'>Reservas já existentes:</strong>
                                <span style='color: #ffc107; font-weight: bold; float: right;'>{$duplicateCount}</span>
                            </td>
                        </tr>" : "") . "
                        <tr>
                            <td style='padding: 8px 0;'>
                                <strong style='color: #495057;'>Motivo:</strong>
                                <span style='color: #212529; float: right;'>" . htmlspecialchars($motivo, ENT_QUOTES, 'UTF-8') . "</span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        
        <p>Carregue no botão em baixo para ver todas as suas reservas.</p>";
    
    return sendStyledEmail(
        $requisitor['email'],
        "ClassLink - {$heading}: {$salaName}",
        $heading,
        $bodyContent,
        $type,
        $reservasUrl,
        "Ver as minhas reservas"
    );
}

/**
 * Send email verification code for external user login
 * 
 * @param string $email Recipient email address
 * @param string $code 6-digit verification code
 * @return array ['success' => bool, 'error' => string|null]
 */
function sendExternalLoginVerificationEmail($email, $code) {
    // Ensure code is exactly 6 characters for display
    $code = str_pad((string)$code, 6, '0', STR_PAD_LEFT);
    $escapedCode = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
    
    $bodyContent = "
        <p>Olá,</p>
        <p>Recebemos um pedido de acesso ao ClassLink com o seu email.</p>
        <p>O seu código de verificação é:</p>
        <div style='text-align: center; margin: 30px 0;'>
            <span style='font-size: 32px; font-weight: bold; letter-spacing: 8px; background-color: #f8f9fa; padding: 15px 25px; border-radius: 8px; display: inline-block; color: #212529;'>{$escapedCode}</span>
        </div>
        <p>Este código é válido por <strong>10 minutos</strong>.</p>
        <p>Se não solicitou este código, pode ignorar este email.</p>";
    
    return sendStyledEmail(
        $email,
        "ClassLink - Código de Verificação",
        "Código de Verificação",
        $bodyContent,
        'primary'
    );
}
