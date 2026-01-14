<?php
require __DIR__ . '/../includes/db.php';
require 'auth_check.php';

$users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$books_count = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
$suggestions_count = $pdo->query("SELECT COUNT(*) FROM suggestions")->fetchColumn();
$loans_count = $pdo->query("SELECT COUNT(*) FROM loans")->fetchColumn();

require __DIR__ . '/../includes/header.php';
?>

<section id="admin-dashboard" style="max-width: 800px; margin: 0 auto;">
    <h1>Security Control Panel</h1>

    <div style="margin-bottom: 30px;">
        <h3>Quick Actions</h3>
        <ul style="list-style: none; padding: 0;">
            <li style="margin-bottom: 10px;">
                <a href="books.php" style="color: #3498db; font-weight: bold; text-decoration: none;">Book Management</a>
            </li>
            <li style="margin-bottom: 10px;">
                <a href="../suggestions/suggestions_box.php" style="color: #3498db; font-weight: bold; text-decoration: none;">Suggestions Management</a>
            </li>
        </ul>
    </div>

    <hr style="margin: 20px 0; border: 0; border-top: 1px solid #eee;">

    <h3>Statistics</h3>
    <section style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; margin-top: 20px;">
        <section style="background: #f9f9f9; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #ddd;">
            <span style="font-size: 2em; display: block; font-weight: bold; color: #2c3e50;"><?= (int)$users_count ?></span>
            <span style="color: #7f8c8d;">Users</span>
        </section>
        <section style="background: #f9f9f9; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #ddd;">
            <span style="font-size: 2em; display: block; font-weight: bold; color: #2c3e50;"><?= (int)$books_count ?></span>
            <span style="color: #7f8c8d;">Books</span>
        </section>
        <section style="background: #f9f9f9; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #ddd;">
            <span style="font-size: 2em; display: block; font-weight: bold; color: #2c3e50;"><?= (int)$loans_count ?></span>
            <span style="color: #7f8c8d;">Active Loans</span>
        </section>
        <section style="background: #f9f9f9; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #ddd;">
            <span style="font-size: 2em; display: block; font-weight: bold; color: #2c3e50;"><?= (int)$suggestions_count ?></span>
            <span style="color: #7f8c8d;">Suggestions</span>
        </section>
    </section>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>