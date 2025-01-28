<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title>Page Title</title>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
</head>
<body>
    <?php
        require_once(__DIR__ . "/vendor/autoload.php");
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        $giae = new \juoum\GiaeConnect\GiaeConnect("giae.aejics.org", $_POST["user"], $_POST["pass"]);
        $config = json_decode($giae->getConfInfo(), true);
        if (str_contains($giae->getConfInfo(), 'Erro do Servidor')){
            echo "A sua palavra-passe está errada. Por favor tente novamente.";
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

</body>
</html>
