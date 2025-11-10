<?php
/*
    This is the new hub_login.php file
    with the "sketch" theme and <img> tag.
*/
?>

<div id="loginModal" class="modal-overlay">

    <div class="modal-container">
        <div class="sketch-container">

            <button class="modal-close" onclick="closeModal('loginModal')">&times;</button>
            
            <h2>WELCOME TO GAMEHUB</h2>
            
            <?php if (!empty($login_error)): ?>
                <div class="error"><?php echo htmlspecialchars($login_error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($login_register_success)): ?>
                <div class="success"><?php echo htmlspecialchars($login_register_success); ?></div>
            <?php endif; ?>

            <div class="content-wrapper">
                <div class="icon-wrapper">
                    <img src="../main/icon.png" alt="User Icon" class="login-icon-img">
                </div>

                <form class="login-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="action" value="login">
                
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                    
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                    
                    <div class="form-links">
                        <a onclick="switchToModal('loginModal', 'forgotPasswordModal')">
                            Forgot your password?
                        </a>
                    </div>
                    
                    <button type="submit">LOGIN</button>
                </form>
            </div>
            
            <p class="sign-up">
                New Player? 
                <a onclick="switchToModal('loginModal', 'registerModal')">Sign in Here</a>
            </p>

        </div> </div> </div> 