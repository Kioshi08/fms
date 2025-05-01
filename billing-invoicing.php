<?php
include 'php/includes/db.php';
require_once 'php/includes/auth.php';
requireLogin();

// Fetch existing students for the dropdown
$students_stmt = $pdo->query("SELECT student_id FROM students");
$students = $students_stmt->fetchAll();

// Handle form submission for creating a new invoice
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_invoice'])) {
    $student_id = $_POST['student_id'];
    $invoice_number = $_POST['invoice_number'];
    $total_amount = $_POST['total_amount'];
    $status = $_POST['status'];
    $date_created = date('Y-m-d H:i:s'); // Automatically set the current date and time

    // Prepare and execute the statement
    $stmt = $pdo->prepare("INSERT INTO invoices (student_id, invoice_number, date_created, status, total_amount) VALUES (?, ?, ?, ?, ?)");
    
    // Execute the statement
    if ($stmt->execute([$student_id, $invoice_number, $date_created, $status, $total_amount])) {
        echo "New invoice created successfully";
    } else {
        echo "Error: Could not create invoice.";
    }
}

// Handle form submission for processing payments
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['process_payment'])) {
    $invoice_id = $_POST['invoice_id'];
    $payment_amount = $_POST['payment_amount'];

    // Update the invoice status and total amount
    $stmt = $pdo->prepare("UPDATE invoices SET total_amount = total_amount - ?, status = CASE WHEN total_amount - ? <= 0 THEN 'paid' ELSE 'partial' END WHERE invoice_id = ?");
    if ($stmt->execute([$payment_amount, $payment_amount, $invoice_id])) {
        echo "Payment processed successfully.";
    } else {
        echo "Error: Could not process payment.";
    }
}

// Handle form submission for editing an invoice
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_invoice'])) {
    $invoice_id = $_POST['invoice_id'];
    $total_amount = $_POST['total_amount'];
    $status = $_POST['status'];

    // Update the invoice details
    $stmt = $pdo->prepare("UPDATE invoices SET total_amount = ?, status = ? WHERE invoice_id = ?");
    if ($stmt->execute([$total_amount, $status, $invoice_id])) {
        echo "Invoice updated successfully.";
    } else {
        echo "Error: Could not update invoice.";
    }
}

// Handle invoice deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_invoice'])) {
    $invoice_id = $_POST['invoice_id'];

    // Delete the invoice
    $stmt = $pdo->prepare("DELETE FROM invoices WHERE invoice_id = ?");
    if ($stmt->execute([$invoice_id])) {
        echo "Invoice deleted successfully.";
    } else {
        echo "Error: Could not delete invoice.";
    }
}

// Fetch existing invoices with optional filtering
$filter_student_id = isset($_GET['student_id']) ? $_GET['student_id'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT * FROM invoices WHERE 1=1";
$params = [];

if ($filter_student_id) {
    $query .= " AND student_id = ?";
    $params[] = $filter_student_id;
}

if ($filter_status) {
    $query .= " AND status = ?";
    $params[] = $filter_status;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$invoices = $stmt->fetchAll();
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
    .billing-container {
    background-color: var(--body-color);
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    width: var(--module-content-width);
}

.billing-container h2 {
    color: var(--primary-color);
}

.billing-container form {
    display: flex;
    flex-direction: column;
}

.billing-container label {
    margin: 10px 0 5px;
}

.billing-container input,
.billing-container select {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.billing-container button {
    padding: 10px;
    margin-top: 10px;
    background-color: var(--primary-color);
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.billing-container button:hover {
    background-color: var(--primary-color-light);
}

.billing-container table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.billing-container th,
.billing-container td {
    padding: 12px;
    border: 1px solid #ececec;
    text-align: left;
}

.billing-container th {
    background-color: var(--primary-color-light);
    color: var(--body-color);
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
            <div class="billing-container">
        <h2>Billing Invoicing</h2>
        
        <!-- Create Invoice Form -->
        <form method="POST" action="">
            <label for="student_id">Student ID:</label>
            <select id="student_id" name="student_id" required>
                <option value="">Select a student</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?php echo $student['student_id']; ?>"><?php echo $student['student_id']; ?></option>
                <?php endforeach; ?>
            </select>

            <label for="invoice_number">Invoice Number:</label>
            <input type="text" id="invoice_number" name="invoice_number" required>

            <label for="total_amount">Total Amount:</label>
            <input type="number" id="total_amount" name="total_amount" required>

            <label for="status">Status:</label>
            <select id="status" name="status">
                <option value="paid">Paid</option>
                <option value="partial">Partial</option>
                <option value="not_paid">Not Paid</option>
            </select>

            <button type="submit" name="create_invoice">Create Invoice</button>
        </form>

        <!-- Filter Invoices -->
        <h3>Filter Invoices</h3>
        <form method="GET" action="">
            <label for="filter_student_id">Student ID:</label>
            <input type="text" id="filter_student_id" name="student_id" value="<?php echo htmlspecialchars($filter_student_id); ?>">

            <label for="filter_status">Status:</label>
            <select id="filter_status" name="status">
                <option value="">All</option>
                <option value="paid" <?php if ($filter_status == 'paid') echo 'selected'; ?>>Paid</option>
                <option value="partial" <?php if ($filter_status == 'partial') echo 'selected'; ?>>Partial</option>
                <option value="not_paid" <?php if ($filter_status == 'not_paid') echo 'selected'; ?>>Not Paid</option>
            </select>

            <button type="submit">Filter</button>
        </form>

        <h3>Existing Invoices</h3>
        <table>
            <tr>
                <th>Invoice ID</th>
                <th>Student ID</th>
                <th>Invoice Number</th>
                <th>Date Created</th>
                <th>Status</th>
                <th>Total Amount</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($invoices as $row): ?>
            <tr>
                <td><?php echo $row['invoice_id']; ?></td>
                <td><?php echo $row['student_id']; ?></td>
                <td><?php echo $row['invoice_number']; ?></td>
                <td><?php echo $row['date_created']; ?></td>
                <td><?php echo $row['status']; ?></td>
                <td><?php echo $row['total_amount']; ?></td>
                <td>
                    <!-- Payment Processing Form -->
                    <form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="invoice_id" value="<?php echo $row['invoice_id']; ?>">
                        <input type="number" name="payment_amount" placeholder="Payment Amount" required>
                        <button type="submit" name="process_payment">Pay</button>
                    </form>
                    <!-- Edit Invoice Form -->
                    <form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="invoice_id" value="<?php echo $row['invoice_id']; ?>">
                        <input type="number" name="total_amount" placeholder="New Total Amount" required>
                        <select name="status" required>
                            <option value="paid">Paid</option>
                            <option value="partial">Partial</option>
                            <option value="not_paid">Not Paid</option>
                        </select>
                        <button type="submit" name="edit_invoice">Edit</button>
                    </form>
                    <!-- Delete Invoice Form -->
                    <form method="POST" action="" style="display:inline;">
                        <input type="hidden" name="invoice_id" value="<?php echo $row['invoice_id']; ?>">
                        <button type="submit" name="delete_invoice" onclick="return confirm('Are you sure you want to delete this invoice?');">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
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

    <script src="./assets/js/fms-script.js"></script>
</body>
</html>

