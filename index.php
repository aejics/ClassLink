<?php
    require 'login.php';
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    ini_set('display_errors', 'On');
    $db = new SQLite3('db.sqlite3');
    $id = filter_input(INPUT_COOKIE, 'user', FILTER_UNSAFE_RAW);
    $db->exec("CREATE TABLE tempos (idtempo INTEGER, horastart TIME, horaend TIME, PRIMARY KEY (idtempo);");
        $db->exec("CREATE TABLE reservas (idtempo INTEGER, data DATE, reservador VARCHAR(10), razao VARCHAR(999), aprovado BOOL, aprovadopor VARCHAR(10) PRIMARY KEY (idtempo, data), FOREIGN KEY (idtempo) REFERENCES tempos(idtempo), FOREIGN KEY (reservador) REFERENCES cache_giae(id), FOREIGN KEY (aprovadopor) REFERENCES cache_giae(id));");
    $nome = $db->querySingle("SELECT nome from cache_giae WHERE id='{$id}';");
    if (isset($_COOKIE["loggedin"])) {
        echo("<div class='h-100 d-flex align-items-center justify-content-center flex-column'>
            <p class='h2 mb-4'>Bem-vindo, <b>{$nome}</b></p>");
    }
    echo(
        "<table class='table table-bordered'><thead><tr><th scope='col'>Tempos</th>"
    );
    for ($i = 0; $i < 5; $i++){
        $segunda = strtotime("monday this week");
        $dia = date("d-m-Y", strtotime("+{$i} day", $segunda));
        echo "<th scope='col'>{$dia}</th>";
    };
    echo "</tr></thead><tbody>";
    for ($i = 0; $i < 12; $i++){
        $numtempo = $i + 1;
        echo "<tr><th scope='row'>Tempo {$numtempo}</th>";
        for ($j = 1; $j <= 5; $j++){
            if ($j == 1 && $i == 2){
                echo "<td class='bg-warning text-white text-center'>Pendente<br>André Gaspar</td>";
            } else if ($j == 5 && $i == 4){
                echo "<td class='bg-danger text-white text-center'>Ocupado<br>José Vidigal</td>";
            } else {
                echo "<td class='bg-success text-white text-center'>Livre</td>";
            }
        }
        echo "</tr>";
    }
    echo "</table>";
?>
<?php require 'src/footer.php'; ?>
    </body>
</html>