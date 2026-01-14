<?php

require __DIR__ . '/../includes/session_config.php';

// Destroy all session data to log out the user
$_SESSION = [];

// Destroying session cookies
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroying the whole session
session_destroy();

// Redirecting to the login page after logout
header('Location: login.php');
exit;

?>