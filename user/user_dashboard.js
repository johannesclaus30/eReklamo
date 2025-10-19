// Mock complaint data - using dynamic dates for testing
const today = new Date();
const complaints = [
    {
        id: '1',
        trackingNumber: 'ERK-2024-001234',
        category: 'Infrastructure',
        subcategory: 'Road Damage',
        description: 'Large pothole on Main Street causing traffic issues',
        location: 'Barangay 1',
        status: 'in-progress',
        dateSubmitted: new Date(today.getTime() - 5 * 24 * 60 * 60 * 1000).toISOString().split('T')[0], // 5 days ago
        lastUpdated: new Date(today.getTime() - 2 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
    },
    {
        id: '2',
        trackingNumber: 'ERK-2024-001189',
        category: 'Public Safety',
        subcategory: 'Street Lighting',
        description: 'Broken street light near the park',
        location: 'Barangay 2',
        status: 'resolved',
        dateSubmitted: new Date(today.getTime() - 15 * 24 * 60 * 60 * 1000).toISOString().split('T')[0], // 15 days ago
        lastUpdated: new Date(today.getTime() - 1 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
    },
    {
        id: '3',
        trackingNumber: 'ERK-2024-001298',
        category: 'Health and Sanitation',
        subcategory: 'Waste Collection',
        description: 'Garbage not collected for 3 weeks',
        location: 'Barangay 3',
        status: 'pending',
        dateSubmitted: new Date(today.getTime() - 2 * 24 * 60 * 60 * 1000).toISOString().split('T')[0], // 2 days ago
        lastUpdated: new Date(today.getTime() - 2 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
    },
    {
        id: '4',
        trackingNumber: 'ERK-2024-001056',
        category: 'Peace and Order',
        subcategory: 'Noise Disturbance',
        description: 'Construction noise at night beyond permitted hours',
        location: 'Barangay 1',
        status: 'rejected',
        dateSubmitted: new Date(today.getTime() - 45 * 24 * 60 * 60 * 1000).toISOString().split('T')[0], // 45 days ago
        lastUpdated: new Date(today.getTime() - 40 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
    },
    {
        id: '5',
        trackingNumber: 'ERK-2024-002001',
        category: 'Environment',
        subcategory: 'Illegal Dumping',
        description: 'Illegal waste dumping site near residential area',
        location: 'Barangay 2',
        status: 'pending',
        dateSubmitted: new Date(today.getTime() - 200 * 24 * 60 * 60 * 1000).toISOString().split('T')[0], // 200 days ago (last year)
        lastUpdated: new Date(today.getTime() - 200 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
    }
];

const statusLabels = {
    'pending': 'Pending Review',
    'in-progress': 'In Progress',
    'resolved': 'Resolved',
    'rejected': 'Rejected'
};

const statusIcons = {
    'pending': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
    'in-progress': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>',
    'resolved': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
    'rejected': '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>'
};

// Load user email from localStorage
window.addEventListener('DOMContentLoaded', function() {
    // const userEmail = localStorage.getItem('<?php ec ?>') || 'user@example.com';
    // document.getElementById('userEmail').textContent = userEmail;
    
    populateLocationFilter();
    renderComplaints(complaints);
});

function populateLocationFilter() {
    const locationFilter = document.getElementById('locationFilter');
    
    // Get unique locations from complaints
    const locations = [...new Set(complaints.map(c => c.location))].sort();
    
    // Clear existing options except "All Locations"
    locationFilter.innerHTML = '<option value="all">All Locations</option>';
    
    // Add location options
    locations.forEach(location => {
        const option = document.createElement('option');
        option.value = location;
        option.textContent = location;
        locationFilter.appendChild(option);
    });
}

function renderComplaints(complaintsToRender) {
    const container = document.getElementById('complaintsList');
    const emptyState = document.getElementById('emptyState');
    
    if (complaintsToRender.length === 0) {
        container.style.display = 'none';
        emptyState.style.display = 'block';
        
        const searchTerm = document.getElementById('searchInput').value;
        const filterStatus = document.getElementById('statusFilter').value;
        const filterTime = document.getElementById('timeFilter').value;
        const filterLocation = document.getElementById('locationFilter').value;
        
        if (searchTerm || filterStatus !== 'all' || filterTime !== 'all' || filterLocation !== 'all') {
            document.getElementById('emptyMessage').textContent = 'No complaints match your current filters. Try adjusting your search or filters.';
        } else {
            document.getElementById('emptyMessage').textContent = "You haven't submitted any complaints yet";
        }
        return;
    }
    
    container.style.display = 'flex';
    emptyState.style.display = 'none';
    
    container.innerHTML = complaintsToRender.map(complaint => `
        <div class="complaint-card" data-status="${complaint.status}">
            <div class="complaint-header">
                <div class="complaint-icon">
                    ${statusIcons[complaint.status]}
                </div>
                <div class="complaint-info">
                    <div class="complaint-title">
                        <span class="tracking-number">${complaint.trackingNumber}</span>
                        <span class="status-badge status-${complaint.status}">
                            ${statusLabels[complaint.status]}
                        </span>
                    </div>
                    <div class="complaint-meta">
                        ${complaint.category} • ${complaint.subcategory}
                    </div>
                </div>
            </div>
            
            <p class="complaint-description">${complaint.description}</p>
            
            <div class="complaint-details">
                <div class="detail-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    ${complaint.location}
                </div>
                <div class="detail-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    Submitted: ${formatDate(complaint.dateSubmitted)}
                </div>
                <div class="detail-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                    </svg>
                    Updated: ${formatDate(complaint.lastUpdated)}
                </div>
            </div>
            
            <div class="complaint-actions">
                <button onclick="viewComplaintDetails('${complaint.trackingNumber}')" class="btn btn-outline">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    View Details
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>
        </div>
    `).join('');
}

function filterComplaints() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const filterStatus = document.getElementById('statusFilter').value;
    const filterTime = document.getElementById('timeFilter').value;
    const filterLocation = document.getElementById('locationFilter').value;
    
    const filtered = complaints.filter(complaint => {
        // Search filter
        const matchesSearch = complaint.trackingNumber.toLowerCase().includes(searchTerm) ||
                             complaint.category.toLowerCase().includes(searchTerm) ||
                             complaint.description.toLowerCase().includes(searchTerm);
        
        // Status filter
        const matchesStatus = filterStatus === 'all' || complaint.status === filterStatus;
        
        // Location filter
        const matchesLocation = filterLocation === 'all' || complaint.location === filterLocation;
        
        // Time filter
        const matchesTime = filterByTime(complaint.dateSubmitted, filterTime);
        
        return matchesSearch && matchesStatus && matchesLocation && matchesTime;
    });
    
    renderComplaints(filtered);
}

function filterByTime(dateSubmitted, timeFilter) {
    if (timeFilter === 'all') {
        return true;
    }
    
    const submittedDate = new Date(dateSubmitted);
    const now = new Date();
    
    // Set hours to 0 for accurate date comparison
    submittedDate.setHours(0, 0, 0, 0);
    now.setHours(0, 0, 0, 0);
    
    const daysDiff = Math.floor((now - submittedDate) / (1000 * 60 * 60 * 24));
    
    switch (timeFilter) {
        case 'week':
            return daysDiff <= 7;
        case 'month':
            return daysDiff <= 30;
        case 'year':
            return daysDiff <= 365;
        default:
            return true;
    }
}

function viewComplaintDetails(trackingNumber) {
    // Store tracking number in sessionStorage
    sessionStorage.setItem('trackingNumber', trackingNumber);
    // Redirect to tracking page
    window.location.href = 'tracking_page.html';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric'
    });
}