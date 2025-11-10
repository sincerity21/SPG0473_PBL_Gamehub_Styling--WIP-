<?php
session_start();
global $conn;
require 'hub_conn.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    
    if (empty($username)) {
        $error = "Please enter a username to process.";
    } else {
        // 1. Retrieve user data (ID and current answer)
        $userData = getSecurityAnswerForHashing($conn, $username);
        
        if (!$userData) {
            $error = "User not found or an error occurred. Check the console for database errors.";
        } else {
            $user_id = $userData['user_id'];
            
            // --- CRITICAL FIX: Trim the fetched database value and check for hash prefixes ---
            $old_answer = trim($userData['sec_answer']);
            
            // Function to check if the string starts with a recognized hash prefix (bcrypt, argon2)
            // If it starts with any of these, it's considered securely hashed.
            $is_already_hashed = (
                str_starts_with($old_answer, '$2y$') ||
                str_starts_with($old_answer, '$2a$') ||
                str_starts_with($old_answer, '$argon2i$') ||
                str_starts_with($old_answer, '$argon2id$')
            );


            if (!$is_already_hashed) {
                // The answer is plain text (does not start with a hash prefix). HASH IT.
                
                // Safety check for empty answer before hashing
                if (empty($old_answer)) {
                    $error = "Security answer for user '{$username}' is empty. Cannot hash an empty value.";
                } else {
                    // 2. Hash the existing plain text answer
                    $new_hashed_answer = password_hash($old_answer, PASSWORD_DEFAULT);
                    
                    // 3. Update the database
                    $success = updateHashedSecurityAnswer($conn, $user_id, $new_hashed_answer);
                    
                    if ($success) {
                        $message = "SUCCESS! Security answer for user '{$username}' has been securely hashed and updated.";
                    } else {
                        $error = "Database update failed for user '{$username}'. Check the console for database errors.";
                    }
                }
            } else {
                // The answer begins with a recognized hash prefix. Skip.
                $error = "Security answer for user '{$username}' is already securely hashed. No action taken.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hash Security Answer Tool</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #e6e6fa; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 500px; margin: 50px auto; padding: 30px; background-color: white; border-radius: 12px; box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15); }
        h2 { color: #8a2be2; text-align: center; margin-bottom: 25px; border-bottom: 2px solid #8a2be2; padding-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #555; }
        input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 16px; }
        .btn { width: 100%; padding: 12px; background-color: #8a2be2; color: white; border: none; border-radius: 6px; font-size: 18px; cursor: pointer; transition: background-color 0.3s; margin-top: 10px; }
        .btn:hover { background-color: #6a5acd; }
        .error { background-color: #fdd; color: #c00; padding: 15px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-weight: bold; border: 1px solid #f99;}
        .success { background-color: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-weight: bold; border: 1px solid #c3e6cb;}
        .note { margin-top: 20px; padding: 15px; background-color: #f0f0f0; border-radius: 6px; font-size: 14px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Security Answer Hashing Tool</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="note">
            This tool is for one-time use to convert **plain text** security answers in your database to **secure hashes** using `password_hash()`.
            This should only be necessary for users registered *before* the hashing feature was implemented.
        </div>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username to Hash Security Answer:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <button type="submit" class="btn">Process & Hash Answer</button>
        </form>
    </div>
</body>
</html>
