<?php
session_start();
require '../../hub_conn.php';

// Check for user login
if (!isset($_SESSION['username'])) {
    header('Location: ../../modals/hub_login.php');
    exit();
}

$error = ''; // For displaying errors

// 1. Check if a specific game ID is requested
$game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : null;

// --- NEW: Handle All POST Actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $game_id_form = (int)$_POST['game_id']; // Get game_id from the form

    // --- HANDLE ADD GALLERY IMAGES ---
    if ($_POST['action'] === 'add_gallery' && isset($_FILES['gallery_images'])) {
        $upload_dir = 'uploads/gallery/'; 
        $server_upload_path = __DIR__ . '/../../' . $upload_dir; 
        if (!is_dir($server_upload_path)) {
            mkdir($server_upload_path, 0777, true);
        }

        $upload_count = 0;
        $failed_count = 0;

        foreach ($_FILES['gallery_images']['name'] as $key => $filename) {
            if ($_FILES['gallery_images']['error'][$key] !== UPLOAD_ERR_OK) {
                if ($_FILES['gallery_images']['error'][$key] !== UPLOAD_ERR_NO_FILE) {
                    $failed_count++;
                }
                continue;
            }

            $file_tmp_path = $_FILES['gallery_images']['tmp_name'][$key];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $new_file_name = 'game_' . $game_id_form . '_' . uniqid() . '.' . $ext;
            $dest_path = $server_upload_path . $new_file_name;
            $db_path = $upload_dir . $new_file_name; 

            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                if (addGameGalleryImage($game_id_form, $db_path, 0)) {
                    $upload_count++;
                } else {
                    $failed_count++;
                    unlink($dest_path); 
                }
            } else {
                $failed_count++;
            }
        }
        
        // Redirect back to the same game page
        header('Location: hub_admin_img.php?game_id=' . $game_id_form . '&status=gallery_updated');
        exit();
    }

    // --- HANDLE ADD/UPDATE COVER IMAGE ---
    if ($_POST['action'] === 'add_cover' && isset($_FILES['cover_image'])) {
        $old_cover_path = null;
        $covers = selectGameCovers($game_id_form);
        if (!empty($covers)) {
            $old_cover_path = $covers[0]['cover_path'];
        }

        $upload_dir = 'uploads/covers/';
        $server_upload_path = __DIR__ . '/../../' . $upload_dir; 
        if (!is_dir($server_upload_path)) {
            mkdir($server_upload_path, 0777, true);
        }

        if ($_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['cover_image']['tmp_name'];
            $filename = $_FILES['cover_image']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $new_file_name = 'cover_' . $game_id_form . '_' . uniqid() . '.' . $ext;
            $dest_path = $server_upload_path . $new_file_name;
            $db_path = $upload_dir . $new_file_name; 

            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                if (addOrUpdateGameCover($game_id_form, $db_path)) {
                    // Success, delete old file if it exists
                    if ($old_cover_path) {
                        $old_file_server_path = __DIR__ . '/../../' . $old_cover_path;
                        if (file_exists($old_file_server_path)) {
                            @unlink($old_file_server_path);
                        }
                    }
                    header('Location: hub_admin_img.php?game_id=' . $game_id_form . '&status=cover_updated');
                    exit();
                } else {
                    unlink($dest_path); 
                    $error = "Database insert/update failed for cover file.";
                }
            } else {
                $error = "Failed to move uploaded cover file.";
            }
        } else {
            $error = "Cover upload failed. Please check file or permissions.";
        }
    }
}


// --- Fetch data for GET request ---
$games = selectAllGames(); 
$gallery_images = [];
$cover_images = [];
$current_game = null;
$cover_button_text = "➕ Add New Cover";

if ($game_id) {
    $current_game = selectGameByID($game_id);
    
    if ($current_game) {
        $gallery_images = selectGameGalleryImages($game_id);
        if (function_exists('selectGameCovers')) {
            $cover_images = selectGameCovers($game_id);
            if (!empty($cover_images)) {
                $cover_button_text = "🔄 Replace Game Cover";
            }
        }
    } else {
        $game_id = null; // Invalid game_id, reset to list view
    }
}

