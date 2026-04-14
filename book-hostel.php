<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
include('includes/booking.php');
include('includes/email.php');
check_login();

$bookingError = '';
$bookingSuccess = '';
$existingBooking = hms_get_active_booking_by_email($mysqli, $_SESSION['login']);
$latestBooking = hms_get_latest_booking_by_email($mysqli, $_SESSION['login']);
$renewableRoom = hms_get_renewable_room_option($mysqli, $_SESSION['login']);
$rooms = hms_get_available_rooms($mysqli);
$isFilterApplied = !empty($_GET);
$preferences = hms_get_room_preferences($_GET);

if (!$isFilterApplied) {
    $userPrefs = hms_get_user_room_preferences($mysqli, $_SESSION['login']);
    if ($userPrefs !== null) {
        $preferences = $userPrefs;
    }
}

$suggestions = hms_get_room_suggestions($mysqli, $preferences);
$hasPreferences = hms_has_room_preferences($preferences);
$selectedRoomNo = isset($_POST['room']) ? (int) $_POST['room'] : 0;
$selectedRoomSeater = isset($_POST['seater']) ? (int) $_POST['seater'] : 0;
$selectedRoomFee = isset($_POST['fpm']) ? (int) $_POST['fpm'] : 0;
$minimumStayDate = hms_today()->modify('+1 day')->format('Y-m-d');
$selectedStayFrom = isset($_POST['stayf']) ? trim((string) $_POST['stayf']) : '';
$selectedDuration = isset($_POST['duration']) ? (int) $_POST['duration'] : 0;
$isRenewalRequest = isset($_GET['renew']) && $_GET['renew'] === 'same-room';

if ($isRenewalRequest && $renewableRoom) {
    $selectedRoomNo = (int) $renewableRoom['room_no'];
    $selectedRoomSeater = (int) $renewableRoom['seater'];
    $selectedRoomFee = (int) $renewableRoom['fees'];
    if (!empty($renewableRoom['previous_booking']['end_date'])) {
        $minimumStayDate = $renewableRoom['previous_booking']['end_date'];
        if ($selectedStayFrom === '') {
            $selectedStayFrom = $minimumStayDate;
        }
    }
}

if (isset($_POST['submit'])) {
    $roomno = $_POST['room'];
    $foodstatus = $_POST['foodstatus'];
    $stayfrom = $_POST['stayf'];
    $duration = $_POST['duration'];
    $course = $_POST['course'];
    $regno = $_POST['regno'];
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $gender = $_POST['gender'];
    $contactno = $_POST['contact'];
    $emailid = $_POST['email'];
    $emcntno = $_POST['econtact'];
    $gurname = $_POST['gname'];
    $gurrelation = $_POST['grelation'];
    $gurcntno = $_POST['gcontact'];
    $caddress = $_POST['address'];
    $ccity = $_POST['city'];
    $cstate = $_POST['state'];
    $cpincode = $_POST['pincode'];
    $paddress = $_POST['paddress'];
    $pcity = $_POST['pcity'];
    $pstate = $_POST['pstate'];
    $ppincode = $_POST['ppincode'];

    $existingBooking = hms_get_active_booking_by_email($mysqli, $emailid);
    $roomAvailability = hms_get_room_availability($mysqli, $roomno);
    $stayFromDate = hms_parse_booking_date($stayfrom);
    $minimumAllowedStayDate = hms_today()->modify('+1 day');

    $renewalAllowedForActiveBooking = false;
    if (
        $isRenewalRequest &&
        $renewableRoom &&
        $existingBooking &&
        (int) $roomno === (int) $renewableRoom['room_no'] &&
        in_array($existingBooking['occupancy_status'], array('expiring_soon', 'expired'), true)
    ) {
        $renewalAllowedForActiveBooking = true;
    }

    if ($existingBooking && !$renewalAllowedForActiveBooking) {
        $bookingError = 'You already have an active hostel booking.';
    } elseif (!$stayFromDate) {
        $bookingError = 'Please select a valid stay from date.';
    } elseif ($renewalAllowedForActiveBooking && !empty($existingBooking['end_date']) && $stayFromDate < hms_parse_booking_date($existingBooking['end_date'])) {
        $bookingError = 'Renewal stay from date must be on or after your current booking end date.';
    } elseif ($stayFromDate < $minimumAllowedStayDate) {
        $bookingError = 'Stay from date must be a future date.';
    } elseif ((int) $duration <= 0) {
        $bookingError = 'Please select a valid duration.';
    } elseif (!$roomAvailability['exists']) {
        $bookingError = 'Selected room does not exist.';
    } elseif ($roomAvailability['room']['room_status'] !== 'available') {
        $bookingError = 'Selected room is not currently open for booking.';
    } elseif ($roomAvailability['is_full'] && !$renewalAllowedForActiveBooking) {
        $bookingError = 'Selected room is already full. Please choose another room.';
        $selectedRoomSeater = (int) $roomAvailability['room']['seater'];
        $selectedRoomFee = (int) $roomAvailability['room']['fees'];
    } else {
        $seater = (int) $roomAvailability['room']['seater'];
        $feespm = (int) $roomAvailability['room']['fees'];
        $selectedRoomSeater = $seater;
        $selectedRoomFee = $feespm;

        // Instead of immediate insertion, save all details into the session temporarily to handle payment.
        $_SESSION['pending_booking'] = array(
            'roomno' => $roomno,
            'seater' => $seater,
            'feespm' => $feespm,
            'foodstatus' => $foodstatus,
            'stayfrom' => $stayfrom,
            'duration' => $duration,
            'course' => $course,
            'regno' => $regno,
            'fname' => $fname,
            'mname' => $mname,
            'lname' => $lname,
            'gender' => $gender,
            'contactno' => $contactno,
            'emailid' => $emailid,
            'emcntno' => $emcntno,
            'gurname' => $gurname,
            'gurrelation' => $gurrelation,
            'gurcntno' => $gurcntno,
            'caddress' => $caddress,
            'ccity' => $ccity,
            'cstate' => $cstate,
            'cpincode' => $cpincode,
            'paddress' => $paddress,
            'pcity' => $pcity,
            'pstate' => $pstate,
            'ppincode' => $ppincode,
            'is_renewal' => $renewalAllowedForActiveBooking ? 1 : 0,
            'renewal_source_booking_id' => $renewalAllowedForActiveBooking ? (int) $existingBooking['id'] : 0
        );

        header("Location: payment.php");
        exit;
    }
}

