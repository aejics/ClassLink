<?php require 'index.php'; ?>
<div style="margin-left: 10%; margin-right: 10%; text-align: center;">
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

// Get total count for display
$totalResult = $db->query("SELECT COUNT(*) as total FROM cache");
$numUtilizadores = $totalResult->fetch_assoc()['total'];

// Get first 20 users
$utilizadores = $db->query("SELECT * FROM cache ORDER BY nome ASC LIMIT 20;");
?>

<div class="mb-3">
    <input type="text" class="form-control" id="userSearchInput" placeholder="Pesquisar por nome ou email..." style="max-width: 400px; margin: 0 auto;">
</div>

<p class="text-muted" id="userCountInfo">A mostrar <span id="userShownCount"><?php echo min(20, $numUtilizadores); ?></span> de <?php echo $numUtilizadores; ?> utilizadores</p>

<div id="userListContainer">
    <?php if ($numUtilizadores == 0): ?>
        <div class='alert alert-warning'>Não existem utilizadores.</div>
    <?php else: ?>
        <div class="row" id="userList">
            <?php while ($row = $utilizadores->fetch_assoc()): 
                $idEnc = urlencode($row['id']);
                $adminStatus = $row['admin'] ? "<span class='badge bg-success'>Admin</span>" : "<span class='badge bg-secondary'>Utilizador</span>";
                $userName = htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8');
                $userEmail = htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');
            ?>
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $userName; ?></h5>
                            <p class="card-text text-muted"><?php echo $userEmail; ?></p>
                            <p class="card-text"><?php echo $adminStatus; ?></p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href='/admin/users.php?action=edit&id=<?php echo $idEnc; ?>' class='btn btn-sm btn-primary'>EDITAR</a>
                            <a href='/admin/users.php?action=apagar&id=<?php echo $idEnc; ?>' class='btn btn-sm btn-danger' onclick='return confirm("Tem a certeza que pretende apagar o utilizador? Isto irá causar problemas se o utilizador tiver reservas passadas.");'>APAGAR</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <div id="loadMoreContainer" class="text-center mb-3" style="<?php echo $numUtilizadores > 20 ? '' : 'display: none;'; ?>">
            <button type="button" class="btn btn-outline-primary" id="loadMoreBtn">Carregar mais</button>
        </div>
    <?php endif; ?>
</div>

<script>
(function() {
    let currentOffset = 20;
    let currentSearch = '';
    let searchTimeout = null;
    const limit = 20;
    
    const searchInput = document.getElementById('userSearchInput');
    const userList = document.getElementById('userList');
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    const loadMoreContainer = document.getElementById('loadMoreContainer');
    const userCountInfo = document.getElementById('userCountInfo');
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function createUserCard(user) {
        const adminBadge = user.admin 
            ? "<span class='badge bg-success'>Admin</span>" 
            : "<span class='badge bg-secondary'>Utilizador</span>";
        const idEnc = encodeURIComponent(user.id);
        
        return `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">${escapeHtml(user.nome)}</h5>
                        <p class="card-text text-muted">${escapeHtml(user.email)}</p>
                        <p class="card-text">${adminBadge}</p>
                    </div>
                    <div class="card-footer bg-transparent">
                        <a href='/admin/users.php?action=edit&id=${idEnc}' class='btn btn-sm btn-primary'>EDITAR</a>
                        <a href='/admin/users.php?action=apagar&id=${idEnc}' class='btn btn-sm btn-danger' onclick='return confirm("Tem a certeza que pretende apagar o utilizador? Isto irá causar problemas se o utilizador tiver reservas passadas.");'>APAGAR</a>
                    </div>
                </div>
            </div>
        `;
    }
    
    function fetchUsers(search, offset, append) {
        const url = `/admin/users_api.php?action=search&search=${encodeURIComponent(search)}&offset=${offset}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }
                
                if (!append) {
                    userList.innerHTML = '';
                }
                
                if (data.users.length === 0 && !append) {
                    userList.innerHTML = "<div class='col-12'><div class='alert alert-warning'>Nenhum utilizador encontrado.</div></div>";
                    loadMoreContainer.style.display = 'none';
                } else {
                    data.users.forEach(user => {
                        userList.insertAdjacentHTML('beforeend', createUserCard(user));
                    });
                    
                    loadMoreContainer.style.display = data.hasMore ? '' : 'none';
                }
                
                const shownCount = offset + data.users.length;
                userCountInfo.textContent = `A mostrar ${shownCount} de ${data.total} utilizadores`;
                
                currentOffset = offset + data.users.length;
            })
            .catch(error => {
                console.error('Erro ao pesquisar utilizadores:', error);
            });
    }
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const search = this.value.trim();
            
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            
            searchTimeout = setTimeout(function() {
                currentSearch = search;
                currentOffset = 0;
                fetchUsers(search, 0, false);
            }, 300);
        });
    }
    
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            fetchUsers(currentSearch, currentOffset, true);
        });
    }
})();
</script>

<?php
$db->close();
?>
</div>
