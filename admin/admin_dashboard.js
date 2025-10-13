// Category color mapping
const categoryColors = {
    'Infrastructure': 'rgba(255, 107, 53, 0.8)',
    'Public Safety': 'rgba(59, 130, 246, 0.8)',
    'Utilities': 'rgba(16, 185, 129, 0.8)',
    'Environment': 'rgba(245, 158, 11, 0.8)',
    'Health and Sanitation': 'rgba(239, 68, 68, 0.8)',
    'Traffic and Transportation': 'rgba(139, 92, 246, 0.8)',
    'Administrative': 'rgba(236, 72, 153, 0.8)',
    'Community Services': 'rgba(20, 184, 166, 0.8)',
    'Peace and Order': 'rgba(34, 197, 94, 0.8)'
};

const categoryBorderColors = {
    'Infrastructure': 'rgba(255, 107, 53, 1)',
    'Public Safety': 'rgba(59, 130, 246, 1)',
    'Utilities': 'rgba(16, 185, 129, 1)',
    'Environment': 'rgba(245, 158, 11, 1)',
    'Health and Sanitation': 'rgba(239, 68, 68, 1)',
    'Traffic and Transportation': 'rgba(139, 92, 246, 1)',
    'Administrative': 'rgba(236, 72, 153, 1)',
    'Community Services': 'rgba(20, 184, 166, 1)',
    'Peace and Order': 'rgba(34, 197, 94, 1)'
};

function getCategoryColor(category) {
    return categoryColors[category] || 'rgba(158, 158, 158, 0.8)';
}

function getCategoryBorderColor(category) {
    return categoryBorderColors[category] || 'rgba(158, 158, 158, 1)';
}

// Mock complaint data
let complaints = [
    {
        id: '1',
        trackingNumber: 'ERK-2024-001234',
        category: 'Infrastructure',
        subcategory: 'Road Damage',
        description: 'Large pothole on Main Street causing traffic issues',
        country: 'Philippines',
        region: 'National Capital Region (NCR)',
        province: 'Metro Manila',
        city: 'Manila',
        barangay: 'Barangay 1',
        street: 'Main Street',
        location: 'Main Street, Barangay 1, Manila, Metro Manila, NCR',
        status: 'in-progress',
        dateSubmitted: '2024-01-15',
        submittedBy: 'juan.delacruz@email.com'
    },
    {
        id: '2',
        trackingNumber: 'ERK-2024-001189',
        category: 'Public Safety',
        subcategory: 'Street Lighting',
        description: 'Broken street light near the park',
        country: 'Philippines',
        region: 'National Capital Region (NCR)',
        province: 'Metro Manila',
        city: 'Quezon City',
        barangay: 'Barangay 2',
        street: 'Commonwealth Avenue',
        location: 'Commonwealth Avenue, Barangay 2, Quezon City, Metro Manila, NCR',
        status: 'resolved',
        dateSubmitted: '2024-01-10',
        submittedBy: 'maria.santos@email.com'
    },
    {
        id: '3',
        trackingNumber: 'ERK-2024-001298',
        category: 'Health and Sanitation',
        subcategory: 'Waste Collection',
        description: 'Garbage not collected for 3 weeks',
        country: 'Philippines',
        region: 'Region VII (Central Visayas)',
        province: 'Cebu',
        city: 'Cebu City',
        barangay: 'Barangay 3',
        street: 'Osmena Boulevard',
        location: 'Osmena Boulevard, Barangay 3, Cebu City, Cebu, Central Visayas',
        status: 'pending',
        dateSubmitted: '2024-01-22',
        submittedBy: 'pedro.reyes@email.com'
    },
    {
        id: '4',
        trackingNumber: 'ERK-2024-001056',
        category: 'Peace and Order',
        subcategory: 'Noise Disturbance',
        description: 'Construction noise at night beyond permitted hours',
        country: 'Philippines',
        region: 'Region XI (Davao Region)',
        province: 'Davao del Sur',
        city: 'Davao City',
        barangay: 'Barangay 1',
        street: 'J.P. Laurel Avenue',
        location: 'J.P. Laurel Avenue, Barangay 1, Davao City, Davao del Sur, Davao Region',
        status: 'rejected',
        dateSubmitted: '2024-01-05',
        submittedBy: 'Guest User'
    },
    {
        id: '5',
        trackingNumber: 'ERK-2024-001145',
        category: 'Infrastructure',
        subcategory: 'Drainage Issues',
        description: 'Clogged drainage causing flooding during rain',
        country: 'Philippines',
        region: 'Region III (Central Luzon)',
        province: 'Pampanga',
        city: 'Angeles City',
        barangay: 'Barangay 4',
        street: 'MacArthur Highway',
        location: 'MacArthur Highway, Barangay 4, Angeles City, Pampanga, Central Luzon',
        status: 'resolved',
        dateSubmitted: '2024-01-08',
        submittedBy: 'ana.garcia@email.com'
    },
    {
        id: '6',
        trackingNumber: 'ERK-2024-001367',
        category: 'Traffic and Transportation',
        subcategory: 'Missing Signage',
        description: 'Missing stop sign at intersection',
        country: 'Philippines',
        region: 'Region IV-A (CALABARZON)',
        province: 'Laguna',
        city: 'Calamba City',
        barangay: 'Barangay 5',
        street: 'National Highway',
        location: 'National Highway, Barangay 5, Calamba City, Laguna, CALABARZON',
        status: 'pending',
        dateSubmitted: '2024-01-25',
        submittedBy: 'carlos.lopez@email.com'
    }
];

