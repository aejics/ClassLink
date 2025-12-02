<?php require 'index.php'; ?>
<div style="margin-left: 10%; margin-right: 10%; text-align: center;">
<h3>Gestão das Salas</h3>
<div class="d-flex align-items-center justify-content-center mb-3">
    <span class="me-3">Adicionar uma sala</span>
    <?php formulario("salas.php?action=criar", [
        ["type" => "text", "id" => "nomesala", "placeholder" => "Sala", "label" => "Sala", "value" => null]
    ]); ?>
</div>

<?php
switch (isset($_GET['action']) ? $_GET['action'] : null){
    // caso seja preenchido o formulário de criação:
    case "criar":
        if (!isset($_POST['nomesala'])) {
            echo "<div class='alert alert-danger fade show' role='alert'>Dados inválidos.</div>";
            break;
        }
        $randomuuid = uuid4();
        $stmt = $db->prepare("INSERT INTO salas (id, nome) VALUES (?, ?)");
        $stmt->bind_param("ss", $randomuuid, $_POST["nomesala"]);
        $stmt->execute();
        $stmt->close();
        acaoexecutada("Criação de Sala");
        break;
    // caso execute a ação apagar:
    case "apagar":
        if (!isset($_GET['id'])) {
            echo "<div class='alert alert-danger fade show' role='alert'>ID inválido.</div>";
            break;
        }
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
        if (!isset($_GET['id'])) {
            echo "<div class='alert alert-danger fade show' role='alert'>ID inválido.</div>";
            break;
        }
        $stmt = $db->prepare("SELECT * FROM salas WHERE id = ?");
        $stmt->bind_param("s", $_GET['id']);
        $stmt->execute();
        $d = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$d) {
            echo "<div class='alert alert-danger fade show' role='alert'>Sala não encontrada.</div>";
            break;
        }
        echo "<div class='alert alert-warning fade show' role='alert'>A editar a Sala <b>" . htmlspecialchars($d['nome'], ENT_QUOTES, 'UTF-8') . "</b>.</div>";
        ?>
        <form action="salas.php?action=update&id=<?php echo urlencode($d['id']); ?>" method="POST" class="d-flex align-items-center">
            <div class="form-floating me-2" style="flex: 1;">
                <input type="text" class="form-control form-control-sm" id="nomesala" name="nomesala" placeholder="Sala" value="<?php echo htmlspecialchars($d['nome'], ENT_QUOTES, 'UTF-8'); ?>" required>
                <label for="nomesala">Sala</label>
            </div>
            <div class="form-floating me-2" style="flex: 1;">
                <select class="form-select form-select-sm" id="tipo_sala" name="tipo_sala" required>
                    <option value="1" <?php echo ($d['tipo_sala'] == 1 || !isset($d['tipo_sala'])) ? 'selected' : ''; ?>>Normal (Requer Aprovação)</option>
                    <option value="2" <?php echo ($d['tipo_sala'] == 2) ? 'selected' : ''; ?>>Reserva Autónoma</option>
                </select>
                <label for="tipo_sala">Tipo de Sala</label>
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="height: 38px;">Submeter</button>
        </form>
        <?php
        break;
    // caso seja submetida a edição:
    case "update":
        if (!isset($_GET['id']) || !isset($_POST['nomesala']) || !isset($_POST['tipo_sala'])) {
            echo "<div class='alert alert-danger fade show' role='alert'>Dados inválidos.</div>";
            break;
        }
        $tipo_sala = intval($_POST['tipo_sala']);
        if ($tipo_sala != 1 && $tipo_sala != 2) {
            echo "<div class='alert alert-danger fade show' role='alert'>Tipo de sala inválido.</div>";
            break;
        }
        $stmt = $db->prepare("UPDATE salas SET nome = ?, tipo_sala = ? WHERE id = ?");
        $stmt->bind_param("sis", $_POST['nomesala'], $tipo_sala, $_GET['id']);
        $stmt->execute();
        $stmt->close();
        acaoexecutada("Atualização de Sala");
        break;
}

$salasAtual = $db->query("SELECT * FROM salas ORDER BY nome ASC;");
$numSalas = $salasAtual->num_rows;
?>

<div class="mb-3">
    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#salasModal">
        Ver Salas (<?php echo $numSalas; ?>)
    </button>
</div>

<!-- Modal for Salas -->
<div class="modal fade" id="salasModal" tabindex="-1" aria-labelledby="salasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="salasModalLabel">Lista de Salas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <?php if ($numSalas == 0): ?>
                    <div class='alert alert-warning'>Não existem salas.</div>
                <?php else: ?>
                    <table class='table table-striped table-hover'>
                        <thead class='table-dark'>
                            <tr>
                                <th scope='col'>Sala</th>
                                <th scope='col'>Tipo de Sala</th>
                                <th scope='col'>AÇÕES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $salasAtual->fetch_assoc()): 
                                $idEnc = urlencode($row['id']);
                                $tipoSala = ($row['tipo_sala'] == 2) ? "<span class='badge bg-success'>Reserva Autónoma</span>" : "<span class='badge bg-primary'>Normal</span>";
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo $tipoSala; ?></td>
                                    <td>
                                        <a href='/admin/salas.php?action=edit&id=<?php echo $idEnc; ?>' class='btn btn-sm btn-primary'>EDITAR</a>
                                        <a href='/admin/salas.php?action=apagar&id=<?php echo $idEnc; ?>' class='btn btn-sm btn-danger' onclick='return confirm("Tem a certeza que pretende apagar a sala? Isto irá causar problemas se a sala tiver reservas passadas.");'>APAGAR</a>
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
</div>