<?php
include 'php/includes/db.php';
require_once 'php/includes/auth.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']); // safe output
$user_role = $_SESSION['user_role'];

// Fetch existing students for the dropdown
$students_stmt = $pdo->query("SELECT student_id, CONCAT(first_name, ' ', last_name) AS full_name FROM students");
$students = $students_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission for creating a new fee
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_fee'])) {
    $student_id = $_POST['student_id'];
    $fee_type = $_POST['fee_type'];
    $amount = $_POST['amount'];
    $discount_amount = $_POST['discount_amount'];

    // Prepare and execute the statement
    $stmt = $pdo->prepare("INSERT INTO student_fees (student_id, fee_type, amount, discount_amount) VALUES (?, ?, ?, ?)");
    
    // Execute the statement
    if ($stmt->execute([$student_id, $fee_type, $amount, $discount_amount])) {
        // Update fee summary
        updateFeeSummary($student_id, $amount, $discount_amount);
        echo "New fee added successfully";
    } else {
        echo "Error: Could not add fee.";
    }
}

// Handle form submission for editing a fee
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_fee'])) {
    $fee_id = $_POST['fee_id'];
    $amount = $_POST['amount'];
    $discount_amount = $_POST['discount_amount'];

    // Update the fee details
    $stmt = $pdo->prepare("UPDATE student_fees SET amount = ?, discount_amount = ? WHERE fee_id = ?");
    if ($stmt->execute([$amount, $discount_amount, $fee_id])) {
        echo "Fee updated successfully.";
    } else {
        echo "Error: Could not update fee.";
    }
}

// Handle fee deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_fee'])) {
    $fee_id = $_POST['fee_id'];

    // Delete the fee
    $stmt = $pdo->prepare("DELETE FROM student_fees WHERE fee_id = ?");
    if ($stmt->execute([$fee_id])) {
        echo "Fee deleted successfully.";
    } else {
        echo "Error: Could not delete fee.";
    }
}

// Fetch existing fees with optional filtering
$filter_student_id = isset($_GET['student_id']) ? $_GET['student_id'] : '';

$query = "SELECT sf.fee_id, sf.student_id, sf.fee_type, sf.amount, sf.discount_amount, CONCAT(s.first_name, ' ', s.last_name) AS student_name 
          FROM student_fees sf 
          JOIN students s ON sf.student_id = s.student_id 
          WHERE 1=1";
$params = [];

if ($filter_student_id) {
    $query .= " AND sf.student_id = ?";
    $params[] = $filter_student_id;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$fees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to update fee summary
function updateFeeSummary($student_id, $amount, $discount_amount) {
    global $pdo;
    // Calculate total fee
    $total_fee = $amount - $discount_amount;

    // Check if summary exists
    $stmt = $pdo->prepare("SELECT * FROM fee_summaries WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($summary) {
        // Update existing summary
        $new_total = $summary['total_computed_fee'] + $total_fee;
        $new_paid = $summary['amount_paid'];
        $new_balance = $new_total - $new_paid;

        $update_stmt = $pdo->prepare("UPDATE fee_summaries SET total_computed_fee = ?, outstanding_balance = ? WHERE student_id = ?");
        $update_stmt->execute([$new_total, $new_balance, $student_id]);
    } else {
        // Create new summary
        $stmt = $pdo->prepare("INSERT INTO fee_summaries (student_id, total_computed_fee, amount_paid, outstanding_balance) VALUES (?, ?, 0.00, ?)");
        $stmt->execute([$student_id, $total_fee, $total_fee]);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./assets/css/fms-style.css">
    <link rel="shortcut icon" href="./assets/favicon/SIS.ico" type="image/x-icon">
    <title>Finance Management - Student Fees</title>
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

    .icon-button {
        background: none;
        border: none;
        font-size: 1.2em;
        cursor: pointer;
        margin: 0 4px;
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
            <nav aria-label="User  options">
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
                <h3>Manage Student Fees</h3>
            </div>
            <article class="module-content">
                <div class="billing-container">
                    <h2>Add New Fee</h2>
                    <form method="POST" action="">
                        <label for="student_id">Student:</label>
                        <select id="student_id" name="student_id" required>
                            <option value="">Select a student</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo $student['student_id']; ?>"><?php echo $student['full_name']; ?></option>
                            <?php endforeach; ?>
                        </select>

                        <label for="fee_type">Fee Type:</label>
                        <select id="fee_type" name="fee_type" required>
                            <option value="Tuition">Tuition</option>
                            <option value="Miscellaneous">Miscellaneous</option>
                            <option value="Laboratory">Laboratory</option>
                            <option value="Other">Other</option>
                        </select>

                        <label for="amount">Amount:</label>
                        <input type="number" id="amount" name="amount" required>

                        <label for="discount_amount">Discount Amount:</label>
                        <input type="number" id="discount_amount" name="discount_amount" value="0.00">

                        <button type="submit" name="add_fee">Add Fee</button>
                    </form>

                    <h3>Existing Fees</h3>
                    <form method="GET" action="">
                        <label for="filter_student_id">Filter by Student ID:</label>
                        <input type="text" id="filter_student_id" name="student_id" value="<?php echo htmlspecialchars($filter_student_id); ?>">
                        <button type="submit">Filter</button>
                    </form>

                    <table>
                        <tr>
                            <th>Fee ID</th>
                            <th>Student ID</th>
                            <th>Fee Type</th>
                            <th>Amount</th>
                            <th>Discount Amount</th>
                            <th>Actions</th>
                        </tr>
                        <?php foreach ($fees as $row): ?>
                        <tr>
                            <td><?php echo $row['fee_id']; ?></td>
                            <td><?php echo $row['student_id']; ?></td>
                            <td><?php echo $row['fee_type']; ?></td>
                            <td>₱ <?php echo number_format($row['amount'], 2); ?></td>
                            <td>₱ <?php echo number_format($row['discount_amount'], 2); ?></td>
                            <td>
                                <!-- Edit Fee Form -->
                                <form method="POST" action="" style="display:inline-block;">
                                    <input type="hidden" name="fee_id" value="<?php echo $row['fee_id']; ?>">
                                    <input type="number" name="amount" placeholder="New Amount" required>
                                    <input type="number" name="discount_amount" placeholder="New Discount" value="0.00">
                                    <button type="submit" name="edit_fee">Edit</button>
                                </form>
                                <!-- Delete Fee Form -->
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="fee_id" value="<?php echo $row['fee_id']; ?>">
                                    <button type="submit" name="delete_fee" onclick="return confirm('Are you sure you want to delete this fee?');">Delete</button>
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

