<?php
session_start();
require '../../hub_conn.php';

if (!isset($_SESSION['username'])) {
    header('Location: ../../hub_login.php');
    exit();
}

// Define root path for file handling
define('ROOT_PATH', __DIR__ . '/../../'); 
$upload_dir = 'uploads/images/';
$error = '';
$game_to_edit = null;

// --- NEW: Handle All POST Actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    // --- HANDLE ADD GAME ---
    if ($_POST['action'] === 'add_game') {
        $game_category = $_POST['game_category'];
        $game_name = $_POST['game_name'];
        $game_desc = $_POST['game_desc'];
        $game_trailerLink = $_POST['game_trailerLink'];
        $game_Link = $_POST['game_Link']; // <-- ADDED
        $game_img_filename = ''; // Default to empty string

        if (isset($_FILES['game_img']) && $_FILES['game_img']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['game_img']['tmp_name'];
            $file_name = $_FILES['game_img']['name'];
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_file_name = uniqid('game_img_', true) . '.' . $ext;
            $dest_path = ROOT_PATH . $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $game_img_filename = $upload_dir . $new_file_name; 
            } else {
                $error = "Error uploading file. Check directory permissions.";
            }
        }

        if (!$error) {
            // MODIFIED function call
            $result = addNewGame($game_category, $game_name, $game_desc, $game_img_filename, $game_trailerLink, $game_Link);
            if ($result) {
                header('Location: hub_admin_games.php?status=added');
                exit();
            } else {
                $error = "Database insertion failed.";
            }
        }
    }

    // --- HANDLE EDIT GAME ---
    if ($_POST['action'] === 'edit_game') {
        $id = (int)$_POST['game_id'];
        $game_name = $_POST['game_name'];
        $game_category = $_POST['game_category'];
        $game_desc = $_POST['game_desc'];
        $game_trailerLink = $_POST['game_trailerLink'];
        $game_Link = $_POST['game_Link']; // <-- ADDED

        // Get current game data to find old image path
        $game = selectGameByID($id);
        if ($game) {
            $game_img_filename = $game['game_img']; // Start with the existing image

            // Check if a new file was uploaded
            if (isset($_FILES['game_img']) && $_FILES['game_img']['error'] === UPLOAD_ERR_OK) {
                $file_tmp_path = $_FILES['game_img']['tmp_name'];
                $file_name = $_FILES['game_img']['name'];
                $ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $new_file_name = uniqid('game_img_', true) . '.' . $ext;
                $dest_path = ROOT_PATH . $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp_path, $dest_path)) {
                    $game_img_filename = $upload_dir . $new_file_name; // Set new path
                    // Delete the old file
                    if (!empty($game['game_img']) && file_exists(ROOT_PATH . $game['game_img'])) {
                         unlink(ROOT_PATH . $game['game_img']);
                    }
                } else {
                    $error = "Error uploading new file. Check directory permissions.";
                }
            }

            // Update database if no upload error
            if (!$error) {
                // MODIFIED function call
                $result = updateGameByID($id, $game_name, $game_category, $game_desc, $game_img_filename, $game_trailerLink, $game_Link);
                if ($result) {
                    header('Location: hub_admin_games.php?status=updated'); 
                    exit();
                } else {
                    $error = "Database update failed.";
                }
            }
        } else {
            $error = "Game not found for update.";
        }
    }
}

// --- NEW: Check for GET actions (to open modals) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $game_to_edit = selectGameByID((int)$_GET['id']);
    }
}

