<div id="gameDetailModal" class="modal-overlay">
    <div class="modal-container" style="max-width: 1000px;"> 
        <button class="modal-close" onclick="closeGameModal()">&times;</button>
        
        <div class="game-detail-layout">
            
            <div class="image-slideshow">
                <div id="modalSlideshowContent">
                    <div class="slide active">
                        <img src="../../uploads/placeholder.png" alt="Loading...">
                    </div>
                </div>
                
                <button type="button" class="slider-control prev" onclick="modalChangeSlide(-1)">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button type="button" class="slider-control next" onclick="modalChangeSlide(1)">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <div class="slide-indicators" id="modalSlideIndicators">
                    </div>
            </div>

            <div class="game-info">
                <div class="game-title-header">
                    <h2 class="game-title" id="modalGameTitle">Loading...</h2>
                    <i class="far fa-heart favorite-icon" id="modalFavoriteIcon" data-game-id="0"></i>
                </div>
                
                <p class="game-desc" id="modalGameDesc">Loading game description...</p>
                
                <div class="star-rating" id="modalStarRating" data-game-id="0">
                    <i class="star far fa-star" data-value="1"></i>
                    <i class="star far fa-star" data-value="2"></i>
                    <i class="star far fa-star" data-value="3"></i>
                    <i class="star far fa-star" data-value="4"></i>
                    <i class="star far fa-star" data-value="5"></i>
                </div>

                <a href="#" class="trailer-link" id="modalTrailerLink" target="_blank">
                    <i class="fab fa-youtube"></i> Watch the Trailer (on YouTube)
                </a>
                
                <a href="#" class="trailer-link" id="modalGameLink" target="_blank" style="border-color: #2ecc71; color: #2ecc71;">
                    <i class="fas fa-play-circle"></i> Play The Game Now!
                </a>

                <a href="#" class="next-link" id="modalSurveyLink">
                    NEXT
                </a>
            </div>
        </div>
        
    </div>
</div>