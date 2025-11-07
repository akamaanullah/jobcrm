document.addEventListener('DOMContentLoaded', function() {
    // Global variables
    let currentPage = 1;
    let currentSearch = '';
    let currentSpecialty = '';
    let isLoading = false;

    // Initialize
    loadVendors();
    setupEventListeners();

    // Setup event listeners
    function setupEventListeners() {
        // Search input
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    currentSearch = this.value.trim();
                    currentPage = 1;
                    loadVendors();
                }, 500);
            });
        }


        // Specialty filter removed since column doesn't exist in database
        // const specialtyFilter = document.querySelectorAll('.filter-dropdown')[1];
        // if (specialtyFilter) {
        //     specialtyFilter.addEventListener('change', function() {
        //         currentSpecialty = this.value;
        //         currentPage = 1;
        //         loadVendors();
        //     });
        // }
    }

    // Load vendors
    async function loadVendors() {
        if (isLoading) return;
        
        isLoading = true;
        showLoading();

        try {
            const params = new URLSearchParams({
                page: currentPage,
                limit: 20,
                search: currentSearch
                // status and specialty removed since columns don't exist
            });

            const response = await fetch(`assets/api/get_vendors.php?${params}`);
            const result = await response.json();

            if (result.success) {
                updateStats(result.data.stats);
                renderVendors(result.data.vendors);
                updatePagination(result.data.pagination);
            } else {
                showError(result.message || 'Failed to load vendors');
            }
        } catch (error) {
            console.error('Error loading vendors:', error);
            showError('An error occurred while loading vendors');
        } finally {
            isLoading = false;
            hideLoading();
        }
    }

    // Update statistics
    function updateStats(stats) {
        const totalVendors = document.getElementById('totalVendors');
        const activeVendors = document.getElementById('activeVendors');
        const pendingVendors = document.getElementById('pendingVendors');
        const verifiedVendors = document.getElementById('verifiedVendors');

        if (totalVendors) totalVendors.textContent = stats.total_vendors || 0;
        if (activeVendors) activeVendors.textContent = stats.active_vendors || 0;
        if (pendingVendors) pendingVendors.textContent = stats.pending_vendors || 0;
        if (verifiedVendors) verifiedVendors.textContent = stats.verified_vendors || 0;
    }

    // Render vendors
    function renderVendors(vendors) {
        const vendorsGrid = document.querySelector('.users-grid');
        if (!vendorsGrid) return;

        if (vendors.length === 0) {
            vendorsGrid.innerHTML = `
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3">No Vendors Found</h5>
                        <p class="text-muted">No vendors match your search criteria.</p>
                    </div>
                </div>
            `;
            return;
        }

        let html = '';
        vendors.forEach(vendor => {
            html += createVendorCard(vendor);
        });

        vendorsGrid.innerHTML = html;
    }

    // Create vendor card
    function createVendorCard(vendor) {
        const lastAssigned = formatDate(vendor.last_assigned);
        const firstAssigned = formatDate(vendor.first_assigned);
        
        // Handle quote amount display for grouped vendors
        let quoteDisplay = '';
        if (vendor.min_quote_amount === vendor.max_quote_amount) {
            quoteDisplay = vendor.min_quote_amount > 0 ? `$${vendor.min_quote_amount}` : 'Free';
        } else {
            quoteDisplay = vendor.min_quote_amount > 0 ? 
                `$${vendor.min_quote_amount} - $${vendor.max_quote_amount}` : 
                'Free - $' + vendor.max_quote_amount;
        }

        return `
            <div class="user-card grouped-vendor-card">
                <div class="user-avatar">
                    <span>${vendor.avatar}</span>
                    ${vendor.is_grouped ? '<span class="group-badge" title="Grouped Vendor">G</span>' : ''}
                </div>
                <div class="user-info">
                    <div class="user-header">
                        <h4>${vendor.name}</h4>
                        <span class="badge badge-${getStatusClass(vendor.status)}">${getStatusText(vendor.status)}</span>
                    </div>
                    
                    <div class="user-badges">
                        <span class="badge badge-platform">${vendor.vendor_platform}</span>
                    </div>
                    
                    <div class="user-details">
                        <p><i class="bi bi-telephone"></i> ${vendor.phone || 'Not provided'}</p>
                        <p><i class="bi bi-geo-alt"></i> Location: ${vendor.location || 'Not specified'}</p>
                        <p><i class="bi bi-briefcase"></i> Jobs: ${vendor.job_names}</p>
                        <p><i class="bi bi-calendar"></i> First: ${firstAssigned} | Last: ${lastAssigned}</p>
                        <p><i class="bi bi-currency-dollar"></i> Quote: ${quoteDisplay}</p>
                        <p><i class="bi bi-globe"></i> Platform: ${vendor.vendor_platform}</p>
                    </div>
                    
                    <div class="user-stats">
                        <div class="stat-item">
                            <span class="stat-label">Total Jobs</span>
                            <span class="stat-value">${vendor.total_jobs_assigned}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Completed</span>
                            <span class="stat-value">${vendor.completed_jobs}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Paid Jobs</span>
                            <span class="stat-value">${vendor.paid_jobs}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Total Earned</span>
                            <span class="stat-value">$${vendor.total_amount_earned}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Success Rate</span>
                            <span class="stat-value">${vendor.success_rate}%</span>
                        </div>
                    </div>
                    
                    ${vendor.total_jobs_assigned > 3 ? `
                    <div class="vendor-jobs-summary">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Working on ${vendor.total_jobs_assigned} jobs total
                        </small>
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
    }

    // Status helper functions
    function getStatusClass(status) {
        const statusMap = {
            'added': 'success',
            'visit_requested': 'warning',
            'visit_request_rejected': 'danger',
            'final_visit_requested': 'info',
            'final_visit_request_rejected': 'danger',
            'job_completed': 'success',
            'requested_vendor_payment': 'primary',
            'payment_request_rejected': 'danger',
            'request_visit_accepted': 'success',
            'final_visit_request_accepted': 'success',
            'vendor_payment_accepted': 'success'
        };
        return statusMap[status] || 'secondary';
    }

    function getStatusText(status) {
        const statusMap = {
            'added': 'Added',
            'visit_requested': 'Visit Requested',
            'visit_request_rejected': 'Visit Rejected',
            'final_visit_requested': 'Final Visit Requested',
            'final_visit_request_rejected': 'Final Visit Rejected',
            'job_completed': 'Job Completed',
            'requested_vendor_payment': 'Payment Requested',
            'payment_request_rejected': 'Payment Rejected',
            'request_visit_accepted': 'Visit Accepted',
            'final_visit_request_accepted': 'Final Visit Accepted',
            'vendor_payment_accepted': 'Payment Accepted'
        };
        return statusMap[status] || status;
    }

    // Format date
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    // Format date and time
    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Update pagination
    function updatePagination(pagination) {
        // For now, we'll implement simple pagination
        // You can add pagination controls here if needed
        console.log('Pagination:', pagination);
    }

    // Show loading
    function showLoading() {
        const vendorsGrid = document.querySelector('.users-grid');
        if (vendorsGrid) {
            vendorsGrid.innerHTML = `
                <div class="col-12">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2">Loading vendors...</p>
                    </div>
                </div>
            `;
        }
    }

    // Hide loading
    function hideLoading() {
        // Loading is hidden when content is rendered
    }

    // Show error
    function showError(message) {
        const vendorsGrid = document.querySelector('.users-grid');
        if (vendorsGrid) {
            vendorsGrid.innerHTML = `
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                        <h5 class="text-danger mt-3">Error</h5>
                        <p class="text-muted">${message}</p>
                        <button class="btn btn-primary" onclick="loadVendors()">Try Again</button>
                    </div>
                </div>
            `;
        }
    }

    // Make loadVendors globally available
    window.loadVendors = loadVendors;
});