let selectedComplaint = null;
let readNotifications = JSON.parse(localStorage.getItem('readNotifications') || '[]');
let complaintChart = null;

const statusLabels = {
    'pending': 'Pending',
    'in-progress': 'In Progress',
    'resolved': 'Resolved',
    'rejected': 'Rejected'
};

const statusColors = {
    'pending': 'status-pending',
    'in-progress': 'status-in-progress',
    'resolved': 'status-resolved',
    'rejected': 'status-rejected'
};

// Initialize on page load
window.addEventListener('DOMContentLoaded', function() {
    updateStats();
    renderComplaints(complaints);
    updateNotifications();
    initializeChart();
    populateChartLocationFilters();
    
    // Set up search
    document.getElementById('searchInput').addEventListener('input', filterComplaints);
    
    // Set up status filter
    document.getElementById('statusFilter').addEventListener('change', filterComplaints);
    
    // Set up chart filters
    document.getElementById('chartPeriod').addEventListener('change', updateChartWithFilters);
    document.getElementById('chartRegionFilter').addEventListener('change', handleChartRegionChange);
    document.getElementById('chartProvinceFilter').addEventListener('change', handleChartProvinceChange);
    document.getElementById('chartCityFilter').addEventListener('change', handleChartCityChange);
    document.getElementById('chartBarangayFilter').addEventListener('change', updateChartWithFilters);
});

function updateStats() {
    const total = complaints.length;
    const pending = complaints.filter(c => c.status === 'pending').length;
    const progress = complaints.filter(c => c.status === 'in-progress').length;
    const resolved = complaints.filter(c => c.status === 'resolved').length;

    document.getElementById('totalCount').textContent = total;
    document.getElementById('pendingCount').textContent = pending;
    document.getElementById('progressCount').textContent = progress;
    document.getElementById('resolvedCount').textContent = resolved;
}

