<?php
require_once 'php/includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']); // safe output
$user_role = $_SESSION['user_role'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/fms-style.css">
    <link rel="shortcut icon" href="./assets/favicon/SIS.ico" type="image/x-icon">
    <title>Finance Management</title>
</head>
<style>
    /* Additional styles for the invoice */
	.invoice-container {
            background-color: var(--body-color);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            width: var(--module-content-width);
            margin-top: 20px;
        }

        .invoice-header {
            text-align: center;
        }

        .invoice-header h2 {
            margin: 0;
            color: var(--primary-color);
        }

        .invoice-details {
            margin: 20px 0;
        }

        .invoice-details p {
            margin: 5px 0;
            color: #666;
        }

        /* Modal styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0, 0, 0, 0.5); /* Black w/ opacity */
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: var(--body-color);
            border-radius: 12px;
            padding: 25px 30px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #888;
        }

        .close:hover {
            color: var(--primary-color);
        }

        /* Print Button */
        #print-invoice {
            padding: 10px 20px;
            background-color: var(--btn-blue);
            color: var(--body-color);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        #print-invoice:hover {
            background-color: #0056b3;
        }

        /* Invoice Form */
        .invoice-form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 20px;
        }

        .invoice-form input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .invoice-form button {
            padding: 10px;
            background-color: #0056b3;
            color: var(--body-color);
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .invoice-form button:hover {
            background-color: #0a3981;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: var(--body-color);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
            border-radius: 12px;
            overflow: hidden;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ececec;
        }

        th {
            background-color: var(--primary-color-light);
            color: var(--body-color);
            font-weight: 600;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: var(--sidebar-color);
            transition: var(--tran-03);
        }
</style>

<body>
    <header>
        <div class="header-content">
            <img src="./assets/img/SIS-logo.png" alt="Student Information System Logo">
            <div>
                <h1>Student Information System</h1>
                <h2>Finance</h2>
            </div>
        </div>
    </header>
    <main class="main">
        <aside class="sidebar">
            <nav aria-label="Main navigation">
                <ul>
                    <li><a href="student-fees.php"><span class="mdi mdi-account-school-outline"></span><span>Student Fees</span></a></li>
                    <li><a href="billing-invoicing.php"><span class="mdi mdi-invoice-list-outline"></span><span>Billing Invoicing</span></a></li>
                    <li><a href="scholarship.php"><span class="mdi mdi-certificate-outline"></span><span>Scholarship</span></a></li>
                    <li><a href="refund.php"><span class="mdi mdi-cash-refund"></span><span>Refund</span></a></li>

                    <!-- Admin-only Modules -->
                    <?php if ($user_role === 'admin'): ?>
                        <li><a href="financial-report.php"><span class="mdi mdi-finance"></span><span>Financial Report</span></a></li>
                        <li><a href="audit-trail.php"><span class="mdi mdi-monitor-eye"></span><span>Audit Trail</span></a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <nav aria-label="User options">
                <ul>
                    <li><a href="./php/logout.php"><span class="mdi mdi-logout"></span> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>
        <section class="content">
            <div class="content-header">
                <button class="js-sidenav-toggle" aria-label="Toggle navigation menu">
                    <span class="mdi mdi-menu"></span>
                </button>
                <h3>Billing Invoice</h3>
            </div>
            <article class="module-content">
			<div class="invoice-form">
                    <input type="text" id="student-name-input" placeholder="Student Name" />
                    <input type="text" id="course-name-input" placeholder="Course Name" />
                    <input type="number" id="amount-due-input" placeholder="Amount Due" />
                    <button id="add-invoice">Add Invoice</button>
                    <button id="update-invoice" style="display:none;">Update Invoice</button>
                </div>
                <table id="invoice-table">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Amount Due</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Invoices will be populated here -->
                    </tbody>
                </table>
            </article>
        </section>

    </main>

    <footer>
        <address>
            <p>For inquiries please contact 000-0000<br>
                Email: sisfinance3220@gmail.com</p>
        </address>
        <p>&copy; 2025 Student Information System<br>All Rights Reserved</p>
    </footer>

	<div id="invoice-preview-modal" class="modal">
        <div class="modal-content">
            <span class="close" id="close-preview">&times;</span>
            <div id="modal-invoice-content"></div>
            <button id="print-invoice">Print Invoice</button>
        </div>
    </div>

    <script src="./assets/js/fms-script.js"></script>
    <script>
		

// Dummy invoice data
let invoices = [];
let currentInvoiceIndex = null;

// Function to render invoices in the table
function renderInvoices() {
    const invoiceTableBody = document.querySelector('#invoice-table tbody');
    invoiceTableBody.innerHTML = ''; // Clear existing rows

    invoices.forEach((invoice, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${invoice.studentName}</td>
            <td>${invoice.courseName}</td>
            <td>$${invoice.amountDue.toFixed(2)}</td>
            <td>
                <button onclick="editInvoice(${index})">Edit</button>
                <button onclick="deleteInvoice(${index})">Delete</button>
                <button onclick="viewInvoice(${index})">View</button>
            </td>
        `;
        invoiceTableBody.appendChild(row);
    });
}

// Function to add a new invoice
function addInvoice() {
    const studentName = document.getElementById('student-name-input').value;
    const courseName = document.getElementById('course-name-input').value;
    const amountDue = parseFloat(document.getElementById('amount-due-input').value);

    if (studentName && courseName && !isNaN(amountDue)) {
        invoices.push({ studentName, courseName, amountDue });
        renderInvoices();
        clearForm();
    } else {
        alert('Please fill in all fields correctly.');
    }
}

// Function to edit an existing invoice
function editInvoice(index) {
    currentInvoiceIndex = index;
    const invoice = invoices[index];

    document.getElementById('student-name-input').value = invoice.studentName;
    document.getElementById('course-name-input').value = invoice.courseName;
    document.getElementById('amount-due-input').value = invoice.amountDue;

    document.getElementById('add-invoice').style.display = 'none';
    document.getElementById('update-invoice').style.display = 'block';
}

// Function to update the invoice
function updateInvoice() {
    const studentName = document.getElementById('student-name-input').value;
    const courseName = document.getElementById('course-name-input').value;
    const amountDue = parseFloat(document.getElementById('amount-due-input').value);

    if (studentName && courseName && !isNaN(amountDue) && currentInvoiceIndex !== null) {
        invoices[currentInvoiceIndex] = { studentName, courseName, amountDue };
        renderInvoices();
        clearForm();
        document.getElementById('add-invoice').style.display = 'block';
        document.getElementById('update-invoice').style.display = 'none';
        currentInvoiceIndex = null;
    } else {
        alert('Please fill in all fields correctly.');
    }
}

// Function to delete an invoice
function deleteInvoice(index) {
    if (confirm('Are you sure you want to delete this invoice?')) {
        invoices.splice(index, 1);
        renderInvoices();
    }
}

// Function to view an invoice in a modal
function viewInvoice(index) {
    const invoice = invoices[index];
    const modalContent = `
        <h2>Invoice Preview</h2>
        <p><strong>Student Name:</strong> ${invoice.studentName}</p>
        <p><strong>Course:</strong> ${invoice.courseName}</p>
        <p><strong>Amount Due:</strong> $${invoice.amountDue.toFixed(2)}</p>
    `;
    document.getElementById('modal-invoice-content').innerHTML = modalContent;
    document.getElementById('invoice-preview-modal').style.display = 'flex';
}

// Function to print the invoice
function printInvoice() {
    const printContent = document.getElementById('modal-invoice-content').innerHTML;
    const newWindow = window.open('', '', 'height=600,width=800');
    newWindow.document.write('<html><head><title>Print Invoice</title></head><body>');
    newWindow.document.write(printContent);
    newWindow.document.write('</body></html>');
    newWindow.document.close();
    newWindow.print();
}

// Function to clear the form
function clearForm() {
    document.getElementById('student-name-input').value = '';
    document.getElementById('course-name-input').value = '';
    document.getElementById('amount-due-input').value = '';
}

// Event listeners for buttons
document.getElementById('add-invoice').addEventListener('click', addInvoice);
document.getElementById('update-invoice').addEventListener('click', updateInvoice);
document.getElementById('print-invoice').addEventListener('click', printInvoice);

// Modal close functionality
const closePreview = document.getElementById('close-preview');
closePreview.onclick = function() {
    document.getElementById('invoice-preview-modal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('invoice-preview-modal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

// Initial render
renderInvoices();
</script>



</body>

</html>