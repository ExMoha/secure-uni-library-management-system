<?php
require __DIR__ . '/../includes/session_config.php';

// Generate CSRF token if missing (Required for the Return button)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Only logged-in users can access this page
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Auth/login.php');
    exit();
}

require __DIR__ . '/../includes/db.php';

$userId = $_SESSION['user_id'];

// Messages from borrow/return actions
$successMsg = $_SESSION['loans_success'] ?? '';
$errorMsg   = $_SESSION['loans_error'] ?? '';
unset($_SESSION['loans_success'], $_SESSION['loans_error']);

$loans = [];

try {
    $stmt = $pdo->prepare("
        SELECT 
            l.loan_id,
            l.borrow_date,
            l.return_date,
            l.status,
            b.book_id,
            b.title,
            b.author,
            b.category
        FROM loans l
        JOIN books b ON l.book_id = b.book_id
        WHERE l.user_id = :uid
        ORDER BY 
            CASE WHEN l.status = 'active' THEN 0 ELSE 1 END,
            l.borrow_date DESC
    ");
    $stmt->execute([':uid' => $userId]);
    $loans = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("My Loans Query Error: " . $e->getMessage());
    $errorMsg = "There was a problem loading your borrowed books.";
}

include __DIR__ . '/../includes/header.php';
?>

<h2>My Borrowed Books</h2>

<?php if ($successMsg): ?>
    <section id="success-msg"><?= htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8') ?></section>
<?php endif; ?>

<?php if ($errorMsg): ?>
    <section id="error-msg"><?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?></section>
<?php endif; ?>

<?php if (empty($loans)): ?>
    <p>You have not borrowed any books yet.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Book Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>Borrow Date</th>
                <th>Return Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($loans as $row): ?>
            <tr>
                <td><?= (int)$row['loan_id'] ?></td>
                <td><?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['author'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['category'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['borrow_date'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['return_date'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                
                <td style="color: <?= $row['status'] === 'active' ? 'green' : 'gray' ?>; font-weight: bold;">
                    <?= ucfirst(htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8')) ?>
                </td>
                
                <td>
                    <?php if ($row['status'] === 'active'): ?>
                        <form action="return_book.php" method="POST" onsubmit="return confirm('Are you sure you want to return this book?');">
                            <input type="hidden" name="loan_id" value="<?= (int)$row['loan_id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" style="background-color: #e74c3c; padding: 5px 10px; font-size: 14px;">Return</button>
                        </form>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>