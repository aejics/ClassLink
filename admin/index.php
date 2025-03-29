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
    // Enquanto não for feito um backend, a variável (que é verificada contra a db)
    // fica a 1, para o ambiente de desenvolvimento.
    $isAdmin = 1;

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
    echo "</div><div class='d-flex' style='height:100vh;'>
    <div class='flex-shrink-0 p-3 text-bg-dark' style='width: 280px;'>
    <h1>Administração</h1>        
    <ul class='nav nav-pills flex-column mb-auto'>
    <li class='nav-item'>";
    // Links da Sidebar
    sidebarLink('/admin/', 'Dashboard');
    // Fechar Sidebar no HTML, e passar o conteúdo para a direita
    echo "</ul></div><div class='flex-grow-1 d-flex align-items-center justify-content-center flex-column'>";

?>

<?php 
    // Conteúdos para a Dashboard Administrativa. Apenas colocar o conteúdo neste bloco, pois
    // este módulo é reutilizado para as subpáginas.

    // Ainda não existe backend para verificar isto.

    echo "<div class='flex-grow-1 d-flex align-items-center justify-content-center flex-column'>
        <h1>Dashboard Administrativo</h1>
        <p class='h4 fw-lighter'>O que vamos fazer hoje?</p>
        <div class='botoes_dashboardadmin d-flex justify-content-center'>
        </div>";
?>

</body>
</html>