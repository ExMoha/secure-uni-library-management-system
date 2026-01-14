<?php
require __DIR__ . '/../includes/session_config.php';

// Generate a CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Only logged-in users can access this page
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Auth/login.php');
    exit();
}

// Include database connection
require __DIR__ . '/../includes/db.php';

$successMsg = '';
$errorMsg   = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Verify CSRF token to prevent CSRF attacks
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed. Request rejected.");
    }

    // Retrieving and sanitizing user inputs
    $title       = trim($_POST['title'] ?? '');
    $author      = trim($_POST['author'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '' || $author === '') {
        $errorMsg = 'Please fill in all required fields (Title and Author).';

    // Validate title format (alphanumeric, spaces, hyphens, apostrophes, and common punctuation)
    } elseif (!preg_match("/^[A-Za-z0-9\s\-\',.:;!?()]+$/", $title)) {
        $errorMsg = 'Book title contains invalid characters.';

    // Validate author format
    } elseif (!preg_match("/^[A-Za-z\s\-\'.]+$/", $author)) {
        $errorMsg = 'Author name can only contain letters, spaces, hyphens and apostrophes.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO suggestions (user_id, book_title, author, description)
                 VALUES (:uid, :title, :author, :description)"
            );
            $stmt->execute([
                ':uid'         => $_SESSION['user_id'],
                ':title'       => $title,
                ':author'      => $author,
                ':description' => $description
            ]);

            $successMsg = 'Your book suggestion has been submitted successfully.';
        } catch (PDOException $e) {
            // Log error for debugging
            error_log("Suggestion Submission Error: " . $e->getMessage());
            $errorMsg = 'An error occurred while saving your suggestion. Please try again later.';
        }
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<h2>Suggest a New Book</h2>

<?php if ($errorMsg): ?>
    <div id="error-msg"><?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($successMsg): ?>
    <div id="success-msg"><?= htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<form action="suggest_book.php" method="POST" id="suggest-form" novalidate>
    <label for="title">Book Title *</label>
    <input type="text" id="title" name="title" required>

    <br><br>

    <label for="author">Author *</label>
    <input type="text" id="author" name="author" required>

    <br><br>

    <label for="description">Description (optional)</label>
    <textarea id="description" name="description" rows="4"></textarea>

    <br><br>

    <!-- CSRF Token Field so it gets sent to the server -->
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

    <button type="submit">Submit Suggestion</button>
</form>

<?php include __DIR__ . '/../includes/footer.php'; ?>
