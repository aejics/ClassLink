<?php
    require 'config.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
    function sendMail($email, $assunto, $texto){
        require_once(__DIR__ . "/vendor/autoload.php");

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $mail->servidor;
        $mail->SMTPAuth   = $mail->autenticacao;
        $mail->Username   = $mail->mail;
        $mail->Password   = $mail->password;
        $mail->SMTPSecure = $mail->tipodeseguranca;
        $mail->Port       = $mail->porta;
            //Recipients
        $mail->setFrom($mail->mail, );
        $mail->addAddress($email);
        $mail->isHTML(false);
        $mail->Subject = utf8_decode($assunto);
        $mail->Body = utf8_decode($texto);

        $mail->send();
    }
?>