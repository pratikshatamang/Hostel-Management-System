<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/checklogin.php';
require_once 'includes/booking.php';
require_once 'includes/email.php';
check_login();

$pending = isset($_SESSION['pending_booking']) && is_array($_SESSION['pending_booking'])
    ? $_SESSION['pending_booking']
    : null;

$status = isset($_GET['status']) ? trim((string) $_GET['status']) : '';
$pidx = isset($_GET['pidx']) ? trim((string) $_GET['pidx']) : '';
$transactionId = isset($_GET['transaction_id']) ? trim((string) $_GET['transaction_id']) : '';
$callbackAmount = isset($_GET['total_amount']) ? (int) $_GET['total_amount'] : (isset($_GET['amount']) ? (int) $_GET['amount'] : 0);
$message = '';
$error = '';
$isSuccess = false;

if (!$pending) {
    $error = 'No pending booking session was found. Please start the booking again.';
} elseif ($pidx === '') {
    $error = 'Khalti callback is missing the payment identifier.';
} elseif (!empty($pending['khalti_pidx']) && $pending['khalti_pidx'] !== $pidx) {
    $error = 'The returned Khalti payment identifier does not match the pending booking.';
} else {
    $expectedAmount = isset($pending['khalti_expected_amount']) ? (int) $pending['khalti_expected_amount'] : hms_calculate_pending_booking_amounts($pending)['total_paisa'];
    $lookup = hms_khalti_lookup_payment($pidx);

    if (!$lookup['success']) {
        $error = 'Unable to verify the Khalti payment status right now.';
        if (!empty($lookup['data']['detail'])) {
            $error .= ' ' . $lookup['data']['detail'];
        } elseif (!empty($lookup['error'])) {
            $error .= ' ' . $lookup['error'];
        }
    } else {
        $lookupData = $lookup['data'];
        $verifiedStatus = isset($lookupData['status']) ? trim((string) $lookupData['status']) : $status;
        $verifiedAmount = isset($lookupData['total_amount']) ? (int) $lookupData['total_amount'] : $callbackAmount;
        $verifiedTransactionId = isset($lookupData['transaction_id']) ? trim((string) $lookupData['transaction_id']) : $transactionId;

        if ($verifiedAmount !== $expectedAmount) {
            $error = 'Khalti amount verification failed. Expected ' . $expectedAmount . ' paisa but received ' . $verifiedAmount . '.';
        } elseif ($verifiedStatus !== 'Completed') {
            $error = 'Khalti returned status "' . $verifiedStatus . '". Room allocation has been kept on hold.';
        } else {
            $result = hms_finalize_pending_booking($mysqli, $pending, 'khalti', 'paid', $verifiedTransactionId !== '' ? $verifiedTransactionId : $pidx);

            if ($result['success']) {
                $fullName = hms_pending_booking_student_name($pending);
                hms_send_booking_notifications_after_submission(
                    $pending['emailid'],
                    $fullName,
                    $pending,
                    hms_email_get_user_id_by_email($pending['emailid'])
                );

                unset($_SESSION['pending_booking']);
                $isSuccess = true;
                $message = 'Payment verified successfully. Room ' . (int) $pending['roomno'] . ' has been allocated and one seat has been consumed from that room.';
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>Khalti Payment Status</title>
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
            <section class="ui-hero" style="margin-bottom:24px;">
                <span class="ui-badge"><i class="fa fa-credit-card"></i> Khalti Checkout</span>
                <h2>Payment Result</h2>
                <p>The booking is only finalized after Khalti lookup confirms the payment.</p>
            </section>

            <div class="ui-surface">
                <div class="ui-surface-body">
                    <?php if ($isSuccess) { ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                        <a href="room-details.php" class="btn btn-primary">View Allocated Room</a>
                    <?php } else { ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error !== '' ? $error : 'Payment could not be confirmed.'); ?></div>
                        <a href="payment.php" class="btn btn-primary">Back to Payment</a>
                        <a href="book-hostel.php" class="btn btn-default">Back to Booking</a>
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