$games = selectAllGames();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Listing - Game Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* ... (All CSS rules from file) ... */
        :root {
            --bg-color: #f4f7f6; --main-text-color: #333; --card-bg-color: white;
            --shadow-color: rgba(0, 0, 0, 0.1); --border-color: #ddd; --header-text-color: #2c3e50;
            --accent-color: #3498db; --accent-text-color: white; --hover-bg-color: #f5f5f5;
            --zebra-bg-color: #f9f9f9; --label-text-color: #555;
        }
        html.dark-mode body {
            --bg-color: #121212; --main-text-color: #f4f4f4; --card-bg-color: #1e1e1e;
            --shadow-color: rgba(0, 0, 0, 0.4); --border-color: #444; --header-text-color: #ecf0f1;
            --accent-color: #4dc2f9; --accent-text-color: #1e1e1e; --hover-bg-color: #2c2c2c;
            --zebra-bg-color: #222; --label-text-color: #bbb;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0;
            background-color: var(--bg-color); color: var(--main-text-color); transition: background-color 0.3s, color 0.3s;
        }
        .navbar {
            background-color: #2c3e50; overflow: hidden; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .navbar a {
            float: left; display: block; color: white; text-align: center;
            padding: 16px 20px; text-decoration: none; transition: background-color 0.3s;
        }
        .navbar a:hover { background-color: #34495e; }
        .content { padding: 30px; max-width: 1200px; margin: 0 auto; }
        h1 {
            color: var(--header-text-color); margin-bottom: 20px;
            border-bottom: 2px solid var(--accent-color); padding-bottom: 5px;
        }
        table {
            width: 100%; border-collapse: collapse; box-shadow: 0 4px 8px var(--shadow-color);
            background-color: var(--card-bg-color); margin-top: 20px;
        }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border-color); vertical-align: top; }
        th {
            background-color: var(--accent-color); color: var(--accent-text-color);
            font-weight: 600; text-transform: uppercase;
        }
        tr:hover { background-color: var(--hover-bg-color); }
        tr:nth-child(even) { background-color: var(--zebra-bg-color); }
        td a { color: #2980b9; text-decoration: none; margin-right: 5px; }
        td a:hover { text-decoration: underline; }
        .add-link {
            display: inline-block; padding: 10px 15px; margin-bottom: 20px;
            background-color: #2ecc71; color: white; text-decoration: none;
            border-radius: 5px; font-weight: bold; transition: background-color 0.3s;
        }
        .add-link:hover { background-color: #27ae60; }
        .game-image { max-width: 80px; height: auto; display: block; }
        .navbar a.active { background-color: #1abc9c; }
        .dark-mode-switch {
            float: right; padding: 16px 20px; cursor: pointer;
            color: white; font-size: 1.1em; transition: color 0.3s;
        }
        .dark-mode-switch:hover { color: #1abc9c; }
        .search-container {
            margin-bottom: 20px;
        }
        #gameSearchInput {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            background-color: var(--card-bg-color);
            color: var(--main-text-color);
            border-radius: 4px;
            box-sizing: border-box; 
            font-size: 16px;
        }
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.7); z-index: 2000; display: none;
            align-items: center; justify-content: center; overflow-y: auto;
        }
        .modal-container {
            background-color: var(--card-bg-color); padding: 30px; border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); position: relative;
            width: 100%; max-width: 600px; color: var(--main-text-color); margin: 20px;
        }
        .modal-close {
            position: absolute; top: 10px; right: 15px; font-size: 28px;
            font-weight: bold; color: var(--secondary-text-color);
            background: none; border: none; cursor: pointer;
        }
        .modal-container h2 {
            color: var(--welcome-title-color); text-align: center; margin-top: 0;
            margin-bottom: 25px; border-bottom: 2px solid var(--accent-color); padding-bottom: 10px;
        }
        .modal-container .form-group { margin-bottom: 20px; }
        .modal-container label {
            display: block; margin-bottom: 8px; font-weight: bold;
            color: var(--label-text-color);
        }
        .modal-container input[type="text"],
        .modal-container input[type="email"],
        .modal-container input[type="password"],
        .modal-container input[type="file"],
        .modal-container select,
        .modal-container textarea {
            width: 100%; padding: 10px; border: 1px solid var(--border-color);
            border-radius: 4px; box-sizing: border-box; font-size: 16px;
            background-color: var(--bg-color); color: var(--main-text-color);
        }
        .modal-container textarea { resize: vertical; }
        .modal-container .btn {
            width: 100%; padding: 12px; background-color: var(--accent-color); 
            color: white; border: none; border-radius: 4px; font-size: 18px;
            cursor: pointer; transition: background-color 0.3s; margin-top: 10px;
        }
        .modal-container .btn:hover { background-color: #2980b9; }
        .modal-container .error {
            background-color: #fdd; color: #c00; padding: 10px; 
            border: 1px solid #f99; border-radius: 4px;
            margin-bottom: 15px; text-align: center; font-weight: bold;
        }
        .current-img { 
            max-width: 150px; height: auto; display: block; margin: 10px 0; 
            border: 1px solid var(--border-color); border-radius: 4px;
        }
    </style>

    <script>
    (function() {
        const localStorageKey = 'adminGamehubDarkMode'; // <-- Note the different key
        if (localStorage.getItem(localStorageKey) === 'dark') {
            document.documentElement.classList.add('dark-mode');
        }
    })();
</script>
</head>
<body id="appBody">
    <div class="navbar">
        <a href="../user/hub_admin_user.php">Admin Home</a>
        <a href="hub_admin_games.php" class="active">Manage Games</a>
        <a href="../../hub_logout.php">Logout</a>

        <div class="dark-mode-switch" onclick="toggleDarkMode()">
            <i class="fas fa-moon" id="darkModeIcon"></i>
        </div>
    </div>

    <div class="content">
        <h1>Game Listing</h1>

        <div class="search-container">
            <input type="text" id="gameSearchInput" placeholder="Search for games by name or description...">
        </div>
        
        <a href="hub_admin_games.php?action=add" class="add-link">➕ Add New Game</a>
        
        <?php if ($error): ?>
            <div class="modal-container error" style="display:block; max-width: 1140px; box-sizing: border-box;"><?php echo $error; ?></div>
        <?php endif; ?>
    
        <table id="gameTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Image</th>
                    <th>Trailer Link</th>
                    <th>Game Link</th> <th>Action</th>
                </tr>
            </thead>
            <tbody id="gameTableBody">
            <?php foreach ($games as $game): ?>
                <tr>
                    <td><?php echo htmlspecialchars($game['game_id']); ?></td>
                    <td><?php echo htmlspecialchars($game['game_category']); ?></td>
                    <td><?php echo htmlspecialchars($game['game_name']); ?></td>
                    <td style="max-width: 300px;"><?php echo htmlspecialchars($game['game_desc']); ?></td> 
                    <td>
                        <?php if ($game['game_img']): ?>
                            <img 
                                src="../../<?php echo htmlspecialchars($game['game_img']); ?>" 
                                alt="<?php echo htmlspecialchars($game['game_name']); ?> Cover" 
                                class="game-image"
                            >
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td><a href="<?php echo htmlspecialchars($game['game_trailerLink']); ?>" target="_blank">Watch Trailer</a></td>
                    
                    <td><a href="<?php echo htmlspecialchars($game['game_Link']); ?>" target="_blank">Go to Game</a></td>
                    <td>
                        <a href="../img/hub_admin_img.php?game_id=<?php echo htmlspecialchars($game['game_id']); ?>">🖼️ Gallery</a> |
                        <a href="hub_admin_games.php?id=<?php echo htmlspecialchars($game['game_id']); ?>">Edit</a> |
                        <a href="hub_admin_game_delete.php?id=<?php echo htmlspecialchars($game['game_id']); ?>">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    // --- NEW: Include modal files ---
    include 'hub_admin_game_add.php';
    if ($game_to_edit) {
        include 'hub_admin_game_edit.php';
    }
    ?>

    <script>
        // --- Updated Dark Mode Logic ---
        const darkModeIcon = document.getElementById('darkModeIcon');
        const localStorageKey = 'adminGamehubDarkMode'; // <-- Note the different key
        const htmlElement = document.documentElement;

        function applyDarkMode(isDark) {
            if (isDark) {
                htmlElement.classList.add('dark-mode');
                if (darkModeIcon) darkModeIcon.classList.replace('fa-moon', 'fa-sun');
            } else {
                htmlElement.classList.remove('dark-mode');
                if (darkModeIcon) darkModeIcon.classList.replace('fa-sun', 'fa-moon');
            }
        }

        function toggleDarkMode() {
            const isDark = htmlElement.classList.contains('dark-mode');
            applyDarkMode(!isDark);
            localStorage.setItem(localStorageKey, !isDark ? 'dark' : 'light');
        }

        (function loadIconState() {
            const isDark = htmlElement.classList.contains('dark-mode');
            applyDarkMode(isDark);
        })();

        // --- Modal JavaScript ---
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) modal.style.display = 'flex';
        }
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) modal.style.display = 'none';
            window.history.pushState({}, '', 'hub_admin_games.php');
        }
        <?php if (isset($_GET['action']) && $_GET['action'] == 'add'): ?>
            openModal('addGameModal');
        <?php elseif ($game_to_edit): ?>
            openModal('editGameModal');
        <?php endif; ?>

        // --- Search Bar Logic ---
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('gameSearchInput');
            const tableBody = document.getElementById('gameTableBody');
            const tableRows = tableBody.getElementsByTagName('tr');

            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const searchTerm = searchInput.value.toLowerCase();

                    for (let i = 0; i < tableRows.length; i++) {
                        const row = tableRows[i];
                        // Search in Name (index 2) and Description (index 3)
                        const gameName = row.cells[2] ? row.cells[2].textContent.toLowerCase() : '';
                        const gameDesc = row.cells[3] ? row.cells[3].textContent.toLowerCase() : '';
                        
                        if (gameName.includes(searchTerm) || gameDesc.includes(searchTerm)) {
                            row.style.display = ""; // Show row
                        } else {
                            row.style.display = "none"; // Hide row
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>