<?php // verificar login
       require '../login.php'; 
    // verificar acesso administrativo
    $admin = 1;
    // ^^ temporariamente dar como 1, pois ainda não foi criada uma gestão administrativa apropriada para o sql
    if ($admin != 1){
        http_response_code(403);
        die("403 - Não tem acesso para aceder a esta página.");
    }
?>
<?php
    // ini_set('display_startup_errors', 1);
    // ini_set('display_errors', 1);
    // error_reporting(E_ALL);


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
    echo "</div><div class='d-flex' style='height: 85vh;'>
    <div class='flex-shrink-0 p-3 text-bg-dark' style='width: 280px;'>
    <h1>Administração</h1>        
    <ul class='nav nav-pills flex-column mb-auto'>
    <li class='nav-item'>";
    // Links da Sidebar
    sidebarLink('/admin/', 'Dashboard');
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
        echo "<div class='flex-grow-1 d-flex align-items-center justify-content-center flex-column'>
            <h1>Dashboard Administrativo</h1>
            <p>Conteúdos TBA</p>";
        require '../src/footer.php';
    }

?>