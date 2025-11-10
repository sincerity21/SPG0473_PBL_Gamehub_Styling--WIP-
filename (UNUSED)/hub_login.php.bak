<?php
session_start();
require 'hub_conn.php';

$error = '';

if ($_POST) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 1. Attempt to log in the user using the secure loginUser function
    // This function handles password verification using password_verify()
    $result = loginUser($username, $password);

    if($result){
        // Login Successful (for either a standard user or an admin)
        $_SESSION['user_id'] = $result['user_id'];
        $_SESSION['username'] = $result['user_username'];
        $_SESSION['email'] = $result['user_email'];

        // 2. Check the user's role using the 'is_admin' column
        if (isset($result['is_admin']) && $result['is_admin'] == 1) {
            // Admin User
            $_SESSION['is_admin'] = true;
            // Redirect Admin to the index/user listing page
            header("Location: admin/user/hub_admin_user.php"); 
        } else {
            // Standard User
            $_SESSION['is_admin'] = false;
            // Redirect standard users to hub_home_logged_in.php
            header("Location: main/logged_in/hub_home_logged_in.php");
        }
        exit(); 
    }
    else{
        // Login Unsuccessful
        $error = "Login Unsuccessful. Check your username and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Game Hub</title>
    <style>
        /* Consistent Body and Font */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7f6;
            color: #333;
        }
        
        /* Consistent Container for Form */
        .container { 
            max-width: 400px;
            margin: 80px auto; 
            padding: 30px; 
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Consistent Heading */
        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        
        /* Form Grouping and Labels */
        .form-group { 
            margin-bottom: 20px; 
        }
        label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: bold;
            color: #555;
        }
        
        /* Input Styling */
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; 
            font-size: 16px;
        }
        
        /* Button Styling (Consistent blue accent) */
        .btn {
            width: 100%;
            padding: 12px;
            background-color: #3498db; 
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }
        .btn:hover {
            background-color: #2980b9; 
        }
        
        /* Error Styling */
        .error {
            background-color: #fdd; 
            color: #c00; 
            padding: 10px; 
            border: 1px solid #f99;
            border-radius: 4px;
            margin-bottom: 15px; 
            text-align: center;
            font-weight: bold;
        }
        
        /* Register Link Styling */
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .register-link a, .forgot-link a { /* Added .forgot-link a */
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
        .register-link a:hover, .forgot-link a:hover { /* Added .forgot-link a:hover */
            text-decoration: underline;
        }

        /* NEW: Forgot Password Link Styling */
        .forgot-link {
            text-align: right; /* Align link to the right */
            margin-top: -15px; /* Pull it closer to the password field */
            margin-bottom: 20px;
            font-size: 13px;
        }

    </style>
</head>
<body>
    <div class="container">
        <h2>Login to Game Hub</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="forgot-link">
                <a href="hub_forgotpassword.php">Forget password?</a>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="register-link">
            <p>Don't have an account? <a href="hub_register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>