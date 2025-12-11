<?php
// Function to check if the current user is banned
// If banned, destroys session and redirects to login with a ban message
function check_user_ban($db) {
    if (!isset($_SESSION['id'])) {
        return;
    }
    
    $stmt = $db->prepare("SELECT banned FROM cache WHERE id = ?");
    $stmt->bind_param("s", $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if ($user && $user['banned']) {
        session_destroy();
        http_response_code(403);
        
        // Display ban notification page
        echo "<!DOCTYPE html>";
        echo "<html lang=\"pt\">";
        echo "<head>";
        echo "<meta charset=\"UTF-8\">";
        echo "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">";
        echo "<title>Acesso Negado - ClassLink</title>";
        echo "<link rel=\"stylesheet\" href=\"/assets/theme.css\">";
        echo "<style>";
        echo "body {";
        echo "margin: 0;";
        echo "height: 100vh;";
        echo "font-family: \"Segoe UI\", sans-serif;";
        echo "background: url(\"/assets/aejicsbg.jpeg\") no-repeat center center/cover;";
        echo "display: flex;";
        echo "justify-content: center;";
        echo "align-items: center;";
        echo "flex-direction: column;";
        echo "color: var(--text-color);";
        echo "}";
        echo ".ban-box {";
        echo "background: var(--white-overlay);";
        echo "padding: 2rem 3rem;";
        echo "border-radius: 16px;";
        echo "box-shadow: 0 4px 20px var(--shadow-color);";
        echo "text-align: center;";
        echo "max-width: 450px;";
        echo "width: 100%;";
        echo "}";
        echo ".ban-box h1 {";
        echo "font-size: 1.4rem;";
        echo "margin-bottom: 1.5rem;";
        echo "color: #dc3545;";
        echo "}";
        echo ".ban-box p {";
        echo "color: var(--text-color);";
        echo "margin-bottom: 1.5rem;";
        echo "}";
        echo ".login-btn {";
        echo "display: inline-block;";
        echo "background-color: #2F2F2F;";
        echo "color: white;";
        echo "text-decoration: none;";
        echo "padding: 0.8rem 1.2rem;";
        echo "border-radius: 8px;";
        echo "font-size: 1rem;";
        echo "font-weight: 500;";
        echo "transition: background 0.2s;";
        echo "}";
        echo ".login-btn:hover {";
        echo "background-color: #1b1b1b;";
        echo "}";
        echo "</style>";
        echo "</head>";
        echo "<body>";
        echo "<div class=\"ban-box\">";
        echo "<img src=\"/assets/logo.png\" alt=\"Logotipo ClassLink\" style=\"max-width:25%;\">";
        echo "<h1>⛔ Acesso Negado</h1>";
        echo "<p>A sua conta foi suspensa e não pode aceder ao ClassLink neste momento.</p>";
        echo "<p>Por favor, contacte a administração para mais informações ou tente iniciar sessão mais tarde.</p>";
        echo "<a href=\"/login\" class=\"login-btn\">Tentar Novamente</a>";
        echo "</div>";
        echo "</body>";
        echo "</html>";
        exit();
    }
}
?>