$renewalWindowOpen = $renewableRoom && !empty($renewableRoom['renewal_window_open']) && !empty($renewableRoom['renew_allowed']);
$hasCompletedBooking = $existingBooking !== null && !$renewalWindowOpen;
$showBookingForm = ($selectedRoomNo > 0 || isset($_POST['submit']) || ($isRenewalRequest && $renewableRoom)) && (!$hasCompletedBooking || $renewalWindowOpen);
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="theme-color" content="#183153">
    <title>Student Hostel Registration</title>
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
    .suggestion-grid { display:grid; gap:18px; grid-template-columns:repeat(2, minmax(0, 1fr)); }
    .suggestion-card { padding:22px; border:1px solid #dbe6ef; }
    .suggestion-card h4 { margin:0 0 8px; color:#10263a; }
    .suggestion-meta { display:flex; flex-wrap:wrap; gap:10px; margin:12px 0 14px; }
    .suggestion-meta span, .feature-chip { display:inline-flex; align-items:center; padding:7px 12px; border-radius:999px; background:#eef5fb; color:#1f5f8b; font-size:12px; font-weight:600; }
    .feature-chip-wrap { display:flex; flex-wrap:wrap; gap:8px; margin:12px 0 16px; }
    .suggestion-score { color:#62758a; font-size:13px; }
    .suggestion-section + .suggestion-section { margin-top:28px; }
    .suggestion-highlight { background:linear-gradient(180deg, #f7fbff 0%, #ffffff 100%); box-shadow:0 16px 32px rgba(16, 38, 58, 0.08); }
    .suggestion-highlight .suggestion-rank { display:inline-flex; margin-bottom:12px; padding:6px 12px; border-radius:999px; background:#10263a; color:#fff; font-size:12px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; }
    .suggestion-alt-note { margin:10px 0 18px; color:#62758a; }
    .flow-step { display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; margin-right:10px; border-radius:50%; background:#10263a; color:#fff; font-size:13px; font-weight:700; }
    .flow-note { margin:0 0 18px; padding:14px 16px; border-left:4px solid #1f5f8b; background:#f4f8fc; color:#4f6478; }
    .booking-panel-hidden { display:none; }
    .selected-room-banner { margin:0 0 18px; padding:14px 16px; border-radius:16px; background:#eef7ff; color:#214364; border:1px solid #d7e8f7; }
    .btn-renew { background:linear-gradient(135deg, #0b6b57, #15967b); border-color:#0b6b57; color:#fff; }
    .btn-renew:hover, .btn-renew:focus { background:linear-gradient(135deg, #095847, #117b65); border-color:#095847; color:#fff; }
    @media (max-width: 991px) { .suggestion-grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
<?php include('includes/header.php'); ?>
<div class="ts-main-content">
    <?php include('includes/sidebar.php'); ?>
    <div class="content-wrapper">
        <div class="container-fluid">
            <section class="ui-hero">
                <span class="ui-badge"><i class="fa fa-building-o"></i> Hostel Booking</span>
                <h2>Book Your Hostel</h2>
                <p>Select your room preferences first, review suggested available rooms, then continue with the same booking flow.</p>
            </section>

            <div class="booking-layout">
                <div class="ui-surface">
                    <div class="ui-surface-head">
                        <h3><span class="flow-step">1</span>Find a Room</h3>
                        <p>Choose your preferred room type and features first. This section only helps you search and compare available rooms.</p>
                    </div>
                    <div class="ui-surface-body">
                        <div class="flow-note">
                            Use this section to filter rooms by seater, budget, and facilities. It does not submit your hostel booking.
                        </div>
                        <?php if ($renewableRoom) { ?>
                            <div class="selected-room-banner">
                                Renew your previous room: <strong>Room <?php echo (int) $renewableRoom['room_no']; ?></strong>
                                at the current price of <strong>Rs <?php echo (int) $renewableRoom['fees']; ?>/month</strong>.
                                <a href="book-hostel.php?renew=same-room#confirm-booking-panel" class="btn btn-renew" style="margin-left:12px;">Renew Same Room</a>
                            </div>
                        <?php } elseif (!$existingBooking && $latestBooking && !$renewableRoom) { ?>
                            <div class="flow-note">
                                Your previous room cannot be renewed right now because it is currently unavailable or full.
                            </div>
                        <?php } ?>
                        <form method="get" action="book-hostel.php#suggested-rooms-panel" class="ui-form">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="pref_seater">Preferred Seater</label>
                                        <select name="pref_seater" id="pref_seater" class="form-control">
                                            <option value="">Any Seater</option>
                                            <option value="1" <?php echo $preferences['seater'] === 1 ? 'selected' : ''; ?>>Single Seater</option>
                                            <option value="2" <?php echo $preferences['seater'] === 2 ? 'selected' : ''; ?>>Two Seater</option>
                                            <option value="3" <?php echo $preferences['seater'] === 3 ? 'selected' : ''; ?>>Three Seater</option>
                                            <option value="4" <?php echo $preferences['seater'] === 4 ? 'selected' : ''; ?>>Four Seater</option>
                                            <option value="5" <?php echo $preferences['seater'] === 5 ? 'selected' : ''; ?>>Five Seater</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="min_budget">Minimum Budget</label>
                                        <input type="number" name="min_budget" id="min_budget" class="form-control" value="<?php echo htmlspecialchars($preferences['min_budget']); ?>" placeholder="Optional">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="max_budget">Maximum Budget</label>
                                        <input type="number" name="max_budget" id="max_budget" class="form-control" value="<?php echo htmlspecialchars($preferences['max_budget']); ?>" placeholder="Optional">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6 col-md-3"><label class="ui-check-option"><input type="checkbox" name="attached_bathroom" value="1" <?php echo !empty($preferences['attached_bathroom']) ? 'checked' : ''; ?>> Attached Bathroom</label></div>
                                <div class="col-sm-6 col-md-3"><label class="ui-check-option"><input type="checkbox" name="air_conditioner" value="1" <?php echo !empty($preferences['air_conditioner']) ? 'checked' : ''; ?>> Air Conditioner</label></div>
                                <div class="col-sm-6 col-md-3"><label class="ui-check-option"><input type="checkbox" name="wifi" value="1" <?php echo !empty($preferences['wifi']) ? 'checked' : ''; ?>> Wi-Fi</label></div>
                                <div class="col-sm-6 col-md-3"><label class="ui-check-option"><input type="checkbox" name="balcony" value="1" <?php echo !empty($preferences['balcony']) ? 'checked' : ''; ?>> Balcony</label></div>
                                <div class="col-sm-6 col-md-3" style="margin-top: 12px;"><label class="ui-check-option"><input type="checkbox" name="study_table" value="1" <?php echo !empty($preferences['study_table']) ? 'checked' : ''; ?>> Study Table</label></div>
                            </div>
                            <div class="ui-actions">
                                <button type="submit" class="btn btn-primary">Find Matching Rooms</button>
                                <a href="book-hostel.php" class="btn btn-default">Reset Filter</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="ui-surface" id="suggested-rooms-panel">
                    <div class="ui-surface-head">
                        <h3><?php echo $hasPreferences ? 'Suggested Rooms' : 'Available Rooms'; ?></h3>
                        <p><?php echo $hasPreferences ? 'Review the rooms found from your preferences. Pick one room to open and auto-fill the confirm booking form.' : 'Browse all currently available rooms and pick one to continue to the booking form.'; ?></p>
                    </div>
                    <div class="ui-surface-body">
                        <?php if (empty($suggestions['best_matches']) && empty($suggestions['exact_matches']) && empty($suggestions['close_matches']) && empty($suggestions['other_available'])) { ?>
                            <div class="ui-empty">
                                <h3 class="ui-section-title">No available rooms matched</h3>
                                <p>Try relaxing one or two preferences to see more rooms.</p>
                            </div>
                        <?php } else { ?>
                            <?php if (!empty($suggestions['best_matches'])) { ?>
                                <div class="suggestion-section">
                                    <h3 class="ui-section-title">Best Matching Rooms</h3>
                                    <div class="suggestion-grid">
                                        <?php foreach ($suggestions['best_matches'] as $index => $room) { ?>
                                            <div class="ui-surface suggestion-card suggestion-highlight">
                                                <span class="suggestion-rank">Top Match <?php echo $index + 1; ?></span>
                                                <h4>Room <?php echo htmlspecialchars($room['room_no']); ?></h4>
                                                <div class="suggestion-score">Match score: <?php echo (int) $room['match_score']; ?></div>
                                                <div class="suggestion-meta">
                                                    <span><?php echo (int) $room['seater']; ?> Seater</span>
                                                    <span>Rs <?php echo (int) $room['fees']; ?>/month</span>
                                                    <span><?php echo (int) $room['remaining']; ?> bed(s) left</span>
                                                </div>
                                                <div class="feature-chip-wrap">
                                                    <?php $featureList = !empty($room['feature_list']) ? $room['feature_list'] : array('Basic Room'); foreach ($featureList as $feature) { ?>
                                                        <span class="feature-chip"><?php echo htmlspecialchars($feature); ?></span>
                                                    <?php } ?>
                                                </div>
                                                <?php if (!empty($room['description'])) { ?><p><?php echo htmlspecialchars($room['description']); ?></p><?php } ?>
                                                <button type="button" class="btn btn-primary" onclick="applySuggestedRoom('<?php echo (int) $room['room_no']; ?>', '<?php echo (int) $room['seater']; ?>', '<?php echo (int) $room['fees']; ?>');">Use This Room</button>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if (!empty($suggestions['exact_matches'])) { ?>
                                <div class="suggestion-section">
                                <h3 class="ui-section-title">Other Best Match Rooms</h3>
                                <div class="suggestion-grid">
                                    <?php foreach ($suggestions['exact_matches'] as $room) { ?>
                                        <div class="ui-surface suggestion-card">
                                            <h4>Room <?php echo htmlspecialchars($room['room_no']); ?></h4>
                                            <div class="suggestion-score"><?php echo $hasPreferences ? 'Match score: ' . (int) $room['match_score'] : 'Ready for booking'; ?></div>
                                            <div class="suggestion-meta">
                                                <span><?php echo (int) $room['seater']; ?> Seater</span>
                                                <span>Rs <?php echo (int) $room['fees']; ?>/month</span>
                                                <span><?php echo (int) $room['remaining']; ?> bed(s) left</span>
                                            </div>
                                            <div class="feature-chip-wrap">
                                                <?php $featureList = !empty($room['feature_list']) ? $room['feature_list'] : array('Basic Room'); foreach ($featureList as $feature) { ?>
                                                    <span class="feature-chip"><?php echo htmlspecialchars($feature); ?></span>
                                                <?php } ?>
                                            </div>
                                            <?php if (!empty($room['description'])) { ?><p><?php echo htmlspecialchars($room['description']); ?></p><?php } ?>
                                            <button type="button" class="btn btn-primary" onclick="applySuggestedRoom('<?php echo (int) $room['room_no']; ?>', '<?php echo (int) $room['seater']; ?>', '<?php echo (int) $room['fees']; ?>');">Use This Room</button>
                                        </div>
                                    <?php } ?>
                                </div>
                                </div>
                            <?php } ?>
                            <?php if (empty($suggestions['best_matches']) && empty($suggestions['exact_matches']) && !empty($suggestions['close_matches'])) { ?>
                                <div class="suggestion-section">
                                <h3 class="ui-section-title">Closest Alternative Rooms</h3>
                                <p class="suggestion-alt-note">No match according to your preference, but here are some you may like.</p>
                                <div class="suggestion-grid">
                                    <?php foreach ($suggestions['close_matches'] as $room) { ?>
                                        <div class="ui-surface suggestion-card">
                                            <h4>Room <?php echo htmlspecialchars($room['room_no']); ?></h4>
                                            <div class="suggestion-score">Match score: <?php echo (int) $room['match_score']; ?></div>
                                            <div class="suggestion-meta">
                                                <span><?php echo (int) $room['seater']; ?> Seater</span>
                                                <span>Rs <?php echo (int) $room['fees']; ?>/month</span>
                                                <span><?php echo (int) $room['remaining']; ?> bed(s) left</span>
                                            </div>
                                            <div class="feature-chip-wrap">
                                                <?php $featureList = !empty($room['feature_list']) ? $room['feature_list'] : array('Basic Room'); foreach ($featureList as $feature) { ?>
                                                    <span class="feature-chip"><?php echo htmlspecialchars($feature); ?></span>
                                                <?php } ?>
                                            </div>
                                            <?php if (!empty($room['description'])) { ?><p><?php echo htmlspecialchars($room['description']); ?></p><?php } ?>
                                            <button type="button" class="btn btn-default" onclick="applySuggestedRoom('<?php echo (int) $room['room_no']; ?>', '<?php echo (int) $room['seater']; ?>', '<?php echo (int) $room['fees']; ?>');">Use This Room</button>
                                        </div>
                                    <?php } ?>
                                </div>
                                </div>
                            <?php } ?>
                            <?php if (!empty($suggestions['other_available']) || (!empty($suggestions['close_matches']) && (!empty($suggestions['best_matches']) || !empty($suggestions['exact_matches'])))) { ?>
                                <div class="suggestion-section">
                                <h3 class="ui-section-title"><?php echo ($hasPreferences && empty($suggestions['best_matches']) && empty($suggestions['exact_matches'])) ? 'More Rooms You May Like' : 'Other Available Rooms'; ?></h3>
                                <?php if ($hasPreferences && empty($suggestions['best_matches']) && empty($suggestions['exact_matches']) && !empty($suggestions['other_available'])) { ?>
                                    <p class="suggestion-alt-note">No match according to your preference, but here are some you may like.</p>
                                <?php } ?>
                                <div class="suggestion-grid">
                                    <?php
                                    $otherAvailableRooms = $suggestions['other_available'];
                                    if (!empty($suggestions['close_matches']) && (!empty($suggestions['best_matches']) || !empty($suggestions['exact_matches']))) {
                                        $otherAvailableRooms = array_merge($suggestions['close_matches'], $otherAvailableRooms);
                                    }
                                    foreach ($otherAvailableRooms as $room) {
                                    ?>
                                        <div class="ui-surface suggestion-card">
                                            <h4>Room <?php echo htmlspecialchars($room['room_no']); ?></h4>
                                            <div class="suggestion-score"><?php echo $hasPreferences ? 'Match score: ' . (int) $room['match_score'] : 'Available now'; ?></div>
                                            <div class="suggestion-meta">
                                                <span><?php echo (int) $room['seater']; ?> Seater</span>
                                                <span>Rs <?php echo (int) $room['fees']; ?>/month</span>
                                                <span><?php echo (int) $room['remaining']; ?> bed(s) left</span>
                                            </div>
                                            <div class="feature-chip-wrap">
                                                <?php $featureList = !empty($room['feature_list']) ? $room['feature_list'] : array('Basic Room'); foreach ($featureList as $feature) { ?>
                                                    <span class="feature-chip"><?php echo htmlspecialchars($feature); ?></span>
                                                <?php } ?>
                                            </div>
                                            <?php if (!empty($room['description'])) { ?><p><?php echo htmlspecialchars($room['description']); ?></p><?php } ?>
                                            <button type="button" class="btn btn-default" onclick="applySuggestedRoom('<?php echo (int) $room['room_no']; ?>', '<?php echo (int) $room['seater']; ?>', '<?php echo (int) $room['fees']; ?>');">Use This Room</button>
                                        </div>
                                    <?php } ?>
                                </div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>

                <div class="ui-surface" id="confirm-booking-section">
                    <div class="ui-surface-head">
                        <h3><span class="flow-step">2</span>Confirm Booking</h3>
                        <p><?php echo $hasCompletedBooking ? 'Your hostel booking has already been saved, so the confirm booking form is hidden.' : 'This is the actual hostel booking form. It opens only after you press a room card\'s `Use This Room` button.'; ?></p>
                    </div>
                    <div id="confirm-booking-panel" class="ui-surface-body<?php echo $showBookingForm ? '' : ' booking-panel-hidden'; ?>">
                        <div class="flow-note">
                            This section is the real booking submission. Selecting `Use This Room` fills the room details here automatically for you.
                        </div>
                        <?php if ($isRenewalRequest && $renewableRoom) { ?>
                            <div class="selected-room-banner">
                                Renewing the same room you stayed in before. Current room details and latest monthly fee have been loaded automatically.
                            </div>
                        <?php } ?>
                        <div id="selected-room-banner" class="selected-room-banner" <?php echo $selectedRoomNo > 0 ? '' : 'style="display:none;"'; ?>>
                            Selected room: <strong id="selected-room-label"><?php echo $selectedRoomNo > 0 ? 'Room ' . (int) $selectedRoomNo : ''; ?></strong>
                        </div>
                        <form method="post" action="" class="ui-form">
                            <?php if ($bookingSuccess !== '') { ?>
                                <div class="alert alert-success"><?php echo htmlspecialchars($bookingSuccess); ?></div>
                            <?php } ?>
                            <?php if ($bookingError !== '') { ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($bookingError); ?></div>
                            <?php } ?>
                            <?php if ($existingBooking) { ?>
                                <div class="booking-status">
                                    <strong>Hostel already booked by you.</strong>
                                    Your existing booking record is already saved. You can still review it from the room details page.
                                </div>
                            <?php } ?>

                            <h3 class="ui-section-title" style="margin-top: 24px;">Room Related Info</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="room">Room No.</label>
                                        <select name="room" id="room" class="form-control" onChange="applyRoomDetailsFromSelect(); checkAvailability();" required <?php echo $existingBooking ? 'disabled' : ''; ?>>
                                            <option value="">Select Room</option>
                                            <?php foreach ($rooms as $roomItem) { ?>
                                                <option value="<?php echo (int) $roomItem['room_no']; ?>" data-seater="<?php echo (int) $roomItem['seater']; ?>" data-fee="<?php echo (int) $roomItem['fees']; ?>" <?php echo $selectedRoomNo === (int) $roomItem['room_no'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars('Room ' . $roomItem['room_no'] . ' - ' . $roomItem['remaining'] . ' bed(s) left'); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                        <span id="room-availability-status" class="ui-form-help"></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="seater">Seater</label>
                                        <input type="text" name="seater" id="seater" class="form-control input-readonly" readonly value="<?php echo $selectedRoomSeater > 0 ? (int) $selectedRoomSeater : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="fpm">Fees Per Month</label>
                                        <input type="text" name="fpm" id="fpm" class="form-control input-readonly" readonly value="<?php echo $selectedRoomFee > 0 ? (int) $selectedRoomFee : ''; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Food Status</label>
                                        <div class="ui-radio-group">
                                            <label class="ui-radio-option"><input type="radio" value="0" name="foodstatus" checked="checked" onchange="updateTotalAmount();"> Without Food</label>
                                            <label class="ui-radio-option"><input type="radio" value="1" name="foodstatus" onchange="updateTotalAmount();"> With Food (Rs 2000.00 extra per month)</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="stayf">Stay From</label>
                                        <input type="date" name="stayf" id="stayf" class="form-control" min="<?php echo htmlspecialchars($minimumStayDate); ?>" value="<?php echo htmlspecialchars($selectedStayFrom); ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="duration">Duration</label>
                                        <select name="duration" id="duration" class="form-control" onchange="updateTotalAmount();">
                                            <option value="">Select Duration in Month</option>
                                            <option value="1" <?php echo $selectedDuration === 1 ? 'selected' : ''; ?>>1</option>
                                            <option value="2" <?php echo $selectedDuration === 2 ? 'selected' : ''; ?>>2</option>
                                            <option value="3" <?php echo $selectedDuration === 3 ? 'selected' : ''; ?>>3</option>
                                            <option value="4" <?php echo $selectedDuration === 4 ? 'selected' : ''; ?>>4</option>
                                            <option value="5" <?php echo $selectedDuration === 5 ? 'selected' : ''; ?>>5</option>
                                            <option value="6" <?php echo $selectedDuration === 6 ? 'selected' : ''; ?>>6</option>
                                            <option value="7" <?php echo $selectedDuration === 7 ? 'selected' : ''; ?>>7</option>
                                            <option value="8" <?php echo $selectedDuration === 8 ? 'selected' : ''; ?>>8</option>
                                            <option value="9" <?php echo $selectedDuration === 9 ? 'selected' : ''; ?>>9</option>
                                            <option value="10" <?php echo $selectedDuration === 10 ? 'selected' : ''; ?>>10</option>
                                            <option value="11" <?php echo $selectedDuration === 11 ? 'selected' : ''; ?>>11</option>
                                            <option value="12" <?php echo $selectedDuration === 12 ? 'selected' : ''; ?>>12</option>
                                        </select>
                                        <div class="ui-form-help">Each month is counted as 30 days for room entitlement.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="ta">Total Amount</label>
                                        <input type="text" name="ta" id="ta" class="result form-control" readonly>
                                    </div>
                                </div>
                            </div>

                            <h3 class="ui-section-title">Personal Info</h3>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="course">Course</label>
                                        <select name="course" id="course" class="form-control" required>
                                            <option value="">Select Course</option>
                                            <?php
                                            $query = "SELECT * FROM courses";
                                            $stmt2 = $mysqli->prepare($query);
                                            $stmt2->execute();
                                            $res = $stmt2->get_result();
                                            while ($courseRow = $res->fetch_object()) {
                                            ?>
                                                <option value="<?php echo $courseRow->course_fn; ?>"><?php echo $courseRow->course_fn; ?>&nbsp;&nbsp;(<?php echo $courseRow->course_sn; ?>)</option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <?php
                            $aid = $_SESSION['id'];
                            $ret = "SELECT * FROM userregistration WHERE id=?";
                            $stmt = $mysqli->prepare($ret);
                            $stmt->bind_param('i', $aid);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            while ($row = $res->fetch_object()) {
                            ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="regno">Registration No</label>
                                            <input type="text" name="regno" id="regno" class="form-control input-readonly" value="<?php echo $row->regNo; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="gender">Gender</label>
                                            <input type="text" name="gender" id="gender" class="form-control input-readonly" value="<?php echo $row->gender; ?>" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="fname">First Name</label>
                                            <input type="text" name="fname" id="fname" class="form-control input-readonly" value="<?php echo $row->firstName; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="mname">Middle Name</label>
                                            <input type="text" name="mname" id="mname" class="form-control input-readonly" value="<?php echo $row->middleName; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="lname">Last Name</label>
                                            <input type="text" name="lname" id="lname" class="form-control input-readonly" value="<?php echo $row->lastName; ?>" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="contact">Contact No</label>
                                            <input type="text" name="contact" id="contact" value="<?php echo $row->contactNo; ?>" class="form-control input-readonly" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email Id</label>
                                            <input type="email" name="email" id="email" class="form-control input-readonly" value="<?php echo $row->email; ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="econtact">Emergency Contact</label>
                                        <input type="text" name="econtact" id="econtact" class="form-control" required="required">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="gname">Guardian Name</label>
                                        <input type="text" name="gname" id="gname" class="form-control" required="required">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="grelation">Guardian Relation</label>
                                        <input type="text" name="grelation" id="grelation" class="form-control" required="required">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="gcontact">Guardian Contact No</label>
                                        <input type="text" name="gcontact" id="gcontact" class="form-control" required="required">
                                    </div>
                                </div>
                            </div>

                            <h3 class="ui-section-title">Correspondence Address</h3>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <textarea rows="5" name="address" id="address" class="form-control" required="required"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="city">City</label>
                                        <input type="text" name="city" id="city" class="form-control" required="required">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="state">State</label>
                                        <select name="state" id="state" class="form-control" required>
                                            <option value="">Select State</option>
                                            <?php
                                            $query = "SELECT * FROM states";
                                            $stmt2 = $mysqli->prepare($query);
                                            $stmt2->execute();
                                            $res = $stmt2->get_result();
                                            while ($stateRow = $res->fetch_object()) {
                                            ?>
                                                <option value="<?php echo $stateRow->State; ?>"><?php echo $stateRow->State; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="pincode">Pincode</label>
                                        <input type="text" name="pincode" id="pincode" class="form-control" required="required">
                                    </div>
                                </div>
                            </div>

                            <h3 class="ui-section-title">Permanent Address</h3>
                            <div class="form-group">
                                <label>Permanent Address same as Correspondense address</label>
                                <div class="ui-inline-check">
                                    <label class="ui-check-option"><input type="checkbox" name="adcheck" value="1"> Copy correspondence address details</label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="paddress">Address</label>
                                        <textarea rows="5" name="paddress" id="paddress" class="form-control" required="required"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="pcity">City</label>
                                        <input type="text" name="pcity" id="pcity" class="form-control" required="required">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="pstate">State</label>
                                        <select name="pstate" id="pstate" class="form-control" required>
                                            <option value="">Select State</option>
                                            <?php
                                            $query = "SELECT * FROM states";
                                            $stmt2 = $mysqli->prepare($query);
                                            $stmt2->execute();
                                            $res = $stmt2->get_result();
                                            while ($pstateRow = $res->fetch_object()) {
                                            ?>
                                                <option value="<?php echo $pstateRow->State; ?>"><?php echo $pstateRow->State; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="ppincode">Pincode</label>
                                        <input type="text" name="ppincode" id="ppincode" class="form-control" required="required">
                                    </div>
                                </div>
                            </div>

                            <div class="ui-actions">
                                <a href="dashboard.php" class="btn btn-default">Cancel</a>
                                <input type="submit" name="submit" value="Register" class="btn btn-primary" <?php echo $existingBooking ? 'disabled' : ''; ?>>
                            </div>
                        </form>
                    </div>
                    <div id="confirm-booking-placeholder" class="ui-surface-body<?php echo $showBookingForm ? ' booking-panel-hidden' : ''; ?>">
                        <div class="ui-empty">
                            <?php if ($hasCompletedBooking) { ?>
                                <h3 class="ui-section-title">Hostel already booked by you</h3>
                                <p>Your booking has already been completed. You can review it from the room details page.</p>
                            <?php } elseif ($renewableRoom) { ?>
                                <h3 class="ui-section-title">Renew your previous room</h3>
                                <p>You can renew <strong>Room <?php echo (int) $renewableRoom['room_no']; ?></strong> using the current room fee and a new future stay date.</p>
                                <a href="book-hostel.php?renew=same-room#confirm-booking-panel" class="btn btn-renew">Renew Same Room</a>
                            <?php } else { ?>
                                <h3 class="ui-section-title">Choose a room first</h3>
                                <p>Press `Use This Room` on any suggested room to open the confirm booking form and fill the room details automatically.</p>
                            <?php } ?>
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
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/dataTables.bootstrap.min.js"></script>
<script src="js/Chart.min.js"></script>
<script src="js/fileinput.js"></script>
<script src="js/chartData.js"></script>
<script src="js/main.js"></script>
<script>
function applyRoomDetailsFromSelect() {
    var roomSelect = document.getElementById('room');
    var selectedOption = roomSelect.options[roomSelect.selectedIndex];
    var seater = selectedOption ? selectedOption.getAttribute('data-seater') : '';
    var fee = selectedOption ? selectedOption.getAttribute('data-fee') : '';
    document.getElementById('seater').value = seater || '';
    document.getElementById('fpm').value = fee || '';
    updateTotalAmount();
}

function applySuggestedRoom(roomNo, seater, fee) {
    var bookingPanel = document.getElementById('confirm-booking-panel');
    var bookingPlaceholder = document.getElementById('confirm-booking-placeholder');
    var selectedRoomBanner = document.getElementById('selected-room-banner');
    var selectedRoomLabel = document.getElementById('selected-room-label');
    var roomSelect = document.getElementById('room');
    roomSelect.value = roomNo;
    document.getElementById('seater').value = seater;
    document.getElementById('fpm').value = fee;
    bookingPanel.classList.remove('booking-panel-hidden');
    bookingPlaceholder.classList.add('booking-panel-hidden');
    selectedRoomBanner.style.display = 'block';
    selectedRoomLabel.textContent = 'Room ' + roomNo;
    updateTotalAmount();
    checkAvailability();
    bookingPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function updateTotalAmount() {
    var fee = parseInt(document.getElementById('fpm').value || '0', 10);
    var duration = parseInt(document.getElementById('duration').value || '0', 10);
    var foodStatus = document.querySelector('input[name="foodstatus"]:checked');
    var extraFoodCharge = foodStatus && foodStatus.value === '1' ? 2000 : 0;
    var totalAmount = duration > 0 ? ((fee + extraFoodCharge) * duration) : 0;
    document.getElementById('ta').value = totalAmount > 0 ? totalAmount : '';
}

function checkAvailability() {
    var roomValue = $("#room").val();
    if (roomValue === '') {
        $("#room-availability-status").html('');
        return;
    }
    jQuery.ajax({
        url: "check_availability.php",
        data: 'roomno=' + roomValue,
        type: "POST",
        success: function(data) {
            $("#room-availability-status").html(data);
        },
        error: function() {}
    });
}

$(document).ready(function() {
    $('input[type="checkbox"][name="adcheck"]').click(function() {
        if ($(this).prop("checked") == true) {
            $('#paddress').val($('#address').val());
            $('#pcity').val($('#city').val());
            $('#pstate').val($('#state').val());
            $('#ppincode').val($('#pincode').val());
        }
    });
    if (document.getElementById('room').value !== '') {
        applyRoomDetailsFromSelect();
    }
    if (window.location.hash === '#confirm-booking-panel') {
        var confirmBookingPanel = document.getElementById('confirm-booking-panel');
        if (confirmBookingPanel && !confirmBookingPanel.classList.contains('booking-panel-hidden')) {
            confirmBookingPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    } else if (window.location.hash === '#suggested-rooms-panel') {
        var suggestedRoomsPanel = document.getElementById('suggested-rooms-panel');
        if (suggestedRoomsPanel) {
            suggestedRoomsPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
});
</script>
</body>
</html>