$page_title = $current_game ? "Image Management for: " . htmlspecialchars($current_game['game_name']) : "Games Gallery (Select a Game)";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* All CSS from hub_admin_user.php (including modal styles) goes here */
        /* ... (omitted for brevity, but copy/paste all styles from hub_admin_user.php) ... */
        :root {
            --bg-color: #f4f7f6; --main-text-color: #333; --card-bg-color: white;
            --shadow-color: rgba(0, 0, 0, 0.1); --border-color: #ddd; --header-text-color: #2c3e50;
            --accent-color: #3498db; --accent-text-color: white; --hover-bg-color: #f5f5f5;
            --zebra-bg-color: #f9f9f9; --info-bg-color: #ecf0f1; --card-border-color: #ccc;
             --label-text-color: #555;
        }
        html.dark-mode body {
            --bg-color: #121212; --main-text-color: #f4f4f4; --card-bg-color: #1e1e1e;
            --shadow-color: rgba(0, 0, 0, 0.4); --border-color: #444; --header-text-color: #ecf0f1;
            --accent-color: #4dc2f9; --accent-text-color: #1e1e1e; --hover-bg-color: #2c2c2c;
            --zebra-bg-color: #222; --info-bg-color: #2c3e50; --card-border-color: #555;
            --label-text-color: #bbb;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: var(--bg-color); color: var(--main-text-color); transition: background-color 0.3s, color 0.3s; }
        .navbar { background-color: #2c3e50; overflow: hidden; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
        .navbar a { float: left; display: block; color: white; text-align: center; padding: 16px 20px; text-decoration: none; transition: background-color 0.3s; }
        .navbar a:hover { background-color: #34495e; }
        .navbar a.active { background-color: #1abc9c; }
        .content { padding: 30px; max-width: 1200px; margin: 0 auto; }
        h1 { color: var(--header-text-color); margin-bottom: 20px; border-bottom: 2px solid var(--accent-color); padding-bottom: 5px; }
        h2 { color: var(--header-text-color); margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; box-shadow: 0 4px 8px var(--shadow-color); background-color: var(--card-bg-color); margin-top: 20px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border-color); vertical-align: middle; } 
        th { background-color: var(--accent-color); color: var(--accent-text-color); font-weight: 600; text-transform: uppercase; }
        tr:hover { background-color: var(--hover-bg-color); }
        tr:nth-child(even) { background-color: var(--zebra-bg-color); }
        td a { color: #2980b9; text-decoration: none; margin-right: 5px; }
        td a:hover { text-decoration: underline; }
        .add-link { display: inline-block; padding: 10px 15px; margin-bottom: 20px; background-color: #2ecc71; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; transition: background-color 0.3s; }
        .add-link:hover { background-color: #27ae60; }
        .game-image { max-width: 80px; height: auto; display: block; }
        .gallery-image-container { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px; }
        .gallery-image-card { border: 1px solid var(--card-border-color); padding: 10px; background-color: var(--card-bg-color); box-shadow: 0 2px 5px var(--shadow-color); width: calc(33.333% - 20px); box-sizing: border-box; }
        .gallery-image-card img { max-width: 100%; height: auto; display: block; margin-bottom: 10px; }
        .gallery-image-card p { margin: 5px 0; font-size: 0.9em; }
        .current-game-info { background-color: var(--info-bg-color); padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .section-divider { margin-top: 30px; margin-bottom: 30px; border: 0; border-top: 2px solid var(--border-color); }
        .dark-mode-switch { float: right; padding: 16px 20px; cursor: pointer; color: white; font-size: 1.1em; transition: color 0.3s; }
        .dark-mode-switch:hover { color: #1abc9c; }
        
        /* Modal Styles */
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
        .modal-container input[type="file"] {
            width: 100%; padding: 10px; border: 1px solid var(--border-color);
            border-radius: 4px; box-sizing: border-box; font-size: 16px;
            background-color: var(--bg-color); color: var(--main-text-color);
        }
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
        <a href="../games/hub_admin_games.php" class="active">Manage Games</a>
        <a href="../../hub_logout.php">Logout</a>

        <div class="dark-mode-switch" onclick="toggleDarkMode()">
            <i class="fas fa-moon" id="darkModeIcon"></i>
        </div>
    </div>

    <div class="content">
        <h1><?php echo $page_title; ?></h1>
        
        <?php if ($error): ?>
            <div class="modal-container error" style="display:block; max-width: 1140px; box-sizing: border-box;"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($game_id && $current_game): ?>
        
            <div class="current-game-info">
                Managing Images for: <strong><?php echo htmlspecialchars($current_game['game_name']); ?></strong> (ID: <?php echo $game_id; ?>) | 
                <a href="../games/hub_admin_games.php">← Back to Game Selection</a>
            </div>
            
            <h2>Game Cover</h2>
            
            <a href="hub_admin_img.php?game_id=<?php echo $game_id; ?>&action=add_cover" class="add-link" style="background-color: #9b59b6;"><?php echo $cover_button_text; ?></a>

            <?php if (function_exists('selectGameCovers')): ?>
                <?php if (!empty($cover_images)): ?>
                    <div class="gallery-image-container">
                    <?php foreach ($cover_images as $image): ?>
                        <div class="gallery-image-card">
                            <img src="../../<?php echo htmlspecialchars($image['cover_path']); ?>" alt="Game Cover ID <?php echo $image['game_cover_id']; ?>">
                            <p>
                                <a href="hub_admin_cover_delete.php?id=<?php echo $image['game_cover_id']; ?>&game_id=<?php echo $game_id; ?>" onclick="return confirm('Are you sure you want to delete this cover image?');">Delete</a>
                            </p>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No cover images found for this game. Click 'Add New Cover' to upload one.</p>
                <?php endif; ?>
            <?php else: ?>
                <p style="color: red; font-weight: bold; background-color: #fdd; padding: 10px; border-radius: 4px;">
                    Error: The function 'selectGameCovers' is not defined in hub_conn.php. Please add it to manage covers.
                </p>
            <?php endif; ?>
            
            <hr class="section-divider">
            <h2>Gallery Images</h2>
            
            <a href="hub_admin_img.php?game_id=<?php echo $game_id; ?>&action=add_gallery" class="add-link">➕ Add New Pictures to Gallery</a>

            <?php if (!empty($gallery_images)): ?>
                <div class="gallery-image-container">
                <?php foreach ($gallery_images as $image): ?>
                    <div class="gallery-image-card">
                        <img src="../../<?php echo htmlspecialchars($image['img_path']); ?>" alt="Gallery Image ID <?php echo $image['game_img_id']; ?>">
                        <p>
                        <strong>Order:</strong> <?php echo htmlspecialchars($image['img_order']); ?>
                            <a href="hub_admin_img_sortplus.php?id=<?php echo $image['game_img_id']; ?>&game_id=<?php echo $game_id; ?>" title="Increase Order" style="text-decoration: none; font-weight: bold; margin-left: 10px;">➕</a>
                            <a href="hub_admin_img_sortminus.php?id=<?php echo $image['game_img_id']; ?>&game_id=<?php echo $game_id; ?>" title="Decrease Order" style="text-decoration: none; font-weight: bold; margin-left: 5px;">➖</a>
                        </p>
                        <p>
                            <a href="hub_admin_img_delete.php?id=<?php echo $image['game_img_id']; ?>&game_id=<?php echo $game_id; ?>" onclick="return confirm('Are you sure you want to delete this gallery image?');">Delete</a>
                        </p>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No gallery images found for this game. Click 'Add New Pictures' to upload some!</p>
            <?php endif; ?>
            
        <?php else: ?>
    
            <h2>Select a Game to Manage its Images</h2>
            
            <?php if (!empty($games)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Main Image</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($games as $game): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($game['game_id']); ?></td>
                            <td><?php echo htmlspecialchars($game['game_name']); ?></td>
                            <td>
                                <?php if ($game['game_img']): ?>
                                    <img src="../../<?php echo htmlspecialchars($game['game_img']); ?>" class="game-image">
                                <?php else: ?>
                                    No Cover
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="hub_admin_img.php?game_id=<?php echo htmlspecialchars($game['game_id']); ?>">Manage Images (Cover & Gallery)</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No games found in the database. Please add a game first via the "Manage Games" tab.</p>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <?php
    // --- NEW: Include modal files ---
    // Only include if we are in a state that needs them
    if ($game_id) {
        include '../../modals/admin/img/hub_admin_img_add.php';
        include '../../modals/admin/img/hub_admin_cover_add.php';
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

        // --- NEW: Modal JavaScript ---
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) modal.style.display = 'flex';
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) modal.style.display = 'none';
            // Update URL to remove action param
            window.history.pushState({}, '', 'hub_admin_img.php?game_id=<?php echo $game_id; ?>');
        }
        
        // Auto-open modal based on URL params
        <?php if (isset($_GET['action'])): ?>
            <?php if ($_GET['action'] == 'add_gallery'): ?>
                openModal('addGalleryModal');
            <?php elseif ($_GET['action'] == 'add_cover'): ?>
                openModal('addCoverModal');
            <?php endif; ?>
        <?php endif; ?>
    </script>
</body>
</html>