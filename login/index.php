<?php
    require_once(__DIR__ . '/../src/config.php');
    require_once(__DIR__ . '/../src/db.php');
    
    session_start();
    if ($_GET['action'] == "logout"){
        session_destroy();
        header("Location: " . $logoutUrlProvider);
    } else if ($_GET['error']) {
	?>
	<!DOCTYPE html>
            <html lang="pt">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Erro - ClassLink</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        text-align: center;
                        padding: 50px;
                        background-color: #f5f5f5;
                    }
                    .error-container {
                        max-width: 600px;
                        margin: 0 auto;
                        background: white;
                        padding: 40px;
                        border-radius: 10px;
                        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    }
                    .error-image {
                        width: 100px;
                        height: 100px;
                        margin-bottom: 20px;
                    }
                    .error-message {
                        font-size: 18px;
                        color: #333;
                        margin-bottom: 30px;
                    }
                    .debug-dropdown {
                        margin-top: 20px;
                    }
                    details {
                        text-align: left;
                        background: #f8f9fa;
                        border: 1px solid #dee2e6;
                        border-radius: 5px;
                        padding: 10px;
                    }
                    summary {
                        cursor: pointer;
                        font-weight: bold;
                        color: #495057;
                    }
                    .debug-content {
                        margin-top: 10px;
                        font-family: monospace;
                        background: #f1f3f4;
                        padding: 10px;
                        border-radius: 3px;
                        word-break: break-all;
                    }
                </style>
            </head>
            <body>
                <div class="error-container">
                    <svg class="error-image" viewBox="0 0 24 24" fill="#dc3545">
                        <path d="M18.3 5.71c-.39-.39-1.02-.39-1.41 0L12 10.59 7.11 5.7c-.39-.39-1.02-.39-1.41 0-.39.39-.39 1.02 0 1.41L10.59 12 5.7 16.89c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0L12 13.41l4.89 4.88c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L13.41 12l4.89-4.89c.38-.38.38-1.02 0-1.4z"/>
                    </svg>
                    <div class="error-message">
                        <h2>Erro inesperado na autenticação!</h2>
                        <p>Contacte o Postmaster para mais informações</p>
                    </div>
                    <div class="debug-dropdown">
                        <details>
                            <summary>Detalhes do erro (Debug)</summary>
                            <div class="debug-content"><?php echo $_GET['error']; echo "<br>"; echo $_GET['error_description']; ?></div>
                        </details>
                    </div>
                </div>
            </body>
            </html>
<?php
    }else if ($_GET['code']){
        try {
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);
            $resourceOwner = $provider->getResourceOwner($accessToken);
            $_SESSION['validity'] = $now + $accessToken->getExpires();
            $_SESSION['resourceOwner'] = $resourceOwner->toArray();
            foreach($_SESSION['resourceOwner']['groups'] as $key => $value){
                if ($value == "ReservaSalas-Administradores"){
                    $_SESSION['admin'] = true;
                }
            }
            if (!isset($_SESSION['admin'])){
                $_SESSION['admin'] = false;
            }
            $_SESSION['nome'] = $_SESSION['resourceOwner']['name'];
            $_SESSION['email'] = $_SESSION['resourceOwner']['email'];
            $_SESSION['id'] = $_SESSION['resourceOwner']['sub'];
            var_dump($_SESSION);
            $db->query("INSERT IGNORE INTO cache (id, nome, email) VALUES ('{$_SESSION['id']}', '{$_SESSION['nome']}', '{$_SESSION['email']}');");
            header('Location: /');
            exit();
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            // Failed to get the access token or user details.
            if ($e->getMessage() == 'invalid_grant') {
                session_destroy();
                header('Location: /login/');
            }
            exit($e->getMessage());
        }
    } else if (str_starts_with($_SERVER['REQUEST_URI'], "/login")) {
	$scopes = [
		'scope' => ['openid profile email']
	];
        $authorizationUrl = $provider->getAuthorizationUrl($scopes);
        $_SESSION['oauth2state'] = $provider->getState();
        header('Location: ' . $authorizationUrl);
    }
?>
