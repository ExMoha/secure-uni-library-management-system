<?php
// Centralized session configurations for the whole project

// Defining the root URL for the project
define('BASE_URL', '/CYB325/'); 


// Starting the session and ensuring security configs
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0, // Session cookie lasts until the browser is closed
        'path' => '/', // Cookie applicable across the whole website
        'domain' => '', 
        'secure' => false, // Should be true if using HTTPS
        'httponly' => true, // Prevent JavaScript access to session cookie to mitigate XSS attacks
        'samesite' => 'Strict' // Helps prevent CSRF attacks by resitricting access to only requests from the same site 
    ]);

    session_start();
}
?>