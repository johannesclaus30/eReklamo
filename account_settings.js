// Initialize address selector on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize the Philippine address selector if available
    if (typeof window.initializeAddressSelector === 'function') {
        window.initializeAddressSelector();
    }
});

// Function to set text value to hidden field
function setText(nameSel, hiddenId) {
    const opt = document.querySelector(nameSel + " option:checked");
    document.getElementById(hiddenId).value = opt ? opt.text : "";
}

// Tab Switching Functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button and corresponding content
            this.classList.add('active');
            document.getElementById(tabName + '-tab').classList.add('active');
        });
    });

    // Initialize hidden fields with current values on page load
    ["#region", "#province", "#city", "#barangay"].forEach((sel, i) => {
        const ids = ["User_Region_Name", "User_Province_Name", "User_City_Name", "User_Barangay_Name"];
        setText(sel, ids[i]);
    });
});

// Password Toggle Functionality
function togglePassword(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const eyeIcon = document.getElementById(iconId);
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
    } else {
        passwordInput.type = 'password';
        eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
    }
}

// ✅ Allow normal form submissions (PHP will handle saving to DB)

// Optional — add client-side checks (non-blocking)
function validatePersonalForm() {
    const firstName = document.getElementById('firstName').value.trim();
    const lastName = document.getElementById('lastName').value.trim();
    const email = document.getElementById('email').value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!firstName || !lastName || !email) {
        alert('Please fill in all required fields');
        return false; // prevent form submission only if empty
    }

    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address');
        return false;
    }

    return true; // allow PHP to handle the rest
}

function validateAddressForm() {
    const region = document.getElementById('region').value;
    const province = document.getElementById('province').value;
    const city = document.getElementById('city').value;
    const barangay = document.getElementById('barangay').value;
    const street = document.getElementById('street').value.trim();

    if (!region || !province || !city || !barangay || !street) {
        alert('Please fill in all required address fields');
        return false;
    }

    return true;
}

function validatePasswordForm() {
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (!currentPassword || !newPassword || !confirmPassword) {
        alert('Please fill in all password fields');
        return false;
    }

    if (newPassword !== confirmPassword) {
        alert('New passwords do not match');
        return false;
    }

    if (newPassword.length < 6) {
        alert('Password must be at least 6 characters long');
        return false;
    }

    return true;
}

// Add smooth scrolling for better UX
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});
