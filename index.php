<!DOCTYPE html>
<html>
    <head>
        <meta charset='utf-8'>
        <meta http-equiv='X-UA-Compatible' content='IE=edge'>
        <title>Reserva Salas</title>
        <meta name='viewport' content='width=device-width, initial-scale=1'>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <link href="/css/main.css" rel="stylesheet">
    </head>
    <?php 
        require_once(__DIR__ . "/vendor/autoload.php");
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    ?>
    <body>
        <?php include 'src/verifsessaovalida.php'; ?>
        <?php include 'src/header.php'; ?>
        <?php include 'src/navbar.php'; ?>
        <br>
        <?php include 'src/asminhasreservas.php'; ?>
        <?php include 'src/loginform.php'; ?>
        <?php include 'src/footer.php'; ?>
    </body>
</html>