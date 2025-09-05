<?php
    require_once(__DIR__ . '/../vendor/autoload.php');
    use League\OAuth2\Client\Provider\GenericProvider;

    $mail = array(
        'ativado' => true,
        'servidor' => 'smtp.gmail.com',
        'porta' => 465,
        'autenticacao' => true,
        // caso a autenticação seja por starttls, usar PHPMailer::ENCRYPTION_STARTTLS
        // caso a autenticação seja por ssl, usar PHPMailer::ENCRYPTION_SMTPS
        // caso não seja necessário autenticação, por false na opção autenticacao, e não importar-se para os outros
        'tipodeseguranca' => 'PHPMailer::ENCRYPTION_STARTTLS ou PHPMailer::ENCRYPTION_SMTPS',
        'username' => '',
        'fromname' => 'Reserva de Salas',
        'mailfrom' => '',
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

    // Set up the OAuth 2.0 provider
    $provider = new GenericProvider([
        'urlAuthorize'            => 'https://authentik.devenv.marcopisco.com/application/o/authorize/',
        'urlAccessToken'          => 'https://authentik.devenv.marcopisco.com/application/o/token/',
        'urlResourceOwnerDetails' => 'https://authentik.devenv.marcopisco.com/application/o/userinfo/',
        'clientId'     => 'clientid',
        'clientSecret' => 'clientsecret',
        'redirectUri'  => 'https://' . $_SERVER['HTTP_HOST'] . '/login'
    ]);
    $logoutUrlProvider = 'https://authentik.devenv.marcopisco.com/application/o/reserva-salas/end-session/';
?>