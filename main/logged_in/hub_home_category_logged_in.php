<?php
session_start();
require '../../hub_conn.php'; // Assuming hub_conn is one level up

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../../hub_login.php');
    exit();
}

$username = htmlspecialchars($_SESSION['username']);

// Fetch data for the page
$categories = selectAllGameCategories();
$games = selectAllGamesWithCovers(); // Use the NEW function

// Define the placeholder image path
$fallback_cover = 'uploads/placeholder.png'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameHub - Library</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* --- 1. CSS Variables for Theming (from hub_home_logged_in.php) --- */
        :root {
            --bg-color: #f4f7f6;
            --main-text-color: #333;
            
            /* === MODIFICATION 1: Light mode tint changed to #9c1809ff === */
            --accent-color: #9c1809ff;
            --accent-color-darker: #801407; 
            
            --secondary-text-color: #7f8c8d;
            --card-bg-color: white;
            --shadow-color: rgba(0, 0, 0, 0.05);
            --border-color: #ddd;
            --welcome-title-color: #2c3e50;
            --login-color: #2ecc71; 
            
            /* Glass Colors */
            --glass-bg-light: rgba(255, 255, 255, 0.7);
            --glass-bg-dark: rgba(30, 30, 30, 0.7);
        }

        html.dark-mode body {
            --bg-color: #121212;
            --main-text-color: #f4f4f4;
            
            /* === MODIFICATION 2: Dark mode tint is #f39c12 === */
            --accent-color: #f39c12;
            --accent-color-darker: #c87f0a;
            
            --secondary-text-color: #95a5a6;
            --card-bg-color: #1e1e1e;
            --shadow-color: rgba(0, 0, 0, 0.4);
            --border-color: #444;
            --welcome-title-color: #ecf0f1;
            --login-color: #27ae60; 
        }

        /* Background Image Styles */
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
            opacity: 1; /* Visible by default */
        }

        #bg-dark {
            background-image: url('../../uploads/home/darksouls.jpg');
            opacity: 0; /* Hidden by default */
        }
        html.dark-mode body #bg-light {
            opacity: 0; 
        }
        html.dark-mode body #bg-dark {
            opacity: 1; 
        }
        
        /* Dark Mode Glass Override */
        html.dark-mode body .header,
        html.dark-mode body .side-menu {
            background-color: var(--glass-bg-dark); /* Dark glass */
        }

        /* --- 2. Base Styles (from hub_home_logged_in.php) --- */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-color);
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
        .logo { font-size: 24px; font-weight: 700; color: var(--accent-color); text-decoration: none; }
        .menu-toggle { background: none; border: none; cursor: pointer; font-size: 24px; color: var(--main-text-color); padding: 5px; }

        /* --- 3. Side Menu (from hub_home_logged_in.php) --- */
        .side-menu {
            position: fixed; top: 60px; right: 0; width: 220px;
            background-color: var(--card-bg-color);
            box-shadow: -4px 4px 8px var(--shadow-color);
            border-radius: 8px 0 8px 8px;
            padding: 10px 0; z-index: 1000;
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        }
        .side-menu.open { transform: translateX(0); }
        .side-menu a, .menu-item { display: block; padding: 12px 20px; color: var(--main-text-color); text-decoration: none; transition: background-color 0.2s; cursor: pointer; }
        .side-menu a:hover, .menu-item:hover { background-color: var(--bg-color); color: var(--accent-color); }
        /* --- ADDED .active class styles --- */
        .side-menu a.active { background-color: var(--accent-color); color: white; font-weight: bold; }
        .side-menu a.active:hover { 
            background-color: var(--accent-color);
            filter: brightness(0.85);
        }
        /* --- END ADDED --- */
        .menu-divider { border-top: 1px solid var(--secondary-text-color); margin: 5px 0; }
        .logout-link { color: #e74c3c !important; font-weight: bold; }
        .icon { margin-right: 10px; width: 20px; text-align: center; }
        .dark-mode-label { display: flex; justify-content: space-between; align-items: center; user-select: none; }

        /* --- 4. NEW: Content & Sketch Styles --- */
        .content-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }
        
        .greeting {
            font-size: 2.5em;
            font-weight: 600;
            color: var(--welcome-title-color);
            margin: 0;
        }

        /* Wavy Divider from Sketch */
        .wave-divider {
            width: 100%;
            height: 30px;
            margin-bottom: 30px;
            overflow: hidden;
        }
        .wave-divider svg {
            width: 100%;
            height: 100%;
            stroke: var(--accent-color);
            stroke-width: 2;
            fill: none;
        }

        .category-title {
            font-size: 1.5em;
            font-weight: 600;
            color: var(--welcome-title-color);
            margin-bottom: 20px;
            text-align: left;
            border-bottom: 3px solid var(--accent-color);
            display: inline-block;
            padding-bottom: 5px;
        }

        /* Category Filter Buttons */
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

        /* Game Grid */
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
        }
        .game-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px var(--shadow-color);
        }
        
        /* --- MODIFIED CSS --- */
        .game-card img {
            width: 100%;
            height: auto; /* Let height be automatic */
            aspect-ratio: 460 / 215; /* Enforce 460x215 aspect ratio */
            object-fit: cover; /* Ensures image fills the space */
            display: block;
        }
        /* --- REMOVED .game-card-title CSS --- */

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
    <a href="hub_home_category_logged_in.php" class="active"><span class="icon"><i class="fas fa-book-open"></i></span>Library</a> 
    <a href="hub_main_profile.php"><span class="icon"><i class="fas fa-user-circle"></i></span>Profile</a>
    <a href="hub_main_about_logged_in.php"><span class="icon"><i class="fas fa-info-circle"></i></span>About</a>
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

    <h1 class="greeting">HELLO <?php echo strtoupper($username); ?></h1>

    <div class="wave-divider">
        <svg viewBox="0 0 100 10" preserveAspectRatio="none">
            <path d="M 0 5 C 25 10, 75 0, 100 5" />
        </svg>
    </div>

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
                // Use fallback placeholder if cover_path is missing
                $cover_path = !empty($game['cover_path']) ? $game['cover_path'] : $fallback_cover;
                ?>
                <a href="hub_game_detail_logged_in.php?game_id=<?php echo $game['game_id']; ?>" 
                   class="game-card" 
                   data-category="<?php echo htmlspecialchars($game['game_category']); ?>">
                    
                    <img src="../../<?php echo htmlspecialchars($cover_path); ?>" alt="<?php echo htmlspecialchars($game['game_name']); ?> Cover">
                    
                    </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No games have been added to the hub yet.</p>
        <?php endif; ?>
    </div>

