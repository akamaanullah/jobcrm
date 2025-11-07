<?php
$pageTitle = "Invoices Generator";
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
                <h2>Invoice Generator</h2>
                <p>Create and manage professional invoices for your jobs</p>
            </div>
        </div>

        <!-- Job Details Metric Cards (Only show if job_id in URL) -->
        <div class="job-details-metrics" id="jobDetailsSection" style="display: none;">
            <div class="row g-4">
                <!-- Store Name Card -->
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-icon store">
                            <i class="bi bi-shop"></i>
                        </div>
                        <div class="metric-content">
                            <h3 id="jobStoreName">-</h3>
                            <p class="metric-label">Store Name</p>
                            <span class="metric-status text-primary">Job Location</span>
                        </div>
                    </div>
                </div>

                <!-- Vendor Name Card -->
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-icon vendor">
                            <i class="bi bi-person-badge"></i>
                        </div>
                        <div class="metric-content">
                            <h3 id="jobVendorName">-</h3>
                            <p class="metric-label">Vendor Name</p>
                            <span class="metric-status text-success">Assigned Vendor</span>
                        </div>
                    </div>
                </div>

                <!-- Job Type Card -->
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-icon type">
                            <i class="bi bi-tag"></i>
                        </div>
                        <div class="metric-content">
                            <h3 id="jobType">-</h3>
                            <p class="metric-label">Job Type</p>
                            <span class="metric-status text-info">Service Category</span>
                        </div>
                    </div>
                </div>

                <!-- Assigned To Card -->
                <div class="col-lg-3 col-md-6">
                    <div class="metric-card">
                        <div class="metric-icon assigned">
                            <i class="bi bi-person-check"></i>
                        </div>
                        <div class="metric-content">
                            <h3 id="jobAssignedTo">-</h3>
                            <p class="metric-label">Assigned To</p>
                            <span class="metric-status text-warning">Responsible User</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Form Section -->
        <div class="invoice-form-section">
            <div class="content-card">
                <div class="card-header">
                    <h4>
                        <i class="bi bi-file-earmark-text"></i>
                        Create New Invoice
                    </h4>
                    <p class="card-subtitle">Fill in the details below to generate a professional invoice</p>
                </div>

                <div class="card-body">
                    <form id="invoiceForm" class="invoice-form">
                        <!-- Invoice Header -->
                        <div class="form-section">
                            <h5 class="section-title">
                                <i class="bi bi-info-circle"></i>
                                Invoice Information
                            </h5>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="companyName" class="form-label">
                                            <i class="bi bi-shop"></i>
                                            Company Name
                                        </label>
                                        <select class="form-control" id="companyName" name="companyName" required>
                                            <option value="">Select Company</option>
                                            <option value="Handy For Repair">Handy For Repair</option>
                                            <option value="Handy Repair Center">Handy Repair Center</option>
                                            <option value="West Gate Contractor">West Gate Contractor</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="invoiceTo" class="form-label">
                                            <i class="bi bi-person"></i>
                                            Invoice To
                                        </label>
                                        <input type="text" class="form-control" id="invoiceTo" name="invoiceTo"
                                            placeholder="Enter client name or company" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="invoiceDate" class="form-label">
                                            <i class="bi bi-calendar"></i>
                                            Invoice Date
                                        </label>
                                        <input type="date" class="form-control" id="invoiceDate" name="invoiceDate"
                                            value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Invoice Items Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <h5 class="section-title">
                                    <i class="bi bi-list-ul"></i>
                                    Invoice Items
                                </h5>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addItemBtn">
                                    <i class="bi bi-plus-circle"></i>
                                    Add Item
                                </button>
                            </div>

                            <div class="invoice-items-container" id="invoiceItemsContainer">
                                <!-- Default Item Row -->
                                <div class="invoice-item-row" data-item="1">
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label class="form-label">Item Description</label>
                                                <input type="text" class="form-control item-description"
                                                    name="items[1][description]" placeholder="Enter item description"
                                                    required>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">Quantity</label>
                                                <input type="number" class="form-control item-quantity"
                                                    name="items[1][quantity]" min="1" value="1" required>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label class="form-label">Unit Price ($)</label>
                                                <input type="number" class="form-control item-price"
                                                    name="items[1][price]" step="0.01" min="0" placeholder="0.00"
                                                    required>
                                            </div>
                                        </div>

                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="button"
                                                    class="btn btn-outline-danger btn-sm remove-item-btn"
                                                    style="display: none;">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row">
                                        <div class="col-md-6 offset-md-6">
                                            <div class="item-total">
                                                <strong>Total: $<span class="item-total-amount">0.00</span></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Section -->
                        <div class="form-section">
                            <div class="section-header">
                                <h5 class="section-title">
                                    <i class="bi bi-geo-alt"></i>
                                    Invoice Addresses
                                </h5>
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addAddressBtn">
                                    <i class="bi bi-plus-circle"></i>
                                    Add Address
                                </button>
                            </div>

                            <div class="addresses-container" id="addressesContainer">
                                <!-- Default Address Row -->
                                <div class="address-row" data-address="1">
                                    <div class="form-group">
                                        <label class="form-label">Address</label>
                                        <div class="input-group">
                                            <textarea class="form-control address-input" name="addresses[1]" rows="3"
                                                placeholder="Enter address" required></textarea>
                                            <button type="button" class="btn btn-outline-danger remove-address-btn"
                                                style="display: none;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Summary -->
                        <div class="form-section">
                            <div class="invoice-summary">
                                <div class="row">
                                    <div class="col-md-6 offset-md-6">
                                        <div class="summary-card">
                                            <div class="summary-row total-row">
                                                <span><strong>Total Amount:</strong></span>
                                                <span id="totalAmount"><strong>$0.00</strong></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="generateInvoiceBtn">
                                <i class="bi bi-file-earmark-pdf"></i>
                                Generate Invoice
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Invoice Preview Modal -->
<div class="modal fade" id="invoicePreviewModal" tabindex="-1" aria-labelledby="invoicePreviewModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoicePreviewModalLabel">
                    <i class="bi bi-eye"></i>
                    Invoice Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="invoicePreviewContent">
                    <!-- Invoice preview will be generated here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="downloadInvoiceBtn">
                    <i class="bi bi-download"></i>
                    Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Invoice Form Specific Styles */
    .page-header {
        background: linear-gradient(135deg, var(--danger-color) 0%, #B91C1C 100%);
        border-radius: var(--radius-lg);
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-medium);
    }

    .page-header-content h2 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .page-header-content p {
        margin: 0.5rem 0 0 0;
        opacity: 0.9;
        font-size: 1.1rem;
    }

    .invoice-form-section {
        margin-bottom: 1rem;
    }

    .form-section {
        margin-bottom: 2.5rem;
        padding: 1.5rem;
        background: var(--bg-light);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
    }

    .section-title {
        color: var(--text-dark);
        font-weight: 600;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.1rem;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .form-control {
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: var(--bg-white);
    }

    .form-control:focus {
        border-color: var(--danger-color);
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
        outline: none;
    }

    .invoice-item-row {
        background: var(--bg-white);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .invoice-item-row:hover {
        box-shadow: var(--shadow-light);
        border-color: var(--danger-color);
    }

    .item-total {
        text-align: right;
        padding: 0.75rem 0;
        /* border-top: 1px solid var(--border-color); */
        margin-top: 1rem;
        color: var(--text-dark);
    }

    .invoice-summary {
        background: var(--bg-white);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
    }

    .summary-card {
        background: linear-gradient(135deg, var(--bg-light) 0%, rgba(220, 53, 69, 0.05) 100%);
        border-radius: var(--radius-md);
        padding: 1.5rem;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .summary-row:last-child {
        border-bottom: none;
    }

    .total-row {
        border-top: 2px solid var(--danger-color);
        margin-top: 1rem;
        padding-top: 1rem;
        font-size: 1.1rem;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        padding: 2rem 0;
        border-top: 1px solid var(--border-color);
        margin-top: 2rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border-radius: var(--radius-md);
        font-weight: 500;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background: var(--danger-color);
        border-color: var(--danger-color);
        color: white;
    }

    .btn-primary:hover {
        background: #B91C1C;
        border-color: #B91C1C;
        transform: translateY(-2px);
        box-shadow: var(--shadow-medium);
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

    .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
    }

    .btn-outline-danger:hover {
        background: #dc3545;
        border-color: #dc3545;
        color: white;
    }

    .btn-secondary {
        background: var(--bg-medium);
        border-color: var(--bg-medium);
        color: white;
    }

    .btn-secondary:hover {
        background: #8a8a8a;
        border-color: #8a8a8a;
        color: white;
    }

    /* Address Management Styles */
    .addresses-container {
        margin-bottom: 1rem;
    }

    .address-row {
        background: var(--bg-white);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .address-row:hover {
        box-shadow: var(--shadow-light);
        border-color: var(--danger-color);
    }

    .address-row:last-child {
        margin-bottom: 0;
    }

    .input-group {
        display: flex;
        gap: 0.5rem;
        align-items: flex-start;
    }

    .input-group .form-control {
        flex: 1;
    }

    .input-group .btn {
        flex-shrink: 0;
        height: fit-content;
        margin-top: 0.25rem;
    }

    /* Job Details Metric Cards Styling */
    .job-details-metrics {
        margin-bottom: 2rem;
    }

    .job-details-metrics .metric-card {
        background: var(--bg-white);
        border: 1px solid var(--border-light);
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        height: 100%;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .job-details-metrics .metric-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .job-details-metrics .metric-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        flex-shrink: 0;
    }

    .job-details-metrics .metric-icon.store {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }

    .job-details-metrics .metric-icon.vendor {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }

    .job-details-metrics .metric-icon.type {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }

    .job-details-metrics .metric-icon.assigned {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    }

    .job-details-metrics .metric-content {
        flex: 1;
        min-width: 0;
    }

    .job-details-metrics .metric-content h3 {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.25rem;
        word-break: break-word;
        line-height: 1.3;
    }

    .job-details-metrics .metric-label {
        font-size: 0.85rem;
        color: var(--text-muted);
        margin-bottom: 0.25rem;
        font-weight: 500;
    }

    .job-details-metrics .metric-status {
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
        }

        .page-header-content h2 {
            font-size: 1.5rem;
        }

        .form-section {
            padding: 1rem;
        }

        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .form-actions {
            flex-direction: column;
        }

        .summary-card {
            padding: 1rem;
        }

        .input-group {
            flex-direction: column;
        }

        .input-group .btn {
            align-self: flex-end;
        }

        .job-details-section .detail-item {
            margin-bottom: 0.75rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let itemCounter = 1;
        let addressCounter = 1;

        // Add new item row
        document.getElementById('addItemBtn').addEventListener('click', function () {
            itemCounter++;
            const container = document.getElementById('invoiceItemsContainer');
            const newRow = createItemRow(itemCounter);
            container.appendChild(newRow);
            updateRemoveButtons();
        });

        // Add new address row
        document.getElementById('addAddressBtn').addEventListener('click', function () {
            addressCounter++;
            const container = document.getElementById('addressesContainer');
            const newRow = createAddressRow(addressCounter);
            container.appendChild(newRow);
            updateAddressRemoveButtons();
        });

        // Remove item row
        document.addEventListener('click', function (e) {
            if (e.target.closest('.remove-item-btn')) {
                e.target.closest('.invoice-item-row').remove();
                updateRemoveButtons();
                calculateTotals();
            }

            // Remove address row
            if (e.target.closest('.remove-address-btn')) {
                e.target.closest('.address-row').remove();
                updateAddressRemoveButtons();
            }
        });

        // Calculate totals when inputs change
        document.addEventListener('input', function (e) {
            if (e.target.classList.contains('item-quantity') || e.target.classList.contains('item-price')) {
                calculateItemTotal(e.target.closest('.invoice-item-row'));
                calculateTotals();
            }
        });

        // Create new item row
        function createItemRow(counter) {
            const row = document.createElement('div');
            row.className = 'invoice-item-row';
            row.setAttribute('data-item', counter);
            row.innerHTML = `
            <div class="row g-3">
                <div class="col-md-5">
                    <div class="form-group">
                        <label class="form-label">Item Description</label>
                        <input type="text" class="form-control item-description" 
                               name="items[${counter}][description]" placeholder="Enter item description" required>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control item-quantity" 
                               name="items[${counter}][quantity]" min="1" value="1" required>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label">Unit Price ($)</label>
                        <input type="number" class="form-control item-price" 
                               name="items[${counter}][price]" step="0.01" min="0" placeholder="0.00" required>
                    </div>
                </div>
                
                <div class="col-md-1">
                    <div class="form-group">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            
            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <div class="item-total">
                        <strong>Total: $<span class="item-total-amount">0.00</span></strong>
                    </div>
                </div>
            </div>
        `;
            return row;
        }

        // Create new address row
        function createAddressRow(counter) {
            const row = document.createElement('div');
            row.className = 'address-row';
            row.setAttribute('data-address', counter);
            row.innerHTML = `
            <div class="form-group">
                <label class="form-label">Address</label>
                <div class="input-group">
                    <textarea class="form-control address-input" 
                              name="addresses[${counter}]" rows="3" placeholder="Enter address" required></textarea>
                    <button type="button" class="btn btn-outline-danger remove-address-btn">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
            return row;
        }

        // Update remove buttons visibility
        function updateRemoveButtons() {
            const rows = document.querySelectorAll('.invoice-item-row');
            rows.forEach((row, index) => {
                const removeBtn = row.querySelector('.remove-item-btn');
                removeBtn.style.display = rows.length > 1 ? 'block' : 'none';
            });
        }

        // Update address remove buttons visibility
        function updateAddressRemoveButtons() {
            const rows = document.querySelectorAll('.address-row');
            rows.forEach((row, index) => {
                const removeBtn = row.querySelector('.remove-address-btn');
                removeBtn.style.display = rows.length > 1 ? 'block' : 'none';
            });
        }

        // Calculate individual item total
        function calculateItemTotal(row) {
            const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            const total = quantity * price;
            row.querySelector('.item-total-amount').textContent = total.toFixed(2);
        }

        // Calculate overall totals
        function calculateTotals() {
            let total = 0;
            document.querySelectorAll('.invoice-item-row').forEach(row => {
                const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                const price = parseFloat(row.querySelector('.item-price').value) || 0;
                total += quantity * price;
            });

            document.getElementById('totalAmount').innerHTML = `<strong>$${total.toFixed(2)}</strong>`;
        }

        // Form submission
        document.getElementById('invoiceForm').addEventListener('submit', function (e) {
            e.preventDefault();

            // Show loading state
            const submitBtn = document.getElementById('generateInvoiceBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Saving...';
            submitBtn.disabled = true;

            // Collect form data
            const formData = new FormData(this);
            
            // Get job_id from URL if present
            const urlParams = new URLSearchParams(window.location.search);
            const jobId = urlParams.get('job_id');
            
            const invoiceData = {
                companyName: formData.get('companyName'),
                invoiceTo: formData.get('invoiceTo'),
                invoiceDate: formData.get('invoiceDate'),
                jobId: jobId, // Include job_id if available
                items: [],
                addresses: []
            };

            // Collect items
            document.querySelectorAll('.invoice-item-row').forEach((row, index) => {
                const description = row.querySelector('.item-description').value;
                const quantity = parseFloat(row.querySelector('.item-quantity').value);
                const price = parseFloat(row.querySelector('.item-price').value);

                if (description && quantity && price) {
                    invoiceData.items.push({
                        description: description,
                        quantity: quantity,
                        price: price,
                        total: quantity * price
                    });
                }
            });

            // Collect addresses
            document.querySelectorAll('.address-input').forEach((input, index) => {
                if (input.value.trim()) {
                    invoiceData.addresses.push(input.value.trim());
                }
            });

            // Calculate totals
            const total = invoiceData.items.reduce((sum, item) => sum + item.total, 0);

            invoiceData.total = total;

            // Validate data
            if (!invoiceData.companyName || !invoiceData.invoiceTo || !invoiceData.invoiceDate || invoiceData.items.length === 0 || invoiceData.addresses.length === 0) {
                alert('Please fill in all required fields, add at least one item, and add at least one address.');
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                return;
            }

            // Send data to API
            fetch('assets/api/save_invoice.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(invoiceData)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        showSuccessMessage('Invoice saved successfully! Invoice Number: ' + data.data.invoice_number);

                        // Redirect to view-invoices.php after a short delay
                        setTimeout(() => {
                            window.location.href = 'view-invoices.php';
                        }, 1000);

                        // Reset form
                        document.getElementById('invoiceForm').reset();
                        document.getElementById('invoiceDate').value = new Date().toISOString().split('T')[0];

                        // Reset items to single row
                        const container = document.getElementById('invoiceItemsContainer');
                        container.innerHTML = `
                    <div class="invoice-item-row" data-item="1">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label class="form-label">Item Description</label>
                                    <input type="text" class="form-control item-description" 
                                           name="items[1][description]" placeholder="Enter item description" required>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" class="form-control item-quantity" 
                                           name="items[1][quantity]" min="1" value="1" required>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Unit Price ($)</label>
                                    <input type="number" class="form-control item-price" 
                                           name="items[1][price]" step="0.01" min="0" placeholder="0.00" required>
                                </div>
                            </div>
                            
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn" 
                                            style="display: none;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <div class="item-total">
                                    <strong>Total: $<span class="item-total-amount">0.00</span></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                        // Reset addresses to single row
                        const addressContainer = document.getElementById('addressesContainer');
                        addressContainer.innerHTML = `
                    <div class="address-row" data-address="1">
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <div class="input-group">
                                <textarea class="form-control address-input" 
                                          name="addresses[1]" rows="3" placeholder="Enter address" required></textarea>
                                <button type="button" class="btn btn-outline-danger remove-address-btn" 
                                        style="display: none;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                        itemCounter = 1;
                        addressCounter = 1;
                        updateRemoveButtons();
                        updateAddressRemoveButtons();
                        calculateTotals();

                    } else {
                        showErrorMessage(data.message || 'Failed to save invoice. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorMessage('Network error. Please check your connection and try again.');
                })
                .finally(() => {
                    // Reset button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
        });

        // Show success message
        function showSuccessMessage(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
            <i class="bi bi-check-circle"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

            const form = document.getElementById('invoiceForm');
            form.insertBefore(alertDiv, form.firstChild);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // Show error message
        function showErrorMessage(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertDiv.innerHTML = `
            <i class="bi bi-exclamation-triangle"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

            const form = document.getElementById('invoiceForm');
            form.insertBefore(alertDiv, form.firstChild);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }


        // Initialize
        updateRemoveButtons();
        updateAddressRemoveButtons();
        calculateTotals();

        // Load job details if job_id is in URL
        loadJobDetails();
    });

    // Load Job Details Function
    async function loadJobDetails() {
        const urlParams = new URLSearchParams(window.location.search);
        const jobId = urlParams.get('job_id');

        if (!jobId) {
            // No job_id parameter, hide job details section
            document.getElementById('jobDetailsSection').style.display = 'none';
            return;
        }

        try {
            // Show job details section
            document.getElementById('jobDetailsSection').style.display = 'block';

            // Fetch job details
            const response = await fetch(`assets/api/get_job_details.php?job_id=${jobId}`);
            const data = await response.json();

            if (data.success && data.job) {
                const job = data.job;

                // Populate job details in metric cards
                document.getElementById('jobStoreName').textContent = job.store_name || 'N/A';
                document.getElementById('jobVendorName').textContent = job.vendor_name || 'No vendor assigned';
                document.getElementById('jobType').textContent = job.job_type || 'N/A';
                document.getElementById('jobAssignedTo').textContent = job.assigned_to_name || 'Not assigned';

                // Pre-fill invoice form with job details
                if (job.store_name) {
                    document.getElementById('invoiceTo').value = job.store_name;
                }
            } else {
                console.error('Failed to load job details:', data.message);
                // Hide job details section if failed to load
                document.getElementById('jobDetailsSection').style.display = 'none';
            }
        } catch (error) {
            console.error('Error loading job details:', error);
            // Hide job details section on error
            document.getElementById('jobDetailsSection').style.display = 'none';
        }
    }
</script>

<?php include 'footer.php'; ?>