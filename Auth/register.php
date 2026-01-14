<?php

// Importing database connection using absolute path for security
require __DIR__ . '/../includes/db.php';

require __DIR__ . '/../includes/session_config.php';

// Generate a CSRF token if one doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

// Initialize variables so the user don't have to re-enter data on error
$fullname_value = '';
$email_value = '';

// Only respond to POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF validation failed. Request rejected.");
    }

    // Retrieving and sanitizing user inputs
    $fullName = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    // Preserve user input in case of error also using htmlspecialchars to prevent XSS
    $fullname_value = htmlspecialchars($fullName);
    $email_value = htmlspecialchars($email);

    // Server-side validation

    // Check for empty fields
    if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error = 'All fields are required.';

    // Validate full name (only letters and spaces) in case JS validation is bypassed for any reason
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $fullName)) {
        $error = 'Full name can only contain letters and spaces.';

    // Validate email format
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';

    // Validate password length (minimum 8 characters)
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';

    // Check if passwords match
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';

    // Validate password strength (must contain at least one uppercase, one lowercase, and one number)
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || (!preg_match('/[!@#$%^&*()]/', $password))) {
        $error = 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.';
    } else {

        // Check if email already exists in the database using prepared statements for security
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $error = 'Email is already registered.';

        // If all validations pass, proceed to register the user and hash the password to save it securely.
        } else {

            // Hash and salt the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user into the database using prepared statements for security
            $sql = "INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, 'student')";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([$fullName, $email, $hashedPassword])) {
                // Clear the input values upon successful registration
                $fullname_value = '';
                $email_value = '';
                $success = true;
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!-- This is the Main section of the HTML -->

<?php require __DIR__ . '/../includes/header.php'; // importing the header  ?>

<section id="auth-container" style="max-width: 400px; margin: 50px auto;">
    <h2>Create an Account</h2>


    <?php if($error): // Handling error display ?>
        <section id="error-msg"><?php echo $error; ?></section>
    <?php endif; ?>

    <?php if($success): // Handling success display?>
        <section id="success-msg">Registration successful! You can now <a href="login.php" style="color: var(--accent-color); text-decoration: none;">login</a>.</section>
    <?php else: ?>

    <!-- Registration Form  with basic HTML validation -->
    <form action="register.php" method="POST" novalidate>

        <label>Full Name</label>
        <input type="text" name="fullname" value="<?php echo $fullname_value; ?>" required placeholder="Should only contain letter and spaces.">
        

        <label>Email Address</label>
        <input type="email" name="email" value="<?php echo $email_value; ?>" required placeholder="example@example.com">
        

        <label>Password</label>
        <input type="password" name="password" required minlength="8" placeholder="Example: 12345678Dg@">
        

        <label>Confirm Password</label>
        <input type="password" name="confirmPassword" required minlength="8">

        <!-- CSRF Token Field so it gets sent to the server -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        

        <button type="submit">Register</button>
        
        <p style="margin-top: 15px;">Already have an account? <a href="login.php">Login here</a></p>
    </form>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../includes/footer.php'; // importing the footer  ?>