<?php require 'index.php'; ?>
<h3>Gestão das Salas</h3>
<div class="d-flex align-items-center mb-3">
    <span class="me-3">Adicionar uma sala</span>
    <form action="salas.php?action=criar" method="POST" class="d-flex align-items-center">
        <div class="form-floating me-2" style="flex: 1;">
            <input type="number" class="form-control form-control-sm" id="idsala" name="idsala" placeholder="ID da Sala (interno)" required>
            <label for="idsala">ID da Sala (interno)</label>
        </div>
        <div class="form-floating me-2" style="flex: 1;">
            <input type="text" class="form-control form-control-sm" id="nomesala" name="nomesala" placeholder="Sala" required>
            <label for="nomesala">Sala</label>
        </div>
        <div class="form-floating me-2" style="flex: 1;">
            <input type="text" class="form-control form-control-sm" id="localsala" name="localsala" placeholder="Local" required>
            <label for="localsala">Local</label>
        </div>
        <div class="form-check me-2" style="flex: 1;">
            <input type="checkbox" class="form-check-input" id="ativar" name="ativar">
            <label class="form-check-label" for="ativar">Mostrar</label>
        </div>
        <button type="submit" class="btn btn-primary btn-sm" style="height: 38px;">Submeter</button>
    </form>
</div>

<?php
$db = new SQLite3('../db.sqlite3'); 
// Criação da Tabela das Salas.
$db->exec("CREATE TABLE salas (idsala INTEGER UNIQUE, nomesala VARCHAR, localsala VARCHAR, ativada BOOLEAN, PRIMARY KEY (idsala));");
// Ações caso seja executada uma ação
switch ($_GET['action']){
    // caso seja preenchido o formulário de criação:
    case "criar":
        $idsala = filter_input(INPUT_POST, 'idsala', FILTER_SANITIZE_NUMBER_INT);
        $nomesala = filter_input(INPUT_POST, 'nomesala', FILTER_SANITIZE_STRING);
        $localsala = filter_input(INPUT_POST, 'localsala', FILTER_SANITIZE_STRING);
        $ativar = filter_input(INPUT_POST, 'ativar', FILTER_SANITIZE_NUMBER_INT);
        $c = $db->exec("INSERT INTO salas (idsala, nomesala, localsala, ativada) VALUES ('$idsala', '$nomesala', '$localsala', '$ativar');");
        if (!$c){
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Falha na criação da sala!
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>";
        }
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>Sala criada com sucesso!
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>";
        break;
    // caso execute a ação apagar:
    case "apagar":
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $c = $db->exec("DELETE FROM salas WHERE idsala = $id;");
        if (!$c){
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Falha na eliminação da sala!
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>";
        }
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>Sala eliminada com sucesso!
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>";
        break;
    // caso execute a ação editar:
    case "edit":
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $c = $db->query("SELECT * FROM salas WHERE idsala = $id;");
        if (!$c){
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Falha na obtenção da sala!
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>";
        }
        $row = $c->fetchArray();
        echo "<div class='alert alert-warning fade show' role='alert'>A editar a Sala ID $row[0] (<b>$row[1]</b>).</div>";
        echo "<form action='salas.php?action=update&id=$row[0]' method='POST' class='d-flex align-items-center'>
        <div class='form-floating me-2' style='flex: 1;'>
            <input type='number' class='form-control form-control-sm' id='idsala' name='idsala' placeholder='ID da Sala (interno) value='$row[0]' required>
            <label for='idsala'>ID da Sala (interno)</label>
        </div>
        <div class='form-floating me-2' style='flex: 1;'>
            <input type='text' class='form-control form-control-sm' id='nomesala' name='nomesala' placeholder='Sala' value='$row[1]' required>
            <label for='nomesala'>Sala</label>
        </div>
        <div class='form-floating me-2' style='flex: 1;'>
            <input type='text' class='form-control form-control-sm' id='localsala' name='localsala' placeholder='Local' value='$row[2]' required>
            <label for='localsala'>Local</label>
        </div>
        <div class='form-floating me-2' style='flex: 1;'>
            <input type='checkbox' class='form-check-input' id='ativar' name='ativar' value='$row[3]'>
            <label class='form-check-label' for='ativar'>Mostrar</label>
        </div>
        <button type='submit' class='btn btn-primary btn-sm' style='height: 38px;'>Submeter</button>
        </form>";
        break;
    // caso seja submetida a edição:
    case "update":
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        $idsala = filter_input(INPUT_POST, 'idsala', FILTER_SANITIZE_NUMBER_INT);
        $nomesala = filter_input(INPUT_POST, 'nomesala', FILTER_SANITIZE_STRING);
        $localsala = filter_input(INPUT_POST, 'localsala', FILTER_SANITIZE_STRING);
        $ativar = filter_input(INPUT_POST, 'ativar', FILTER_SANITIZE_NUMBER_INT);
        $c = $db->exec("UPDATE salas SET idsala = '$idsala', nomesala = '$nomesala', localsala = '$localsala', ativada = '$ativar' WHERE idsala = $id;");
        if (!$c){
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Falha na atualização da Sala!
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>";
        }
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>Sala atualizada com sucesso!
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>";
        break;
}

$temposatuais = $db->query("SELECT * FROM salas;");

// esta variável é definida para poder mostrar se não existem tempos,
// para corrigir um bug caso sejam criados e apagados tempos
$numerosalas = $db->querySingle("SELECT COUNT(*) as numerotempos FROM salas");

if (!$numerosalas || $numerosalas == 0) {
    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Não existem salas.</div>\n";
}
echo "<table class='table'><tr><th scope='col'>ID</th><th scope='col'>Nome Sala</th><th scope='col'>Local</th><th scope='col'>Ativada</th><th scope='col'>AÇÕES</th></tr>";
while ($row = $temposatuais->fetchArray()) {
    // definir para valores legíveis, na db fica 0 ou 1
    if ($row[3] == 1) {
        $ativada = "Sim";
    } else {
        $ativada = "Não";
    }
    echo "<tr><td>$row[0]</td><td>$row[1]</td><td>$row[2]</td><td>$ativada</td><td><a href='/admin/salas.php?action=edit&id=$row[0]'>EDITAR</a>  <a href='/admin/salas.php?action=apagar&id=$row[0]'>APAGAR</a></tr>";
}
$db->close();
echo "</div></table>"
?>

<?php require '../src/footer.php'; ?>