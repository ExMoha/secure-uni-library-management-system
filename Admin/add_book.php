<?php
require __DIR__ . '/../includes/db.php';
require 'auth_check.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed.");
    }

    // Sanitize & Validate
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $category = trim($_POST['category']);
    $status = $_POST['status'];

    if (empty($title) || empty($author)) {
        $error = "Title and Author are required.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO books (title, author, category, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $author, $category, $status]);
            header("Location: books.php");
            exit;
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

<section style="max-width: 500px; margin: 0 auto;">
    <h1>Add New Book</h1>
    
    <?php if ($error): ?>
        <p style="color: red; background: #fce4e4; padding: 10px; border: 1px solid #fcc;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Title</label>
        <input type="text" name="title" required>
        <br><br>

        <label>Author</label>
        <input type="text" name="author" required>
        <br><br>

        <label>Category</label>
        <input type="text" name="category">
        <br><br>

        <label>Status</label>
        <select name="status">
            <option value="available">Available</option>
            <option value="borrowed">Borrowed</option> </select>
        <br><br>

        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <button type="submit">Save Book</button>
    </form>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>