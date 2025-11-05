<?php require '../index.php'; ?>
<h1>Semanas repetidas</h1>
<p>Este script permite criar reservas repetidas de salas ao longo de várias semanas.</p>
<p>Selecione a sala, o utilizador, os tempos desejados e o intervalo de semanas para criar as reservas.</p>

<style>
    .time-checkbox-container {
        max-height: 400px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
    }
    .time-checkbox-item {
        padding: 0.5rem;
        margin-bottom: 0.5rem;
        border: 1px solid #e0e0e0;
        border-radius: 0.25rem;
        background-color: #f8f9fa;
    }
    .time-checkbox-item:hover {
        background-color: #e9ecef;
    }
</style>

<form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="POST" class="mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="form-floating">
                <select class="form-select" id="sala" name="sala" required>
                    <option value="" selected disabled>Escolha uma sala</option>
                    <?php
                    $salas = $db->query("SELECT * FROM salas ORDER BY nome ASC;");
                    while ($sala = $salas->fetch_assoc()) {
                        $selected = (isset($_POST['sala']) && $_POST['sala'] == $sala['id']) ? 'selected' : '';
                        echo "<option value='{$sala['id']}' {$selected}>" . htmlspecialchars($sala['nome'], ENT_QUOTES, 'UTF-8') . "</option>";
                    }
                    ?>
                </select>
                <label for="sala">Sala</label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating">
                <select class="form-select" id="requisitor" name="requisitor" required>
                    <option value="" selected disabled>Escolha um utilizador</option>
                    <?php
                    $users = $db->query("SELECT * FROM cache ORDER BY nome ASC;");
                    while ($user = $users->fetch_assoc()) {
                        $selected = (isset($_POST['requisitor']) && $_POST['requisitor'] == $user['id']) ? 'selected' : '';
                        echo "<option value='{$user['id']}' {$selected}>" . htmlspecialchars($user['nome'], ENT_QUOTES, 'UTF-8') . "</option>";
                    }
                    ?>
                </select>
                <label for="requisitor">Utilizador (requisitor)</label>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12">
            <label class="form-label"><strong>Tempos (selecione os horários para reservar):</strong></label>
            <div class="time-checkbox-container">
                <?php
                $tempos = $db->query("SELECT * FROM tempos ORDER BY horashumanos ASC;");
                while ($tempo = $tempos->fetch_assoc()) {
                    $checked = (isset($_POST['tempos']) && in_array($tempo['id'], $_POST['tempos'])) ? 'checked' : '';
                    echo "<div class='time-checkbox-item'>
                        <div class='form-check'>
                            <input class='form-check-input' type='checkbox' name='tempos[]' value='{$tempo['id']}' id='tempo_{$tempo['id']}' {$checked}>
                            <label class='form-check-label' for='tempo_{$tempo['id']}'>
                                " . htmlspecialchars($tempo['horashumanos'], ENT_QUOTES, 'UTF-8') . "
                            </label>
                        </div>
                    </div>";
                }
                ?>
            </div>
            <small class="text-muted">Selecione um ou mais tempos para reservar em cada semana</small>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="form-floating">
                <select class="form-select" id="dia_semana" name="dia_semana" required>
                    <option value="" selected disabled>Escolha um dia</option>
                    <?php
                    $dias = [
                        '1' => 'Segunda-feira',
                        '2' => 'Terça-feira',
                        '3' => 'Quarta-feira',
                        '4' => 'Quinta-feira',
                        '5' => 'Sexta-feira',
                        '6' => 'Sábado',
                        '0' => 'Domingo'
                    ];
                    foreach ($dias as $value => $label) {
                        $selected = (isset($_POST['dia_semana']) && $_POST['dia_semana'] == $value) ? 'selected' : '';
                        echo "<option value='{$value}' {$selected}>{$label}</option>";
                    }
                    ?>
                </select>
                <label for="dia_semana">Dia da semana</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-floating">
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" placeholder="Data de início" value="<?php echo isset($_POST['data_inicio']) ? htmlspecialchars($_POST['data_inicio']) : ''; ?>" required>
                <label for="data_inicio">Data de início (primeira semana)</label>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-floating">
                <input type="number" class="form-control" id="semanas" name="semanas" placeholder="Número de semanas" min="1" max="52" value="<?php echo isset($_POST['semanas']) ? htmlspecialchars($_POST['semanas']) : ''; ?>" required>
                <label for="semanas">Número de semanas</label>
            </div>
        </div>
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">Criar Reservas</button>
    </div>
</form>

