<?php
require __DIR__ . '/../includes/db.php';
require 'auth_check.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Validate Book ID
if (!isset($_GET['book_id']) || !ctype_digit($_GET['book_id'])) {
    die("Invalid Book ID.");
}

$id = $_GET['book_id'];
$error = '';

$stmt = $pdo->prepare("SELECT * FROM books WHERE book_id=?");
$stmt->execute([$id]);
$book = $stmt->fetch();

if (!$book) die("the book is not avalible");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed.");
    }

    $title = trim($_POST['title']);
    $author = trim($_POST['author']);

    if (empty($title) || empty($author)) {
        $error = "Title and Author are required.";
    } else {
        $stmt = $pdo->prepare("UPDATE books SET title=?, author=?, category=?, status=? WHERE book_id=?");
        $stmt->execute([
            $title,
            $author,
            trim($_POST['category']),
            $_POST['status'],
            $id
        ]);
        header("Location: books.php");
        exit;
    }
}

require __DIR__ . '/../includes/header.php';
?>

<section style="max-width: 500px; margin: 0 auto;">
    <h1>Edit Book</h1>

    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>" required><br><br>

        <label>Author</label>
        <input type="text" name="author" value="<?= htmlspecialchars($book['author']) ?>" required><br><br>

        <label>Category</label>
        <input type="text" name="category" value="<?= htmlspecialchars($book['category']) ?>"><br><br>

        <label>Status</label>
        <select name="status">
            <option <?= $book['status']=='available'?'selected':'' ?> value="available">Available</option>
            <option <?= $book['status']=='borrowed'?'selected':'' ?> value="borrowed">Borrowed</option>
        </select><br><br>

        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <button type="submit">Update Book</button>
    </form>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>