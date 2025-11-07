// User Performance Management
document.addEventListener('DOMContentLoaded', function() {
    // Initialize performance page
    loadPerformanceData();
    initializeFilters();
});

let allPerformanceData = [];
let currentFilter = {
    search: '',
    sortBy: 'completed_desc',
    grade: ''
};

// Initialize filters
function initializeFilters() {
    const searchInput = document.getElementById('userSearchInput');
    const sortByFilter = document.getElementById('sortByFilter');
    const gradeFilter = document.getElementById('gradeFilter');
    
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentFilter.search = this.value.trim().toLowerCase();
                filterAndRenderData();
            }, 500);
        });
    }
    
    if (sortByFilter) {
        sortByFilter.addEventListener('change', function() {
            currentFilter.sortBy = this.value;
            filterAndRenderData();
        });
    }
    
    if (gradeFilter) {
        gradeFilter.addEventListener('change', function() {
            currentFilter.grade = this.value;
            filterAndRenderData();
        });
    }
}

// Load performance data from API
async function loadPerformanceData() {
    try {
        const response = await fetch('assets/api/get_user_performance.php');
        const result = await response.json();
        
        if (result.success) {
            allPerformanceData = result.data;
            updateSummaryMetrics(result.summary);
            filterAndRenderData();
        } else {
            showError(result.message || 'Failed to load performance data');
        }
    } catch (error) {
        console.error('Load Performance Error:', error);
        showError('Failed to load performance data. Please try again.');
    }
}

// Filter and render data
function filterAndRenderData() {
    let filteredData = [...allPerformanceData];
    
    // Apply search filter
    if (currentFilter.search) {
        filteredData = filteredData.filter(user => 
            user.user_name.toLowerCase().includes(currentFilter.search) ||
            (user.username && user.username.toLowerCase().includes(currentFilter.search))
        );
    }
    
    // Apply grade filter
    if (currentFilter.grade) {
        filteredData = filteredData.filter(user => user.performance_grade === currentFilter.grade);
    }
    
    // Apply sorting
    filteredData = sortData(filteredData, currentFilter.sortBy);
    
    // Render table
    renderPerformanceTable(filteredData);
}

// Sort data
function sortData(data, sortBy) {
    switch (sortBy) {
        case 'completed_desc':
            return data.sort((a, b) => b.completed_jobs - a.completed_jobs);
        case 'revenue_desc':
            return data.sort((a, b) => b.total_invoice_amount - a.total_invoice_amount);
        case 'jobs_desc':
            return data.sort((a, b) => b.total_jobs - a.total_jobs);
        case 'sla_desc':
            return data.sort((a, b) => b.sla_compliance_rate - a.sla_compliance_rate);
        case 'name_asc':
            return data.sort((a, b) => a.user_name.localeCompare(b.user_name));
        default:
            return data;
    }
}

// Update summary metrics
function updateSummaryMetrics(summary) {
    const totalUsersElement = document.getElementById('totalUsersCount');
    const totalCompletedElement = document.getElementById('totalCompletedJobsCount');
    const totalRevenueElement = document.getElementById('totalRevenueCount');
    const avgCompletionElement = document.getElementById('avgCompletionTime');
    
    if (totalUsersElement) {
        totalUsersElement.textContent = summary.total_users || 0;
    }
    
    if (totalCompletedElement) {
        totalCompletedElement.textContent = summary.total_completed_jobs || 0;
    }
    
    if (totalRevenueElement) {
        totalRevenueElement.textContent = summary.formatted_total_revenue || '$0.00';
    }
    
    if (avgCompletionElement) {
        avgCompletionElement.textContent = summary.overall_avg_completion_days || 0;
    }
}

// Render performance table
function renderPerformanceTable(data) {
    const tbody = document.getElementById('performanceTableBody');
    
    if (!tbody) return;
    
    if (data.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="11">
                    <div class="empty-performance-state">
                        <i class="bi bi-people"></i>
                        <h4>No Users Found</h4>
                        <p>No users match your current filter criteria.</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = data.map(user => createPerformanceRow(user)).join('');
}

// Create performance row
function createPerformanceRow(user) {
    const initials = getInitials(user.user_name);
    const slaRateClass = getSlaRateClass(user.sla_compliance_rate);
    
    return `
        <tr>
            <td>
                <div class="user-info-cell">
                    <div class="user-avatar-small">${initials}</div>
                    <div class="user-details">
                        <div class="user-name">${escapeHtml(user.user_name)}</div>
                        <div class="user-email">${escapeHtml(user.username || 'N/A')}</div>
                    </div>
                </div>
            </td>
            <td>
                <span class="metric-badge jobs">${user.total_jobs}</span>
            </td>
            <td>
                <span class="metric-badge completed">${user.completed_jobs}</span>
            </td>
            <td>
                <span class="metric-badge progress">${user.in_progress_jobs}</span>
            </td>
            <td>
                <span class="metric-badge pending">${user.pending_jobs}</span>
            </td>
            <td>
                <span class="metric-badge jobs">${user.total_vendors}</span>
            </td>
            <td>
                <span class="revenue-amount">${user.formatted_total_amount}</span>
            </td>
            <td>
                <strong>${user.total_invoices}</strong>
            </td>
            <td>
                <span class="text-muted">${user.avg_completion_days} days</span>
            </td>
            <td>
                <span class="sla-rate ${slaRateClass}">${user.sla_compliance_rate}%</span>
            </td>
            <td>
                <div class="performance-grade grade-${user.grade_color}" title="${getGradeTitle(user.performance_grade)}">
                    ${user.performance_grade}
                </div>
            </td>
        </tr>
    `;
}

// Helper functions
function getInitials(name) {
    if (!name) return 'NA';
    const parts = name.split(' ');
    if (parts.length === 1) {
        return parts[0].substring(0, 2).toUpperCase();
    }
    return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
}

function getSlaRateClass(rate) {
    if (rate >= 80) return 'high';
    if (rate >= 60) return 'medium';
    return 'low';
}

function getGradeTitle(grade) {
    const titles = {
        'A+': 'Excellent Performance',
        'A': 'Very Good Performance',
        'B': 'Good Performance',
        'C': 'Average Performance',
        'D': 'Needs Improvement'
    };
    return titles[grade] || 'Performance Grade';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    const tbody = document.getElementById('performanceTableBody');
    
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="11">
                    <div class="empty-performance-state">
                        <i class="bi bi-exclamation-triangle text-danger"></i>
                        <h4 class="text-danger">Error Loading Data</h4>
                        <p>${escapeHtml(message)}</p>
                        <button class="btn btn-primary btn-sm" onclick="loadPerformanceData()">
                            <i class="bi bi-arrow-clockwise"></i> Retry
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }
}

// Export functions for global access
window.PerformanceSystem = {
    loadPerformanceData,
    filterAndRenderData
};

