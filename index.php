<?php
    require 'login.php';
    if (isset($_COOKIE["loggedin"])) {
        echo("<div class='h-100 d-flex align-items-center justify-content-center flex-column'>
            <p class='h2 mb-4'>Bem-vindo, <b>{$nome}</b></p>");
    }
    require 'src/footer.php';
?>
    </body>
</html>