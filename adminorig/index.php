<?php
    require '../src/base.php';
    require '../src/config.php';
    require '../src/db.php';
    if (!$isAdmin == 1) {
        http_response_code(403);
        die("<h2>403 - Não tem acesso para aceder a esta página.</h2>");
    }
?>
<?php
    // Criação da Sidebar (reaproveito do módulo para as subpáginas)
    // Links do Sidebar
    function sidebarLink($url, $nome) {
        if ($url == "/admin/" && $_SERVER['REQUEST_URI'] == "/admin/") {
            echo "<li class='nav-item'><a href='$url' class='nav-link active'>$nome</a></li>";
        } else if (str_starts_with($_SERVER['REQUEST_URI'], $url) && $url != "/admin/") {
            echo "<li class='nav-item'><a href='$url' class='nav-link active'>$nome</a></li>";
        } else {
            echo "<li class='nav-item'><a href='$url' class='nav-link'>$nome</a></li>";
        }
    }

    // Criação da Sidebar no HTML
    echo "</div><div class='d-flex' style='height: 100vh;'>
    <div class='flex-shrink-0 p-3 text-bg-dark' style='width: 280px;'>
    <h1>Administração</h1>        
    <ul class='nav nav-pills flex-column mb-auto'>
    <li class='nav-item'>";
    // Links da Sidebar
    sidebarLink('/admin/', 'Dashboard');
    sidebarLink('/admin/pedidos.php', 'Pedidos de Reserva');
    sidebarLink('/admin/tempos.php', 'Gestão de Tempos');
    sidebarLink('/admin/salas.php', 'Gestão de Salas');
    sidebarLink('/admin/admins.php', 'Gestão de Administradores');
    // Fechar Sidebar no HTML, e passar o conteúdo para a direita
    echo "</ul></div><div class='flex-grow-1 d-flex align-items-center justify-content-center flex-column'>";

?>

<?php 
    // Conteúdos para a Dashboard Administrativa. Apenas colocar o conteúdo neste bloco, pois
    // este módulo é reutilizado para as subpáginas.
    if($_SERVER['REQUEST_URI'] == "/admin/") {
        $pedidospendentes = $db->query("SELECT * FROM reservas WHERE aprovado = 0;")->num_rows;
        echo "<div class='flex-grow-1 d-flex align-items-center justify-content-center flex-column'>
            <h1>Dashboard Administrativo</h1>
            <p class='h4 fw-lighter'>O que vamos fazer hoje?</p>
            <p class='h6 fw-lighter'>Existem <b>$pedidospendentes</b> pedidos de reserva pendentes.</p>
            <div class='botoes_dashboardadmin d-flex justify-content-center'>
            <a href='/admin/pedidos.php' class='btn btn-success w-20 me-2'>Responder a pedidos</a>
            <a href='/admin/admins.php' class='btn btn-success w-20 me-2'>Gerir os administradores da aplicação</a>
            <a href='/admin/semanasrepetidas.php' class='btn btn-danger w-20 me-2'>Criar reservas em grande escala no horário</a>
            </div>";
    }

    // criação rápida de formulários
    function formulario($action, $inputs) {
        echo "<form action='$action' method='POST' class='d-flex align-items-center'>";
        foreach ($inputs as $input) {
            echo "<div class='form-floating me-2' style='flex: 1;'>
            <input type='{$input['type']}' class='form-control form-control-sm' id='{$input['id']}' name='{$input['id']}' placeholder='{$input['placeholder']}' value='{$input['value']}' required>
            <label for='{$input['id']}'>{$input['label']}</label>
            </div>";
            }
        echo "<button type='submit' class='btn btn-primary btn-sm' style='height: 38px;'>Submeter</button></form>";
    }

    // ação executada
    function acaoexecutada($acao) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>Ação executada. <b>$acao</b>
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>";
    }
?>