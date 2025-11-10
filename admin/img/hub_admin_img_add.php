<div id="addGalleryModal" class="modal-overlay">
    <div class="modal-container">
        <button class="modal-close" onclick="closeModal('addGalleryModal')">&times;</button>
        
        <h2>Add Gallery Images</h2>
        
        <h3>For Game: <?php echo htmlspecialchars($current_game['game_name']); ?></h3>

        <form method="POST" action="hub_admin_img.php?game_id=<?php echo $game_id; ?>" enctype="multipart/form-data"> 
            <input type="hidden" name="action" value="add_gallery">
            <input type="hidden" name="game_id" value="<?php echo $game_id; ?>">

            <div class="form-group">
                <label for="gallery_images">Select Images (Hold Ctrl/Cmd to select multiple):</label>
                <input 
                    type="file" 
                    id="gallery_images" 
                    name="gallery_images[]" 
                    accept="image/*" 
                    multiple 
                    required
                >
            </div>
            
            <button type="submit" class="btn">Upload and Add to Gallery</button>
        </form>
    </div>
</div>