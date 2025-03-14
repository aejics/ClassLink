<?php 
    require '../src/config.php';
    require '../src/db.php';
    require '../src/base.php';
?>

<div class="h-100 d-flex align-items-center justify-content-center flex-column">
    <p class="h2 fw-light">Reservar uma Sala</p>    
<form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST" class="d-flex align-items-center">
    <div class="form-floating me-2">
        <select class="form-select" id="sala" name="sala" required onchange="this.form.submit();">
            <?php if ($_POST['sala'] == "0" | !$_POST['sala']){
            echo "<option value='0' selected disabled>Escolha uma sala</option>";
            } else {
                echo "<option value='0' disabled>Escolha uma sala</option>";
            }
            $salas = $db->query("SELECT * FROM salas;");
            while ($sala = $salas->fetch_assoc()){
                if ($_POST['sala'] == $sala['id'] || $_GET['sala'] == $sala['id']){
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
    if ($_POST['sala'] | $_GET['sala']){
        echo(
            "<div class='mt-3 h-100 d-flex align-items-center justify-content-center flex-column'>
            <table class='table table-bordered'><thead><tr><th scope='col'>Tempos</th>"
        );
        for ($i = 0; $i < 7; $i++){
            if ($_GET['before']){
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
        $tempos = $db->query("SELECT * FROM tempos;");
        // por cada tempo:
        for ($i = 1; $i <= $tempos->num_rows; $i++){
            while ($row = $tempos->fetch_assoc()){
                echo "<tr><th scope='row'>{$row['horashumanos']}</td>";
                // por cada dia da semana:
                for ($j = 0; $j < 7; $j++){
                    $diacheckdb = $segunda + ($j * 86400);
                    $diacheckdb = date("Y-m-d", $diacheckdb);
                    if ($_GET['sala']){
                        $tempoatualdb = $db->query("SELECT * FROM reservas WHERE sala='{$_GET['sala']}' AND data='{$diacheckdb}' AND tempo='{$row['id']}';");
                        $sala = $_GET['sala'];
                    } else {
                        $tempoatualdb = $db->query("SELECT * FROM reservas WHERE sala='{$_POST['sala']}' AND data='{$diacheckdb}' AND tempo='{$row['id']}';");
                        $sala = $_POST['sala'];
                    }
                    $tempoatualdb = $tempoatualdb->fetch_assoc();
                    if (!$tempoatualdb){
                        echo "<td class='bg-success text-white text-center'>
                        <a class='reserva' href='/reservas/manage.php?tempo={$row['id']}&sala={$sala}&data={$diacheckdb}'>
                        Livre
                        </a></td>";
                    } else { 
                        $nomerequisitor = $db->query("SELECT nome FROM cache_giae WHERE id='{$tempoatualdb['requisitor']}';");
                        $nomerequisitor = $nomerequisitor->fetch_assoc();
                        if ($tempoatualdb['aprovado'] == 0){
                            echo "<td class='bg-warning text-white text-center'>
                            <a class='reserva' href='/reservas/manage.php?tempo={$row['id']}&sala={$sala}&data={$diacheckdb}'>
                            Pendente
                            <br>
                            {$nomerequisitor['nome']}
                            </a></td>";
                        } else if ($tempoatualdb['aprovado'] == 1){
                            echo "<td class='bg-danger text-white text-center'>
                            <a class='reserva' href='/reservas/manage.php?tempo={$row['id']}&sala={$sala}&data={$diacheckdb}'>
                            Ocupado
                            <br>
                            {$nomerequisitor['nome']}
                            </a></td>";
                    }
                }
            }
            echo "</tr>";
            }
        }
        echo "</table><div class='d-flex'><a href='/reservas/?before={$segundadiaantes}&sala=";
        if ($_POST['sala']){
            echo "{$_POST['sala']}' class='btn mb-2 me-2 btn-success'>Semana Anterior</a> <a href='/reservas/?before={$segundadiadepois}&sala={$_POST['sala']}' class='btn mb-2 ms-2 btn-success'>Semana Seguinte</a></div></div>";
        } else {
            echo "{$_GET['sala']}' class='btn mb-2 me-2 btn-success'>Semana Anterior</a> <a href='/reservas/?before={$segundadiadepois}&sala={$_GET['sala']}' class='btn mb-2 ms-2 btn-success'>Semana Seguinte</a></div></div>";
        }
    }
?>