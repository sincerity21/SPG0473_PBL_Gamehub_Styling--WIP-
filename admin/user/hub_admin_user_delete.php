<?php
session_start();
require '../../hub_conn.php';

// --- SECURITY CHECKS START ---

// 1. Authentication Check (Must be logged in)
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header('Location: ../hub_login.php');
    exit();
} 

// 2. Authorization Check (Must be an Admin to delete users)
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../hub_home_logged_in.php'); 
    exit();
}

// Get the ID of the currently logged-in admin
$logged_in_user_id = $_SESSION['user_id']; 

// --- SECURITY CHECKS END ---


if (isset($_GET['id'])) {
    // Sanitize and cast the ID from the URL to ensure it's an integer
    $id_to_delete = (int)$_GET['id'];
    
    // Check if the user ID is valid (optional: check if it exists before deleting)
    if ($id_to_delete <= 0) {
        header('Location: hub_admin_user.php?error=invalid_id');
        exit();
    }
    
    // --- 1. Execute Deletion ---
    // This calls the secured deleteByID function in ../hub_conn.php
    $deletion_successful = deleteByID($id_to_delete);
    
    if ($deletion_successful) {
        
        // --- 2. CRITICAL CHECK: Self-Deletion Logic ---
        // If the ID just deleted matches the ID of the user currently logged in
        if ($id_to_delete == $logged_in_user_id) { 
            
            // LOGOUT: If admin deleted their own account
            session_unset();
            session_destroy();
            
            // KICK THE USER OUT to the login page
            header('Location: ../hub_login.php?status=self_deleted'); 
            exit();
        }
        
        // --- 3. REDIRECT: If admin deleted someone else's account ---
        // Go back to the user list to show the change
        header('Location: hub_admin_user.php?status=deleted');
        exit();
    } else {
        // Deletion failed (e.g., database error)
        header('Location: hub_admin_user.php?error=deletion_failed');
        exit();
    }
}

// Fallback redirect if ID is missing or not processed
header('Location: hub_admin_user.php');
exit();
?>