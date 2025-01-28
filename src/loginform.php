<?php
    if (!isset($_COOKIE["loggedin"]))
        echo "<div class='h-100 d-flex align-items-center justify-content-center flex-column'>
            <p class='h2 mb-4'>Autentique-se via GIAE</p>
            <p class='mb-4'>Utilize as credenciais do GIAE para continuar para Reserva Salas</p>
            <form action='login.php' method='POST' class='w-100' style='max-width: 300px;'>
                <div class='mb-3'>
                    <label for='user' class='form-label'>Nome de utilizador <b class='required'>*</b>:</label>
                    <input type='text' class='form-control' id='user' name='user' required autofocus placeholder='f1964'>
                </div>
                <div class='mb-3'>
                    <label for='pass' class='form-label'>Palavra-passe <b class='required'>*</b>:</label>
                    <input type='password' class='form-control' id='pass' name='pass' required placeholder='********'>
                </div>
                <button type='submit' class='btn btn-primary w-100'>Iniciar sessão</button>
            </form>
            <hr>
        </div>"
?>