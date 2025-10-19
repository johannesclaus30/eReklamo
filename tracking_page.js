window.addEventListener('DOMContentLoaded', function() {
    const trackingSpan = document.getElementById('trackingNumber');
    
    // If PHP already inserted a tracking number, use that
    if (trackingSpan && trackingSpan.textContent.trim() !== '') {
        console.log('Using PHP tracking number:', trackingSpan.textContent);
        return; // ✅ Stop here, don’t overwrite it
    }

    // // Otherwise, try sessionStorage or localStorage
    // let trackingNumber = sessionStorage.getItem('trackingNumber');

    // if (!trackingNumber) {
    //     trackingNumber = localStorage.getItem('trackingNumber');
    // }

    // if (!trackingNumber) {
    //     // ✅ Generate a purely numeric tracking number based on timestamp
    //     const timestamp = Date.now(); // e.g. 1739923846231
    //     trackingNumber = 'ERK-' + timestamp;
    // }

    // trackingSpan.textContent = trackingNumber;

    // Clear sessionStorage (if used)
    if (sessionStorage.getItem('trackingNumber')) {
        sessionStorage.removeItem('trackingNumber');
    }
});

// ✅ Copy tracking number function remains the same
function copyTrackingNumber() {
    const trackingNumber = document.getElementById('trackingNumber').textContent;
    const copyIcon = document.getElementById('copyIcon');
    
    navigator.clipboard.writeText(trackingNumber).then(function() {
        copyIcon.innerHTML = `<polyline points="20 6 9 17 4 12"></polyline>`;
        const copyButton = copyIcon.closest('.copy-button');
        copyButton.style.background = '#10b981';
        
        setTimeout(function() {
            copyIcon.innerHTML = `
                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
            `;
            copyButton.style.background = 'var(--primary)';
        }, 2000);
    }).catch(function() {
        alert('Failed to copy tracking number');
    });
}
