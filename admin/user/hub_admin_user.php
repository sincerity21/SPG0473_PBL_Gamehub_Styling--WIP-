<?php
session_start();
require '../../hub_conn.php';

// --- 1. Authentication Check (Must be logged in) ---
if (!isset($_SESSION['username'])) {
    header('Location: ../../modals/hub_login.php');
    exit();
}

// --- 2. Authorization Check (Must be an Admin) ---
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: ../../main/hub_home_logged_in.php'); 
    exit();
}

// Only admins can reach this point
$username = $_SESSION['username'];
$email = $_SESSION['email'];
$error = '';
$user_to_edit = null;

// --- 3. NEW: Handle POST logic for editing a user ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_user') {
    $id = (int)$_POST['user_id'];
    $username_form = $_POST['username'];
    $email_form = $_POST['email'];
    $new_password = $_POST['password'] ?? '';
    
    // Fetch the user's current data to get the old password hash
    $current_user_data = selectUserByID($id);
    
    if ($current_user_data) {
        // 1. Determine which password hash to use
        if (!empty($new_password)) {
            // HASH the new password if provided
            $final_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        } else {
            // Keep the existing hash if the field was left blank
            $final_password_hash = $current_user_data['user_password'];
        }

        // 2. Update the data using the determined hash
        $result = updateByID($id, $username_form, $email_form, $final_password_hash);  

        if ($result) {
            // (3) Go back to hub_admin_user.php on success
            header('Location: hub_admin_user.php?status=updated');     
            exit();
        } else {
            $error = "Error updating user data in the database.";
        }
    } else {
        $error = "User not found for update.";
    }
}

// --- 4. NEW: Check for GET request to edit a user ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_to_edit = selectUserByID((int)$_GET['id']);
}

// Get all users for the table
$users = selectAllUsers(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - User Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
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
        .content { padding: 30px; max-width: 1000px; margin: 0 auto; }
        h1 {
            color: var(--header-text-color); margin-bottom: 20px;
            border-bottom: 2px solid var(--accent-color); padding-bottom: 5px;
        }
        table {
            width: 100%; border-collapse: collapse; box-shadow: 0 4px 8px var(--shadow-color);
            background-color: var(--card-bg-color); margin-top: 20px;
        }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border-color); }
        th {
            background-color: var(--accent-color); color: var(--accent-text-color);
            font-weight: 600; text-transform: uppercase;
        }
        tr:hover { background-color: var(--hover-bg-color); }
        tr:nth-child(even) { background-color: var(--zebra-bg-color); }
        td a { color: #2980b9; text-decoration: none; margin-right: 5px; }
        td a:hover { text-decoration: underline; }
        .navbar a.active { background-color: #1abc9c; }
        .dark-mode-switch {
            float: right; padding: 16px 20px; cursor: pointer;
            color: white; font-size: 1.1em; transition: color 0.3s;
        }
        .dark-mode-switch:hover { color: #1abc9c; }

        /* --- Modal Styles --- */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.7); z-index: 2000; display: none;
            align-items: center; justify-content: center; overflow-y: auto;
        }
        .modal-container {
            background-color: var(--card-bg-color); padding: 30px; border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3); position: relative;
            width: 100%; max-width: 500px; color: var(--main-text-color); margin: 20px;
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
        .modal-container select,
        .modal-container textarea {
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
        <a href="hub_admin_user.php" class="active">Admin Home</a>
        <a href="../games/hub_admin_games.php">Manage Games</a>
        <a href="../../hub_logout.php">Logout</a>

        <div class="dark-mode-switch" onclick="toggleDarkMode()">
            <i class="fas fa-moon" id="darkModeIcon"></i>
        </div>
    </div>

    <div class="content">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>

        <h2>User Listing (Administrative View)</h2>
        
        <?php if ($error): ?>
            <div class="modal-container error" style="display:block; max-width: 940px; box-sizing: border-box;"><?php echo $error; ?></div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Admin Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($user['user_username']); ?></td>
                    <td><?php echo htmlspecialchars($user['user_email']); ?></td>
                    <td><?php echo htmlspecialchars($user['is_admin']); ?></td>
                    <td>
                        <a href="hub_admin_user.php?id=<?php echo htmlspecialchars($user['user_id']); ?>">Edit</a> |
                        <a href="hub_admin_user_delete.php?id=<?php echo htmlspecialchars($user['user_id']); ?>">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php
    // --- NEW: Include the modal file ---
    // We only include it if we are in an edit state
    if ($user_to_edit) {
        include '../../modals/admin/user/hub_admin_user_edit.php';
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
            // Also, update URL to remove the 'id' parameter to prevent re-opening on refresh
            window.history.pushState({}, '', 'hub_admin_user.php');
        }
        
        // Auto-open modal if PHP has set the user_to_edit
        <?php if ($user_to_edit): ?>
            openModal('editUserModal');
        <?php endif; ?>
    </script>
</body>
</html>