<?php
    $info = array(
        'adminforcado' => 'a11531',
        'giae' => 'giae.aejics.org'
    );

    $mail = array(
        'servidor' => 'smtp.gmail.com',
        'porta' => 465,
        'autenticacao' => true,
        // caso a autenticação seja por starttls, usar PHPMailer::ENCRYPTION_STARTTLS
        // caso a autenticação seja por ssl, usar PHPMailer::ENCRYPTION_SMTPS
        // caso não seja necessário autenticação, por false na opção autenticacao, e não importar-se para os outros
        'tipodeseguranca' => 'PHPMailer::ENCRYPTION_STARTTLS ou PHPMailer::ENCRYPTION_SMTPS',
        'mail' => '',
        'password' => ''
    );

    // A base de dados deste projeto é MySQL.
    $db = array(
        'tipo' => 'mysql',
        'servidor' => 'localhost',
        'user' => 'reservasalas',
        'password' => 'salaspass',
        'db' => 'reservasalas',
        'porta' => 3306
    );
?>