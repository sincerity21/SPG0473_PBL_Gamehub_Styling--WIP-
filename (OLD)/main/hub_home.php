<?php
session_start();
require '../hub_conn.php';

$login_error = '';
$register_error = '';
$forgot_step1_error = '';
$forgot_step2_error = '';
$reset_error = '';
$reset_success = '';
$login_register_success = '';

if ($_POST) {
    $action = $_POST['action'] ?? '';

    // For Login (uses modal; modal has no PHP)
    if ($action === 'login') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $result = loginUser($username, $password);
        if($result){
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['username'] = $result['user_username'];
            $_SESSION['email'] = $result['user_email'];
            if (isset($result['is_admin']) && $result['is_admin'] == 1) {
                $_SESSION['is_admin'] = true;
                header("Location: ../admin/user/hub_admin_user.php"); 
            } else {
                $_SESSION['is_admin'] = false;
                header("Location: logged_in/hub_home_logged_in.php");
            }
            exit(); 
        } else {
            $login_error = "Login Unsuccessful. Check your username and password.";
        }
    }

    // For Registration (uses modal)
    if ($action === 'register') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $prompt = $_POST['prompt'];
        $answer = $_POST['answer'];
        if (empty($username) || empty($email) || empty($password) || empty($answer)) {
            $register_error = "You must fill in all fields.";
        } else {
            $success = registerUser($username, $email, $password, $prompt, $answer);
            if ($success) {
                $login_register_success = "Registration successful! You can now log in.";
            } else {
                $register_error = "Registration failed. Username or email may already be in use.";
            }
        }
    }
    
    // For Forget Password (uses modal)
    if ($action === 'forgot_step1') {
        $username = trim($_POST['username']);
        // Input username
        if (!empty($username)) {
            // Gets username, check with database and see if it exists
            $userData = getUserResetData($conn, $username);
            if ($userData) {
                //  Success: Store data and let the page reload to show modal 2
                $_SESSION['temp_user_id'] = $userData['user_id'];
                $_SESSION['security_question'] = $userData['security_question'];
                $_SESSION['security_answer_hash'] = $userData['security_answer_hash'];
                $_SESSION['temp_username'] = $username;
            } else {
                // Username doesn't exist
                $forgot_step1_error = "Username not found. Please try again.";
            }
        } else {
            $forgot_step1_error = "Please enter your username.";
        }
    }
    
    // For Forget Password #2 (uses modal)
    if ($action === 'forgot_step2') {
        if (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['security_answer_hash'])) {
            $forgot_step1_error = "Session expired. Please start over.";
            //  Clear session just in case
            session_unset();
            session_destroy();
        } else {
            // Fetches the username's security answer
            $user_answer = trim($_POST['security_answer']);
            if (empty($user_answer)) {
                // User needs to input their 1-to-1 security answer
                $forgot_step2_error = "Please provide an answer to your security question.";
            } elseif (password_verify($user_answer, $_SESSION['security_answer_hash'])) {
                //  Success: Set auth flag and let page reload to show modal 3
                $_SESSION['auth_for_reset'] = true;
            } else {
                //  Failure: Destroy session and send back to step 1
                session_unset();
                session_destroy();
                $forgot_step1_error = "Incorrect security answer. Please start the reset process again.";
            }
        }
    }
    
    // For Reset Password (uses modal)
    if ($action === 'reset_password') {
        if (!isset($_SESSION['auth_for_reset']) || $_SESSION['auth_for_reset'] !== true || !isset($_SESSION['temp_user_id'])) {
            session_unset();
            session_destroy();
            $reset_error = "Security authorization lost. Please start over.";
        } else {
            // User input new password, and confirmation of new password
            $user_id = $_SESSION['temp_user_id'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];

            if (empty($new_password) || empty($confirm_password)) {
                $reset_error = "Both password fields are required.";
            } elseif ($new_password !== $confirm_password) {
                $reset_error = "The new password and confirmation password do not match.";
            } elseif (strlen($new_password) < 8) {
                $reset_error = "Password must be at least 8 characters long.";
            } else {
                // Password will be hashed
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_successful = updateUserPassword($conn, $user_id, $hashed_password);
                
                if ($update_successful) {
                    $reset_success = "Your password has been reset successfully!";
                    //  Clear all temporary session data
                    unset($_SESSION['temp_user_id']);
                    unset($_SESSION['security_question']);
                    unset($_SESSION['security_answer_hash']);
                    unset($_SESSION['temp_username']);
                    unset($_SESSION['auth_for_reset']);
                } else {
                    $reset_error = "A database error occurred. Please try again.";
                }
            }
        }
    }
}

