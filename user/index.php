<?php




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - eReklamo</title>
    <link rel="stylesheet" href="user_dashboard_design.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <img class="ereklamo-logo" src="../logos/eReklamo_White.png" />
                </div>
                <nav class="nav">
                    <span class="user-email" id="userEmail">user@example.com</span>
                    <a href="../index" class="btn btn-outline-white">Home</a>
                    <a href="../sign_in" class="btn btn-outline-white">Sign Out</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h2>My Complaints Dashboard</h2>
                <p>Track and manage all your submitted complaints in one place</p>
            </div>

            <!-- Actions Bar -->
            <div class="actions-bar">
                <div class="search-container">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input 
                        type="text" 
                        id="searchInput" 
                        placeholder="Search by tracking number, category, or description..."
                        class="search-input"
                        onkeyup="filterComplaints()"
                    />
                </div>
                <div class="filter-group">
                    <select id="timeFilter" class="filter-select" onchange="filterComplaints()">
                        <option value="all">All Time</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                    </select>
                    <select id="locationFilter" class="filter-select" onchange="filterComplaints()">
                        <option value="all">All Locations</option>
                        <!-- Locations will be populated dynamically -->
                    </select>
                    <select id="statusFilter" class="filter-select" onchange="filterComplaints()">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="in-progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <a href="../add_complaint" class="btn btn-primary">
                    <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="12" y1="18" x2="12" y2="12"></line>
                        <line x1="9" y1="15" x2="15" y2="15"></line>
                    </svg>
                    New Complaint
                </a>
            </div>

            <!-- Complaints List -->
            <div id="complaintsList" class="complaints-list">
                <!-- Complaints will be loaded here by JavaScript -->
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="empty-state" style="display: none;">
                <svg class="empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                <h3>No complaints found</h3>
                <p id="emptyMessage">You haven't submitted any complaints yet</p>
                <a href="../add_complaint" class="btn btn-primary">Submit Your First Complaint</a>
            </div>
        </div>
    </main>

    <script src="user_dashboard.js"></script>
</body>
</html>