<div id="addGameModal" class="modal-overlay">
    <div class="modal-container">
        <button class="modal-close" onclick="closeModal('addGameModal')">&times;</button>

        <h2>Add New Game</h2>
        
        <form method="POST" action="hub_admin_games.php" enctype="multipart/form-data"> 
            <input type="hidden" name="action" value="add_game">
            
            <div class="form-group">
                <label for="game_category">Category:</label>
                <select name="game_category" id="game_category" required>
                    <option value="fps">First-Person Shooter</option>
                    <option value="rpg">Role-Playing Games</option>
                    <option value="moba">Multiplayer Online Battle Area (MOBA)</option>
                    <option value="puzzle">Puzzle</option>
                    <option value="sport">Sports</option>
                    <option value="racing">Racing</option>
                    <option value="sim">Simulator</option>
                    <option value="survival">Survival</option>
                    <option value="fight">Fighting</option>
                </select>
            </div>

            <div class="form-group">
                <label for="game_name">Name:</label>
                <input type="text" id="game_name" name="game_name" required>
            </div>
            
            <div class="form-group">
                <label for="game_desc">Description:</label>
                <textarea id="game_desc" name="game_desc" rows="5" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="game_img">Game Image (File):</label>
                <input type="file" id="game_img" name="game_img" accept="image/*" required>
            </div>

            <div class="form-group">
                <label for="game_trailerLink">Trailer (Link):</label>
                <input type="text" id="game_trailerLink" name="game_trailerLink" required>
            </div>

            <div class="form-group">
                <label for="game_Link">Game Link (URL):</label>
                <input type="text" id="game_Link" name="game_Link" placeholder="e.g., https://store.steampowered.com/..." required>
            </div>
            <button type="submit" class="btn">Add Game</button>
        </form>
    </div>
</div>