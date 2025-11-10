<div id="loginModal" class="modal-overlay">
    <div class="modal-container">
        <button class="modal-close" onclick="closeModal('loginModal')">&times;</button>
        
        <h2>Login to Game Hub</h2>
        
        <?php if (!empty($login_register_success)): ?>
            <div class="success"><?php echo $login_register_success; ?></div>
        <?php endif; ?>
        <?php if (!empty($login_error)): ?>
            <div class="error"><?php echo $login_error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="login">
            
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="forgot-link">
                <a href="#" onclick="switchToModal('loginModal', 'forgotPasswordModal')">Forget password?</a>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="register-link">
            <p>Don't have an account? <a href="#" onclick="switchToModal('loginModal', 'registerModal')">Register here</a></p>
        </div>
    </div>
</div>