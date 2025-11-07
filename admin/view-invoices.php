<?php
$pageTitle = "View Invoices";
include 'header.php';
include 'sidebar.php';
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Dashboard Content -->
    <main class="dashboard-content">
        
        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="welcome-content">
                <h2>View Invoices</h2>
                <p>Manage and view all generated invoices</p>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="metrics-row" id="metricsRow">
            <div class="metric-card" id="metricCardTotalInvoices">
                <div class="metric-icon invoices">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="metric-content">
                    <h3 id="totalInvoicesCount">0</h3>
                    <p class="metric-label">TOTAL INVOICES</p>
                    <span class="metric-status text-success">All invoices</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardTotalAmount">
                <div class="metric-icon amount">
                    <i class="bi bi-currency-dollar"></i>
                </div>
                <div class="metric-content">
                    <h3 id="totalAmountCount">$0</h3>
                    <p class="metric-label">TOTAL AMOUNT</p>
                    <span class="metric-status text-info">All invoices</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardCompanies">
                <div class="metric-icon companies">
                    <i class="bi bi-building"></i>
                </div>
                <div class="metric-content">
                    <h3 id="companiesCount">0</h3>
                    <p class="metric-label">COMPANIES</p>
                    <span class="metric-status text-warning">Active companies</span>
                </div>
            </div>

            <div class="metric-card" id="metricCardRecentInvoices">
                <div class="metric-icon recent">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="metric-content">
                    <h3 id="recentInvoicesCount">0</h3>
                    <p class="metric-label">THIS MONTH</p>
                    <span class="metric-status text-primary">New invoices</span>
                </div>
            </div>
        </div>

        <!-- Invoices List Section -->
        <div class="invoices-section">
            <div class="section-header">
                <h3>All Invoices</h3>
                <div class="section-actions">
                    <a href="invoices-generator.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create New Invoice
                    </a>
                </div>
            </div>

            <!-- Search and Filter Section -->
            <div class="search-filter-section">
                <div class="row g-3">
                    <div class="col-lg-6 col-md-8 col-12">
                        <div class="search-box">
                            <label>Search</label>
                            <div class="search-input-wrapper">
                                <i class="bi bi-search search-icon"></i>
                                <input type="text" class="search-input" id="invoiceSearchInput" 
                                       placeholder="Search by invoice number, client, or company...">
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-4 col-12">
                        <div class="filter-dropdowns">
                            <div class="dropdown-wrapper">
                                <label>Company</label>
                                <select class="filter-dropdown" id="companyFilter">
                                    <option value="">All Companies</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-md-12 col-12">
                        <div class="filter-dropdowns">
                            <div class="dropdown-wrapper">
                                <label>Sort By</label>
                                <select class="filter-dropdown" id="sortFilter">
                                    <option value="created_at">Date Created</option>
                                    <option value="date">Invoice Date</option>
                                    <option value="invoice_number">Invoice Number</option>
                                    <option value="company_name">Company</option>
                                    <option value="total_amount">Amount</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="invoices-table-container" id="invoicesTableContainer">
                <div class="text-center py-5" id="invoicesLoading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading invoices...</p>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Invoice Details Modal -->
