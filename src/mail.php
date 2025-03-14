<?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    function sendMail($nomeficheiro, $email, $assunto, $texto){
        require_once(__DIR__ . "/vendor/autoload.php");
        $config = json_decode(file_get_contents("config.json"));

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $config->mail->servidor;
        $mail->SMTPAuth   = $config->mail->autenticacao;
        $mail->Username   = $config->mail->mail;
        $mail->Password   = $config->mail->password;
        $mail->SMTPSecure = $config->mail->tipodeseguranca;
        $mail->Port       = $config->mail->porta;
            //Recipients
        $mail->setFrom($config->mail->mail, 'ReservaSalas');
        $mail->addAddress($email);
        $mail->addAttachment($nomeficheiro);
        $mail->isHTML(false);
        $mail->Subject = utf8_decode($assunto);
        $mail->Body = utf8_decode($texto);

        $mail->send();
    }
?>