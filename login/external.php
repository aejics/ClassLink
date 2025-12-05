<?php
/**
 * External Email Login Handler
 * 
 * This file handles the external email login flow:
 * 1. Receives email submission, validates it's not @aejics.org
 * 2. Generates a 6-digit code and sends it via email
 * 3. Shows the code verification page
 * 4. Verifies the code and creates a session
 */

require_once(__DIR__ . '/../src/config.php');
require_once(__DIR__ . '/../src/db.php');
require_once(__DIR__ . '/../func/csrf.php');
require_once(__DIR__ . '/../func/genuuid.php');
require_once(__DIR__ . '/../func/email_helper.php');
require_once(__DIR__ . '/../func/session_config.php');

session_start();

// Rate limiting constants
define('MAX_CODE_REQUESTS_PER_HOUR', 5);
define('CODE_EXPIRY_MINUTES', 10);

/**
 * Generate a cryptographically secure 6-digit code
 */
function generateVerificationCode() {
    return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Check if email is from the blocked domain
 */
function isBlockedDomain($email) {
    $email = strtolower(trim($email));
    return str_ends_with($email, '@aejics.org');
}

/**
 * Check rate limiting for email
 */
function checkRateLimit($db, $email) {
    $oneHourAgo = date('Y-m-d H:i:s', strtotime('-1 hour'));
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM email_verification_codes WHERE email = ? AND created_at > ?");
    $stmt->bind_param("ss", $email, $oneHourAgo);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['count'] < MAX_CODE_REQUESTS_PER_HOUR;
}

/**
 * Clean up expired codes
 */
function cleanupExpiredCodes($db) {
    $db->query("DELETE FROM email_verification_codes WHERE expires_at < NOW() OR used = TRUE");
}

/**
 * Render error page and redirect
 */
function redirectWithError($message) {
    header('Location: /login/?external_error=' . urlencode($message));
    exit();
}

/**
 * Render the verification code input page
 */
function renderVerificationPage($email, $error = null) {
    $escapedEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
    $csrfField = csrf_token_field();
    $errorHtml = $error ? '<p class="error-message">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</p>' : '';
    
    echo <<<HTML
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código - ClassLink</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            font-family: "Segoe UI", sans-serif;
            background: url("/assets/aejicsbg.jpeg") no-repeat center center/cover;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: #333;
        }
        
        .login-box {
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem 3rem;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 350px;
            width: 100%;
        }
        
        .login-box h1 {
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }
        
        .email-display {
            background-color: #f8f9fa;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #495057;
        }
        
        .code-input {
            width: 100%;
            padding: 1rem;
            font-size: 1.5rem;
            text-align: center;
            letter-spacing: 8px;
            border: 2px solid #ccc;
            border-radius: 8px;
            margin-bottom: 1rem;
            box-sizing: border-box;
        }
        
        .code-input:focus {
            outline: none;
            border-color: #0056b3;
        }
        
        .login-btn {
            display: inline-block;
            background-color: #0056b3;
            color: white;
            text-decoration: none;
            padding: 0.8rem 1.2rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            transition: background 0.2s;
            border: none;
            cursor: pointer;
            width: 100%;
            box-sizing: border-box;
        }
        
        .login-btn:hover {
            background-color: #004494;
        }
        
        .back-link {
            display: block;
            margin-top: 1rem;
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .back-link:hover {
            color: #0056b3;
        }
        
        .error-message {
            color: #dc3545;
            font-size: 0.85rem;
            margin-bottom: 1rem;
        }
        
        .info-text {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 1rem;
        }
        
        .notice {
            position: absolute;
            bottom: 20px;
            background: rgba(255, 255, 255, 0.8);
            padding: 1rem 1.5rem;
            border-radius: 10px;
            font-size: 0.9rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            line-height: 1.4;
        }
        
        .notice strong {
            display: block;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <img src="/assets/logo.png" alt="Logotipo ClassLink" style="max-width:25%;">
        <h1>Verificar Código</h1>
        <p class="info-text">Enviámos um código de 6 dígitos para:</p>
        <div class="email-display">{$escapedEmail}</div>
        {$errorHtml}
        <form method="POST" action="/login/external.php">
            {$csrfField}
            <input type="hidden" name="action" value="verify">
            <input type="hidden" name="email" value="{$escapedEmail}">
            <input type="text" name="code" class="code-input" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autofocus>
            <button type="submit" class="login-btn">Verificar</button>
        </form>
        <a href="/login" class="back-link">← Voltar ao login</a>
    </div>
    
    <div class="notice">
        <strong>Este website é oficial do Agrupamento de Escolas Joaquim Inácio da Cruz Sobral.</strong>
    </div>
</body>
</html>
HTML;
    exit();
}

// Clean up expired codes periodically
cleanupExpiredCodes($db);

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        redirectWithError('Token de segurança inválido. Por favor, tente novamente.');
    }
    
    $action = $_POST['action'] ?? 'send_code';
    
    if ($action === 'send_code' || $action === '') {
        // Step 1: Receive email and send verification code
        $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            redirectWithError('Por favor, introduza um email válido.');
        }
        
        // Block @aejics.org users
        if (isBlockedDomain($email)) {
            redirectWithError('Utilizadores @aejics.org devem usar o login Microsoft.');
        }
        
        // Check rate limiting
        if (!checkRateLimit($db, $email)) {
            redirectWithError('Demasiados pedidos. Por favor, aguarde antes de tentar novamente.');
        }
        
        // Generate verification code
        $code = generateVerificationCode();
        $id = uuid4();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . CODE_EXPIRY_MINUTES . ' minutes'));
        
        // Store the code in the database
        $stmt = $db->prepare("INSERT INTO email_verification_codes (id, email, code, expires_at) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $id, $email, $code, $expiresAt);
        
        if (!$stmt->execute()) {
            $stmt->close();
            redirectWithError('Erro ao processar o pedido. Por favor, tente novamente.');
        }
        $stmt->close();
        
        // Send the verification email
        $emailResult = sendExternalLoginVerificationEmail($email, $code);
        
        if (!$emailResult['success']) {
            // Delete the code if email failed
            $stmt = $db->prepare("DELETE FROM email_verification_codes WHERE id = ?");
            $stmt->bind_param("s", $id);
            $stmt->execute();
            $stmt->close();
            redirectWithError('Erro ao enviar o email. Por favor, tente novamente.');
        }
        
        // Store email in session for verification step
        $_SESSION['external_login_email'] = $email;
        
        // Show verification page
        renderVerificationPage($email);
        
    } else if ($action === 'verify') {
        // Step 2: Verify the code
        $email = isset($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
        $code = isset($_POST['code']) ? trim($_POST['code']) : '';
        
        // Validate inputs
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            redirectWithError('Email inválido.');
        }
        
        if (!preg_match('/^[0-9]{6}$/', $code)) {
            renderVerificationPage($email, 'Por favor, introduza um código de 6 dígitos.');
        }
        
        // Verify the session email matches
        if (!isset($_SESSION['external_login_email']) || $_SESSION['external_login_email'] !== $email) {
            redirectWithError('Sessão inválida. Por favor, tente novamente.');
        }
        
        // Look up the code
        $stmt = $db->prepare("SELECT id, code, expires_at, used FROM email_verification_codes WHERE email = ? AND used = FALSE ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$result) {
            renderVerificationPage($email, 'Código não encontrado ou expirado. Por favor, solicite um novo código.');
        }
        
        // Check if code has expired
        if (strtotime($result['expires_at']) < time()) {
            renderVerificationPage($email, 'O código expirou. Por favor, solicite um novo código.');
        }
        
        // Verify the code using timing-safe comparison
        if (!hash_equals($result['code'], $code)) {
            renderVerificationPage($email, 'Código incorreto. Por favor, tente novamente.');
        }
        
        // Mark the code as used
        $stmt = $db->prepare("UPDATE email_verification_codes SET used = TRUE WHERE id = ?");
        $stmt->bind_param("s", $result['id']);
        $stmt->execute();
        $stmt->close();
        
        // Create or get the external user
        $externalId = EXTERNAL_USER_PREFIX . hash('sha256', $email);
        
        // Check if user already exists
        $stmt = $db->prepare("SELECT id, nome FROM cache WHERE id = ?");
        $stmt->bind_param("s", $externalId);
        $stmt->execute();
        $existingUser = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$existingUser) {
            // Create new external user - extract name from email
            $emailParts = explode('@', $email);
            $nome = ucwords(str_replace(['.', '_', '-'], ' ', $emailParts[0]));
            
            $stmt = $db->prepare("INSERT INTO cache (id, nome, email, admin) VALUES (?, ?, ?, FALSE)");
            $stmt->bind_param("sss", $externalId, $nome, $email);
            $stmt->execute();
            $stmt->close();
        } else {
            $nome = $existingUser['nome'];
        }
        
        // Set up the session
        $_SESSION['validity'] = time() + 3600; // 1 hour validity
        $_SESSION['id'] = $externalId;
        $_SESSION['nome'] = $nome;
        $_SESSION['email'] = $email;
        $_SESSION['admin'] = false; // External users are never admins
        $_SESSION['external_user'] = true; // Mark as external user
        
        // Clean up
        unset($_SESSION['external_login_email']);
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Redirect to home
        header('Location: /');
        exit();
    }
} else {
    // GET request - redirect to login page
    header('Location: /login');
    exit();
}
?>
