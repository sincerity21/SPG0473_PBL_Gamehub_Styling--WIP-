<?php
session_start();
require '../hub_conn.php'; 

$categories = selectAllGameCategories();
$games = selectAllGamesWithCovers(); 

$login_error = '';
$register_error = '';
$forgot_step1_error = '';
$forgot_step2_error = '';
$reset_error = '';
$reset_success = '';
$login_register_success = '';

if ($_POST) {
    
    $action = $_POST['action'] ?? '';

    
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
                
                header("Location: logged_in/hub_category_logged_in.php"); 
            }
            exit(); 
        } else {
            $login_error = "Login Unsuccessful. Check your username and password.";
        }
    }

    
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

    
    if ($action === 'forgot_step1') {
        $username = trim($_POST['username']);
        if (!empty($username)) {
            $userData = getUserResetData($conn, $username);
            if ($userData) {
                
                $_SESSION['temp_user_id'] = $userData['user_id'];
                $_SESSION['security_question'] = $userData['security_question'];
                $_SESSION['security_answer_hash'] = $userData['security_answer_hash'];
                $_SESSION['temp_username'] = $username;
            } else {
                $forgot_step1_error = "Username not found. Please try again.";
            }
        } else {
            $forgot_step1_error = "Please enter your username.";
        }
    }
    
    
    if ($action === 'forgot_step2') {
        if (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['security_answer_hash'])) {
            $forgot_step1_error = "Session expired. Please start over.";
            
            session_unset();
            session_destroy();
        } else {
            $user_answer = trim($_POST['security_answer']);
            if (empty($user_answer)) {
                $forgot_step2_error = "Please provide an answer to your security question.";
            } elseif (password_verify($user_answer, $_SESSION['security_answer_hash'])) {
                
                $_SESSION['auth_for_reset'] = true;
            } else {
                
                session_unset();
                session_destroy();
                $forgot_step1_error = "Incorrect security answer. Please start the reset process again.";
            }
        }
    }
    
    
    if ($action === 'reset_password') {
        if (!isset($_SESSION['auth_for_reset']) || $_SESSION['auth_for_reset'] !== true || !isset($_SESSION['temp_user_id'])) {
            session_unset();
            session_destroy();
            $reset_error = "Security authorization lost. Please start over.";
        } else {
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
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_successful = updateUserPassword($conn, $user_id, $hashed_password);
                
                if ($update_successful) {
                    $reset_success = "Your password has been reset successfully!";
                    
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


$resolved_question_text = 'Error: No question loaded.';
$greeting_text = 'Please answer your security question.';

if (isset($_SESSION['temp_user_id']) && isset($_SESSION['security_question']) && isset($_SESSION['temp_username'])) {
    
    $username = $_SESSION['temp_username'];
    $security_question = $_SESSION['security_question'];
    $default_question = "Your selected security question (not recognized by internal logic).";
    $default_greeting = "Hi $username, That's okay, it happens! Just answer the question below to confirm it's you and reset your password.";
    $resolved_question_text = $security_question;
    $greeting_text = $default_greeting;

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


$fallback_cover = 'uploads/placeholder.png'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameHub - Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kalam:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-color: #f4f7f6;
            --main-text-color: #333;
            --accent-color: #9c1809ff;
            --accent-color-darker: #801407;
            --secondary-text-color: #7f8c8d;
            --card-bg-color: white;
            --shadow-color: rgba(0, 0, 0, 0.05);
            --border-color: #ddd;
            --welcome-title-color: #2c3e50;
            --login-color: #2ecc71;
            --glass-bg-light: rgba(255, 255, 255, 0.7);
            --glass-bg-dark: rgba(30, 30, 30, 0.7);
            --star-color: #f39c12;
            --heart-color: #e74c3c;
        }

        html.dark-mode body {
            --bg-color: #121212;
            --main-text-color: #f4f4f4;
            --accent-color: #f39c12;
            --accent-color-darker: #c87f0a;
            --secondary-text-color: #95a5a6;
            --card-bg-color: #1e1e1e;
            --shadow-color: rgba(0, 0, 0, 0.4);
            --border-color: #444;
            --welcome-title-color: #ecf0f1;
            --login-color: #27ae60;
        }

        .background-image {
            position: fixed;
            top: -10px;
            left: -10px;
            width: calc(100% + 20px);
            height: calc(100% + 20px);
            z-index: -1;
            background-size: cover;
            background-position: center;
            filter: blur(5px);
            transition: opacity 0.5s ease-in-out;
            background-color: var(--bg-color);
        }

        #bg-light {
            background-image: url('../uploads/home/prototype.jpg');
            opacity: 1;
        }

        #bg-dark {
            background-image: url('../uploads/home/darksouls.jpg');
            opacity: 0;
        }

        html.dark-mode body #bg-light {
            opacity: 0;
        }

        html.dark-mode body #bg-dark {
            opacity: 1;
        }

        html.dark-mode body .header,
        html.dark-mode body .side-menu {
            background-color: var(--glass-bg-dark);
        }
        
        html.dark-mode body .content-container {
             background-color: transparent;
             box-shadow: none;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: var(--main-text-color);
            min-height: 100vh;
            transition: background-color 0.3s, color 0.3s;
        }

        .header {
            background-color: var(--glass-bg-light);
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px var(--shadow-color);
            position: sticky;
            top: 0;
            z-index: 1001;
            transition: background-color 0.3s;
        }

        .logo {
            font-size: 24px;
            font-weight: 700;
            color: var(--accent-color);
            text-decoration: none;
        }

        .menu-toggle {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 24px;
            color: var(--main-text-color);
            padding: 5px;
        }

        .side-menu {
            position: fixed;
            top: 60px;
            right: 0;
            width: 220px;
            background-color: var(--glass-bg-light);
            backdrop-filter: blur(10px);
            box-shadow: -4px 4px 8px var(--shadow-color);
            border-radius: 8px 0 8px 8px;
            padding: 10px 0;
            z-index: 1000;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out, background-color 0.3s;
        }

        .side-menu.open {
            transform: translateX(0);
        }

        .side-menu a,
        .menu-item {
            display: block;
            padding: 12px 20px;
            color: var(--main-text-color);
            text-decoration: none;
            transition: background-color 0.2s;
            cursor: pointer;
        }

        .side-menu a:hover,
        .menu-item:hover {
            background-color: var(--bg-color);
            color: var(--accent-color);
        }

        .side-menu a.active {
            background-color: var(--accent-color);
            color: white;
            font-weight: bold;
        }

        .side-menu a.active:hover {
            background-color: var(--accent-color);
            filter: brightness(0.85);
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
            display: none;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
        }

        .modal-container {
            background-color: var(--card-bg-color);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
            width: 100%;
            max-width: 500px;
            color: var(--main-text-color);
            margin: 20px;
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

        #loginModal .login-form {
            display: flex;
            flex-direction: column;
            text-align: left;
            width: 100%;
        }

        #loginModal .form-links {
            text-align: right;
            margin-top: -10px;
            margin-bottom: 15px;
        }

        #loginModal .form-links a {
            font-size: 0.9rem;
            text-decoration: none;
            color: var(--accent-color);
            font-weight: bold;
            cursor: pointer;
        }

        #loginModal .form-links a:hover {
            text-decoration: underline;
        }

        #registerModal .sketch-form {
            display: flex;
            flex-direction: column;
            text-align: left;
            width: 100%;
            max-height: 60vh;
            overflow-y: auto;
            padding-right: 10px;
        }
        
        #forgotPasswordModal .btn {
            background-color: #2ecc71;
        }
        #forgotPasswordModal .btn:hover {
            background-color: #27ae60;
        }

        #resetPasswordModal .btn {
            background-color: #2ecc71;
        }
        #resetPasswordModal .btn:hover {
            background-color: #27ae60;
        }

        .content-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
        }

        .category-title {
            font-size: 1.5em;
            font-weight: 600;
            color: var(--welcome-title-color);
            margin-top: 20px;
            margin-bottom: 20px;
            text-align: left;
            border-bottom: 3px solid var(--accent-color);
            display: inline-block;
            padding-bottom: 5px;
        }

        .category-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 30px;
        }

        .filter-btn {
            padding: 8px 18px;
            font-size: 1em;
            font-weight: 500;
            color: var(--secondary-text-color);
            background-color: var(--bg-color);
            border: 2px solid var(--border-color);
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-btn:hover {
            color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .filter-btn.active {
            color: var(--accent-text-color, white);
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            font-weight: bold;
        }

        .game-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .game-card {
            background-color: var(--card-bg-color);
            border-radius: 8px;
            box-shadow: 0 4px 8px var(--shadow-color);
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            text-decoration: none;
            color: var(--main-text-color);
            cursor: pointer;
        }

        .game-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px var(--shadow-color);
        }

        .game-card img {
            width: 100%;
            height: auto;
            aspect-ratio: 460 / 215;
            object-fit: cover;
            display: block;
        }
        
        #gameDetailModal .modal-container {
            max-width: 1000px;
            background-color: var(--glass-bg-light);
            backdrop-filter: blur(10px);
        }
        
        html.dark-mode body #gameDetailModal .modal-container {
             background-color: var(--glass-bg-dark);
        }

        #gameDetailModal .modal-close {
            position: absolute;
            top: 10px;
            left: 15px;
            right: auto;
            font-size: 28px;
            font-weight: bold;
            color: var(--main-text-color);
            background: none;
            border: none;
            cursor: pointer;
            text-decoration: none;
            line-height: 1;
        }
        
        #gameDetailModal .modal-close:hover {
            color: var(--accent-color);
        }

        .game-detail-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: flex-start;
            margin-top: 30px;
        }

        .image-slideshow {
            position: relative;
            width: 100%;
            height: 0;
            padding-bottom: 56.25%;
            overflow: hidden;
            border-radius: 8px;
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
        }

        .slide.active {
            opacity: 1;
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .slider-control {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.4);
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            z-index: 10;
            font-size: 1.5em;
        }

        .slider-control:hover {
            background: rgba(0, 0, 0, 0.6);
        }

        .prev {
            left: 0;
            border-radius: 0 5px 5px 0;
        }

        .next {
            right: 0;
            border-radius: 5px 0 0 5px;
        }

        .slide-indicators {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
            display: flex;
            gap: 5px;
        }

        .dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            cursor: pointer;
            transition: background 0.3s;
        }

        .dot.active {
            background: white;
        }

        .game-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .game-title-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
        }

        .game-title {
            font-size: 2.5em;
            font-weight: 600;
            color: var(--welcome-title-color);
            margin: 0;
            line-height: 1.1;
        }

        .game-desc {
            font-size: 1.1em;
            color: var(--main-text-color);
            line-height: 1.6;
        }

        html.dark-mode body .game-desc { 
            color: var(--secondary-text-color); 
        }

        .favorite-icon {
            font-size: 2.5em;
            color: var(--border-color);
            cursor: pointer;
            transition: color 0.2s, transform 0.2s;
        }

        .favorite-icon.active {
            color: var(--heart-color);
        }

        .star-rating {
            font-size: 2em;
            color: var(--star-color);
        }

        .star-rating .star {
            cursor: pointer;
            transition: transform 0.1s;
        }

        .star-rating .star:hover {
            transform: scale(1.2);
        }

        .trailer-link,
        .next-link {
            display: inline-block;
            padding: 12px 20px;
            font-size: 1.1em;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .trailer-link {
            background-color: var(--card-bg-color);
            color: var(--accent-color);
            border: 2px solid var(--accent-color);
        }

        .trailer-link:hover {
            background-color: var(--accent-color);
            color: white;
        }

        .next-link {
            background-color: #8e44ad;
            color: white;
            border: 2px solid #8e44ad;
            margin-top: 20px;
        }

        .next-link:hover {
            background-color: #9b59b6;
        }
    </style>

    <script>
        (function() {
            const localStorageKey = 'gamehubDarkMode'; 
            if (localStorage.getItem(localStorageKey) === 'dark') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
</head>
<body id="appBody">

<div class="background-image" id="bg-light"></div>
<div class="background-image" id="bg-dark"></div>
<div class="header">
    <div class="logo">GAMEHUB</div>
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>
</div>

<div class="side-menu" id="sideMenu">
    <a href="hub_home.php"><span class="icon"><i class="fas fa-home"></i></span>Home</a>
    <a href="hub_category.php" class="active"><span class="icon"><i class="fas fa-book-open"></i></span>Library</a> 
    <a href="hub_about.php"><span class="icon"><i class="fas fa-info-circle"></i></span>About</a>
    
    <div class="menu-divider"></div>

    <a href="#" class="login-link" onclick="openModal('loginModal')"><span class="icon"><i class="fas fa-sign-in-alt"></i></span>Login</a>
    
    <div class="menu-divider"></div>
    <div class="menu-item dark-mode-label" onclick="toggleDarkMode()">
        <span class="icon"><i class="fas fa-moon" id="darkModeIcon"></i></span>
        <span id="darkModeText">Switch Dark Mode</span>
    </div>
</div>

<div class="content-container">

    <h2 class="category-title">CHOOSE GAME CATEGORY</h2>

    <div class="category-filters" id="categoryFilters">
        <button class="filter-btn active" data-category="all">All</button>
        <?php foreach ($categories as $category): ?>
            <button class="filter-btn" data-category="<?php echo htmlspecialchars($category); ?>">
                <?php echo htmlspecialchars(strtoupper($category)); ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="game-grid" id="gameGrid">
        <?php if (!empty($games)): ?>
            <?php foreach ($games as $game): ?>
                <?php
                $cover_path = !empty($game['cover_path']) ? $game['cover_path'] : $fallback_cover;
                ?>
                <a href="#" 
                   class="game-card" 
                   onclick="openGameModal(<?php echo $game['game_id']; ?>); return false;"
                   data-category="<?php echo htmlspecialchars($game['game_category']); ?>">
                    
                    <img src="../<?php echo htmlspecialchars($cover_path); ?>" alt="<?php echo htmlspecialchars($game['game_name']); ?> Cover">
                    
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No games have been added to the hub yet.</p>
        <?php endif; ?>
    </div>

</div>

<?php
    
    include '../modals/hub_login.php';
    include '../modals/hub_register.php';
    include '../modals/hub_forgotpassword.php'; 
    include '../modals/hub_forgotpassword2.php'; 
    include '../modals/hub_resetpassword.php'; 
    include '../modals/main/hub_category_game_detail.php';
?>

<script>
    

    
    document.getElementById('menuToggle').addEventListener('click', function() {
        const menu = document.getElementById('sideMenu');
        menu.classList.toggle('open');
    });

    
    const darkModeText = document.getElementById('darkModeText');
    const darkModeIcon = document.getElementById('darkModeIcon');
    const localStorageKey = 'gamehubDarkMode';
    const htmlElement = document.documentElement; 

    
    function applyDarkMode(isDark) {
        if (isDark) {
            htmlElement.classList.add('dark-mode');

        if (darkModeText) {
            darkModeText.textContent = 'Switch Light Mode';
        }
        if (darkModeIcon) {
            darkModeIcon.classList.replace('fa-moon', 'fa-sun');
        }
        } else {
            htmlElement.classList.remove('dark-mode');

        if (darkModeText) {
            darkModeText.textContent = 'Switch Dark Mode';
        }
        if (darkModeIcon) {
            darkModeIcon.classList.replace('fa-sun', 'fa-moon');
        }
        }
    }

    
    function toggleDarkMode() {
        
        const isDark = htmlElement.classList.contains('dark-mode');

        
        applyDarkMode(!isDark);

        
        localStorage.setItem(localStorageKey, !isDark ? 'dark' : 'light');
    }

    
    (function loadButtonText() {
        const isDark = htmlElement.classList.contains('dark-mode');
        applyDarkMode(isDark);
    })();


    
    document.addEventListener('DOMContentLoaded', function() {
        const filterContainer = document.getElementById('categoryFilters');
        const gameCards = document.querySelectorAll('#gameGrid .game-card');

        if (filterContainer) {
            filterContainer.addEventListener('click', function(e) {
                if (!e.target.classList.contains('filter-btn')) {
                    return;
                }
                const selectedCategory = e.target.getAttribute('data-category');
                filterContainer.querySelector('.filter-btn.active').classList.remove('active');
                e.target.classList.add('active');
                gameCards.forEach(card => {
                    const cardCategory = card.getAttribute('data-category');
                    if (selectedCategory === 'all' || cardCategory === selectedCategory) {
                        card.style.display = 'block'; 
                    } else {
                        card.style.display = 'none';  
                    }
                });
            });
        }
    });

    
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
        
        openModal('resetPasswordModal');
    <?php elseif (isset($_SESSION['temp_user_id'])): ?>
        
        openModal('forgotPasswordModal2');
    <?php endif; ?>
    
    
    
    let modalCurrentSlide = 0;
    let modalSlides = [];
    let modalDots = [];
    let modalTotalSlides = 0;
    let modalSlideTimer = null;
    const modalFallbackImg = '../uploads/placeholder.png';
    
    
    function initializeModalSlideshow(gallery) {
        modalSlides = document.querySelectorAll('#modalSlideshowContent .slide');
        modalDots = document.querySelectorAll('#modalSlideIndicators .dot');
        modalTotalSlides = modalSlides.length;
        modalCurrentSlide = 0;
        
        if (modalTotalSlides > 0) {
            showModalSlide(0);
        }
        if (modalTotalSlides > 1) {
            startModalAutoSlide();
        } else {
            stopModalAutoSlide();
        }
        
        
        modalDots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                stopModalAutoSlide();
                showModalSlide(index);
                startModalAutoSlide();
            });
        });
    }

    function showModalSlide(n) {
        if (modalTotalSlides === 0) return;
        modalCurrentSlide = (n + modalTotalSlides) % modalTotalSlides; 
        modalSlides.forEach(slide => slide.classList.remove('active'));
        modalDots.forEach(dot => dot.classList.remove('active'));
        if (modalSlides[modalCurrentSlide]) modalSlides[modalCurrentSlide].classList.add('active');
        if (modalDots[modalCurrentSlide]) modalDots[modalCurrentSlide].classList.add('active');
    }

    function modalChangeSlide(n) {
        stopModalAutoSlide();
        showModalSlide(modalCurrentSlide + n);
        startModalAutoSlide();
    }

    function startModalAutoSlide() {
        if (modalTotalSlides > 1 && !modalSlideTimer) {
            modalSlideTimer = setInterval(() => showModalSlide(modalCurrentSlide + 1), 5000);
        }
    }

    function stopModalAutoSlide() {
        clearInterval(modalSlideTimer);
        modalSlideTimer = null;
    }

    
    function initializeModalFeedback() {
        const favoriteIcon = document.getElementById('modalFavoriteIcon');
        if (favoriteIcon) {
            favoriteIcon.addEventListener('click', function() {
                closeGameModal();
                openModal('loginModal');
            });
        }
        
        const starRatingContainer = document.getElementById('modalStarRating');
        if (starRatingContainer) {
            const stars = starRatingContainer.querySelectorAll('.star');
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    closeGameModal();
                    openModal('loginModal');
                });
            });
        }
    }
    
    
    function populateGameModal(data) {
        const game = data.details;
        const gallery = data.gallery;

        
        document.getElementById('modalGameTitle').textContent = game.game_name;
        document.getElementById('modalGameDesc').innerHTML = game.game_desc.replace(/\n/g, '<br>'); 
        document.getElementById('modalTrailerLink').href = game.game_trailerLink;

        
        const favoriteIcon = document.getElementById('modalFavoriteIcon');
        favoriteIcon.className = 'favorite-icon far fa-heart'; 
        
        const starContainer = document.getElementById('modalStarRating');
        let starHTML = '';
        for (let i = 1; i <= 5; i++) {
            starHTML += `<i class="star far fa-star" data-value="${i}"></i>`;
        }
        starContainer.innerHTML = starHTML;

        
        const slideContainer = document.getElementById('modalSlideshowContent');
        const indicatorContainer = document.getElementById('modalSlideIndicators');
        let slideHTML = '';
        let indicatorHTML = '';
        
        let imagesToUse = gallery.length > 0 ? gallery : [{ img_path: modalFallbackImg }];

        imagesToUse.forEach((image, index) => {
            const activeClass = (index === 0) ? 'active' : '';
            slideHTML += `<div class="slide ${activeClass}">
                            <img src="../${image.img_path}" alt="Game Screenshot">
                          </div>`;
            if (imagesToUse.length > 1) {
                 indicatorHTML += `<span class="dot ${activeClass}" data-index="${index}"></span>`;
            }
        });
        
        slideContainer.innerHTML = slideHTML;
        indicatorContainer.innerHTML = indicatorHTML;
        
        
        initializeModalSlideshow();
        initializeModalFeedback();
    }

    function setGameModalLoading() {
        stopModalAutoSlide();
        document.getElementById('modalGameTitle').textContent = 'Loading...';
        document.getElementById('modalGameDesc').textContent = 'Loading game description...';
        document.getElementById('modalTrailerLink').href = '#';
        document.getElementById('modalFavoriteIcon').className = 'favorite-icon far fa-heart';
        document.getElementById('modalStarRating').innerHTML = `
            <i class="star far fa-star" data-value="1"></i>
            <i class="star far fa-star" data-value="2"></i>
            <i class="star far fa-star" data-value="3"></i>
            <i class="star far fa-star" data-value="4"></i>
            <i class="star far fa-star" data-value="5"></i>`;
        document.getElementById('modalSlideshowContent').innerHTML = `
            <div class="slide active">
                <img src="${modalFallbackImg}" alt="Loading...">
            </div>`;
        document.getElementById('modalSlideIndicators').innerHTML = '';
    }

    async function openGameModal(gameId) {
        openModal('gameDetailModal');
        setGameModalLoading();
        
        try {
            const response = await fetch(`../hub_fetch_game_details_public.php?game_id=${gameId}`);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            const data = await response.json();
            if (data.error) {
                 document.getElementById('modalGameTitle').textContent = 'Error';
                 document.getElementById('modalGameDesc').textContent = data.error;
            } else {
                populateGameModal(data);
            }
        } catch (error) {
            console.error('Failed to fetch game details:', error);
            document.getElementById('modalGameTitle').textContent = 'Error';
            document.getElementById('modalGameDesc').textContent = 'Could not load game details. Please try again.';
        }
    }
    
    
    function closeGameModal() {
        closeModal('gameDetailModal');
        stopModalAutoSlide();
    }
</script>

</body>
</html>