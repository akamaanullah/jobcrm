// User Vendors Management
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DOM elements
    const searchInput = document.getElementById('vendorSearchInput');
    const sortBy = document.getElementById('vendorSortBy');
    const vendorsGrid = document.getElementById('vendorsGrid');
    const loadingElement = document.getElementById('vendorsLoading');
    
    // Load initial data
    loadVendors();
    
    // Add event listeners
    if (searchInput) {
        searchInput.addEventListener('input', debounce(loadVendors, 300));
    }
    
    if (sortBy) {
        sortBy.addEventListener('change', loadVendors);
    }
});

// Load vendors from API
async function loadVendors() {
    try {
        const searchInput = document.getElementById('vendorSearchInput');
        const sortBy = document.getElementById('vendorSortBy');
        
        const params = new URLSearchParams();
        
        if (searchInput && searchInput.value.trim()) {
            params.append('search', searchInput.value.trim());
        }
        
        if (sortBy && sortBy.value) {
            params.append('sort_by', sortBy.value);
        }
        
        const response = await fetch(`assets/api/get_vendors.php?${params.toString()}`);
        const result = await response.json();
        
        if (result.success) {
            displayVendors(result.data);
        } else {
            showVendorsError(result.message);
        }
    } catch (error) {
        console.error('Load Vendors Error:', error);
        showVendorsError('Failed to load vendors');
    }
}

// Display vendors in grid
function displayVendors(vendors) {
    const vendorsGrid = document.getElementById('vendorsGrid');
    const loadingElement = document.getElementById('vendorsLoading');
    
    if (loadingElement) {
        loadingElement.remove();
    }
    
    if (!vendors || vendors.length === 0) {
        vendorsGrid.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-shop text-muted" style="font-size: 4rem;"></i>
                <h4 class="text-muted mt-3">No Vendors Found</h4>
                <p class="text-muted">No vendors match your current search criteria.</p>
            </div>
        `;
        return;
    }
    
    const vendorsHTML = vendors.map(vendor => createVendorCard(vendor)).join('');
    vendorsGrid.innerHTML = vendorsHTML;
}

// Create vendor card HTML
function createVendorCard(vendor) {
    const initials = vendor.avatar || getInitials(vendor.vendor_name);
    const specialty = vendor.specialty || 'General';
    
    // Handle quote amount display for grouped vendors
    let quoteDisplay = '';
    if (vendor.min_amount === vendor.max_amount) {
        quoteDisplay = vendor.min_amount > 0 ? 
            `<span class="badge badge-success">$${vendor.min_amount}</span>` : 
            `<span class="badge badge-info">Free Quote</span>`;
    } else {
        quoteDisplay = vendor.min_amount > 0 ? 
            `<span class="badge badge-warning">$${vendor.min_amount} - $${vendor.max_amount}</span>` : 
            `<span class="badge badge-info">Free - $${vendor.max_amount}</span>`;
    }
    
    return `
        <div class="user-card grouped-vendor-card">
            <div class="user-avatar">
                <span>${initials}</span>
                ${vendor.is_grouped ? '<span class="group-badge" title="Grouped Vendor">G</span>' : ''}
            </div>
            <div class="user-info">
                <div class="user-header">
                    <h4>${vendor.vendor_name}</h4>
                    <span class="badge badge-${getStatusClass(vendor.status)}">${getStatusText(vendor.status)}</span>
                </div>
                
                <div class="user-badges">
                    <span class="badge badge-primary">${specialty}</span>
                    ${quoteDisplay}
                </div>
                
                <div class="user-details">
                    <p><i class="bi bi-telephone"></i> ${vendor.phone || 'N/A'}</p>
                    <p><i class="bi bi-briefcase"></i> Jobs: ${vendor.job_names || 'N/A'}</p>
                    <p><i class="bi bi-calendar"></i> First: ${formatDate(vendor.first_assigned)} | Last: ${formatDate(vendor.last_assigned)}</p>
                </div>
                
                <div class="user-stats">
                    <div class="stat-item">
                        <span class="stat-label">Total Jobs</span>
                        <span class="stat-value">${vendor.total_jobs_assigned || 0}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Completed</span>
                        <span class="stat-value">${vendor.completed_jobs || 0}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Paid Jobs</span>
                        <span class="stat-value">${vendor.paid_jobs || 0}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Total Earned</span>
                        <span class="stat-value">$${vendor.total_amount_earned || 0}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Success Rate</span>
                        <span class="stat-value">${vendor.success_rate || 0}%</span>
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


// Show error message
function showVendorsError(message) {
    const vendorsGrid = document.getElementById('vendorsGrid');
    const loadingElement = document.getElementById('vendorsLoading');
    
    if (loadingElement) {
        loadingElement.remove();
    }
    
    vendorsGrid.innerHTML = `
        <div class="text-center py-5">
            <i class="bi bi-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
            <h4 class="text-danger mt-3">Error Loading Vendors</h4>
            <p class="text-muted">${message}</p>
            <button class="btn btn-primary" onclick="loadVendors()">
                <i class="bi bi-arrow-clockwise"></i> Retry
            </button>
        </div>
    `;
}

// Helper functions
function getInitials(name) {
    if (!name) return 'N/A';
    const words = name.trim().split(' ');
    if (words.length === 1) {
        return words[0].substring(0, 2).toUpperCase();
    }
    return (words[0].charAt(0) + words[words.length - 1].charAt(0)).toUpperCase();
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

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function formatDateTime(datetime) {
    if (!datetime) return 'N/A';
    const date = new Date(datetime);
    return date.toLocaleString();
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
