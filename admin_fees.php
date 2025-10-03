<?php

// --- Export to Excel ---
// This block MUST be at the very top before any output or whitespace
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    include 'db.php';
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="fees_export_' . date('Ymd_His') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Output column headers
    echo "Student Name\tCourse\tTotal Fee\tPaid Fee\tDue Fee\tDate\n";
    // Fetch all fees for export (limit to 1000 rows to avoid performance issues)
    $export_result = $conn->query("SELECT * FROM fees ORDER BY id DESC LIMIT 1000");
    if ($export_result) {
        while ($row = $export_result->fetch_assoc()) {
            echo 
                str_replace(["\t", "\n", "\r"], ' ', $row['student_name']) . "\t" .
                str_replace(["\t", "\n", "\r"], ' ', $row['course']) . "\t" .
                number_format($row['total_fee'], 2) . "\t" .
                number_format($row['paid_fee'], 2) . "\t" .
                number_format($row['due_fee'], 2) . "\t" .
                (isset($row['created_at']) ? date('Y-m-d', strtotime($row['created_at'])) : '-') . "\n";
        }
    }
    exit;
}

include 'auth.php';
include 'db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// --- Role-based access control ---
$message = '';
$edit_mode = false;
// --- Fee Management Logic ---
$edit_id = null;
$edit_student = '';
$edit_course = '';
$edit_total = 0;
$edit_paid = 0;
$edit_due = 0;

// Handle Delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        die('<div class="alert alert-danger m-5">Invalid CSRF token.</div>');
    }
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM fees WHERE id=?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    $message = "Fee record deleted.";
}

// Handle Edit (fetch data)
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_mode = true;
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM fees WHERE id=?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $edit_student = $row['student_name'];
        $edit_course = $row['course'];
        $edit_total = $row['total_fee'];
        $edit_paid = $row['paid_fee'];
        $edit_due = $row['due_fee'];
    }
    $stmt->close();
    if (!$edit_student) {
        die('<div class="alert alert-danger m-5">Fee record not found.</div>');
    }
}

// Handle form submission (add/update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['pay_fee_id'])) {
    // CSRF token validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('<div class="alert alert-danger m-5">Invalid CSRF token.</div>');
    }

    $student_name = trim($_POST['student_name'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $total_fee = floatval($_POST['total_fee'] ?? 0);
    $paid_fee = floatval($_POST['paid_fee'] ?? 0);
    $due_fee = $total_fee - $paid_fee;
    if ($due_fee < 0) {
        $due_fee = 0;
    }

    if ($student_name && $course && $total_fee > 0) {
        if (isset($_POST['edit_id']) && is_numeric($_POST['edit_id'])) {
            // Update
            $edit_id = intval($_POST['edit_id']);
            $stmt = $conn->prepare("UPDATE fees SET student_name=?, course=?, total_fee=?, paid_fee=?, due_fee=? WHERE id=?");
            $stmt->bind_param("ssdddi", $student_name, $course, $total_fee, $paid_fee, $due_fee, $edit_id);
            $stmt->execute();
            $stmt->close();
            $message = "Fee record updated!";
        } else {
            // Insert
            $stmt = $conn->prepare("INSERT INTO fees (student_name, course, total_fee, paid_fee, due_fee) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssddd", $student_name, $course, $total_fee, $paid_fee, $due_fee);
            $stmt->execute();
            $stmt->close();
            $message = "Fee record added!";
        }
    } else {
        $message = "All fields are required and total fee must be greater than 0.";
    }
}

// --- Payment Recording Logic ---
if (isset($_POST['pay_fee_id']) && is_numeric($_POST['pay_fee_id'])) {
    // CSRF token validation
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('<div class="alert alert-danger m-5">Invalid CSRF token.</div>');
    }
    $pay_fee_id = intval($_POST['pay_fee_id']);
    $payment_amount = floatval($_POST['payment_amount'] ?? 0);
    $payment_method = trim($_POST['payment_method'] ?? '');
    // Fetch current fee record
    $stmt = $conn->prepare("SELECT paid_fee, total_fee, due_fee FROM fees WHERE id=?");
    $stmt->bind_param("i", $pay_fee_id);
    $stmt->execute();
    $stmt->bind_result($paid_fee, $total_fee, $due_fee);
    $stmt->fetch();
    $stmt->close();

    if ($payment_amount > 0 && $payment_amount <= $due_fee && $payment_method) {
        $new_paid = $paid_fee + $payment_amount;
        $new_due = $total_fee - $new_paid;
        if ($new_due < 0) {
            $new_due = 0;
        }

        // Generate unique transaction ID
        $transaction_id = strtoupper(uniqid('TXN'));

        // Update fees table
        $stmt = $conn->prepare("UPDATE fees SET paid_fee=?, due_fee=? WHERE id=?");
        $stmt->bind_param("ddi", $new_paid, $new_due, $pay_fee_id);
        $stmt->execute();
        $stmt->close();

        // Insert into payments table
        $stmt = $conn->prepare("INSERT INTO fee_payments (fee_id, amount, method, transaction_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idss", $pay_fee_id, $payment_amount, $payment_method, $transaction_id);
        $stmt->execute();
        $stmt->close();

        $message = "Payment recorded! Transaction ID: <b>$transaction_id</b>";
    } elseif ($payment_amount > $due_fee) {
        $message = "Payment amount cannot exceed the due fee.";
    } else {
        $message = "Invalid payment amount or method.";
    }
}

