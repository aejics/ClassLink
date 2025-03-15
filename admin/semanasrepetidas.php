<?php require 'index.php'; ?>
<h1>Repetir ocupação de sala</h1>
<p>Esta funcionalidade permite repetir uma ocupação de sala para um dia específico em várias semanas.</p>
<p>Por exemplo, se o primeiro dia for o dia 1, e repetir-se 5 semanas, é o dia 1, 8, 15, 22 e 29.</p>
<form action="/admin/horariosforcados.php" method="POST">
    <div class="form-floating me-2">
        <input type="number" class="form-control form-control-sm" id="sala" name="sala" placeholder="ID Sala" value="">
        <label for="sala">ID Sala</label>
    </div>
    <div class="form-floating me-2">
        <input type="number" class="form-control form-control-sm" id="tempo" name="tempo" placeholder="Tempo (número)" value="">
        <label for="tempo">Tempo (número)</label>
    </div>
    <div class="form-floating me-2">
        <input type="text" class="form-control form-control-sm" id="data" name="data" placeholder="Data" value="">
        <label for="data">Primeiro Dia</label>
    </div>
    <div class="form-floating me-2">
        <input type="text" class="form-control form-control-sm" id="semanas" name="semanas" placeholder="Semanas" value="">
        <label for="semanas">Quantas semanas é que repete-se?</label>
    </div>
    <button type="submit" class="btn btn-primary btn-sm" style="height: 38px;">Submeter</button>
</form>

<?php 
    if ($_POST['sala'] && $_POST['tempo'] && $_POST['data'] && $_POST['semanas']) {
        echo "a";
        $sala = $db->query("SELECT * FROM salas WHERE id='{$_POST['sala']}';")->fetch_assoc();
        $tempo = $db->query("SELECT * FROM tempos WHERE id='{$_POST['tempo']}';")->fetch_assoc();
        $data = strtotime($_POST['data']);
        $semanas = $_POST['semanas'];
        $requisitor = $info['adminforcado'];
        for ($i = 0; $i < $semanas; $i++) {
            $datadb = date('Y-m-d', $data);
            $db->query("INSERT INTO reservas (sala, tempo, data, requisitor, aprovado, motivo, extra) VALUES ('{$sala['id']}', '{$tempo['id']}', '{$datadb}', '{$requisitor}', 1, 'Horário adicionado por um administrador.', 'Este horário foi adicionado durante várias semanas por um administrador do site.');");
            $data = strtotime(date('Y-m-d', $data) . ' + 7 days');
        }
        echo "<div class='mt-2 alert
        alert-success fade show' role='alert'>Reservas adicionadas com sucesso.</div>";
    }
?>