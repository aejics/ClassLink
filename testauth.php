<?php
require_once('vendor/autoload.php');
var_dump($_SERVER);
echo "<br>";

use League\OAuth2\Client\Provider\GenericProvider;

// Set up the OAuth 2.0 provider
$provider = new GenericProvider([
    'urlAuthorize'            => 'https://authentik.devenv.marcopisco.com/application/o/authorize/',
    'urlAccessToken'          => 'https://authentik.devenv.marcopisco.com/application/o/token/',
    'urlResourceOwnerDetails' => 'https://authentik.devenv.marcopisco.com/application/o/userinfo/',
    'clientId'     => 'Cn5tPoeJIjoWnvUJLKdxe63yHx3UnuhLegpLDPUY',
    'clientSecret' => '0U9jm7iA3WZy27j3x1fAJxWIRcNC4sGqaQt3yKS55Orw4e1y0TuoYPX0nGMZBbUIr4vJl8MG11Vto9UDLwlYJqibIHDj6ClU22yccaKTw6PpHO8IXcy4DbuHAPNeSrSz',
    'redirectUri'  => 'https://' . $_SERVER['HTTP_HOST'] . '/testauth.php',
]);

session_start();


// Step 1: Redirect user to Google authorization page
if (!isset($_GET['code'])) {
    $authorizationUrl = $provider->getAuthorizationUrl();
    $_SESSION['oauth2state'] = $provider->getState();
    header('Location: ' . $authorizationUrl);
    exit;
}

// Step 2: Exchange authorization code for an access token
if (isset($_GET['code'])) {
    try {
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        echo 'Access Token: ' . $accessToken->getToken() . "<br>";
        echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
        echo 'Expired in: ' . $accessToken->getExpires() . "<br>";
        echo 'Already expired? ' . ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<br>";

        // Use the access token to get user information
        $resourceOwner = $provider->getResourceOwner($accessToken);

        // Print user information
        echo 'Hello, ' . var_dump($resourceOwner);
    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        // Failed to get the access token or user details.
        if ($e->getMessage() == 'invalid_grant') {
            session_destroy();
            header('Location: /testauth.php');
        }
        exit($e->getMessage());
    }
}
