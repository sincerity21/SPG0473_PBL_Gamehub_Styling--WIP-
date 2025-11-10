<?php
session_start();
require 'hub_conn.php';

// Set content type to JSON
header('Content-Type: application/json');

// --- 1. Check for Authentication ---
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'User not logged in.']);
    exit();
}

// --- 2. Get and Validate IDs ---
$user_id = (int)$_SESSION['user_id'];
$game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;

if ($game_id <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Invalid game ID.']);
    exit();
}

// --- 3. Fetch All Data ---
$game_details = selectGameByID($game_id);

if (!$game_details) {
    http_response_code(404); // Not Found
    echo json_encode(['error' => 'Game not found.']);
    exit();
}

$gallery_images = selectGameGalleryImages($game_id);
$user_feedback = selectUserGameFeedback($user_id, $game_id);

// --- 4. Combine and Return Data ---
$data = [
    'details'  => $game_details,
    'gallery'  => $gallery_images,
    'feedback' => $user_feedback
];

echo json_encode($data);
exit();
?>