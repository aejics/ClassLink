<?php require 'index.php'; ?>
<script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
<h3>Gestão de Páginas Pós-Reserva</h3>
<div class="d-flex align-items-center mb-3">
    <span class="me-3">Selecione uma sala para editar a página pós-reserva</span>
</div>

<?php
switch (isset($_GET['action']) ? $_GET['action'] : null){
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
        echo "<div class='alert alert-warning fade show' role='alert'>A editar a Página Pós-Reserva da Sala <b>" . htmlspecialchars($d['nome'], ENT_QUOTES, 'UTF-8') . "</b>.</div>";
        ?>
        <form action="salas_postreserva.php?action=update&id=<?php echo urlencode($d['id']); ?>" method="POST" style="width: 90%;">
            <div class="mb-3">
                <label for="nomesala" class="form-label">Sala</label>
                <input type="text" class="form-control" id="nomesala" value="<?php echo htmlspecialchars($d['nome'], ENT_QUOTES, 'UTF-8'); ?>" disabled>
            </div>
            <div class="mb-3">
                <label for="post_reservation_content" class="form-label">Conteúdo da Página Pós-Reserva</label>
                <textarea id="post_reservation_content" name="post_reservation_content" class="form-control" rows="10"><?php echo htmlspecialchars($d['post_reservation_content'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
            <a href="salas_postreserva.php" class="btn btn-secondary">Voltar</a>
        </form>
        <script>
            ClassicEditor
                .create(document.querySelector('#post_reservation_content'), {
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|', 'blockQuote', 'insertTable', 'undo', 'redo']
                })
                .catch(error => {
                    console.error(error);
                });
        </script>
        <?php
        break;
    // caso seja submetida a edição:
    case "update":
        if (!isset($_GET['id']) || !isset($_POST['post_reservation_content'])) {
            echo "<div class='alert alert-danger fade show' role='alert'>Dados inválidos.</div>";
            break;
        }
        $stmt = $db->prepare("UPDATE salas SET post_reservation_content = ? WHERE id = ?");
        $stmt->bind_param("ss", $_POST['post_reservation_content'], $_GET['id']);
        $stmt->execute();
        $stmt->close();
        acaoexecutada("Atualização de Página Pós-Reserva");
        echo "<a href='salas_postreserva.php' class='btn btn-primary mt-2'>Voltar</a>";
        break;
    default:
        // Listar todas as salas
        $salas = $db->query("SELECT * FROM salas ORDER BY nome ASC;");
        if ($salas->num_rows == 0) {
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>Não existem salas.</div>\n";
        } else {
            echo "<div style='max-height: 400px; overflow-y: auto; width: 90%;'>";
            echo "<table class='table'><tr><th scope='col'>Sala</th><th scope='col'>Estado do Conteúdo</th><th scope='col'>AÇÕES</th></tr>";
            while ($row = $salas->fetch_assoc()) {
                $idEnc = urlencode($row['id']);
                $hasContent = !empty($row['post_reservation_content']) ? "<span class='badge bg-success'>Configurado</span>" : "<span class='badge bg-secondary'>Vazio</span>";
                echo "<tr><td>" . htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8') . "</td><td>$hasContent</td><td><a href='/admin/salas_postreserva.php?action=edit&id={$idEnc}' class='btn btn-sm btn-primary'>EDITAR</a></td></tr>";
            }
            echo "</table></div>";
        }
        break;
}
$db->close();
?>
