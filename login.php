<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>ReservaSalas - Login</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta http-equiv="refresh" content="5;url=/" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link href="/css/main.css" rel="stylesheet">
</head>
<body>
    <?php include 'src/header.php'; ?>
    <?php include 'src/navbar.php'; ?>
    <?php
        require_once(__DIR__ . "/vendor/autoload.php");
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $giae = new \juoum\GiaeConnect\GiaeConnect("giae.aejics.org", $_POST["user"], $_POST["pass"]);
        $config = json_decode($giae->getConfInfo(), true);
        if (str_contains($giae->getConfInfo(), 'Erro do Servidor')){
            echo("<div class='alert alert-danger text-center' role='alert'>A sua palavra-passe está errada.
                </div>");
        }
        else {
            setcookie("loggedin", "true", time() + 3599, "/");
            setcookie("session", $giae->session, time() + 3599, "/");
            setcookie("nomedapessoa", $config['nomeutilizador'], time() + 3599, "/");
            setcookie("username", $_POST["user"], time() + 3599, "/");
            setcookie("password", $_POST["pass"], time() + 3599, "/");
            header('Location: /');
        }
    ?>
    <?php include 'src/footer.php'; ?>
</body>
</html>
