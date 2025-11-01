<?php require '../index.php'; ?>
<h3>Logs Administrativos</h3>
<?php
$sql = "SELECT * FROM logs ORDER BY timestamp DESC";
$result = $db->query($sql);
$numLogs = $result ? $result->num_rows : 0;
?>

<div class="mb-3">
    <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#logsModal">
        Ver Logs (<?php echo $numLogs; ?>)
    </button>
</div>

<!-- Modal for Logs -->
<div class="modal fade" id="logsModal" tabindex="-1" aria-labelledby="logsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logsModalLabel">Logs Administrativos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <?php if ($numLogs == 0): ?>
                    <div class='alert alert-warning'>Não existem logs.</div>
                <?php else: ?>
                    <table class='table table-striped table-hover table-sm'>
                        <thead class='table-dark'>
                            <tr>
                                <th scope='col'>User</th>
                                <th scope='col'>Action</th>
                                <th scope='col'>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['userid'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($row['loginfo'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($row['timestamp'], ENT_QUOTES, 'UTF-8'); ?></td>
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
