<?php 
    $giae = new \juoum\GiaeConnect\GiaeConnect("giae.aejics.org");
    $giae->session=$_COOKIE["session"];
    if (isset($_COOKIE["loggedin"]))
        echo "<div class='h-100 d-flex align-items-center justify-content-center flex-column'>
        <p class='h5 mb-4'>Bem-vindo, <b>", $_COOKIE["nomedapessoa"], "</b></p>
        <p class='mb-4'>Não tem reservas atualmente em seu nome.</p>
        </div>";

?>
