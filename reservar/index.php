<?php 
    require '../login/index.php';
    $dados = $db->query("SELECT * FROM cache_giae WHERE email = '{$perfil['perfil']['email']}';")->fetch_assoc();
    $isadmin = $db->query("SELECT * FROM admins WHERE id = '{$dados['id']}' AND permitido = 1;")->num_rows;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar uma Sala | Reserva Salas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="/assets/index.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/reservar.css">
</head>
<body>
<nav>
    <a href="/"><img src="/assets/logo.png" class="logo"></a>
    <div class="list">
        <ul>
            <li><a href="/reservas">As minhas reservas</a></li>
            <li><a href="/reservar">Reservar sala</a></li>
            <?php
                if ($isadmin) {
                echo "<li><a href='/admin'>Painel administrativo</a></li>";
                }
            ?>
            <li><a href="/login/?action=logout">Terminar sessão</a></li>
        </ul> 
    </div>
</nav>
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
                    if (!$tempoatualdb || $tempoatualdb['aprovado'] == -1){
                        echo "<td class='bg-success text-white text-center'>
                        <a class='reserva' href='/reservar/manage.php?tempo={$row['id']}&sala={$sala}&data={$diacheckdb}'>
                        Livre
                        </a></td>";
                    } else { 
                        $nomerequisitor = $db->query("SELECT nome FROM cache_giae WHERE id='{$tempoatualdb['requisitor']}';");
                        $nomerequisitor = $nomerequisitor->fetch_assoc();
                        if ($tempoatualdb['aprovado'] == 0){
                            echo "<td class='bg-warning text-white text-center'>
                            <a class='reserva' href='/reservar/manage.php?tempo={$row['id']}&sala={$sala}&data={$diacheckdb}'>
                            Pendente
                            <br>
                            {$nomerequisitor['nome']}
                            </a></td>";
                        } else if ($tempoatualdb['aprovado'] == 1){
                            echo "<td class='bg-danger text-white text-center'>
                            <a class='reserva' href='/reservar/manage.php?tempo={$row['id']}&sala={$sala}&data={$diacheckdb}'>
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
        echo "</table><div class='d-flex'><a href='/reservar/?before={$segundadiaantes}&sala=";
        if ($_POST['sala']){
            echo "{$_POST['sala']}' class='btn mb-2 me-2 btn-success'>Semana Anterior</a> <a href='/reservar/?before={$segundadiadepois}&sala={$_POST['sala']}' class='btn mb-2 ms-2 btn-success'>Semana Seguinte</a></div></div>";
        } else {
            echo "{$_GET['sala']}' class='btn mb-2 me-2 btn-success'>Semana Anterior</a> <a href='/reservar/?before={$segundadiadepois}&sala={$_GET['sala']}' class='btn mb-2 ms-2 btn-success'>Semana Seguinte</a></div></div>";
        }
    }
?>
</body>
</html>
