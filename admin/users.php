<?php require 'index.php'; ?>
<?php require_once(__DIR__ . '/../func/genuuid.php'); ?>
<div style="margin-left: 10%; margin-right: 10%; text-align: center;">
<h3>Gestão de Utilizadores</h3>
<script>
    function filterUsers() {
        const searchInput = document.getElementById('userSearchInput');
        const filter = searchInput.value.toLowerCase();
        const tableRows = document.querySelectorAll('#userTableBody tr');
        
        tableRows.forEach(row => {
            const name = row.getAttribute('data-user-name').toLowerCase();
            const email = row.getAttribute('data-user-email').toLowerCase();
            if (name.includes(filter) || email.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
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
    // caso execute a ação pré-adicionar:
    case "preadd":
        if (!isset($_POST['nome']) || !isset($_POST['email']) || empty($_POST['nome']) || empty($_POST['email'])) {
            echo "<div class='alert alert-danger fade show' role='alert'>Nome e Email são obrigatórios.</div>";
            break;
        }
        // Validate email format
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            echo "<div class='alert alert-danger fade show' role='alert'>Formato de email inválido.</div>";
            break;
        }
        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM cache WHERE email = ?");
        $stmt->bind_param("s", $_POST['email']);
        $stmt->execute();
        $existingUser = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($existingUser) {
            echo "<div class='alert alert-danger fade show' role='alert'>Já existe um utilizador com este email.</div>";
            break;
        }
        // Generate a temporary ID with pre-registered prefix for pre-registered users
        $tempId = PRE_REGISTERED_PREFIX . uuid4();
        $stmt = $db->prepare("INSERT INTO cache (id, nome, email, admin) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("sss", $tempId, $_POST['nome'], $_POST['email']);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success fade show' role='alert'>Utilizador pré-adicionado com sucesso. Quando o utilizador iniciar sessão pela primeira vez, as reservas serão automaticamente associadas à conta.</div>";
            acaoexecutada("Pré-adição de Utilizador");
        } else {
            echo "<div class='alert alert-danger fade show' role='alert'>Erro ao pré-adicionar utilizador.</div>";
        }
        $stmt->close();
        break;
}

$utilizadores = $db->query("SELECT * FROM cache ORDER BY nome ASC;");
$numUtilizadores = $utilizadores->num_rows;
?>

<!-- Pre-add User Form -->
<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">Pré-adicionar Utilizador</h5>
    </div>
    <div class="card-body">
        <p class="text-muted small">Adicione utilizadores antes de eles iniciarem sessão. Quando o utilizador iniciar sessão pela primeira vez, as reservas associadas serão automaticamente transferidas.</p>
        <form action="users.php?action=preadd" method="POST" class="d-flex align-items-center flex-wrap gap-2">
            <div class="form-floating" style="flex: 1; min-width: 200px;">
                <input type="text" class="form-control" id="nome" name="nome" placeholder="Nome" required>
                <label for="nome">Nome</label>
            </div>
            <div class="form-floating" style="flex: 1; min-width: 200px;">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                <label for="email">Email</label>
            </div>
            <button type="submit" class="btn btn-success" style="height: 58px;">Pré-adicionar</button>
        </form>
    </div>
</div>

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
                    <div class="mb-3">
                        <input type="text" class="form-control" id="userSearchInput" placeholder="Pesquisar por nome ou email..." oninput="filterUsers()">
                    </div>
                    <table class='table table-striped table-hover'>
                        <thead class='table-dark'>
                            <tr>
                                <th scope='col'>Nome</th>
                                <th scope='col'>Email</th>
                                <th scope='col'>Estado</th>
                                <th scope='col'>Admin</th>
                                <th scope='col'>AÇÕES</th>
                            </tr>
                        </thead>
                        <tbody id='userTableBody'>
                            <?php while ($row = $utilizadores->fetch_assoc()): 
                                $idEnc = urlencode($row['id']);
                                $adminStatus = $row['admin'] ? "Sim" : "Não";
                                $userName = htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8');
                                $userEmail = htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');
                                $isPreRegistered = str_starts_with($row['id'], PRE_REGISTERED_PREFIX);
                            ?>
                                <tr data-user-name="<?php echo $userName; ?>" data-user-email="<?php echo $userEmail; ?>">
                                    <td><?php echo $userName; ?></td>
                                    <td><?php echo $userEmail; ?></td>
                                    <td>
                                        <?php if ($isPreRegistered): ?>
                                            <span class="badge bg-warning text-dark">Pré-registado</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Ativo</span>
                                        <?php endif; ?>
                                    </td>
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
</div>
