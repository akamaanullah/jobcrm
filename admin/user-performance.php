<?php $pageTitle = 'User Performance'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Dashboard Content -->
    <main class="dashboard-content">
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-content">
                <h2>User Performance Tracking</h2>
                <p>Track and analyze user performance, job completion, and revenue metrics</p>
            </div>
            <!-- <div class="welcome-actions">
                <button class="btn btn-back" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print Report
                </button>
            </div> -->
        </div>

        <!-- Summary Metrics Cards -->
        <div class="metrics-row" id="summaryMetricsRow">
            <div class="metric-card">
                <div class="metric-icon users">
                    <i class="bi bi-people"></i>
                </div>
                <div class="metric-content">
                    <h3 id="totalUsersCount">0</h3>
                    <p class="metric-label">TOTAL USERS</p>
                    <span class="metric-status text-success">Active users</span>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon jobs">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="metric-content">
                    <h3 id="totalCompletedJobsCount">0</h3>
                    <p class="metric-label">COMPLETED JOBS</p>
                    <span class="metric-status text-success">Total completed</span>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon revenue">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="metric-content">
                    <h3 id="totalRevenueCount">$0.00</h3>
                    <p class="metric-label">TOTAL REVENUE</p>
                    <span class="metric-status text-info">From invoices</span>
                </div>
            </div>

            <div class="metric-card">
                <div class="metric-icon time">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="metric-content">
                    <h3 id="avgCompletionTime">0</h3>
                    <p class="metric-label">AVG COMPLETION</p>
                    <span class="metric-status text-warning">Days per job</span>
                </div>
            </div>
        </div>

        <!-- Performance Table Section -->
        <div class="users-section">
            <div class="section-header">
                <h3>User Performance Details</h3>
                <p class="text-muted mb-0">Detailed performance metrics for each user</p>
            </div>

            <!-- Search and Filter Section -->
            <div class="search-filter-section">
                <div class="row g-3">
                    <div class="col-lg-6 col-md-7 col-12">
                        <div class="search-box">
                            <label>Search</label>
                            <div class="search-input-wrapper">
                                <i class="bi bi-search search-icon"></i>
                                <input type="text" class="search-input" id="userSearchInput" placeholder="Search by name or username...">
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-5 col-12">
                        <div class="filter-dropdowns">
                            <div class="dropdown-wrapper">
                                <label>Sort By</label>
                                <select class="filter-dropdown" id="sortByFilter">
                                    <option value="completed_desc">Most Completed</option>
                                    <option value="revenue_desc">Highest Revenue</option>
                                    <option value="jobs_desc">Most Jobs</option>
                                    <option value="sla_desc">Best SLA Compliance</option>
                                    <option value="name_asc">Name A-Z</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-12 col-12">
                        <div class="filter-dropdowns">
                            <div class="dropdown-wrapper">
                                <label>Performance Grade</label>
                                <select class="filter-dropdown" id="gradeFilter">
                                    <option value="">All Grades</option>
                                    <option value="A+">A+ (Excellent)</option>
                                    <option value="A">A (Very Good)</option>
                                    <option value="B">B (Good)</option>
                                    <option value="C">C (Average)</option>
                                    <option value="D">D (Needs Improvement)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Table -->
            <div class="table-responsive">
                <table class="table performance-table">
                    <thead>
                        <tr>
                            <th>USER</th>
                            <th>TOTAL JOBS</th>
                            <th>COMPLETED</th>
                            <th>IN PROGRESS</th>
                            <th>PENDING</th>
                            <th>VENDORS</th>
                            <th>TOTAL REVENUE</th>
                            <th>INVOICES</th>
                            <th>AVG TIME</th>
                            <th>SLA RATE</th>
                            <th>GRADE</th>
                        </tr>
                    </thead>
                    <tbody id="performanceTableBody">
                        <!-- Dynamic performance data will be loaded here -->
                        <tr>
                            <td colspan="11" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2">Loading performance data...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include 'footer.php'; ?>

<style>
/* Performance Table Styling */
.performance-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.performance-table thead {
    background: linear-gradient(135deg, var(--accent-blue) 0%, #5a7ce8 100%);
    color: white;
}

.performance-table thead th {
    padding: 1rem;
    font-weight: 600;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    text-align: center;
    border: none;
}

.performance-table thead th:first-child {
    border-radius: var(--radius-md) 0 0 0;
    text-align: left;
    padding-left: 1.5rem;
}

.performance-table thead th:last-child {
    border-radius: 0 var(--radius-md) 0 0;
}

.performance-table tbody tr {
    background: white;
    border-bottom: 1px solid var(--border-color);
    transition: all 0.3s ease;
}

.performance-table tbody tr:hover {
    background: var(--bg-primary);
}

.performance-table tbody td {
    padding: 1rem;
    text-align: center;
    vertical-align: middle;
}

.performance-table tbody td:first-child {
    text-align: left;
    padding-left: 1.5rem;
}

.user-info-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent-blue) 0%, #5a7ce8 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.user-details {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.user-name {
    font-weight: 600;
    color: var(--text-dark);
    font-size: 0.95rem;
}

.user-email {
    font-size: 0.75rem;
    color: var(--text-medium);
}

.metric-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
    min-width: 30px;
    text-align: center;
}

.metric-badge.jobs {
    background: #e0f2fe;
    color: #0284c7;
}

.metric-badge.completed {
    background: #d1fae5;
    color: #059669;
}

.metric-badge.progress {
    background: #fef3c7;
    color: #d97706;
    border-radius: 20px;
    display: inline-block;
    padding: 0.25rem 0.75rem;
    font-weight: 600;
    font-size: 0.85rem;
    min-width: 30px;
    min-height: 30px;
    text-align: center;
}

.metric-badge.pending {
    background: #fee2e2;
    color: #dc2626;
}

.revenue-amount {
    font-weight: 700;
    color: var(--success-color);
    font-size: 1rem;
}

.performance-grade {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    font-weight: 700;
    font-size: 1.1rem;
    color: white;
}

.grade-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.grade-info {
    background: linear-gradient(135deg, var(--accent-blue) 0%, #5a7ce8 100%);
}

.grade-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.grade-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.sla-rate {
    font-weight: 600;
}

.sla-rate.high {
    color: var(--success-color);
}

.sla-rate.medium {
    color: var(--warning-color);
}

.sla-rate.low {
    color: var(--danger-color);
}

/* Empty State */
.empty-performance-state {
    text-align: center;
    padding: 3rem;
}

.empty-performance-state i {
    font-size: 4rem;
    color: var(--text-medium);
    margin-bottom: 1rem;
}

.empty-performance-state h4 {
    color: var(--text-dark);
    margin-bottom: 0.5rem;
}

.empty-performance-state p {
    color: var(--text-medium);
}

/* Responsive */
@media (max-width: 768px) {
    .performance-table {
        font-size: 0.85rem;
    }
    
    .performance-table thead th,
    .performance-table tbody td {
        padding: 0.75rem 0.5rem;
    }
    
    .user-info-cell {
        flex-direction: column;
        gap: 0.5rem;
    }
}
</style>

<script src="assets/js/user-performance.js"></script>

