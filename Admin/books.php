<?php
require __DIR__ . '/../includes/db.php';
require 'auth_check.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$books = $pdo->query("SELECT * FROM books")->fetchAll();

require __DIR__ . '/../includes/header.php';
?>

<section id="book-management" style="max-width: 1000px; margin: 0 auto;">
    <section style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>Book Management</h1>
        <a href="add_book.php" class="btn" style="background-color: #27ae60; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;">+ Add New Book</a>
    </section>

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #2c3e50; color: white;">
                <th style="padding: 10px;">Title</th>
                <th style="padding: 10px;">Author</th>
                <th style="padding: 10px;">Category</th>
                <th style="padding: 10px;">Status</th>
                <th style="padding: 10px;">Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($books as $b): ?>
        <tr style="border-bottom: 1px solid #ddd;">
            <td style="padding: 10px;"><?= htmlspecialchars($b['title']) ?></td>
            <td style="padding: 10px;"><?= htmlspecialchars($b['author']) ?></td>
            <td style="padding: 10px;"><?= htmlspecialchars($b['category']) ?></td>
            
            <td style="padding: 10px;">
                <span style="color: <?= $b['status'] == 'available' ? 'green' : 'red' ?>; font-weight: bold;">
                    <?= htmlspecialchars($b['status']) ?>
                </span>
            </td>

            <td style="padding: 10px; display: flex; gap: 10px;">
                <a href="edit_book.php?book_id=<?= $b['book_id'] ?>" style="color: #3498db; text-decoration: none;">Edit</a>
                
                <form action="delete_book.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this book?');" style="display:inline;">
                    <input type="hidden" name="book_id" value="<?= $b['book_id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <button type="submit" style="background: none; border: none; color: red; cursor: pointer; text-decoration: underline;">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>