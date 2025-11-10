<?php
session_start();
require '../../hub_conn.php'; 

if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header('Location: ../../hub_login.php');
    exit();
}

if (!isset($_GET['game_id']) || !is_numeric($_GET['game_id'])) {
    header('Location: hub_home_category_logged_in.php'); //  Redirect if no valid game ID
    exit();
}

$game_id = (int)$_GET['game_id'];
$user_id = (int)$_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);

// get all data

// Get the game's main info
$game = selectGameByID($game_id);

if (!$game) {
    //  redirect back if game not found
    header('Location: hub_home_category_logged_in.php');
    exit();
}

// Get slideshow images
$gallery_images = selectGameGalleryImages($game_id);

//  Get this user's feedback
$feedback = selectUserGameFeedback($user_id, $game_id);

// Set default heart/star values
$current_rating = $feedback['game_rating'] ?? 0;
$is_favorite = $feedback['favorite_game'] ?? 0;

// Use a placeholder if no gallery images
$fallback_path = 'uploads/placeholder.png';
if (empty($gallery_images)) {
    $gallery_images[] = ['img_path' => $fallback_path];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($game['game_name']); ?> - GameHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Color Theme Variables */
        :root {
            --bg-color: #f4f7f6;
            --main-text-color: #333;
            --accent-color: #3498db;
            --secondary-text-color: #7f8c8d;
            --card-bg-color: white;
            --shadow-color: rgba(0, 0, 0, 0.05);
            --border-color: #ddd;
            --welcome-title-color: #2c3e50;
            --star-color: #f39c12;
            --heart-color: #e74c3c;
        }
        html.dark-mode body {
            --bg-color: #121212; --main-text-color: #f4f4f4; --accent-color: #4dc2f9;
            --secondary-text-color: #95a5a6; --card-bg-color: #1e1e1e; --shadow-color: rgba(0, 0, 0, 0.4);
            --border-color: #444; --welcome-title-color: #ecf0f1;
        }

        /* Base & Menu Styles */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: var(--bg-color); color: var(--main-text-color); min-height: 100vh; transition: background-color 0.3s, color 0.3s; }
        .header { background-color: var(--card-bg-color); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px var(--shadow-color); position: sticky; top: 0; z-index: 1001; }
        .logo { font-size: 24px; font-weight: 700; color: var(--accent-color); text-decoration: none; }
        .menu-toggle { background: none; border: none; cursor: pointer; font-size: 24px; color: var(--main-text-color); padding: 5px; }
        .side-menu { position: fixed; top: 60px; right: 0; width: 220px; background-color: var(--card-bg-color); box-shadow: -4px 4px 8px var(--shadow-color); border-radius: 8px 0 8px 8px; padding: 10px 0; z-index: 1000; transform: translateX(100%); transition: transform 0.3s ease-in-out; }
        .side-menu.open { transform: translateX(0); }
        .side-menu a, .menu-item { display: block; padding: 12px 20px; color: var(--main-text-color); text-decoration: none; transition: background-color 0.2s; cursor: pointer; }
        .side-menu a:hover, .menu-item:hover { background-color: var(--bg-color); color: var(--accent-color); }
        /* Style for the 'active' menu link */
        .side-menu a.active { background-color: var(--accent-color); color: white; font-weight: bold; }
        .side-menu a.active:hover { background-color: #2980b9; }
        /* END ADDED  */
        .menu-divider { border-top: 1px solid var(--secondary-text-color); margin: 5px 0; }
        .logout-link { color: #e74c3c !important; font-weight: bold; }
        .icon { margin-right: 10px; width: 20px; text-align: center; }
        .dark-mode-label { display: flex; justify-content: space-between; align-items: center; user-select: none; }
        
        /* Page Layout */
        .content-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 30px;
        }
        .greeting { font-size: 1.8em; font-weight: 500; color: var(--welcome-title-color); margin: 0; }
        .wave-divider { width: 100%; height: 30px; margin-bottom: 30px; overflow: hidden; }
        .wave-divider svg { width: 100%; height: 100%; stroke: var(--accent-color); stroke-width: 2; fill: none; }

        .game-detail-layout {
            display: grid;
            grid-template-columns: 1fr 1fr; /* 50/50 split */
            gap: 40px;
            align-items: flex-start;
        }
        
        /* Left Column: Slideshow */
        .image-slideshow {
            position: relative;
            width: 100%;
            height: 0;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
            overflow: hidden;
            border-radius: 8px;
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
        }
        .slide { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; transition: opacity 1s ease-in-out; }
        .slide.active { opacity: 1; }
        .slide img { width: 100%; height: 100%; object-fit: cover; }
        .slider-control { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(0, 0, 0, 0.4); color: white; border: none; padding: 10px; cursor: pointer; z-index: 10; font-size: 1.5em; }
        .slider-control:hover { background: rgba(0, 0, 0, 0.6); }
        .prev { left: 0; border-radius: 0 5px 5px 0; }
        .next { right: 0; border-radius: 5px 0 0 5px; }
        .slide-indicators { position: absolute; bottom: 10px; left: 50%; transform: translateX(-50%); z-index: 10; display: flex; gap: 5px; }
        .dot { display: inline-block; width: 10px; height: 10px; background: rgba(255, 255, 255, 0.5); border-radius: 50%; cursor: pointer; transition: background 0.3s; }
        .dot.active { background: white; }

        /* Right Column (Game Info) */
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
            color: var(--secondary-text-color);
            line-height: 1.6;
        }

        /* Heart Icon */
        .favorite-icon {
            font-size: 2.5em;
            color: var(--border-color);
            cursor: pointer;
            transition: color 0.2s, transform 0.2s;
        }
        .favorite-icon.active {
            color: var(--heart-color);
            transform: scale(1.1);
        }

        /* Star Rating */
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

        /* Trailer & Next Buttons */
        .trailer-link, .next-link {
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
            background-color: #8e44ad; /* Next button color */
            color: white;
            border: 2px solid #8e44ad;
            margin-top: 20px;
        }
        .next-link:hover {
            background-color: #9b59b6;
        }
        
        /* Back link */
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover { text-decoration: underline; }

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

    <h1 class="greeting">Let's see your games!</h1>

    <div class="wave-divider">
        <svg viewBox="0 0 100 10" preserveAspectRatio="none">
            <path d="M 0 5 C 25 10, 75 0, 100 5" />
        </svg>
    </div>
    
    <a href="hub_home_category_logged_in.php" class="back-link">
        <i class="fas fa-chevron-left"></i> Back to Library
    </a>

    <div class="game-detail-layout">
        
        <div class="image-slideshow">
            <div id="slideshow-content">
                <?php foreach ($gallery_images as $index => $image): ?>
                    <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="../../<?php echo htmlspecialchars($image['img_path']); ?>" alt="Game Screenshot">
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($gallery_images) > 1): ?>
                <button type="button" class="slider-control prev" onclick="changeSlide(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button type="button" class="slider-control next" onclick="changeSlide(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <div class="slide-indicators" id="slide-indicators">
                    <?php for ($i = 0; $i < count($gallery_images); $i++): ?>
                        <span class="dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></span>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="game-info">
            <div class="game-title-header">
                <h2 class="game-title"><?php echo htmlspecialchars($game['game_name']); ?></h2>
                <i class="fa-heart favorite-icon <?php echo $is_favorite ? 'fas active' : 'far'; ?>" 
                   id="favoriteIcon" 
                   data-game-id="<?php echo $game_id; ?>"></i>
            </div>
            
            <p class="game-desc"><?php echo nl2br(htmlspecialchars($game['game_desc'])); ?></p>
            
            <div class="star-rating" id="starRating" data-game-id="<?php echo $game_id; ?>">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="star <?php echo $i <= $current_rating ? 'fas' : 'far'; ?> fa-star" data-value="<?php echo $i; ?>"></i>
                <?php endfor; ?>
            </div>

            <a href="<?php echo htmlspecialchars($game['game_trailerLink']); ?>" class="trailer-link" target="_blank">
                <i class="fab fa-youtube"></i> Watch the Trailer (on YouTube)
            </a>

            <a href="../survey/hub_survey_game.php?game_id=<?php echo $game_id; ?>" class="next-link">
                NEXT
            </a>
        </div>
    </div>
</div>

<script>
    

    // Side Menu
    document.getElementById('menuToggle').addEventListener('click', function() {
        document.getElementById('sideMenu').classList.toggle('open');
    });

    // Dark Mode
    const darkModeText = document.getElementById('darkModeText');
    const localStorageKey = 'gamehubDarkMode';
    const htmlElement = document.documentElement;

    // Applies dark mode and updates menu text
    function applyDarkMode(isDark) {
        if (isDark) {
            htmlElement.classList.add('dark-mode');
            if (darkModeText) darkModeText.textContent = 'Switch Light Mode';
        } else {
            htmlElement.classList.remove('dark-mode');
            if (darkModeText) darkModeText.textContent = 'Switch Dark Mode';
        }
    }

    // Toggles dark mode on click
    function toggleDarkMode() {
        const isDark = htmlElement.classList.contains('dark-mode');

        //  Toggle the state
        applyDarkMode(!isDark);

        //  Save preference to local storage
        localStorage.setItem(localStorageKey, !isDark ? 'dark' : 'light');
    }

    // Set correct menu text on page load
    (function loadButtonText() {
        const isDark = htmlElement.classList.contains('dark-mode');
        applyDarkMode(isDark);
    })();

    // Slideshow Logic
    let currentSlide = 0;
    const slides = document.querySelectorAll('#slideshow-content .slide');
    const dots = document.querySelectorAll('#slide-indicators .dot');
    const totalSlides = slides.length;
    let slideTimer = null;

    function showSlide(n) {
        if (totalSlides === 0) return;
        currentSlide = (n + totalSlides) % totalSlides; // Loop back to start/end
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        if (slides[currentSlide]) slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) dots[currentSlide].classList.add('active');
    }
    function changeSlide(n) {
        stopAutoSlide();
        showSlide(currentSlide + n);
        startAutoSlide();
    }
    function startAutoSlide() {
        if (totalSlides > 1 && !slideTimer) {
            slideTimer = setInterval(() => showSlide(currentSlide + 1), 5000);
        }
    }
    function stopAutoSlide() {
        clearInterval(slideTimer);
        slideTimer = null;
    }
    document.addEventListener('DOMContentLoaded', () => {
        if(totalSlides > 0) showSlide(0);
        startAutoSlide();
    });


    // Feedback (Heart/Stars) Logic

    //  Generic function to send feedback updates
    async function sendFeedback(gameId, feedbackData) {
        try {
            const response = await fetch('../../hub_update_feedback.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    game_id: gameId,
                    ...feedbackData // e.g. { rating: 4 } or { favorite: 1 }
                })
            });

            if (!response.ok) {
                console.error('Feedback update failed:', response.statusText);
            }
            //  You can optionally handle the JSON response here
            //  const result = await response.json();
            //  console.log(result.message);

        } catch (error) {
            console.error('Error sending feedback:', error);
        }
    }

    // Favorite (Heart icon)
    const favoriteIcon = document.getElementById('favoriteIcon');
    if (favoriteIcon) {
        favoriteIcon.addEventListener('click', function() {
            const gameId = this.getAttribute('data-game-id');
            const isNowActive = !this.classList.contains('active');
            
            //  Toggle visual state immediately
            this.classList.toggle('active');
            this.classList.toggle('fas'); //  Toggle solid icon
            this.classList.toggle('far'); //  Toggle regular icon
            
            // Send update to server
            const favoriteValue = isNowActive ? 1 : 0;
            sendFeedback(gameId, { favorite: favoriteValue });
        });
    }

    // Star Rating
    const starRatingContainer = document.getElementById('starRating');
    if (starRatingContainer) {
        const stars = starRatingContainer.querySelectorAll('.star');
        const gameId = starRatingContainer.getAttribute('data-game-id');

        //  Function to visually update stars
        function updateStars(rating) {
            stars.forEach(star => {
                const starValue = parseInt(star.getAttribute('data-value'));
                if (starValue <= rating) {
                    star.classList.add('fas');
                    star.classList.remove('far');
                } else {
                    star.classList.add('far');
                    star.classList.remove('fas');
                }
            });
        }

        //  Add click event to each star
        stars.forEach(star => {
            star.addEventListener('click', function() {
                const ratingValue = parseInt(this.getAttribute('data-value'));
                
                //  Update visual state
                updateStars(ratingValue);
                
                //  Send update to server
                sendFeedback(gameId, { rating: ratingValue });
            });
        });
    }
</script>

</body>
</html>