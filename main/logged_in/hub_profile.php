<?php
session_start();
require '../../hub_conn.php'; 

if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header('Location: ../../modals/hub_login.php');
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);
$email = htmlspecialchars($_SESSION['email']); 

$username_error = '';
$username_success = '';
$password_error = '';
$password_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    
    if ($_POST['action'] === 'change_username') {
        $new_username = $_POST['new_username'] ?? '';
        $current_password = $_POST['current_password'] ?? '';

        if (empty($new_username) || empty($current_password)) {
            $username_error = "All fields are required.";
        } else {
            $result = updateUsername($user_id, $new_username, $current_password);
            if ($result === "success") {
                $username_success = "Username updated successfully!";
                $username = htmlspecialchars($new_username); 
            } else {
                $username_error = $result; 
            }
        }
    }

    
    if ($_POST['action'] === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_new_password = $_POST['confirm_new_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
            $password_error = "All fields are required.";
        } elseif ($new_password !== $confirm_new_password) {
            $password_error = "New passwords do not match.";
        } elseif (strlen($new_password) < 8) {
            $password_error = "Password must be at least 8 characters long.";
        } else {
            $result = updateUserPasswordSecurely($user_id, $current_password, $new_password);
            if ($result === "success") {
                $password_success = "Password updated successfully!";
            } else {
                $password_error = $result; 
            }
        }
    }
}


