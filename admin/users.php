<?php require 'index.php'; ?>
<h3>Gestão de Utilizadores</h3>
<?php
switch (isset($_GET['action']) ? $_GET['action'] : null){
    // caso execute a ação apagar:
    case "apagar":
        if (!isset($_GET['id'])) {
            echo "<div class='alert alert-danger fade show' role='alert'>ID inválido.</div>";
            break;
        }
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
        if (!isset($_GET['id'])) {
            echo "<div class='alert alert-danger fade show' role='alert'>ID inválido.</div>";
            break;
        }
        $stmt = $db->prepare("SELECT * FROM cache WHERE id = ?");
        $stmt->bind_param("s", $_GET['id']);
        $stmt->execute();
        $d = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$d) {
            echo "<div class='alert alert-danger fade show' role='alert'>Utilizador não encontrado.</div>";
            break;
        }
        echo "<div class='alert alert-warning fade show' role='alert'>A editar o Utilizador <b>" . htmlspecialchars($d['nome'], ENT_QUOTES, 'UTF-8') . "</b>.</div>";
        formulario("users.php?action=update&id=" . urlencode($d['id']), [
            ["type" => "text", "id" => "nome", "placeholder" => "Nome", "label" => "Nome", "value" => $d['nome']],
            ["type" => "email", "id" => "email", "placeholder" => "Email", "label" => "Email", "value" => $d['email']],
            ["type" => "checkbox", "id" => "administrador", "placeholder" => "Admin", "label" => "Administrador", "value" => $d['admin'] ? "1" : "0"]
        ]);
        break;
    // caso seja submetida a edição:
    case "update":
        if (!isset($_GET['id']) || !isset($_POST['nome']) || !isset($_POST['email'])) {
            echo "<div class='alert alert-danger fade show' role='alert'>Dados inválidos.</div>";
            break;
        }
        $adminValue = isset($_POST["administrador"]) ? 1 : 0;
        $stmt = $db->prepare("UPDATE cache SET nome = ?, email = ?, admin = ? WHERE id = ?");
        $stmt->bind_param("ssis", $_POST['nome'], $_POST['email'], $adminValue, $_GET['id']);
        $stmt->execute();
        $stmt->close();
        acaoexecutada("Atualização de Utilizador");
        break;
}

$utilizadores = $db->query("SELECT * FROM cache ORDER BY nome ASC;");
$numUtilizadores = $utilizadores->num_rows;
?>

<div class="mb-3">
    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#utilizadoresModal">
        Ver Utilizadores (<?php echo $numUtilizadores; ?>)
    </button>
</div>

<!-- Modal for Utilizadores -->
<div class="modal fade" id="utilizadoresModal" tabindex="-1" aria-labelledby="utilizadoresModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="utilizadoresModalLabel">Lista de Utilizadores</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <?php if ($numUtilizadores == 0): ?>
                    <div class='alert alert-warning'>Não existem utilizadores.</div>
                <?php else: ?>
                    <table class='table table-striped table-hover'>
                        <thead class='table-dark'>
                            <tr>
                                <th scope='col'>Nome</th>
                                <th scope='col'>Email</th>
                                <th scope='col'>Admin</th>
                                <th scope='col'>AÇÕES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $utilizadores->fetch_assoc()): 
                                $adminStatus = $row['admin'] ? "Sim" : "Não";
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($adminStatus, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td>
                                        <a href='/admin/users.php?action=edit&id=<?php echo $idEnc; ?>' class='btn btn-sm btn-primary'>EDITAR</a>
                                        <a href='/admin/users.php?action=apagar&id=<?php echo $idEnc; ?>' class='btn btn-sm btn-danger' onclick='return confirm("Tem a certeza que pretende apagar o utilizador? Isto irá causar problemas se o utilizador tiver reservas passadas.");'>APAGAR</a>
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
