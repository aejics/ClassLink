<?php     
    echo "<p class='h2 fw-light'>As suas reservas:</p>";
    $requisitor = filter_var($_COOKIE['user'], FILTER_SANITIZE_STRING);
    $reservas = $db->query("SELECT * FROM reservas WHERE requisitor='{$requisitor}' ORDER BY data DESC;");
    echo "<div class='mt-3 text-center'>";
    echo "<table class='mt-2 table table-bordered'><thead><tr><th scope='col'>Sala</th><th scope='col'>Data</th><th scope='col'>Tempo</th><th scope='col'>Estado</th></tr></thead><tbody>";
    while ($reserva = $reservas->fetch_assoc()) {
        $sala = $db->query("SELECT nome FROM salas WHERE id='{$reserva['sala']}';")->fetch_assoc();
        $tempo = $db->query("SELECT horashumanos FROM tempos WHERE id='{$reserva['tempo']}';")->fetch_assoc();
        if ($reserva['aprovado'] == 1) {
            echo "<tr><td>{$sala['nome']}</td><td>{$reserva['data']}</td><td>{$tempo['horashumanos']}</td><td><span class='badge bg-success' data-bs-toggle='tooltip' data-placement='top' title='A sua reserva foi aprovada! Um email foi lhe enviado com mais informações.'>Aprovado</span></td></tr>";
        } else if ($reserva['aprovado'] == -1) {
            echo "<tr><td>{$sala['nome']}</td><td>{$reserva['data']}</td><td>{$tempo['horashumanos']}</td><td><span class='badge bg-danger' data-bs-toggle='tooltip' data-placement='top' title='Foi lhe enviado um email com mais informações sobre a rejeição.'>Rejeitado</span></td></tr>";
        } else {
            echo "<tr><td>{$sala['nome']}</td><td>{$reserva['data']}</td><td>{$tempo['horashumanos']}</td><td><span class='badge bg-warning' data-bs-toggle='tooltip' data-placement='top' title='A sua reserva foi enviada e está a ser revista. Irá receber um email com mais informações em breve'>Pendente</span></td></tr>";
        }
    }
    echo "</table></div>";
?>