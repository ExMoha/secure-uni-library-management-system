<?php
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/session_config.php';

// Refuse connection if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Generating a CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$userId = $_SESSION['user_id'];
$message = '';
$error = '';

// Fetching user info
try {
    $stmt = $pdo->prepare("SELECT fullname, email, role, password FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log("Fetch User Info Error: " . $e->getMessage());
    die("Error fetching user information. Please try again later.");
}

// Handling form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verifying CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token verification failed. Request rejected.');
    }

    // update email
    if (isset($_POST['update_email'])) {

        // we need password to verify the change request
        $newEmailInput  = trim($_POST['new_email']);
        $verifyPassword = $_POST['verify_password']; 

        // input validation
        if (empty($newEmailInput) || empty($verifyPassword)) {
            $error = "All fields are required.";
        
        } elseif (!filter_var($newEmailInput, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid new email format.";

        // Verify Password before allowing change
        } elseif (!password_verify($verifyPassword, $user['password'])) {
            $error = "Incorrect password. Email update denied.";

        // if new email is same as old
        } elseif ($newEmailInput === $user['email']) {
            $error = "New email is the same as the current one.";

        } else {
            // if new email is already used in the database
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
            $stmt->execute([$newEmailInput]);
            
            if ($stmt->rowCount() > 0) {
                $error = "This email address is already registered.";
            } else {
                try {
                    $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE user_id = ?");
                    if ($stmt->execute([$newEmailInput, $userId])) {
                        $user['email'] = $newEmailInput; 
                        $message = "Email address updated successfully.";
                    }
                } catch (PDOException $e) {
                    error_log("Update Email Error: " . $e->getMessage());
                    $error = "Error updating email. Please try again later.";
                }
            }
        }
    }

    // update password
    if (isset($_POST['update_password'])) {
        $currentPass = $_POST['current_password'];
        $newPass     = $_POST['new_password'];
        $confirmPass = $_POST['confirm_password'];

        if (empty($currentPass) || empty($newPass) || empty($confirmPass)) {
             $error = "All fields are required.";

        // Verify Current Password
        } elseif (!password_verify($currentPass, $user['password'])) {
            $error = "Incorrect current password.";

        } elseif (strlen($newPass) < 8) {
            $error = "New password must be at least 8 characters long.";
        
        // Prevent using the same password
        } elseif (password_verify($newPass, $user['password'])) {
            $error = "New password cannot be the same as the current one.";
            
        } elseif ($newPass !== $confirmPass) {
            $error = "New passwords do not match.";

        } elseif (!preg_match('/[A-Z]/', $newPass) || !preg_match('/[a-z]/', $newPass) || !preg_match('/[0-9]/', $newPass) || !preg_match('/^[!@#$%^&*()]$/')) {
            $error = "Password must include uppercase, lowercase, number, and a special character.";
        } else {
            try {
                $hashed = password_hash($newPass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                if ($stmt->execute([$hashed, $userId])) {
                    $message = "Password changed successfully!";

                    // Update local variable
                    $user['password'] = $hashed; 
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>

<section id="profile-page" style="max-width: 800px; margin: 0 auto;">
    
    <section style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 30px;">
        <section>
            <h1 style="margin: 0;">My Profile</h1>
            <p style="color: #666; margin-top: 5px;">Manage your account settings and security.</p>
        </section>
        <section style="text-align: right;">
            <span style="background-color: #3498db; color: white; padding: 5px 10px; border-radius: 4px; font-size: 0.9em;">
                <?php echo ucfirst(htmlspecialchars($user['role'])); ?> Account
            </span>
        </section>
    </section>

    <?php if ($message): ?>
        <section id="success-msg"><?php echo htmlspecialchars($message); ?></section>
    <?php endif; ?>
    <?php if ($error): ?>
        <section id="error-msg"><?php echo htmlspecialchars($error); ?></section>
    <?php endif; ?>

    <section style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
        
        <section>
            <h3 style="color: #2c3e50; border-bottom: 1px solid #ddd; padding-bottom: 10px;">Account Details</h3>
            
            <form action="profile.php" method="POST" style="margin-top: 20px;">
                
                <label>Full Name</label>
                <input type="text" value="<?php echo htmlspecialchars($user['fullname']); ?>" disabled style="background-color: #f9f9f9; color: #7f8c8d; cursor: not-allowed; margin-bottom: 15px;">
                
                <label>Current Email</label>
                <input type="text" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background-color: #f9f9f9; color: #7f8c8d; margin-bottom: 15px;">

                <label for="new_email">New Email Address</label>
                <input type="email" name="new_email" id="new_email" required placeholder="Enter new email">

                <label for="verify_password" style="margin-top: 15px; color: #e74c3c;">Current Password (Required)</label>
                <input type="password" name="verify_password" id="verify_password" required placeholder="Confirm with password">

                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit" name="update_email" style="margin-top: 20px;">Update Email</button>
            </form>
        </section>

        <section>
            <h3 style="color: #e74c3c; border-bottom: 1px solid #ddd; padding-bottom: 10px;">Change Password</h3>
            
            <form action="profile.php" method="POST" style="margin-top: 20px;">
                
                <label for="current_password">Current Password</label>
                <input type="password" name="current_password" id="current_password" required placeholder="Enter current password">

                <label for="new_password" style="margin-top: 15px;">New Password</label>
                <input type="password" name="new_password" id="new_password" required minlength="8" placeholder="Enter new password">

                <label for="confirm_password" style="margin-top: 15px;">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required minlength="8" placeholder="Repeat new password">

                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <button type="submit" name="update_password" style="margin-top: 20px; background-color: #e74c3c;">Change Password</button>
            </form>
        </section>

    </section>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>