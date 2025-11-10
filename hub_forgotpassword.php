<div id="forgotPasswordModal" class="modal-overlay">
    <div class="modal-container">
        <button class="modal-close" onclick="closeModal('forgotPasswordModal')">&times;</button>
        
        <h2>Forgot Password</h2>
        <p style="text-align: center; color: var(--main-text-color);">Enter your username to begin the password reset process.</p>
        
        <?php if (!empty($forgot_step1_error)): ?>
            <div class="error"><?php echo $forgot_step1_error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="forgot_step1">
            <div class="form-group">
                <label for="fp_username">Username:</label>
                <input type="text" id="fp_username" name="username" required>
            </div>
            
            <button type="submit" class="btn">Continue</button>
        </form>

        <div class="register-link">
            <p><a href="#" onclick="switchToModal('forgotPasswordModal', 'loginModal')">Back to Login</a></p>
        </div>
    </div>
</div>