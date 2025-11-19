<?php
    require_once(__DIR__ . '/../src/config.php');
    require_once(__DIR__ . '/../src/db.php');
    
    session_start();
    if (isset($_GET['action']) && $_GET['action'] == "logout"){
        session_destroy();
        header("Location: " . $logoutUrlProvider);
        exit();
    } else if (isset($_GET['error'])) {
	?>
<?php
    }else if (isset($_GET['code'])){
        try {
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);
            $resourceOwner = $provider->getResourceOwner($accessToken);
            // Atribuir valores desta sessão OAuth2
            $_SESSION['validity'] = $now + $accessToken->getExpires();
            $_SESSION['resourceOwner'] = $resourceOwner->toArray();
            $_SESSION['nome'] = $_SESSION['resourceOwner']['name'];
            $_SESSION['email'] = $_SESSION['resourceOwner']['email'];
            $_SESSION['id'] = $_SESSION['resourceOwner']['sub'];

            // Atribuir valores à Cache na DB
            $stmt = $db->prepare("INSERT IGNORE INTO cache (id, nome, email) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $_SESSION['id'], $_SESSION['nome'], $_SESSION['email']);
            $stmt->execute();
            $stmt->close();

            // Determinar se é Administrador
            $stmt = $db->prepare("SELECT admin FROM cache WHERE id = ?");
            $stmt->bind_param("s", $_SESSION['id']);
            $stmt->execute();
            $isadmin = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($isadmin['admin'] == 1){
                $_SESSION['admin'] = true;
            } else {
                $_SESSION['admin'] = false;
            }
            // Regenerate session ID for security
            session_regenerate_id(true);
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
    } else if (str_starts_with($_SERVER['REQUEST_URI'], "/login" && $_GET['redirecttoflow'])) {
	$scopes = [
		'scope' => ['openid profile email']
	];
        $authorizationUrl = $provider->getAuthorizationUrl($scopes);
        $_SESSION['oauth2state'] = $provider->getState();
        header('Location: ' . $authorizationUrl);
    } else {
        echo('a');
    }
?>