function renderComplaints(complaintsToRender) {
    const tbody = document.getElementById('complaintsTableBody');
    const emptyState = document.getElementById('emptyState');
    
    if (complaintsToRender.length === 0) {
        tbody.innerHTML = '';
        emptyState.style.display = 'table-row';
        return;
    }
    
    emptyState.style.display = 'none';
    
    tbody.innerHTML = complaintsToRender.map(complaint => `
        <tr>
            <td class="tracking-cell">${complaint.trackingNumber}</td>
            <td>
                <div class="category-cell">
                    <div class="category-main">${complaint.category}</div>
                    <div class="category-sub">${complaint.subcategory}</div>
                </div>
            </td>
            <td>
                <div class="description-cell" title="${complaint.description}">
                    ${complaint.description}
                </div>
            </td>
            <td>${complaint.location}</td>
            <td>${formatDate(complaint.dateSubmitted)}</td>
            <td>
                <select 
                    class="status-select ${statusColors[complaint.status]}"
                    onchange="handleStatusChange('${complaint.id}', this.value)"
                >
                    <option value="pending" ${complaint.status === 'pending' ? 'selected' : ''}>Pending</option>
                    <option value="in-progress" ${complaint.status === 'in-progress' ? 'selected' : ''}>In Progress</option>
                    <option value="resolved" ${complaint.status === 'resolved' ? 'selected' : ''}>Resolved</option>
                    <option value="rejected" ${complaint.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                </select>
            </td>
            <td>
                <div class="action-buttons">
                    <button 
                        class="btn-action btn-view" 
                        onclick="viewComplaintDetails('${complaint.id}')"
                        title="View Details"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        View
                    </button>
                    ${complaint.status === 'resolved' ? `
                        <button 
                            class="btn-action btn-delete" 
                            onclick="confirmDelete('${complaint.id}')"
                            title="Delete Resolved Complaint"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="m19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');
}

function filterComplaints() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    
    const filtered = complaints.filter(complaint => {
        // Search filter
        const matchesSearch = 
            complaint.trackingNumber.toLowerCase().includes(searchTerm) ||
            complaint.description.toLowerCase().includes(searchTerm) ||
            complaint.location.toLowerCase().includes(searchTerm) ||
            complaint.category.toLowerCase().includes(searchTerm) ||
            (complaint.street && complaint.street.toLowerCase().includes(searchTerm));
        
        // Status filter
        const matchesStatus = statusFilter === 'all' || complaint.status === statusFilter;
        
        return matchesSearch && matchesStatus;
    });
    
    renderComplaints(filtered);
}

function handleStatusChange(complaintId, newStatus) {
    complaints = complaints.map(c => 
        c.id === complaintId ? { ...c, status: newStatus } : c
    );
    
    updateStats();
    filterComplaints();
    updateNotifications();
    
    // Update chart with filters
    updateChartWithFilters();
    
    showToast('Complaint status updated successfully!', 'success');
}

function viewComplaintDetails(complaintId) {
    selectedComplaint = complaints.find(c => c.id === complaintId);
    if (!selectedComplaint) return;
    
    // Populate modal
    document.getElementById('modalTrackingNumber').textContent = selectedComplaint.trackingNumber;
    document.getElementById('modalCategory').textContent = selectedComplaint.category;
    document.getElementById('modalSubcategory').textContent = selectedComplaint.subcategory;
    document.getElementById('modalDescription').textContent = selectedComplaint.description;
    document.getElementById('modalLocation').textContent = selectedComplaint.location;
    document.getElementById('modalSubmittedBy').textContent = selectedComplaint.submittedBy;
    document.getElementById('modalDateSubmitted').textContent = formatDate(selectedComplaint.dateSubmitted);
    
    // Set status dropdown
    const statusSelect = document.getElementById('modalStatusSelect');
    statusSelect.value = selectedComplaint.status;
    statusSelect.className = `status-select ${statusColors[selectedComplaint.status]}`;
    
    // Show/hide delete button
    const deleteSection = document.getElementById('modalDeleteSection');
    if (selectedComplaint.status === 'resolved') {
        deleteSection.style.display = 'block';
    } else {
        deleteSection.style.display = 'none';
    }
    
    // Show modal
    document.getElementById('detailsModal').style.display = 'flex';
}

function closeDetailsModal() {
    document.getElementById('detailsModal').style.display = 'none';
    selectedComplaint = null;
}

