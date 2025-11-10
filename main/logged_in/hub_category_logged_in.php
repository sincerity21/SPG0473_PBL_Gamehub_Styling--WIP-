<?php
session_start();
require '../../hub_conn.php'; 

if (!isset($_SESSION['username'])) {
    header('Location: ../../hub_login.php');
    exit();
}

$username = htmlspecialchars($_SESSION['username']);

$categories = selectAllGameCategories();
$games = selectAllGamesWithCovers(); 

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
        
        html.dark-mode body .modal-container {
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
            background-color: var(--glass-bg-light);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 5px 15px var(--shadow-color);
            position: relative;
            width: 100%;
            max-width: 1000px;
            color: var(--main-text-color);
            margin: 20px;
        }

        .modal-close {
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
        
        .modal-close:hover {
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
            max-height: 150px;
            overflow-y: auto;
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
            transform: scale(1.1);
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
            margin-top: 10px;
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
    <a href="hub_home_logged_in.php"><span class="icon"><i class="fas fa-home"></i></span>Home</a>
    <a href="hub_category_logged_in.php" class="active"><span class="icon"><i class="fas fa-book-open"></i></span>Library</a> 
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
                
                $cover_path = !empty($game['cover_path']) ? $game['cover_path'] : $fallback_cover;
                ?>
                <a href="#" 
                   class="game-card" 
                   onclick="openGameModal(<?php echo $game['game_id']; ?>); return false;"
                   data-category="<?php echo htmlspecialchars($game['game_category']); ?>">
                    
                    <img src="../../<?php echo htmlspecialchars($cover_path); ?>" alt="<?php echo htmlspecialchars($game['game_name']); ?> Cover">
                    
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No games have been added to the hub yet.</p>
        <?php endif; ?>
    </div>

</div>

<?php
    include '../../modals/main/logged_in/hub_category_game_detail_logged_in.php';
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
    
    
    let modalCurrentSlide = 0;
    let modalSlides = [];
    let modalDots = [];
    let modalTotalSlides = 0;
    let modalSlideTimer = null;
    const modalFallbackImg = '../../uploads/placeholder.png';
    
    
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

    
    async function sendModalFeedback(gameId, feedbackData) {
        try {
            const response = await fetch('../../hub_update_feedback.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ game_id: gameId, ...feedbackData })
            });
            if (!response.ok) console.error('Feedback update failed:', response.statusText);
        } catch (error) {
            console.error('Error sending feedback:', error);
        }
    }

    function initializeModalFeedback(gameId) {
        const favoriteIcon = document.getElementById('modalFavoriteIcon');
        const starRatingContainer = document.getElementById('modalStarRating');
        
        favoriteIcon.setAttribute('data-game-id', gameId);
        starRatingContainer.setAttribute('data-game-id', gameId);

        
        favoriteIcon.onclick = function() {
            const isNowActive = !this.classList.contains('active');
            this.classList.toggle('active');
            this.classList.toggle('fas');
            this.classList.toggle('far');
            sendModalFeedback(gameId, { favorite: isNowActive ? 1 : 0 });
        };

        
        const stars = starRatingContainer.querySelectorAll('.star');
        
        function updateModalStars(rating) {
            stars.forEach(star => {
                const starValue = parseInt(star.getAttribute('data-value'));
                star.classList.toggle('fas', starValue <= rating);
                star.classList.toggle('far', starValue > rating);
            });
        }

        stars.forEach(star => {
            star.onclick = function() {
                const ratingValue = parseInt(this.getAttribute('data-value'));
                updateModalStars(ratingValue);
                sendModalFeedback(gameId, { rating: ratingValue });
            };
        });
    }
    
    
    function populateGameModal(data) {
        const game = data.details;
        const gallery = data.gallery;
        const feedback = data.feedback;

        
        document.getElementById('modalGameTitle').textContent = game.game_name;
        document.getElementById('modalGameDesc').innerHTML = game.game_desc.replace(/\n/g, '<br>'); 
        document.getElementById('modalTrailerLink').href = game.game_trailerLink;
        document.getElementById('modalSurveyLink').href = `hub_survey.php?game_id=${game.game_id}`;

        
        const favoriteIcon = document.getElementById('modalFavoriteIcon');
        favoriteIcon.className = 'favorite-icon fa-heart'; 
        if (feedback.favorite_game == 1) {
            favoriteIcon.classList.add('fas', 'active');
        } else {
            favoriteIcon.classList.add('far');
        }
        
        const starContainer = document.getElementById('modalStarRating');
        let starHTML = '';
        for (let i = 1; i <= 5; i++) {
            const iconClass = (i <= feedback.game_rating) ? 'fas' : 'far';
            starHTML += `<i class="star ${iconClass} fa-star" data-value="${i}"></i>`;
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
                            <img src="../../${image.img_path}" alt="Game Screenshot">
                          </div>`;
            if (imagesToUse.length > 1) {
                 indicatorHTML += `<span class="dot ${activeClass}" data-index="${index}"></span>`;
            }
        });
        
        slideContainer.innerHTML = slideHTML;
        indicatorContainer.innerHTML = indicatorHTML;
        
        
        initializeModalSlideshow();
        initializeModalFeedback(game.game_id);
    }

    function setModalLoading() {
        stopModalAutoSlide();
        document.getElementById('modalGameTitle').textContent = 'Loading...';
        document.getElementById('modalGameDesc').textContent = 'Loading game description...';
        document.getElementById('modalTrailerLink').href = '#';
        document.getElementById('modalSurveyLink').href = '#';
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
        setModalLoading();
        
        try {
            const response = await fetch(`../../hub_fetch_game_details.php?game_id=${gameId}`);
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