</div>

<script>
    

    // --- 1. Side Menu Toggle Logic (from hub_home_logged_in.php) ---
    document.getElementById('menuToggle').addEventListener('click', function() {
        const menu = document.getElementById('sideMenu');
        menu.classList.toggle('open');
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


    // --- 3. NEW: Category Filtering Logic ---
    document.addEventListener('DOMContentLoaded', function() {
        const filterContainer = document.getElementById('categoryFilters');
        const gameCards = document.querySelectorAll('#gameGrid .game-card');

        if (filterContainer) {
            filterContainer.addEventListener('click', function(e) {
                // Only act if a filter button was clicked
                if (!e.target.classList.contains('filter-btn')) {
                    return;
                }

                // Get the category to filter by
                const selectedCategory = e.target.getAttribute('data-category');

                // Update active button state
                filterContainer.querySelector('.filter-btn.active').classList.remove('active');
                e.target.classList.add('active');

                // Show/Hide game cards
                gameCards.forEach(card => {
                    const cardCategory = card.getAttribute('data-category');
                    
                    if (selectedCategory === 'all' || cardCategory === selectedCategory) {
                        card.style.display = 'block'; // Show card
                    } else {
                        card.style.display = 'none';  // Hide card
                    }
                });
            });
        }
    });
</script>

</body>
</html>