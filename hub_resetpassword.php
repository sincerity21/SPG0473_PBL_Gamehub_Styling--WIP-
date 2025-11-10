<div id="resetPasswordModal" class="modal-overlay">
    <div class="modal-container">
        <button class="modal-close" onclick="closeModal('resetPasswordModal')">&times;</button>
        
        <h2>Set New Password</h2>
        
        <?php if (!empty($reset_success)): ?>
            <div class="success"><?php echo $reset_success; ?></div>
            <div class="register-link">
                <p><a href="#" onclick="switchToModal('resetPasswordModal', 'loginModal')">Click here to Login</a></p>
            </div>
        <?php else: ?>
            <p style="text-align: center; color: var(--main-text-color);">Please enter and confirm your new password.</p>

            <?php if (!empty($reset_error)): ?>
                <div class="error"><?php echo $reset_error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="reset_password">
                <div class="form-group">
                    <label for="new_password">New Password (min. 8 characters):</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn" style="background-color: #2ecc71;">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</div>