<?php




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - eReklamo</title>
    <link rel="stylesheet" href="admin_dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <svg class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <div>
                        <h1 class="logo-text">eReklamo Admin</h1>
                        <p class="logo-subtitle">Complaint Management Dashboard</p>
                    </div>
                </div>
                <div class="header-actions">
                    <div class="notification-container">
                        <button class="notification-btn" id="notificationBtn" onclick="toggleNotifications()">
                            <svg class="notification-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span class="notification-badge" id="notificationBadge">0</span>
                        </button>
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="notification-header">
                                <h3>Recent Complaints</h3>
                                <button class="notification-clear" onclick="markAllAsRead()">
                                    Mark all as read
                                </button>
                            </div>
                            <div class="notification-list" id="notificationList">
                                <!-- Notifications will be inserted here -->
                            </div>
                            <div class="notification-footer">
                                <button onclick="closeNotifications()" class="view-all-btn">Close</button>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-outline-white" onclick="window.location.href='sign_in.html'">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        Logout
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Page Title -->
            <div class="page-header">
                <h2>Complaint Management</h2>
                <p>View, manage, and update the status of all complaints</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-total">
                    <div class="stat-label">Total Complaints</div>
                    <div class="stat-value" id="totalCount">6</div>
                </div>
                <div class="stat-card stat-pending">
                    <div class="stat-label">Pending Review</div>
                    <div class="stat-value" id="pendingCount">2</div>
                </div>
                <div class="stat-card stat-progress">
                    <div class="stat-label">In Progress</div>
                    <div class="stat-value" id="progressCount">1</div>
                </div>
                <div class="stat-card stat-resolved">
                    <div class="stat-label">Resolved</div>
                    <div class="stat-value" id="resolvedCount">2</div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="chart-section">
                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <h3>Most Common Complaint Types</h3>
                            <p class="chart-subtitle">Distribution of complaints by category</p>
                        </div>
                    </div>
                    
                    <!-- Chart Filters -->
                    <div class="chart-filters">
                        <div class="chart-filter-group">
                            <label class="chart-filter-label">Time Period</label>
                            <select id="chartPeriod" class="chart-filter-select">
                                <option value="all">All Time</option>
                                <option value="year">This Year</option>
                                <option value="month">This Month</option>
                                <option value="week">This Week</option>
                            </select>
                        </div>
                        
                        <div class="chart-filter-group">
                            <label class="chart-filter-label">Region</label>
                            <select id="chartRegionFilter" class="chart-filter-select">
                                <option value="all">All Regions</option>
                            </select>
                        </div>
                        
                        <div class="chart-filter-group">
                            <label class="chart-filter-label">Province</label>
                            <select id="chartProvinceFilter" class="chart-filter-select" disabled>
                                <option value="all">All Provinces</option>
                            </select>
                        </div>
                        
                        <div class="chart-filter-group">
                            <label class="chart-filter-label">City/Municipality</label>
                            <select id="chartCityFilter" class="chart-filter-select" disabled>
                                <option value="all">All Cities</option>
                            </select>
                        </div>
                        
                        <div class="chart-filter-group">
                            <label class="chart-filter-label">Barangay</label>
                            <select id="chartBarangayFilter" class="chart-filter-select" disabled>
                                <option value="all">All Barangays</option>
                            </select>
                        </div>
                        
                        <div class="chart-filter-group">
                            <label class="chart-filter-label" style="opacity: 0; pointer-events: none;">Actions</label>
                            <button id="clearChartFiltersBtn" class="btn-clear-chart-filters" onclick="clearChartFilters()">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                                Clear
                            </button>
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <canvas id="complaintChart"></canvas>
                    </div>
                    
                    <div class="chart-toggle-section">
                        <button id="toggleSubcategoryBtn" class="btn-toggle-subcategory" onclick="toggleSubcategoryChart()">
                            <svg class="toggle-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                            View Detailed Breakdown by Subcategory
                        </button>
                    </div>
                    
                    <div id="subcategoryChartSection" class="subcategory-chart-section" style="display: none;">
                        <div class="subcategory-header">
                            <h4>Complaint Subcategories Breakdown</h4>
                            <p class="subcategory-subtitle">Detailed view of complaints by subcategory</p>
                        </div>
                        <div class="chart-container chart-container-subcategory">
                            <canvas id="subcategoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-section">
                <div class="search-container">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="Search by tracking number, description, or location..."
                        class="search-input"
                    />
                </div>
                <select id="statusFilter" class="filter-select">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="in-progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <!-- Complaints Table -->
            <div class="table-container">
                <table class="complaints-table">
                    <thead>
                        <tr>
                            <th>Tracking #</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Location</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="complaintsTableBody">
                        <!-- Rows will be inserted by JavaScript -->
                    </tbody>
                    <tbody id="emptyState" style="display: none;">
                        <tr>
                            <td colspan="7" class="empty-state">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                                <p>No complaints found matching your criteria.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <svg class="modal-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                    Complaint Details
                </h3>
                <button class="modal-close" onclick="closeDetailsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p class="modal-tracking">Tracking Number: <span id="modalTrackingNumber"></span></p>
                
                <div class="modal-grid">
                    <div class="modal-field">
                        <label>Category</label>
                        <p id="modalCategory"></p>
                    </div>
                    <div class="modal-field">
                        <label>Subcategory</label>
                        <p id="modalSubcategory"></p>
                    </div>
                </div>

                <div class="modal-field">
                    <label>Description</label>
                    <p class="description-box" id="modalDescription"></p>
                </div>

                <div class="modal-field">
                    <label>Location</label>
                    <p id="modalLocation"></p>
                </div>

                <div class="modal-grid">
                    <div class="modal-field">
                        <label>Submitted By</label>
                        <p id="modalSubmittedBy"></p>
                    </div>
                    <div class="modal-field">
                        <label>Date Submitted</label>
                        <p id="modalDateSubmitted"></p>
                    </div>
                </div>

                <div class="modal-field">
                    <label>Current Status</label>
                    <select 
                        id="modalStatusSelect" 
                        class="status-select"
                        onchange="handleModalStatusChange(this.value)"
                    >
                        <option value="pending">Pending</option>
                        <option value="in-progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div id="modalDeleteSection" class="modal-delete-section">
                    <button class="btn btn-delete" onclick="confirmDelete(selectedComplaint.id)">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="m19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        Delete Resolved Complaint
                    </button>
                    <p class="delete-hint">Delete this complaint to free up storage space</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h3>Delete Resolved Complaint?</h3>
                <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete complaint <strong id="deleteTrackingNumber"></strong>?</p>
                <p class="warning-text">This action cannot be undone and will permanently remove this complaint from the system.</p>
                
                <div class="modal-actions">
                    <button class="btn btn-outline" onclick="closeDeleteModal()">Cancel</button>
                    <button class="btn btn-delete" onclick="executeDelete()">
                        <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="m19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        Delete Complaint
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="admin_dashboard.js"></script>
</body>
</html>