<?php
session_start();

// We need to declare $conn as global here so it becomes available 
// in this script's scope after hub_conn.php creates it.
global $conn; 
require 'hub_conn.php'; 

$error = '';

// Check if an error message was passed from hub_forgotpassword2.php
if (isset($_GET['err'])) {
    $error = htmlspecialchars($_GET['err']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);

    if (!empty($username)) {
        // 1. Fetch user data securely, passing the $conn connection object
        $userData = getUserResetData($conn, $username);

        if ($userData) {
            // Success: User found. Store the necessary data in the session
            $_SESSION['temp_user_id'] = $userData['user_id'];
            $_SESSION['security_question'] = $userData['security_question'];
            $_SESSION['security_answer_hash'] = $userData['security_answer_hash'];
            $_SESSION['temp_username'] = $username;
            
            // 2. Redirect to the security question verification page
            header("Location: hub_forgotpassword2.php");
            exit();
        } else {
            // Failure: User not found or question data missing.
            // Use a generic error message to prevent username enumeration.
            $error = "If your account exists, you will be prompted with a security question on the next screen.";
        }
    } else {
        $error = "Please enter your username.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Step 1</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f7f6; color: #333; }
        .container { max-width: 400px; margin: 80px auto; padding: 30px; background-color: white; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        h2 { color: #2c3e50; text-align: center; margin-bottom: 25px; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 16px; }
        .btn { width: 100%; padding: 12px; background-color: #3498db; color: white; border: none; border-radius: 4px; font-size: 18px; cursor: pointer; transition: background-color 0.3s; margin-top: 10px; }
        .btn:hover { background-color: #2980b9; }
        .error { background-color: #fdd; color: #c00; padding: 10px; border: 1px solid #f99; border-radius: 4px; margin-bottom: 15px; text-align: center; font-weight: bold; }
        .back-link { text-align: center; margin-top: 20px; font-size: 14px; }
        .back-link a { color: #3498db; text-decoration: none; font-weight: bold; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <p style="text-align: center; color: #555;">Enter your username to begin the password reset process.</p>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <button type="submit" class="btn">Continue to Security Check</button>
        </form>

        <div class="back-link">
            <p><a href="hub_login.php">Back to Login</a></p>
        </div>
    </div>
</body>
</html>
