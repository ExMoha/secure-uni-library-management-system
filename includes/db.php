<?php

// Database connection configs
$host = 'localhost';
$dbname = 'library';
$username = 'root';
$password = '';

// Connection string.
$connectionString = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    // setting up the PDO
    $pdo = new PDO($connectionString, $username, $password);

    // PDO object options

    // Setting it to throw exceptions on errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Putting data into associative arrays before fetching for better performance.
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Use real prepared statements for security.
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch (PDOException $e) {
    // When an error occurres, the DB server will log the error and close the connection.
    error_log("Database Connection Error: " . $e->getMessage());

    die("There are issues with the database connection. Try again later.");
}

?>