<?php
session_start();
require '../../hub_conn.php';

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../hub_login.php');
    exit();
}

// Ensure both image ID and game ID are present
if (!isset($_GET['id']) || !isset($_GET['game_id']) || !is_numeric($_GET['id']) || !is_numeric($_GET['game_id'])) {
    header('Location: hub_admin_img.php');
    exit();
}

$image_id = (int)$_GET['id'];
$game_id = (int)$_GET['game_id'];
$change_amount = -1; // Decrease the sort order value

updateImageSortOrder($image_id, $change_amount);

// Redirect back to the gallery view for the specific game
header('Location: hub_admin_img.php?game_id=' . $game_id);
exit();
?>