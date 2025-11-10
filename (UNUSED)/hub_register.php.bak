<?php
require 'hub_conn.php';

$error = '';

if($_POST){
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $server = $_POST['server'];
    $prompt = $_POST['prompt'];
    $answer = $_POST['answer'];
    
    // NOTE: It is critical that your registerUser function uses prepared statements 
    // and ideally hashes the password correctly, as implied by the commented code in hub_conn.php.
    $success = registerUser($username, $email, $password, $server, $prompt, $answer);
    
    if (empty($username) || empty($email) || empty($password) || empty($answer)) {
        echo "<script>alert('You must fill in all fields');</script>";
    }
    else if ($success) {
        header('Location: hub_login.php');
        exit();
    } else {
        $error = "Registration failed. Username or email may already be in use.";
    }

}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Game Hub</title>
    <style>
        /* Consistent Body and Font */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7f6; /* Consistent Background */
            color: #333;
        }
        
        /* Consistent Container for Form */
        .container { 
            max-width: 500px; /* Slightly wider for more fields */
            margin: 50px auto; 
            padding: 30px; 
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Consistent Shadow */
        }
        
        /* Consistent Heading */
        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #3498db; /* Consistent Accent Line */
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
        
        /* Input, Select, and Textarea Styling */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select {
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
            background-color: #3498db; /* Consistent Blue */
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
        
        /* Error Styling (Consistent red) */
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
        
        /* Login Link Styling */
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .register-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register for Game Hub</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" minlength="3" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" minlength="8" required>
            </div>

            <div class="form-group">
                <label for="server">Preferred Server Region:</label>
                <select name="server" id="server" required>
                    <!-- Ensure input type is text for email field -->
                    <option value="seas">Southeast Asia</option>
                    <option value="east">East Asia</option>
                    <option value="aus">Oceania</option>
                    <option value="mea">Middle East</option>
                    <option value="safrica">South Africa</option>
                    <option value="euwest">West Europe</option>
                    <option value="eunorth">North Europe</option>
                    <option value="nawest">West N. America</option>
                    <option value="naeast">East N. America</option>
                    <option value="nacentral">Central N. America</option>
                    <option value="southmerica">South Americas</option>
                </select>
            </div>

            <div class="form-group">
                <label for="prompt">Security Prompt (Question):</label>
                <select name="prompt" id="prompt" required>
                    <option value="prompt_1">What is love?</option>
                    <option value="prompt_2">Who will never give you up?</option>
                    <option value="prompt_3">Who is Franz Hermann?</option>
                    <option value="prompt_4">Who will win the 2025 Formula 1 World's Drivers Championship, and why is it Max Verstappen?</option>
                    <option value="prompt_5">How?</option>
                </select>
            </div>

            <div class="form-group">
                <label for="answer">Security Answer:</label>
                <input type="text" id="answer" name="answer" required>
            </div>
            
            <button type="submit" class="btn">Register</button>
        </form>

        <div class="register-link">
            <p>Already have an account? <a href="hub_login.php">Login here</a></p>
        </div>
        
    </div>
</body>
</html>