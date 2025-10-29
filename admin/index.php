<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - ClassLink</title>
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
    sidebarLink('/admin/', 'Dashboard');
    sidebarLink('/admin/pedidos.php', 'Pedidos de Reserva');
    sidebarLink('/admin/tempos.php', 'Gestão de Tempos');
    sidebarLink('/admin/salas.php', 'Gestão de Salas');
    sidebarLink('/admin/users.php', 'Gestão de Utilizadores');
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
        $nome_safe = htmlspecialchars($_SESSION['nome'], ENT_QUOTES, 'UTF-8');
        $pedidos_safe = htmlspecialchars($pedidospendentes, ENT_QUOTES, 'UTF-8');
        echo "<div class='flex-grow-1 d-flex align-items-center justify-content-center flex-column'>
        <h1>Olá, {$nome_safe}</h1>
        <p class='h4 fw-lighter'>O que vamos fazer hoje?</p>
        <p class='h6 fw-lighter'>Existem <b>{$pedidos_safe}</b> pedidos de reserva pendentes.</p>
        <div class='botoes_dashboardadmin d-flex justify-content-center'>
        <a href='/admin/pedidos.php' class='btn btn-success w-20 me-2'>Responder a pedidos</a>
        </div>";
    }

        // criação rápida de formulários
        function formulario($action, $inputs) {
            $action_safe = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
            echo "<form action='$action_safe' method='POST' class='d-flex align-items-center'>";
            foreach ($inputs as $input) {
                $id_safe = htmlspecialchars($input['id'], ENT_QUOTES, 'UTF-8');
                $value_safe = htmlspecialchars($input['value'], ENT_QUOTES, 'UTF-8');
                $label_safe = htmlspecialchars($input['label'], ENT_QUOTES, 'UTF-8');
                
                if ($input['type'] == "checkbox") {
                    echo "<div class='form-check me-2' style='flex: 1;'>
                        <input type='checkbox' class='form-check-input' id='$id_safe' name='$id_safe' value='$value_safe'>
                        <label class='form-check-label' for='$id_safe'>$label_safe</label>
                        </div>";
                } else {
                    $type_safe = htmlspecialchars($input['type'], ENT_QUOTES, 'UTF-8');
                    $placeholder_safe = htmlspecialchars($input['placeholder'], ENT_QUOTES, 'UTF-8');
                    echo "<div class='form-floating me-2' style='flex: 1;'>
                    <input type='$type_safe' class='form-control form-control-sm' id='$id_safe' name='$id_safe' placeholder='$placeholder_safe' value='$value_safe' required>
                    <label for='$id_safe'>$label_safe</label>
                    </div>";
                }
            }
            echo "<button type='submit' class='btn btn-primary btn-sm' style='height: 38px;'>Submeter</button></form>";
        }
    
        // ação executada
        function acaoexecutada($acao) {
            require_once(__DIR__ . '/../func/logaction.php');
            $acao_safe = htmlspecialchars($acao, ENT_QUOTES, 'UTF-8');
            echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>Ação executada. <b>$acao_safe</b>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Fechar'></button></div>";
            logaction($acao . ".\nPOST: " . var_export($_POST, true) . "\nGET: " . var_export($_GET, true), $_SESSION['id']);
        }    
?>

</body>
</html>