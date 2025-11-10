<?php
session_start();
global $conn; // Declare $conn for use inside the script's global scope
require 'hub_conn.php';

$error = '';
$success = '';

// --- SECURITY CHECK: Ensure the user is authorized to reset ---
if (!isset($_SESSION['auth_for_reset']) || $_SESSION['auth_for_reset'] !== true || !isset($_SESSION['temp_user_id'])) {
    // If authorization flags are missing, redirect them to start the process over
    header("Location: hub_forgotpassword.php?err=" . urlencode("Security checks failed. Please start the password reset process again."));
    exit();
}

$user_id = $_SESSION['temp_user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Validate Password Input
    if (empty($new_password) || empty($confirm_password)) {
        $error = "Both password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "The new password and confirmation password do not match.";
    } elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else {
        // 2. Hash the new password securely
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // 3. Update the database using the secure function
        $update_successful = updateUserPassword($conn, $user_id, $hashed_password);
        
        if ($update_successful) {
            $success = "Your password has been reset successfully! You can now log in with your new password.";
            
            // --- CRITICAL SECURITY STEP: Destroy Authorization Flags ---
            unset($_SESSION['temp_user_id']);
            unset($_SESSION['security_question']);
            unset($_SESSION['security_answer_hash']);
            unset($_SESSION['temp_username']);
            unset($_SESSION['auth_for_reset']);
            // Session variables are destroyed to prevent the link from being reused.

            // After success, redirect to login page (optional: wait for user to read message)
            header("refresh:5; url=hub_login.php"); // Redirect after 5 seconds
        } else {
            $error = "A database error occurred while trying to update your password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Step 3</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f7f6; color: #333; }
        .container { max-width: 400px; margin: 80px auto; padding: 30px; background-color: white; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        h2 { color: #2c3e50; text-align: center; margin-bottom: 25px; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 16px; }
        .btn { width: 100%; padding: 12px; background-color: #2ecc71; color: white; border: none; border-radius: 4px; font-size: 18px; cursor: pointer; transition: background-color 0.3s; margin-top: 10px; }
        .btn:hover { background-color: #27ae60; }
        .error { background-color: #fdd; color: #c00; padding: 10px; border: 1px solid #f99; border-radius: 4px; margin-bottom: 15px; text-align: center; font-weight: bold; }
        .success { background-color: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 15px; text-align: center; font-weight: bold; }
        .login-link { text-align: center; margin-top: 20px; font-size: 14px; }
        .login-link a { color: #3498db; text-decoration: none; font-weight: bold; }
        .login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Set New Password</h2>
        
        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
            <div class="login-link">
                <p>Redirecting to login in 5 seconds... <a href="hub_login.php">Click here to go now</a></p>
            </div>
        <?php else: ?>

            <p style="text-align: center; color: #555;">Please enter and confirm your new password.</p>

            <?php if ($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
