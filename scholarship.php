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
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    display: flex; /* Use flexbox to center the modal */
}

.modal-content {
    background: white; /* Background color of the modal */
    border-radius: 12px;
    padding: 20px;
    width: 500px; /* Set a width for the modal */
    max-width: 90%; /* Responsive width */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    position: relative; /* Position relative for the close button */
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
    color: #000; /* Change color on hover */
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
                <h3>Scholarship</h3>
            </div>
            <article class="module-content">
            <div class="tabs scholarship">
                    <button class="tab-btn active" onclick="switchTab('applications')">Scholarship Applications</button>
                    <button class="tab-btn" onclick="switchTab('records')">Scholarship Records</button>

                    <div id="applications" class="section active">
                        <input type="text" id="searchApp" placeholder="Search student name..."
                            onkeyup="filterTable('appTable', this.value)">
                        <table id="appTable">
                            <thead>
                                <tr>
                                    <th>Application ID</th>
                                    <th>Student Name</th>
                                    <th>Type</th>
                                    <th>Date Applied</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>SCH-001</td>
                                    <td>Juan Dela Cruz</td>
                                    <td>Academic</td>
                                    <td>2025-04-10</td>
                                    <td id="status-1">Pending</td>
                                    <td class="action">
                                        <button class="btn view" onclick="viewApplication(1)">View</button>
                                        <button class="btn approve" onclick="updateStatus(1, 'Approved')">Approve</button>
                                        <button class="btn reject" onclick="updateStatus(1, 'Rejected')">Reject</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Scholarship Records Section -->
                    <div id="records" class="section">
                        <input type="text" id="searchRec" placeholder="Search student name..."
                            onkeyup="filterTable('recordTable', this.value)">
                        <button class="btn add-btn" onclick="toggleAddForm()">Add Scholarship</button>

                        <div id="addScholarshipForm" style="display: none;">
                            <h3>Add Scholarship</h3>
                            <input type="text" id="studentName" placeholder="Student Name" required>
                            <select id="type">
                                <option value="">Select Type</option>
                                <option value="Academic">Academic</option>
                                <option value="Athletic">Athletic</option>
                            </select>
                            <input type="number" id="amount" placeholder="Amount" required>
                            <input type="date" id="dateAwarded" required>
                            <button class="btn approve" onclick="addScholarship()">Save</button>
                            <button class="btn reject" onclick="toggleAddForm()">Cancel</button>
                        </div>

                        <table id="recordTable">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Date Awarded</th>
                                    <th>Approval Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="recordsBody">
                                <tr>
                                    <td>Academic</td>
                                    <td>10000</td>
                                    <td>2025-04-01</td>
                                    <td>Approved</td>
                                    <td class="action"><button class="btn reject" onclick="removeScholarship(this)">Remove</button></td>
                                </tr>
                            </tbody>
                        </table>
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

    <!-- Modal for Viewing Application Details -->
    <div id="applicationModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Application Details</h3>
            <div id="applicationDetails"></div>
         </div>
    </div>

    <script src="./assets/js/fms-script.js"></script>
    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.getElementById(tabId).classList.add('active');
            event.target.classList.add('active');
        }

        function updateStatus(id, newStatus) {
            document.getElementById(`status-${id}`).innerText = newStatus;
            alert(`Application status updated to ${newStatus}`);
        }

        function toggleAddForm() {
            const form = document.getElementById("addScholarshipForm");
            form.style.display = form.style.display === "none" ? "block" : "none";
        }

        function addScholarship() {
            const name = document.getElementById("studentName").value;
            const type = document.getElementById("type").value;
            const amount = document.getElementById("amount").value;
            const date = document.getElementById("dateAwarded").value;

            if (!name || !type || !amount || !date) {
                alert("Please fill out all fields.");
                return;
            }

            const row = `<tr>
                <td>${type}</td>
                <td>${amount}</td>
                <td>${date}</td>
                <td>Approved</td>
                <td><button class="btn reject" onclick="removeScholarship(this)">Remove</button></td>
            </tr>`;
            document.getElementById("recordsBody").innerHTML += row;
            document.getElementById("addScholarshipForm").reset();
            toggleAddForm();
        }

        function removeScholarship(button) {
            const row = button.closest('tr');
            row.parentNode.removeChild(row);
            alert("Scholarship record removed.");
        }

        function filterTable(tableId, query) {
            const rows = document.querySelectorAll(`#${tableId} tbody tr`);
            rows.forEach(row => {
                const nameCell = row.cells[1]?.innerText.toLowerCase() || '';
                row.style.display = nameCell.includes(query.toLowerCase()) ? '' : 'none';
            });
        }

        function viewApplication(id) {
            // Here you can fetch the application details based on the ID
            // For demonstration, we will use static data. You can replace this with dynamic data as needed.
            const applicationDetails = `
                <p><strong>Application ID:</strong> SCH-00${id}</p>
                <p><strong>Student Name:</strong> Juan Dela Cruz</p>
                <p><strong>Type:</strong> Academic</p>
                <p><strong>Date Applied:</strong> 2025-04-10</p>
                <p><strong>Status:</strong> ${document.getElementById(`status-${id}`).innerText}</p>
            `;
            document.getElementById("applicationDetails").innerHTML = applicationDetails;
            document.getElementById("applicationModal").style.display = "block";
        }

        function closeModal() {
            document.getElementById("applicationModal").style.display = "none";
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById("applicationModal");
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
      
</body>

</html>  