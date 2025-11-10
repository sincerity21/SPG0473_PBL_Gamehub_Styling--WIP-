<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: ../../modals/hub_login.php');
    exit();
}

$username = htmlspecialchars($_SESSION['username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameHub - Welcome</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --bg-color: #f4f7f6;
            --main-text-color: #333;
            --accent-color: #9c1809ff;
            --accent-color-darker: #801407;
            --secondary-text-color: #7f8c8d;
            --card-bg-color: white;
            --shadow-color: rgba(0, 0, 0, 0.05);
            --wave-opacity: 0.15;
            --welcome-title-color: #2c3e50;
            --login-color: #2ecc71;
            --border-color: #ccc;
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
            --wave-opacity: 0.05;
            --welcome-title-color: #ecf0f1;
            --login-color: #27ae60;
            --border-color: #444;
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
            background-image: url('../../uploads/home/prototype.jpg');
            opacity: 1;
        }

        #bg-dark {
            background-image: url('../../uploads/home/darksouls.jpg');
            opacity: 0;
        }

        html.dark-mode body #bg-light {
            opacity: 0;
        }

        html.dark-mode body #bg-dark {
            opacity: 1;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: var(--main-text-color);
            display: flex;
            flex-direction: column;
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
        }

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

        html.dark-mode body .header,
        html.dark-mode body .side-menu {
            background-color: var(--glass-bg-dark);
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
            font-size: 16px;
            transition: background-color 0.2s, color 0.2s;
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

        .logout-link { 
            color: #e74c3c !important; 
            font-weight: bold; 
        }

        .logout-link:hover { 
            background-color: #4e2925; 
        }

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
            color: #444;
            margin-bottom: 40px;
        }

        html.dark-mode body .welcome-subtitle {
            color: var(--secondary-text-color);
        }

        .start-button {
            padding: 15px 40px;
            background: var(--accent-color);
            color: white;
            text-decoration: none;
            border: 2px solid var(--accent-color-darker);
            border-radius: 6px;
            font-size: 1.2em;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease-in-out;
            margin-top: 50px;
        }

        .start-button:hover {
            background: var(--accent-color-darker);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.25);
            transform: translateY(-2px);
        }

        .wave-container {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 150px;
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
            0% {
                transform: translate(0, 0);
            }
            50% {
                transform: translate(-25%, 5%);
            }
            100% {
                transform: translate(0, 0);
            }
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
    <a href="hub_home_logged_in.php" class="active"><span class="icon"><i class="fas fa-home"></i></span>Home</a>
    <a href="hub_category_logged_in.php"><span class="icon"><i class="fas fa-book-open"></i></span>Library</a>
    
    <a href="hub_profile.php"><span class="icon"><i class="fas fa-user-circle"></i></span>Profile</a>
    <a href="hub_about_logged_in.php"><span class="icon"><i class="fas fa-info-circle"></i></span>About</a>
    <div class="menu-divider"></div>
    
    <div class="menu-item dark-mode-label" onclick="toggleDarkMode()">
        <span class="icon"><i class="fas fa-moon" id="darkModeIcon"></i></span>
        <span id="darkModeText">Switch Dark Mode</span>
    </div>

    <div class="menu-divider"></div>

    <a href="../../hub_logout.php" class="logout-link">
        <span class="icon"><i class="fas fa-sign-out-alt"></i></span>
        Logout
    </a>
</div>

<div class="main-content">
    <h1 class="welcome-title">WELCOME <?php echo strtoupper($username); ?></h1>
    <p class="welcome-subtitle">
        This is the GameHub, where you can rate your favourite games.
    </p>

    <a href="hub_category_logged_in.php" class="start-button">START</a>

    <div class="wave-container">
        <div class="wave"></div>
        <div class="wave"></div>
    </div>
</div>

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
</script>

</body>
</html>