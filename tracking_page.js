// Load tracking number from sessionStorage, localStorage, or generate one
window.addEventListener('DOMContentLoaded', function() {
    // First try sessionStorage (from tracking search)
    let trackingNumber = sessionStorage.getItem('trackingNumber');
    
    // If not in sessionStorage, try localStorage (from form submission)
    if (!trackingNumber) {
        trackingNumber = localStorage.getItem('trackingNumber');
    }
    
    if (!trackingNumber) {
        // Generate tracking number if not found
        trackingNumber = 'ERK-' + Date.now().toString(36).toUpperCase() + 
                        Math.random().toString(36).substring(2, 5).toUpperCase();
    }
    
    document.getElementById('trackingNumber').textContent = trackingNumber;
    
    // Clear sessionStorage after loading (so refreshing doesn't keep the same number)
    // but keep it if it came from localStorage (form submission)
    if (sessionStorage.getItem('trackingNumber')) {
        sessionStorage.removeItem('trackingNumber');
    }
});

// Copy tracking number to clipboard
function copyTrackingNumber() {
    const trackingNumber = document.getElementById('trackingNumber').textContent;
    const copyIcon = document.getElementById('copyIcon');
    
    navigator.clipboard.writeText(trackingNumber).then(function() {
        // Change icon to checkmark
        copyIcon.innerHTML = `
            <polyline points="20 6 9 17 4 12"></polyline>
        `;
        
        // Show feedback
        const copyButton = copyIcon.closest('.copy-button');
        copyButton.style.background = '#10b981';
        
        // Reset after 2 seconds
        setTimeout(function() {
            copyIcon.innerHTML = `
                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
            `;
            copyButton.style.background = 'var(--primary)';
        }, 2000);
    }).catch(function(err) {
        alert('Failed to copy tracking number');
    });
}
