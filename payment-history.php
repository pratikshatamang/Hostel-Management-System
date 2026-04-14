<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/checklogin.php';
check_login();

$userEmail = isset($_SESSION['login']) ? $_SESSION['login'] : '';
$payments = array();

$stmt = $mysqli->prepare('SELECT id, roomno, feespm, foodstatus, duration, postingDate, payment_method, payment_status, transaction_id FROM registration WHERE emailid = ? ORDER BY id DESC');
$stmt->bind_param('s', $userEmail);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $monthlyFee = (int) $row['feespm'];
    $duration = (int) $row['duration'];
    $foodCharge = (int) $row['foodstatus'] === 1 ? 2000 : 0;
    $row['food_charge_per_month'] = $foodCharge;
    $row['total_amount'] = ($monthlyFee + $foodCharge) * $duration;
    $payments[] = $row;
}

$stmt->close();
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="theme-color" content="#183153">
    <title>Payment History</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/user-ui.css">
</head>
<body>
<?php include('includes/header.php'); ?>
<div class="ts-main-content">
    <?php include('includes/sidebar.php'); ?>
    <div class="content-wrapper">
        <div class="container-fluid">
            <section class="ui-hero">
                <span class="ui-badge"><i class="fa fa-credit-card"></i> Payment Records</span>
                <h2>Payment History</h2>
                <p>Review every hostel payment together with the room it was used to book.</p>
            </section>

            <div class="ui-surface">
                <div class="ui-surface-head">
                    <h3>All Booking Payments</h3>
                    <p>Each row shows which room was booked, how it was paid, and the recorded payment status.</p>
                </div>
                <div class="ui-surface-body">
                    <?php if (empty($payments)) { ?>
                        <div class="ui-empty">
                            <h3 class="ui-section-title">No payment history yet</h3>
                            <p>Your booking payments will appear here after a room is reserved.</p>
                            <a href="book-hostel.php" class="btn btn-primary">Book Hostel</a>
                        </div>
                    <?php } else { ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Booked On</th>
                                        <th>Room</th>
                                        <th>Monthly Fee</th>
                                        <th>Duration</th>
                                        <th>Total Paid</th>
                                        <th>Method</th>
                                        <th>Status</th>
                                        <th>Transaction ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($payments as $payment) { ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($payment['postingDate']); ?></td>
                                            <td><?php echo 'Room ' . htmlspecialchars($payment['roomno']); ?></td>
                                            <td>Rs <?php echo htmlspecialchars($payment['feespm']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['duration']); ?> month(s)</td>
                                            <td>Rs <?php echo htmlspecialchars($payment['total_amount']); ?></td>
                                            <td><?php echo htmlspecialchars(ucfirst((string) $payment['payment_method'])); ?></td>
                                            <td><?php echo htmlspecialchars(ucfirst((string) $payment['payment_status'])); ?></td>
                                            <td><?php echo htmlspecialchars($payment['transaction_id'] !== null && $payment['transaction_id'] !== '' ? $payment['transaction_id'] : 'N/A'); ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
