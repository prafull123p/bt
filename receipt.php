<?php
include 'db.php';

// Check if transaction ID is provided
if (!isset($_GET['txn']) || empty($_GET['txn'])) {
    die("Transaction ID is required.");
}

// Fetch payment details
$txn = $_GET['txn'];
$stmt = $conn->prepare("SELECT p.*, f.student_name, f.course FROM fee_payments p JOIN fees f ON p.fee_id=f.id WHERE p.transaction_id=?");
$stmt->bind_param("s", $txn);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { text-align: center; }
        p { font-size: 16px; }
        b { color: #333; }
    </style>
</head>
<body>
<?php if ($row = $res->fetch_assoc()): ?>
    <h2>Payment Receipt</h2>
    <p><b>Transaction ID:</b> <?= htmlspecialchars($row['transaction_id']) ?></p>
    <p><b>Student Name:</b> <?= htmlspecialchars($row['student_name']) ?></p>
    <p><b>Course:</b> <?= htmlspecialchars($row['course']) ?></p>
    <p><b>Amount Paid:</b> ₹<?= number_format($row['amount'], 2) ?></p>
    <p><b>Payment Method:</b> <?= htmlspecialchars(ucfirst($row['method'])) ?></p>
    <p><b>Date of Payment:</b> <?= htmlspecialchars($row['created_at']) ?></p>
    <p><b>Status:</b> <?= htmlspecialchars($row['status']) ?></p>
    <p><b>Remarks:</b> <?= htmlspecialchars($row['remarks']) ?></p>
<?php else: ?>
    <div class="alert alert-danger">Receipt not found for transaction ID: <?= htmlspecialchars($txn) ?></div>
<?php endif; ?>
<?php
$stmt->close();
$conn->close();
?>
</body>
</html>