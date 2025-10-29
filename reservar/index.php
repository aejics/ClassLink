<?php
require_once(__DIR__ . '/../src/db.php');
session_start();
if (!isset($_SESSION['validity']) || $_SESSION['validity'] < time()) {
    http_response_code(403);
    header("Location: /login");
    die("A reencaminhar para iniciar sessão...");
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar uma Sala | ClassLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="/assets/index.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/reservar.css">
    <link rel='icon' href='/assets/logo.png'>
    <script>
        function updateBulkControls() {
            const checkboxes = document.querySelectorAll('.bulk-checkbox:checked');
            const controls = document.getElementById('bulkReservationControls');
            const counter = document.getElementById('selectedCount');
            
            if (checkboxes.length > 0) {
                controls.style.display = 'block';
                counter.textContent = checkboxes.length + ' tempo' + (checkboxes.length > 1 ? 's' : '') + ' selecionado' + (checkboxes.length > 1 ? 's' : '');
            } else {
                controls.style.display = 'none';
            }
        }
        
        function clearBulkSelection() {
            const checkboxes = document.querySelectorAll('.bulk-checkbox');
            checkboxes.forEach(cb => cb.checked = false);
            updateBulkControls();
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.bulk-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateBulkControls);
            });
        });
    </script>
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
    <div class="d-flex align-items-center justify-content-center flex-column">
        <p class="h2 fw-light">Reservar uma Sala</p>
        <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST" class="d-flex align-items-center">
            <div class="form-floating me-2">
                <select class="form-select" id="sala" name="sala" required onchange="this.form.submit();">
                    <?php if ($_POST['sala'] == "0" | !$_POST['sala']) {
                        echo "<option value='0' selected disabled>Escolha uma sala</option>";
                    } else {
                        echo "<option value='0' disabled>Escolha uma sala</option>";
                    }
                    $salas = $db->query("SELECT * FROM salas ORDER BY nome ASC;");
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
        </form>
    </div>
    <?php
    if (isset($_POST['sala']) | isset($_GET['sala'])) {
        echo (
            "<div class='container mt-3 d-flex align-items-center justify-content-center flex-column'>
            <form id='bulkReservationForm' method='POST' action='/reservar/manage.php?subaction=bulk'>
            <table class='table table-bordered'><thead><tr><th scope='col'>Tempos</th>"
        );
        for ($i = 0; $i < 7; $i++) {
            if ($_GET['before']) {
                $segunda = strtotime($_GET['before']);
            } else {
                $segunda = strtotime("monday this week");
            }
            $segundadiaantes = strtotime("-1 week", $segunda);
            $segundadiaantes = date("d-m-Y", $segundadiaantes);
            $segundadiadepois = strtotime("+1 week", $segunda);
            $segundadiadepois = date("d-m-Y", $segundadiadepois);
            $dia = date("d-m-Y", strtotime("+{$i} day", $segunda));
            echo "<th scope='col'>{$dia}</th>";
        };
        echo "</tr></thead><tbody>";
        $tempos = $db->query("SELECT * FROM tempos ORDER BY horashumanos ASC;");
        // por cada tempo:
        for ($i = 1; $i <= $tempos->num_rows; $i++) {
            while ($row = $tempos->fetch_assoc()) {
                echo "<tr><th scope='row'>{$row['horashumanos']}</td>";
                // por cada dia da semana:
                for ($j = 0; $j < 7; $j++) {
                    $diacheckdb = $segunda + ($j * 86400);
                    $diacheckdb = date("Y-m-d", $diacheckdb);
                    
                    $sala = isset($_GET['sala']) ? $_GET['sala'] : $_POST['sala'];
                    
                    $stmt = $db->prepare("SELECT * FROM reservas WHERE sala=? AND data=? AND tempo=?");
                    $stmt->bind_param("sss", $sala, $diacheckdb, $row['id']);
                    $stmt->execute();
                    $tempoatualdb = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    
                    if (!$tempoatualdb || $tempoatualdb['aprovado'] == -1) {
                        echo "<td class='bg-success text-white text-center'>
                        <div style='display: flex; flex-direction: column; align-items: center; gap: 5px;'>
                        <input type='checkbox' name='slots[]' value='" . urlencode($row['id']) . "|" . urlencode($sala) . "|" . urlencode($diacheckdb) . "' class='bulk-checkbox' style='width: 20px; height: 20px;'>
                        <a class='reserva' href='/reservar/manage.php?tempo=" . urlencode($row['id']) . "&sala=" . urlencode($sala) . "&data=" . urlencode($diacheckdb) . "'>
                        Livre
                        </a>
                        </div></td>";
                    } else {
                        $stmt = $db->prepare("SELECT nome FROM cache WHERE id=?");
                        $stmt->bind_param("s", $tempoatualdb['requisitor']);
                        $stmt->execute();
                        $nomerequisitor = $stmt->get_result()->fetch_assoc();
                        $stmt->close();
                        
                        $nomerequisitor['nome'] = preg_replace('/^(\S+).*?(\S+)$/u', '$1 $2', $nomerequisitor['nome']);
                        if ($tempoatualdb['aprovado'] == 0) {
                            echo "<td class='bg-warning text-white text-center'>
                            <a class='reserva' href='/reservar/manage.php?tempo=" . urlencode($row['id']) . "&sala=" . urlencode($sala) . "&data=" . urlencode($diacheckdb) . "'>
                            Pendente
                            <br>
                            " . htmlspecialchars($nomerequisitor['nome'], ENT_QUOTES, 'UTF-8') . "
                            </a></td>";
                        } else if ($tempoatualdb['aprovado'] == 1) {
                            echo "<td class='bg-danger text-white text-center'>
                            <a class='reserva' href='/reservar/manage.php?tempo=" . urlencode($row['id']) . "&sala=" . urlencode($sala) . "&data=" . urlencode($diacheckdb) . "'>
                            Ocupado
                            <br>
                            " . htmlspecialchars($nomerequisitor['nome'], ENT_QUOTES, 'UTF-8') . "
                            </a></td>";
                        }
                    }
                }
                echo "</tr>";
            }
        }
        echo "</table>
        <div id='bulkReservationControls' style='display: none; width: 100%; margin-bottom: 15px;'>
            <div class='card'>
                <div class='card-body'>
                    <h5 class='card-title'>Reservas em Massa</h5>
                    <p id='selectedCount'>0 tempos selecionados</p>
                    <div class='form-floating mb-2'>
                        <input type='text' class='form-control' id='bulkMotivo' name='motivo' placeholder='Motivo da Reserva' required>
                        <label for='bulkMotivo'>Motivo da Reserva</label>
                    </div>
                    <div class='form-floating mb-2'>
                        <textarea class='form-control' id='bulkExtra' name='extra' placeholder='Informação Extra' rows='3' style='height: 100px;'></textarea>
                        <label for='bulkExtra'>Informação Extra</label>
                    </div>
                    <button type='submit' class='btn btn-success me-2'>Reservar Selecionados</button>
                    <button type='button' class='btn btn-secondary' onclick='clearBulkSelection()'>Limpar Seleção</button>
                </div>
            </div>
        </div>
        </form>
        <div class='d-flex'><a href='/reservar/?before={$segundadiaantes}&sala=";
        if ($_POST['sala']) {
            echo "{$_POST['sala']}' class='btn mb-2 me-2 btn-success'>Semana Anterior</a> <a href='/reservar/?before={$segundadiadepois}&sala={$_POST['sala']}' class='btn mb-2 ms-2 btn-success'>Semana Seguinte</a></div></div>";
        } else {
            echo "{$_GET['sala']}' class='btn mb-2 me-2 btn-success'>Semana Anterior</a> <a href='/reservar/?before={$segundadiadepois}&sala={$_GET['sala']}' class='btn mb-2 ms-2 btn-success'>Semana Seguinte</a></div></div>";
        }
    }
    ?>
</body>

</html>