<?php
session_start();
require '../../hub_conn.php'; 

if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
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
    <title>GameHub - About Us</title>
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

        html.dark-mode body .header,
        html.dark-mode body .side-menu {
            background-color: var(--glass-bg-dark);
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

        .menu-divider { 
            border-top: 1px solid var(--secondary-text-color); 
            margin: 5px 0; 
        }

        .logout-link { 
            color: #e74c3c !important; 
            font-weight: bold; 
        }

        .logout-link:hover { 
            background-color: #4e2925; 
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

        .content-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 30px;
        }
        
        .content-title {
            font-size: 1.8em;
            font-weight: 600;
            color: var(--welcome-title-color);
            margin-top: 20px;
            margin-bottom: 25px;
            text-align: left;
            border-bottom: 3px solid var(--accent-color);
            display: inline-block;
            padding-bottom: 5px;
        }

        .about-section {
            background-color: var(--card-bg-color);
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 8px var(--shadow-color);
            margin-bottom: 30px;
        }
        
        .about-section p {
            font-size: 1.1em;
            line-height: 1.7;
            color: #444;
        }

        html.dark-mode body .about-section p {
            color: var(--secondary-text-color);
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .team-card {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 5px 15px var(--shadow-color);
        }

        .team-card img {
            display: block;
            width: 100%;
            height: auto;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .team-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: rgba(52, 152, 219, 0.9);
            color: white;
            text-align: center;
            padding: 1.5rem 1rem;
            box-sizing: border-box;
            transform: translateY(100%);
            transition: transform 0.4s ease-out;
        }

        .team-card:hover .team-overlay {
            transform: translateY(0);
        }

        .team-card:hover img {
            transform: scale(1.1);
        }

        .team-overlay h3 {
            margin: 0 0 5px 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .team-overlay p {
            margin: 0 0 1rem 0;
            font-size: 1rem;
            font-style: italic;
            color: white;
            line-height: 1.4;
        }

        .social-links a {
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            margin: 0 8px;
            transition: color 0.2s;
        }

        .social-links a:hover {
            color: #f4f4f4;
            transform: scale(1.1);
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
    <a href="hub_home_logged_in.php"><span class="icon"><i class="fas fa-home"></i></span>Home</a>
    <a href="hub_category_logged_in.php"><span class="icon"><i class="fas fa-book-open"></i></span>Library</a> 
    <a href="hub_profile.php"><span class="icon"><i class="fas fa-user-circle"></i></span>Profile</a>
    <a href="hub_about_logged_in.php" class="active"><span class="icon"><i class="fas fa-info-circle"></i></span>About</a>
    
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

<div class="content-container">

    <h2 class="content-title">About GameHub</h2>
    <div class="about-section">
        <p>
            Welcome to GameHub, your ultimate destination for discovering, rating, and discussing your favorite video games. 
            Our mission is to create a vibrant community of gamers who can share their passion and expertise.
        </p>
        <p>
            Whether you're looking for the next big AAA title, a hidden indie gem, or just want to see how your
            favorites stack up against the crowd, GameHub provides the tools you need.
        </p>
    </div>

    <h2 class="content-title">Our Team</h2>
    <div class="team-grid">

        <div class="team-card">
            <img src="../../uploads/members/iman2.jpg" alt="Team Member 1">
            <div class="team-overlay">
                <h3>IMAN DARWISH</h3>
                <p>Front-End, HTML</p>
                <div class="social-links">
                    <a href="https://instagram.com" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="https://linkedin.com" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>

        <div class="team-card">
            <img src="../../uploads/members/khairul.jpg" alt="Team Member 2">
            <div class="team-overlay">
                <h3>KHAIRULANWAR</h3>
                <p>Design, GUI</p>
                <div class="social-links">
                    <a href="https://instagram.com" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="https://linkedin.com" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
        
        <div class="team-card">
            <img src="../../uploads/members/fawwaz3.jpg" alt="Team Member 3">
            <div class="team-overlay">
                <h3>FAWWAZ</h3>
                <p>Back-End, Database</p>
                <div class="social-links">
                    <a href="https://github.com/sincerity21" target="_blank" aria-label="GitHub"><i class="fab fa-github"></i></a>
                </div>
            </div>
        </div>
        
        </div>
    </div>

<script>
    document.getElementById('menuToggle').addEventListener('click', function() {
        document.getElementById('sideMenu').classList.toggle('open');
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