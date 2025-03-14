<?php
    require 'src/config.php';
    require 'src/db.php';
    require 'src/base.php';

    if (isset($_COOKIE["loggedin"])) {
        $id = filter_var($_COOKIE['user'], FILTER_SANITIZE_STRING);
        $nome = $db->query("SELECT nome from cache_giae WHERE id='{$id}';")->fetch_assoc();
        echo("<div class='h-100 d-flex align-items-center justify-content-center flex-column'>
            <p class='h2'>Bem-vindo, <b>{$nome['nome']}</b></p>
            <p class='h4 fw-light'>O que vamos fazer hoje?</p></div>
            <div class='w-100 d-flex justify-content-center'>
            <a href='/reservas/' class='btn btn-success w-20 me-2'>Reservar uma Sala</a>
            </div>");
        // notificações do utilizador
        $reservas = $db->query("SELECT * FROM reservas WHERE requisitor='{$id}';");
        if ($reservas->num_rows > 0) {
            echo "<div class='mt-3 alert alert-primary text-center' role='alert'>As suas reservas:</div>";
            echo "<table class='table table-bordered'><thead><tr><th scope='col'>Sala</th><th scope='col'>Data</th><th scope='col'>Tempo</th><th scope='col'>Ações</th></tr></thead><tbody>";
            while ($reserva = $reservas->fetch_assoc()) {
                $sala = $db->query("SELECT nome FROM salas WHERE id='{$reserva['sala']}';")->fetch_assoc();
                $tempo = $db->query("SELECT horashumanos FROM tempos WHERE id='{$reserva['tempo']}';")->fetch_assoc();
                echo "<tr><td>{$sala['nome']}</td><td>{$reserva['data']}</td><td>{$tempo['horashumanos']}</td><td><a href='/reservas/?action=cancelar&id={$reserva['id']}' class='btn btn-danger'>Cancelar</a></td></tr>";
            }
        } else {
        echo "<div class='mt-3 alert alert-danger text-center' role='alert'>Ainda não tem reservas. Irão aparecer as suas reservas aqui:</div>";
        }
    } else {
        header('Location: /login/');
    }
?>