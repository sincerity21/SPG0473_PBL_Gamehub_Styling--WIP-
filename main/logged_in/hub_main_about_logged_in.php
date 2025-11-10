<?php
session_start();
require '../../hub_conn.php'; 

// --- 1. Authentication & Authorization ---
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
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
    <title>GameHub - About Us</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kalam:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- 1. CSS Variables for Theming --- */
        :root {
            --bg-color: #f4f7f6;
            --main-text-color: #333;
            
            /* === MODIFICATION 1: Light mode tint changed to #9c1809ff === */
            --accent-color: #9c1809ff;
            --accent-color-darker: #801407; /* Added darker shade */
            
            --secondary-text-color: #7f8c8d;
            --card-bg-color: white;
            --shadow-color: rgba(0, 0, 0, 0.05);
            --border-color: #ddd;
            --welcome-title-color: #2c3e50;
            --login-color: #2ecc71; /* Green for login */
            
            /* === NEW: Glass Colors === */
            --glass-bg-light: rgba(255, 255, 255, 0.7);
            --glass-bg-dark: rgba(30, 30, 30, 0.7);
        }

        html.dark-mode body {
            --bg-color: #121212;
            --main-text-color: #f4f4f4;
            
             /* === MODIFICATION 2: Dark mode tint is #f39c12 === */
            --accent-color: #f39c12;
            --accent-color-darker: #c87f0a; /* Added darker shade */
            
            --secondary-text-color: #95a5a6;
            --card-bg-color: #1e1e1e;
            --shadow-color: rgba(0, 0, 0, 0.4);
            --border-color: #444;
            --welcome-title-color: #ecf0f1;
            --login-color: #27ae60; /* Darker green */
        }
        
        /* === NEW BACKGROUND IMAGE STYLES === */
        .background-image {
            position: fixed;
            top: -10px; /* Overscan to hide blur edges */
            left: -10px;
            width: calc(100% + 20px);
            height: calc(100% + 20px);
            z-index: -1; /* Behind all content */
            
            background-size: cover;
            background-position: center;
            filter: blur(5px);
            transition: opacity 0.5s ease-in-out;
            background-color: var(--bg-color); /* Fallback */
        }

        /* 1. Light Mode Image */
        #bg-light {
            /* This path goes UP two levels (from /logged_in/ to /main/ to /) */
            background-image: url('../../uploads/home/prototype.jpg');
            opacity: 1; /* Visible by default */
        }

        /* 2. Dark Mode Image */
        #bg-dark {
            background-image: url('../../uploads/home/darksouls.jpg');
            opacity: 0; /* Hidden by default */
        }

        /* 3. The Swap Logic */
        html.dark-mode body #bg-light {
            opacity: 0; /* Hide light image in dark mode */
        }
        html.dark-mode body #bg-dark {
            opacity: 1; /* Show dark image in dark mode */
        }
        
        /* === NEW: Dark Mode Glass Override === */
        html.dark-mode body .header,
        html.dark-mode body .side-menu {
            background-color: var(--glass-bg-dark); /* Dark glass */
        }
        /* === END NEW === */

        /* --- 2. Base Styles --- */
       body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            /* background-color: var(--bg-color); */ /* REMOVED */
            color: var(--main-text-color);
            min-height: 100vh;
            transition: background-color 0.3s, color 0.3s;
        }

        .header {
            /* background-color: var(--card-bg-color); */ /* OLD */
            background-color: var(--glass-bg-light); /* NEW */
            backdrop-filter: blur(10px); /* NEW */
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px var(--shadow-color);
            position: sticky;
            top: 0;
            z-index: 1001;
            transition: background-color 0.3s; /* Add transition for glass */
        }
        .logo { font-size: 24px; font-weight: 700; color: var(--accent-color); text-decoration: none; }
        .menu-toggle { background: none; border: none; cursor: pointer; font-size: 24px; color: var(--main-text-color); padding: 5px; }

        /* --- 3. Side Menu --- */
        .side-menu {
            position: fixed; top: 60px; right: 0; width: 220px;
            /* background-color: var(--card-bg-color); */ /* OLD */
            background-color: var(--glass-bg-light); /* NEW */
            backdrop-filter: blur(10px); /* NEW */
            box-shadow: -4px 4px 8px var(--shadow-color);
            border-radius: 8px 0 8px 8px;
            padding: 10px 0; z-index: 1000;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out, background-color 0.3s;
        }
        .side-menu.open { transform: translateX(0); }
        .side-menu a, .menu-item { display: block; padding: 12px 20px; color: var(--main-text-color); text-decoration: none; transition: background-color 0.2s; cursor: pointer; }
        .side-menu a:hover, .menu-item:hover { background-color: var(--bg-color); color: var(--accent-color); }
        .side-menu a.active { background-color: var(--accent-color); color: white; font-weight: bold; }
        .side-menu a.active:hover { 
            background-color: var(--accent-color);
            filter: brightness(0.85);
        }
        .menu-divider { border-top: 1px solid var(--secondary-text-color); margin: 5px 0; }
        .logout-link { color: #e74c3c !important; font-weight: bold; }
        .logout-link:hover { background-color: #4e2925; }
        .icon { margin-right: 10px; width: 20px; text-align: center; }
        .dark-mode-label { display: flex; justify-content: space-between; align-items: center; user-select: none; }

        /* --- 4. Content Styles --- */
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
        
        /* === MODIFIED: Darker text for light mode === */
        .about-section p {
            font-size: 1.1em;
            line-height: 1.7;
            /* color: var(--secondary-text-color); */ /* OLD - too light */
            color: #444; /* NEW - A nice dark grey for light mode */
        }
        html.dark-mode body .about-section p {
            color: var(--secondary-text-color); /* Kept light for dark mode */
        }
        /* === END MODIFIED === */


        /*
        ===============================================
           === NEW TEAM CARD CSS (Added) ===
        ===============================================
        */

        /* A grid to layout the cards */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        /* 1. The Card Container */
        .team-card {
            position: relative; /* This is essential for positioning the overlay */
            overflow: hidden; /* This hides the overlay when it's "down" */
            border-radius: 12px;
            box-shadow: 0 5px 15px var(--shadow-color);
        }

        .team-card img {
            display: block;
            width: 100%;
            height: auto;
            aspect-ratio: 1 / 1; /* Makes images square, adjust as needed */
            object-fit: cover;
            transition: transform 0.4s ease; /* Adds a slight zoom on hover */
        }

        /* 2. The "Orange Thing" Overlay */
        .team-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            
            /* === MODIFICATION 4: Changed hardcoded orange to blue rgba === */
            background: rgba(52, 152, 219, 0.9); /* WAS rgba(230, 126, 34, 0.9) */
            
            color: white;
            text-align: center;
            padding: 1.5rem 1rem;
            box-sizing: border-box; 
            transform: translateY(100%); 
            transition: transform 0.4s ease-out;
        }

        /* 3. The Hover State */
        .team-card:hover .team-overlay {
            transform: translateY(0);
        }

        .team-card:hover img {
            transform: scale(1.1);
        }


        /* 4. Styling for the Overlay Content */
        .team-overlay h3 {
            margin: 0 0 5px 0;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .team-overlay p {
            margin: 0 0 1rem 0;
            font-size: 1rem;
            font-style: italic;
            color: white; /* Override the default .about-section p color */
            line-height: 1.4;
        }

        .social-links a {
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            margin: 0 8px; /* Space between icons */
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
    <a href="hub_home_category_logged_in.php"><span class="icon"><i class="fas fa-book-open"></i></span>Library</a> 
    <a href="hub_main_profile.php"><span class="icon"><i class="fas fa-user-circle"></i></span>Profile</a>
    <a href="hub_main_about_logged_in.php" class="active"><span class="icon"><i class="fas fa-info-circle"></i></span>About</a>
    
    <div class="menu-divider"></div>

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

    // --- Updated Dark Mode Logic ---
    const darkModeText = document.getElementById('darkModeText');
    const localStorageKey = 'gamehubDarkMode';
    const htmlElement = document.documentElement; // Target the <html> tag

    // This function now applies the class to <html> AND updates the button text
    function applyDarkMode(isDark) {
        if (isDark) {
            htmlElement.classList.add('dark-mode');
            if (darkModeText) darkModeText.textContent = 'Switch Light Mode';
        } else {
            htmlElement.classList.remove('dark-mode');
            if (darkModeText) darkModeText.textContent = 'Switch Dark Mode';
        }
    }

    // This function toggles the mode
    function toggleDarkMode() {
        // Check the class on the <html> tag
        const isDark = htmlElement.classList.contains('dark-mode');

        // Toggle the state
        applyDarkMode(!isDark);

        // Save preference to local storage
        localStorage.setItem(localStorageKey, !isDark ? 'dark' : 'light');
    }

    // This function runs on page load to set the *button text* correctly.
    // The class itself was already set by the script in the <head>.
    (function loadButtonText() {
        const isDark = htmlElement.classList.contains('dark-mode');
        applyDarkMode(isDark);
    })();
</script>

</body>
</html>