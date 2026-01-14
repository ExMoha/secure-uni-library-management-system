<?php

// Importing database connection 
require __DIR__ . '/../includes/db.php';

require __DIR__ . '/../includes/session_config.php';

// Generating a CSRF token if it's not already generated to prevent CSRF attacks
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

// Check if the user is already logged in; if so, redirect to home page
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Only respond to POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verifying the CSRF token to prevent CSRF attacks
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed. Request rejected.");
    }

    // Retrieving and sanitizing user inputs
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Server-side validation

    // Check for empty fields
    if (empty($email) || empty($password)) {
        $error = 'Both email and password are required.';

    // Check if the user's credentials are valid+
    } else {

        // Fetch user from the database using prepared statements for security
        $sql = "SELECT user_id, fullname, password, role FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Verify user existence and password
        // password_verify() retrieve the hashed password from the database and extract the salt to hash the entered password for comparison
        if ($user && password_verify($password, $user['password'])) {

            // Regenerating the session ID to prevent session fixation attacks
            session_regenerate_id(true);

            // Set session variables upon successful login to make the website remember the user
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];

            
            header('Location: ../index.php');
            
            exit;

        } else {

            // Balancing between security and user experience by providing a generic error message
            $error = 'Invalid email or password.';
        }
    }
}
?>

<?php require __DIR__ . '/../includes/header.php'; // importing the header  ?>

<section id="auth-container" style="max-width: 400px; margin: 50px auto;">
    <h2>Login to Library</h2>

    <?php if($error): ?>
        <section id="error-msg"><?php echo $error; ?></section>
    <?php endif; ?>

    <form action="login.php" method="POST" novalidate>

        <label>Email Address</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <!-- CSRF Token Field so it gets sent to the server -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <button type="submit">Login</button>
        
        <p style="margin-top: 15px;">Don't have an account? <a href="register.php">Register here</a></p>
    </form>
</section>

<?php require __DIR__ . '/../includes/footer.php'; // importing the footer  ?>  