<?php
session_start();
require '../../hub_conn.php'; 

if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header('Location: ../../hub_login.php');
    exit();
}

if (!isset($_GET['game_id']) || !is_numeric($_GET['game_id'])) {
    header('Location: hub_category_logged_in.php'); 
    exit();
}

$game_id = (int)$_GET['game_id'];
$user_id = (int)$_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);

$game = selectGameByID($game_id);
if (!$game) {
    $game_link = 'hub_category_logged_in.php'; 
} else {
    $game_link = $game['game_Link'];
}

$message = '';
$message_type = '';
$survey_finished = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $frequency = $_POST['frequency'] ?? '';
    $open_feedback = $_POST['open_feedback'] ?? '';
    $satisfaction = $_POST['satisfaction'] ?? '';
    $site_feedback = $_POST['site_feedback'] ?? '';

    if (!empty($frequency) && !empty($open_feedback) && !empty($satisfaction) && !empty($site_feedback)) {
        
        $game_feedback_success = upsertGameFeedback($user_id, $game_id, $frequency, $open_feedback);
        $site_feedback_success = upsertSiteFeedback($user_id, $satisfaction, $site_feedback);

        if ($game_feedback_success && $site_feedback_success) {
            $survey_finished = true;
        } else {
            $message = 'There was an error saving your feedback. Please try again.';
            $message_type = 'error';
        }
    } else {
        $message = 'Please fill out all fields from both sections.';
        $message_type = 'error';
    }
}

$existing_game_feedback = selectUserSurveyFeedback($user_id, $game_id);
$current_frequency = $existing_game_feedback['feedback_game_frequency'] ?? '';
$current_open_feedback = $existing_game_feedback['feedback_game_open'] ?? '';

