<div id="changeUsernameModal" class="modal-overlay">
    <div class="modal-container">
        <button class="modal-close" onclick="closeModal('changeUsernameModal')">&times;</button>
        
        <h2>Change Username</h2>
        
        <?php if (!empty($username_success)): ?>
            <div class="message success"><?php echo $username_success; ?></div>
        <?php endif; ?>
        <?php if (!empty($username_error)): ?>
            <div class="message error"><?php echo $username_error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="hub_main_profile.php">
            <input type="hidden" name="action" value="change_username">
            
            <div class="form-group">
                <label for="new_username">New Username:</label>
                <input type="text" id="new_username" name="new_username" required>
            </div>
            
            <div class="form-group">
                <label for="current_password_user">Enter Current Password to Confirm:</label>
                <input type="password" id="current_password_user" name="current_password" required>
            </div>
            
            <button type="submit" class="btn">Save Username</button>
        </form>
    </div>
</div>