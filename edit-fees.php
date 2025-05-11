<?php
require_once 'php/includes/auth.php';
require_once 'php/includes/db.php';

$user_id = $_SESSION['user_id'];
$student_id = $_GET['id'] ?? null;

if (!$student_id) {
    die("No student ID provided.");
}

// Fetch current fees
$stmt = $pdo->prepare("SELECT fee_id, fee_type AS type, amount, discount_amount FROM student_fees WHERE student_id = :student_id");
$stmt->execute(['student_id' => $student_id]);
$fees = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update fees based on form submission
    foreach ($_POST['fees'] as $feeId => $feeData) {
        $stmtUpdate = $pdo->prepare("UPDATE student_fees SET amount = :amount, discount_amount = :discount WHERE fee_id = :fee_id");
        $stmtUpdate->execute([
            'amount' => $feeData['amount'],
            'discount' => $feeData['discount'],
            'fee_id' => $feeId
        ]);
    }
    header("Location: student-fees.php"); // Redirect after update
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Fees</title>
    <link rel="stylesheet" href="./assets/css/fms-style.css">
</head>
<body>
    <h1>Edit Fees for Student ID: <?php echo htmlspecialchars($student_id); ?></h1>
    <form method="POST">
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
                        <td><input type="number" name="fees[<?php echo $fee['fee_id']; ?>][amount]" value="<?php echo htmlspecialchars($fee['amount']); ?>" required></td>
                        <td><input type="number" name="fees[<?php echo $fee['fee_id']; ?>][discount]" value="<?php echo htmlspecialchars($fee['discount_amount']); ?>" required></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit">Update Fees</button>
    </form>
</body>
</html>