// --- Payment history filter ---
$where = [];
$params = [];
$types = '';
if (!empty($_GET['filter_user'])) {
    $where[] = "student_name = ?";
    $params[] = $_GET['filter_user'];
    $types .= 's';
}
if (!empty($_GET['filter_date'])) {
    $where[] = "DATE(created_at) = ?";
    $params[] = $_GET['filter_date'];
    $types .= 's';
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Fetch all fees for listing
$fees = [];
if ($where_sql) {
    $sql = "SELECT * FROM fees $where_sql ORDER BY id DESC";
    $stmt = $conn->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM fees ORDER BY id DESC");
}
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $fees[] = $row;
    }
    // Properly close $stmt if it was used
    // if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    //     $stmt->close();
    // }
}

// Fetch payment history for all fees and group by fee_id
$payments_by_fee = [];
if (!empty($fees)) {
    $fee_ids = array_column($fees, 'id');
    if (!empty($fee_ids)) {
        $in = implode(',', array_fill(0, count($fee_ids), '?'));
        $types_pay = str_repeat('i', count($fee_ids));
        $sql_pay = "SELECT * FROM fee_payments WHERE fee_id IN ($in) ORDER BY created_at DESC";
        $stmt_pay = $conn->prepare($sql_pay);
        $stmt_pay->bind_param($types_pay, ...$fee_ids);
        $stmt_pay->execute();
        $result_pay = $stmt_pay->get_result();
        while ($pay = $result_pay->fetch_assoc()) {
            $payments_by_fee[$pay['fee_id']][] = $pay;
        }
        $stmt_pay->close();
    }
}

// For filter dropdowns
$students = $conn->query("SELECT DISTINCT student_name FROM fees ORDER BY student_name ASC");

// --- Collection Reports ---
$report_type = $_GET['report_type'] ?? 'daily';
$report_sql = '';
$report_label = '';
switch ($report_type) {
    case 'weekly':
        $report_sql = "SELECT DATE(created_at) as period, SUM(paid_fee) as collected FROM fees WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) GROUP BY period ORDER BY period DESC";
        $report_label = 'This Week';
        break;
    case 'monthly':
        $report_sql = "SELECT DATE(created_at) as period, SUM(paid_fee) as collected FROM fees WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) GROUP BY period ORDER BY period DESC";
        $report_label = 'This Month';
        break;
    default:
        $report_sql = "SELECT DATE(created_at) as period, SUM(paid_fee) as collected FROM fees WHERE DATE(created_at) = CURDATE() GROUP BY period";
        $report_label = 'Today';
        break;
}
$report_data = [];
if ($res = $conn->query($report_sql)) {
    while ($row = $res->fetch_assoc()) {
        $report_data[] = $row;
    }
}

// --- Outstanding Dues Summary ---
$dues_sql = "SELECT student_name, course, total_fee, paid_fee, due_fee FROM fees WHERE due_fee > 0 ORDER BY due_fee DESC";
$dues_data = [];
if ($res = $conn->query($dues_sql)) {
    while ($row = $res->fetch_assoc()) {
        $dues_data[] = $row;
    }
}

