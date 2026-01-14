<?php
require_once __DIR__ . '/session_config.php';
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Digital Library</title>
        <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    </head>

    <body>
        <header>
            <nav>
                <figure id="logo">
                    <img src="<?php echo BASE_URL; ?>images/Project logo2.png" alt="Library Logo" height="80px">
                </figure>

                <ul id="nav-links">
                    <li><a href="<?php echo BASE_URL; ?>index.php">Home</a></li>

                    <?php if (isset($_SESSION['user_id'])): ?>

                        <?php if ($_SESSION['role'] === 'admin'): // checking whether the user is admin or not. if admin then the admin options will show within the navigation bar ?>
                            <li><a href="<?php echo BASE_URL; ?>Admin/admin.php" style="color: red;">Admin Panel</a></li>
                            <li><a href="<?php echo BASE_URL; ?>Admin/add_book.php">Add Book</a></li>

                        <?php else: // normal users exclusive options ?>
                            <li><a href="<?php echo BASE_URL; ?>Loans/my_loans.php">My Borrowed Books</a></li>
                            <li><a href="<?php echo BASE_URL; ?>suggestions/suggest_book.php">Suggest Book</a></li>
                        <?php endif; ?>

                        <li><a href="<?php echo BASE_URL; ?>Auth/profile.php">Profile</a></li>
                        <li><a href="<?php echo BASE_URL; ?>Auth/logout.php" id="btn-logout">Logout</a></li>

                    <?php else: // No SESSION ID found for user which means user is new or the previous session ID is renewed ?>
                        <li><a href="<?php echo BASE_URL; ?>Auth/login.php">Login</a></li>
                        <li><a href="<?php echo BASE_URL; ?>Auth/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </header>
        <hr> 
        <main>
    