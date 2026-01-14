<?php
// Detailed view for a single book.

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session_config.php'; 

$book = null;
$error = '';

// Validate ID
if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $bookId = (int) $_GET['id'];

    try {
        $sql = "SELECT * FROM books WHERE book_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $bookId]);
        $book = $stmt->fetch();

        if (!$book) {
            $error = 'Book not found.';
        }
    } catch (PDOException $e) {
        error_log("BookDetails Error: " . $e->getMessage());
        $error = 'There was a problem loading this book.';
    }
} else {
    $error = 'No book specified.';
}

require __DIR__ . '/../includes/header.php';
?>

<section id="book-details" style="max-width: 800px; margin: 0 auto;">
    <a href="../index.php" class="back-link" style="text-decoration: none; color: #3498db;">&larr; Back to Home</a>
    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

    <?php if (!empty($error)): ?>
        <p class="error" style="color: red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php elseif (!empty($book)): ?>
        
        <div style="display: flex; gap: 30px; flex-wrap: wrap;">
            <div style="flex: 0 0 200px;">
                <div style="width: 200px; height: 300px; background: #f4f4f4; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd;">
                    <?php if (!empty($book['cover_image'])): ?>
                        <img src="../images/<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Cover" style="max-width: 100%; max-height: 100%;">
                    <?php else: ?>
                        <span>No Cover</span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="flex: 1;">
                <h1 style="margin-top: 0;"><?php echo htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <h3>By <?php echo htmlspecialchars($book['author'], ENT_QUOTES, 'UTF-8'); ?></h3>
                
                <p>
                    <strong>Status: </strong> 
                    <span style="color: <?php echo $book['status'] === 'available' ? 'green' : 'red'; ?>; font-weight: bold;">
                        <?php echo ucfirst($book['status']); ?>
                    </span>
                </p>

                <?php if (!empty($book['description'])): ?>
                    <p style="margin-top: 20px; line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars($book['description'], ENT_QUOTES, 'UTF-8')); ?>
                    </p>
                <?php endif; ?>

                <div style="margin-top: 30px;">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['role'] === 'student'): ?>
                            <?php if ($book['status'] === 'available'): ?>
                                
                                <form action="../Loans/borrow_book.php" method="POST">
                                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    
                                    <button type="submit" style="background-color: #27ae60; color: white; padding: 12px 24px; font-size: 16px; border: none; border-radius: 4px; cursor: pointer;">
                                        Borrow This Book
                                    </button>
                                </form>

                            <?php else: ?>
                                <button disabled style="background-color: #ccc; color: #666; padding: 12px 24px; font-size: 16px; border: none; border-radius: 4px; cursor: not-allowed;">
                                    Currently Unavailable
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <p><a href="../Auth/login.php" style="color: #3498db; font-weight: bold;">Log in</a> to borrow this book.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php endif; ?>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>