$games_list = selectUserInteractedGames($user_id);
$fallback_cover = 'uploads/placeholder.png'; 

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - GameHub</title>
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
            --label-text-color: #555;
            --success-bg: #d4edda;
            --success-text: #155724;
            --success-border: #c3e6cb;
            --error-bg: #f8d7da;
            --error-text: #721c24;
            --error-border: #f5c6cb;
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
            --label-text-color: #bbb;
            --success-bg: #1a3a24;
            --success-text: #d4edda;
            --success-border: #2a5c3a;
            --error-bg: #3a1a1f;
            --error-text: #f8d7da;
            --error-border: #5c2a30;
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
        
        .profile-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 30px;
        }

        .account-panel, 
        .games-panel {
            background-color: var(--card-bg-color);
            border-radius: 8px;
            box-shadow: 0 4px 12px var(--shadow-color);
            padding: 25px;
        }

        .account-panel h2 {
            margin-top: 0;
            color: var(--welcome-title-color);
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 10px;
        }

        .user-details p {
            font-size: 1.1em;
            color: var(--secondary-text-color);
            word-wrap: break-word;
        }

        .user-details p strong {
            color: var(--main-text-color);
        }

        .account-btn {
            display: block;
            width: 100%;
            padding: 10px;
            font-size: 1em;
            font-weight: bold;
            color: var(--accent-color);
            background-color: var(--bg-color);
            border: 2px solid var(--border-color);
            border-radius: 6px;
            cursor: pointer;
            margin-top: 15px;
            transition: all 0.2s;
        }

        .account-btn:hover {
            border-color: var(--accent-color);
            background-color: var(--card-bg-color);
        }
        
        .games-panel h2 {
            margin-top: 0;
            color: var(--welcome-title-color);
        }
        
        .sort-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 15px;
            background-color: var(--bg-color);
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .sort-controls label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-weight: 500;
        }

        .sort-controls input[type="radio"] {
            margin-right: 5px;
            accent-color: var(--accent-color);
        }

        .game-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .game-card {
            background-color: var(--bg-color);
            border-radius: 8px;
            overflow: hidden;
            text-decoration: none;
            color: var(--main-text-color);
            box-shadow: 0 2px 5px var(--shadow-color);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }

        .game-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px var(--shadow-color);
        }

        .game-card img {
            width: 100%;
            height: auto;
            aspect-ratio: 460 / 215;
            object-fit: cover;
            display: block;
            border-bottom: 3px solid var(--accent-color);
        }

        .game-card-info {
            padding: 15px;
        }

        .game-card-title {
            font-weight: bold;
            font-size: 1.1em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin: 0 0 10px 0;
        }

        .game-card-stats {
            display: flex;
            justify-content: space-between;
            font-size: 0.9em;
            color: var(--secondary-text-color);
        }

        .game-card-stats .stat-rating {
            color: #f39c12;
            font-weight: bold;
        }

        .game-card-stats .stat-fav {
            color: #e74c3c;
        }

        .game-card-stats .icon {
            margin-right: 5px;
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
            left: 15px;
            right: auto; 
            font-size: 28px;
            font-weight: bold;
            color: var(--secondary-text-color);
            background: none;
            border: none;
            cursor: pointer;
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
            color: var(--label-text-color);
        }

        .modal-container input[type="text"],
        .modal-container input[type="password"] {
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
            background-color: var(--accent-color); 
            color: white; 
            border: none; 
            border-radius: 4px; 
            font-size: 18px;
            cursor: pointer; 
            transition: background-color 0.3s; 
            margin-top: 10px;
        }

        .modal-container .btn:hover { 
            background-color: var(--accent-color-darker); 
        }

        .modal-container .message { 
            padding: 10px; 
            border-radius: 4px; 
            margin-bottom: 15px; 
            text-align: center; 
            font-weight: bold;
        }

        .modal-container .error { 
            background-color: var(--error-bg); 
            color: var(--error-text); 
            border: 1px solid var(--error-border); 
        }

        .modal-container .success { 
            background-color: var(--success-bg); 
            color: var(--success-text); 
            border: 1px solid var(--success-border); 
        }

        #gameDetailModal .modal-container {
            max-width: 1000px;
        }

        .modal-container .game-detail-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: flex-start;
        }

        .modal-container .image-slideshow {
            position: relative;
            width: 100%;
            height: 0;
            padding-bottom: 56.25%;
            overflow: hidden;
            border-radius: 8px;
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            margin-top: 30px;
        }

        .modal-container .slide { 
            position: absolute; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            opacity: 0; 
            transition: opacity 1s ease-in-out; 
        }

        .modal-container .slide.active { 
            opacity: 1; 
        }

        .modal-container .slide img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
        }

        .modal-container .slider-control { 
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

        .modal-container .slider-control:hover { 
            background: rgba(0, 0, 0, 0.6); 
        }

        .modal-container .prev { 
            left: 0; 
            border-radius: 0 5px 5px 0; 
        }

        .modal-container .next { 
            right: 0; 
            border-radius: 5px 0 0 5px; 
        }

        .modal-container .slide-indicators { 
            position: absolute; 
            bottom: 10px; 
            left: 50%; 
            transform: translateX(-50%); 
            z-index: 10; 
            display: flex; 
            gap: 5px; 
        }

        .modal-container .dot { 
            display: inline-block; 
            width: 10px; 
            height: 10px; 
            background: rgba(255, 255, 255, 0.5); 
            border-radius: 50%; 
            cursor: pointer; 
            transition: background 0.3s; 
        }

        .modal-container .dot.active { 
            background: white; 
        }

        .modal-container .game-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .modal-container .game-title-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
        }

        .modal-container .game-title {
            font-size: 2.5em;
            font-weight: 600;
            color: var(--welcome-title-color);
            margin: 0;
            line-height: 1.1;
        }

        .modal-container .game-desc {
            font-size: 1.1em;
            color: var(--secondary-text-color);
            line-height: 1.6;
            max-height: 150px;
            overflow-y: auto;
        }

        .modal-container .favorite-icon {
            font-size: 2.5em;
            color: var(--border-color);
            cursor: pointer;
            transition: color 0.2s, transform 0.2s;
        }

        .modal-container .favorite-icon.active {
            color: var(--heart-color);
            transform: scale(1.1);
        }

        .modal-container .star-rating {
            font-size: 2em;
            color: var(--star-color);
        }

        .modal-container .star-rating .star {
            cursor: pointer;
            transition: transform 0.1s;
        }

        .modal-container .star-rating .star:hover {
            transform: scale(1.2);
        }

        .modal-container .trailer-link, 
        .modal-container .next-link {
            display: inline-block;
            padding: 12px 20px;
            font-size: 1.1em;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .modal-container .trailer-link {
            background-color: var(--card-bg-color);
            color: var(--accent-color);
            border: 2px solid var(--accent-color);
        }

        .modal-container .trailer-link:hover {
            background-color: var(--accent-color);
            color: white;
        }

        .modal-container .next-link {
            background-color: #8e44ad;
            color: white;
            border: 2px solid #8e44ad;
            margin-top: 10px;
        }

        .modal-container .next-link:hover {
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
    <a href="hub_category_logged_in.php"><span class="icon"><i class="fas fa-book-open"></i></span>Library</a> 
    <a href="hub_profile.php" class="active"><span class="icon"><i class="fas fa-user-circle"></i></span>Profile</a>
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

<div class="profile-container">

    <aside class="account-panel">
        <h2>ACCOUNT</h2>
        <div class="user-details">
            <p><strong>Username:</strong> <span id="currentUsername"><?php echo $username; ?></span></p>
            <p><strong>Email:</strong> <?php echo $email; ?></p>
        </div>
        <button class="account-btn" onclick="openModal('changeUsernameModal')">Change Username</button>
        <button class="account-btn" onclick="openModal('changePasswordModal')">Change Password</button>
    </aside>

    <main class="games-panel">
        <h2>YOUR GAMES</h2>
        <div class="sort-controls" id="sortControls">
            <label>
                <input type="radio" name="sort" value="alpha" checked>
                Alphabetical
            </label>
            <label>
                <input type="radio" name="sort" value="rating">
                Your Rating
            </label>
            <label>
                <input type="radio" name="sort" value="category">
                Category
            </label>
            <label>
                <input type="checkbox" id="showFavourites">
                Show Favourites Only
            </label>
        </div>

        <div class="game-grid" id="gameGrid">
            <?php if (!empty($games_list)): ?>
                <?php foreach ($games_list as $game): ?>
                    <?php
                    $cover_path = !empty($game['cover_path']) ? $game['cover_path'] : $fallback_cover;
                    ?>
                    <a class="game-card"
                       onclick="openGameModal(<?php echo $game['game_id']; ?>); return false;"
                       data-name="<?php echo htmlspecialchars($game['game_name']); ?>"
                       data-category="<?php echo htmlspecialchars($game['game_category']); ?>"
                       data-rating="<?php echo htmlspecialchars($game['user_rating']); ?>"
                       data-favourite="<?php echo htmlspecialchars($game['user_favourite']); ?>">
                        
                        <img src="../../<?php echo htmlspecialchars($cover_path); ?>" alt="<?php echo htmlspecialchars($game['game_name']); ?> Cover">
                        
                        <div class="game-card-info">
                            <h3 class="game-card-title"><?php echo htmlspecialchars($game['game_name']); ?></h3>
                            <div class="game-card-stats">
                                <span class="stat-rating">
                                    <i class="icon <?php echo ($game['user_rating'] > 0) ? 'fas' : 'far'; ?> fa-star"></i>
                                    <?php echo $game['user_rating'] > 0 ? $game['user_rating'] : '-'; ?>/5
                                </span>
                                <?php if ($game['user_favourite'] == 1): ?>
                                    <span class="stat-fav">
                                        <i class="icon fas fa-heart"></i>
                                        Favourite
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>You have not rated or favourited any games yet. Go to the Library to get started!</p>
            <?php endif; ?>
        </div>
    </main>

</div>

<?php
    
    include '../../modals/main/logged_in/hub_profile_change_username.php';
    include '../../modals/main/logged_in/hub_profile_change_password.php';
    
    include '../../modals/main/logged_in/hub_profile_game_details.php';
?>

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

    
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'flex';
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'none';
        
        
        const successMsg = modal.querySelector('.success');
        const errorMsg = modal.querySelector('.error');
        if (successMsg) successMsg.style.display = 'none';
        if (errorMsg) errorMsg.style.display = 'none';
    }
    
    
    <?php if (!empty($username_error) || !empty($username_success)): ?>
        openModal('changeUsernameModal');
    <?php elseif (!empty($password_error) || !empty($password_success)): ?>
        openModal('changePasswordModal');
    <?php endif; ?>

    
    document.addEventListener('DOMContentLoaded', function() {
        const controls = document.getElementById('sortControls');
        const grid = document.getElementById('gameGrid');
        
        const allGames = Array.from(grid.getElementsByClassName('game-card'));

        function applySortAndFilter() {
            const sortType = controls.querySelector('input[name="sort"]:checked').value;
            const showFavourites = controls.querySelector('#showFavourites').checked;

            
            let filteredGames = allGames.filter(game => {
                if (showFavourites) {
                    return game.dataset.favourite == '1';
                }
                
                return true; 
            });

            
            if (sortType === 'alpha') {
                filteredGames.sort((a, b) => a.dataset.name.localeCompare(b.dataset.name));
            } else if (sortType === 'rating') {
                filteredGames.sort((a, b) => b.dataset.rating - a.dataset.rating);
            } else if (sortType === 'category') {
                filteredGames.sort((a, b) => {
                    if (a.dataset.category === b.dataset.category) {
                        
                        return a.dataset.name.localeCompare(b.dataset.name);
                    }
                    return a.dataset.category.localeCompare(b.dataset.category);
                });
            }

            
            grid.innerHTML = '';
            filteredGames.forEach(game => grid.appendChild(game));
            
            if (filteredGames.length === 0 && allGames.length > 0) {
                 grid.innerHTML = '<p>No games match your criteria.</p>';
            } else if (allGames.length === 0) {
                 grid.innerHTML = '<p>You have not rated, favourited, or surveyed any games yet. Go to the Library to get started!</p>';
            }
        }

        
        controls.addEventListener('change', applySortAndFilter);
        
        
        if (allGames.length === 0) {
             grid.innerHTML = '<p>You have not rated, favourited, or surveyed any games yet. Go to the Library to get started!</p>';
        }
    });

    
    
    
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
        document.getElementById('modalGameLink').href = game.game_Link;
        document.getElementById('modalSurveyLink').href = `../survey/hub_survey_game.php?game_id=${game.game_id}`;

        
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
        document.getElementById('modalGameLink').href = '#';
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