// --- Fee Receipt Download ---
if (isset($_GET['receipt']) && !empty($_GET['receipt'])) {
    include 'db.php';
    $txn = $_GET['receipt'];
    $stmt = $conn->prepare("SELECT p.*, f.student_name, f.course FROM fee_payments p JOIN fees f ON p.fee_id=f.id WHERE p.transaction_id=?");
    $stmt->bind_param("s", $txn);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="receipt_' . $txn . '.txt"');
        echo "----- Fee Payment Receipt -----\n";
        echo "Transaction ID: {$row['transaction_id']}\n";
        echo "Student Name: {$row['student_name']}\n";
        echo "Course: {$row['course']}\n";
        echo "Amount Paid: " . number_format($row['amount'], 2) . "\n";
        echo "Payment Method: " . ucfirst($row['method']) . "\n";
        echo "Date: {$row['created_at']}\n";
        echo "------------------------------\n";
    } else {
        echo "Receipt not found.";
    }
    exit;
}
?>
<!-- ...rest of your HTML as in your file... -->
<!DOCTYPE html>
<html lang="en">    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Fees Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .message { margin-bottom: 20px; }
        .table th, .table td { vertical-align: middle; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Fees
    Management</h1>
        <?php if ($message): ?>
            <div class="alert alert-info message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post" class="mb-4">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="form-row">
                <div class="col-md-3 mb-3">
                    <label for="student_name">Student Name</label>
                    <input type="text" name="student_name" id="student_name" class="form-control" value="<?= htmlspecialchars($edit_student ?? '') ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="course">Course</label>
                    <input type="text" name="course" id="course" class="form-control" value="<?= htmlspecialchars($edit_course ?? '') ?>" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="total_fee">Total Fee</label>
                    <input type="number" name="total_fee" id="total_fee" class="form-control" value="<?= htmlspecialchars($edit_total ?? 0) ?>" step="0.01" min="0" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="paid_fee">Paid Fee</label>
                    <input type="number" name="paid_fee" id="paid_fee" class="form-control" value="<?= htmlspecialchars($edit_paid ?? 0) ?>" step="0.01" min="0">
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <?php if ($edit_mode): ?>
                        <input type="hidden" name="edit_id" value="<?= intval($edit_id) ?>">
                        <button type="submit" class="btn btn-primary w-100">Update Fee</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-success w-100">Add Fee</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <!-- Payment Recording -->
        <?php if ($edit_mode): ?>
            <h4>Record Payment for <?= htmlspecialchars($edit_student) ?></h4>
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="pay_fee_id" value="<?= intval($edit_id) ?>">
                <div class="form-row">
                    <div class="col-md-3 mb-3">
                        <label for="payment_amount">Payment Amount</label>
                        <input type="number" name="payment_amount" id="payment_amount" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="payment_method">Payment Method</label>
                        <input type="text" name="payment_method" id="payment_method" class="form-control" placeholder="e.g. Cash, Bank Transfer" required>
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">Record Payment</button>
                    </div>  
                </div>
            </form> 
        <?php endif; ?>
        <!-- Fees List -->
        <h4 class="mb-3">All Fees</h4>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Student Name</th>
                        <th>Course</th>
                        <th>Total Fee</th>
                        <th>Paid Fee</th>
                        <th>Due Fee</th>
                        <th>Date</th>
                        <th style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($fees): ?>
                        <?php foreach ($fees as $fee): ?>
                            <tr>
                                <td><?= htmlspecialchars($fee['student_name']) ?></td>
                                <td><?= htmlspecialchars($fee['course']) ?></td>
                                <td><?= number_format($fee['total_fee'], 2) ?></td>
                                <td><?= number_format($fee['paid_fee'], 2) ?></td>
                                <td><?= number_format($fee['due_fee'], 2) ?></td>
                                <td><?= isset($fee['created_at']) ? date('Y-m-d', strtotime($fee['created_at'])) : '-' ?></td>
                                <td>
                                    <a href="?edit=<?= $fee['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <?php if ($fee['due_fee'] > 0): ?>
                                        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#payModal<?= $fee['id'] ?>">Pay</button>
                                    <?php endif; ?>
                                    <a href="?delete=<?= $fee['id'] ?>&csrf_token=<?= htmlspecialchars($csrf_token) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this fee record?')">Delete</a>

                                    <!-- Payment Modal -->
                                    <?php if ($fee['due_fee'] > 0): ?>
                                        <div class="modal fade" id="payModal<?= $fee['id'] ?>" tabindex="-1" role="dialog" aria-labelledby="payModalLabel<?= $fee['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="payModalLabel<?= $fee['id'] ?>">Record Payment for <?= htmlspecialchars($fee['student_name']) ?></h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form method="post">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                                        <input type="hidden" name="pay_fee_id" value="<?= $fee['id'] ?>">
                                                        <div class="modal-body">
                                                            <div class="form-group">
                                                                <label for="payment_amount">Payment Amount</label>
                                                                <input type="number" name="payment_amount" id="payment_amount" class="form-control" step="0.01" min="0" required>   
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="payment_method">Payment Method</label>
                                                                <input type="text" name="payment_method" id="payment_method" class="form-control" placeholder="e.g. Cash, Bank Transfer" required>      
                                                            </div>
                                                        </div>  
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-success">Record Payment</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No fee records found.</td>
                        </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>
        <!-- Payment History -->
        <h4 class="mb-3">Payment History</h4>
        <form method="get" class="mb-3">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="form-row">
                <div class="col-md-3 mb-3">
                    <label for="filter_user">Filter by Student</label>
                    <select name="filter_user" id="filter_user" class="form-control">
                        <option value="">All Students</option>
                        <?php if ($students): ?>
                            <?php while ($row = $students->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($row['student_name']) ?>" <?= (isset($_GET['filter_user']) && $_GET['filter_user'] === $row['student_name']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($row['student_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="filter_date">Filter by Date</label>
                    <input type="date" name="filter_date" id="filter_date" class="form-control" value="<?= htmlspecialchars($_GET['filter_date'] ?? '') ?>">
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>  
        </form>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Student Name</th>
                        <th>Course</th>
                        <th>Total Fee</th>
                        <th>Paid Fee</th>
                        <th>Due Fee</th>
                        <th>Date</th>
                        <th style="width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($fees): ?>
                        <?php foreach ($fees as $fee): ?>
                            <tr>
                                <td><?= htmlspecialchars($fee['student_name']) ?></td>
                                <td><?= htmlspecialchars($fee['course']) ?></td>
                                <td><?= number_format($fee['total_fee'], 2) ?></td>
                                <td><?= number_format($fee['paid_fee'], 2) ?></td>
                                <td><?= number_format($fee['due_fee'], 2) ?></td>
                                <td><?= isset($fee['created_at']) ? date('Y-m-d', strtotime($fee['created_at'])) : '-' ?></td>
                                <td>
                                    <?php if (isset($payments_by_fee[$fee['id']])): ?>
                                        <?php foreach ($payments_by_fee[$fee['id']] as $payment): ?>
                                            <?= htmlspecialchars($payment['method']) ?> - <?= number_format($payment['amount'], 2) ?> (<?= date('Y-m-d', strtotime($payment['created_at'])) ?>)
                                            <a href="?receipt=<?= urlencode($payment['transaction_id']) ?>&format=pdf" class="btn btn-link btn-sm" target="_blank">Download PDF Receipt</a><br>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        No payments recorded.
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No fee records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>    
        </div>  
        <!-- Collection Reports -->
        <h4 class="mb-3">Collection Reports</h4>
        <form method="get" class="mb-3">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="form-row">
                <div class="col-md-3 mb-3">
                    <label for="report_type">Report Type</label>
                    <select name="report_type" id="report_type" class="form-control">
                        <option value="daily" <?= ($report_type === 'daily') ? 'selected' : '' ?>>Daily</option>
                        <option value="weekly" <?= ($report_type === 'weekly') ? 'selected' : '' ?>>Weekly</option>
                        <option value="monthly" <?= ($report_type === 'monthly') ? 'selected' : '' ?>>Monthly</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                </div>
            </div>
        </form>
        <div class="table-responsive">
                        
            <table class="table table-bordered align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Date</th>
                        <th>Amount Collected</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($report_data): ?>
                        <?php foreach ($report_data as $data): ?>
                            <tr>
                                <td><?= htmlspecialchars($data['period']) ?></td>
                                <td><?= number_format($data['collected'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="text-center">No data available for this report.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>    
        </div>
        <!-- Outstanding Dues Summary -->
        <h4 class="mb-3">Outstanding Dues Summary</h4>
        <div class="table-responsive">
                        
            <table class="table table-bordered align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>Student Name</th>
                        <th>Course</th>
                        <th>Total Fee</th>
                        <th>Paid Fee</th>
                        <th>Due Fee</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($dues_data): ?>
                        <?php foreach ($dues_data as $due): ?>
                            <tr>
                                <td><?= htmlspecialchars($due['student_name']) ?></td>
                                <td><?= htmlspecialchars($due['course']) ?></td>
                                <td><?= number_format($due['total_fee'], 2) ?></td>
                                <td><?= number_format($due['paid_fee'], 2) ?></td>
                                <td><?= number_format($due['due_fee'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No outstanding dues found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>    
        </div>
    </div>  
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>