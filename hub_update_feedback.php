<?php
session_start();
require 'hub_conn.php';

// Set content type to JSON
header('Content-Type: application/json');

// --- 1. Check for Authentication ---
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

// --- 2. Get Data from POST request ---
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
    exit();
}

// --- 3. Sanitize and Prepare Variables ---
$user_id = (int)$_SESSION['user_id'];
$game_id = isset($data['game_id']) ? (int)$data['game_id'] : 0;

// Initialize update values to null
$rating = null;
$favorite = null;

// Check which value is being updated
if (isset($data['rating'])) {
    $rating = (int)$data['rating'];
    if ($rating < 1 || $rating > 5) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid rating value.']);
        exit();
    }
} elseif (isset($data['favorite'])) {
    $favorite = (int)$data['favorite'];
    if ($favorite < 0 || $favorite > 1) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid favorite value.']);
        exit();
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No rating or favorite data provided.']);
    exit();
}

if ($game_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid game ID.']);
    exit();
}

// --- 4. Call the correct upsert function ---
$success = false;
if ($rating !== null) {
    // Call the new rating-specific function
    $success = upsertGameRating($user_id, $game_id, $rating);
} elseif ($favorite !== null) {
    // Call the new favorite-specific function
    $success = upsertGameFavourite($user_id, $game_id, $favorite);
}

// --- 5. Send JSON Response ---
if ($success) {
    echo json_encode(['success' => true, 'message' => 'Feedback updated successfully.']);
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Database update failed.']);
}
?>