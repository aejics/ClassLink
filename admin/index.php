<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - ReservaSalas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <link rel='icon' href='/assets/logo.png'>
</head>
<body>
<?php
    require_once(__DIR__ . '/../src/db.php');
    require_once(__DIR__ . '/../func/genuuid.php');
    session_start();
    if (!$_SESSION['admin']) {
        http_response_code(403);
        die("Não pode entrar no Painel Administrativo. <a href='/'>Voltar para a página inicial</a>");
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
    echo "<li class='nav-item dropdown'>
            <a class='nav-link dropdown-toggle' href='#' id='extensibilidadeDropdown' role='button' data-bs-toggle='dropdown' aria-expanded='false'>
                Extensibilidade
            </a>
            <ul class='dropdown-menu' aria-labelledby='extensibilidadeDropdown'>";
    foreach (glob(__DIR__ . "/scripts/*.php") as $scriptFile) {
        if (basename($scriptFile) !== "example.php") {
            $scriptName = basename($scriptFile, ".php");
            echo "<li>";
            sidebarLink("/admin/scripts/$scriptName.php", ucfirst($scriptName));
            echo "</li>";
        }
    }
    echo "</ul></li>";
    sidebarLink('/admin/', 'Dashboard');
    sidebarLink('/admin/pedidos.php', 'Pedidos de Reserva');
    sidebarLink('/admin/tempos.php', 'Gestão de Tempos');
    sidebarLink('/admin/salas.php', 'Gestão de Salas');
    sidebarLink('/', 'Voltar para a página principal');
    // Fechar Sidebar no HTML, e passar o conteúdo para a direita
    echo "</ul></div><div class='flex-grow-1 d-flex align-items-center justify-content-center flex-column'>";

?>

<?php
    if ($_SERVER['REQUEST_URI'] == "/admin") {
        header("Location: /admin/");
        die();
    }
    if ($_SERVER['REQUEST_URI'] == "/admin/") {
        // Conteúdos para a Dashboard Administrativa. Apenas colocar o conteúdo neste bloco, pois
        // este módulo é reutilizado para as subpáginas.
        $pedidospendentes = $db->query("SELECT * FROM reservas WHERE aprovado = 0;")->num_rows;
        echo "<div class='flex-grow-1 d-flex align-items-center justify-content-center flex-column'>
        <h1>Olá, {$_SESSION['nome']}</h1>
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
                if ($input['type'] == "checkbox") {
                    echo "<div class='form-check me-2' style='flex: 1;'>
                        <input type='checkbox' class='form-check-input' id='{$input['id']}' name='{$input['id']}' value='{$input['value']}'>
                        <label class='form-check-label' for='{$input['id']}'>{$input['label']}</label>
                        </div>";
                } else {
                echo "<div class='form-floating me-2' style='flex: 1;'>
                <input type='{$input['type']}' class='form-control form-control-sm' id='{$input['id']}' name='{$input['id']}' placeholder='{$input['placeholder']}' value='{$input['value']}' required>
                <label for='{$input['id']}'>{$input['label']}</label>
                </div>";
                }
            }
            echo "<button type='submit' class='btn btn-primary btn-sm' style='height: 38px;'>Submeter</button></form>";
        }
    
        // ação executada
        function acaoexecutada($acao) {
            require_once(__DIR__ . '/../func/logaction.php');
            echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>Ação executada. <b>$acao</b>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>";
            logaction($acao . ".\nPOST: " . var_export($_POST, true) . "\nGET: " . var_export($_GET, true) . "\nSERVER: " . var_export($_SERVER, true), $_COOKIE['token']);
        }    
?>

</body>
</html>