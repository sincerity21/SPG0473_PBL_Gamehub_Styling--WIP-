<?php

$host = '127.0.0.1';
$dbname = 'gamehub';
$username = 'root';
$password = '';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error){
    die("Connection failed: " . $conn->connect_error);
}

// To register a new user
function registerUser($username, $email, $password, $prompt, $answer){
    global $conn;
    
    // Hashed password and security answer
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $hashed_answer = password_hash($answer, PASSWORD_DEFAULT);
    
  
    $sql = "INSERT INTO users (user_username, user_email, user_password, sec_prompt, sec_answer) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);


    if ($stmt === false) {
        error_log("Prepare failed: " . $conn->error);
        return false;
    }

    // Bind 5 strings ("sssss") instead of 6
    $stmt->bind_param("sssss", $username, $email, $hashed_password, $prompt, $hashed_answer);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

// To login
function loginUser($username, $password){
    global $conn;
    $sql = "SELECT * FROM users WHERE user_username = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed in loginUser: " . $conn->error);
        return false;
    }
    $stmt->bind_param("s", $username); 
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    if ($user) {
        if (password_verify($password, $user['user_password'])) {
            return $user; 
        }
    }
    return false;
}

// To view users on admin page
function selectUserByID($id){
    global $conn;
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id); 
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

