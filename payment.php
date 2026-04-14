<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
include('includes/booking.php');
include('includes/email.php');
check_login();

if (!isset($_SESSION['pending_booking'])) {
    header("Location: book-hostel.php");
    exit;
}

$pending = $_SESSION['pending_booking'];
$fee = (int)$pending['feespm'];
$duration = (int)$pending['duration'];
$foodstatus = (int)$pending['foodstatus'];

$extraFoodCharge = $foodstatus === 1 ? 2000 : 0;
$totalRs = ($fee + $extraFoodCharge) * $duration;
$totalPaisa = $totalRs * 100;
$paymentError = '';
$khaltiReady = hms_khalti_is_configured();

// Force redirect back to booking if amount makes no sense
if ($totalRs <= 0) {
    echo "<script>alert('Invalid payment amount calculated.'); window.location.href='book-hostel.php';</script>";
    exit;
}

if (isset($_POST['pay_cash'])) {
    $paymentMethod = 'cash';
    $paymentStatus = 'pending';
    $txnId = 'n/a';

    $result = hms_finalize_pending_booking($mysqli, $pending, $paymentMethod, $paymentStatus, $txnId);
    if ($result['success']) {
        $fullName = hms_pending_booking_student_name($pending);
        hms_send_booking_notifications_after_submission(
            $pending['emailid'],
            $fullName,
            $pending,
            hms_email_get_user_id_by_email($pending['emailid'])
        );

        unset($_SESSION['pending_booking']);
        echo "<script>alert('Room booked locally! Please submit your cash payment to the administration manually.'); window.location.href='room-details.php';</script>";
        exit;
    }

    $paymentError = $result['message'];
}

if (isset($_POST['pay_khalti'])) {
    if (!$khaltiReady) {
        $paymentError = 'Khalti sandbox keys are not configured yet. Add your keys in includes/config.php and try again.';
    } else {
        $initiation = hms_khalti_initiate_payment($pending);

        if ($initiation['success']) {
            $_SESSION['pending_booking']['khalti_pidx'] = $initiation['data']['pidx'];
            $_SESSION['pending_booking']['khalti_purchase_order_id'] = $initiation['purchase_order_id'];
            $_SESSION['pending_booking']['khalti_expected_amount'] = $totalPaisa;
            $_SESSION['pending_booking']['khalti_payment_url'] = $initiation['data']['payment_url'];
            header('Location: khalti_redirect.php');
            exit;
        }

        $paymentError = 'Khalti payment initiation failed.';
        if (!empty($initiation['data']['detail'])) {
            $paymentError .= ' ' . $initiation['data']['detail'];
        } elseif (!empty($initiation['error'])) {
            $paymentError .= ' ' . $initiation['error'];
        }
    }
}

?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>Room Checkout Payment</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/user-ui.css">
    <style>
        .payment-box {
            padding: 30px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid #ebf1f6;
            margin-bottom: 25px;
            text-align: center;
        }
        .payment-box h3 { margin-top: 0; font-weight: 700; color: #163954; }
        .payment-box .amount { font-size: 34px; font-weight: 700; color: #1f8d63; margin: 15px 0; }
        .btn-khalti {
            background: #5C2D91;
            color: #fff;
            padding: 12px 24px;
            border-radius: 12px;
            border: 0;
            font-size: 16px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            justify-content: center;
        }
        .btn-khalti:hover { background: #482373; color:#fff;}
        .btn-cash { background: #eef5fb; color: #1f5f8b; border: 1px solid #1f5f8b; }
        .payment-actions { display: grid; gap: 15px; max-width: 400px; margin: 0 auto; }
        .booking-summary { max-width: 400px; margin: 0 auto 25px; text-align: left; }
        .booking-summary ul { list-style: none; padding:0; margin:0;}
        .booking-summary li { margin-bottom: 8px; font-size:15px; color:#555;}
        .booking-summary li strong { color:#333; float:right; }
        .payment-note { max-width: 640px; margin: 0 auto 20px; color:#526273; line-height:1.7; }
    </style>
</head>
<body>
<?php include('includes/header.php'); ?>
<div class="ts-main-content">
    <?php include('includes/sidebar.php'); ?>
    <div class="content-wrapper">
        <div class="container-fluid">
            <section class="ui-hero" style="margin-bottom:30px;">
                <span class="ui-badge"><i class="fa fa-lock"></i> Secure Checkout</span>
                <h2>Complete Your Booking Details</h2>
                <p>You are one step away. Please select a payment option below to finalize the transaction for Room <?php echo (int)$pending['roomno']; ?>.</p>
            </section>

            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="payment-box">
                        <h3>Payment Breakdown</h3>
                        <p class="payment-note">
                            Khalti is configured in <strong><?php echo htmlspecialchars(defined('KHALTI_ENVIRONMENT') ? KHALTI_ENVIRONMENT : 'sandbox'); ?></strong> mode.
                            Room allocation only happens after Khalti confirms the payment, so the room seat is consumed only for successful payments.
                        </p>

                        <?php if ($paymentError !== '') { ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($paymentError); ?></div>
                        <?php } ?>
                        
                        <div class="booking-summary">
                            <ul>
                                <li>Room Fee / Month <strong>Rs <?php echo $fee; ?></strong></li>
                                <?php if($extraFoodCharge > 0): ?>
                                <li>Food Charge / Month <strong>Rs <?php echo $extraFoodCharge; ?></strong></li>
                                <?php endif; ?>
                                <li>Duration <strong><?php echo $duration; ?> Months</strong></li>
                                <li style="border-top:1px solid #eee; margin-top:12px; padding-top:12px; font-size:18px; color:#163954; font-weight:600;">Total Amount <strong>Rs <?php echo $totalRs; ?></strong></li>
                            </ul>
                        </div>

                        <div class="payment-actions">
                            <form method="post" action="payment.php">
                                <button type="submit" name="pay_khalti" class="btn btn-khalti" <?php echo $khaltiReady ? '' : 'disabled'; ?>>
                                    <i class="fa fa-credit-card"></i> Pay Online via Khalti
                                </button>
                            </form>

                            <div style="margin: 15px 0; color:#888; font-weight:600;">&mdash; OR &mdash;</div>
                            
                            <form method="post" action="payment.php">
                                <button type="submit" name="pay_cash" class="btn btn-outline btn-cash container-fluid" style="border-radius:12px; padding:12px;" onclick="return confirm('Press OK to confirm you will explicitly pay Rs <?php echo $totalRs; ?> in cash locally.');">
                                    <i class="fa fa-money"></i> Reserve Now & Pay Cash
                                </button>
                            </form>
                            
                            <a href="book-hostel.php" class="btn btn-default" style="margin-top:10px;">Cancel Booking</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap-select.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
