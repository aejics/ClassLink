<?php
    $info = array(
        'nome' => 'ReservaSalas AEJICS',
        // Assumimos que o giae está configurado com o SSL.
        'urlGiae' => 'giae.aejics.org'
    );

    // Não tenho 100% de certeza se enviar mails irá ser uma função da app.
    // 11/03/2025 mpisco
    $mail = array(
        'servidor' => 'smtp.gmail.com',
        'porta' => 465,
        'autenticacao' => true,
        // caso a autenticação seja por starttls, usar PHPMailer::ENCRYPTION_STARTTLS
        // caso a autenticação seja por ssl, usar PHPMailer::ENCRYPTION_SMTPS
        // caso não seja necessário autenticação, por false na opção autenticacao, e não importar-se para os outros
        // ^^ (não testado)
        'tipodeseguranca' => 'PHPMailer::ENCRYPTION_STARTTLS ou PHPMailer::ENCRYPTION_SMTPS',
        'mail' => '',
        'password' => ''
    );


    // mensagem para membros externos da comunidade de hacking
    // potencialmente removida na versão final
    if ($_SERVER['REQUEST_URI'] == '/config.php'){
        echo "What are you doing you snooper???<img src=\"https://i.gifer.com/74Uy.gif\">";
    }
?>