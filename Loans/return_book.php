<?php
require __DIR__ . '/../includes/session_config.php';

// Only logged-in users can return
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Auth/login.php');
    exit();
}

require __DIR__ . '/../includes/db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['loans_error'] = "Invalid request method.";
    header('Location: my_loans.php');
    exit();
}

// Verify CSRF Token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF validation failed. Request rejected.");
}

$userId = $_SESSION['user_id'];
$loanId = isset($_POST['loan_id']) ? (int)$_POST['loan_id'] : 0;

$_SESSION['loans_success'] = '';
$_SESSION['loans_error']   = '';

if ($loanId <= 0) {
    $_SESSION['loans_error'] = "Invalid loan selected.";
    header('Location: my_loans.php');
    exit();
}

try {
    $pdo->beginTransaction();

    // Get loan and make sure it belongs to current user and is active
    $stmt = $pdo->prepare("
        SELECT loan_id, book_id, status 
        FROM loans 
        WHERE loan_id = :loan_id AND user_id = :uid
        FOR UPDATE
    ");
    $stmt->execute([
        ':loan_id' => $loanId,
        ':uid'     => $userId
    ]);
    $loan = $stmt->fetch();

    if (!$loan) {
        $_SESSION['loans_error'] = "Loan not found.";
        $pdo->rollBack();
        header('Location: my_loans.php');
        exit();
    }

    if ($loan['status'] !== 'active') {
        $_SESSION['loans_error'] = "This loan is already returned.";
        $pdo->rollBack();
        header('Location: my_loans.php');
        exit();
    }

    // Update loan status and return date
    $stmt = $pdo->prepare("
        UPDATE loans
        SET status = 'returned',
            return_date = CURRENT_DATE
        WHERE loan_id = :loan_id
    ");
    $stmt->execute([':loan_id' => $loanId]);

    // Update book status back to available
    $stmt = $pdo->prepare("
        UPDATE books
        SET status = 'available'
        WHERE book_id = :book_id
    ");
    $stmt->execute([':book_id' => $loan['book_id']]);

    $pdo->commit();

    $_SESSION['loans_success'] = "Book returned successfully.";
} catch (PDOException $e) {
    error_log("Return Book Error: " . $e->getMessage());
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['loans_error'] = "There was an error while returning the book.";
}

header('Location: my_loans.php');
exit();