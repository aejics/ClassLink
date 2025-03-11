<?php require 'index.php'; ?>
<h3>Gestão dos Tempos</h3>
<hr>
<div class="d-flex align-items-center mb-3">
    <span class="me-3">Adicionar um tempo</span>
    <form action="tempos.php?action=criar" method="POST" class="d-flex align-items-center">
        <div class="form-floating me-2" style="flex: 1;">
            <input type="text" class="form-control form-control-sm" id="idtempo" name="idtempo" placeholder="ID do Tempo" required>
            <label for="idtempo">ID do Tempo</label>
        </div>
        <div class="form-floating me-2" style="flex: 1;">
            <input type="text" class="form-control form-control-sm" id="horashumanos" name="horashumanos" placeholder="Horas Humanas" required>
            <label for="horashumanos">Horas Humanas</label>
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="height: 38px;">Submeter</button>
    </form>
</div>

<?php
// Buscar à Base de Dados
$db = new SQLite3('../db.sqlite3'); 
// Criação da Tabela de Tempos, caso não exista. Se houverem logs ligados, esta linha vai produzir um aviso se a
// base de dados já estiver criada.
$db->exec("CREATE TABLE tempos (idtempo INTEGER DISTINCT, horashumanos VARCHAR, PRIMARY KEY (idtempo));");

// Ações caso seja preenchido o formulário
if ($_POST && $_GET['action'] == "criar") {
    $idtempo = filter_input(INPUT_POST, 'idtempo', FILTER_SANITIZE_NUMBER_INT);
    $horashumanos = filter_input(INPUT_POST, 'horashumanos', FILTER_SANITIZE_STRING);
    $c = $db->exec("INSERT INTO tempos (idtempo, horashumanos) VALUES ('$idtempo', '$horashumanos');");
    var_dump($c);
    if (!$c){
        echo "falha";
    }
    echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>Tempo criado com sucesso!
    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>";
}

$temposatuais = $db->exec("SELECT * FROM tempos;");
var_dump($temposatuais);
if (!$temposatuais) {
    echo "Não existem tempos criados.\n";
    
}
echo "<table class='table'><tr><th scope='col'>ID</th><th scope='col'>Horas Humanas</th></tr>";
while ($row = $temposatuais->fetchArray()) {
    $nome = utf8_encode($row[1]);
    $nomecompleto = utf8_encode($row[2]);
    echo "<tr><td>$row[0]</td><td>$nome</td><td>$nomecompleto</td><td>$row[3]</td><td><a href='/admin.php?action=gestao_cache&subaction=edit&user=$row[0]'>EDITAR</a>  <a href='/admin.php?action=gestao_cache&subaction=delete&user=$row[0]'>APAGAR</a></tr>";
}
$db->close();
echo "</div>"
?>

<?php require '../src/footer.php'; ?>