// For Forget Password #2 (Logic Block, uses modal)
$resolved_question_text = 'Error: No question loaded.';
$greeting_text = 'Please answer your security question.';

if (isset($_SESSION['temp_user_id']) && isset($_SESSION['security_question']) && isset($_SESSION['temp_username'])) {
    // Fetches user's security answer
    $username = $_SESSION['temp_username'];
    $security_question = $_SESSION['security_question'];

    $default_question = "Your selected security question (not recognized by internal logic).";
    $default_greeting = "Hi $username, That's okay, it happens! Just answer the question below to confirm it's you and reset your password.";

    $resolved_question_text = $security_question; //  Default to the raw prompt code
    $greeting_text = $default_greeting;

    //  Converts the sec_question's internal name into actual questions
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
}
?>
<!DOCTYPE html>
<html lang="en"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameHub - Welcome</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /*Variables for Theming*/
        :root {
            /* Light Mode Defaults */
            --bg-color: #f4f7f6;
            --main-text-color: #333;
            --accent-color: #3498db;
            --secondary-text-color: #7f8c8d;
            --card-bg-color: white;
            --shadow-color: rgba(0, 0, 0, 0.05);
            --wave-opacity: 0.15;
            --welcome-title-color: #2c3e50;
            --login-color: #2ecc71;
            --border-color: #ccc;
        }

        /* Dark Mode Override */
        html.dark-mode body {
            --bg-color: #121212;
            --main-text-color: #f4f4f4;
            --accent-color: #4dc2f9;
            --secondary-text-color: #95a5a6;
            --card-bg-color: #1e1e1e;
            --shadow-color: rgba(0, 0, 0, 0.4);
            --wave-opacity: 0.05;
            --welcome-title-color: #ecf0f1;
            --login-color: #27ae60;
            --border-color: #444;
        }


        /* Base Setup */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-color);
            color: var(--main-text-color);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: background-color 0.3s, color 0.3s; /* Smooth transition */
        }

        
        /* Header (Top Bar) */
        .header {
            background-color: var(--card-bg-color);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px var(--shadow-color);
        }
        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--accent-color);
        }

        /* Menu Toggle Button */
        .menu-toggle {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 24px;
            color: var(--main-text-color);
            padding: 5px;
            transition: color 0.2s;
        }
        .menu-toggle:hover {
            color: var(--accent-color);
        }

        /* Side Menu Styles*/
        .side-menu {
            position: fixed;
            top: 60px; /* Below the header */
            right: 0;
            width: 220px;
            background-color: var(--card-bg-color);
            box-shadow: -4px 4px 8px var(--shadow-color);
            border-radius: 8px 0 8px 8px;
            padding: 10px 0;
            z-index: 1000;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        }
        .side-menu.open {
            transform: translateX(0);
        }
        .side-menu a, .menu-item {
            display: block;
            padding: 12px 20px;
            color: var(--main-text-color);
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.2s, color 0.2s;
            cursor: pointer;
        }
        .side-menu a:hover, .menu-item:hover {
            background-color: var(--bg-color);
            color: var(--accent-color);
        }
        .side-menu a.active { 
            background-color: var(--accent-color); 
            color: white; font-weight: bold; }
        .side-menu a.active:hover { 
            background-color: #2980b9; 
        }
        .side-menu a.login-link {
            color: var(--login-color) !important;
            font-weight: bold;
        }
        .side-menu a.login-link:hover {
            background-color: var(--bg-color);
            color: #2ecc71 !important;
        }
        .menu-divider {
            border-top: 1px solid var(--secondary-text-color);
            margin: 5px 0;
        }
        .icon {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }


        /* Main Content Area */
        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 50px 20px;
            position: relative;
        }
        .welcome-title {
            font-size: 3.5em;
            font-weight: 600;
            color: var(--welcome-title-color);
            margin-bottom: 10px;
        }
        .welcome-subtitle {
            font-size: 1.2em;
            color: var(--secondary-text-color);
            margin-bottom: 40px;
        }
        .continue-button {
            padding: 15px 40px;
            background: #e74c3c; /* Red color (like 'START' button) */
            color: white;
            text-decoration: none;
            border: 2px solid #c0392b;
            border-radius: 6px;
            font-size: 1.2em;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease-in-out;
            margin-top: 50px;
        }
        .continue-button:hover {
            background: #c0392b;
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.25);
            transform: translateY(-2px);
        }

        /* Wave Separator*/
        .wave-container {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 150px; /* Space for the wave */
            overflow: hidden;
            z-index: 1;
        }
        .wave {
            position: absolute;
            width: 200%;
            height: 200%;
            background: var(--accent-color);
            border-radius: 40%;
            bottom: -150%; 
            left: -50%;
            opacity: var(--wave-opacity);
            animation: wave-motion 10s linear infinite;
        }
        .wave:nth-child(2) {
            opacity: calc(var(--wave-opacity) / 1.5);
            animation: wave-motion 15s linear infinite reverse;
            bottom: -160%;
            border-radius: 45%;
        }

        @keyframes wave-motion {
            0% { transform: translate(0, 0); }
            50% { transform: translate(-25%, 5%); }
            100% { transform: translate(0, 0); }
        }
        
        /* Dark Mode Styling*/
        .dark-mode-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            user-select: none;
        }
        .dark-mode-label .icon {
            font-size: 1.2em;
        }
        
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 2000;
            display: none; /* Hidden by default */
            align-items: center;
            justify-content: center;
            overflow-y: auto; /* Allow scrolling if modal is tall */
        }
        .modal-container {
            background-color: var(--card-bg-color);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
            width: 100%;
            max-width: 500px; /* Width of register modal */
            color: var(--main-text-color); 
            margin: 20px; /* Add margin for small screens */
        }

        .modal-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--secondary-text-color);
        }

        .modal-container h2 {
            color: var(--welcome-title-color);
            text-align: center;
            margin-top: 0;
            margin-bottom: 25px;
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 10px;
        }
        .modal-container .form-group { 
            margin-bottom: 20px; 
        }
        .modal-container label { 
            display: block; 
            margin-bottom: 8px; 
            font-weight: bold;
            color: var(--secondary-text-color);
        }
        .modal-container input[type="text"],
        .modal-container input[type="email"],
        .modal-container input[type="password"],
        .modal-container select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px; 
            box-sizing: border-box; 
            font-size: 16px;
            background-color: var(--bg-color);
            color: var(--main-text-color);
        }

        .modal-container .btn {
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
        .modal-container .btn:hover {
            background-color: #2980b9;
        }
        .modal-container .error {
            background-color: #fdd; 
            color: #c00; 
            padding: 10px; 
            border: 1px solid #f99;
            border-radius: 4px;
            margin-bottom: 15px; 
            text-align: center;
            font-weight: bold;
        }
        .modal-container .success { 
            background-color: #d4edda; 
            color: #155724; 
            padding: 10px; 
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            margin-bottom: 15px; 
            text-align: center;
            font-weight: bold;
        }
        .modal-container .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .modal-container .register-link a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }
        .modal-container .register-link a:hover {
            text-decoration: underline;
        }
        .modal-container .forgot-link {
            text-align: right;
            margin-top: -15px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .modal-container .forgot-link a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: bold;
        }
        .modal-container .greeting {
             margin-bottom: 25px; 
             line-height: 1.4; 
        }
        .modal-container .prompt { 
            font-size: 1.1em; 
            font-weight: bold; 
            margin-bottom: 15px; 
        }
        .modal-container input[readonly] {
            background-color: var(--bg-color);
            opacity: 0.7;
        }

    </style>

    <script>
        // Local Storage; Essential for Dark Mode Fix
        (function() {
            const localStorageKey = 'gamehubDarkMode'; 
            if (localStorage.getItem(localStorageKey) === 'dark') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
    </head>
<body id="appBody"> <div class="header">
    <div class="logo">GAMEHUB</div>
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>
</div>

<!-- Side Menu -->
<div class="side-menu" id="sideMenu">
    
    <a href="hub_home.php" class="active"><span class="icon"><i class="fas fa-home"></i></span>Home</a>
    <a href="hub_home_category.php"><span class="icon"><i class="fas fa-book-open"></i></span>Library</a>
    <a href="hub_main_about.php"><span class="icon"><i class="fas fa-info-circle"></i></span>About</a>

    <div class="menu-divider"></div>
    
    <a href="#" class="login-link" onclick="openModal('loginModal')"><span class="icon"><i class="fas fa-sign-in-alt"></i></span>Login</a>
    
    <div class="menu-divider"></div>
    
    <div class="menu-item dark-mode-label" onclick="toggleDarkMode()">
        <span class="icon"><i class="fas fa-moon"></i></span>
        <span id="darkModeText">Switch Dark Mode</span>
    </div>
    
</div>

<div class="main-content">
    <h1 class="welcome-title">WELCOME</h1>
    <p class="welcome-subtitle">
        This is the GameHub, where you can rate your favourite games.
    </p>

    <a href="hub_home_category.php" class="continue-button">START</a>

    <div class="wave-container">
        <div class="wave"></div>
        <div class="wave"></div>
    </div>
</div>

<?php
    // Included relevant modals
    include '../hub_login.php';
    include '../hub_register.php';
    include '../hub_forgotpassword.php'; // Step 1
    include '../hub_forgotpassword2.php'; // Step 2
    include '../hub_resetpassword.php'; // Step 3
?>

<script>
    document.getElementById('menuToggle').addEventListener('click', function() {
        const menu = document.getElementById('sideMenu');
        menu.classList.toggle('open');
    });

    // Updated Dark Mode
    const darkModeText = document.getElementById('darkModeText');
    const localStorageKey = 'gamehubDarkMode';
    const htmlElement = document.documentElement; //  Target the <html> tag

    function applyDarkMode(isDark) {
        if (isDark) {
            htmlElement.classList.add('dark-mode');
            if (darkModeText) darkModeText.textContent = 'Switch Light Mode';
        } else {
            htmlElement.classList.remove('dark-mode');
            if (darkModeText) darkModeText.textContent = 'Switch Dark Mode';
        }
    }

    // Function toggles  mode
    function toggleDarkMode() {
        const isDark = htmlElement.classList.contains('dark-mode');

        //  Toggle the state
        applyDarkMode(!isDark);

        //  Save preference to local storage
        localStorage.setItem(localStorageKey, !isDark ? 'dark' : 'light');
    }

    (function loadButtonText() {
        const isDark = htmlElement.classList.contains('dark-mode');
        applyDarkMode(isDark);
    })();

    //  Modal's Javascript
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'flex';
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'none';
    }
    
    function switchToModal(fromModalId, toModalId) {
        closeModal(fromModalId);
        openModal(toModalId);
    }
    
    <?php if (!empty($login_error)): ?>
        openModal('loginModal');
    <?php elseif (!empty($register_error)): ?>
        openModal('registerModal');
    <?php elseif (!empty($login_register_success)): ?> 
        openModal('loginModal');
    <?php elseif (!empty($forgot_step1_error)): ?>
        openModal('forgotPasswordModal');
    <?php elseif (!empty($forgot_step2_error)): ?>
        openModal('forgotPasswordModal2');
    <?php elseif (!empty($reset_error) || !empty($reset_success)): ?>
        openModal('resetPasswordModal');
    <?php elseif (isset($_SESSION['auth_for_reset']) && $_SESSION['auth_for_reset'] === true): ?>
        //  Successful step 2, show step 3
        openModal('resetPasswordModal');
    <?php elseif (isset($_SESSION['temp_user_id'])): ?>
        //  Successful step 1, show step 2
        openModal('forgotPasswordModal2');
    <?php endif; ?>
</script>

</body>
</html>