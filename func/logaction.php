<?php
/**
 * Get the client's IP address
 * Handles proxies and load balancers by checking multiple headers
 */
function get_client_ip() {
    $ip = '';
    
    // Check for proxy headers first
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // HTTP_X_FORWARDED_FOR can contain multiple IPs (client, proxy1, proxy2, ...)
        // We want the first one (the client's real IP)
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ips[0]);
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    // Validate IP address format (IPv4 or IPv6)
    if (filter_var($ip, FILTER_VALIDATE_IP)) {
        return $ip;
    }
    
    return 'Unknown';
}

function logaction(string $loginfo, string $userid){
    require_once(__DIR__ . '/../func/genuuid.php');
    require_once(__DIR__ . "/../src/db.php");
    global $db;

    $id = uuid4();
    $ip_address = get_client_ip();
    $stmt = $db->prepare("INSERT INTO logs (id, loginfo, userid, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $id, $loginfo, $userid, $ip_address);
    $stmt->execute();
    $stmt->close();
};
?>
