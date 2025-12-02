<?php
require 'index.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get statistics for the dashboard
$totalPendentes = $db->query("SELECT COUNT(*) as total FROM reservas WHERE aprovado = 0")->fetch_assoc()['total'];
$totalHoje = $db->query("SELECT COUNT(*) as total FROM reservas WHERE aprovado = 0 AND data = CURDATE()")->fetch_assoc()['total'];
$totalAprovadas = $db->query("SELECT COUNT(*) as total FROM reservas WHERE aprovado = 1")->fetch_assoc()['total'];
?>

<style>
    .stat-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border: none;
        border-radius: 12px;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
    }
    .pedido-card {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
        margin-bottom: 1rem;
    }
    .pedido-card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .pedido-card.pending {
        border-left-color: #ffc107;
    }
    .pedido-card.approved {
        border-left-color: #28a745;
    }
    .action-btn {
        transition: all 0.2s ease;
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-weight: 500;
    }
    .action-btn:hover {
        transform: scale(1.05);
    }
    .search-box {
        border-radius: 25px;
        padding-left: 1rem;
        border: 2px solid #e9ecef;
        transition: border-color 0.2s ease;
    }
    .search-box:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,.15);
    }
    .empty-state {
        padding: 3rem;
        text-align: center;
        color: #6c757d;
    }
    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }
    .fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .pulse {
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    .badge-pending {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    }
    .badge-approved {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    .filter-btn.active {
        background-color: #0d6efd;
        color: white;
    }
</style>

<div class="container-fluid px-4 fade-in">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                            Gestão de Pedidos
                        </span>
                    </h2>
                    <p class="text-muted mb-0">Gerir e aprovar pedidos de reserva de salas</p>
                </div>
                <div>
                    <span class="badge bg-primary fs-6 px-3 py-2">
                        <?php echo date('d/m/Y'); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%);">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning text-white me-3">
                        &#x23F3;
                    </div>
                    <div>
                        <div class="stat-number text-warning"><?php echo $totalPendentes; ?></div>
                        <div class="text-muted small">Pedidos Pendentes</div>
                    </div>
                    <?php if ($totalPendentes > 0): ?>
                    <div class="ms-auto">
                        <span class="badge bg-warning text-dark pulse">Requer Atenção</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success text-white me-3">
                        &#x2705;
                    </div>
                    <div>
                        <div class="stat-number text-success"><?php echo $totalAprovadas; ?></div>
                        <div class="text-muted small">Reservas Aprovadas</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card shadow-sm h-100" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-info text-white me-3">
                        &#x1F4C5;
                    </div>
                    <div>
                        <div class="stat-number text-info"><?php echo $totalHoje; ?></div>
                        <div class="text-muted small">Pendentes para Hoje</div>
                    </div>
                    <?php if ($totalHoje > 0): ?>
                    <div class="ms-auto">
                        <span class="badge bg-info">Urgente</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="/admin/pedidos.php" method="POST" id="filterForm">
                <input type="hidden" id="requisitor" name="requisitor" value="<?php echo isset($_POST['requisitor']) ? htmlspecialchars($_POST['requisitor'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="sala" class="form-label fw-bold">Filtrar por Sala</label>
                        <select class="form-select" id="sala" name="sala" onchange="this.form.submit();">
                            <?php 
                            $selectedSala = isset($_POST['sala']) ? $_POST['sala'] : (isset($_GET['sala']) ? $_GET['sala'] : "0");
                            if ($selectedSala == "0") {
                                echo "<option value='0' selected>Todas as salas</option>";
                            } else {
                                echo "<option value='0'>Todas as salas</option>";
                            }
                            $salasQuery = $db->query("SELECT * FROM salas ORDER BY nome ASC;");
                            while ($salaItem = $salasQuery->fetch_assoc()) {
                                $selected = ($selectedSala == $salaItem['id']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($salaItem['id'], ENT_QUOTES, 'UTF-8') . "' {$selected}>" . htmlspecialchars($salaItem['nome'], ENT_QUOTES, 'UTF-8') . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Filtrar por Requisitor</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="selectedUserDisplay" 
                                   placeholder="Todos os utilizadores" readonly
                                   value="<?php 
                                   if (isset($_POST['requisitor']) && !empty($_POST['requisitor'])) {
                                       $userStmt = $db->prepare("SELECT nome FROM cache WHERE id = ?");
                                       $userStmt->bind_param("s", $_POST['requisitor']);
                                       $userStmt->execute();
                                       $userResult = $userStmt->get_result()->fetch_assoc();
                                       $userStmt->close();
                                       echo $userResult ? htmlspecialchars($userResult['nome'], ENT_QUOTES, 'UTF-8') : '';
                                   }
                                   ?>">
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#userSelectModal">
                                Procurar
                            </button>
                            <button class="btn btn-outline-danger" type="button" onclick="clearUserSelection()">
                                Limpar
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            Pesquisar
                        </button>
                        <a href="/admin/pedidos.php" class="btn btn-outline-secondary">
                            Limpar Tudo
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- User Selection Modal -->
    <div class="modal fade" id="userSelectModal" tabindex="-1" aria-labelledby="userSelectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userSelectModalLabel">Selecionar Utilizador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="text" class="form-control" id="userSearchInput" placeholder="Pesquisar por nome ou email..." oninput="filterUsersModal()">
                    </div>
                    <div class="list-group" id="userList">
                        <?php
                        $usersQuery = $db->query("SELECT id, nome, email FROM cache ORDER BY nome ASC");
                        while ($user = $usersQuery->fetch_assoc()) {
                            $userId = htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8');
                            $userName = htmlspecialchars($user['nome'], ENT_QUOTES, 'UTF-8');
                            $userEmail = htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8');
                            echo "<button type='button' class='list-group-item list-group-item-action user-item' 
                                data-user-id='{$userId}' 
                                data-user-name='{$userName}' 
                                data-user-email='{$userEmail}'
                                onclick='selectUser(this)'>
                                <strong>{$userName}</strong><br>
                                <small class='text-muted'>{$userEmail}</small>
                            </button>";
                        }
                        ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <?php
    if (isset($_GET['subaction'])) {
        if (!isset($_GET['sala']) || !isset($_GET['tempo']) || !isset($_GET['data'])) {
            echo "<div class='alert alert-danger alert-dismissible fade show shadow-sm' role='alert'>
                    <strong>Erro!</strong> Parâmetros inválidos.
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                  </div>";
            echo "<a href='/admin/pedidos.php' class='btn btn-primary'>Voltar aos Pedidos</a>";
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
                    
                    echo "<div class='card border-success shadow-sm mb-4'>
                            <div class='card-body text-center py-4'>
                                <div class='mb-3' style='font-size: 4rem;'>&#x1F389;</div>
                                <h4 class='text-success mb-3'>Reserva Aprovada com Sucesso!</h4>
                                <p class='text-muted mb-4'>O utilizador será notificado por email sobre a aprovação.</p>
                                <div class='d-flex justify-content-center gap-2'>";
                    $reservaUrl = "/reservar/manage.php?sala=" . urlencode($_GET['sala']) . "&tempo=" . urlencode($_GET['tempo']) . "&data=" . urlencode($_GET['data']);
                    echo "<a href='{$reservaUrl}' class='btn btn-outline-info' target='_blank'>
                            Ver Detalhes
                          </a>
                          <a href='/admin/pedidos.php' class='btn btn-primary'>
                            Voltar aos Pedidos
                          </a>
                        </div>
                      </div>
                    </div>";
                    
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
                        echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                                <strong>Aviso:</strong> A reserva foi aprovada, mas o email de notificação não foi enviado. Contacte o Postmaster.
                                <br><small>Erro: " . htmlspecialchars($enviarmail->ErrorInfo, ENT_QUOTES, 'UTF-8') . "</small>
                                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                              </div>";
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
                    
                    echo "<div class='card border-danger shadow-sm mb-4'>
                            <div class='card-body text-center py-4'>
                                <div class='mb-3' style='font-size: 4rem;'>&#x1F6AB;</div>
                                <h4 class='text-danger mb-3'>Reserva Rejeitada</h4>
                                <p class='text-muted mb-4'>O utilizador foi notificado por email sobre a rejeição.</p>
                                <a href='/admin/pedidos.php' class='btn btn-primary'>
                                    Voltar aos Pedidos
                                </a>
                            </div>
                          </div>";
                    
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
                        echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>
                                <strong>Aviso:</strong> A reserva foi rejeitada, mas o email de notificação não foi enviado. Contacte o Postmaster.
                                <br><small>Erro: " . htmlspecialchars($enviarmail->ErrorInfo, ENT_QUOTES, 'UTF-8') . "</small>
                                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                              </div>";
                    }
                    break;

                case "detalhes":
                    break;
            }
        }
    } elseif (isset($_POST['sala']) || isset($_GET['sala']) || isset($_POST['requisitor'])) {
        // Determine what to show
        $showAllPending = (isset($_POST['sala']) && $_POST['sala'] == "0") || (!isset($_POST['sala']) && !isset($_GET['sala']) && !isset($_POST['requisitor']));
        
        if (isset($_POST['sala']) && $_POST['sala'] != "0") {
            $sala = $_POST['sala'];
        } elseif (isset($_GET['sala'])) {
            $sala = $_GET['sala'];
        } else {
            $sala = null;
        }
        
        $pedidosArray = [];
        
        if (isset($_POST['requisitor']) && !empty($_POST['requisitor'])) {
            // Search by requisitor - only show pending requests
            $stmt = $db->prepare("SELECT * FROM reservas WHERE requisitor=? AND aprovado=0 ORDER BY data ASC");
            $stmt->bind_param("s", $_POST['requisitor']);
            $stmt->execute();
            $pedidos = $stmt->get_result();
            while ($pedido = $pedidos->fetch_assoc()) {
                $pedidosArray[] = $pedido;
            }
            $stmt->close();
            $searchMode = "requisitor";
        } elseif ($showAllPending || $sala === null) {
            // Show all pending requests
            $pedidos = $db->query("SELECT * FROM reservas WHERE aprovado=0 ORDER BY data ASC, tempo ASC");
            while ($pedido = $pedidos->fetch_assoc()) {
                $pedidosArray[] = $pedido;
            }
            $searchMode = "all";
        } else {
            // Filter by room
            $stmt = $db->prepare("SELECT * FROM reservas WHERE aprovado=0 AND sala=? ORDER BY data ASC");
            $stmt->bind_param("s", $sala);
            $stmt->execute();
            $pedidos = $stmt->get_result();
            while ($pedido = $pedidos->fetch_assoc()) {
                $pedidosArray[] = $pedido;
            }
            $stmt->close();
            $searchMode = "room";
        }
        
        $totalResults = count($pedidosArray);
        
        // Results Header
        echo "<div class='d-flex justify-content-between align-items-center mb-3'>";
        echo "<h5 class='mb-0'>";
        if ($searchMode == "requisitor") {
            $stmt = $db->prepare("SELECT nome FROM cache WHERE id=?");
            $stmt->bind_param("s", $_POST['requisitor']);
            $stmt->execute();
            $reqName = $stmt->get_result()->fetch_assoc()['nome'];
            $stmt->close();
            echo "Pedidos Pendentes - " . htmlspecialchars($reqName, ENT_QUOTES, 'UTF-8');
        } elseif ($searchMode == "room") {
            $stmt = $db->prepare("SELECT nome FROM salas WHERE id=?");
            $stmt->bind_param("s", $sala);
            $stmt->execute();
            $salaName = $stmt->get_result()->fetch_assoc()['nome'];
            $stmt->close();
            echo "Pedidos Pendentes - " . htmlspecialchars($salaName, ENT_QUOTES, 'UTF-8');
        } else {
            echo "Todos os Pedidos Pendentes";
        }
        echo "</h5>";
        echo "<span class='badge bg-secondary fs-6'>{$totalResults} resultado(s)</span>";
        echo "</div>";
        
        if ($totalResults == 0) {
            echo "<div class='card shadow-sm'>
                    <div class='card-body empty-state'>
                        <div class='empty-state-icon'>&#x1F4ED;</div>
                        <h5>Nenhum pedido encontrado</h5>
                        <p class='mb-0'>Não existem pedidos pendentes para os filtros selecionados.</p>
                    </div>
                  </div>";
        } else {
            // Search within results
            echo "<div class='mb-3'>
                    <input type='text' class='form-control search-box' id='tableSearch' 
                           placeholder='Pesquisar nos resultados...' onkeyup='filterTable()'>
                  </div>";
            
            echo "<div class='table-responsive'>
                    <table class='table table-hover align-middle' id='pedidosTable'>
                        <thead class='table-dark'>
                            <tr>
                                <th scope='col'>Data</th>
                                <th scope='col'>Horário</th>
                                <th scope='col'>Sala</th>
                                <th scope='col'>Requisitor</th>
                                <th scope='col'>Motivo</th>
                                <th scope='col' class='text-center'>Ações</th>
                            </tr>
                        </thead>
                        <tbody>";
            
            foreach ($pedidosArray as $pedido) {
                // Get room name
                $stmt2 = $db->prepare("SELECT nome FROM salas WHERE id=?");
                $stmt2->bind_param("s", $pedido['sala']);
                $stmt2->execute();
                $salaResult = $stmt2->get_result()->fetch_assoc();
                $salaextenso = $salaResult ? $salaResult['nome'] : 'N/A';
                $stmt2->close();
                
                // Get requisitor name
                $stmt2 = $db->prepare("SELECT nome FROM cache WHERE id=?");
                $stmt2->bind_param("s", $pedido['requisitor']);
                $stmt2->execute();
                $reqResult = $stmt2->get_result()->fetch_assoc();
                $requisitorextenso = $reqResult ? $reqResult['nome'] : 'N/A';
                $stmt2->close();
                
                // Get time slot
                $stmt2 = $db->prepare("SELECT horashumanos FROM tempos WHERE id=?");
                $stmt2->bind_param("s", $pedido['tempo']);
                $stmt2->execute();
                $tempoResult = $stmt2->get_result()->fetch_assoc();
                $horastempo = $tempoResult ? $tempoResult['horashumanos'] : 'N/A';
                $stmt2->close();
                
                $tempoEnc = urlencode($pedido['tempo']);
                $dataEnc = urlencode($pedido['data']);
                $salaEnc = urlencode($pedido['sala']);
                
                // Format date nicely
                $dataFormatted = date('d/m/Y', strtotime($pedido['data']));
                $isToday = ($pedido['data'] == date('Y-m-d'));
                $isPast = (strtotime($pedido['data']) < strtotime(date('Y-m-d')));
                
                $rowClass = "";
                if ($isPast && $pedido['aprovado'] == 0) {
                    $rowClass = "table-danger";
                } elseif ($isToday) {
                    $rowClass = "table-warning";
                }
                
                echo "<tr class='{$rowClass}' data-search='" . 
                     htmlspecialchars(strtolower($salaextenso . ' ' . $requisitorextenso . ' ' . $pedido['motivo'] . ' ' . $pedido['data']), ENT_QUOTES, 'UTF-8') . "'>";
                
                // Date column
                echo "<td>";
                echo "<strong>" . htmlspecialchars($dataFormatted, ENT_QUOTES, 'UTF-8') . "</strong>";
                if ($isToday) {
                    echo " <span class='badge bg-warning text-dark'>Hoje</span>";
                } elseif ($isPast) {
                    echo " <span class='badge bg-danger'>Passado</span>";
                }
                echo "</td>";
                
                // Time column
                echo "<td><span class='badge bg-light text-dark border'>" . htmlspecialchars($horastempo, ENT_QUOTES, 'UTF-8') . "</span></td>";
                
                // Room column
                echo "<td>" . htmlspecialchars($salaextenso, ENT_QUOTES, 'UTF-8') . "</td>";
                
                // Requisitor column
                echo "<td><span class='d-inline-flex align-items-center'>";
                echo "<span class='bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center me-2' style='width: 30px; height: 30px; font-size: 0.8rem;'>";
                echo strtoupper(substr($requisitorextenso, 0, 1));
                echo "</span>";
                echo htmlspecialchars($requisitorextenso, ENT_QUOTES, 'UTF-8');
                echo "</span></td>";
                
                // Motivo column
                $motivoTruncated = strlen($pedido['motivo']) > 50 ? substr($pedido['motivo'], 0, 50) . '...' : $pedido['motivo'];
                echo "<td title='" . htmlspecialchars($pedido['motivo'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($motivoTruncated, ENT_QUOTES, 'UTF-8') . "</td>";
                
                // Actions column
                echo "<td class='text-center'>";
                echo "<div class='btn-group' role='group'>";
                
                echo "<button type='button' class='btn btn-success btn-sm action-btn' 
                      onclick='confirmAction(\"aprovar\", \"{$tempoEnc}\", \"{$dataEnc}\", \"{$salaEnc}\", \"" . htmlspecialchars($salaextenso, ENT_QUOTES, 'UTF-8') . "\", \"" . htmlspecialchars($dataFormatted, ENT_QUOTES, 'UTF-8') . "\", \"" . htmlspecialchars($horastempo, ENT_QUOTES, 'UTF-8') . "\")' 
                      title='Aprovar'>
                    &#x2705;
                </button>";
                echo "<button type='button' class='btn btn-danger btn-sm action-btn' 
                      onclick='confirmAction(\"rejeitar\", \"{$tempoEnc}\", \"{$dataEnc}\", \"{$salaEnc}\", \"" . htmlspecialchars($salaextenso, ENT_QUOTES, 'UTF-8') . "\", \"" . htmlspecialchars($dataFormatted, ENT_QUOTES, 'UTF-8') . "\", \"" . htmlspecialchars($horastempo, ENT_QUOTES, 'UTF-8') . "\")' 
                      title='Rejeitar'>
                    &#x274C;
                </button>";
                echo "<a href='/reservar/manage.php?tempo={$tempoEnc}&data={$dataEnc}&sala={$salaEnc}' 
                      class='btn btn-outline-secondary btn-sm action-btn' title='Ver Detalhes' target='_blank'>
                    &#x1F441;
                  </a>";
                echo "</div>";
                echo "</td>";
                
                echo "</tr>";
            }
            
            echo "</tbody></table></div>";
        }
    } else {
        // Default view - Show all pending requests
        $allPending = $db->query("SELECT * FROM reservas WHERE aprovado=0 ORDER BY data ASC, tempo ASC");
        $pendingArray = [];
        while ($p = $allPending->fetch_assoc()) {
            $pendingArray[] = $p;
        }
        $totalPending = count($pendingArray);
        
        echo "<div class='d-flex justify-content-between align-items-center mb-3'>";
        echo "<h5 class='mb-0'>Todos os Pedidos Pendentes</h5>";
        echo "<span class='badge bg-secondary fs-6'>{$totalPending} pedido(s)</span>";
        echo "</div>";
        
        if ($totalPending == 0) {
            echo "<div class='card shadow-sm'>
                    <div class='card-body empty-state'>
                        <div class='empty-state-icon'>&#x1F389;</div>
                        <h5>Nenhum pedido pendente!</h5>
                        <p class='mb-0'>Todos os pedidos foram processados. Bom trabalho!</p>
                    </div>
                  </div>";
        } else {
            echo "<div class='mb-3'>
                    <input type='text' class='form-control search-box' id='tableSearch' 
                           placeholder='Pesquisar nos resultados...' onkeyup='filterTable()'>
                  </div>";
            
            echo "<div class='table-responsive'>
                    <table class='table table-hover align-middle' id='pedidosTable'>
                        <thead class='table-dark'>
                            <tr>
                                <th scope='col'>Data</th>
                                <th scope='col'>Horário</th>
                                <th scope='col'>Sala</th>
                                <th scope='col'>Requisitor</th>
                                <th scope='col'>Motivo</th>
                                <th scope='col' class='text-center'>Ações</th>
                            </tr>
                        </thead>
                        <tbody>";
            
            foreach ($pendingArray as $pedido) {
                $stmt2 = $db->prepare("SELECT nome FROM salas WHERE id=?");
                $stmt2->bind_param("s", $pedido['sala']);
                $stmt2->execute();
                $salaResult = $stmt2->get_result()->fetch_assoc();
                $salaextenso = $salaResult ? $salaResult['nome'] : 'N/A';
                $stmt2->close();
                
                $stmt2 = $db->prepare("SELECT nome FROM cache WHERE id=?");
                $stmt2->bind_param("s", $pedido['requisitor']);
                $stmt2->execute();
                $reqResult = $stmt2->get_result()->fetch_assoc();
                $requisitorextenso = $reqResult ? $reqResult['nome'] : 'N/A';
                $stmt2->close();
                
                $stmt2 = $db->prepare("SELECT horashumanos FROM tempos WHERE id=?");
                $stmt2->bind_param("s", $pedido['tempo']);
                $stmt2->execute();
                $tempoResult = $stmt2->get_result()->fetch_assoc();
                $horastempo = $tempoResult ? $tempoResult['horashumanos'] : 'N/A';
                $stmt2->close();
                
                $tempoEnc = urlencode($pedido['tempo']);
                $dataEnc = urlencode($pedido['data']);
                $salaEnc = urlencode($pedido['sala']);
                
                $dataFormatted = date('d/m/Y', strtotime($pedido['data']));
                $isToday = ($pedido['data'] == date('Y-m-d'));
                $isPast = (strtotime($pedido['data']) < strtotime(date('Y-m-d')));
                
                $rowClass = "";
                if ($isPast) {
                    $rowClass = "table-danger";
                } elseif ($isToday) {
                    $rowClass = "table-warning";
                }
                
                echo "<tr class='{$rowClass}' data-search='" . 
                     htmlspecialchars(strtolower($salaextenso . ' ' . $requisitorextenso . ' ' . $pedido['motivo'] . ' ' . $pedido['data']), ENT_QUOTES, 'UTF-8') . "'>";
                
                echo "<td><strong>" . htmlspecialchars($dataFormatted, ENT_QUOTES, 'UTF-8') . "</strong>";
                if ($isToday) {
                    echo " <span class='badge bg-warning text-dark'>Hoje</span>";
                } elseif ($isPast) {
                    echo " <span class='badge bg-danger'>Passado</span>";
                }
                echo "</td>";
                
                echo "<td><span class='badge bg-light text-dark border'>" . htmlspecialchars($horastempo, ENT_QUOTES, 'UTF-8') . "</span></td>";
                echo "<td>" . htmlspecialchars($salaextenso, ENT_QUOTES, 'UTF-8') . "</td>";
                
                echo "<td><span class='d-inline-flex align-items-center'>";
                echo "<span class='bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center me-2' style='width: 30px; height: 30px; font-size: 0.8rem;'>";
                echo strtoupper(substr($requisitorextenso, 0, 1));
                echo "</span>";
                echo htmlspecialchars($requisitorextenso, ENT_QUOTES, 'UTF-8');
                echo "</span></td>";
                
                $motivoTruncated = strlen($pedido['motivo']) > 50 ? substr($pedido['motivo'], 0, 50) . '...' : $pedido['motivo'];
                echo "<td title='" . htmlspecialchars($pedido['motivo'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($motivoTruncated, ENT_QUOTES, 'UTF-8') . "</td>";
                
                echo "<td class='text-center'>";
                echo "<div class='btn-group' role='group'>";
                echo "<button type='button' class='btn btn-success btn-sm action-btn' 
                      onclick='confirmAction(\"aprovar\", \"{$tempoEnc}\", \"{$dataEnc}\", \"{$salaEnc}\", \"" . htmlspecialchars($salaextenso, ENT_QUOTES, 'UTF-8') . "\", \"" . htmlspecialchars($dataFormatted, ENT_QUOTES, 'UTF-8') . "\", \"" . htmlspecialchars($horastempo, ENT_QUOTES, 'UTF-8') . "\")' 
                      title='Aprovar'>
                    &#x2705;
                </button>";
                echo "<button type='button' class='btn btn-danger btn-sm action-btn' 
                      onclick='confirmAction(\"rejeitar\", \"{$tempoEnc}\", \"{$dataEnc}\", \"{$salaEnc}\", \"" . htmlspecialchars($salaextenso, ENT_QUOTES, 'UTF-8') . "\", \"" . htmlspecialchars($dataFormatted, ENT_QUOTES, 'UTF-8') . "\", \"" . htmlspecialchars($horastempo, ENT_QUOTES, 'UTF-8') . "\")' 
                      title='Rejeitar'>
                    &#x274C;
                </button>";
                echo "<a href='/reservar/manage.php?tempo={$tempoEnc}&data={$dataEnc}&sala={$salaEnc}' 
                      class='btn btn-outline-secondary btn-sm action-btn' title='Ver Detalhes' target='_blank'>
                    &#x1F441;
                  </a>";
                echo "</div>";
                echo "</td>";
                echo "</tr>";
            }
            
            echo "</tbody></table></div>";
        }
    }
    ?>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="modalHeader">
                <h5 class="modal-title" id="confirmModalLabel">Confirmar Ação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmBtn" class="btn btn-primary">Confirmar</a>
            </div>
        </div>
    </div>
</div>

<script>
function filterTable() {
    const searchInput = document.getElementById('tableSearch');
    if (!searchInput) return;
    
    const filter = searchInput.value.toLowerCase();
    const table = document.getElementById('pedidosTable');
    if (!table) return;
    
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const searchData = rows[i].getAttribute('data-search');
        if (searchData && searchData.includes(filter)) {
            rows[i].style.display = '';
        } else {
            rows[i].style.display = 'none';
        }
    }
}

function confirmAction(action, tempo, data, sala, salaNome, dataFormatted, horasNome) {
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    const modalHeader = document.getElementById('modalHeader');
    const modalBody = document.getElementById('modalBody');
    const confirmBtn = document.getElementById('confirmBtn');
    
    if (action === 'aprovar') {
        modalHeader.className = 'modal-header bg-success text-white';
        modalBody.innerHTML = `
            <div class="text-center mb-3">
                <div style="font-size: 4rem;">&#x2705;</div>
            </div>
            <h5 class="text-center">Aprovar esta reserva?</h5>
            <div class="alert alert-light border mt-3">
                <p class="mb-1"><strong>Sala:</strong> ${salaNome}</p>
                <p class="mb-1"><strong>Data:</strong> ${dataFormatted}</p>
                <p class="mb-0"><strong>Horário:</strong> ${horasNome}</p>
            </div>
            <p class="text-muted small text-center mb-0">O utilizador será notificado por email.</p>
        `;
        confirmBtn.className = 'btn btn-success';
        confirmBtn.textContent = 'Aprovar Reserva';
        confirmBtn.href = `/admin/pedidos.php?subaction=aprovar&tempo=${tempo}&data=${data}&sala=${sala}`;
    } else {
        modalHeader.className = 'modal-header bg-danger text-white';
        modalBody.innerHTML = `
            <div class="text-center mb-3">
                <div style="font-size: 4rem;">&#x274C;</div>
            </div>
            <h5 class="text-center text-danger">Rejeitar esta reserva?</h5>
            <div class="alert alert-light border mt-3">
                <p class="mb-1"><strong>Sala:</strong> ${salaNome}</p>
                <p class="mb-1"><strong>Data:</strong> ${dataFormatted}</p>
                <p class="mb-0"><strong>Horário:</strong> ${horasNome}</p>
            </div>
            <div class="alert alert-warning">
                <strong>Atenção:</strong> Esta ação irá eliminar a reserva permanentemente e notificar o utilizador.
            </div>
        `;
        confirmBtn.className = 'btn btn-danger';
        confirmBtn.textContent = 'Rejeitar Reserva';
        confirmBtn.href = `/admin/pedidos.php?subaction=rejeitar&tempo=${tempo}&data=${data}&sala=${sala}`;
    }
    
    modal.show();
}

function filterUsersModal() {
    const searchInput = document.getElementById('userSearchInput');
    const filter = searchInput.value.toLowerCase();
    const userItems = document.querySelectorAll('.user-item');
    
    userItems.forEach(function(item) {
        const name = item.getAttribute('data-user-name').toLowerCase();
        const email = item.getAttribute('data-user-email').toLowerCase();
        if (name.includes(filter) || email.includes(filter)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

function selectUser(element) {
    const userId = element.getAttribute('data-user-id');
    const userName = element.getAttribute('data-user-name');
    
    document.getElementById('requisitor').value = userId;
    document.getElementById('selectedUserDisplay').value = userName;
    
    // Close the modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('userSelectModal'));
    modal.hide();
    
    // Submit the form
    document.getElementById('filterForm').submit();
}

function clearUserSelection() {
    document.getElementById('requisitor').value = '';
    document.getElementById('selectedUserDisplay').value = '';
    document.getElementById('filterForm').submit();
}
</script>
