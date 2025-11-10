<?php
session_start();
require 'hub_conn.php';

header('Content-Type: application/json');

$game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;

if ($game_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid game ID.']);
    exit();
}

$game_details = selectGameByID($game_id);

if (!$game_details) {
    http_response_code(404);
    echo json_encode(['error' => 'Game not found.']);
    exit();
}

$gallery_images = selectGameGalleryImages($game_id);

$data = [
    'details'  => $game_details,
    'gallery'  => $gallery_images,
    'feedback' => ['game_rating' => 0, 'favorite_game' => 0]
];

echo json_encode($data);
exit();
?>