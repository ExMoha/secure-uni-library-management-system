<?php
require __DIR__ . '/../includes/db.php';
require 'auth_check.php';

// only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid Request Method. Deletion must be done via POST.");
}

// Verify CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF validation failed.");
}

// Validate ID
if (!isset($_POST['book_id']) || !ctype_digit($_POST['book_id'])) {
    die("Invalid Book ID.");
}

$id = $_POST['book_id'];

// Delete
try {
    $stmt = $pdo->prepare("DELETE FROM books WHERE book_id=?");
    $stmt->execute([$id]);
    header("Location: books.php");
    exit;
} catch (PDOException $e) {
    die("Error deleting book: " . $e->getMessage());
}
?>