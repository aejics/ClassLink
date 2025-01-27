<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Page Title</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
</head>
<body>
    <style>
        img {
            width: 100px;
            height: 150px;
            border-radius: 50%;
        }
    </style>
    <?php
        require_once(__DIR__ . "/vendor/autoload.php");
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $giae = new \juoum\GiaeConnect\GiaeConnect("giae.aejics.org", $_POST["user"], $_POST["pass"]);
        $config = json_decode($giae->getConfInfo(), true);
        $nome = $config['nomeutilizador'];
        $fotoutente = json_decode('"' . $config['fotoutente'] . '"'); // Decode Unicode
        echo "<img src='https://giae.aejics.org/" . $fotoutente . "' />";
    ?>

    <h1>Bem-vindo, <?php echo($nome)?></h1>
</body>
</html>
