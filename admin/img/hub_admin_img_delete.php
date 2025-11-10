<?php
session_start();
require '../../hub_conn.php';

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../hub_login.php');
    exit();
}

// Ensure the image ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // If no ID is provided, just redirect back to the main gallery list
    header('Location: hub_admin_img.php');
    exit();
}

$image_id = (int)$_GET['id'];
$game_id = null; // We will try to get this from the deleteGalleryImageByID function

// Execute the database deletion and retrieve the image data (path and game_id)
$deleted_data = deleteGalleryImageByID($image_id);

if ($deleted_data) {
    $file_path = $deleted_data['img_path'];
    $game_id = $deleted_data['game_id'];

    // --- CRITICAL STEP: Physical File Deletion ---
    
    // 1. Define the full server path to the file.
    // UPDATED: Path corrected from /../ to /../../ to point to project root
    $server_file_path = __DIR__ . '/../../' . $file_path; 

    // 2. Check if the file exists on the server and attempt to delete it
    if (file_exists($server_file_path)) {
        if (unlink($server_file_path)) {
            // File deleted successfully
        } else {
            error_log("Failed to delete physical file: " . $server_file_path);
        }
    } else {
        error_log("Physical file not found (but DB record deleted): " . $server_file_path);
    }
    
} else {
    // Optional: Log an error if deletion failed (e.g., ID not found)
    error_log("Failed to delete gallery image ID: " . $image_id);
}

// Determine the redirect location
$redirect_url = ($game_id) ? 'hub_admin_img.php?game_id=' . $game_id : 'hub_admin_img.php';

// Redirect back to the gallery view (or the main list if game_id wasn't available)
header('Location: ' . $redirect_url);
exit();
?>
