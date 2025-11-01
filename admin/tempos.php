<?php require 'index.php'; ?>
<h3>Gestão de Tempos</h3>
<div class="d-flex align-items-center mb-3">
    <span class="me-3">Adicionar um tempo</span>
    <?php formulario("tempos.php?action=criar", [
        ["type" => "text", "id" => "horahumana", "placeholder" => "Horas (08:05-08:55)", "label" => "Horas (08:05-08:55)", "value" => null]
    ]); ?>
</div>

<?php
switch (isset($_GET['action']) ? $_GET['action'] : null){
    // caso seja preenchido o formulário de criação:
    case "criar":
        if (!isset($_POST['horahumana'])) {
            echo "<div class='alert alert-danger fade show' role='alert'>Dados inválidos.</div>";
            break;
        }
        $randomuuid = uuid4();
        $stmt = $db->prepare("INSERT INTO tempos (id, horashumanos) VALUES (?, ?)");
        $stmt->bind_param("ss", $randomuuid, $_POST["horahumana"]);
        $stmt->execute();
        $stmt->close();
        acaoexecutada("Criação de Tempo");
        break;
    // caso execute a ação apagar:
    case "apagar":
        if (!isset($_GET['id'])) {
            echo "<div class='alert alert-danger fade show' role='alert'>ID inválido.</div>";
            break;
        }
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
        if (!isset($_GET['id'])) {
            echo "<div class='alert alert-danger fade show' role='alert'>ID inválido.</div>";
            break;
        }
        $stmt = $db->prepare("SELECT * FROM tempos WHERE id = ?");
        $stmt->bind_param("s", $_GET['id']);
        $stmt->execute();
        $d = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$d) {
            echo "<div class='alert alert-danger fade show' role='alert'>Tempo não encontrado.</div>";
            break;
        }
        echo "<div class='alert alert-warning fade show' role='alert'>A editar o Tempo <b>" . htmlspecialchars($d['horashumanos'], ENT_QUOTES, 'UTF-8') . "</b>.</div>";
        formulario("tempos.php?action=update&id=" . urlencode($d['id']), [
            ["type" => "text", "id" => "horahumana", "placeholder" => "Horas (08:05-08:55)", "label" => "Horas (08:05-08:55)", "value" => $d['horashumanos']]]);
        break;
    // caso seja submetida a edição:
    case "update":
        if (!isset($_GET['id']) || !isset($_POST['horahumana'])) {
            echo "<div class='alert alert-danger fade show' role='alert'>Dados inválidos.</div>";
            break;
        }
        $stmt = $db->prepare("UPDATE tempos SET horashumanos = ? WHERE id = ?");
        $stmt->bind_param("ss", $_POST['horahumana'], $_GET['id']);
        $stmt->execute();
        $stmt->close();
        acaoexecutada("Atualização de Tempo");
        break;
}

$temposatuais = $db->query("SELECT * FROM tempos ORDER BY horashumanos ASC;");
$numTempos = $temposatuais->num_rows;
?>

<div class="mb-3">
    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#temposModal">
        Ver Tempos (<?php echo $numTempos; ?>)
    </button>
</div>

<!-- Modal for Tempos -->
<div class="modal fade" id="temposModal" tabindex="-1" aria-labelledby="temposModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="temposModalLabel">Lista de Tempos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <?php if ($numTempos == 0): ?>
                    <div class='alert alert-warning'>Não existem tempos.</div>
                <?php else: ?>
                    <table class='table table-striped table-hover'>
                        <thead class='table-dark'>
                            <tr>
                                <th scope='col'>Hora Humana</th>
                                <th scope='col'>AÇÕES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $temposatuais->fetch_assoc()): 
                                $idEnc = urlencode($row['id']);
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['horashumanos'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <a href='/admin/tempos.php?action=edit&id=<?php echo $idEnc; ?>' class='btn btn-sm btn-primary'>EDITAR</a>
                                        <a href='/admin/tempos.php?action=apagar&id=<?php echo $idEnc; ?>' class='btn btn-sm btn-danger' onclick='return confirm("Tem a certeza que pretende apagar o tempo? Isto irá causar problemas se a sala tiver reservas passadas.");'>APAGAR</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<?php
$db->close();
?>