<div class="modal fade" id="invoiceDetailsModal" tabindex="-1" aria-labelledby="invoiceDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceDetailsModalLabel">
                    <i class="bi bi-receipt"></i>
                    Invoice Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="invoiceDetailsContent">
                <!-- Invoice details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="printInvoiceBtn">
                    <i class="bi bi-printer"></i>
                    Print Invoice
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Invoice Metric Icons */
.metric-icon.invoices {
    background: linear-gradient(135deg, var(--success-color), #059669);
}

.metric-icon.amount {
    background: linear-gradient(135deg, var(--info-color), #4f46e5);
}

.metric-icon.companies {
    background: linear-gradient(135deg, var(--warning-color), #d97706);
}

.metric-icon.recent {
    background: linear-gradient(135deg, var(--primary-color), #5a7ce8);
}

.invoices-section {
    margin-bottom: 2rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.section-header h3 {
    margin: 0;
    color: var(--text-dark);
    font-weight: 600;
}


.search-box {
    margin-bottom: 1rem;
}

.search-box label {
    display: block;
    font-weight: 500;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
}

.search-input-wrapper {
    position: relative;
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: var(--bg-white);
}

.search-input:focus {
    border-color: var(--danger-color);
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    outline: none;
}

.search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-medium);
    font-size: 1rem;
}

.filter-dropdowns {
    margin-bottom: 1rem;
}

.filter-dropdowns label {
    display: block;
    font-weight: 500;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
}

.filter-dropdown {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: 0.95rem;
    background: var(--bg-white);
    transition: all 0.3s ease;
}

.filter-dropdown:focus {
    border-color: var(--danger-color);
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
    outline: none;
}

.invoices-table-container {
    background: var(--bg-white);
    border-radius: var(--radius-lg);
    border: 1px solid var(--border-color);
    overflow: hidden;
    box-shadow: var(--shadow-light);
}

.invoices-table {
    width: 100%;
    margin: 0;
    border-collapse: collapse;
}

.invoices-table th {
    background: var(--bg-light);
    color: var(--text-dark);
    font-weight: 600;
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.invoices-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
}

.invoices-table tbody tr:hover {
    background: var(--bg-light);
}

.invoice-number {
    font-weight: 600;
    color: var(--danger-color);
}

.invoice-company {
    font-weight: 500;
    color: var(--text-dark);
}

.invoice-client {
    color: var(--text-secondary);
}

.invoice-amount {
    font-weight: 600;
    color: var(--success-color);
}

.invoice-date {
    color: var(--text-medium);
    font-size: 0.9rem;
}

.invoice-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: var(--radius-sm);
}

.btn-outline-primary {
    color: var(--danger-color);
    border-color: var(--danger-color);
}

.btn-outline-primary:hover {
    background: var(--danger-color);
    border-color: var(--danger-color);
    color: white;
}

.btn-outline-info {
    color: var(--info-color);
    border-color: var(--info-color);
}

.btn-outline-info:hover {
    background: var(--info-color);
    border-color: var(--info-color);
    color: white;
}

.no-data {
    text-align: center;
    padding: 3rem;
    color: var(--text-medium);
}

.no-data i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: var(--text-light);
}

/* Responsive Design */
@media (max-width: 768px) {
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .invoices-table {
        font-size: 0.85rem;
    }
    
    .invoices-table th,
    .invoices-table td {
        padding: 0.75rem 0.5rem;
    }
    
    .invoice-actions {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let allInvoices = [];
    let filteredInvoices = [];
    
    // Load invoices on page load
    loadInvoices();
    
    // Search functionality
    document.getElementById('invoiceSearchInput').addEventListener('input', function() {
        filterInvoices();
    });
    
    // Company filter
    document.getElementById('companyFilter').addEventListener('change', function() {
        filterInvoices();
    });
    
    // Sort filter
    document.getElementById('sortFilter').addEventListener('change', function() {
        filterInvoices();
    });
    
    // Load invoices from API
    async function loadInvoices() {
        try {
            const response = await fetch('assets/api/get_invoices.php');
            const result = await response.json();
            
            if (result.success) {
                allInvoices = result.invoices;
                filteredInvoices = [...allInvoices];
                
                // Update metrics
                updateMetrics(result.stats);
                
                // Populate company filter
                populateCompanyFilter(result.stats.companies);
                
                // Display invoices
                displayInvoices();
            } else {
                showError('Failed to load invoices: ' + result.message);
            }
        } catch (error) {
            console.error('Error loading invoices:', error);
            showError('Network error. Please check your connection.');
        }
    }
    
    // Update metrics
    function updateMetrics(stats) {
        document.getElementById('totalInvoicesCount').textContent = stats.total_invoices;
        document.getElementById('totalAmountCount').textContent = '$' + parseFloat(stats.total_amount).toLocaleString();
        document.getElementById('companiesCount').textContent = stats.companies.length;
        
        // Calculate recent invoices (this month)
        const thisMonth = new Date().getMonth();
        const thisYear = new Date().getFullYear();
        const recentCount = allInvoices.filter(invoice => {
            const invoiceDate = new Date(invoice.created_at);
            return invoiceDate.getMonth() === thisMonth && invoiceDate.getFullYear() === thisYear;
        }).length;
        
        document.getElementById('recentInvoicesCount').textContent = recentCount;
    }
    
    // Populate company filter
    function populateCompanyFilter(companies) {
        const companyFilter = document.getElementById('companyFilter');
        companyFilter.innerHTML = '<option value="">All Companies</option>';
        
        companies.forEach(company => {
            const option = document.createElement('option');
            option.value = company;
            option.textContent = company;
            companyFilter.appendChild(option);
        });
    }
    
    // Filter invoices
    function filterInvoices() {
        const searchTerm = document.getElementById('invoiceSearchInput').value.toLowerCase();
        const companyFilter = document.getElementById('companyFilter').value;
        const sortBy = document.getElementById('sortFilter').value;
        
        filteredInvoices = allInvoices.filter(invoice => {
            const matchesSearch = !searchTerm || 
                invoice.invoice_number.toLowerCase().includes(searchTerm) ||
                invoice.invoice_to.toLowerCase().includes(searchTerm) ||
                invoice.company_name.toLowerCase().includes(searchTerm);
            
            const matchesCompany = !companyFilter || invoice.company_name === companyFilter;
            
            return matchesSearch && matchesCompany;
        });
        
        // Sort invoices
        filteredInvoices.sort((a, b) => {
            if (sortBy === 'total_amount') {
                return parseFloat(b[sortBy]) - parseFloat(a[sortBy]);
            } else if (sortBy === 'date' || sortBy === 'created_at') {
                return new Date(b[sortBy]) - new Date(a[sortBy]);
            } else {
                return a[sortBy].localeCompare(b[sortBy]);
            }
        });
        
        displayInvoices();
    }
    
    // Display invoices in table
    function displayInvoices() {
        const container = document.getElementById('invoicesTableContainer');
        
        if (filteredInvoices.length === 0) {
            container.innerHTML = `
                <div class="no-data">
                    <i class="bi bi-receipt"></i>
                    <h5>No invoices found</h5>
                    <p>No invoices match your current filters.</p>
                </div>
            `;
            return;
        }
        
        const tableHTML = `
            <table class="invoices-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Company</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Items</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${filteredInvoices.map(invoice => `
                        <tr>
                            <td>
                                <span class="invoice-number">${invoice.invoice_number}</span>
                            </td>
                            <td>
                                <span class="invoice-company">${invoice.company_name}</span>
                            </td>
                            <td>
                                <span class="invoice-client">${invoice.invoice_to}</span>
                            </td>
                            <td>
                                <span class="invoice-date">${invoice.formatted_date}</span>
                            </td>
                            <td>
                                <span class="invoice-amount">${invoice.formatted_amount}</span>
                            </td>
                            <td>
                                <span class="badge bg-info">${invoice.items_count} items</span>
                            </td>
                            <td>
                                <div class="invoice-actions">
                                    <button class="btn btn-outline-primary btn-sm" onclick="viewInvoiceDetails('${invoice.invoice_number}')">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" onclick="printInvoice('${invoice.invoice_number}')">
                                        <i class="bi bi-printer"></i> Print
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
        
        container.innerHTML = tableHTML;
    }
    
    // View invoice details
    window.viewInvoiceDetails = function(invoiceNumber) {
        window.location.href = `view-invoice.php?invoice=${invoiceNumber}`;
    };
    
    // Print invoice
    window.printInvoice = function(invoiceNumber) {
        window.open(`view-invoice.php?invoice=${invoiceNumber}&print=1`, '_blank');
    };
    
    // Show error message
    function showError(message) {
        const container = document.getElementById('invoicesTableContainer');
        container.innerHTML = `
            <div class="no-data">
                <i class="bi bi-exclamation-triangle"></i>
                <h5>Error</h5>
                <p>${message}</p>
            </div>
        `;
    }
});
</script>

<?php include 'footer.php'; ?>
