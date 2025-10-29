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
        $stmt = $db->prepare("INSERT INTO cache (id, nome, email, admin) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $_POST["userid"], $_POST["nome"], $_POST["email"], $adminValue);
        $stmt->execute();
        $stmt->close();
        acaoexecutada("Criação de Utilizador");
        break;
    // caso execute a ação apagar:
    case "apagar":
        try {
            $stmt = $db->prepare("SELECT * FROM reservas WHERE requisitor = ? AND aprovado != -1");
            $stmt->bind_param("s", $_GET['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                throw new Exception("Existem reservas associadas a este utilizador. Por segurança, é necessária uma intervenção manual.");
            }
            $stmt->close();
        } catch (Exception $e) {
            echo "<div class='alert alert-danger fade show' role='alert'>Erro: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
            break;
        }
        $stmt = $db->prepare("DELETE FROM cache WHERE id = ?");
        $stmt->bind_param("s", $_GET['id']);
        $stmt->execute();
        $stmt->close();
        acaoexecutada("Eliminação de Utilizador");
        break;
    // caso execute a ação editar:
    case "edit":
        $stmt = $db->prepare("SELECT * FROM cache WHERE id = ?");
        $stmt->bind_param("s", $_GET['id']);
        $stmt->execute();
        $d = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        echo "<div class='alert alert-warning fade show' role='alert'>A editar o Utilizador <b>" . htmlspecialchars($d['nome'], ENT_QUOTES, 'UTF-8') . "</b>.</div>";
        formulario("users.php?action=update&id=" . urlencode($d['id']), [
            ["type" => "text", "id" => "nome", "placeholder" => "Nome", "label" => "Nome", "value" => $d['nome']],
            ["type" => "email", "id" => "email", "placeholder" => "Email", "label" => "Email", "value" => $d['email']],
            ["type" => "checkbox", "id" => "administrador", "placeholder" => "Admin", "label" => "Administrador", "value" => $d['admin'] ? "1" : "0"]
        ]);
        break;
    // caso seja submetida a edição:
    case "update":
        $adminValue = isset($_POST["administrador"]) ? 1 : 0;
        $stmt = $db->prepare("UPDATE cache SET nome = ?, email = ?, admin = ? WHERE id = ?");
        $stmt->bind_param("ssis", $_POST['nome'], $_POST['email'], $adminValue, $_GET['id']);
        $stmt->execute();
        $stmt->close();
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
    $idEnc = urlencode($row['id']);
    echo "<tr><td>" . htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8') . "</td><td>" . htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8') . "</td><td>" . htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') . "</td><td>" . htmlspecialchars($adminStatus, ENT_QUOTES, 'UTF-8') . "</td><td><a href='/admin/users.php?action=edit&id={$idEnc}'>EDITAR</a>  <a href='/admin/users.php?action=apagar&id={$idEnc}' onclick='return confirm(\"Tem a certeza que pretende apagar o utilizador? Isto irá causar problemas se o utilizador tiver reservas passadas.\");'>APAGAR</a></tr>";
}
echo "</table>";
echo "</div>";
$db->close();
?>
