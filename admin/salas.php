<?php require 'index.php'; ?>
<h3>Gestão das Salas</h3>
<div class="d-flex align-items-center mb-3">
    <span class="me-3">Adicionar uma sala</span>
    <?php formulario("salas.php?action=criar", [
        ["type" => "number", "id" => "idsala", "placeholder" => "ID da Sala", "label" => "ID da Sala", "value" => null],
        ["type" => "text", "id" => "nomesala", "placeholder" => "Sala", "label" => "Sala", "value" => null]
    ]); ?>
</div>

<?php
switch ($_GET['action']){
    // caso seja preenchido o formulário de criação:
    case "criar":
        $db->query("INSERT INTO salas (id, nome) VALUES ('{$_POST["idsala"]}', '{$_POST["nomesala"]}');");
        acaoexecutada("Criação de Sala");
        break;
    // caso execute a ação apagar:
    case "apagar":
        $db->query("DELETE FROM salas WHERE id = {$_GET['id']};");
        acaoexecutada("Eliminação de Sala");
        break;
    // caso execute a ação editar:
    case "edit":
        $c = $db->query("SELECT * FROM salas WHERE id = '{$_GET['id']}';");
        $d = $c->fetch_assoc();
        echo "<div class='alert alert-warning fade show' role='alert'>A editar a Sala ID {$d['id']} (<b>{$d['nome']}</b>).</div>";
        formulario("salas.php?action=update&id={$d['id']}", [
            ["type" => "number", "id" => "idsala", "placeholder" => "ID da Sala", "label" => "ID da Sala", "value" => $d['id']],
            ["type" => "text", "id" => "nomesala", "placeholder" => "Sala", "label" => "Sala", "value" => $d['nome']]]);
        break;
    // caso seja submetida a edição:
    case "update":
        $c = $db->query("UPDATE salas SET id = '{$_POST['idsala']}', nome = '{$_POST['nomesala']}' WHERE id = {$_GET['id']};");
        acaoexecutada("Atualização de Sala");
        break;
}

$temposatuais = $db->query("SELECT * FROM salas;");
if ($temposatuais->num_rows == 0) {
    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Não existem salas.</div>\n";
}
echo "<table class='table'><tr><th scope='col'>ID</th><th scope='col'>Nome Sala</th><th scope='col'>AÇÕES</th></tr>";
while ($row = $temposatuais->fetch_assoc()) {
    echo "<tr><td>{$row['id']}</td><td>{$row['nome']}</td><td><a href='/admin/salas.php?action=edit&id={$row['id']}'>EDITAR</a>  <a href='/admin/salas.php?action=apagar&id={$row['id']}'>APAGAR</a></tr>";
}
$db->close();
echo "</div></table>"
?>