<?php
require __DIR__ . '/../includes/session_config.php';

// Only logged-in users can borrow
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Auth/login.php');
    exit();
}

require __DIR__ . '/../includes/db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['loans_error'] = "Invalid request method.";
    header('Location: ../index.php'); // Redirect to homepage or catalog
    exit();
}

// Verify CSRF Token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF validation failed. Request rejected.");
}

$userId  = $_SESSION['user_id'];
$bookId  = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;

$_SESSION['loans_success'] = '';
$_SESSION['loans_error']   = '';

if ($bookId <= 0) {
    $_SESSION['loans_error'] = "Invalid book selected.";
    header('Location: my_loans.php');
    exit();
}

try {
    // Start transaction to keep data consistent
    $pdo->beginTransaction();

    // Check book exists and is available
    $stmt = $pdo->prepare("SELECT status FROM books WHERE book_id = :book_id FOR UPDATE");
    $stmt->execute([':book_id' => $bookId]);
    $book = $stmt->fetch();

    if (!$book) {
        $_SESSION['loans_error'] = "Book not found.";
        $pdo->rollBack();
        header('Location: my_loans.php');
        exit();
    }

    if ($book['status'] !== 'available') {
        $_SESSION['loans_error'] = "This book is currently not available.";
        $pdo->rollBack();
        header('Location: my_loans.php');
        exit();
    }

    // Make sure no active loan exists for this book
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS cnt 
        FROM loans 
        WHERE book_id = :book_id AND status = 'active'
    ");
    $stmt->execute([':book_id' => $bookId]);
    $row = $stmt->fetch();

    if ($row && (int)$row['cnt'] > 0) {
        $_SESSION['loans_error'] = "This book is already borrowed by another user.";
        $pdo->rollBack();
        header('Location: my_loans.php');
        exit();
    }

    // Insert new loan
    $stmt = $pdo->prepare("
        INSERT INTO loans (user_id, book_id, borrow_date, status)
        VALUES (:uid, :book_id, CURRENT_DATE, 'active')
    ");
    $stmt->execute([
        ':uid'     => $userId,
        ':book_id' => $bookId
    ]);

    // Update book status
    $stmt = $pdo->prepare("
        UPDATE books 
        SET status = 'borrowed' 
        WHERE book_id = :book_id
    ");
    $stmt->execute([':book_id' => $bookId]);

    $pdo->commit();

    $_SESSION['loans_success'] = "Book borrowed successfully.";
} catch (PDOException $e) {
    error_log("Borrow Book Error: " . $e->getMessage());
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['loans_error'] = "There was an error while borrowing the book.";
}

header('Location: my_loans.php');
exit();