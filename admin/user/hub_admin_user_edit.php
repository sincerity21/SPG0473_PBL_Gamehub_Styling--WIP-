<div id="editUserModal" class="modal-overlay">
    <div class="modal-container">
        <button class="modal-close" onclick="closeModal('editUserModal')">&times;</button>

        <h2>Edit User: <?php echo htmlspecialchars($user_to_edit['user_username']); ?></h2>
        
        <form method="POST" action="hub_admin_user.php">
            <input type="hidden" name="action" value="edit_user">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_to_edit['user_id']); ?>">
            
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_to_edit['user_username']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_to_edit['user_email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password (Leave blank to keep current password):</label>
                <input type="password" id="password" name="password" placeholder="Enter new password to change" value=""> 
            </div>
            
            <input type="submit" value="Update User" class="btn">
        </form>
    </div>
</div>