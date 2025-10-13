// Subcategories data
const subcategories = {
    infrastructure: [
        { value: 'damaged_roads', label: 'Damaged Road' },
        { value: 'bridges', label: 'Sewer Blockage' },
        { value: 'blocked_footpaths', label: 'Blocked Footpaths/Sidewalks' },
        { value: 'weak_streetlights', label: 'Weak Streetlights' },
        { value: 'leaking_waterpipes', label: 'Leaking Waterpipes' },
        { value: 'broken_potholes', label: 'Broken/Missing Potholes' },
        { value: 'bridge_issues', label: 'Bridge/Overpass Issues' }
    ],
    environment: [
        { value: 'eroded_soil', label: 'Eroded Soil' },
        { value: 'flooding', label: 'Flooding' },
        { value: 'airwater_pollution', label: 'Air and Water Pollution' },
        { value: 'littering', label: 'Littering' },
        { value: 'illegal_tree_cutting', label: 'Illegal Tree Cutting' },
        { value: 'garbage_missing_collection', label: 'Garbage Missing Collection' },
        { value: 'burning_of_garbage', label: 'Burning of Garbage' },
        { value: 'nonsweeping_of_streets', label: 'Non-sweeping of Streets' }
    ],
    peace_and_order: [
        { value: 'citizen_misconduct', label: 'Citizen Misconduct' },
        { value: 'noise_pollution', label: 'Noise Pollution' },
        { value: 'domestic_disturbance', label: 'Domestic Disturbance' },
        { value: 'improper_parking', label: 'Improper Parking' }
    ],
    health_and_sanitation: [
        { value: 'trash_dumps', label: 'Trash Dumps' },
        { value: 'animal_waste', label: 'Animal Waste' },
        { value: 'sewage_problems', label: 'Sewage Problems' },
        { value: 'pest_infestation', label: 'Pest/Rodents Infestation' },
        { value: 'waste_runoff', label: 'Waste Runoff from Animal Pens' }
    ],
    public_safety: [
        { value: 'fire_hazard', label: 'Fire Hazard' },
        { value: 'road_accidents', label: 'Road Accidents' },
        { value: 'cablewiring_issues', label: 'Cable/Wiring Issues' },
        { value: 'construction_debris', label: 'Construction Debris on Road' }
    ],
    traffic_and_transportation: [
        { value: 'traffic_signal_malfunctions', label: 'Traffic Signal Malfunctions' },
        { value: 'illegal_parking_av', label: 'Illegal Parking or Abandoned Vehicles' },
        { value: 'road_congestionsignage_issues', label: 'Road Congestion/Signage Issues' },
        { value: 'bike_lane_obstruction', label: 'Bike Lane Obstruction' },
        { value: 'Speedreckless_driving', label: 'Speed/Reckless driving' },
        { value: 'pedestrian_crossing_isssues', label: 'Pedestrian Crossing Issues' }
    ],
    others: [
        { value: 'others', label: 'Others'}
    ]
};

// Photo and video storage
let photos = [];
let video = null;

// Category change handler
document.getElementById('category').addEventListener('change', function() {
    const subcategorySelect = document.getElementById('subcategory');
    const otherCategoryGroup = document.getElementById('otherCategoryGroup');
    const otherCategoryInput = document.getElementById('otherCategory');
    const category = this.value;
    
    subcategorySelect.innerHTML = '<option value="">Select a subcategory</option>';
    
    // Check if "Others" category is selected
    if (category === 'others') {
        // Show the "Other" text field
        otherCategoryGroup.style.display = 'block';
        otherCategoryInput.setAttribute('required', 'required');
        
        // Disable and clear subcategory
        subcategorySelect.disabled = true;
        subcategorySelect.removeAttribute('required');
        subcategorySelect.value = '';
    } else {
        // Hide the "Other" text field
        otherCategoryGroup.style.display = 'none';
        otherCategoryInput.removeAttribute('required');
        otherCategoryInput.value = '';
        
        // Enable subcategory for other categories
        subcategorySelect.setAttribute('required', 'required');
        
        if (category && subcategories[category]) {
            subcategorySelect.disabled = false;
            subcategories[category].forEach(sub => {
                const option = document.createElement('option');
                option.value = sub.value;
                option.textContent = sub.label;
                subcategorySelect.appendChild(option);
            });
        } else {
            subcategorySelect.disabled = true;
        }
    }
});