// To view users on admin page
function selectAllUsers(){
    global $conn;
    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// To edit users on admin page
function updateByID($id, $username, $email, $password){
    global $conn;
    // Ensures updating the password generates new hashed
    $hashed_password = $password; 
    $sql = "UPDATE users SET user_username = ?, user_email = ?, user_password = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed in updateByID: " . $conn->error);
        return false;
    }
    $stmt->bind_param("sssi", $username, $email, $hashed_password, $id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// To delete a user on admin page
function deleteByID($id){
    global $conn;
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id); 
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// To add a new game on admin page
function addNewGame($game_category, $game_name, $game_desc, $game_img, $game_trailerLink, $game_Link){
    global $conn;
    $sql = "INSERT INTO games (game_category, game_name, game_desc, game_img, game_trailerLink, game_Link) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $game_category, $game_name, $game_desc, $game_img, $game_trailerLink, $game_Link);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// To view games on admin page
function selectAllGames(){
    global $conn;
    $sql = "SELECT * FROM games";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Various: To select a game to edit (admin) / delete (admin) / view (main) / survey (main)
function selectGameByID($id){
    global $conn;
    $sql = "SELECT * FROM games WHERE game_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id); 
    $stmt->execute();
    $result = $stmt->get_result();
    $game = $result->fetch_assoc();
    $stmt->close();
    return $game;
}

// To edit a game on admin page
function updateGameByID($id, $name, $category, $desc, $img, $trailerLink, $game_Link){
    global $conn;
    $sql = "UPDATE games SET 
            game_name = ?, 
            game_category = ?, 
            game_desc = ?, 
            game_img = ?, 
            game_trailerLink = ?,
            game_Link = ? 
            WHERE game_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $name, $category, $desc, $img, $trailerLink, $game_Link, $id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// To delete a game on admin page
function deleteGameByID($id){
    global $conn;
    $sql = "DELETE FROM games WHERE game_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id); 
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// To get user data, first from inputting a username, then it finds that username and try to find that username's security question,
// then user inputs their security answer and it checks if it's similar to database, then to reset password if forgot (main)
function getUserResetData($conn, $username) {
    $sql = "SELECT user_id, sec_prompt, sec_answer 
             FROM users 
             WHERE user_username = ?";
    if ($conn === null) {
        error_log("MySQLi Connection object is null in getUserResetData.");
        return false;
    }
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed in getUserResetData: " . $conn->error);
        return false;
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    if ($user) {
        return [
            'user_id' => $user['user_id'],
            'security_question' => $user['sec_prompt'],
            'security_answer_hash' => $user['sec_answer'] 
        ];
    }

    return false;
}

// To then update (reset) user password after they've confirmed their security Q&A
function updateUserPassword($conn, $user_id, $hashed_password) {
    $sql = "UPDATE users SET user_password = ? WHERE user_id = ?";
    if ($conn === null) {
        error_log("MySQLi Connection object is null in updateUserPassword.");
        return false;
    }
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed in updateUserPassword: " . $conn->error);
        return false;
    }
    $stmt->bind_param("si", $hashed_password, $user_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// To add image for game galleries from local system into database, by converting filepath into a varchar 
function addGameGalleryImage($game_id, $image_path, $sort_order = 0){
    global $conn;
    $sql = "INSERT INTO game_images (game_id, img_path, img_order) 
            VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isi", $game_id, $image_path, $sort_order);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// To view game galleries, on admin and main page
function selectGameGalleryImages($game_id){
    global $conn;
    $sql = "SELECT * FROM game_images WHERE game_id = ? ORDER BY img_order ASC, game_img_id ASC";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed in selectGameGalleryImages: " . $conn->error);
        return [];
    }
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $images = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $images;
}

// To update game galleries' image order
function updateImageSortOrder($image_id, $change_amount) {
    global $conn;
    $sql = "UPDATE game_images SET img_order = img_order + ? WHERE game_img_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed in updateImageSortOrder: " . $conn->error);
        return false;
    }
    $stmt->bind_param("ii", $change_amount, $image_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// To delete game galleries' image
function deleteGalleryImageByID($image_id) {
    global $conn;
    $sql_select = "SELECT img_path, game_id FROM game_images WHERE game_img_id = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $image_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $image_data = $result->fetch_assoc();
    $stmt_select->close();
    if (!$image_data) {
        return false;
    }
    $sql_delete = "DELETE FROM game_images WHERE game_img_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $image_id);
    $success = $stmt_delete->execute();
    $stmt_delete->close();
    if ($success) {
        return $image_data;
    }
    return false;
}

// To fetch all game categories and view them in library (main)
function selectAllGameCategories(){
    global $conn;
    $sql = "SELECT DISTINCT game_category FROM games ORDER BY game_category ASC";
    $result = $conn->query($sql);
    $categories = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['game_category'];
        }
    }
    return $categories;
}

// To view game covers on admin page
function selectGameCovers($game_id){
    global $conn;
    $sql = "SELECT * FROM game_cover WHERE game_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed in selectGameCovers: " . $conn->error);
        return [];
    }
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $images = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $images;
}

// To add OR update game covers on admin page (game should only have ONE cover)
function addOrUpdateGameCover($game_id, $cover_path) {
    global $conn;
    // Check if cover for game exists
    $sql_check = "SELECT game_cover_id FROM game_cover WHERE game_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    if ($stmt_check === false) {
        error_log("Prepare failed (check) in addOrUpdateGameCover: " . $conn->error);
        return false;
    }
    $stmt_check->bind_param("i", $game_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $existing_cover = $result_check->fetch_assoc();
    $stmt_check->close();
    if ($existing_cover) {
        // Exists? Update (Replace)
        $sql = "UPDATE game_cover SET cover_path = ? WHERE game_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare failed (update) in addOrUpdateGameCover: " . $conn->error);
            return false;
        }
        $stmt->bind_param("si", $cover_path, $game_id);
    } else {
        // Doesn't? Add new
        $sql = "INSERT INTO game_cover (game_id, cover_path) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare failed (insert) in addOrUpdateGameCover: " . $conn->error);
            return false;
        }
        $stmt->bind_param("is", $game_id, $cover_path);
    }
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// To delete game cover
function deleteGameCover($cover_id) {
    global $conn;
    $sql_select = "SELECT cover_path, game_id FROM game_cover WHERE game_cover_id = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param("i", $cover_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $image_data = $result->fetch_assoc();
    $stmt_select->close();
    if (!$image_data) {
        return false; 
    }
    $sql_delete = "DELETE FROM game_cover WHERE game_cover_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $cover_id);
    $success = $stmt_delete->execute();
    $stmt_delete->close();
    if ($success) {
        return $image_data;
    }
    return false;
}