function handleModalStatusChange(newStatus) {
    if (selectedComplaint) {
        handleStatusChange(selectedComplaint.id, newStatus);
        selectedComplaint.status = newStatus;
        
        // Update modal display
        const statusSelect = document.getElementById('modalStatusSelect');
        statusSelect.className = `status-select ${statusColors[newStatus]}`;
        
        // Update delete button visibility
        const deleteSection = document.getElementById('modalDeleteSection');
        if (newStatus === 'resolved') {
            deleteSection.style.display = 'block';
        } else {
            deleteSection.style.display = 'none';
        }
    }
}

function confirmDelete(complaintId) {
    const complaint = complaints.find(c => c.id === complaintId);
    if (!complaint) return;
    
    document.getElementById('deleteTrackingNumber').textContent = complaint.trackingNumber;
    document.getElementById('deleteConfirmModal').style.display = 'flex';
    
    // Store complaint ID for deletion
    document.getElementById('deleteConfirmModal').dataset.complaintId = complaintId;
}

function closeDeleteModal() {
    document.getElementById('deleteConfirmModal').style.display = 'none';
}

function executeDelete() {
    const complaintId = document.getElementById('deleteConfirmModal').dataset.complaintId;
    const complaint = complaints.find(c => c.id === complaintId);
    
    if (complaint) {
        complaints = complaints.filter(c => c.id !== complaintId);
        
        updateStats();
        filterComplaints();
        updateNotifications();
        
        // Update chart with filters
        updateChartWithFilters();
        
        showToast(`Complaint ${complaint.trackingNumber} deleted successfully!`, 'success');
        
        closeDeleteModal();
        closeDetailsModal();
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric'
    });
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    const detailsModal = document.getElementById('detailsModal');
    const deleteModal = document.getElementById('deleteConfirmModal');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationBtn = document.getElementById('notificationBtn');
    
    if (event.target === detailsModal) {
        closeDetailsModal();
    }
    if (event.target === deleteModal) {
        closeDeleteModal();
    }
    
    // Close notification dropdown when clicking outside
    if (notificationDropdown && !notificationDropdown.contains(event.target) && 
        !notificationBtn.contains(event.target)) {
        notificationDropdown.classList.remove('show');
    }
});

// Notification Functions
function updateNotifications() {
    // Get recent complaints (last 5, sorted by date)
    const recentComplaints = [...complaints]
        .sort((a, b) => new Date(b.dateSubmitted) - new Date(a.dateSubmitted))
        .slice(0, 5);
    
    const notificationList = document.getElementById('notificationList');
    const notificationBadge = document.getElementById('notificationBadge');
    
    // Count unread notifications
    const unreadCount = recentComplaints.filter(c => !readNotifications.includes(c.id)).length;
    
    // Update badge
    if (unreadCount > 0) {
        notificationBadge.textContent = unreadCount;
        notificationBadge.style.display = 'flex';
    } else {
        notificationBadge.style.display = 'none';
    }
    
    // Render notifications
    if (recentComplaints.length === 0) {
        notificationList.innerHTML = `
            <div class="notification-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <p>No recent complaints</p>
            </div>
        `;
    } else {
        notificationList.innerHTML = recentComplaints.map(complaint => {
            const isUnread = !readNotifications.includes(complaint.id);
            const timeAgo = getTimeAgo(complaint.dateSubmitted);
            
            return `
                <div class="notification-item ${isUnread ? 'unread' : ''}" onclick="viewComplaintFromNotification('${complaint.id}')">
                    <div class="notification-content">
                        <div class="notification-title">
                            ${isUnread ? '<span class="notification-dot"></span>' : ''}
                            <strong>${complaint.category}</strong> - ${complaint.subcategory}
                        </div>
                        <div class="notification-desc">${truncateText(complaint.description, 60)}</div>
                        <div class="notification-meta">
                            <span class="notification-location">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                ${complaint.location}
                            </span>
                            <span class="notification-time">${timeAgo}</span>
                        </div>
                    </div>
                    <span class="notification-status ${statusColors[complaint.status]}">
                        ${statusLabels[complaint.status]}
                    </span>
                </div>
            `;
        }).join('');
    }
}

