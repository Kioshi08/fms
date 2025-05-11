<?php
require_once 'php/includes/auth.php';
require_once 'php/includes/db.php';

$user_id = $_SESSION['user_id'];
$student_id = $_GET['id'] ?? null;

if (!$student_id) {
    die("No student ID provided.");
}

// Fetch student fees
$stmt = $pdo->prepare("SELECT fee_type AS type, amount, discount_amount FROM student_fees WHERE student_id = :student_id");
$stmt->execute(['student_id' => $student_id]);
$fees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total fees
$total = 0;
foreach ($fees as $fee) {
    $total += $fee['amount'] - $fee['discount_amount'];
}

// Generate invoice
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
    <link rel="stylesheet" href="./assets/css/fms-style.css">
</head>
<body>
    <h1>Invoice for Student ID: <?php echo htmlspecialchars($student_id); ?></h1>
    <table>
        <thead>
            <tr>
                <th>Fee Type</th>
                <th>Amount</th>
                <th>Discount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($fees as $fee): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fee['type']); ?></td>
                    <td>₱<?php echo number_format($fee['amount'], 2); ?></td>
                    <td>₱<?php echo number_format($fee['discount_amount'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p><strong>Total Fees:</strong> ₱<?php echo number_format($total, 2); ?></p>
    <button onclick="window.print()">Print Invoice</button>
</body>
</html>
