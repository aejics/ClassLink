<?php require 'index.php'; ?>
<h3>Gestão de Utilizadores</h3>
<div class="d-flex align-items-center mb-3">
    <span class="me-3">Adicionar um utilizador</span>
    <?php formulario("users.php?action=criar", [
        ["type" => "text", "id" => "userid", "placeholder" => "ID do Utilizador", "label" => "ID do Utilizador", "value" => null],
        ["type" => "text", "id" => "nome", "placeholder" => "Nome", "label" => "Nome", "value" => null],
        ["type" => "email", "id" => "email", "placeholder" => "Email", "label" => "Email", "value" => null],
        ["type" => "checkbox", "id" => "admin", "placeholder" => "Admin", "label" => "Administrador", "value" => "1"]
    ]); ?>
</div>

<?php
switch ($_GET['action']){
    // caso seja preenchido o formulário de criação:
    case "criar":
        $adminValue = isset($_POST["admin"]) ? 1 : 0;
        $db->query("INSERT INTO cache (id, nome, email, admin) VALUES ('{$_POST["userid"]}', '{$_POST["nome"]}', '{$_POST["email"]}', {$adminValue});");
        acaoexecutada("Criação de Utilizador");
        break;
    // caso execute a ação apagar:
    case "apagar":
        try {
            $db->query("SELECT * FROM reservas WHERE requisitor = '{$_GET['id']}' AND aprovado != -1;");
            if ($db->affected_rows > 0) {
                throw new Exception("Existem reservas associadas a este utilizador. Por segurança, é necessária uma intervenção manual.");
            }
        } catch (Exception $e) {
            echo "<div class='alert alert-danger fade show' role='alert'>Erro: {$e->getMessage()}</div>";
            break;
        }
        $db->query("DELETE FROM cache WHERE id = '{$_GET['id']}';");
        acaoexecutada("Eliminação de Utilizador");
        break;
    // caso execute a ação editar:
    case "edit":
        $c = $db->query("SELECT * FROM cache WHERE id = '{$_GET['id']}';");
        $d = $c->fetch_assoc();
        echo "<div class='alert alert-warning fade show' role='alert'>A editar o Utilizador <b>{$d['nome']}</b>.</div>";
        formulario("users.php?action=update&id={$d['id']}", [
            ["type" => "text", "id" => "nome", "placeholder" => "Nome", "label" => "Nome", "value" => $d['nome']],
            ["type" => "email", "id" => "email", "placeholder" => "Email", "label" => "Email", "value" => $d['email']],
            ["type" => "checkbox", "id" => "admin", "placeholder" => "Admin", "label" => "Administrador", "value" => $d['admin'] ? "1" : "0"]
        ]);
        break;
    // caso seja submetida a edição:
    case "update":
        $adminValue = isset($_POST["admin"]) ? 1 : 0;
        $db->query("UPDATE cache SET nome = '{$_POST['nome']}', email = '{$_POST['email']}', admin = {$adminValue} WHERE id = '{$_GET['id']}';");
        acaoexecutada("Atualização de Utilizador");
        break;
}

$utilizadores = $db->query("SELECT * FROM cache ORDER BY nome ASC;");
if ($utilizadores->num_rows == 0) {
    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Não existem utilizadores.</div>\n";
}
echo "<div style='max-height: 400px; overflow-y: auto; width: 90%;'>";
echo "<table class='table'><tr><th scope='col'>ID</th><th scope='col'>Nome</th><th scope='col'>Email</th><th scope='col'>Admin</th><th scope='col'>AÇÕES</th></tr>";
while ($row = $utilizadores->fetch_assoc()) {
    $adminStatus = $row['admin'] ? "Sim" : "Não";
    echo "<tr><td>{$row['id']}</td><td>{$row['nome']}</td><td>{$row['email']}</td><td>{$adminStatus}</td><td><a href='/admin/users.php?action=edit&id={$row['id']}'>EDITAR</a>  <a href='/admin/users.php?action=apagar&id={$row['id']}' onclick='return confirm(\"Tem a certeza que pretende apagar o utilizador? Isto irá causar problemas se o utilizador tiver reservas passadas.\");'>APAGAR</a></tr>";
}
echo "</table>";
$db->close();
echo "</div></table>"
?>
