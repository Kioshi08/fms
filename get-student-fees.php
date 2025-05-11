<?php
// Include the database connection
require_once 'db.php'; // Adjust the path if necessary
// Get the student ID from the request
$studentId = $_GET['id'] ?? null;
// Check if the student ID is provided
if ($studentId) {
    // Prepare the SQL statement to fetch fees for the student
    $stmt = $pdo->prepare("SELECT fee_id, fee_type AS type, amount, discount_amount FROM student_fees WHERE student_id = :student_id");
    $stmt->execute(['student_id' => $studentId]);
    
    // Fetch all fees associated with the student
    $fees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Return the fees as a JSON response
    echo json_encode(['fees' => $fees]);
} else {
    // Return an error message if no student ID is provided
    echo json_encode(['error' => 'No student ID provided.']);
}
?>