$existing_site_feedback = selectUserSiteFeedback($user_id);
$current_satisfaction = $existing_site_feedback['feedback_site_satisfaction'] ?? '';
$current_site_open_feedback = $existing_site_feedback['feedback_site_open'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Feedback - GameHub</title>
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
        
        html.dark-mode body .content-container,
        html.dark-mode body .modal-container {
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
            transition: color 0.3s; 
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
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background-color: var(--glass-bg-light);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            box-shadow: 0 4px 12px var(--shadow-color);
        }

        h2 {
            color: var(--welcome-title-color);
            text-align: center;
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 10px;
            margin-top: 0;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 1.1em;
            color: var(--label-text-color);
        }

        textarea {
            width: 100%;
            min-height: 120px;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 1em;
            background-color: var(--bg-color);
            color: var(--main-text-color);
            resize: vertical;
        }

        .radio-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .radio-label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-size: 1em;
        }

        .radio-label input {
            margin-right: 10px;
            accent-color: var(--accent-color);
        }

        .btn {
            width: 100%;
            padding: 12px;
            background-color: #2ecc71;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }

        .btn:hover {
            background-color: #27ae60;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
        }

        .back-link:hover { 
            text-decoration: underline; 
        }
        
        .message { 
            padding: 15px; 
            margin-bottom: 20px; 
            border-radius: 4px; 
            font-weight: bold; 
            text-align: center;
        }

        .success { 
            background-color: var(--success-bg); 
            color: var(--success-text); 
            border: 1px solid var(--success-border); 
        }

        .error { 
            background-color: var(--error-bg); 
            color: var(--error-text); 
            border: 1px solid var(--error-border); 
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            z-index: 2000;
            display: none; 
            align-items: center;
            justify-content: center;
        }

        .modal-container {
            background-color: var(--card-bg-color);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            position: relative;
            width: 100%;
            max-width: 500px; 
            margin: 20px;
            text-align: center;
        }

        .modal-container h2 {
            color: var(--welcome-title-color);
            border-bottom: none;
            margin-bottom: 15px;
        }

        .modal-container p {
            font-size: 1.1em;
            color: var(--secondary-text-color);
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .modal-btn {
            display: inline-block;
            padding: 12px 25px;
            font-size: 1em;
            font-weight: bold;
            text-decoration: none;
            border-radius: 6px;
            transition: all 0.2s;
            border: 2px solid var(--accent-color);
            background-color: var(--accent-color);
            color: white;
        }

        .modal-btn.secondary {
            background-color: var(--card-bg-color);
            color: var(--accent-color);
        }

        .modal-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            background-color: var(--accent-color-darker);
            border-color: var(--accent-color-darker);
        }

        .modal-btn.secondary:hover {
            background-color: var(--bg-color);
            border-color: var(--accent-color);
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

    <a href="hub_category_logged_in.php" class="back-link">
        <i class="fas fa-chevron-left"></i> Back to Game Detail
    </a>

    <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="hub_survey.php?game_id=<?php echo $game_id; ?>">
        
        <h2>Feedback for: <?php echo htmlspecialchars($game['game_name']); ?></h2>
        
        <div class="form-group">
            <label>How often do you play this game?</label>
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="frequency" value="frequency_0" <?php echo ($current_frequency == 'frequency_0') ? 'checked' : ''; ?> required>
                    Daily
                </label>
                <label class="radio-label">
                    <input type="radio" name="frequency" value="frequency_1" <?php echo ($current_frequency == 'frequency_1') ? 'checked' : ''; ?>>
                    Once a Week
                </label>
                <label class="radio-label">
                    <input type="radio" name="frequency" value="frequency_2" <?php echo ($current_frequency == 'frequency_2') ? 'checked' : ''; ?>>
                    Once a Month
                </label>
                <label class="radio-label">
                    <input type="radio" name="frequency" value="frequency_3" <?php echo ($current_frequency == 'frequency_3') ? 'checked' : ''; ?>>
                    Less than once a Month
                </label>
            </div>
        </div>

        <div class="form-group">
            <label for="open_feedback">What do you think about this game?</label>
            <textarea id="open_feedback" name="open_feedback" placeholder="Share your thoughts..." required><?php echo htmlspecialchars($current_open_feedback); ?></textarea>
        </div>

        
        <h2 style="margin-top: 40px;">Site Feedback</h2>

        <div class="form-group">
            <label>How satisfied are you with the site?</label>
            <div class="radio-group">
                <label class="radio-label">
                    <input type="radio" name="satisfaction" value="satisfaction_4" <?php echo ($current_satisfaction == 'satisfaction_4') ? 'checked' : ''; ?> required>
                    Fully Satisfied
                </label>
                <label class="radio-label">
                    <input type="radio" name="satisfaction" value="satisfaction_3" <?php echo ($current_satisfaction == 'satisfaction_3') ? 'checked' : ''; ?>>
                    Satisfied
                </label>
                <label class="radio-label">
                    <input type="radio" name="satisfaction" value="satisfaction_2" <?php echo ($current_satisfaction == 'satisfaction_2') ? 'checked' : ''; ?>>
                    Neutral
                </label>
                <label class="radio-label">
                    <input type="radio" name="satisfaction" value="satisfaction_1" <?php echo ($current_satisfaction == 'satisfaction_1') ? 'checked' : ''; ?>>
                    Dissatisfied
                </label>
                 <label class="radio-label">
                    <input type="radio" name="satisfaction" value="satisfaction_0" <?php echo ($current_satisfaction == 'satisfaction_0') ? 'checked' : ''; ?>>
                    Totally Dissatisfied
                </label>
            </div>
        </div>

        <div class="form-group">
            <label for="site_feedback">How can we make this site better?</label>
            <textarea id="site_feedback" name="site_feedback" placeholder="Share your thoughts on site design, features, or any bugs you found..."><?php echo htmlspecialchars($current_site_open_feedback); ?></textarea>
        </div>

        <button type="submit" class="btn">Submit All Feedback</button>
    </form>

</div>

<?php
    
    include '../../modals/main/logged_in/hub_survey_finished.php';
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
    
    
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'flex';
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'none';
    }
    
    
    <?php if ($survey_finished): ?>
        openModal('surveyFinishedModal');
    <?php endif; ?>
</script>

</body>
</html>