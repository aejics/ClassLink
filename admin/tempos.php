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
        $db->query("INSERT INTO tempos (id, horashumanos) VALUES ('{$randomuuid}', '{$_POST["horahumana"]}');");
        acaoexecutada("Criação de Tempo");
        break;
    // caso execute a ação apagar:
    case "apagar":
        try {
            $db->query("SELECT * FROM reservas WHERE tempo = '{$_GET['id']}' AND aprovado != -1;");
            if ($db->affected_rows > 0) {
                throw new Exception("Existem reservas associadas a este tempo. Por segurança, é necessária uma intervenção manual.");
            }
        } catch (Exception $e) {
            echo "<div class='alert alert-danger fade show' role='alert'>Erro: {$e->getMessage()}</div>";
            break;
        }
        $db->query("DELETE FROM tempos WHERE id = '{$_GET['id']}';");
        acaoexecutada("Eliminação de Tempo");
        break;
    // caso execute a ação editar:
    case "edit":
        $c = $db->query("SELECT * FROM tempos WHERE id = '{$_GET['id']}';");
        $d = $c->fetch_assoc();
        echo "<div class='alert alert-warning fade show' role='alert'>A editar o Tempo <b>{$d['horashumanos']}</b>.</div>";
        formulario("tempos.php?action=update&id={$d['id']}", [
            ["type" => "text", "id" => "horahumana", "placeholder" => "Horas (08:05-08:55)", "label" => "Horas (08:05-08:55)", "value" => $d['horashumanos']]]);
        break;
    // caso seja submetida a edição:
    case "update":
        $db->query("UPDATE tempos SET horashumanos = '{$_POST['horahumana']}' WHERE id = '{$_GET['id']}';");
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
    echo "<tr><td>{$row['horashumanos']}</td><td><a href='/admin/tempos.php?action=edit&id={$row['id']}'>EDITAR</a>  <a href='/admin/tempos.php?action=apagar&id={$row['id']}' onclick='return confirm(\"Tem a certeza que pretende apagar o tempo? Isto irá causar problemas se a sala tiver reservas passadas.\");'>APAGAR</a></tr>";
}
echo "</div></table>"
?>