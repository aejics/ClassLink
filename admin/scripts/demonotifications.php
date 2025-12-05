<?php 
require '../index.php';
require_once(__DIR__ . '/../../func/email_helper.php');
?>
<div style="margin-left: 10%; margin-right: 10%; text-align: center;">
<h1>Demo de Notificações</h1>
<p>Este script permite testar todas as notificações por email do sistema ClassLink.</p>
<p>Insira o email de destino e clique no botão para enviar todas as notificações de demonstração.</p>

<style>
    body {
        overflow-y: auto !important;
    }
    
    .notification-list {
        text-align: left;
        margin: 20px auto;
        max-width: 600px;
    }
    
    .notification-list li {
        margin-bottom: 10px;
    }
    
    .result-item {
        padding: 10px;
        margin-bottom: 10px;
        border-radius: 5px;
    }
    
    .result-success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
    }
    
    .result-error {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
    }
</style>

<div class="card mt-4 mb-4" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">Tipos de Notificações</h5>
    </div>
    <div class="card-body">
        <ul class="notification-list mb-0">
            <li><strong>Reserva Submetida</strong> - Quando uma reserva é submetida e aguarda aprovação</li>
            <li><strong>Reserva Confirmada</strong> - Quando uma reserva autónoma é criada automaticamente</li>
            <li><strong>Reserva Aprovada</strong> - Quando uma reserva é aprovada por um administrador</li>
            <li><strong>Reserva Rejeitada</strong> - Quando uma reserva é rejeitada por um administrador</li>
            <li><strong>Reserva Removida (pelo utilizador)</strong> - Quando o utilizador remove a sua própria reserva</li>
            <li><strong>Reserva Removida (pelo administrador)</strong> - Quando um administrador remove uma reserva</li>
            <li><strong>Reservas em Massa Submetidas</strong> - Quando várias reservas são submetidas para aprovação</li>
            <li><strong>Reservas em Massa Aprovadas</strong> - Quando várias reservas autónomas são criadas</li>
            <li><strong>Reservas Semanais Criadas</strong> - Quando um administrador cria reservas semanais recorrentes</li>
        </ul>
    </div>
</div>

<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST" class="mt-4" style="max-width: 500px; margin: 0 auto;">
    <div class="mb-3">
        <div class="form-floating">
            <input type="email" class="form-control" id="email" name="email" placeholder="Email de destino" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
            <label for="email">Email de destino</label>
        </div>
    </div>
    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">Enviar Todas as Notificações</button>
    </div>
</form>

