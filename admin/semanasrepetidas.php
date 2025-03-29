<?php require 'index.php'; ?>
<h1>Semanas repetidas</h1>
<p>Este script permite repetir uma ocupação de sala para um dia específico em várias semanas.</p>
<p>Por exemplo, se o primeiro dia for o dia 1, e repetir-se 5 semanas, é o dia 1, 8, 15, 22 e 29.</p>
<form action="/admin/semanasrepetidas.php" method="POST" class="mt-4">
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="form-floating">
                <input type="number" class="form-control" id="sala" name="sala" placeholder="ID Sala" value="">
                <label for="sala">ID Sala</label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating">
                <input type="number" class="form-control" id="tempo" name="tempo" placeholder="Tempo (número)" value="">
                <label for="tempo">Tempo (número)</label>
            </div>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="form-floating">
                <input type="text" class="form-control" id="data" name="data" placeholder="Data" value="">
                <label for="data">Primeiro Dia</label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-floating">
                <input type="text" class="form-control" id="semanas" name="semanas" placeholder="Semanas a repetir" value="">
                <label for="semanas">Semanas a repetir</label>
            </div>
        </div>
    </div>
    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">Submeter</button>
    </div>
</form>
<p class="mt-3">Atenção, estas reservas aparecem em seu nome!</p>
<?php 
    if ($_POST['sala'] && $_POST['tempo'] && $_POST['data'] && $_POST['semanas']) {
        echo "a";
        $sala = $db->query("SELECT * FROM salas WHERE id='{$_POST['sala']}';")->fetch_assoc();
        $tempo = $db->query("SELECT * FROM tempos WHERE id='{$_POST['tempo']}';")->fetch_assoc();
        $data = strtotime($_POST['data']);
        $semanas = $_POST['semanas'];
        $requisitor = $_COOKIE['user'];
        for ($i = 0; $i < $semanas; $i++) {
            $datadb = date('Y-m-d', $data);
            $db->query("INSERT INTO reservas (sala, tempo, data, requisitor, aprovado, motivo, extra) VALUES ('{$sala['id']}', '{$tempo['id']}', '{$datadb}', '{$requisitor}', 1, 'Horário adicionado por um administrador.', 'Este horário foi adicionado durante várias semanas por um administrador do site.');");
            $data = strtotime(date('Y-m-d', $data) . ' + 7 days');
        }
        echo "<div class='mt-2 alert
        alert-success fade show' role='alert'>Reservas adicionadas com sucesso.</div>";
    }
?>