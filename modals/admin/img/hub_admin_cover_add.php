<div id="addCoverModal" class="modal-overlay">
    <div class="modal-container">
        <button class="modal-close" onclick="closeModal('addCoverModal')">&times;</button>

        <h2>Add/Update Game Cover</h2>
        
        <h3>For Game: <?php echo htmlspecialchars($current_game['game_name']); ?></h3>
        <p><i>Uploading a new cover will replace the old one.</i></p>

        <form method="POST" action="hub_admin_img.php?game_id=<?php echo $game_id; ?>" enctype="multipart/form-data"> 
            <input type="hidden" name="action" value="add_cover">
            <input type="hidden" name="game_id" value="<?php echo $game_id; ?>">

            <div class="form-group">
                <label for="cover_image">Select Cover Image (Single file only):</label>
                <input 
                    type="file" 
                    id="cover_image" 
                    name="cover_image" 
                    accept="image/*" 
                    required
                >
            </div>
            
            <button type="submit" class="btn" style="background-color: #9b59b6;">Upload and Set as Cover</button>
        </form>
    </div>
</div>