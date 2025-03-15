<?php 
    require '../src/config.php';
    require '../src/db.php';
    require '../src/base.php';

    if ($_GET['tempo'] && $_GET['data'] && $_GET['sala']){
        $tempo = filter_var($_GET['tempo'], FILTER_SANITIZE_NUMBER_INT);
        $data = filter_var($_GET['data'], FILTER_SANITIZE_STRING);
        $sala = filter_var($_GET['sala'], FILTER_SANITIZE_NUMBER_INT);
        $requisitor = filter_var($_COOKIE['user'], FILTER_SANITIZE_STRING);
        switch ($_GET['subaction']){
            case "reservar":
                $motivo = filter_var($_POST['motivo'], FILTER_SANITIZE_STRING);
                $extra = filter_var($_POST['extra'], FILTER_SANITIZE_STRING);
                $db->query("INSERT INTO reservas (sala, tempo, requisitor, data, aprovado, motivo, extra) VALUES ('{$sala}', '{$tempo}', '{$requisitor}', '{$data}', 0, '{$motivo}', '{$extra}');");
                header("Location: /reservas/?sala={$sala}&tempo={$tempo}");
                break;
            case null:
                $detalhesreserva = $db->query("SELECT * FROM reservas WHERE sala='{$sala}' AND tempo='{$tempo}' AND data='{$data}' AND aprovado!=-1;")->fetch_assoc();
                if (!$detalhesreserva){
                    $salaextenso = $db->query("SELECT nome FROM salas WHERE id='{$sala}';")->fetch_assoc()['nome'];
                    echo "<h2>Reservar Sala</h2>";
                    echo "<form class='form w-50' action='/reservas/manage.php?subaction=reservar&tempo={$tempo}&data={$data}&sala={$_GET['sala']}' method='POST'>
                    <div class='form-floating me-2'>
                    <input type='text' class='form-control form-control-sm' id='sala' name='sala' placeholder='Sala' value='{$salaextenso}' disabled>
                    <label for='sala'>Sala</label>
                    </div>
                    <div class='form-floating me-2 mt-2'>
                    <input type='text' class='form-control form-control-sm' id='motivo' name='motivo' placeholder='Motivo da Reserva' required>
                    <label for='motivo'>Motivo da Reserva</label>
                    </div>
                    <div class='form-floating me-2 mt-2'>
                    <textarea class='form-control form-control-sm' id='extra' name='extra' placeholder='Informação Extra' rows='6' style='height: 150px;'></textarea>
                    <label for='extra'>Informação Extra</label>
                    <p class='mt-1 text-center'>Nota: A reserva será submetida para aprovação.</p>
                    </div>
                    <button type='submit' class='btn btn-success w-100'>Reservar</button>
                    </form>";
                    echo "<a href='{$_SERVER['HTTP_REFERER']}' class='mt-2 btn btn-primary'>Voltar</a>";
                } else {
                    echo "<h2>Detalhes da Reserva:</h2>";
                    $salaextenso = $db->query("SELECT nome FROM salas WHERE id='{$sala}';")->fetch_assoc()['nome'];
                    echo "<p class='fw-bold'>Sala: <span class='fw-normal'>{$salaextenso}</span></p>";
                    $requisitorextenso = $db->query("SELECT nomecompleto FROM cache_giae WHERE id='{$requisitor}';")->fetch_assoc()['nomecompleto'];
                    echo "<p class='fw-bold'>Requisitada por: <span class='fw-normal'>{$requisitorextenso}</span></p>";
                    $horastempo = $db->query("SELECT horashumanos FROM tempos WHERE id='{$tempo}';")->fetch_assoc()['horashumanos'];
                    echo "<p class='fw-bold'>Tempo: <span class='fw-normal'>{$horastempo}</span></p>";
                    echo "<p class='fw-bold'>Data: <span class='fw-normal'>{$data}</span></p>";
                    echo "<p class='fw-bold'>Aprovado: ";
                    if ($detalhesreserva['aprovado'] == 1){
                        echo "<span class='fw-normal'>Sim</span></p>";
                    } if ($detalhesreserva['aprovado'] == -1) {
                        echo "<span class='fw-normal'>Rejeitado</span></p>";
                    } else {
                        echo "<span class='fw-normal'>Não</span></p>";
                    }
                    echo "<p class='fw-bold'>Motivo: <span class='fw-normal'>{$detalhesreserva['motivo']}</span></p>";
                    echo "<p class='fw-bold'>Informação Extra:</p>
                        <textarea rows='4' cols='50' class='fw-normal' disabled>{$detalhesreserva['extra']}</textarea>";
                    if (strpos($_SERVER['HTTP_REFERER'], '/admin/pedidos.php') !== false) {
                        echo "<a href='/admin/pedidos.php' class='btn btn-primary mt-2'>Voltar</a>";
                    } else {
                        echo "<a href='/reservas/?sala={$sala}' class='btn btn-primary mt-2'>Voltar</a>";
                    }
                }
        }
    }