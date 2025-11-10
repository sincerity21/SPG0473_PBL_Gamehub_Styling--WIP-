<?php
session_start();
require '../../hub_conn.php';

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../modals/hub_login.php');
    exit();
}

// Ensure the image ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: hub_admin_img.php');
    exit();
}

$image_id = (int)$_GET['id'];
$game_id = (int)$_GET['game_id']; // Get game_id from URL to redirect back

// Execute the database deletion and retrieve the image data
$deleted_data = deleteGameCover($image_id);

if ($deleted_data) {
    $file_path = $deleted_data['cover_path'];

    // --- CRITICAL STEP: Physical File Deletion ---
    $server_file_path = __DIR__ . '/../../' . $file_path; 

    if (file_exists($server_file_path)) {
        if (unlink($server_file_path)) {
            // File deleted successfully
        } else {
            error_log("Failed to delete physical cover file: " . $server_file_path);
        }
    } else {
        error_log("Physical cover file not found (but DB record deleted): " . $server_file_path);
    }
    
} else {
    error_log("Failed to delete cover image ID: " . $image_id);
}

// Redirect back to the image management page for that game
if ($game_id) {
    header('Location: hub_admin_img.php?game_id=' . $game_id);
} else {
    header('Location: hub_admin_img.php');
}
exit();
?>
