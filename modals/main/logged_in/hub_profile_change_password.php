<div id="changePasswordModal" class="modal-overlay">
    <div class="modal-container">
        <button class="modal-close" onclick="closeModal('changePasswordModal')">&times;</button>
        
        <h2>Change Password</h2>
        
        <?php if (!empty($password_success)): ?>
            <div class="message success"><?php echo $password_success; ?></div>
        <?php endif; ?>
        <?php if (!empty($password_error)): ?>
            <div class="message error"><?php echo $password_error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="hub_profile.php">
            <input type="hidden" name="action" value="change_password">
            
            <div class="form-group">
                <label for="current_password">Current Password:</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            
            <div class="form-group">
                <label for="new_password">New Password (min. 8 characters):</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>

            <div class="form-group">
                <label for="confirm_new_password">Confirm New Password:</label>
                <input type="password" id="confirm_new_password" name="confirm_new_password" required>
            </div>
            
            <button type="submit" class="btn">Save Password</button>
        </form>
    </div>
</div>