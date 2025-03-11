<?php
    require 'login.php';
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
    $db = new SQLite3('db.sqlite3');
    $id = filter_input(INPUT_COOKIE, 'user', FILTER_UNSAFE_RAW);
    $db->exec("CREATE TABLE tempos (idtempo INTEGER, , PRIMARY KEY (idtempo);");
    $db->exec("CREATE TABLE reservas (idtempo INTEGER, data DATE, reservador VARCHAR(10), razao VARCHAR(999), aprovado BOOL, aprovadopor VARCHAR(10) PRIMARY KEY (idtempo, data), FOREIGN KEY (idtempo) REFERENCES tempos(idtempo), FOREIGN KEY (reservador) REFERENCES cache_giae(id), FOREIGN KEY (aprovadopor) REFERENCES cache_giae(id));");
    $nome = $db->querySingle("SELECT nome from cache_giae WHERE id='{$id}';");
    if (isset($_COOKIE["loggedin"])) {
        echo("<div class='h-100 d-flex align-items-center justify-content-center flex-column'>
            <p class='h2 mb-4'>Bem-vindo, <b>{$nome}</b></p></div>");
    }
?>
<?php require 'src/footer.php'; ?>
    </body>
</html>