<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sala']) && isset($_POST['requisitor']) && isset($_POST['tempos']) && isset($_POST['dia_semana']) && isset($_POST['data_inicio']) && isset($_POST['semanas'])) {
    
    // Validate inputs
    $sala_id = $_POST['sala'];
    $requisitor_id = $_POST['requisitor'];
    $tempos_ids = $_POST['tempos'];
    $dia_semana = intval($_POST['dia_semana']);
    $data_inicio = $_POST['data_inicio'];
    $num_semanas = intval($_POST['semanas']);
    
    if (empty($tempos_ids)) {
        echo "<div class='mt-3 alert alert-danger fade show' role='alert'>
            <strong>Erro:</strong> Deve selecionar pelo menos um tempo.
        </div>";
    } else {
        // Verify sala exists
        $stmt = $db->prepare("SELECT * FROM salas WHERE id = ?");
        $stmt->bind_param("s", $sala_id);
        $stmt->execute();
        $sala = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        // Verify requisitor exists
        $stmt = $db->prepare("SELECT * FROM cache WHERE id = ?");
        $stmt->bind_param("s", $requisitor_id);
        $stmt->execute();
        $requisitor = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$sala || !$requisitor) {
            echo "<div class='mt-3 alert alert-danger fade show' role='alert'>
                <strong>Erro:</strong> Sala ou utilizador inválido.
            </div>";
        } else {
            // Calculate first occurrence of the selected day of week from data_inicio
            $first_date = strtotime($data_inicio);
            $first_day_of_week = date('w', $first_date);
            
            // Adjust to the next occurrence of the selected day if needed
            if ($first_day_of_week != $dia_semana) {
                $days_to_add = ($dia_semana - $first_day_of_week + 7) % 7;
                $first_date = strtotime("+{$days_to_add} days", $first_date);
            }
            
            $reservas_criadas = 0;
            $reservas_duplicadas = 0;
            $erros = [];
            
            // Create reservations for each week
            for ($semana = 0; $semana < $num_semanas; $semana++) {
                $data_reserva = strtotime("+{$semana} weeks", $first_date);
                $data_reserva_formatted = date('Y-m-d', $data_reserva);
                
                // Create reservation for each selected time
                foreach ($tempos_ids as $tempo_id) {
                    // Verify tempo exists
                    $stmt = $db->prepare("SELECT * FROM tempos WHERE id = ?");
                    $stmt->bind_param("s", $tempo_id);
                    $stmt->execute();
                    $tempo = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    
                    if (!$tempo) {
                        $erros[] = "Tempo inválido: {$tempo_id}";
                        continue;
                    }
                    
                    // Check if reservation already exists
                    $stmt = $db->prepare("SELECT * FROM reservas WHERE sala = ? AND tempo = ? AND data = ?");
                    $stmt->bind_param("sss", $sala_id, $tempo_id, $data_reserva_formatted);
                    $stmt->execute();
                    $existing = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    
                    if ($existing) {
                        $reservas_duplicadas++;
                        continue;
                    }
                    
                    // Insert reservation
                    $motivo = "Horário adicionado por um administrador através do script de semanas repetidas.";
                    $extra = "Reserva criada automaticamente pelo administrador " . htmlspecialchars($_SESSION['nome'], ENT_QUOTES, 'UTF-8') . " para múltiplas semanas.";
                    
                    $stmt = $db->prepare("INSERT INTO reservas (sala, tempo, data, requisitor, aprovado, motivo, extra) VALUES (?, ?, ?, ?, 1, ?, ?)");
                    $stmt->bind_param("ssssss", $sala_id, $tempo_id, $data_reserva_formatted, $requisitor_id, $motivo, $extra);
                    
                    if ($stmt->execute()) {
                        $reservas_criadas++;
                    } else {
                        $erros[] = "Erro ao criar reserva para {$data_reserva_formatted} - {$tempo['horashumanos']}: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
            
            // Display results
            echo "<div class='mt-3 alert alert-success fade show' role='alert'>
                <strong>Sucesso!</strong> {$reservas_criadas} reserva(s) criada(s) com sucesso.
            </div>";
            
            if ($reservas_duplicadas > 0) {
                echo "<div class='mt-3 alert alert-warning fade show' role='alert'>
                    <strong>Atenção:</strong> {$reservas_duplicadas} reserva(s) já existia(m) e não foi/foram criada(s).
                </div>";
            }
            
            if (!empty($erros)) {
                echo "<div class='mt-3 alert alert-danger fade show' role='alert'>
                    <strong>Erros encontrados:</strong>
                    <ul class='mb-0'>";
                foreach ($erros as $erro) {
                    echo "<li>" . htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') . "</li>";
                }
                echo "</ul></div>";
            }
            
            // Summary
            echo "<div class='mt-3 alert alert-info fade show' role='alert'>
                <strong>Resumo:</strong><br>
                - Sala: " . htmlspecialchars($sala['nome'], ENT_QUOTES, 'UTF-8') . "<br>
                - Utilizador: " . htmlspecialchars($requisitor['nome'], ENT_QUOTES, 'UTF-8') . "<br>
                - Tempos selecionados: " . count($tempos_ids) . "<br>
                - Semanas: {$num_semanas}<br>
                - Total de reservas esperadas: " . (count($tempos_ids) * $num_semanas) . "<br>
                - Reservas criadas: {$reservas_criadas}
            </div>";
        }
    }
}
?>