// Location cascading dropdowns
// Replace the Fetch API calls with your own implementation



// Character counter for description
document.getElementById('description').addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length;
});

// Photo upload handler
document.getElementById('photoUpload').addEventListener('click', function() {
    if (video) {
        alert('You cannot upload photos when a video is already attached. Please remove the video first.');
        return;
    }
    
    if (photos.length < 5) {
        document.getElementById('photoInput').click();
    }
});

document.getElementById('photoInput').addEventListener('change', function(e) {
    if (video) {
        alert('You cannot upload photos when a video is already attached. Please remove the video first.');
        this.value = '';
        return;
    }
    
    const files = Array.from(e.target.files);
    const remainingSlots = 5 - photos.length;
    
    if (files.length > remainingSlots) {
        alert(`You can only upload ${remainingSlots} more photo(s). Maximum is 5 photos.`);
        return;
    }
    
    files.forEach(file => {
        if (photos.length < 5) {
            photos.push(file);
            displayPhotoPreview(file, photos.length - 1);
        }
    });
    
    updateUploadStates();
    this.value = '';
});

function displayPhotoPreview(file, index) {
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('photoPreview');
        const div = document.createElement('div');
        div.className = 'preview-item';
        div.innerHTML = `
            <img src="${e.target.result}" alt="Preview ${index + 1}">
            <button type="button" class="preview-remove" onclick="removePhoto(${index})">×</button>
        `;
        preview.appendChild(div);
    };
    reader.readAsDataURL(file);
}

window.removePhoto = function(index) {
    photos.splice(index, 1);
    const preview = document.getElementById('photoPreview');
    preview.innerHTML = '';
    photos.forEach((photo, i) => {
        displayPhotoPreview(photo, i);
    });
    updateUploadStates();
}

window.clearAllPhotos = function() {
    if (photos.length === 0) return;
    
    if (confirm(`Are you sure you want to remove all ${photos.length} photo(s)?`)) {
        photos = [];
        document.getElementById('photoPreview').innerHTML = '';
        document.getElementById('photoInput').value = '';
        updateUploadStates();
    }
}

// Video upload handler
document.getElementById('videoUpload').addEventListener('click', function() {
    if (photos.length > 0) {
        alert('You cannot upload a video when photos are already attached. Please remove the photos first.');
        return;
    }
    document.getElementById('videoInput').click();
});

document.getElementById('videoInput').addEventListener('change', function(e) {
    if (photos.length > 0) {
        alert('You cannot upload a video when photos are already attached. Please remove the photos first.');
        this.value = '';
        return;
    }
    
    const file = e.target.files[0];
    if (file) {
        video = file;
        displayVideoPreview(file);
        updateUploadStates();
    }
});

function displayVideoPreview(file) {
    const preview = document.getElementById('videoPreview');
    preview.innerHTML = `
        <div class="video-preview">
            <div class="video-info">
                <div class="video-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polygon points="23 7 16 12 23 17 23 7"></polygon>
                        <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                    </svg>
                </div>
                <div>
                    <strong>${file.name}</strong>
                    <br>
                    <small>${(file.size / (1024 * 1024)).toFixed(2)} MB</small>
                </div>
            </div>
            <button type="button" class="preview-remove" onclick="removeVideo()">×</button>
        </div>
    `;
}

window.removeVideo = function() {
    video = null;
    document.getElementById('videoPreview').innerHTML = '';
    document.getElementById('videoInput').value = '';
    updateUploadStates();
}

window.clearAllAttachments = function() {
    const hasPhotos = photos.length > 0;
    const hasVideo = video !== null;
    
    if (!hasPhotos && !hasVideo) return;
    
    let message = 'Are you sure you want to remove all attachments?';
    if (hasPhotos && hasVideo) {
        message = `Are you sure you want to remove all ${photos.length} photo(s) and 1 video?`;
    } else if (hasPhotos) {
        message = `Are you sure you want to remove all ${photos.length} photo(s)?`;
    } else if (hasVideo) {
        message = 'Are you sure you want to remove the video?';
    }
    
    if (confirm(message)) {
        photos = [];
        video = null;
        document.getElementById('photoPreview').innerHTML = '';
        document.getElementById('videoPreview').innerHTML = '';
        document.getElementById('photoInput').value = '';
        document.getElementById('videoInput').value = '';
        updateUploadStates();
    }
}

