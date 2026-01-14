<?php
require __DIR__ . '/../includes/session_config.php';

// Generate a CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Only logged-in admin can access this page
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Auth/login.php');
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo 'Access denied.';
    exit();
}

// Include database connection
require __DIR__ . '/../includes/db.php';

$successMsg = '';
$errorMsg   = '';

// Handle approve / reject action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['id'])) {
    
    // Verify CSRF token to prevent CSRF attacks
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed. Request rejected.");
    }

    $id     = (int) $_POST['id'];
    $action = $_POST['action'];

    if ($id > 0 && in_array($action, ['approve', 'reject'], true)) {
        $newStatus = ($action === 'approve') ? 'approved' : 'rejected';

        try {
            $stmt = $pdo->prepare(
                "UPDATE suggestions
                 SET status = :status
                 WHERE suggestion_id = :id"
            );
            $stmt->execute([
                ':status' => $newStatus,
                ':id'     => $id
            ]);

            $successMsg = 'Suggestion status updated successfully.';
        } catch (PDOException $e) {
            // Log error for debugging
            error_log("Suggestion Update Error: " . $e->getMessage());
            $errorMsg = 'Error updating suggestion status.';
        }
    }
}

// Load suggestions from database
try {
    $stmt = $pdo->query(
        "SELECT s.*, u.fullname
         FROM suggestions s
         JOIN users u ON s.user_id = u.user_id
         ORDER BY s.created_at DESC"
    );
    $suggestions = $stmt->fetchAll();
} catch (PDOException $e) {
    $errorMsg    = 'Error loading suggestions.';
    $suggestions = [];
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<h2>Suggestions Inbox</h2>

<?php if ($errorMsg): ?>
    <div id="error-msg"><?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($successMsg): ?>
    <div id="success-msg"><?= htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if (empty($suggestions)): ?>
    <p>No suggestions found.</p>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Book Title</th>
                <th>Author</th>
                <th>Description</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($suggestions as $row): ?>
            <tr>
                <td><?= (int) $row['suggestion_id'] ?></td>
                <td><?= htmlspecialchars($row['fullname'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['book_title'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['author'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= nl2br(htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8')) ?></td>
                <td><?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php if ($row['status'] === 'pending'): ?>
                        <form method="POST" action="suggestions_box.php" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="id" value="<?= (int) $row['suggestion_id'] ?>">
                            <button type="submit" style="background: none; border: none; color: #3498db; text-decoration: underline; cursor: pointer; padding: 0; font-size: inherit;">Approve</button>
                        </form>
                        |
                        <form method="POST" action="suggestions_box.php" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="id" value="<?= (int) $row['suggestion_id'] ?>">
                            <button type="submit" style="background: none; border: none; color: #e74c3c; text-decoration: underline; cursor: pointer; padding: 0; font-size: inherit;">Reject</button>
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
