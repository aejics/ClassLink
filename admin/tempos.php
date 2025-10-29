<?php require 'index.php'; ?>
<h3>Gestão de Tempos</h3>
<div class="d-flex align-items-center mb-3">
    <span class="me-3">Adicionar um tempo</span>
    <?php formulario("tempos.php?action=criar", [
        ["type" => "text", "id" => "horahumana", "placeholder" => "Horas (08:05-08:55)", "label" => "Horas (08:05-08:55)", "value" => null]
    ]); ?>
</div>

<?php
switch ($_GET['action']){
    // caso seja preenchido o formulário de criação:
    case "criar":
        $randomuuid = uuid4();
        $stmt = $db->prepare("INSERT INTO tempos (id, horashumanos) VALUES (?, ?)");
        $stmt->bind_param("ss", $randomuuid, $_POST["horahumana"]);
        $stmt->execute();
        $stmt->close();
        acaoexecutada("Criação de Tempo");
        break;
    // caso execute a ação apagar:
    case "apagar":
        try {
            $stmt = $db->prepare("SELECT * FROM reservas WHERE tempo = ? AND aprovado != -1");
            $stmt->bind_param("s", $_GET['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                throw new Exception("Existem reservas associadas a este tempo. Por segurança, é necessária uma intervenção manual.");
            }
            $stmt->close();
        } catch (Exception $e) {
            echo "<div class='alert alert-danger fade show' role='alert'>Erro: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
            break;
        }
        $stmt = $db->prepare("DELETE FROM tempos WHERE id = ?");
        $stmt->bind_param("s", $_GET['id']);
        $stmt->execute();
        $stmt->close();
        acaoexecutada("Eliminação de Tempo");
        break;
    // caso execute a ação editar:
    case "edit":
        $stmt = $db->prepare("SELECT * FROM tempos WHERE id = ?");
        $stmt->bind_param("s", $_GET['id']);
        $stmt->execute();
        $d = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        echo "<div class='alert alert-warning fade show' role='alert'>A editar o Tempo <b>" . htmlspecialchars($d['horashumanos'], ENT_QUOTES, 'UTF-8') . "</b>.</div>";
        formulario("tempos.php?action=update&id=" . urlencode($d['id']), [
            ["type" => "text", "id" => "horahumana", "placeholder" => "Horas (08:05-08:55)", "label" => "Horas (08:05-08:55)", "value" => $d['horashumanos']]]);
        break;
    // caso seja submetida a edição:
    case "update":
        $stmt = $db->prepare("UPDATE tempos SET horashumanos = ? WHERE id = ?");
        $stmt->bind_param("ss", $_POST['horahumana'], $_GET['id']);
        $stmt->execute();
        $stmt->close();
        acaoexecutada("Atualização de Tempo");
        break;
}

$temposatuais = $db->query("SELECT * FROM tempos ORDER BY horashumanos ASC;");
if ($temposatuais->num_rows == 0) {
    echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Não existem tempos.</div>\n";
}
echo "<div style='max-height: 400px; overflow-y: auto; width: 90%;'>";
echo "<table class='table'><tr><th scope='col'>Hora Humana</th><th scope='col'>AÇÕES</th></tr>";
while ($row = $temposatuais->fetch_assoc()) {
    $idEnc = urlencode($row['id']);
    echo "<tr><td>" . htmlspecialchars($row['horashumanos'], ENT_QUOTES, 'UTF-8') . "</td><td><a href='/admin/tempos.php?action=edit&id={$idEnc}'>EDITAR</a>  <a href='/admin/tempos.php?action=apagar&id={$idEnc}' onclick='return confirm(\"Tem a certeza que pretende apagar o tempo? Isto irá causar problemas se a sala tiver reservas passadas.\");'>APAGAR</a></tr>";
}
echo "</div></table>"
?>