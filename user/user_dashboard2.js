let complaints = [];

window.addEventListener('DOMContentLoaded', initUserDashboard);

async function initUserDashboard() {
    try {
        const response = await fetch('fetch_complaints.php', { headers: { 'Accept': 'application/json' } });
        if (!response.ok) {
            const text = await response.text();
            console.error('Fetch complaints failed:', response.status, text);
            showEmptyStateWithMessage('We could not load your complaints right now. Please try again later.');
            return;
        }

        const data = await response.json().catch(() => null);
        if (!Array.isArray(data)) {
            console.error('Invalid data format:', data);
            showEmptyStateWithMessage('We could not load your complaints right now. Please try again later.');
            return;
        }

        complaints = data;
        populateLocationFilter();
        renderComplaints(complaints);
    } catch (error) {
        console.error('Error loading complaints:', error);
        showEmptyStateWithMessage('We could not load your complaints right now. Please check your connection and try again.');
    }
}

const TRACKING_PAGE_URL = '../tracking_page';

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

function populateLocationFilter() {
    const locationFilter = document.getElementById('locationFilter');
    if (!locationFilter) return;

    const locations = [...new Set((complaints || []).map(c => c.location).filter(Boolean))].sort();

    locationFilter.innerHTML = '<option value="all">All Locations</option>';
    locations.forEach(location => {
        const option = document.createElement('option');
        option.value = location;
        option.textContent = location;
        locationFilter.appendChild(option);
    });
}

function renderComplaints(complaintsToRender = []) {
    const container = document.getElementById('complaintsList');
    const emptyState = document.getElementById('emptyState');
    if (!container || !emptyState) return;

    if (!Array.isArray(complaintsToRender) || complaintsToRender.length === 0) {
        container.style.display = 'none';
        emptyState.style.display = 'block';
        const searchTerm = document.getElementById('searchInput')?.value || '';
        const filterStatus = document.getElementById('statusFilter')?.value || 'all';
        const filterTime = document.getElementById('timeFilter')?.value || 'all';
        const filterLocation = document.getElementById('locationFilter')?.value || 'all';
        if (searchTerm || filterStatus !== 'all' || filterTime !== 'all' || filterLocation !== 'all') {
            document.getElementById('emptyMessage').textContent = 'No complaints match your current filters. Try adjusting your search or filters.';
        } else {
            document.getElementById('emptyMessage').textContent = "You haven't submitted any complaints yet";
        }
        return;
    }

    container.style.display = 'flex';
    emptyState.style.display = 'none';

    container.innerHTML = complaintsToRender.map(complaint => {
        const status = (complaint.status || '').toLowerCase();
        const label = statusLabels[status] || status;
        const icon = statusIcons[status] || '';

        return `
        <div class="complaint-card" data-status="${status}">
            <div class="complaint-header">
                <div class="complaint-icon">
                    ${icon}
                </div>
                <div class="complaint-info">
                    <div class="complaint-title">
                        <span class="tracking-number">${escapeHtml(complaint.trackingNumber || '')}</span>
                        <span class="status-badge status-${status}">
                            ${escapeHtml(label)}
                        </span>
                    </div>
                    <div class="complaint-meta">
                        ${escapeHtml(complaint.category || '')} • ${escapeHtml(complaint.subcategory || '—')}
                    </div>
                </div>
            </div>

            <p class="complaint-description">${escapeHtml(complaint.description || '')}</p>

            <div class="complaint-details">
                <div class="detail-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    ${escapeHtml(complaint.location || '')}
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
                    Updated: ${formatDate(complaint.lastUpdated || complaint.dateSubmitted)}
                </div>
            </div>

            <div class="complaint-actions">
                <button onclick="viewComplaintDetails('${escapeAttr(complaint.trackingNumber || '')}')" class="btn btn-outline">
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
        `;
    }).join('');
}

function filterComplaints() {
    const searchTerm = (document.getElementById('searchInput')?.value || '').toLowerCase();
    const filterStatus = document.getElementById('statusFilter')?.value || 'all';
    const filterTime = document.getElementById('timeFilter')?.value || 'all';
    const filterLocation = document.getElementById('locationFilter')?.value || 'all';

    const filtered = (complaints || []).filter(complaint => {
        const status = (complaint.status || '').toLowerCase();
        const matchesSearch =
            (complaint.trackingNumber || '').toLowerCase().includes(searchTerm) ||
            (complaint.category || '').toLowerCase().includes(searchTerm) ||
            (complaint.description || '').toLowerCase().includes(searchTerm);

        const matchesStatus = filterStatus === 'all' || status === filterStatus;
        const matchesLocation = filterLocation === 'all' || (complaint.location || '') === filterLocation;
        const matchesTime = filterByTime(complaint.dateSubmitted, filterTime);

        return matchesSearch && matchesStatus && matchesLocation && matchesTime;
    });

    renderComplaints(filtered);
}

function filterByTime(dateSubmitted, timeFilter) {
    if (timeFilter === 'all') return true;

    const submittedDate = new Date(dateSubmitted);
    if (isNaN(submittedDate.getTime())) return false;

    const now = new Date();

    // Set hours to 0 for accurate date comparison
    submittedDate.setHours(0, 0, 0, 0);
    now.setHours(0, 0, 0, 0);

    const daysDiff = Math.floor((now - submittedDate) / (1000 * 60 * 60 * 24));

    switch (timeFilter) {
        case 'week':  return daysDiff <= 7;
        case 'month': return daysDiff <= 30;
        case 'year':  return daysDiff <= 365;
        default:      return true;
    }
}

function viewComplaintDetails(trackingNumber) {
    try {
        sessionStorage.setItem('trackingNumber', trackingNumber);
    } catch {}
    // Build target URL. If it’s a folder (ends with /), we can append a query param.
    const url = TRACKING_PAGE_URL + (TRACKING_PAGE_URL.endsWith('/') ? `?tn=${encodeURIComponent(trackingNumber)}` : `?tn=${encodeURIComponent(trackingNumber)}`);
    window.location.href = url;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '—';
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric'
    });
}

function showEmptyStateWithMessage(msg) {
    const container = document.getElementById('complaintsList');
    const emptyState = document.getElementById('emptyState');
    if (container) container.style.display = 'none';
    if (emptyState) {
        emptyState.style.display = 'block';
        const msgEl = document.getElementById('emptyMessage');
        if (msgEl) msgEl.textContent = msg;
    }
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function escapeAttr(str) {
    // conservative escaping for HTML attribute context
    return String(str).replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}