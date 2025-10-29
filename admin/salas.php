<?php require 'index.php'; ?>
<h3>Gestão das Salas</h3>
<div class="d-flex align-items-center mb-3">
    <span class="me-3">Adicionar uma sala</span>
    <?php formulario("salas.php?action=criar", [
        ["type" => "text", "id" => "nomesala", "placeholder" => "Sala", "label" => "Sala", "value" => null]
    ]); ?>
</div>

<?php
switch ($_GET['action']){
    // caso seja preenchido o formulário de criação:
    case "criar":
        $randomuuid = uuid4();
        $stmt = $db->prepare("INSERT INTO salas (id, nome) VALUES (?, ?)");
        $stmt->bind_param("ss", $randomuuid, $_POST["nomesala"]);
        $stmt->execute();
        $stmt->close();
        acaoexecutada("Criação de Sala");
        break;
    // caso execute a ação apagar:
    case "apagar":
        try {
            $stmt = $db->prepare("SELECT * FROM reservas WHERE sala = ? AND aprovado != -1");
            $stmt->bind_param("s", $_GET['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                throw new Exception("Existem reservas associadas a esta sala. Por segurança, é necessária uma intervenção manual.");
            }
            $stmt->close();
        } catch (Exception $e) {
            echo "<div class='alert alert-danger fade show' role='alert'>Erro: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
            break;
        }
        $stmt = $db->prepare("DELETE FROM salas WHERE id = ?");
        $stmt->bind_param("s", $_GET['id']);
        $stmt->execute();
        $stmt->close();
        acaoexecutada("Eliminação de Sala");
        break;
    // caso execute a ação editar:
    case "edit":
        $stmt = $db->prepare("SELECT * FROM salas WHERE id = ?");
        $stmt->bind_param("s", $_GET['id']);
        $stmt->execute();
        $d = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        echo "<div class='alert alert-warning fade show' role='alert'>A editar a Sala <b>" . htmlspecialchars($d['nome'], ENT_QUOTES, 'UTF-8') . "</b>.</div>";
        formulario("salas.php?action=update&id=" . urlencode($d['id']), [
            ["type" => "text", "id" => "nomesala", "placeholder" => "Sala", "label" => "Sala", "value" => $d['nome']]]);
        break;
    // caso seja submetida a edição:
    case "update":
        $stmt = $db->prepare("UPDATE salas SET nome = ? WHERE id = ?");
        $stmt->bind_param("ss", $_POST['nomesala'], $_GET['id']);
        $stmt->execute();
        $stmt->close();
        acaoexecutada("Atualização de Sala");
        break;
}

$temposatuais = $db->query("SELECT * FROM salas ORDER BY nome ASC;");
if ($temposatuais->num_rows == 0) {
    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Não existem salas.</div>\n";
}
echo "<div style='max-height: 400px; overflow-y: auto; width: 90%;'>";
echo "<table class='table'><tr><th scope='col'>Sala</th><th scope='col'>AÇÕES</th></tr>";
while ($row = $temposatuais->fetch_assoc()) {
    $idEnc = urlencode($row['id']);
    echo "<tr><td>" . htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8') . "</td><td><a href='/admin/salas.php?action=edit&id={$idEnc}'>EDITAR</a>  <a href='/admin/salas.php?action=apagar&id={$idEnc}' onclick='return confirm(\"Tem a certeza que pretende apagar a sala? Isto irá causar problemas se a sala tiver reservas passadas.\");'>APAGAR</a></tr>";
}
echo "</table>";
$db->close();
echo "</div></table>"
?>