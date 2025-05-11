<?php
require_once 'php/includes/auth.php';
require_once 'php/includes/db.php'; // Database connection

$user_id = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']); // safe output
$user_role = $_SESSION['user_role'];
requireLogin();
requireRole('admin');

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
                    <li><a href="./php/logout.php"><span class="mdi mdi-logout"></span><span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>
        <section class="content">
            <div class="content-header">
                <button class="js-sidenav-toggle" aria-label="Toggle navigation menu">
                    <span class="mdi mdi-menu"></span>
                </button>
                <h3>Student Fees</h3>
            </div>
            <article class="module-content">
                <div class="fee-assessment">
                    <h3>Fee Assessment</h3>

                    <!-- Student Search/Dropdown -->
                    <div class="search-bar">
                        <label for="studentSelect">Select Student:</label>
                        <select id="studentSelect">
                            <option value="">-- Select Student --</option>
                            <?php
                            // Fetch active students from the database
                            $students = $pdo->query("SELECT student_id, first_name, last_name FROM students WHERE status = 'Active'"); 

                            while ($row = $students->fetch(PDO::FETCH_ASSOC)) {
                                $fullName = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
                                echo "<option value='{$row['student_id']}'>$fullName</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Fees Table -->
                    <table id="feeTable">
                        <thead>
                            <tr>
                                <th>Fee Type</th>
                                <th>Amount</th>
                                <th>Discount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- JS Injected rows -->
                        </tbody>
                    </table>

                    <!-- Summary Panel -->
                    <div class="summary-panel">
                        <p><strong>Total Computed Fee:</strong> ₱<span id="totalFee">0.00</span></p>
                        <p><strong>Outstanding Balance:</strong> ₱<span id="balance">0.00</span></p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="actions">
                        <button onclick="generateInvoice()">Generate Invoice</button>
                        <button onclick="editFees()">Edit Fees</button>
                    </div>
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
    <script>
        document.getElementById('studentSelect').addEventListener('change', function () {
            const studentId = this.value;
            if (!studentId) return;

            fetch(`php/api/get-student-fees.php?id=${studentId}`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.querySelector('#feeTable tbody');
                    tbody.innerHTML = '';

                    let total = 0;

                    data.fees.forEach(fee => {
                        const discounted = fee.amount - fee.discount_amount;
                        total += discounted;

                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${fee.type}</td>
                            <td>₱${parseFloat(fee.amount).toFixed(2)}</td>
                            <td>₱${parseFloat(fee.discount_amount).toFixed(2)}</td>
                        `;
                        tbody.appendChild(row);
                    });

                    document.getElementById('totalFee').textContent = total.toFixed(2);
                    document.getElementById('balance').textContent = total.toFixed(2); // No payments table, so balance equals total
                })
                .catch(err => console.error(err));
        });

        function generateInvoice() {
            const studentId = document.getElementById('studentSelect').value;
            if (!studentId) {
                alert("Please select a student first.");
                return;
            }

            // Redirect to the invoice generation page
            window.location.href = `generate-invoice.php?id=${studentId}`;
        }

        function editFees() {
            const studentId = document.getElementById('studentSelect').value;
            if (!studentId) {
                alert("Please select a student first.");
                return;
            }

            // Redirect to the fee editing page
            window.location.href = `edit-fees.php?id=${studentId}`;
        }
    </script>
</body>

</html>
