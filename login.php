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
        $giae = new \juoum\GiaeConnect\GiaeConnect("giae.aejics.org", $_POST["user"], $_POST["pass"]);
        print_r($giae->getSaldo());
    ?>
</body>
</html>