function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.classList.toggle('show');
}

function closeNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.classList.remove('show');
}

function markAllAsRead() {
    const recentComplaints = [...complaints]
        .sort((a, b) => new Date(b.dateSubmitted) - new Date(a.dateSubmitted))
        .slice(0, 5);
    
    readNotifications = [...new Set([...readNotifications, ...recentComplaints.map(c => c.id)])];
    localStorage.setItem('readNotifications', JSON.stringify(readNotifications));
    
    updateNotifications();
}

function viewComplaintFromNotification(complaintId) {
    // Mark as read
    if (!readNotifications.includes(complaintId)) {
        readNotifications.push(complaintId);
        localStorage.setItem('readNotifications', JSON.stringify(readNotifications));
        updateNotifications();
    }
    
    // Close notification dropdown
    closeNotifications();
    
    // View complaint details
    viewComplaintDetails(complaintId);
}

function getTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) {
        return 'Just now';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `${minutes} ${minutes === 1 ? 'minute' : 'minutes'} ago`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `${hours} ${hours === 1 ? 'hour' : 'hours'} ago`;
    } else if (diffInSeconds < 604800) {
        const days = Math.floor(diffInSeconds / 86400);
        return `${days} ${days === 1 ? 'day' : 'days'} ago`;
    } else {
        return formatDate(dateString);
    }
}

function truncateText(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
}

// Chart Functions
function initializeChart() {
    const ctx = document.getElementById('complaintChart').getContext('2d');
    
    complaintChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Number of Complaints',
                data: [],
                backgroundColor: [
                    'rgba(255, 107, 53, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(236, 72, 153, 0.8)'
                ],
                borderColor: [
                    'rgba(255, 107, 53, 1)',
                    'rgba(59, 130, 246, 1)',
                    'rgba(16, 185, 129, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(239, 68, 68, 1)',
                    'rgba(139, 92, 246, 1)',
                    'rgba(236, 72, 153, 1)'
                ],
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 600
                    },
                    bodyFont: {
                        size: 13
                    },
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed.y / total) * 100).toFixed(1);
                            return `${context.parsed.y} complaints (${percentage}%)`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 12,
                            weight: 500
                        }
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    
    updateChart(complaints);
    initializeSubcategoryChart();
}

let subcategoryChart = null;

