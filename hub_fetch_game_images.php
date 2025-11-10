<?php
session_start();
require 'hub_conn.php'; // hub_conn.php is in the same directory

header('Content-Type: application/json'); // Respond with JSON

// Check for authorized access
if (!isset($_SESSION['username'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Unauthorized access.']);
    exit();
}

if (isset($_GET['game_id'])) {
    $game_id = (int)$_GET['game_id'];

    if ($game_id > 0) {
        // Use the existing function that returns associative arrays
        $images = selectGameGalleryImages($game_id);
        
        // Return the full data structure, including img_path, etc.
        echo json_encode(['images' => $images]);
    } else {
        echo json_encode(['error' => 'Invalid game ID']);
    }
} else {
    echo json_encode(['error' => 'Game ID not provided']);
}
?>