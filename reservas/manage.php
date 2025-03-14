<?php 
    require '../src/config.php';
    require '../src/db.php';
    require '../src/base.php';
    echo "<h1>Estado da Reserva</h1>";
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    if ($_GET['tempo'] && $_GET['data'] && $_GET['sala']){
        $tempo = filter_var($_GET['tempo'], FILTER_SANITIZE_NUMBER_INT);
        $data = filter_var($_GET['data'], FILTER_SANITIZE_STRING);
        $sala = filter_var($_GET['sala'], FILTER_SANITIZE_STRING);
        $requisitor = filter_var($_COOKIE['user'], FILTER_SANITIZE_STRING);
        switch ($_GET['subaction']){
            case "reservar":
                $db->query("INSERT INTO reservas (sala, tempo, requisitor, data, aprovado) VALUES ('{$sala}', '{$tempo}', '{$requisitor}', '{$data}', 0);");
                header("Location: /reservas/");
                break;
            case "cancelar":
                $db->query("DELETE FROM reservas WHERE sala='{$sala}' AND tempo='{$tempo}' AND requisitor='{$requisitor}' AND data='{$data}';");
                header("Location: /reservas/");
                break;
            case null:
                echo "a";
        }
    }