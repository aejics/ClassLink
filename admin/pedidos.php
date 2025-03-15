<?php 
    require 'index.php';
?>
<div class="h-100 d-flex align-items-center justify-content-center flex-column">
    <h3>Gestão de Pedidos de Salas</h3>
<form action="/admin/pedidos.php" method="POST" class="d-flex align-items-center">
    <div class="form-floating me-2">
        <select class="form-select" id="sala" name="sala" required onchange="this.form.submit();">
            <?php if ($_POST['sala'] == "0" | !$_POST['sala']){
            echo "<option value='0' selected disabled>Escolha uma sala</option>";
            } else {
                echo "<option value='0' disabled>Escolha uma sala</option>";
            }
            $salas = $db->query("SELECT * FROM salas;");
            while ($sala = $salas->fetch_assoc()){
                if ($_POST['sala'] == $sala['id'] || $_GET['sala'] == $sala['id']){
                    echo "<option value='{$sala['id']}' selected>{$sala['nome']}</option>";
                } else {
                    echo "<option value='{$sala['id']}'>{$sala['nome']}</option>";
                }
            }
            ?>
        </select>
        <label for="sala" class="form-label">Escolha uma sala</label>
    </div>
    <div class="form-floating me-2">
        <input type="text" class="form-control form-control-sm" id="requisitor" name="requisitor" placeholder="Requisitor" value="">
        <label for="requisitor">Requisitor</label>
    </div>
</form>

<?php
    if ($_GET['subaction']) {
        switch ($_GET['subaction']){
            case "aprovar":
                $db->query("UPDATE reservas SET aprovado=1 WHERE sala='{$_GET['sala']}' AND tempo='{$_GET['tempo']}' AND data='{$_GET['data']}';");
                echo "<div class='mt-2 alert alert-success fade show' role='alert'>Reserva aprovada com sucesso.</div>";
                echo "<a href='/admin/pedidos.php'><button class='btn btn-primary'>Voltar</button></a>";
                break;
            case "rejeitar":
                $db->query("DELETE FROM reservas WHERE sala='{$sala}' AND tempo='{$tempo}' AND data='{$data}';");
                header("Location: /reservas/?sala={$sala}");
                break;
            case "detalhes":
                
        }
    } elseif ($_POST['sala'] | $_GET['sala']){
        echo "<div style='max-height: 400px; overflow-y: auto; width: 100%;'>";
        echo "<table class='table'><tr><th scope='col'>Data</th><th scope='col'>Tempo</th><th scope='col'>Requisitor</th><th scope='col'>Motivo</th><th scope='col'>AÇÕES</th></tr>";
        if ($_POST['sala']){
            $sala = $_POST['sala'];
        } else {
            $sala = $_GET['sala'];
        }
        if ($_POST['requisitor']){ 
            $pedidos = $db->query("SELECT * FROM reservas WHERE aprovado=0 AND sala={$sala} AND requisitor='{$_POST['requisitor']}' ORDER BY data ASC;");
            while ($pedido = $pedidos->fetch_assoc()){
                $salaextenso = $db->query("SELECT nome FROM salas WHERE id='{$pedido['sala']}';")->fetch_assoc()['nome'];
                $requisitorextenso = $db->query("SELECT nomecompleto FROM cache_giae WHERE id='{$pedido['requisitor']}';")->fetch_assoc()['nomecompleto'];
                $horastempo = $db->query("SELECT horashumanos FROM tempos WHERE id='{$pedido['tempo']}';")->fetch_assoc()['horashumanos'];
                echo "<tr><td>{$pedido['data']}</td>
                <td>{$horastempo}</td>
                <td>{$requisitorextenso}</td>
                <td>{$pedido['motivo']}</td>
                <td><a href='/reservas/manage.php?subaction=reservar&tempo={$pedido['tempo']}&data={$pedido['data']}&sala={$pedido['sala']}' class='btn btn-success'>Aprovar</a> <a href='/reservas/manage.php?subaction=delete&tempo={$pedido['tempo']}&data={$pedido['data']}&sala={$pedido['sala']}' class='btn btn-danger'>Rejeitar</a></td></tr>";
            }    
        } else {
            $pedidos = $db->query("SELECT * FROM reservas WHERE aprovado=0 AND sala={$sala};");
            while ($pedido = $pedidos->fetch_assoc()){
                $salaextenso = $db->query("SELECT nome FROM salas WHERE id='{$pedido['sala']}';")->fetch_assoc()['nome'];
                $requisitorextenso = $db->query("SELECT nomecompleto FROM cache_giae WHERE id='{$pedido['requisitor']}';")->fetch_assoc()['nomecompleto'];
                $horastempo = $db->query("SELECT horashumanos FROM tempos WHERE id='{$pedido['tempo']}';")->fetch_assoc()['horashumanos'];
                echo "<tr><td>{$pedido['data']}</td>
                <td>{$horastempo}</td>
                <td>{$requisitorextenso}</td>
                <td>{$pedido['motivo']}</td>
                <td><a href='/admin/pedidos.php?subaction=aprovar&tempo={$pedido['tempo']}&data={$pedido['data']}&sala={$pedido['sala']}' class='btn btn-success'>Aprovar</a>
                 <a href='/admin/pedidos.php?subaction=rejeitar&tempo={$pedido['tempo']}&data={$pedido['data']}&sala={$pedido['sala']}' class='btn btn-danger'>Rejeitar</a>
                 <a href='/reservas/manage.php?&tempo={$pedido['tempo']}&data={$pedido['data']}&sala={$pedido['sala']}' class='btn btn-secondary'>Detalhes</a></td></tr>";
            }
        }
        echo "</table>";
        echo "</div>";
    }
?>