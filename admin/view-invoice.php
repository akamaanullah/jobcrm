<?php
// Get invoice number from URL
$invoiceNumber = $_GET['invoice'] ?? '';
$printMode = isset($_GET['print']) && $_GET['print'] == '1';

if (empty($invoiceNumber)) {
    header('Location: view-invoices.php');
    exit;
}

if ($printMode) {
    // For print mode, use minimal layout without admin panel
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Invoice - <?php echo $invoiceNumber; ?></title>

        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

        <!-- Google Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
            rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&display=swap" rel="stylesheet">

        <style>
            body {
                margin: 0;
                padding: 20px;
                background: white;
                font-family: 'Poppins', sans-serif;
            }

            .invoice-container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
            }

            /* Company Specific Styles */
            .invoice-template {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: white;
                color: #333;
            }

            /* Handy For Repair Template */
            .template-handyforrepair {
                max-width: 800px;
                margin: 0 auto;
                display: flex;
                flex-direction: column;
                min-height: 100vh;
            }

            .template-handyforrepair .invoice-header {
                border-radius: 0px 0px 73px 0px;
                background: #183655;
                color: white;
                padding: 20px;
                position: relative;
            }

            .template-handyforrepair .header-left {
                display: flex;
                align-items: center;
                gap: 15px;
            }

            .template-handyforrepair .logo {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
            }

            .template-handyforrepair .logo-image {
                width: 70px;
                height: 70px;
                object-fit: contain;
                position: relative;
                z-index: 2;
            }

            .template-handyforrepair .company-name {
                font-family: 'Cinzel', serif;
                font-size: 24px;
                font-weight: 600;
                letter-spacing: 2px;
                text-transform: uppercase;
            }

            .template-handyforrepair .header-right {
                position: absolute;
                top: 0;
                right: 0;
                width: 100px;
                height: 100%;
            }

            .template-handyforrepair .orange-curve {
                position: absolute;
                top: 0;
                right: 0;
                width: 380%;
                height: 45%;
                background: #f97316;
                border-radius: 0 0 0 100px;
            }

            .template-handyforrepair .invoice-title-section {
                padding: 30px 20px;
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
            }

            .template-handyforrepair .invoice-title h1 {
                font-size: 48px;
                font-weight: bold;
                color: #1f2937;
                margin: 0;
                text-transform: uppercase;
                letter-spacing: 3px;
            }

            .template-handyforrepair .contact-info {
                text-align: right;
                line-height: 1.6;
            }

            .template-handyforrepair .contact-info p {
                margin: 5px 0;
                font-size: 16px;
                color: #374151;
            }

            .template-handyforrepair .invoice-details {
                padding: 30px 20px;
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
            }

            .template-handyforrepair .client-info h3 {
                font-size: 18px;
                font-weight: bold;
                color: #1f2937;
                margin-bottom: 10px;
                text-transform: uppercase;
            }

            .template-handyforrepair .client-info p {
                margin: 3px 0;
                font-weight: 500;
                font-size: 16px;
                color: #374151;
            }

            .template-handyforrepair .invoice-meta p {
                margin: 0px 0px 5px 0px;
                font-size: 16px;
                color: #374151;
            }

            .template-handyforrepair .services-table {
                padding: 50px 20px;
            }

            .template-handyforrepair .table {
                width: 100%;
                border-collapse: collapse;
                margin: 0;
                border: 1px solid #d1d5db;
            }

            .template-handyforrepair .table-header {
                background: #183655 !important;
                color: white !important;
            }

            .template-handyforrepair .table-header th {
                background: #183655 !important;
                color: white !important;
            }

            .template-handyforrepair .table-header th {
                padding: 15px 10px;
                text-align: left;
                font-weight: bold;
                font-size: 14px;
                text-transform: uppercase;
                letter-spacing: 1px;
                border: 1px solid rgba(255, 255, 255, 0.3);
            }

            .template-handyforrepair .table tbody tr {
                border-bottom: 1px solid #d1d5db;
            }

            .template-handyforrepair .table tbody td {
                padding: 12px 10px;
                font-size: 14px;
                color: #374151;
                vertical-align: top;
                border: 1px solid #d1d5db;
                border-top: none;
            }

            .template-handyforrepair .table tbody td:nth-child(1),
            .template-handyforrepair .table tbody td:nth-child(3),
            .template-handyforrepair .table tbody td:nth-child(4),
            .template-handyforrepair .table tbody td:nth-child(5) {
                text-align: center;
            }

            .template-handyforrepair .total-row {
                border-top: 2px solid #d1d5db;
            }

            .template-handyforrepair .total-row td {
                font-weight: bold;
                font-size: 16px;
                color: #1f2937;
                padding: 15px 10px;
                border: 1px solid #d1d5db;
            }

            .template-handyforrepair .total-row td:nth-child(4),
            .template-handyforrepair .total-row td:nth-child(5) {
                text-align: center;
            }

            .template-handyforrepair .invoice-footer {
                background: linear-gradient(to bottom, white 50%, #e5e7eb 50%);
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                gap: 10px;
                border-top: 1px solid #e5e7eb;
                min-height: 120px;
                position: relative;
                margin-top: auto;
            }

            .template-handyforrepair .footer-left {
                text-align: center;
                padding: 20px;
            }

            .template-handyforrepair .footer-left h2 {
                font-size: 32px;
                font-weight: bold;
                color: #1f2937;
                margin-bottom: 10px;
            }

            .template-handyforrepair .footer-left p {
                font-weight: 500;
                margin: 3px 0;
                font-size: 14px;
                color: #374151;
            }

            .template-handyforrepair .footer-image {
                width: 150px;
                height: 150px;
                object-fit: contain;
            }

            /* .template-handyforrepair .addresses-section {
                margin: 20px 0;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 8px;
                border-left: 4px solid #183655;
            } */

            .template-handyforrepair .addresses-section .section-title {
                font-weight: 600;
                color: #183655;
                margin-bottom: 10px;
                font-size: 16px;
            }

            .template-handyforrepair .addresses-list {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            /* .template-handyforrepair .address-item {
                background: white;
                padding: 10px;
                border-radius: 5px;
                border: 1px solid #e9ecef;
                font-size: 14px;
                line-height: 1.4;
            } */

            /* Handy Repair Center Template */
            .template-handyrepaircenter {
                max-width: 661px;
                margin: 0 auto;
                padding: 20px;
                display: flex;
                flex-direction: column;
                min-height: 100vh;
            }

            .template-handyrepaircenter .header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 30px;
            }

            .template-handyrepaircenter .logo-section {
                display: flex;
                align-items: center;
                gap: 15px;
            }

            .template-handyrepaircenter .logo {
                height: 100px;
                width: 150px;
                object-fit: contain;
            }

            .template-handyrepaircenter .billing-section {
                margin-bottom: 30px;
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
            }

            .template-handyrepaircenter .billing-left {
                flex: 1;
            }

            .template-handyrepaircenter .billing-label {
                font-weight: bold;
                font-size: 20px;
                margin-bottom: 10px;
            }

            .template-handyrepaircenter .billing-address {
                font-size: 14px;
                line-height: 1.6;
            }

            .template-handyrepaircenter .billing-address div {
                margin-bottom: 2px;
            }

            .template-handyrepaircenter .addresses-section {
                margin-bottom: 50px;
            }

            .template-handyrepaircenter .addresses-section .section-title {
                font-weight: 600;
                color: #000000;
                margin-bottom: 10px;
                font-size: 16px;
            }

            .template-handyrepaircenter .addresses-list {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            /* .template-handyrepaircenter .address-item {
                background: white;
                padding: 10px;
                border-radius: 5px;
                border: 1px solid #e9ecef;
                font-size: 14px;
                line-height: 1.4;
            } */

            .template-handyrepaircenter .invoice-details {
                text-align: right;
                font-size: 18px;
                margin-top: 0;
            }

            .template-handyrepaircenter .invoice-number {
                font-weight: bold;
                margin-bottom: 5px;
            }

            .template-handyrepaircenter .invoice-date {
                color: #333333;
            }

            .template-handyrepaircenter .table-container {
                margin-bottom: 30px;
            }

            .template-handyrepaircenter .items-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 18px;
            }

            .template-handyrepaircenter .items-table thead {
                border-bottom: 1px solid #000000;
            }

            .template-handyrepaircenter .items-table th {
                text-align: left;
                padding: 10px 0;
                font-weight: normal;
                border-bottom: 1px solid #000000;
            }

            .template-handyrepaircenter .items-table tbody tr {
                border-bottom: 1px solid #000000;
            }

            .template-handyrepaircenter .items-table td {
                padding: 10px 0;
                vertical-align: top;
            }

            .template-handyrepaircenter .item-col {
                width: 50%;
            }

            .template-handyrepaircenter .qty-col {
                width: 15%;
            }

            .template-handyrepaircenter .price-col {
                width: 17.5%;
            }

            .template-handyrepaircenter .total-col {
                width: 17.5%;
            }

            .template-handyrepaircenter .summary-section {
                text-align: right;
                margin-bottom: 30px;
            }

            .template-handyrepaircenter .subtotal {
                font-size: 14px;
                margin-bottom: 10px;
                padding-bottom: 10px;
                border-bottom: 1px solid #000000;
            }

            .template-handyrepaircenter .total {
                font-size: 16px;
                font-weight: bold;
            }

            .template-handyrepaircenter .footer {
                background-color: #000000;
                color: #ffffff;
                padding: 36px 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 14px;
                position: relative;
                margin-top: auto;
            }

            /* West Gate Contractors Template */
            .template-westgatecontractors {
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                background-image: url('invoices/westgatecontractors/assets/images/background.png');
                background-repeat: no-repeat;
                min-height: 100vh;
            }

            .template-westgatecontractors .header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 30px;
            }

            .template-westgatecontractors .company-info {
                flex: 1;
            }

            .template-westgatecontractors .company-name {
                font-family: 'Times New Roman', serif;
                font-size: 28px;
                font-weight: bold;
                color: black;
                margin-bottom: 15px;
                text-transform: uppercase;
                letter-spacing: -0.5px;
            }

            .template-westgatecontractors .contact-details p {
                font-size: 14px;
                color: black;
                margin-bottom: 5px;
            }

            .template-westgatecontractors .addresses-section {
                margin: 20px 0;
                padding: 15px;
                background: rgba(255, 255, 255, 0.9);
                border-radius: 8px;
                border-left: 4px solid #4A8C9B;
            }

            .template-westgatecontractors .addresses-section .section-title {
                font-weight: 600;
                color: #4A8C9B;
                margin-bottom: 10px;
                font-size: 16px;
            }

            .template-westgatecontractors .addresses-list {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .template-westgatecontractors .address-item {
                background: white;
                padding: 10px;
                border-radius: 5px;
                border: 1px solid #e9ecef;
                font-size: 14px;
                line-height: 1.4;
            }

            .template-westgatecontractors .logo-section {
                display: flex;
                justify-content: center;
                align-items: center;
                margin-left: 20px;
            }

            .template-westgatecontractors .company-logo {
                max-width: 120px;
                max-height: 150px;
                width: auto;
                height: auto;
                object-fit: contain;
            }

            .template-westgatecontractors .invoice-title {
                font-family: 'Georgia', 'Cambria', serif;
                font-size: 24px;
                font-weight: bold;
                color: black;
                text-align: center;
                text-transform: uppercase;
                margin-bottom: 25px;
                letter-spacing: 1px;
            }

            .template-westgatecontractors .client-table {
                width: 100%;
                border-collapse: collapse;
                border: 2px solid black;
                margin-bottom: 20px;
                background-color: transparent;
            }

            .template-westgatecontractors .client-table td {
                padding: 5px;
                border: 1px solid black;
                vertical-align: top;
            }

            .template-westgatecontractors .client-table .label {
                font-weight: bold;
                color: black;
                text-align: left;
                width: 50%;
            }

            .template-westgatecontractors .client-table .value {
                color: black;
                text-align: left;
                font-weight: normal;
                width: 50%;
            }

            .template-westgatecontractors .invoice-details {
                display: flex;
                gap: 30px;
                margin-bottom: 25px;
                justify-content: space-between;
            }

            .template-westgatecontractors .detail-row {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .template-westgatecontractors .detail-row .label {
                font-weight: bold;
                color: black;
            }

            .template-westgatecontractors .value-box {
                border: 2px solid black;
                padding: 8px 15px;
                background-color: white;
                color: black;
                font-weight: normal;
            }

            .template-westgatecontractors .table-container {
                margin-bottom: 30px;
            }

            .template-westgatecontractors .invoice-table {
                width: 100%;
                border-collapse: collapse;
                border: 2px solid black;
            }

            .template-westgatecontractors .invoice-table th,
            .template-westgatecontractors .invoice-table td {
                border: 1px solid black;
                padding: 12px 8px;
                text-align: left;
            }

            .template-westgatecontractors .main-header th {
                background-color: #4A8C9B;
                color: white;
                font-weight: bold;
                text-transform: uppercase;
                text-align: left;
            }

            .template-westgatecontractors .main-header th:last-child {
                text-align: center;
            }

            .template-westgatecontractors .sub-header th {
                background-color: transparent;
                color: rgb(0, 0, 0);
                font-weight: bold;
                text-transform: uppercase;
                text-align: center;
            }

            .template-westgatecontractors .sub-header th:nth-child(3),
            .template-westgatecontractors .sub-header th:nth-child(4) {
                text-align: center;
            }

            .template-westgatecontractors .invoice-table tbody td {
                background-color: transparent;
                color: black;
                text-align: center;
            }

            .template-westgatecontractors .invoice-table tbody td:nth-child(2) {
                text-align: center;
            }

            .template-westgatecontractors .invoice-table tbody td:nth-child(3),
            .template-westgatecontractors .invoice-table tbody td:nth-child(4) {
                text-align: center;
            }

            .template-westgatecontractors .subtotal-row td,
            .template-westgatecontractors .total-row td {
                background-color: transparent;
                border-top: 1px solid black;
                text-align: center;
            }

            .template-westgatecontractors .subtotal-label,
            .template-westgatecontractors .total-label {
                text-align: center;
                font-weight: bold;
            }

            .template-westgatecontractors .subtotal-amount,
            .template-westgatecontractors .total-amount {
                text-align: right;
                font-weight: bold;
            }

            .template-westgatecontractors .payment-section {
                display: flex;
                align-items: center;
                gap: 15px;
            }

            .template-westgatecontractors .payment-label {
                font-weight: bold;
                color: black;
                text-transform: uppercase;
            }

            .template-westgatecontractors .payment-box {
                border: 2px solid black;
                padding: 8px 15px;
                background-color: transparent;
                color: black;
            }

            /* Print Styles */
            @media print {
                body {
                    background: white;
                    padding: 0;
                    margin: 0;
                }

                .invoice-container {
                    max-width: none;
                    margin: 0;
                    box-shadow: none;
                }

                /* Hide browser header and footer */
                @page {
                    margin: 0;
                    size: A4;
                }

                /* Hide print headers and footers */
                @media print {
                    @page {
                        margin-top: 0;
                        margin-bottom: 0;
                        margin-left: 0;
                        margin-right: 0;
                    }
                }

                .template-handyforrepair .invoice-header {
                    background: #183655 !important;
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                }

                .template-handyforrepair .table-header {
                    background: #183655 !important;
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                }

                .template-handyforrepair .table-header th {
                    background: #183655 !important;
                    color: white !important;
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                }

                .template-handyforrepair .orange-curve {
                    background: #f97316 !important;
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                }

                .template-westgatecontractors .main-header th {
                    background-color: #4A8C9B !important;
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                }

                .template-westgatecontractors {
                    background-image: url('invoices/westgatecontractors/assets/images/background.png') !important;
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                    background-size: cover !important;
                    background-repeat: no-repeat !important;
                    background-position: center !important;
                }

                .template-handyrepaircenter {
                    display: flex;
                    flex-direction: column;
                    min-height: 100vh;
                }

                .template-handyrepaircenter .footer {
                    background-color: #000000 !important;
                    color: #ffffff !important;
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                    position: relative;
                    margin-top: auto;
                    page-break-inside: avoid;
                }

                .addresses-section {
                    background: #f8f9fa !important;
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                    page-break-inside: avoid;
                }

                .address-item {
                    background: white !important;
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                }
            }
        </style>
    </head>

    <body>
        <!-- Invoice Container -->
        <div class="invoice-container" id="invoiceContainer">
            <div class="text-center py-5" id="invoiceLoading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading invoice details...</p>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const invoiceNumber = '<?php echo $invoiceNumber; ?>';
                loadInvoiceDetails(invoiceNumber);

                // Auto print after loading
                setTimeout(() => {
                    // Hide browser header and footer
                    const style = document.createElement('style');
                    style.innerHTML = `
                    @page {
                        margin: 0;
                        size: A4;
                    }
                    @media print {
                        @page {
                            margin-top: 0;
                            margin-bottom: 0;
                            margin-left: 0;
                            margin-right: 0;
                        }
                    }
                `;
                    document.head.appendChild(style);

                    window.print();
                }, 2000);
            });

            async function loadInvoiceDetails(invoiceNumber) {
                try {
                    const response = await fetch(`assets/api/get_invoice_details.php?invoice_number=${invoiceNumber}`);
                    const result = await response.json();

                    if (result.success) {
                        displayInvoice(result.invoice, result.items, result.addresses || []);
                    } else {
                        showError('Failed to load invoice: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error loading invoice:', error);
                    showError('Network error. Please check your connection.');
                }
            }

            function displayInvoice(invoice, items, addresses = []) {
                const container = document.getElementById('invoiceContainer');

                // Determine template based on company
                let templateClass = 'template-handyforrepair';
                if (invoice.company_name === 'Handy Repair Center') {
                    templateClass = 'template-handyrepaircenter';
                } else if (invoice.company_name === 'West Gate Contractor') {
                    templateClass = 'template-westgatecontractors';
                }

                let template = '';

                if (invoice.company_name === 'Handy For Repair') {
                    template = generateHandyForRepairTemplate(invoice, items, addresses);
                } else if (invoice.company_name === 'Handy Repair Center') {
                    template = generateHandyRepairCenterTemplate(invoice, items, addresses);
                } else if (invoice.company_name === 'West Gate Contractor') {
                    template = generateWestGateContractorsTemplate(invoice, items, addresses);
                }

                container.innerHTML = `<div class="invoice-template ${templateClass}">${template}</div>`;
            }

            function generateHandyForRepairTemplate(invoice, items, addresses = []) {
                const itemsHTML = items.map((item, index) => `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.item}</td>
                    <td>${item.quantity}</td>
                    <td>${item.formatted_price}</td>
                    <td>${item.formatted_total}</td>
                </tr>
            `).join('');

                const addressesHTML = addresses.map((address, index) => `
                <div class="address-item">
                    <strong>${addresses.length === 1 ? 'Address:' : `Address ${index + 1}:`}</strong><br>
                    ${address.address}
                </div>
            `).join('');

                return `
                <div class="invoice-header">
                    <div class="header-left">
                        <div class="logo">
                            <img src="invoices/handyforrepair/assets/handyforrepair-01.png" alt="Handy For Repair Logo" class="logo-image">
                        </div>
                        <div class="company-name">
                            HANDY FOR REPAIR
                        </div>
                    </div>
                    <div class="header-right">
                        <div class="orange-curve"></div>
                    </div>
                </div>

                <div class="invoice-title-section">
                    <div class="invoice-title">
                        <h1>INVOICE</h1>
                    </div>
                    <div class="contact-info">
                        <p><strong>Contact At:</strong> (517) 273-6232</p>
                        <p><strong>Email:</strong> info@handyforrepair.com</p>
                    </div>
                </div>

                <div class="invoice-details">
                    <div class="client-info">
                        <h3>Invoice to:</h3>
                        <p>${invoice.invoice_to}</p>
                        ${addresses.length > 0 ? `
                        <div class="addresses-section">
                            <div class="addresses-list">
                                ${addressesHTML}
                            </div>
                        </div>
                        ` : ''}
                    </div>
                    <div class="invoice-meta">
                        <p><strong>Invoice#</strong> ${invoice.invoice_number}</p>
                        <p><strong>Date:</strong> ${invoice.formatted_date}</p>
                    </div>
                </div>

                <div class="services-table">
                    <table class="table">
                        <thead>
                            <tr class="table-header">
                                <th>S No</th>
                                <th>Description</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHTML}
                            <tr class="total-row">
                                <td></td>
                                <td colspan="3"><strong>Total</strong></td>
                                <td><strong>${invoice.formatted_amount}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="invoice-footer">
                    <div class="footer-left">
                        <h2>Thank You!</h2>
                        <p class="mt-4">2222 W Grand River Ave Ste A,</p>
                        <p>Okemos, MI 48864</p>
                    </div>
                    <img src="invoices/handyforrepair/assets/footerimage.png" alt="Handyman" class="footer-image">
                </div>
            `;
            }

            function generateHandyRepairCenterTemplate(invoice, items, addresses = []) {
                const itemsHTML = items.map(item => `
                <tr>
                    <td>${item.item}</td>
                    <td>${item.quantity}</td>
                    <td>${item.formatted_price}</td>
                    <td>${item.formatted_total}</td>
                </tr>
            `).join('');

                const addressesHTML = addresses.map((address, index) => `
                <div class="address-item">
                    <strong>${addresses.length === 1 ? 'Address:' : `Address ${index + 1}:`}</strong><br>
                    ${address.address}
                </div>
            `).join('');

                return `
                <div class="header">
                    <div class="logo-section">
                        <img src="invoices/handyrepaircenter/assets/images/Getfixready-LOGO_1.png" alt="HandyRepairCenter Logo" class="logo">
                    </div>
                </div>

                <div class="billing-section">
                    <div class="billing-left">
                        <div class="billing-label">Invoice To:</div>
                        <div class="billing-address">
                            <div>${invoice.invoice_to}</div>
                        </div>
                    </div>
                    <div class="invoice-details">
                        <div class="invoice-number">Invoice No. ${invoice.invoice_number}</div>
                        <div class="invoice-date">Date: ${invoice.formatted_date}</div>
                    </div>
                </div>

                ${addresses.length > 0 ? `
                <div class="addresses-section">
                    <div class="addresses-list">
                        ${addressesHTML}
                    </div>
                </div>
                ` : ''}

                <div class="table-container">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th class="item-col">Item</th>
                                <th class="qty-col">Quantity</th>
                                <th class="price-col">Unit Price</th>
                                <th class="total-col">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHTML}
                        </tbody>
                    </table>
                </div>

                <div class="summary-section">
                    <div class="subtotal">
                        <span>Subtotal: ${invoice.formatted_amount}</span>
                    </div>
                    <div class="total">
                        <span>Total: ${invoice.formatted_amount}</span>
                    </div>
                </div>

                <div class="footer">
                    <div class="footer-left">Thank you</div>
                    <div class="footer-right">PHONE NO: (469) 943-2530</div>
                </div>
            `;
            }

            function generateWestGateContractorsTemplate(invoice, items, addresses = []) {
                const itemsHTML = items.map(item => `
                <tr>
                    <td>${item.item}</td>
                    <td>${item.quantity}</td>
                    <td>${item.formatted_price}</td>
                    <td>${item.formatted_total}</td>
                </tr>
            `).join('');

                const addressesHTML = addresses.map((address, index) => `
                <tr>
                    <td class="label">${addresses.length === 1 ? 'Address:' : `Address ${index + 1}:`}</td>
                    <td class="value">${address.address}</td>
                </tr>
            `).join('');

                return `
                <div class="header">
                    <div class="company-info">
                        <h1 class="company-name">WEST GATE CONTRACTORS</h1>
                        <div class="contact-details">
                            <p>4539 N 22ND ST STE N, PHOENIX, AZ 85016</p>
                            <p>(623) 306-7613</p>
                            <p>contact@westgatecontractors.com</p>
                            <p>www.westgatecontractors.com</p>
                        </div>
                    </div>
                    <div class="logo-section">
                        <img src="invoices/westgatecontractors/assets/images/logo.jpg" alt="WEST GATE CONTRACTORS Logo" class="company-logo">
                    </div>
                </div>

                <h2 class="invoice-title">COMMERCIAL INVOICE</h2>

                <table class="client-table">
                    <tr>
                        <td class="label">Name or Company Name:</td>
                        <td class="value">${invoice.invoice_to}</td>
                    </tr>
                    ${addresses.length > 0 ? addressesHTML : ''}
                </table>

                <div class="invoice-details">
                    <div class="detail-row">
                        <span class="label">Invoice Number:</span>
                        <div class="value-box">${invoice.invoice_number}</div>
                    </div>
                    <div class="detail-row">
                        <span class="label">Date:</span>
                        <div class="value-box">${invoice.formatted_date}</div>
                    </div>
                </div>

                <div class="table-container">
                    <table class="invoice-table">
                        <thead>
                            <tr class="main-header">
                                <th colspan="3">DESCRIPTION</th>
                                <th>AMOUNT</th>
                            </tr>
                            <tr class="sub-header">
                                <th style="width: 45%;">PRODUCT</th>
                                <th style="width: 15%;">QUANTITY</th>
                                <th style="width: 17.5%;">UNIT PRICE</th>
                                <th style="width: 17.5%;">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${itemsHTML}
                        </tbody>
                        <tfoot>
                            <tr class="subtotal-row">
                                <td colspan="2"></td>
                                <td class="subtotal-label">SUBTOTAL</td>
                                <td class="subtotal-amount">${invoice.formatted_amount}</td>
                            </tr>
                            <tr class="total-row">
                                <td colspan="2"></td>
                                <td class="total-label">TOTAL</td>
                                <td class="total-amount">${invoice.formatted_amount}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="payment-section">
                    <div class="payment-label">PAYMENT METHOD:</div>
                    <div class="payment-box">Credit Card</div>
                </div>
            `;
            }

            function showError(message) {
                const container = document.getElementById('invoiceContainer');
                container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Error</h5>
                    <p>${message}</p>
                </div>
            `;
            }
        </script>
    </body>

    </html>
    <?php
    exit;
}

// For normal view mode, include admin panel
$pageTitle = "Invoice Details";
include 'header.php';
include 'sidebar.php';
?>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Dashboard Content -->
    <main class="dashboard-content">

        <?php if (!$printMode): ?>
            <!-- Back Button -->
            <div class="invoice-nav mb-3 d-flex justify-content-between">
                <a href="view-invoices.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Invoices
                </a>
                <button class="btn btn-primary" onclick="printInvoice()">
                    <i class="bi bi-printer"></i> Print Invoice
                </button>
            </div>
        <?php endif; ?>

        <!-- Invoice Container -->
        <div class="invoice-container" id="invoiceContainer">
            <div class="text-center py-5" id="invoiceLoading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading invoice details...</p>
            </div>
        </div>

        <?php if (!$printMode): ?>
            <!-- Action Buttons -->
        <?php endif; ?>
    </main>
</div>

<style>
    /* Invoice Navigation */
    .invoice-nav {
        margin-bottom: 1rem;
    }

    /* Invoice Actions */
    .invoice-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        padding: 2rem 0;
    }

    /* Invoice Container Styles */
    .invoice-container {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-medium);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    /* Company Specific Styles */
    .invoice-template {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: white;
        color: #333;
    }

    /* Handy For Repair Template */
    .template-handyforrepair {
        max-width: 800px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .template-handyforrepair .invoice-header {
        border-radius: 0px 0px 73px 0px;
        background: #183655;
        color: white;
        padding: 20px;
        position: relative;
    }

    .template-handyforrepair .header-left {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .template-handyforrepair .logo {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .template-handyforrepair .logo-image {
        width: 70px;
        height: 70px;
        object-fit: contain;
        position: relative;
        z-index: 2;
    }

    .template-handyforrepair .company-name {
        font-family: 'Cinzel', serif;
        font-size: 24px;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
    }

    .template-handyforrepair .header-right {
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100%;
    }

    .template-handyforrepair .orange-curve {
        position: absolute;
        top: 0;
        right: 0;
        width: 380%;
        height: 45%;
        background: #f97316;
        border-radius: 0 0 0 100px;
    }

    .template-handyforrepair .invoice-title-section {
        padding: 30px 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .template-handyforrepair .invoice-title h1 {
        font-size: 48px;
        font-weight: bold;
        color: #1f2937;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 3px;
    }

    .template-handyforrepair .contact-info {
        text-align: right;
        line-height: 1.6;
    }

    .template-handyforrepair .contact-info p {
        margin: 5px 0;
        font-size: 16px;
        color: #374151;
    }

    .template-handyforrepair .invoice-details {
        padding: 30px 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .template-handyforrepair .client-info h3 {
        font-size: 18px;
        font-weight: bold;
        color: #1f2937;
        margin-bottom: 10px;
        text-transform: uppercase;
    }

    .template-handyforrepair .client-info p {
        margin: 3px 0;
        font-weight: 500;
        font-size: 16px;
        color: #374151;
    }

    .template-handyforrepair .invoice-meta p {
        margin: 0px 0px 5px 0px;
        font-size: 16px;
        color: #374151;
    }

    .template-handyforrepair .services-table {
        padding: 50px 20px;
    }

    .addresses-section {
        margin-bottom: 50px;
    }

    .template-handyforrepair .table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        border: 1px solid #d1d5db;
    }

    .template-handyforrepair .table-header {
        background: #183655 !important;
        color: white !important;
    }

    .template-handyforrepair .table-header th {
        background: #183655 !important;
        color: white !important;
    }

    .template-handyforrepair .table-header th {
        padding: 15px 10px;
        text-align: left;
        font-weight: bold;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .template-handyforrepair .table tbody tr {
        border-bottom: 1px solid #d1d5db;
    }

    .template-handyforrepair .table tbody td {
        padding: 12px 10px;
        font-size: 14px;
        color: #374151;
        vertical-align: top;
        border: 1px solid #d1d5db;
        border-top: none;
    }

    .template-handyforrepair .table tbody td:nth-child(1),
    .template-handyforrepair .table tbody td:nth-child(3),
    .template-handyforrepair .table tbody td:nth-child(4),
    .template-handyforrepair .table tbody td:nth-child(5) {
        text-align: center;
    }

    .template-handyforrepair .total-row {
        border-top: 2px solid #d1d5db;
    }

    .template-handyforrepair .total-row td {
        font-weight: bold;
        font-size: 16px;
        color: #1f2937;
        padding: 15px 10px;
        border: 1px solid #d1d5db;
    }

    .template-handyforrepair .total-row td:nth-child(4),
    .template-handyforrepair .total-row td:nth-child(5) {
        text-align: center;
    }

    .template-handyforrepair .invoice-footer {
        background: linear-gradient(to bottom, white 50%, #e5e7eb 50%);
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        border-top: 1px solid #e5e7eb;
        min-height: 120px;
        position: relative;
        margin-top: auto;
    }

    .template-handyforrepair .footer-left {
        text-align: center;
        padding: 20px;
    }

    .template-handyforrepair .footer-left h2 {
        font-size: 32px;
        font-weight: bold;
        color: #1f2937;
        margin-bottom: 10px;
    }

    .template-handyforrepair .footer-left p {
        font-weight: 500;
        margin: 3px 0;
        font-size: 14px;
        color: #374151;
    }

    .template-handyforrepair .footer-image {
        width: 150px;
        height: 150px;
        object-fit: contain;
    }

    /* Handy Repair Center Template */
    .template-handyrepaircenter {
        max-width: 661px;
        margin: 0 auto;
        padding: 20px;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .template-handyrepaircenter .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 30px;
    }

    .template-handyrepaircenter .logo-section {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .template-handyrepaircenter .logo {
        height: 100px;
        width: 150px;
        object-fit: contain;
    }

    .template-handyrepaircenter .billing-section {
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .template-handyrepaircenter .billing-left {
        flex: 1;
    }

    .template-handyrepaircenter .billing-label {
        font-weight: bold;
        font-size: 20px;
        margin-bottom: 10px;
    }

    .template-handyrepaircenter .billing-address {
        font-size: 14px;
        line-height: 1.6;
    }

    .template-handyrepaircenter .billing-address div {
        margin-bottom: 2px;
    }

    .template-handyrepaircenter .invoice-details {
        text-align: right;
        font-size: 18px;
        margin-top: 0;
    }

    .template-handyrepaircenter .invoice-number {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .template-handyrepaircenter .invoice-date {
        color: #333333;
    }

    .template-handyrepaircenter .table-container {
        margin-bottom: 30px;
    }

    .template-handyrepaircenter .items-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 18px;
    }

    .template-handyrepaircenter .items-table thead {
        border-bottom: 1px solid #000000;
    }

    .template-handyrepaircenter .items-table th {
        text-align: left;
        padding: 10px 0;
        font-weight: normal;
        border-bottom: 1px solid #000000;
    }

    .template-handyrepaircenter .items-table tbody tr {
        border-bottom: 1px solid #000000;
    }

    .template-handyrepaircenter .items-table td {
        padding: 10px 0;
        vertical-align: top;
    }

    .template-handyrepaircenter .item-col {
        width: 50%;
    }

    .template-handyrepaircenter .qty-col {
        width: 15%;
    }

    .template-handyrepaircenter .price-col {
        width: 17.5%;
    }

    .template-handyrepaircenter .total-col {
        width: 17.5%;
    }

    .template-handyrepaircenter .summary-section {
        text-align: right;
        margin-bottom: 30px;
    }

    .template-handyrepaircenter .subtotal {
        font-size: 14px;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #000000;
    }

    .template-handyrepaircenter .total {
        font-size: 16px;
        font-weight: bold;
    }

    .template-handyrepaircenter .footer {
        background-color: #000000;
        color: #ffffff;
        padding: 36px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 14px;
        position: relative;
        margin-top: auto;
    }

    /* West Gate Contractors Template */
    .template-westgatecontractors {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        background-image: url('invoices/westgatecontractors/assets/images/background.png');
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
    }

    .template-westgatecontractors .header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 30px;
    }

    .template-westgatecontractors .company-info {
        flex: 1;
    }

    .template-westgatecontractors .company-name {
        font-family: 'Times New Roman', serif;
        font-size: 28px;
        font-weight: bold;
        color: black;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: -0.5px;
    }

    .template-westgatecontractors .contact-details p {
        font-size: 14px;
        color: black;
        margin-bottom: 5px;
    }

    .template-westgatecontractors .logo-section {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-left: 20px;
    }

    .template-westgatecontractors .company-logo {
        max-width: 120px;
        max-height: 150px;
        width: auto;
        height: auto;
        object-fit: contain;
    }

    .template-westgatecontractors .invoice-title {
        font-family: 'Georgia', 'Cambria', serif;
        font-size: 24px;
        font-weight: bold;
        color: black;
        text-align: center;
        text-transform: uppercase;
        margin-bottom: 25px;
        letter-spacing: 1px;
    }

    .template-westgatecontractors .client-table {
        width: 100%;
        border-collapse: collapse;
        border: 2px solid black;
        margin-bottom: 20px;
        background-color: transparent;
    }

    .template-westgatecontractors .client-table td {
        padding: 5px;
        border: 1px solid black;
        vertical-align: top;
    }

    .template-westgatecontractors .client-table .label {
        font-weight: bold;
        color: black;
        text-align: left;
        width: 50%;
    }

    .template-westgatecontractors .client-table .value {
        color: black;
        text-align: left;
        font-weight: normal;
        width: 50%;
    }

    .template-westgatecontractors .invoice-details {
        display: flex;
        gap: 30px;
        margin-bottom: 25px;
        justify-content: space-between;
    }

    .template-westgatecontractors .detail-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .template-westgatecontractors .detail-row .label {
        font-weight: bold;
        color: black;
    }

    .template-westgatecontractors .value-box {
        border: 2px solid black;
        padding: 8px 15px;
        background-color: white;
        color: black;
        font-weight: normal;
    }

    .template-westgatecontractors .table-container {
        margin-bottom: 30px;
    }

    .template-westgatecontractors .invoice-table {
        width: 100%;
        border-collapse: collapse;
        border: 2px solid black;
    }

    .template-westgatecontractors .invoice-table th,
    .template-westgatecontractors .invoice-table td {
        border: 1px solid black;
        padding: 12px 8px;
        text-align: left;
    }

    .template-westgatecontractors .main-header th {
        background-color: #4A8C9B;
        color: white;
        font-weight: bold;
        text-transform: uppercase;
        text-align: left;
    }

    .template-westgatecontractors .main-header th:last-child {
        text-align: center;
    }

    .template-westgatecontractors .sub-header th {
        background-color: transparent;
        color: rgb(0, 0, 0);
        font-weight: bold;
        text-transform: uppercase;
        text-align: center;
    }

    .template-westgatecontractors .sub-header th:nth-child(3),
    .template-westgatecontractors .sub-header th:nth-child(4) {
        text-align: center;
    }

    .template-westgatecontractors .invoice-table tbody td {
        background-color: transparent;
        color: black;
        text-align: center;
    }

    .template-westgatecontractors .invoice-table tbody td:nth-child(2) {
        text-align: center;
    }

    .template-westgatecontractors .invoice-table tbody td:nth-child(3),
    .template-westgatecontractors .invoice-table tbody td:nth-child(4) {
        text-align: center;
    }

    .template-westgatecontractors .subtotal-row td,
    .template-westgatecontractors .total-row td {
        background-color: transparent;
        border-top: 1px solid black;
        text-align: center;
    }

    .template-westgatecontractors .subtotal-label,
    .template-westgatecontractors .total-label {
        text-align: center;
        font-weight: bold;
    }

    .template-westgatecontractors .subtotal-amount,
    .template-westgatecontractors .total-amount {
        text-align: right;
        font-weight: bold;
    }

    .template-westgatecontractors .payment-section {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .template-westgatecontractors .payment-label {
        font-weight: bold;
        color: black;
        text-transform: uppercase;
    }

    .template-westgatecontractors .payment-box {
        border: 2px solid black;
        padding: 8px 15px;
        background-color: transparent;
        color: black;
    }

    /* Print Styles */
    @media print {
        body {
            background: white;
            padding: 0;
        }

        .invoice-container {
            box-shadow: none;
            border-radius: 0;
            max-width: none;
        }

        /* Hide browser header and footer */
        @page {
            margin: 0;
            size: A4;
        }

        /* Hide print headers and footers */
        @media print {
            @page {
                margin-top: 0;
                margin-bottom: 0;
                margin-left: 0;
                margin-right: 0;
            }
        }

        .invoice-nav,
        .invoice-actions {
            display: none !important;
        }

        .template-handyforrepair .invoice-header {
            background: #183655 !important;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }

        .template-handyforrepair .table-header {
            background: #183655 !important;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }

        .template-handyforrepair .table-header th {
            background: #183655 !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }

        .template-handyforrepair .orange-curve {
            background: #f97316 !important;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }

        .template-westgatecontractors .main-header th {
            background-color: #4A8C9B !important;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .invoice-actions {
            flex-direction: column;
            align-items: center;
        }

        .template-handyforrepair .invoice-title-section {
            flex-direction: column;
            gap: 0px;
        }

        .template-handyforrepair .orange-curve {
            width: 180%;
            height: 40%;
        }

        .template-handyforrepair .contact-info {
            text-align: left;
        }

        .template-handyforrepair .invoice-details {
            flex-direction: column;
            gap: 20px;
        }

        .template-handyforrepair .invoice-meta {
            text-align: left;
        }

        .template-handyforrepair {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .template-handyforrepair .invoice-footer {
            flex-direction: column;
            gap: 20px;
            text-align: center;
            position: relative;
            margin-top: auto;
            page-break-inside: avoid;
        }

        .template-handyforrepair .table {
            font-size: 12px;
        }

        .template-handyforrepair .table-header th,
        .template-handyforrepair .table tbody td {
            padding: 8px 5px;
        }

        .template-handyforrepair .company-name {
            font-size: 19px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const invoiceNumber = '<?php echo $invoiceNumber; ?>';
        const printMode = <?php echo $printMode ? 'true' : 'false'; ?>;

        // Load invoice details
        loadInvoiceDetails(invoiceNumber);

        // Auto print if in print mode
        if (printMode) {
            setTimeout(() => {
                // Hide browser header and footer
                const style = document.createElement('style');
                style.innerHTML = `
                @page {
                    margin: 0;
                    size: A4;
                }
                @media print {
                    @page {
                        margin-top: 0;
                        margin-bottom: 0;
                        margin-left: 0;
                        margin-right: 0;
                    }
                }
            `;
                document.head.appendChild(style);

                window.print();
            }, 1000);
        }

        async function loadInvoiceDetails(invoiceNumber) {
            try {
                const response = await fetch(`assets/api/get_invoice_details.php?invoice_number=${invoiceNumber}`);
                const result = await response.json();

                if (result.success) {
                    displayInvoice(result.invoice, result.items, result.addresses || []);
                } else {
                    showError('Failed to load invoice: ' + result.message);
                }
            } catch (error) {
                console.error('Error loading invoice:', error);
                showError('Network error. Please check your connection.');
            }
        }

        function displayInvoice(invoice, items, addresses = []) {
            const container = document.getElementById('invoiceContainer');

            // Determine template based on company
            let templateClass = 'template-handyforrepair';
            if (invoice.company_name === 'Handy Repair Center') {
                templateClass = 'template-handyrepaircenter';
            } else if (invoice.company_name === 'West Gate Contractor') {
                templateClass = 'template-westgatecontractors';
            }

            let template = '';

            if (invoice.company_name === 'Handy For Repair') {
                template = generateHandyForRepairTemplate(invoice, items, addresses);
            } else if (invoice.company_name === 'Handy Repair Center') {
                template = generateHandyRepairCenterTemplate(invoice, items, addresses);
            } else if (invoice.company_name === 'West Gate Contractor') {
                template = generateWestGateContractorsTemplate(invoice, items, addresses);
            }

            container.innerHTML = `<div class="invoice-template ${templateClass}">${template}</div>`;
        }

        function generateHandyForRepairTemplate(invoice, items, addresses = []) {
            const itemsHTML = items.map((item, index) => `
            <tr>
                <td>${index + 1}</td>
                <td>${item.item}</td>
                <td>${item.quantity}</td>
                <td>${item.formatted_price}</td>
                <td>${item.formatted_total}</td>
            </tr>
        `).join('');

            const addressesHTML = addresses.map((address, index) => `
            <div class="address-item">
                <strong>${addresses.length === 1 ? 'Address:' : `Address ${index + 1}:`}</strong><br>
                ${address.address}
            </div>
        `).join('');

            return `
            <div class="invoice-header">
                <div class="header-left">
                    <div class="logo">
                        <img src="invoices/handyforrepair/assets/handyforrepair-01.png" alt="Handy For Repair Logo" class="logo-image">
                    </div>
                    <div class="company-name">
                        HANDY FOR REPAIR
                    </div>
                </div>
                <div class="header-right">
                    <div class="orange-curve"></div>
                </div>
            </div>

            <div class="invoice-title-section">
                <div class="invoice-title">
                    <h1>INVOICE</h1>
                </div>
                <div class="contact-info">
                    <p><strong>Contact At:</strong> (517) 273-6232</p>
                    <p><strong>Email:</strong> info@handyforrepair.com</p>
                </div>
            </div>

            <div class="invoice-details">
                <div class="client-info">
                    <h3>Invoice to:</h3>
                    <p>${invoice.invoice_to}</p>
                    ${addresses.length > 0 ? `
                    <div class="addresses-section">
                        <div class="addresses-list">
                            ${addressesHTML}
                        </div>
                    </div>
                    ` : ''}
                </div>
                <div class="invoice-meta">
                    <p><strong>Invoice#</strong> ${invoice.invoice_number}</p>
                    <p><strong>Date:</strong> ${invoice.formatted_date}</p>
                </div>
            </div>

            <div class="services-table">
                <table class="table">
                    <thead>
                        <tr class="table-header">
                            <th>S No</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHTML}
                        <tr class="total-row">
                            <td></td>
                            <td colspan="3"><strong>Total</strong></td>
                            <td><strong>${invoice.formatted_amount}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="invoice-footer">
                <div class="footer-left">
                    <h2>Thank You!</h2>
                    <p class="mt-4">2222 W Grand River Ave Ste A,</p>
                    <p>Okemos, MI 48864</p>
                </div>
                <img src="invoices/handyforrepair/assets/footerimage.png" alt="Handyman" class="footer-image">
            </div>
        `;
        }

        function generateHandyRepairCenterTemplate(invoice, items, addresses = []) {
            const itemsHTML = items.map(item => `
            <tr>
                <td>${item.item}</td>
                <td>${item.quantity}</td>
                <td>${item.formatted_price}</td>
                <td>${item.formatted_total}</td>
            </tr>
        `).join('');

            const addressesHTML = addresses.map((address, index) => `
            <div class="address-item">
                <strong>${addresses.length === 1 ? 'Address:' : `Address ${index + 1}:`}</strong><br>
                ${address.address}
            </div>
        `).join('');

            return `
            <div class="header">
                <div class="logo-section">
                    <img src="invoices/handyrepaircenter/assets/images/Getfixready-LOGO_1.png" alt="HandyRepairCenter Logo" class="logo">
                </div>
            </div>

            <div class="billing-section">
                <div class="billing-left">
                    <div class="billing-label">Invoice To:</div>
                    <div class="billing-address">
                        <div>${invoice.invoice_to}</div>
                    </div>
                </div>
                <div class="invoice-details">
                    <div class="invoice-number">Invoice No. ${invoice.invoice_number}</div>
                    <div class="invoice-date">Date: ${invoice.formatted_date}</div>
                </div>
            </div>

            ${addresses.length > 0 ? `
            <div class="addresses-section">
                <div class="addresses-list">
                    ${addressesHTML}
                </div>
            </div>
            ` : ''}

            <div class="table-container">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th class="item-col">Item</th>
                            <th class="qty-col">Quantity</th>
                            <th class="price-col">Unit Price</th>
                            <th class="total-col">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHTML}
                    </tbody>
                </table>
            </div>

            <div class="summary-section">
                <div class="subtotal">
                    <span>Subtotal: ${invoice.formatted_amount}</span>
                </div>
                <div class="total">
                    <span>Total: ${invoice.formatted_amount}</span>
                </div>
            </div>

            <div class="footer">
                <div class="footer-left">Thank you</div>
                <div class="footer-right">PHONE NO: (469) 943-2530</div>
            </div>
        `;
        }

        function generateWestGateContractorsTemplate(invoice, items, addresses = []) {
            const itemsHTML = items.map(item => `
            <tr>
                <td>${item.item}</td>
                <td>${item.quantity}</td>
                <td>${item.formatted_price}</td>
                <td>${item.formatted_total}</td>
            </tr>
        `).join('');

            const addressesHTML = addresses.map((address, index) => `
            <tr>
                <td class="label">${addresses.length === 1 ? 'Address:' : `Address ${index + 1}:`}</td>
                <td class="value">${address.address}</td>
            </tr>
        `).join('');

            return `
            <div class="header">
                <div class="company-info">
                    <h1 class="company-name">WEST GATE CONTRACTORS</h1>
                    <div class="contact-details">
                        <p>4539 N 22ND ST STE N, PHOENIX, AZ 85016</p>
                        <p>(623) 306-7613</p>
                        <p>contact@westgatecontractors.com</p>
                        <p>www.westgatecontractors.com</p>
                    </div>
                </div>
                <div class="logo-section">
                    <img src="invoices/westgatecontractors/assets/images/logo.jpg" alt="WEST GATE CONTRACTORS Logo" class="company-logo">
                </div>
            </div>

            <h2 class="invoice-title">COMMERCIAL INVOICE</h2>

            <table class="client-table">
                <tr>
                    <td class="label">Name or Company Name:</td>
                    <td class="value">${invoice.invoice_to}</td>
                </tr>
                ${addresses.length > 0 ? addressesHTML : ''}
            </table>

            <div class="invoice-details">
                <div class="detail-row">
                    <span class="label">Invoice Number:</span>
                    <div class="value-box">${invoice.invoice_number}</div>
                </div>
                <div class="detail-row">
                    <span class="label">Date:</span>
                    <div class="value-box">${invoice.formatted_date}</div>
                </div>
            </div>

            <div class="table-container">
                <table class="invoice-table">
                    <thead>
                        <tr class="main-header">
                            <th colspan="3">DESCRIPTION</th>
                            <th>AMOUNT</th>
                        </tr>
                        <tr class="sub-header">
                            <th style="width: 45%;">PRODUCT</th>
                            <th style="width: 15%;">QUANTITY</th>
                            <th style="width: 17.5%;">UNIT PRICE</th>
                            <th style="width: 17.5%;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHTML}
                    </tbody>
                    <tfoot>
                        <tr class="subtotal-row">
                            <td colspan="2"></td>
                            <td class="subtotal-label">SUBTOTAL</td>
                            <td class="subtotal-amount">${invoice.formatted_amount}</td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="2"></td>
                            <td class="total-label">TOTAL</td>
                            <td class="total-amount">${invoice.formatted_amount}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="payment-section">
                <div class="payment-label">PAYMENT METHOD:</div>
                <div class="payment-box">Credit Card</div>
            </div>
        `;
        }

        function showError(message) {
            const container = document.getElementById('invoiceContainer');
            container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Error</h5>
                <p>${message}</p>
            </div>
        `;
        }

        // Print function
        window.printInvoice = function () {
            // Hide browser header and footer before opening print window
            const style = document.createElement('style');
            style.innerHTML = `
            @page {
                margin: 0;
                size: A4;
            }
            @media print {
                @page {
                    margin-top: 0;
                    margin-bottom: 0;
                    margin-left: 0;
                    margin-right: 0;
                }
            }
        `;
            document.head.appendChild(style);

            window.open(`view-invoice.php?invoice=${invoiceNumber}&print=1`, '_blank');
        };

        // Download PDF function (placeholder)
        window.downloadPDF = function () {
            alert('PDF download functionality will be implemented here');
        };
    });
</script>

<?php include 'footer.php'; ?>