<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
include('includes/booking.php');
check_login();

$userEmail = $_SESSION['login'];
$bookings = hms_get_bookings_by_email($mysqli, $userEmail);
$latestBooking = !empty($bookings) ? $bookings[0] : null;
$activeBooking = hms_get_active_booking_by_email($mysqli, $userEmail);
$renewableRoom = hms_get_renewable_room_option($mysqli, $userEmail);
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="theme-color" content="#183153">
    <title>Room Details</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-social.css">
    <link rel="stylesheet" href="css/bootstrap-select.css">
    <link rel="stylesheet" href="css/fileinput.min.css">
    <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/user-ui.css">
    <style>
    .btn-renew { background:linear-gradient(135deg, #0b6b57, #15967b); border-color:#0b6b57; color:#fff; }
    .btn-renew:hover, .btn-renew:focus { background:linear-gradient(135deg, #095847, #117b65); border-color:#095847; color:#fff; }
    .info-chip.status-expired { background:#fff1f1; color:#b54747; }
    .info-chip.status-active { background:#ecfff7; color:#11795f; }
    .info-chip.status-upcoming { background:#eef5ff; color:#285ea8; }
    .expiry-panel {
        margin: 0 0 24px;
        border-radius: 18px;
        border: 1px solid #d8e5f0;
        overflow: hidden;
        box-shadow: 0 10px 24px rgba(16, 38, 58, 0.06);
    }
    .expiry-panel-head {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:16px;
        padding:18px 22px;
        color:#fff;
    }
    .expiry-panel-head h3 {
        margin:0;
        font-size:22px;
        font-weight:700;
    }
    .expiry-panel-head p {
        margin:6px 0 0;
        color:rgba(255,255,255,0.88);
    }
    .expiry-panel-head-main {
        display:flex;
        align-items:center;
        gap:16px;
    }
    .expiry-icon-badge {
        width:58px;
        height:58px;
        border-radius:16px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        background:rgba(255,255,255,0.18);
        border:1px solid rgba(255,255,255,0.25);
        font-size:24px;
    }
    .expiry-panel-body {
        padding:20px 22px 22px;
        background:#fff;
    }
    .expiry-panel-grid {
        display:grid;
        grid-template-columns:repeat(3, minmax(0, 1fr));
        gap:14px;
        margin-bottom:18px;
    }
    .expiry-stat {
        padding:14px 16px;
        border-radius:14px;
        background:#f8fbfe;
        border:1px solid #e2ebf3;
    }
    .expiry-stat span {
        display:block;
        font-size:12px;
        font-weight:700;
        letter-spacing:.04em;
        text-transform:uppercase;
        color:#63788d;
        margin-bottom:6px;
    }
    .expiry-stat strong {
        display:block;
        font-size:20px;
        color:#17324d;
    }
    .expiry-panel-note {
        margin:0;
        font-size:15px;
        line-height:1.7;
        color:#486075;
    }
    .expiry-panel-warning .expiry-panel-head { background:linear-gradient(135deg, #c98010, #e7a52f); }
    .expiry-panel-warning .expiry-stat.primary { background:#fff6df; border-color:#f2d08a; }
    .expiry-panel-warning .expiry-stat.primary strong { color:#9a5e00; }
    .expiry-panel-danger .expiry-panel-head { background:linear-gradient(135deg, #b64747, #d96a6a); }
    .expiry-panel-danger .expiry-stat.primary { background:#fff1f1; border-color:#efc1c1; }
    .expiry-panel-danger .expiry-stat.primary strong { color:#a63d3d; }
    .expiry-panel-success .expiry-panel-head { background:linear-gradient(135deg, #13795b, #1fa57b); }
    .expiry-panel-success .expiry-stat.primary { background:#ecfff7; border-color:#bfe9d7; }
    .expiry-panel-success .expiry-stat.primary strong { color:#13795b; }
    .countdown-badge {
        display:inline-flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        min-width:120px;
        padding:16px 18px;
        border-radius:18px;
        background:#fff;
        border:1px solid #e2ebf3;
        box-shadow:0 10px 24px rgba(16, 38, 58, 0.08);
    }
    .countdown-badge strong {
        font-size:34px;
        line-height:1;
        color:#17324d;
    }
    .countdown-badge span {
        margin-top:6px;
        font-size:12px;
        font-weight:700;
        letter-spacing:.06em;
        text-transform:uppercase;
        color:#6d8194;
    }
    .renew-action-card {
        margin:0 0 22px;
        padding:22px 24px;
        border-radius:20px;
        border:1px solid #f1cc82;
        background:linear-gradient(135deg, #fff7df, #fff2c4);
        box-shadow:0 12px 28px rgba(187, 134, 32, 0.14);
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:18px;
    }
    .renew-action-card.expired {
        border-color:#efb4b4;
        background:linear-gradient(135deg, #fff0f0, #ffdede);
        box-shadow:0 12px 28px rgba(182, 71, 71, 0.12);
    }
    .renew-action-copy h3 {
        margin:0 0 8px;
        font-size:24px;
        color:#17324d;
    }
    .renew-action-copy p {
        margin:0;
        color:#4e6377;
        font-size:15px;
        line-height:1.7;
    }
    @media (max-width: 767px) {
        .expiry-panel-grid { grid-template-columns:1fr; }
        .expiry-panel-head { flex-direction:column; align-items:flex-start; }
        .expiry-panel-head-main { width:100%; }
        .countdown-badge { width:100%; }
        .renew-action-card { flex-direction:column; align-items:flex-start; }
    }
    </style>
    <script type="text/javascript">
    var popUpWin = 0;
    function popUpWindow(URLStr, left, top, width, height) {
        if (popUpWin) {
            if (!popUpWin.closed) {
                popUpWin.close();
            }
        }
        popUpWin = open(URLStr, 'popUpWin', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,copyhistory=yes,width=' + 900 + ',height=' + 700 + ',left=' + left + ', top=' + top + ',screenX=' + left + ',screenY=' + top + '');
    }
    </script>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="ts-main-content">
    <?php include('includes/sidebar.php'); ?>
    <div class="content-wrapper">
        <div class="container-fluid">
            <section class="ui-hero">
                <span class="ui-badge"><i class="fa fa-bed"></i> Booking Summary</span>
                <h2>Room Details</h2>
                <p>Review your full room, fee, personal, guardian, and address information from a clearer booking summary page.</p>
            </section>

            <?php if (!empty($bookings)) { ?>
                <div class="ui-actions" style="margin:0 0 20px;">
                    <a href="payment-history.php" class="btn btn-primary">
                        <i class="fa fa-credit-card"></i> View Payment History
                    </a>
                </div>
            <?php } ?>

            <?php if ($activeBooking && in_array($activeBooking['occupancy_status'], array('active', 'expiring_soon', 'expired'), true)) { ?>
                <?php
                $panelClass = 'expiry-panel-success';
                $panelTitle = 'Current Room Status';
                $panelSummary = 'Your booking is active and your room access is in good standing.';
                $primaryValue = (int) $activeBooking['remaining_days'] . ' day(s)';
                $primaryLabel = 'Time Remaining';

                if ($activeBooking['occupancy_status'] === 'expiring_soon') {
                    $panelClass = 'expiry-panel-warning';
                    $panelTitle = 'Room Expiring Soon';
                    $panelSummary = 'Your room is close to expiry. Renew now to keep the same room without interruption.';
                } elseif ($activeBooking['occupancy_status'] === 'expired') {
                    $panelClass = 'expiry-panel-danger';
                    $panelTitle = 'Room Booking Expired';
                    $panelSummary = 'This booking has already expired. Renew or make a new payment to continue your stay.';
                    $primaryValue = 'Expired';
                }
                ?>
                <?php if ($renewableRoom && !empty($renewableRoom['renew_allowed']) && in_array($activeBooking['occupancy_status'], array('expiring_soon', 'expired'), true)) { ?>
                    <div class="renew-action-card<?php echo $activeBooking['occupancy_status'] === 'expired' ? ' expired' : ''; ?>">
                        <div class="renew-action-copy">
                            <h3><i class="fa fa-refresh" style="margin-right:8px;"></i>Renew Now</h3>
                            <p>
                                <?php if ($activeBooking['occupancy_status'] === 'expired') { ?>
                                    Your previous stay for Room <?php echo htmlspecialchars($activeBooking['roomno']); ?> has ended. Renew the same room and continue with a fresh payment.
                                <?php } else { ?>
                                    Room <?php echo htmlspecialchars($activeBooking['roomno']); ?> expires in <?php echo (int) $activeBooking['remaining_days']; ?> day(s). Renew now to keep your seat without a last-minute rush.
                                <?php } ?>
                            </p>
                        </div>
                        <a href="book-hostel.php?renew=same-room#confirm-booking-panel" class="btn btn-renew">Renew and Pay</a>
                    </div>
                <?php } ?>
                <div class="expiry-panel <?php echo $panelClass; ?>">
                    <div class="expiry-panel-head">
                        <div class="expiry-panel-head-main">
                            <span class="expiry-icon-badge">
                                <i class="fa <?php echo $activeBooking['occupancy_status'] === 'expired' ? 'fa-times-circle' : ($activeBooking['occupancy_status'] === 'expiring_soon' ? 'fa-exclamation-triangle' : 'fa-check-circle'); ?>"></i>
                            </span>
                            <div>
                                <h3><?php echo htmlspecialchars($panelTitle); ?></h3>
                                <p><?php echo htmlspecialchars($panelSummary); ?></p>
                            </div>
                        </div>
                        <div class="countdown-badge">
                            <strong><?php echo $activeBooking['occupancy_status'] === 'expired' ? '0' : (int) $activeBooking['remaining_days']; ?></strong>
                            <span><?php echo $activeBooking['occupancy_status'] === 'expired' ? 'Days Left' : 'Days Left'; ?></span>
                        </div>
                    </div>
                    <div class="expiry-panel-body">
                        <div class="expiry-panel-grid">
                            <div class="expiry-stat primary">
                                <span><?php echo htmlspecialchars($primaryLabel); ?></span>
                                <strong><?php echo htmlspecialchars($primaryValue); ?></strong>
                            </div>
                            <div class="expiry-stat">
                                <span>Room Number</span>
                                <strong><?php echo htmlspecialchars($activeBooking['roomno']); ?></strong>
                            </div>
                            <div class="expiry-stat">
                                <span>Expiry Date</span>
                                <strong><?php echo htmlspecialchars($activeBooking['end_date']); ?></strong>
                            </div>
                        </div>
                        <p class="expiry-panel-note">
                            <?php
                            if ($activeBooking['occupancy_status'] === 'expired') {
                                echo 'The previous stay ended on ' . htmlspecialchars($activeBooking['end_date']) . '. Renewing will take you to the booking and payment flow for the same room.';
                            } else {
                                echo 'You have ' . (int) $activeBooking['remaining_days'] . ' day(s) left before Room ' . htmlspecialchars($activeBooking['roomno']) . ' expires on ' . htmlspecialchars($activeBooking['end_date']) . '.';
                            }
                            ?>
                        </p>
                    </div>
                </div>
            <?php } ?>

            <?php if (empty($bookings)) { ?>
                <div class="ui-surface">
                    <div class="ui-empty">
                        <h3 class="ui-section-title">No booking record found</h3>
                        <p>Your room allocation has not been saved yet. You can continue with the hostel booking form when you are ready.</p>
                        <a href="book-hostel.php" class="btn btn-primary">Book Hostel</a>
                    </div>
                </div>
            <?php } ?>

            <?php if ($renewableRoom) { ?>
                <div class="ui-surface" style="margin-bottom:20px;">
                    <div class="ui-surface-body">
                        <div class="selected-room-banner" style="margin-bottom:0;">
                            Renew your previous room <strong>Room <?php echo (int) $renewableRoom['room_no']; ?></strong>
                            at the current price of <strong>Rs <?php echo (int) $renewableRoom['fees']; ?>/month</strong>.
                            <a href="book-hostel.php?renew=same-room#confirm-booking-panel" class="btn btn-renew" style="margin-left:12px;">Renew Same Room</a>
                        </div>
                    </div>
                </div>
            <?php } ?>

            <?php foreach ($bookings as $row) {
                $fpm = (int) $row['feespm'];
                $dr = (int) $row['duration'];
                $totalFee = (int) $row['foodstatus'] === 1 ? (($dr * $fpm) + 2000) : ($dr * $fpm);
                $isLatestBooking = $latestBooking && (int) $latestBooking['id'] === (int) $row['id'];
            ?>
                <div class="ui-surface room-card">
                    <div class="room-card-head">
                        <div>
                            <h3>Room #<?php echo htmlspecialchars($row['roomno']); ?></h3>
                            <p>Booking created on <?php echo htmlspecialchars($row['postingDate']); ?></p>
                        </div>
                        <div class="ui-actions" style="margin-top:0;">
                            <span class="info-chip"><?php echo (int) $row['foodstatus'] === 0 ? 'Without Food' : 'With Food'; ?></span>
                            <span class="info-chip status-<?php echo strtolower(htmlspecialchars($row['booking_status_label'])); ?>"><?php echo htmlspecialchars($row['booking_status_label']); ?></span>
                            <?php if ($isLatestBooking && $renewableRoom) { ?>
                                <a href="book-hostel.php?renew=same-room#confirm-booking-panel" class="btn btn-renew">Renew Same Room</a>
                            <?php } ?>
                            <a href="javascript:void(0);" class="btn btn-primary" onClick="popUpWindow('full-profile.php?id=<?php echo urlencode($row['emailid']); ?>');" title="View Full Details">Print Details</a>
                        </div>
                    </div>

                    <div class="ui-grid cols-2">
                        <div class="ui-data-card">
                            <h4 class="ui-section-title">Room Information</h4>
                            <div class="ui-data-list">
                                <div class="ui-data-item">
                                    <span>Room No</span>
                                    <strong><?php echo htmlspecialchars($row['roomno']); ?></strong>
                                </div>
                                <div class="ui-data-item">
                                    <span>Seater</span>
                                    <strong><?php echo htmlspecialchars($row['seater']); ?></strong>
                                </div>
                                <div class="ui-data-item">
                                    <span>Fees Per Month</span>
                                    <strong><?php echo htmlspecialchars($row['feespm']); ?></strong>
                                </div>
                                <div class="ui-data-item">
                                    <span>Duration</span>
                                    <strong><?php echo htmlspecialchars($row['duration']); ?> Months</strong>
                                </div>
                                <div class="ui-data-item">
                                    <span>Stay From</span>
                                    <strong><?php echo htmlspecialchars($row['stayfrom']); ?></strong>
                                </div>
                                <div class="ui-data-item">
                                    <span>Stay Until</span>
                                    <strong><?php echo htmlspecialchars($row['end_date']); ?></strong>
                                </div>
                                <div class="ui-data-item">
                                    <span>Days Remaining</span>
                                    <strong>
                                        <?php
                                        if ($row['occupancy_status'] === 'expired') {
                                            echo 'Expired';
                                        } elseif (!$row['has_started']) {
                                            echo 'Starts in ' . (int) $row['starts_in_days'] . ' day(s)';
                                        } else {
                                            echo (int) $row['remaining_days'] . ' day(s)';
                                        }
                                        ?>
                                    </strong>
                                </div>
                                <div class="ui-data-item">
                                    <span>Booking Status</span>
                                    <strong><?php echo htmlspecialchars($row['booking_status_label']); ?></strong>
                                </div>
                                <div class="ui-data-item full">
                                    <span>Total Fee</span>
                                    <strong><?php echo htmlspecialchars($totalFee); ?></strong>
                                </div>
                                <div class="ui-data-item">
                                    <span>Payment Method</span>
                                    <strong><?php echo htmlspecialchars(ucfirst(isset($row['payment_method']) ? (string) $row['payment_method'] : 'N/A')); ?></strong>
                                </div>
                                <div class="ui-data-item">
                                    <span>Payment Status</span>
                                    <strong><?php echo htmlspecialchars(ucfirst(isset($row['payment_status']) ? (string) $row['payment_status'] : 'N/A')); ?></strong>
                                </div>
                                <div class="ui-data-item full">
                                    <span>Transaction ID</span>
                                    <strong><?php echo htmlspecialchars(!empty($row['transaction_id']) ? $row['transaction_id'] : 'N/A'); ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="ui-data-card">
                            <h4 class="ui-section-title">Personal Information</h4>
                            <div class="ui-data-list">
                                <div class="ui-data-item">
                                    <span>Registration No</span>
                                    <strong><?php echo htmlspecialchars($row['regno']); ?></strong>
                                </div>
                                <div class="ui-data-item">
                                    <span>Course</span>
                                    <strong><?php echo htmlspecialchars($row['course']); ?></strong>
                                </div>
                                <div class="ui-data-item full">
                                    <span>Full Name</span>
                                    <strong><?php echo htmlspecialchars(trim($row['firstName'] . ' ' . $row['middleName'] . ' ' . $row['lastName'])); ?></strong>
                                </div>
                                <div class="ui-data-item">
                                    <span>Email</span>
                                    <strong><?php echo htmlspecialchars($row['emailid']); ?></strong>
                                </div>
                                <div class="ui-data-item">
                                    <span>Contact No</span>
                                    <strong><?php echo htmlspecialchars($row['contactno']); ?></strong>
                                </div>
                                <div class="ui-data-item">
                                    <span>Gender</span>
                                    <strong><?php echo htmlspecialchars($row['gender']); ?></strong>
                                </div>
                                <div class="ui-data-item">
                                    <span>Emergency Contact</span>
                                    <strong><?php echo htmlspecialchars($row['egycontactno']); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ui-grid cols-2" style="margin-top: 20px;">
                        <div class="ui-data-card">
                            <h4 class="ui-section-title">Guardian Information</h4>
                            <div class="ui-data-list">
                                <div class="ui-data-item">
                                    <span>Guardian Name</span>
                                    <strong><?php echo htmlspecialchars($row['guardianName']); ?></strong>
                                </div>
                                <div class="ui-data-item">
                                    <span>Relation</span>
                                    <strong><?php echo htmlspecialchars($row['guardianRelation']); ?></strong>
                                </div>
                                <div class="ui-data-item full">
                                    <span>Guardian Contact</span>
                                    <strong><?php echo htmlspecialchars($row['guardianContactno']); ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="ui-data-card">
                            <h4 class="ui-section-title">Address Information</h4>
                            <div class="ui-data-list">
                                <div class="ui-data-item full">
                                    <span>Correspondence Address</span>
                                    <strong><?php echo htmlspecialchars($row['corresAddress'] . ', ' . $row['corresCIty'] . ', ' . $row['corresState'] . ' - ' . $row['corresPincode']); ?></strong>
                                </div>
                                <div class="ui-data-item full">
                                    <span>Permanent Address</span>
                                    <strong><?php echo htmlspecialchars($row['pmntAddress'] . ', ' . $row['pmntCity'] . ', ' . $row['pmnatetState'] . ' - ' . $row['pmntPincode']); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap-select.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap.min.js"></script>
<script src="js/Chart.min.js"></script>
<script src="js/fileinput.js"></script>
<script src="js/chartData.js"></script>
<script src="js/main.js"></script>
</body>
</html>
