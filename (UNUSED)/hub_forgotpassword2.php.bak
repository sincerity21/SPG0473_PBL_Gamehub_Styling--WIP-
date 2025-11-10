<?php
session_start();
global $conn;
require 'hub_conn.php';

$error = '';

// --- SECURITY CHECK: Ensure user has passed Step 1 (Identification) ---
// Checks if the necessary session variables are set by hub_forgotpassword.php
if (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['security_question']) || !isset($_SESSION['security_answer_hash'])) {
    // If session data is missing, redirect them to start the process over
    header("Location: hub_forgotpassword.php?err=" . urlencode("Session data lost or unauthorized access. Please start the reset process again."));
    exit();
}

// Extract data from session
$username = $_SESSION['temp_username'];
$security_question = $_SESSION['security_question']; // e.g., 'prompt_1'
$security_answer_hash = $_SESSION['security_answer_hash']; // NOTE: This holds the HASHED answer from the database
$user_id = $_SESSION['temp_user_id'];

// --- Conditional Question & Greeting Logic (Based on your requests) ---
$default_question = "Your selected security question (not recognized by internal logic).";
$default_greeting = "Hi $username, That's okay, it happens! Just answer the question below to confirm it's you and reset your password.";

$resolved_question_text = $security_question; // Default to the raw prompt code
$greeting_text = $default_greeting;

// Map the prompt codes to human-readable questions and greetings
switch (strtolower(trim($security_question))) {
    case 'prompt_1':
        $resolved_question_text = "What is love?";
        $greeting_text = "Hello $username, Welcome back! To prove your identity, please answer the secret question.";
        break;
    case 'prompt_2':
        $resolved_question_text = "Who will never give you up?";
        $greeting_text = "Hi $username! We're here to help you reset your password. Please verify your account by answering the question below.";
        break;
    case 'prompt_3':
        $resolved_question_text = "Who is Franz Hermann?";
        $greeting_text = "Hey $username! Let's get you set up with a new password. Answer your security question below.";
        break;
    case 'prompt_4':
        $resolved_question_text = "Who will win the 2025 Formula 1 World's Drivers Championship, and why is it Max Verstappen?";
        $greeting_text = "Greetings $username! Account recovery in progress. Please provide the answer to your question.";
        break;
    case 'prompt_5':
        $resolved_question_text = "How?";
        $greeting_text = "Salutations $username! One last step for identity confirmation: please answer the security question.";
        break;
    default:
        $resolved_question_text = $default_question;
        $greeting_text = $default_greeting;
        break;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and trim the user's input answer
    $user_answer = trim($_POST['security_answer']);

    if (empty($user_answer)) {
        $error = "Please provide an answer to your security question.";
    } else {
        // --- CRITICAL STEP: Secure Verification ---
        // Compare the submitted plain text answer against the stored HASH using password_verify().
        // This is case-sensitive, which is correct for security answers.
        if (password_verify($user_answer, $security_answer_hash)) {
            
            // Success: Set authorization flag and redirect to final reset screen
            $_SESSION['auth_for_reset'] = true;
            
            // We keep temp_user_id because it is needed in hub_resetpassword.php
            header("Location: hub_resetpassword.php");
            exit();

        } else {
            // Failure: Destroy all session data to force restart and prevent brute-force attempts
            session_unset();
            session_destroy();

            // Redirect back to step 1 with an error
            header("Location: hub_forgotpassword.php?err=" . urlencode("Incorrect security answer. Please try the reset process again."));
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Step 2</title>
    <style>
        /* Styles adapted from your previous request for consistency */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f7f6; color: #333; }
        .container { max-width: 450px; margin: 80px auto; padding: 30px; background-color: #3498db; border-radius: 8px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); color: white; }
        h2 { color: #f4f7f6; text-align: center; margin-bottom: 25px; border-bottom: 2px solid #fff; padding-bottom: 10px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; color: #f4f7f6; }
        /* Input styling for user's answer */
        input[type="text"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-size: 16px; color: #333; }
        /* Input styling for the READ-ONLY question display */
        #security_question_display { background-color: #3498db; color: white; border-color: #fff; }
        .btn { width: 100%; padding: 12px; background-color: #2ecc71; color: white; border: none; border-radius: 4px; font-size: 18px; cursor: pointer; transition: background-color 0.3s; margin-top: 20px; }
        .btn:hover { background-color: #27ae60; }
        .error { background-color: #e74c3c; color: white; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-weight: bold; }
        .prompt { font-size: 1.1em; font-weight: bold; margin-bottom: 15px; }
        .greeting { margin-bottom: 25px; line-height: 1.4; }
    </style>
</head>
<body>
    <div class="container">
        <h2>FORGOT YOUR PASSWORD?</h2>
        
        <div class="greeting"><?php echo $greeting_text; ?></div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="security_question_display" class="prompt">Security Prompt (Question):</label>
                <!-- Displays the full, human-readable question, read-only -->
                <input type="text" id="security_question_display" value="<?php echo htmlspecialchars($resolved_question_text); ?>" readonly>
            </div>
            
            <div class="form-group">
                <label for="security_answer">Security Answer:</label>
                <!-- Input field for the user to type their answer -->
                <input type="text" id="security_answer" name="security_answer" placeholder="ni user letak sendiri" required>
            </div>
            
            <button type="submit" class="btn">RESET PASSWORD</button>
        </form>
    </div>
</body>
</html>
