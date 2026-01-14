<?php
// Home page: Lists books and Search Bar.

require_once __DIR__ . '/includes/db.php';

require_once __DIR__ . '/includes/session_config.php';

$searchQuery = '';
$books = [];
$error = '';

try {
    if (isset($_GET['search']) && trim($_GET['search']) !== '') {
        $searchQuery = trim($_GET['search']);
        $searchTerm = '%' . $searchQuery . '%';

        $sql = "SELECT * FROM books WHERE title LIKE :titleVal OR author LIKE :authorVal ORDER BY title";
        $stmt = $pdo->prepare($sql);
        
        // Binding the terms to both placeholders
        $stmt->execute([
            'titleVal' => $searchTerm,
            'authorVal' => $searchTerm
        ]);
    } else {
        $sql = "SELECT * FROM books ORDER BY title";
        $stmt = $pdo->query($sql);
    }
    $books = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Homepage Error: " . $e->getMessage());
    $error = 'There was a problem loading the books.';
}

require __DIR__ . '/includes/header.php';
?>

<section id="home-hero" style="text-align: center; padding: 40px 20px; background-color: #ecf0f1; border-radius: 8px; margin-bottom: 30px;">
    <h1 style="color: #2c3e50;">Welcome to the Digital Library</h1>
    <p style="font-size: 1.2em; color: #7f8c8d;">Search, borrow, and manage your books in one place.</p>
</section>

<section id="search-section" style="text-align: center; margin-bottom: 40px;">
    <form method="GET" action="index.php" id="search-form">
        <input 
            type="text" 
            name="search" 
            placeholder="Search by title or author..." 
            value="<?php echo htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8'); ?>"
            style="width: 60%; padding: 10px; font-size: 16px;"
        >
        <button type="submit" style="padding: 10px 20px;">Search</button>

        <?php if ($searchQuery !== ''): ?>
            <a href="index.php" style="margin-left: 10px; text-decoration: none; color: #e74c3c;">Clear</a>
        <?php endif; ?>
    </form>
</section>

<section id="books-list">
    <h2 style="border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-bottom: 20px;">
        <?php echo $searchQuery !== '' ? 'Search Results' : 'All Books'; ?>
    </h2>

    <?php if (!empty($error)): ?>
        <p class="error" style="color: red;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <div class="books-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
        <?php if (!empty($books)): ?>
            <?php foreach ($books as $book): ?>
                <article class="book-card" style="border: 1px solid #ddd; padding: 20px; border-radius: 8px; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div style="height: 200px; background: #eee; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <?php if (!empty($book['cover_image'])): ?>
                            <img src="images/<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Cover" style="max-width: 100%; max-height: 100%;">
                        <?php else: ?>
                            <span style="color: #aaa;">No Cover</span>
                        <?php endif; ?>
                    </div>

                    <h3 style="margin: 0 0 10px 0; font-size: 1.2em;"><?php echo htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p style="margin: 0 0 10px 0; color: #666;"><strong>Author:</strong> <?php echo htmlspecialchars($book['author'], ENT_QUOTES, 'UTF-8'); ?></p>
                    
                    <p style="margin-bottom: 15px;">
                        Status: 
                        <span style="font-weight: bold; color: <?php echo $book['status'] === 'available' ? 'green' : 'red'; ?>">
                            <?php echo ucfirst($book['status']); ?>
                        </span>
                    </p>

                    <a href="BookDetails/BookDetails.php?id=<?php echo urlencode($book['book_id']); ?>" 
                       style="display: inline-block; padding: 8px 15px; background-color: #3498db; color: white; text-decoration: none; border-radius: 4px;">
                        View Details
                    </a>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No books found.</p>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>