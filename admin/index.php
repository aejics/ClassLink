<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - ReservaSalas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>
<body>
<?php
    require "../login/index.php";
    $dados = $db->query("SELECT * FROM cache_giae WHERE email = '{$perfil['perfil']['email']}';")->fetch_assoc();
    $isAdmin = $db->query("SELECT permitido FROM admins WHERE id = '{$dados['id']}'")->fetch_assoc()['permitido'];
    if (!$isAdmin == 1) {
        http_response_code(403);
        die("<div class='text-center mt-2'><h2>403 - Não tem acesso para aceder a esta página.</h2></div>");
    }
?>
<?php
    // Criação da Sidebar (reaproveito do módulo para as subpáginas)
    // Links do Sidebar
    function sidebarLink($url, $nome) {
        if ($url == "/admin/" && $_SERVER['REQUEST_URI'] == "/admin/") {
            echo "<li class='nav-item'><a href='$url' class='nav-link active'>$nome</a></li>";
        } else if (str_starts_with($_SERVER['REQUEST_URI'], $url) && $url != "/admin/" && $url != "/") {
            echo "<li class='nav-item'><a href='$url' class='nav-link active'>$nome</a></li>";
        } else {
            echo "<li class='nav-item'><a href='$url' class='nav-link'>$nome</a></li>";
        }
    }

    // Criação da Sidebar no HTML
    echo "</div><div class='d-flex' style='height:100vh;'>
    <div class='flex-shrink-0 p-3 text-bg-dark' style='width: 280px;'>
    <h1>Administração</h1>        
    <ul class='nav nav-pills flex-column mb-auto text-center justify-content-center align-items-center' style='height: 100%;'>
    <li class='nav-item'>";
    // Links da Sidebar
    sidebarLink('/admin/', 'Dashboard');
    sidebarLink('/admin/pedidos.php', 'Pedidos de Reserva');
    sidebarLink('/admin/tempos.php', 'Gestão de Tempos');
    sidebarLink('/admin/salas.php', 'Gestão de Salas');
    sidebarLink('/admin/admins.php', 'Gestão de Administradores');
    sidebarLink('/', 'Voltar para a página principal');
    // Fechar Sidebar no HTML, e passar o conteúdo para a direita
    echo "</ul></div><div class='flex-grow-1 d-flex align-items-center justify-content-center flex-column'>";

?>

<?php
    if ($_SERVER['REQUEST_URI'] == "/admin/") {
        // Conteúdos para a Dashboard Administrativa. Apenas colocar o conteúdo neste bloco, pois
        // este módulo é reutilizado para as subpáginas.
        $pedidospendentes = $db->query("SELECT * FROM reservas WHERE aprovado = 0;")->num_rows;
        echo "<div class='flex-grow-1 d-flex align-items-center justify-content-center flex-column'>
        <h1>Olá, {$dados['nome']}</h1>
        <p class='h4 fw-lighter'>O que vamos fazer hoje?</p>
        <p class='h6 fw-lighter'>Existem <b>$pedidospendentes</b> pedidos de reserva pendentes.</p>
        <div class='botoes_dashboardadmin d-flex justify-content-center'>
        <a href='/admin/pedidos.php' class='btn btn-success w-20 me-2'>Responder a pedidos</a>
        <a href='/admin/semanasrepetidas.php' class='btn btn-danger w-20 me-2'>Criar reservas de várias semanas no horário</a>
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

</body>
</html>