// To select all games with covers for library
function selectAllGamesWithCovers(){
    global $conn;
    $sql = "SELECT 
                g.game_id, 
                g.game_name, 
                g.game_category,
                gc.cover_path 
            FROM 
                games g
            LEFT JOIN 
                game_cover gc ON g.game_id = gc.game_id
            ORDER BY 
                g.game_name ASC";      
    $result = $conn->query($sql);
    if ($result === false) {
        error_log("Query failed in selectAllGamesWithCovers: " . $conn->error);
        return [];
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

// To fetch ratings and favourite status of a game by a user
function selectUserGameFeedback($user_id, $game_id){
    global $conn;
    $feedback = ['game_rating' => 0, 'favorite_game' => 0];
    $sql_rating = "SELECT rating_game FROM rating WHERE user_id = ? AND game_id = ?";
    $stmt_rating = $conn->prepare($sql_rating);
    $stmt_rating->bind_param("ii", $user_id, $game_id);
    $stmt_rating->execute();
    $result_rating = $stmt_rating->get_result();
    if ($row_rating = $result_rating->fetch_assoc()) {
        $feedback['game_rating'] = (int)$row_rating['rating_game'];
    }
    $stmt_rating->close();
    $sql_fav = "SELECT favourite_game FROM favourites WHERE user_id = ? AND game_id = ?";
    $stmt_fav = $conn->prepare($sql_fav);
    $stmt_fav->bind_param("ii", $user_id, $game_id);
    $stmt_fav->execute();
    $result_fav = $stmt_fav->get_result();
    if ($row_fav = $result_fav->fetch_assoc()) {
        $feedback['favorite_game'] = (int)$row_fav['favourite_game'];
    }
    $stmt_fav->close();
    return $feedback;
}

// To update ratings on a game by a user
function upsertGameRating($user_id, $game_id, $rating) {
    global $conn;
    $sql = "INSERT INTO rating (user_id, game_id, rating_game)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE rating_game = VALUES(rating_game)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed in upsertGameRating: " . $conn->error); 
        return false;
    }
    $stmt->bind_param("iii", $user_id, $game_id, $rating);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// To update favourite status on a game by a user
function upsertGameFavourite($user_id, $game_id, $favorite) {
    global $conn;
    $sql = "INSERT INTO favourites (user_id, game_id, favourite_game)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE favourite_game = VALUES(favourite_game)";
    $stmt = $conn->prepare($sql);
     if ($stmt === false) {
        error_log("Prepare failed in upsertGameFavourite: " . $conn->error); 
        return false;
    }
    $stmt->bind_param("iii", $user_id, $game_id, $favorite);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// To update the two above data into the database
function upsertGameFeedback($user_id, $game_id, $frequency, $open_feedback) {
    global $conn;
    $sql = "INSERT INTO feedback_game (user_id, game_id, feedback_game_frequency, feedback_game_open)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                feedback_game_frequency = VALUES(feedback_game_frequency), 
                feedback_game_open = VALUES(feedback_game_open)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed in upsertGameFeedback: " . $conn->error); 
        return false;
    }
    $stmt->bind_param("iiss", $user_id, $game_id, $frequency, $open_feedback);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// To fetch survey data (frequency, open-ended) on a game by a user, if it exists
function selectUserSurveyFeedback($user_id, $game_id){
    global $conn;
    $sql = "SELECT feedback_game_frequency, feedback_game_open 
            FROM feedback_game 
            WHERE user_id = ? AND game_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $game_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $feedback = $result->fetch_assoc();
    $stmt->close();
    return $feedback;
}

//  To update gamesurvey data above into the database
function upsertSiteFeedback($user_id, $satisfaction, $open_feedback) {
    global $conn;
    $sql = "INSERT INTO feedback_site (user_id, feedback_site_satisfaction, feedback_site_open)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                feedback_site_satisfaction = VALUES(feedback_site_satisfaction), 
                feedback_site_open = VALUES(feedback_site_open)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed in upsertSiteFeedback: " . $conn->error); 
        return false;
    }
    $stmt->bind_param("iss", $user_id, $satisfaction, $open_feedback);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}

// To fetch survey data (likert scale, open-ended) on site by a user, if it exists, and also update into the database
function selectUserSiteFeedback($user_id){
    global $conn;
    $sql = "SELECT feedback_site_satisfaction, feedback_site_open 
            FROM feedback_site 
            WHERE user_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $feedback = $result->fetch_assoc();
    $stmt->close();
    return $feedback;
}

// To fetch games by a user where, EITHER the game is rated OR favourite OR have answered survey on OR all three; if it achieves neither requirement, doesn't show up
// For profile page 
function selectUserInteractedGames($user_id){
    global $conn;
    $sql = "SELECT 
                g.game_id, 
                g.game_name, 
                g.game_category,
                g.game_Link,
                gc.cover_path,
                COALESCE(r.rating_game, 0) AS user_rating,
                COALESCE(f.favourite_game, 0) AS user_favourite,
                (fg.feedback_game_id IS NOT NULL) AS user_surveyed
            FROM 
                games g
            LEFT JOIN 
                game_cover gc ON g.game_id = gc.game_id
            LEFT JOIN 
                rating r ON g.game_id = r.game_id AND r.user_id = ?
            LEFT JOIN 
                favourites f ON g.game_id = f.game_id AND f.user_id = ?
            LEFT JOIN
                feedback_game fg ON g.game_id = fg.game_id AND fg.user_id = ?
            WHERE
                r.user_id IS NOT NULL 
                OR f.user_id IS NOT NULL 
                OR fg.user_id IS NOT NULL
            GROUP BY
                g.game_id
            ORDER BY
                g.game_name ASC";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed in selectUserInteractedGames: " . $conn->error);
        return [];
    }
    $stmt->bind_param("iii", $user_id, $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $games = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $games;
}