function initializeSubcategoryChart() {
    const ctx = document.getElementById('subcategoryChart').getContext('2d');
    subcategoryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Complaints',
                data: [],
                backgroundColor: [],
                borderColor: [],
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            indexAxis: 'y', // Horizontal bar chart
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 600
                    },
                    bodyFont: {
                        size: 13
                    },
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    callbacks: {
                        title: function(context) {
                            return context[0].label;
                        },
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed.x / total) * 100).toFixed(1);
                            return `${context.parsed.x} complaints (${percentage}%)`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                y: {
                    ticks: {
                        font: {
                            size: 11,
                            weight: 500
                        },
                        autoSkip: false
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

function updateChart(complaintsToChart) {
    // Count complaints by category
    const categoryCounts = {};
    complaintsToChart.forEach(complaint => {
        const category = complaint.category;
        categoryCounts[category] = (categoryCounts[category] || 0) + 1;
    });
    
    // Sort by count (descending)
    const sortedCategories = Object.entries(categoryCounts)
        .sort((a, b) => b[1] - a[1])
        .slice(0, 7); // Show top 7 categories
    
    const labels = sortedCategories.map(([category]) => category);
    const data = sortedCategories.map(([, count]) => count);
    
    // Update chart data
    complaintChart.data.labels = labels;
    complaintChart.data.datasets[0].data = data;
    complaintChart.update();
}

function updateChartWithFilters() {
    const period = document.getElementById('chartPeriod').value;
    const regionFilter = document.getElementById('chartRegionFilter').value;
    const provinceFilter = document.getElementById('chartProvinceFilter').value;
    const cityFilter = document.getElementById('chartCityFilter').value;
    const barangayFilter = document.getElementById('chartBarangayFilter').value;
    
    // Filter complaints by period
    let filteredComplaints = filterComplaintsByPeriod(complaints, period);
    
    // Filter by location
    filteredComplaints = filteredComplaints.filter(complaint => {
        const matchesRegion = regionFilter === 'all' || complaint.region === regionFilter;
        const matchesProvince = provinceFilter === 'all' || complaint.province === provinceFilter;
        const matchesCity = cityFilter === 'all' || complaint.city === cityFilter;
        const matchesBarangay = barangayFilter === 'all' || complaint.barangay === barangayFilter;
        
        return matchesRegion && matchesProvince && matchesCity && matchesBarangay;
    });
    
    updateChart(filteredComplaints);
    updateSubcategoryChart(filteredComplaints);
}

function updateSubcategoryChart(complaintsToChart) {
    // Count complaints by subcategory and track category
    const subcategoryData = {};
    complaintsToChart.forEach(complaint => {
        const key = `${complaint.category} - ${complaint.subcategory}`;
        if (!subcategoryData[key]) {
            subcategoryData[key] = {
                count: 0,
                category: complaint.category
            };
        }
        subcategoryData[key].count++;
    });
    
    // Sort by count (descending) and take top 15
    const sortedSubcategories = Object.entries(subcategoryData)
        .sort((a, b) => b[1].count - a[1].count)
        .slice(0, 15);
    
    const labels = sortedSubcategories.map(([subcategory]) => subcategory);
    const data = sortedSubcategories.map(([, info]) => info.count);
    const backgroundColors = sortedSubcategories.map(([, info]) => getCategoryColor(info.category));
    const borderColors = sortedSubcategories.map(([, info]) => getCategoryColor(info.category));
    
    // Update chart data
    subcategoryChart.data.labels = labels;
    subcategoryChart.data.datasets[0].data = data;
    subcategoryChart.data.datasets[0].backgroundColor = backgroundColors;
    subcategoryChart.data.datasets[0].borderColor = borderColors;
    subcategoryChart.update();
}

function toggleSubcategoryChart() {
    const section = document.getElementById('subcategoryChartSection');
    const button = document.getElementById('toggleSubcategoryBtn');
    
    if (section.style.display === 'none') {
        section.style.display = 'block';
        button.classList.add('active');
        button.innerHTML = `
            <svg class="toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
            Hide Detailed Breakdown
        `;
        // Update subcategory chart with current filters
        updateChartWithFilters();
    } else {
        section.style.display = 'none';
        button.classList.remove('active');
        button.innerHTML = `
            <svg class="toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
            View Detailed Breakdown by Subcategory
        `;
    }
}

function filterComplaintsByPeriod(complaints, period) {
    if (period === 'all') {
        return complaints;
    }
    
    const now = new Date();
    const filtered = complaints.filter(complaint => {
        const complaintDate = new Date(complaint.dateSubmitted);
        
        if (period === 'week') {
            const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
            return complaintDate >= weekAgo;
        } else if (period === 'month') {
            const monthAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
            return complaintDate >= monthAgo;
        } else if (period === 'year') {
            const yearAgo = new Date(now.getTime() - 365 * 24 * 60 * 60 * 1000);
            return complaintDate >= yearAgo;
        }
        
        return true;
    });
    
    return filtered;
}

// Chart location filter functions
function populateChartLocationFilters() {
    // Get unique regions
    const regions = [...new Set(complaints.map(c => c.region))].sort();
    const regionSelect = document.getElementById('chartRegionFilter');
    
    regions.forEach(region => {
        const option = document.createElement('option');
        option.value = region;
        option.textContent = region;
        regionSelect.appendChild(option);
    });
}

function handleChartRegionChange() {
    const regionFilter = document.getElementById('chartRegionFilter').value;
    const provinceSelect = document.getElementById('chartProvinceFilter');
    const citySelect = document.getElementById('chartCityFilter');
    const barangaySelect = document.getElementById('chartBarangayFilter');
    
    // Reset dependent dropdowns
    provinceSelect.innerHTML = '<option value="all">All Provinces</option>';
    citySelect.innerHTML = '<option value="all">All Cities</option>';
    barangaySelect.innerHTML = '<option value="all">All Barangays</option>';
    
    citySelect.disabled = true;
    barangaySelect.disabled = true;
    
    if (regionFilter === 'all') {
        provinceSelect.disabled = true;
        updateChartWithFilters();
        return;
    }
    
    // Get unique provinces for selected region
    const provinces = [...new Set(
        complaints
            .filter(c => c.region === regionFilter)
            .map(c => c.province)
    )].sort();
    
    provinces.forEach(province => {
        const option = document.createElement('option');
        option.value = province;
        option.textContent = province;
        provinceSelect.appendChild(option);
    });
    
    provinceSelect.disabled = false;
    updateChartWithFilters();
}

function handleChartProvinceChange() {
    const regionFilter = document.getElementById('chartRegionFilter').value;
    const provinceFilter = document.getElementById('chartProvinceFilter').value;
    const citySelect = document.getElementById('chartCityFilter');
    const barangaySelect = document.getElementById('chartBarangayFilter');
    
    // Reset dependent dropdowns
    citySelect.innerHTML = '<option value="all">All Cities</option>';
    barangaySelect.innerHTML = '<option value="all">All Barangays</option>';
    
    barangaySelect.disabled = true;
    
    if (provinceFilter === 'all') {
        citySelect.disabled = true;
        updateChartWithFilters();
        return;
    }
    
    // Get unique cities for selected province
    const cities = [...new Set(
        complaints
            .filter(c => c.region === regionFilter && c.province === provinceFilter)
            .map(c => c.city)
    )].sort();
    
    cities.forEach(city => {
        const option = document.createElement('option');
        option.value = city;
        option.textContent = city;
        citySelect.appendChild(option);
    });
    
    citySelect.disabled = false;
    updateChartWithFilters();
}

function handleChartCityChange() {
    const regionFilter = document.getElementById('chartRegionFilter').value;
    const provinceFilter = document.getElementById('chartProvinceFilter').value;
    const cityFilter = document.getElementById('chartCityFilter').value;
    const barangaySelect = document.getElementById('chartBarangayFilter');
    
    // Reset barangay dropdown
    barangaySelect.innerHTML = '<option value="all">All Barangays</option>';
    
    if (cityFilter === 'all') {
        barangaySelect.disabled = true;
        updateChartWithFilters();
        return;
    }
    
    // Get unique barangays for selected city
    const barangays = [...new Set(
        complaints
            .filter(c => c.region === regionFilter && c.province === provinceFilter && c.city === cityFilter)
            .map(c => c.barangay)
    )].sort();
    
    barangays.forEach(barangay => {
        const option = document.createElement('option');
        option.value = barangay;
        option.textContent = barangay;
        barangaySelect.appendChild(option);
    });
    
    barangaySelect.disabled = false;
    updateChartWithFilters();
}

function clearChartFilters() {
    // Reset period filter
    document.getElementById('chartPeriod').value = 'all';
    
    // Reset location filters
    document.getElementById('chartRegionFilter').value = 'all';
    document.getElementById('chartProvinceFilter').innerHTML = '<option value="all">All Provinces</option>';
    document.getElementById('chartProvinceFilter').disabled = true;
    document.getElementById('chartCityFilter').innerHTML = '<option value="all">All Cities</option>';
    document.getElementById('chartCityFilter').disabled = true;
    document.getElementById('chartBarangayFilter').innerHTML = '<option value="all">All Barangays</option>';
    document.getElementById('chartBarangayFilter').disabled = true;
    
    // Update chart
    updateChartWithFilters();
}