// Update upload areas based on current state
function updateUploadStates() {
    const photoUpload = document.getElementById('photoUpload');
    const videoUpload = document.getElementById('videoUpload');
    const photoCount = document.getElementById('photoCount');
    const clearAllPhotosBtn = document.getElementById('clearAllPhotos');
    const clearAllAttachmentsBtn = document.getElementById('clearAllAttachments');
    const photoInfo = document.getElementById('photoInfo');
    
    // Update photo counter
    if (photoCount) {
        photoCount.textContent = `${photos.length}/5`;
        photoCount.classList.remove('has-items', 'is-full');
        if (photos.length > 0 && photos.length < 5) {
            photoCount.classList.add('has-items');
        } else if (photos.length === 5) {
            photoCount.classList.add('is-full');
        }
    }
    
    // Show/hide clear all photos button
    if (clearAllPhotosBtn) {
        clearAllPhotosBtn.style.display = photos.length > 0 ? 'inline-flex' : 'none';
    }
    
    // Show/hide photo info message
    if (photoInfo) {
        photoInfo.style.display = photos.length > 0 ? 'flex' : 'none';
    }
    
    // Show/hide clear all attachments button
    if (clearAllAttachmentsBtn) {
        clearAllAttachmentsBtn.style.display = (photos.length > 0 || video) ? 'inline-flex' : 'none';
    }
    
    // Disable photo upload if video is attached
    if (video) {
        photoUpload.classList.add('upload-disabled');
        photoUpload.style.cursor = 'not-allowed';
        photoUpload.style.opacity = '0.5';
        
        // Add disabled message to photo upload
        const photoText = photoUpload.querySelector('.upload-text');
        if (photoText && !photoText.dataset.original) {
            photoText.dataset.original = photoText.textContent;
            photoText.textContent = 'Photo upload disabled - Video is attached';
            photoText.style.color = '#d97706';
        }
    } else {
        photoUpload.classList.remove('upload-disabled');
        photoUpload.style.cursor = 'pointer';
        photoUpload.style.opacity = '1';
        
        // Restore original text
        const photoText = photoUpload.querySelector('.upload-text');
        if (photoText && photoText.dataset.original) {
            photoText.textContent = photoText.dataset.original;
            photoText.style.color = '';
            delete photoText.dataset.original;
        }
    }
    
    // Disable video upload if photos are attached
    if (photos.length > 0) {
        videoUpload.classList.add('upload-disabled');
        videoUpload.style.cursor = 'not-allowed';
        videoUpload.style.opacity = '0.5';
        
        // Add disabled message to video upload
        const videoText = videoUpload.querySelector('.upload-text');
        if (videoText && !videoText.dataset.original) {
            videoText.dataset.original = videoText.textContent;
            videoText.textContent = 'Video upload disabled - Photos are attached';
            videoText.style.color = '#d97706';
        }
    } else {
        videoUpload.classList.remove('upload-disabled');
        videoUpload.style.cursor = 'pointer';
        videoUpload.style.opacity = '1';
        
        // Restore original text
        const videoText = videoUpload.querySelector('.upload-text');
        if (videoText && videoText.dataset.original) {
            videoText.textContent = videoText.dataset.original;
            videoText.style.color = '';
            delete videoText.dataset.original;
        }
    }
}

// Form submission
document.getElementById('complaintForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const category = document.getElementById('category').value;
    const subcategory = document.getElementById('subcategory').value;
    const description = document.getElementById('description').value;
    const location = document.getElementById('location').value;
    const otherCategory = document.getElementById('otherCategory').value;
    
    // Validate required fields
    if (!category || !description || !location) {
        alert('Please fill in all required fields');
        return;
    }
    
    // If "Others" category is selected, validate the "Other" text field
    if (category === 'others') {
        if (!otherCategory || otherCategory.trim() === '') {
            alert('Please specify your complaint in the text field');
            return;
        }
    } else {
        // For other categories, validate subcategory
        if (!subcategory) {
            alert('Please select a subcategory');
            return;
        }
    }
    
    // Generate tracking number
    const trackingNumber = 'ERK-' + Date.now().toString(36).toUpperCase() + 
                          Math.random().toString(36).substring(2, 5).toUpperCase();
    
    // Store tracking number and redirect
    localStorage.setItem('trackingNumber', trackingNumber);
    window.location.href = 'tracking_page.html';
});
