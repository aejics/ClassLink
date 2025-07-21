<?php require 'index.php'; ?>
<h3>Gestão de Administradores</h3>
<div class="d-flex align-items-center mb-3">
    <span class="me-3">Adicionar um admin</span>
    <?php formulario("admins.php?action=criar", [
        ["type" => "text", "id" => "id", "placeholder" => "ID do Admin", "label" => "ID do Admin", "value" => null],
        ["type" => "checkbox", "id" => "permitido", "placeholder" => "Permitido", "label" => "Permitido", "value" => "1"]
    ]); ?>
</div>

<?php
switch ($_GET['action']){
    // caso seja preenchido o formulário de criação:
    case "criar":
        $db->query("INSERT INTO admins (id, permitido) VALUES ('{$_POST["id"]}', '{$_POST["permitido"]}');");
        acaoexecutada("Criação de Administrador na DB de Administradores");
        break;
    // caso execute a ação apagar:
    case "apagar":
        $db->query("DELETE FROM admins WHERE id = '{$_GET['id']}';");
        acaoexecutada("Eliminação de Administrador na DB de Administradores");
        break;
    // caso execute a ação editar:
    case "edit":
        $c = $db->query("SELECT * FROM admins WHERE id = '{$_GET['id']}';");
        $d = $c->fetch_assoc();
        echo "<div class='alert alert-warning fade show' role='alert'>A editar o Administrador {$d['id']}.</div>";
        formulario("admins.php?action=update&id={$d['id']}", [
            ["type" => "text", "id" => "id", "placeholder" => "ID do Admin", "label" => "ID do Admin", "value" => $d['id']],
            ["type" => "checkbox", "id" => "permitido", "placeholder" => "Permitido", "label" => "Permitido", "value" => "1"]]);
            break;
    // caso seja submetida a edição:
    case "update":
        $db->query("UPDATE admins SET id = '{$_POST['id']}', permitido = '{$_POST['permitido']}' WHERE id = '{$_GET['id']}';");
        acaoexecutada("Atualização de perfil de Administrador na DB de Administradores");
        break;
}

$temposatuais = $db->query("SELECT * FROM admins;");
if ($temposatuais->num_rows == 0) {
    echo "Está a ocorrer um bug no Painel. Por favor corra o ficheiro src/db.php para configurar as DBs.";
}
echo "<table class='table'><tr><th scope='col'>ID</th><th scope='col'>Nome</th><th scope='col'>Permitido</th><th scope='col'>AÇÕES</th></tr>";
while ($row = $temposatuais->fetch_assoc()) {
    if ($row['permitido'] == 1) {
        $row['permitido'] = "Sim";
    } else {
        $row['permitido'] = "Não";
    }
    $nome = $db->query("SELECT nome FROM cache_giae WHERE id = '{$row['id']}';")->fetch_assoc()['nome'];
    if ($nome == null) {
        $nome = "Desconhecido";
    }
    echo "<tr><td>{$row['id']}</td><td>{$nome}</td><td>{$row['permitido']}</td><td><a href='/admin/admins.php?action=edit&id={$row['id']}'>EDITAR</a>  <a href='/admin/admins.php?action=apagar&id={$row['id']}'>APAGAR</a></tr>";
}
$db->close();
echo "</div></table>"
?>