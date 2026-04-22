<?php
// Include Controller
require_once '../../controllers/DonorController.php';

// Init Controller
$controller = new DonorController();
$data = $controller->createDonation();

// Page Configuration
$pageTitle = 'Donate Food - FoodShare';
$pageHeading = 'Donate Food';
$userType = 'donor';
$userName = $_SESSION['user_name'] ?? 'Donor';

// Start output buffering
ob_start();
?>

<style>
    .donation-container {
        display: flex;
        gap: 2rem;
        align-items: flex-start;
    }
    
    .donation-form-card {
        flex: 1.5;
        background: var(--bg-secondary);
        border-radius: var(--radius-lg);
        padding: 2rem;
        border: 1px solid rgba(255,255,255,0.05);
    }
    
    .preview-card-container {
        flex: 1;
        position: sticky;
        top: 100px;
    }
    
    .preview-card {
        background: var(--surface);
        border-radius: var(--radius-lg);
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: var(--shadow-lg);
        transition: all 0.3s ease;
    }
    
    .preview-image-container {
        height: 250px;
        background: #2d3748;
        position: relative;
        overflow: hidden;
    }
    
    .preview-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .preview-content {
        padding: 1.5rem;
    }
    
    .preview-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        background: rgba(0,0,0,0.6);
        backdrop-filter: blur(4px);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .image-controls {
        position: absolute;
        bottom: 1rem;
        right: 1rem;
        display: flex;
        gap: 0.5rem;
    }
    
    .control-btn {
        background: rgba(255,255,255,0.9);
        color: #333;
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    
    .control-btn:hover {
        transform: scale(1.1);
        background: white;
    }
    
    /* Visual Selectors */
    .visual-selector {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .selector-item {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: var(--radius-md);
        padding: 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .selector-item:hover {
        background: rgba(255,255,255,0.05);
        transform: translateY(-2px);
    }
    
    .selector-item.active {
        background: rgba(102, 126, 234, 0.15);
        border-color: #667eea;
        color: #667eea;
    }
    
    .selector-icon {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    @media (max-width: 992px) {
        .donation-container {
            flex-direction: column-reverse;
        }
        .preview-card-container {
            position: static;
            width: 100%;
        }
    }
    /* Thumbnails Selection */
    .thumbnails-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 0.5rem;
        margin-top: 1rem;
    }
    
    .thumbnail-item {
        aspect-ratio: 1;
        border-radius: var(--radius-sm);
        overflow: hidden;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.2s;
        opacity: 0.6;
    }
    
    .thumbnail-item:hover {
        opacity: 1;
        transform: translateY(-2px);
    }
    
    .thumbnail-item.active {
        border-color: var(--primary);
        opacity: 1;
        box-shadow: 0 0 10px rgba(102, 126, 234, 0.4);
    }
    
    .thumbnail-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Autocomplete Suggestions */
    .suggestions-container {
        position: absolute;
        width: 100%;
        background: var(--surface);
        border: 1px solid rgba(255,255,255,0.1);
        border-top: none;
        border-radius: 0 0 var(--radius-md) var(--radius-md);
        z-index: 100;
        box-shadow: var(--shadow-lg);
        max-height: 200px;
        overflow-y: auto;
        display: none;
    }
    
    .suggestion-item {
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: all 0.2s;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    
    .suggestion-item:last-child {
        border-bottom: none;
    }
    
    .suggestion-item:hover {
        background: rgba(255,255,255,0.05);
        color: var(--primary);
    }

    /* Loading Spinner Overlay */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: inherit;
    }
    
    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid rgba(255,255,255,0.1);
        border-left-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .image-placeholder {
        background: linear-gradient(45deg, #1a202c, #2d3748);
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--text-muted);
    }
</style>

<div class="donation-container">
    <!-- Form Section -->
    <div class="donation-form-card animate-fade-in">
        <h2 class="mb-lg" style="font-family: var(--font-heading); display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-hand-holding-heart" style="color: var(--primary);"></i> Share Your Food
        </h2>
        
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" id="donationForm">
            
            <!-- Food Name -->
            <div class="form-group mb-lg" style="position: relative;">
                <label class="form-label">What are you donating?</label>
                <div style="position: relative;">
                    <input type="text" name="food_name" id="food_name" class="form-control" 
                           value="<?php echo $data['food_name']; ?>" 
                           placeholder="e.g. Fresh Vegetable Biryani" required autocomplete="off">
                    <div id="suggestions" class="suggestions-container"></div>
                </div>
                <span class="invalid-feedback"><?php echo $data['food_name_err']; ?></span>
                <div class="flex-between mt-xs">
                    <small class="text-muted">We'll automatically find matching images!</small>
                    <label class="text-primary" style="font-size: 0.8rem; cursor: pointer; display: flex; align-items: center; gap: 0.3rem;" onclick="triggerAIUpload()">
                        <i class="fas fa-camera"></i> Scan Photo
                    </label>
                    <input type="file" id="ai_upload" style="display: none;" onchange="handleAIUpload(this)">
                </div>
            </div>
            
            <!-- Category Selector -->
            <div class="form-group mb-lg">
                <label class="form-label">Food Category</label>
                <div class="visual-selector">
                    <div class="selector-item active" onclick="selectCategory('cooked')">
                        <i class="fas fa-utensils selector-icon"></i>
                        <span>Cooked</span>
                    </div>
                    <div class="selector-item" onclick="selectCategory('raw')">
                        <i class="fas fa-carrot selector-icon"></i>
                        <span>Raw / Veg</span>
                    </div>
                    <div class="selector-item" onclick="selectCategory('packaged')">
                        <i class="fas fa-box-open selector-icon"></i>
                        <span>Packaged</span>
                    </div>
                    <div class="selector-item" onclick="selectCategory('bakery')">
                        <i class="fas fa-bread-slice selector-icon"></i>
                        <span>Bakery</span>
                    </div>
                </div>
                <input type="hidden" name="food_type" id="food_type" value="cooked">
                <input type="hidden" name="food_category" id="food_category" value="vegetarian">
            </div>
            
            <!-- Quantity & Unit -->
            <div class="grid grid-2 gap-md mb-lg">
                <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" 
                           value="<?php echo $data['quantity']; ?>" placeholder="e.g. 50" required>
                    <span class="invalid-feedback"><?php echo $data['quantity_err']; ?></span>
                </div>
                <div class="form-group">
                    <label class="form-label">Unit</label>
                    <select name="unit" id="unit" class="form-control">
                        <option value="plates">Plates</option>
                        <option value="kg">Kg</option>
                        <option value="liters">Liters</option>
                        <option value="boxes">Boxes</option>
                        <option value="units">Units</option>
                    </select>
                </div>
            </div>
            
            <!-- People Served & Expiry -->
            <div class="grid grid-2 gap-md mb-lg">
                <div class="form-group">
                    <label class="form-label">Serves (Approx.)</label>
                    <input type="number" name="people_served" id="people_served" class="form-control" 
                           value="<?php echo $data['people_served']; ?>" placeholder="No. of people">
                </div>
                <div class="form-group">
                    <label class="form-label">Expires On</label>
                    <div class="grid grid-2 gap-xs">
                        <input type="date" name="expiry_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        <input type="time" name="expiry_time" class="form-control" required>
                    </div>
                    <span class="invalid-feedback"><?php echo $data['expiry_err']; ?></span>
                </div>
            </div>
            
            <!-- Description -->
            <div class="form-group mb-lg">
                <label class="form-label">Description (Optional)</label>
                <textarea name="description" id="description" class="form-control" rows="3" 
                          placeholder="Any details about ingredients, allergens, or packaging..."><?php echo $data['description']; ?></textarea>
            </div>
            
            <!-- Pickup Address -->
            <div class="form-group mb-lg">
                <label class="form-label">Pickup Address</label>
                <textarea name="pickup_address" class="form-control" rows="2" required 
                          placeholder="Full address for pickup"><?php echo $data['pickup_address']; ?></textarea>
                <span class="invalid-feedback"><?php echo $data['address_err']; ?></span>
            </div>
            
            <div class="form-group mb-xl">
                <label class="form-label">City</label>
                <input type="text" name="pickup_city" class="form-control" 
                       value="<?php echo $data['pickup_city']; ?>" placeholder="City" required>
            </div>
            
            <!-- Hidden Image URL -->
            <input type="hidden" name="food_image_url" id="food_image_url" value="">
            
            <button type="submit" class="btn btn-primary btn-lg" style="width: 100%; justify-content: center;">
                <i class="fas fa-check-circle"></i> Submit Donation
            </button>
        </form>
    </div>
    
    <!-- Live Preview Section -->
    <div class="preview-card-container">
        <h3 class="mb-md" style="color: var(--text-secondary); font-size: 1rem; text-transform: uppercase; letter-spacing: 1px;">Live Preview</h3>
        
        <div class="preview-card">
            <div class="preview-image-container">
                <!-- Loading Overlay -->
                <div class="loading-overlay" id="image_loading">
                    <div class="spinner"></div>
                </div>
                
                <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80" alt="Food Preview" class="preview-image" id="preview_img">
                <div class="preview-badge" id="preview_badge">Cooked</div>
                
                <div class="image-controls">
                    <button type="button" class="control-btn" id="refresh_btn" onclick="fetchFoodImages(true)" title="Refresh Image options">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                    <button type="button" class="control-btn" onclick="openImageSearch()" title="Search Image">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            
            <div class="preview-content">
                <h3 style="font-size: 1.4rem; margin-bottom: 0.5rem;" id="preview_title">Your Food Title</h3>
                
                <div class="thumbnails-grid" id="thumbnails_grid">
                    <!-- Thumbnails will be injected here -->
                </div>
                
                <p style="color: var(--text-secondary); margin: 1rem 0;" id="preview_desc">
                    <i class="fas fa-map-marker-alt"></i> <span id="preview_location">Location</span>
                </p>
                
                <div class="grid grid-2 gap-sm">
                    <div style="background: rgba(255,255,255,0.05); padding: 0.5rem; border-radius: 8px; text-align: center;">
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">Quantity</div>
                        <div style="font-weight: 600;" id="preview_qty">--</div>
                    </div>
                    <div style="background: rgba(255,255,255,0.05); padding: 0.5rem; border-radius: 8px; text-align: center;">
                        <div style="font-size: 0.8rem; color: var(--text-secondary);">Serves</div>
                        <div style="font-weight: 600;" id="preview_serves">--</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-lg p-md" style="background: rgba(102, 126, 234, 0.1); border-radius: var(--radius-md); border: 1px solid rgba(102, 126, 234, 0.2);">
            <div style="display: flex; gap: 1rem;">
                <i class="fas fa-magic" style="color: #667eea; font-size: 1.5rem;"></i>
                <div>
                    <h4 style="color: #667eea; margin-bottom: 0.25rem;">AI Magic</h4>
                    <p style="font-size: 0.9rem; opacity: 0.8;">We automatically find the best image for your food. Don't like it? Click the refresh button on the image!</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let typingTimer;
    let suggestionTimer;
    const DEBOUNCE_TIME = 500;
    const SUGGEST_DEBOUNCE = 300;
    
    // UI Interaction
    function selectCategory(type) {
        document.getElementById('food_type').value = type;
        document.querySelectorAll('.selector-item').forEach(el => el.classList.remove('active'));
        // Find the element with onclick containing this type
        const items = document.querySelectorAll('.selector-item');
        items.forEach(item => {
            if(item.getAttribute('onclick').includes(type)) {
                item.classList.add('active');
            }
        });
        
        document.getElementById('preview_badge').innerText = type.charAt(0).toUpperCase() + type.slice(1);
        if(document.getElementById('food_name').value) {
            fetchFoodImages();
        }
    }
    
    // Live Preview Updates
    const foodNameInput = document.getElementById('food_name');
    const suggestionsBox = document.getElementById('suggestions');

    foodNameInput.addEventListener('input', function(e) {
        const val = e.target.value;
        document.getElementById('preview_title').innerText = val || 'Your Food Title';
        
        // Handle Suggestions
        clearTimeout(suggestionTimer);
        if(val.length > 2) {
            suggestionTimer = setTimeout(() => getSuggestions(val), SUGGEST_DEBOUNCE);
        } else {
            suggestionsBox.style.display = 'none';
        }

        // Handle Image Fetch
        clearTimeout(typingTimer);
        if(val.length > 2) {
            typingTimer = setTimeout(() => fetchFoodImages(), DEBOUNCE_TIME);
        }
    });

    // Close suggestions on click outside
    document.addEventListener('click', function(e) {
        if (e.target !== foodNameInput) {
            suggestionsBox.style.display = 'none';
        }
    });

    async function getSuggestions(query) {
        try {
            const response = await fetch(`../../api/get-food-suggestions.php?q=${encodeURIComponent(query)}`);
            const suggestions = await response.json();
            
            if (suggestions.length > 0) {
                suggestionsBox.innerHTML = suggestions.map(s => `<div class="suggestion-item" onclick="selectSuggestion('${s}')">${s}</div>`).join('');
                suggestionsBox.style.display = 'block';
            } else {
                suggestionsBox.style.display = 'none';
            }
        } catch (error) {
            console.error('Suggestion error:', error);
        }
    }

    function selectSuggestion(val) {
        foodNameInput.value = val;
        document.getElementById('preview_title').innerText = val;
        suggestionsBox.style.display = 'none';
        fetchFoodImages();
    }
    
    document.getElementById('quantity').addEventListener('input', updatePreviewQty);
    document.getElementById('unit').addEventListener('change', updatePreviewQty);
    
    function updatePreviewQty() {
        const qty = document.getElementById('quantity').value;
        const unit = document.getElementById('unit').value;
        document.getElementById('preview_qty').innerText = (qty ? qty : '--') + ' ' + unit;
    }
    
    document.getElementById('people_served').addEventListener('input', function(e) {
        document.getElementById('preview_serves').innerText = (e.target.value ? e.target.value : '--') + ' People';
    });
    
    document.querySelector('input[name="pickup_city"]').addEventListener('input', function(e) {
        document.getElementById('preview_location').innerText = e.target.value || 'Location';
    });
    
    // Advanced Image Fetching
    async function fetchFoodImages(isRefresh = false, customQuery = null) {
        const foodName = customQuery || foodNameInput.value;
        const category = document.getElementById('food_type').value;
        
        if (!foodName) return;

        const loader = document.getElementById('image_loading');
        const refreshBtn = document.getElementById('refresh_btn');
        
        loader.style.display = 'flex';
        if(refreshBtn) refreshBtn.classList.add('fa-spin');

        try {
            const response = await fetch('../../api/get-food-image.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ food_name: foodName, category: category, refresh: isRefresh })
            });
            
            const data = await response.json();
            
            if (data.success && data.images) {
                renderThumbnails(data.images);
                // Select first image by default
                selectImage(data.images[0], 0);
            }
        } catch (error) {
            console.error('Image fetch error:', error);
        } finally {
            loader.style.display = 'none';
            if(refreshBtn) refreshBtn.classList.remove('fa-spin');
        }
    }

    function renderThumbnails(images) {
        const grid = document.getElementById('thumbnails_grid');
        grid.innerHTML = images.map((url, index) => `
            <div class="thumbnail-item" onclick="selectImage('${url}', ${index})" data-index="${index}">
                <img src="${url}" alt="Food Option">
            </div>
        `).join('');
    }

    function selectImage(url, index) {
        document.getElementById('preview_img').src = url;
        document.getElementById('food_image_url').value = url;
        
        // Update active state in grid
        document.querySelectorAll('.thumbnail-item').forEach(el => el.classList.remove('active'));
        const activeItem = document.querySelector(`.thumbnail-item[data-index="${index}"]`);
        if(activeItem) activeItem.classList.add('active');
    }

    function openImageSearch() {
        const term = prompt("What food are you looking for?");
        if(term) fetchFoodImages(false, term);
    }

    // AI Food Detection
    function triggerAIUpload() {
        document.getElementById('ai_upload').click();
    }

    function handleAIUpload(input) {
        if (input.files && input.files[0]) {
            const loader = document.getElementById('image_loading');
            loader.style.display = 'flex';
            
            // Mock AI detection delay
            setTimeout(() => {
                // In real implementation, you'd upload input.files[0] to Vision API
                // For demo, we detect name from filename or just use a placeholder
                const mockNames = ['Pizza', 'Burger', 'Pasta', 'Salad', 'Biryani'];
                const detectedName = mockNames[Math.floor(Math.random() * mockNames.length)];
                
                foodNameInput.value = detectedName;
                document.getElementById('preview_title').innerText = detectedName;
                fetchFoodImages(false, detectedName);
                
                alert(`AI Detected: ${detectedName}! Images updated.`);
                loader.style.display = 'none';
            }, 1000);
        }
    }
    
    // Initial update
    updatePreviewQty();
</script>

<?php
$content = ob_get_clean();
include '../../partials/layout.php';
?>
