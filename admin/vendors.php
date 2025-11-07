<?php $pageTitle = 'Manage Vendors'; ?>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>


<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Dashboard Content -->
    <main class="dashboard-content">

        <!-- Metrics Cards -->
        <div class="metrics-row" id="metricsRow">
            <div class="metric-card" id="metricCardTotalVendors">
                <div class="metric-icon vendors">
                    <i class="bi bi-shop"></i>
                </div>
                <div class="metric-content">
                    <h3 id="totalVendors">0</h3>
                    <p class="metric-label">TOTAL VENDORS</p>
                    <span class="metric-status text-success">All registered vendors</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardActiveVendors">
                <div class="metric-icon active-vendors">
                    <i class="bi bi-shop-window"></i>
                </div>
                <div class="metric-content">
                    <h3 id="activeVendors">0</h3>
                    <p class="metric-label">ACTIVE VENDORS</p>
                    <span class="metric-status text-success">Currently active</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardPendingVendors">
                <div class="metric-icon pending-vendors">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="metric-content">
                    <h3 id="pendingVendors">0</h3>
                    <p class="metric-label">PENDING VENDORS</p>
                    <span class="metric-status text-warning">Awaiting approval</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardVerifiedVendors">
                <div class="metric-icon verified-vendors">
                    <i class="bi bi-patch-check"></i>
                </div>
                <div class="metric-content">
                    <h3 id="verifiedVendors">0</h3>
                    <p class="metric-label">VERIFIED VENDORS</p>
                    <span class="metric-status text-info">Verified and trusted</span>
                </div>
            </div>
        </div>

        <!-- Users List Section -->
        <div class="users-section">

            <div class="section-header">
                <h3>Manage Vendors</h3>
                <p>View and manage all registered vendors</p>

            </div>

            <!-- Search and Filter Section -->
            <div class="search-filter-section">
                <div class="row g-3">
                    <div class="col-lg-12 col-md-12 col-12">
                        <div class="search-box">
                            <label>Search</label>
                            <div class="search-input-wrapper">
                                <i class="bi bi-search search-icon"></i>
                                <input type="text" class="search-input"
                                    placeholder="Search vendors by name, phone, platform, or location...">
                            </div>
                        </div>
                    </div>


                </div>
            </div>

            <div class="users-grid" id="vendorsGrid">
                <!-- Dynamic vendors will be loaded here -->
            </div>
        </div>

    </main>
</div>

<script src="assets/js/vendors.js"></script>
<?php include 'footer.php'; ?>