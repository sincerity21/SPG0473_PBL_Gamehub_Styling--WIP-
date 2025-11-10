<div id="forgotPasswordModal2" class="modal-overlay">
    <div class="modal-container">
        <button class="modal-close" onclick="closeModal('forgotPasswordModal2')">&times;</button>
        
        <h2>FORGOT YOUR PASSWORD?</h2>
        
        <div class="greeting" style="color: var(--main-text-color);"><?php echo $greeting_text ?? 'Please answer your security question.'; ?></div>
        
        <?php if (!empty($forgot_step2_error)): ?>
            <div class="error"><?php echo $forgot_step2_error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="forgot_step2">
            
            <div class="form-group">
                <label for="security_question_display" class="prompt">Security Prompt (Question):</label>
                <input type="text" id="security_question_display" value="<?php echo htmlspecialchars($resolved_question_text ?? 'Error: No question loaded.'); ?>" readonly>
            </div>
            
            <div class="form-group">
                <label for="security_answer">Security Answer:</label>
                <input type="text" id="security_answer" name="security_answer" placeholder="Enter your answer" required>
            </div>
            
            <button type="submit" class="btn" style="background-color: #2ecc71;">VERIFY ANSWER</button>
        </form>
        
         <div class="register-link">
            <p><a href="#" onclick="switchToModal('forgotPasswordModal2', 'loginModal')">Cancel and go back</a></p>
        </div>
    </div>
</div>