<?php
session_start();

//  Check for login
if (!isset($_SESSION['username'])) {
    header('Location: ../../hub_login.php');
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
            color: white; font-weight: bold; 
        }
        .side-menu a.active:hover { 
            background-color: #2980b9; 
        }
        .menu-divider {
            border-top: 1px solid var(--secondary-text-color);
            margin: 5px 0;
        }
        .logout-link {
            color: #e74c3c !important; /* Red for Logout*/
            font-weight: bold;
        }
        .logout-link:hover {
            background-color: #4e2925; /* Darker hover for dark mode consistency */
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

        /* Start Button */
        .start-button {
            padding: 15px 40px;
            background: #e74c3c;
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
        .start-button:hover {
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

<div class="header">
    <div class="logo">GAMEHUB</div>
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>
</div>

<!-- Side Menu -->
<div class="side-menu" id="sideMenu">
    <a href="hub_home_logged_in.php" class="active"><span class="icon"><i class="fas fa-home"></i></span>Home</a>
    <a href="hub_home_category_logged_in.php"><span class="icon"><i class="fas fa-book-open"></i></span>Library</a>
    <a href="hub_main_profile.php"><span class="icon"><i class="fas fa-user-circle"></i></span>Profile</a>
    <a href="hub_main_about_logged_in.php"><span class="icon"><i class="fas fa-info-circle"></i></span>About</a>


    <div class="menu-divider"></div>
    
    <!-- Switch Dark Mode Button -->
    <div class="menu-item dark-mode-label" onclick="toggleDarkMode()">
        <span class="icon"><i class="fas fa-moon"></i></span>
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

    <!-- START button-->
    <a href="hub_home_category_logged_in.php" class="start-button">START</a>

    <!-- Blue Wave Background Effect -->
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

    // Updated Dark Mode
    const darkModeText = document.getElementById('darkModeText');
    const localStorageKey = 'gamehubDarkMode';
    const htmlElement = document.documentElement;

   
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
</script>

</body>
</html>