// To update USERNAME only on user side on profile page
function updateUsername($user_id, $new_username, $current_password) {
    global $conn;
    $sql_check = "SELECT user_id FROM users WHERE user_username = ? AND user_id != ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("si", $new_username, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        $stmt_check->close();
        return "Username already taken. Please choose another.";
    }
    $stmt_check->close();
    $sql_user = "SELECT user_password FROM users WHERE user_id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user = $result_user->fetch_assoc();
    $stmt_user->close();
    if ($user && password_verify($current_password, $user['user_password'])) {
        $sql_update = "UPDATE users SET user_username = ? WHERE user_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $new_username, $user_id);
        if ($stmt_update->execute()) {
            $_SESSION['username'] = $new_username; // Update session variable
            $stmt_update->close();
            return "success";
        } else {
            $stmt_update->close();
            return "Database error. Could not update username.";
        }
    } else {
        return "Incorrect current password.";
    }
}

// To update PASSWORD only on user side on profile page
function updateUserPasswordSecurely($user_id, $current_password, $new_password) {
    global $conn;
    $sql_user = "SELECT user_password FROM users WHERE user_id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user = $result_user->fetch_assoc();
    $stmt_user->close();
    if ($user && password_verify($current_password, $user['user_password'])) {
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql_update = "UPDATE users SET user_password = ? WHERE user_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $new_hashed_password, $user_id);
        if ($stmt_update->execute()) {
            $stmt_update->close();
            return "success";
        } else {
            $stmt_update->close();
            return "Database error. Could not update password.";
        }
    } else {
        return "Incorrect current password.";
    }
}


/**Get user's security question, used temporarily to update old accounts with non-hashed security answers (hub_hashsecanswer.php). 
function getSecurityAnswerForHashing($conn, $username) {
    $sql = "SELECT user_id, sec_answer FROM users WHERE user_username = ?";
    if ($conn === null) {
        return false;
    }
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed in getSecurityAnswerForHashing: " . $conn->error);
        return false;
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
} */

/**Updates security answer (either same one or new inputted one) into a hashed format (hub_hashsecanswer.php). 
function updateHashedSecurityAnswer($conn, $user_id, $hashed_answer) {
    $sql = "UPDATE users SET sec_answer = ? WHERE user_id = ?";
    if ($conn === null) {
        return false;
    }
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed in updateHashedSecurityAnswer: " . $conn->error);
        return false;
    }
    $stmt->bind_param("si", $hashed_answer, $user_id);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
} */

/**function selectRandomGalleryImages($limit = 9){
    global $conn;
    $sql = "
        SELECT gi.img_path
        FROM game_images gi
        INNER JOIN games g ON gi.game_id = g.game_id
        INNER JOIN (
            -- Subquery to force a random game_img_id for each category
            SELECT
                g3.game_category,
                (SELECT gi3.game_img_id
                 FROM game_images gi3
                 INNER JOIN games g4 ON gi3.game_id = g4.game_id
                 WHERE g4.game_category = g3.game_category
                 ORDER BY RAND()
                 LIMIT 1
                ) AS random_img_id
            FROM games g3
            GROUP BY g3.game_category
            ORDER BY RAND()
            LIMIT ?
        ) AS RandomCategories ON gi.game_img_id = RandomCategories.random_img_id
    ";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed in selectRandomGalleryImages: " . $conn->error);
        return [];
    }
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $images = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $paths = array_column($images, 'img_path');
    return $paths;
} */


/**function selectGamesByCategory($category_value){
    global $conn;
    $sql = "SELECT * FROM games WHERE game_category = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Prepare failed in selectGamesByCategory: " . $conn->error);
        return [];
    }
    $stmt->bind_param("s", $category_value);
    $stmt->execute();
    $result = $stmt->get_result();
    $games = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $games;
} */

?>
