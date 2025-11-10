<?php
session_start();
require '../../hub_conn.php'; 

if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header('Location: ../../hub_login.php');
    exit();
}

if (!isset($_GET['game_id']) || !is_numeric($_GET['game_id'])) {
    header('Location: ../logged_in/hub_home_category_logged_in.php'); //  Redirect if no valid game ID
    exit();
}

$game_id = (int)$_GET['game_id'];
$user_id = (int)$_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);
$game = selectGameByID($game_id);

if (!$game) {
    header('Location: ../logged_in/hub_home_category_logged_in.php');
    exit();
}

$message = '';
$message_type = '';

//  Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $frequency = $_POST['frequency'] ?? '';
    $open_feedback = $_POST['open_feedback'] ?? '';

    if (!empty($frequency) && !empty($open_feedback)) {
        if (upsertGameFeedback($user_id, $game_id, $frequency, $open_feedback)) {
            
            //  --- MODIFICATION: Redirect to site survey page on success ---
            //  We must pass the game_id so the "Go to Game" button works
            header('Location: hub_survey_site.php?game_id=' . $game_id);
            exit();

        } else {
            $message = 'There was an error saving your feedback. Please try again.';
            $message_type = 'error';
        }
    } else {
        $message = 'Please fill out all fields.';
        $message_type = 'error';
    }
}

//  Fetch existing feedback if exists
$existing_feedback = selectUserSurveyFeedback($user_id, $game_id);
$current_frequency = $existing_feedback['feedback_game_frequency'] ?? '';
$current_open_feedback = $existing_feedback['feedback_game_open'] ?? '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback for <?php echo htmlspecialchars($game['game_name']); ?> - GameHub</title>
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
            --border-color: #ddd;
            --welcome-title-color: #2c3e50;
            --label-text-color: #555;
            --success-bg: #d4edda;
            --success-text: #155724;
            --success-border: #c3e6cb;
            --error-bg: #f8d7da;
            --error-text: #721c24;
            --error-border: #f5c6cb;
        }
        
        /* Dark Mode Override */
        html.dark-mode body {
            --bg-color: #121212; 
            --main-text-color: #f4f4f4; 
            --accent-color: #4dc2f9;
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

        /* Base Setup */
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 0; 
            background-color: var(--bg-color); 
            color: var(--main-text-color); 
            min-height: 100vh; 
            transition: background-color 0.3s, color 0.3s; 
        }

        
        /* Header (Top Bar) */
        .header { 
            background-color: var(--card-bg-color); 
            padding: 15px 30px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 2px 4px var(--shadow-color); 
            position: sticky; top: 0; z-index: 1001; 
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
            transition: background-color 0.2s; 
            cursor: pointer; 
        }
        .side-menu a:hover, .menu-item:hover { 
            background-color: var(--bg-color); 
            color: var(--accent-color); 
        }
        .side-menu a.active { 
            background-color: var(--accent-color); 
            color: white; 
            font-weight: bold; 
        }
        .side-menu a.active:hover { 
            background-color: #2980b9; 
        }
        .menu-divider { border-top: 1px solid var(--secondary-text-color); margin: 5px 0; }
        .logout-link { color: #e74c3c !important; font-weight: bold; }
        .icon { margin-right: 10px; width: 20px; text-align: center; }
        .dark-mode-label { display: flex; justify-content: space-between; align-items: center; user-select: none; }
        
        /* --- 4. Content & Form Styles --- */
        .content-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background-color: var(--card-bg-color);
            border-radius: 8px;
            box-shadow: 0 4px 12px var(--shadow-color);
        }
        h2 {
            color: var(--welcome-title-color);
            text-align: center;
            border-bottom: 2px solid var(--accent-color);
            padding-bottom: 10px;
            margin-top: 0;
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
            background-color: #8e44ad; /* Purple 'Next' color */
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 10px;
        }
        .btn:hover {
            background-color: #9b59b6;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover { text-decoration: underline; }
        
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

    </style>

    <script>
        // Local Storage; Essential for Dark Mode Fix
        (function() {
            const localStorageKey = 'gamehubDarkMode'; 
            if (localStorage.getItem(localStorageKey) === 'dark') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>

</head>
<body id="appBody"> <div class="header">
    <div class="logo">GAMEHUB</div>
    <button class="menu-toggle" id="menuToggle">
        <i class="fas fa-bars"></i>
    </button>
</div>

<div class="side-menu" id="sideMenu">
    <a href="../logged_in/hub_home_logged_in.php"><span class="icon"><i class="fas fa-home"></i></span>Home</a>
    <a href="../logged_in/hub_home_category_logged_in.php" class="active"><span class="icon"><i class="fas fa-book-open"></i></span>Library</a> 
    <a href="../logged_in/hub_main_profile.php"><span class="icon"><i class="fas fa-user-circle"></i></span>Profile</a>
    <a href="../logged_in/hub_main_about_logged_in.php"><span class="icon"><i class="fas fa-info-circle"></i></span>About</a>
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

    <a href="../logged_in/hub_game_detail_logged_in.php?game_id=<?php echo $game_id; ?>" class="back-link">
        <i class="fas fa-chevron-left"></i> Back to Game Detail
    </a>

    <h2>Feedback for: <?php echo htmlspecialchars($game['game_name']); ?></h2>

    <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
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

        <button type="submit" class="btn">Next</button>
    </form>

</div>

<script>
    document.getElementById('menuToggle').addEventListener('click', function() {
        const menu = document.getElementById('sideMenu');
        menu.classList.toggle('open');
    });

    // Updated Dark Mode
    const darkModeText = document.getElementById('darkModeText');
    const localStorageKey = 'gamehubDarkMode';
    const htmlElement = document.documentElement; //  Target the <html> tag

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