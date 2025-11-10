<?php

require 'hub_conn.php';

if($_POST){
    $username = $_POST['username'];
    $imageName = uniqid() . '_' . $_FILES['image']['name'];

    move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $imageName);
    echo 'Image uploaded successfully';
}

?>

<html>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="username" id="username" placeholder="Enter Username">
        <input type="file" name="image" id="image">
        <input type="submit" value="Upload">
    </form> 
</html>