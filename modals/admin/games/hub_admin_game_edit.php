<div id="editGameModal" class="modal-overlay">
    <div class="modal-container">
        <button class="modal-close" onclick="closeModal('editGameModal')">&times;</button>
        
        <h2>Edit Game: <?php echo htmlspecialchars($game_to_edit['game_name']); ?></h2>
        
        <form method="POST" action="hub_admin_games.php" enctype="multipart/form-data"> 
            <input type="hidden" name="action" value="edit_game">
            <input type="hidden" name="game_id" value="<?php echo htmlspecialchars($game_to_edit['game_id']); ?>">

            <div class="form-group">
                <label for="game_category">Category:</label>
                <select name="game_category" id="game_category" required>
                    <option value="fps" <?php echo ($game_to_edit['game_category'] == 'fps') ? 'selected' : ''; ?>>First-Person Shooter</option>
                    <option value="rpg" <?php echo ($game_to_edit['game_category'] == 'rpg') ? 'selected' : ''; ?>>Role-Playing Games</option>
                    <option value="moba" <?php echo ($game_to_edit['game_category'] == 'moba') ? 'selected' : ''; ?>>Multiplayer Online Battle Arena (MOBA)</option>
                    <option value="puzzle" <?php echo ($game_to_edit['game_category'] == 'puzzle') ? 'selected' : ''; ?>>Puzzle</option>
                    <option value="sport" <?php echo ($game_to_edit['game_category'] == 'sport') ? 'selected' : ''; ?>>Sports</option>
                    <option value="sim" <?php echo ($game_to_edit['game_category'] == 'sim') ? 'selected' : ''; ?>>Simulator</option>
                    <option value="survival" <?php echo ($game_to_edit['game_category'] == 'survival') ? 'selected' : ''; ?>>Survival</option>
                    <option value="fight" <?php echo ($game_to_edit['game_category'] == 'fight') ? 'selected' : ''; ?>>Fighting</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="game_name">Name:</label>
                <input type="text" id="game_name" name="game_name" value="<?php echo htmlspecialchars($game_to_edit['game_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="game_desc">Description:</label>
                <textarea id="game_desc" name="game_desc" rows="5" required><?php echo htmlspecialchars($game_to_edit['game_desc']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Current Image:</label>
                <?php if (!empty($game_to_edit['game_img'])): ?>
                    <img src="../../<?php echo htmlspecialchars($game_to_edit['game_img']); ?>" class="current-img" alt="Current Game Image">
                    <p><small>File: <?php echo htmlspecialchars($game_to_edit['game_img']); ?></small></p>
                <?php else: ?>
                    <p>No current image.</p>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="game_img">Browse/Upload New Image (Leave blank to keep current):</label>
                <input type="file" id="game_img" name="game_img" accept="image/*">
            </div>
            
            <div class="form-group">
                <label for="game_trailerLink">Trailer Link:</label>
                <input type="text" id="game_trailerLink" name="game_trailerLink" value="<?php echo htmlspecialchars($game_to_edit['game_trailerLink']); ?>" required>
            </div>

            <div class="form-group">
                <label for="game_Link">Game Link (URL):</label>
                <input type="text" id="game_Link" name="game_Link" value="<?php echo htmlspecialchars($game_to_edit['game_Link']); ?>" required>
            </div>
            <input type="submit" value="Update Game" class="btn">
        </form>
    </div>
</div>