<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && !empty($_POST['email'])) {
    
    // Validate email
    $targetEmail = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    
    if (!$targetEmail) {
        echo "<div class='mt-4 alert alert-danger'>
            <strong>Erro:</strong> Email inválido.
        </div>";
    } else {
        echo "<div class='mt-4'>";
        echo "<h3>Resultados do Envio</h3>";
        
        // Example data for demos
        $demoData = [
            'userName' => 'Maria Silva',
            'roomName' => 'Sala de Informática B1',
            'date' => date('Y-m-d', strtotime('+1 week')),
            'time' => '10:00 - 10:50',
            'reason' => 'Aula de Programação',
            'diaSemana' => '3', // Quarta-feira
            'dataInicio' => date('Y-m-d', strtotime('+1 week')),
            'dataFim' => date('Y-m-d', strtotime('+2 months'))
        ];
        
        $results = [];
        
        // Define colors based on type for the sendStyledEmail function
        $baseUrl = getBaseUrl();
        
        // 1. Reservation Submitted (pending approval)
        $bodyContent = "
            <p>Olá <strong>" . htmlspecialchars($demoData['userName'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p>A sua reserva foi submetida com sucesso e está <strong>a aguardar aprovação</strong>.</p>
            " . buildReservationDetailsHtml($demoData['roomName'], $demoData['date'], $demoData['time'], $demoData['reason']) . "
            <p>Irá receber um email assim que a sua reserva for aprovada ou rejeitada.</p>";
        
        $result = sendStyledEmail(
            $targetEmail,
            "ClassLink - Reserva Submetida: {$demoData['roomName']}",
            "Reserva Submetida",
            $bodyContent,
            'info',
            $baseUrl . "/reservar/manage.php?demo=1",
            "Ver Detalhes da Reserva"
        );
        $results[] = ['name' => 'Reserva Submetida (a aguardar aprovação)', 'result' => $result];
        
        // 2. Reservation Created (autonomous/auto-approved)
        $bodyContent = "
            <p>Olá <strong>" . htmlspecialchars($demoData['userName'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p>Informamos que a sua reserva foi criada com sucesso.</p>
            " . buildReservationDetailsHtml($demoData['roomName'], $demoData['date'], $demoData['time'], $demoData['reason']) . "
            <p>Pode ver todos os detalhes e informações importantes sobre a sua reserva através do botão em baixo.</p>";
        
        $result = sendStyledEmail(
            $targetEmail,
            "ClassLink - Confirmação de Reserva da Sala: {$demoData['roomName']}",
            "Confirmação de Reserva da Sala",
            $bodyContent,
            'success',
            $baseUrl . "/reservar/manage.php?demo=1",
            "Ver Detalhes da Reserva"
        );
        $results[] = ['name' => 'Reserva Confirmada (autónoma)', 'result' => $result];
        
        // 3. Reservation Approved
        $bodyContent = "
            <p>Olá <strong>" . htmlspecialchars($demoData['userName'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p>Temos boas notícias! A sua reserva foi <strong style='color: #28a745;'>aprovada</strong>.</p>
            " . buildReservationDetailsHtml($demoData['roomName'], $demoData['date'], $demoData['time']) . "
            <p>Carregue no botão em baixo para ver todos os detalhes e informações importantes sobre a sua reserva.</p>";
        
        $result = sendStyledEmail(
            $targetEmail,
            "ClassLink - Reserva Aprovada: {$demoData['roomName']}",
            "🎉 Reserva Aprovada",
            $bodyContent,
            'success',
            $baseUrl . "/reservar/manage.php?demo=1",
            "Ver Detalhes da Reserva"
        );
        $results[] = ['name' => 'Reserva Aprovada', 'result' => $result];
        
        // 4. Reservation Rejected
        $bodyContent = "
            <p>Olá <strong>" . htmlspecialchars($demoData['userName'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p>Lamentamos informar que a sua reserva foi <strong style='color: #dc3545;'>rejeitada</strong>.</p>
            " . buildReservationDetailsHtml($demoData['roomName'], $demoData['date'], $demoData['time']) . "
            <p>Pode efetuar um novo pedido através do botão em baixo.</p>";
        
        $result = sendStyledEmail(
            $targetEmail,
            "ClassLink - Reserva Rejeitada: {$demoData['roomName']}",
            "Reserva Rejeitada",
            $bodyContent,
            'danger',
            $baseUrl . "/reservar",
            "Fazer Nova Reserva"
        );
        $results[] = ['name' => 'Reserva Rejeitada', 'result' => $result];
        
        // 5. Reservation Deleted (self)
        $bodyContent = "
            <p>Olá <strong>" . htmlspecialchars($demoData['userName'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p>Informamos que a sua reserva foi <strong>removida</strong> com sucesso.</p>
            " . buildReservationDetailsHtml($demoData['roomName'], $demoData['date'], $demoData['time']) . "
            <p>Pode sempre efetuar uma nova reserva a qualquer momento.</p>";
        
        $result = sendStyledEmail(
            $targetEmail,
            "ClassLink - Reserva Removida: {$demoData['roomName']}",
            "Reserva Removida",
            $bodyContent,
            'warning',
            $baseUrl . "/reservar",
            "Fazer Nova Reserva"
        );
        $results[] = ['name' => 'Reserva Removida (pelo utilizador)', 'result' => $result];
        
        // 6. Reservation Deleted (by admin)
        $bodyContent = "
            <p>Olá <strong>" . htmlspecialchars($demoData['userName'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p>Informamos que a sua reserva foi <strong>removida</strong> por um administrador.</p>
            " . buildReservationDetailsHtml($demoData['roomName'], $demoData['date'], $demoData['time']);
        
        $result = sendStyledEmail(
            $targetEmail,
            "ClassLink - Reserva Removida: {$demoData['roomName']}",
            "Reserva Removida",
            $bodyContent,
            'warning',
            $baseUrl . "/reservar",
            "Fazer Nova Reserva"
        );
        $results[] = ['name' => 'Reserva Removida (pelo administrador)', 'result' => $result];
        
        // 7. Bulk Reservations Submitted (pending approval)
        $successCount = 5;
        $failedCount = 1;
        $salaName = $demoData['roomName'];
        
        $bodyContent = "
            <p>Olá <strong>" . htmlspecialchars($demoData['userName'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p>Informamos que as suas reservas em massa foram processadas.</p>
            
            <table cellpadding='0' cellspacing='0' border='0' width='100%' style='background-color: #f8f9fa; border-radius: 8px; margin: 20px 0;'>
                <tr>
                    <td style='padding: 20px;'>
                        <table cellpadding='0' cellspacing='0' border='0' width='100%'>
                            <tr>
                                <td style='padding: 8px 0; border-bottom: 1px solid #e9ecef;'>
                                    <strong style='color: #495057;'>Reservas submetidas para aprovação:</strong>
                                    <span style='color: #28a745; font-weight: bold; float: right;'>{$successCount}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; border-bottom: 1px solid #e9ecef;'>
                                    <strong style='color: #495057;'>Reservas falhadas:</strong>
                                    <span style='color: #dc3545; font-weight: bold; float: right;'>{$failedCount}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0;'>
                                    <strong style='color: #495057;'>Sala:</strong>
                                    <span style='color: #212529; float: right;'>" . htmlspecialchars($salaName, ENT_QUOTES, 'UTF-8') . "</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            
            <p>Carregue no botão em baixo para ver todas as suas reservas.</p>";
        
        $result = sendStyledEmail(
            $targetEmail,
            "ClassLink - Reservas Submetidas",
            "Reservas Submetidas",
            $bodyContent,
            'info',
            $baseUrl . "/reservas",
            "Ver as minhas reservas"
        );
        $results[] = ['name' => 'Reservas em Massa Submetidas (a aguardar aprovação)', 'result' => $result];
        
        // 8. Bulk Reservations Autonomous (auto-approved)
        $bodyContent = "
            <p>Olá <strong>" . htmlspecialchars($demoData['userName'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p>Informamos que as suas reservas em massa foram processadas.</p>
            
            <table cellpadding='0' cellspacing='0' border='0' width='100%' style='background-color: #f8f9fa; border-radius: 8px; margin: 20px 0;'>
                <tr>
                    <td style='padding: 20px;'>
                        <table cellpadding='0' cellspacing='0' border='0' width='100%'>
                            <tr>
                                <td style='padding: 8px 0; border-bottom: 1px solid #e9ecef;'>
                                    <strong style='color: #495057;'>Reservas aprovadas automaticamente:</strong>
                                    <span style='color: #28a745; font-weight: bold; float: right;'>{$successCount}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0;'>
                                    <strong style='color: #495057;'>Sala:</strong>
                                    <span style='color: #212529; float: right;'>" . htmlspecialchars($salaName, ENT_QUOTES, 'UTF-8') . "</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            
            <p>Carregue no botão em baixo para ver todas as suas reservas.</p>";
        
        $result = sendStyledEmail(
            $targetEmail,
            "ClassLink - Reservas Aprovadas",
            "Reservas Aprovadas",
            $bodyContent,
            'success',
            $baseUrl . "/reservas",
            "Ver as minhas reservas"
        );
        $results[] = ['name' => 'Reservas em Massa Aprovadas (autónomas)', 'result' => $result];
        
        // 9. Recurring Weekly Reservations
        $diasSemana = [
            '0' => 'Domingo',
            '1' => 'Segunda-feira',
            '2' => 'Terça-feira',
            '3' => 'Quarta-feira',
            '4' => 'Quinta-feira',
            '5' => 'Sexta-feira',
            '6' => 'Sábado'
        ];
        $diaSemanaName = $diasSemana[$demoData['diaSemana']];
        $numSemanas = 8;
        $numTempos = 2;
        $reservasCriadas = 16;
        $reservasDuplicadas = 2;
        
        $bodyContent = "
            <p>Olá <strong>" . htmlspecialchars($demoData['userName'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
            <p>Informamos que foram adicionadas reservas semanais por um administrador.</p>
            
            <table cellpadding='0' cellspacing='0' border='0' width='100%' style='background-color: #f8f9fa; border-radius: 8px; margin: 20px 0;'>
                <tr>
                    <td style='padding: 20px;'>
                        <table cellpadding='0' cellspacing='0' border='0' width='100%'>
                            <tr>
                                <td style='padding: 8px 0; border-bottom: 1px solid #e9ecef;'>
                                    <strong style='color: #495057;'>Sala:</strong>
                                    <span style='color: #212529; float: right;'>" . htmlspecialchars($demoData['roomName'], ENT_QUOTES, 'UTF-8') . "</span>
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
                                    <span style='color: #212529; float: right;'>" . htmlspecialchars($demoData['dataInicio'], ENT_QUOTES, 'UTF-8') . " a " . htmlspecialchars($demoData['dataFim'], ENT_QUOTES, 'UTF-8') . "</span>
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
                                    <span style='color: #28a745; font-weight: bold; float: right;'>{$reservasCriadas}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0; border-bottom: 1px solid #e9ecef;'>
                                    <strong style='color: #495057;'>Reservas já existentes:</strong>
                                    <span style='color: #ffc107; font-weight: bold; float: right;'>{$reservasDuplicadas}</span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding: 8px 0;'>
                                    <strong style='color: #495057;'>Motivo:</strong>
                                    <span style='color: #212529; float: right;'>" . htmlspecialchars($demoData['reason'], ENT_QUOTES, 'UTF-8') . "</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            
            <p>Carregue no botão em baixo para ver todas as suas reservas.</p>";
        
        $result = sendStyledEmail(
            $targetEmail,
            "ClassLink - Reservas Semanais Criadas: {$demoData['roomName']}",
            "Reservas Semanais Criadas",
            $bodyContent,
            'success',
            $baseUrl . "/reservas",
            "Ver as minhas reservas"
        );
        $results[] = ['name' => 'Reservas Semanais Criadas', 'result' => $result];
        
        // Display results
        $successTotal = 0;
        $failedTotal = 0;
        
        foreach ($results as $item) {
            $statusClass = $item['result']['success'] ? 'result-success' : 'result-error';
            $statusIcon = $item['result']['success'] ? '✓' : '✗';
            $statusText = $item['result']['success'] ? 'Enviado com sucesso' : 'Falhou: ' . htmlspecialchars($item['result']['error'] ?? 'Erro desconhecido', ENT_QUOTES, 'UTF-8');
            
            if ($item['result']['success']) {
                $successTotal++;
            } else {
                $failedTotal++;
            }
            
            echo "<div class='result-item {$statusClass}'>
                <strong>{$statusIcon} " . htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') . "</strong><br>
                <small>{$statusText}</small>
            </div>";
        }
        
        echo "</div>";
        
        // Summary
        $totalEmails = count($results);
        if ($failedTotal == 0) {
            echo "<div class='mt-3 alert alert-success'>
                <strong>Sucesso!</strong> Todos os {$totalEmails} emails foram enviados com sucesso para <strong>" . htmlspecialchars($targetEmail, ENT_QUOTES, 'UTF-8') . "</strong>.
            </div>";
        } else {
            echo "<div class='mt-3 alert alert-warning'>
                <strong>Aviso:</strong> {$successTotal} de {$totalEmails} emails foram enviados com sucesso. {$failedTotal} email(s) falharam.
            </div>";
        }
    }
}
?>
</div>
