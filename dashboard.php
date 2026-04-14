<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
include('includes/booking.php');
check_login();

$activeBooking = hms_get_active_booking_by_email($mysqli, $_SESSION['login']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'skip_preferences') {
        $_SESSION['pref_skipped'] = true;
        header("Location: dashboard.php");
        exit;
    } elseif (isset($_POST['action']) && $_POST['action'] === 'save_preferences') {
        $pref_seater = isset($_POST['pref_seater']) ? trim($_POST['pref_seater']) : '';
        $pref_attached_bathroom = isset($_POST['pref_attached_bathroom']) ? 1 : 0;
        $pref_air_conditioner = isset($_POST['pref_air_conditioner']) ? 1 : 0;
        $pref_wifi = isset($_POST['pref_wifi']) ? 1 : 0;
        $pref_balcony = isset($_POST['pref_balcony']) ? 1 : 0;
        $pref_study_table = isset($_POST['pref_study_table']) ? 1 : 0;
        
        $legacyId = (int) $_SESSION['id'];
        $pref_seater_val = $pref_seater !== '' ? (int)$pref_seater : null;

        $stmt = $mysqli->prepare('UPDATE userregistration SET pref_seater = ?, pref_attached_bathroom = ?, pref_air_conditioner = ?, pref_wifi = ?, pref_balcony = ?, pref_study_table = ? WHERE id = ?');
        $stmt->bind_param('iiiiiii', $pref_seater_val, $pref_attached_bathroom, $pref_air_conditioner, $pref_wifi, $pref_balcony, $pref_study_table, $legacyId);
        $stmt->execute();
        $stmt->close();
        
        header("Location: dashboard.php");
        exit;
    }
}

$userPrefs = hms_get_user_room_preferences($mysqli, $_SESSION['login']);
$hasPreferences = $userPrefs !== null && hms_has_room_preferences($userPrefs);
$showPrefPopup = !$hasPreferences && !isset($_SESSION['pref_skipped']);
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="theme-color" content="#183153">
    <title>Dashboard</title>
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
    .ui-metric.metric-profile .ui-metric-icon,
    .student-card.card-profile .icon { background:#e8f1ff; color:#285ea8; }
    .ui-metric.metric-booking .ui-metric-icon,
    .student-card.card-booking .icon { background:#fff3df; color:#b36a13; }
    .ui-metric.metric-days .ui-metric-icon,
    .student-card.card-room .icon { background:#eafbf3; color:#167a58; }
    .student-card.card-security .icon { background:#f3ebff; color:#6d46b2; }
    .student-card.card-access .icon { background:#ffeef1; color:#b54762; }
    .student-card.card-logout .icon { background:#fff1f1; color:#bf4b4b; }
    .ui-metric.metric-profile { border-top:4px solid #285ea8; }
    .ui-metric.metric-booking { border-top:4px solid #b36a13; }
    .ui-metric.metric-days { border-top:4px solid #167a58; }
    </style>
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="ts-main-content">
    <?php include('includes/sidebar.php'); ?>
    <div class="content-wrapper">
        <div class="container-fluid">
            <section class="ui-hero">
                <span class="ui-badge"><i class="fa fa-graduation-cap"></i> Student Dashboard</span>
                <h2>Welcome back, <?php echo htmlspecialchars(isset($_SESSION['name']) ? $_SESSION['name'] : 'Student'); ?>!</h2>
                <p style="font-size: 16px;">Track your hostel account, update your profile, review room information, and manage your security settings from one streamlined dashboard.</p>
            </section>

            <div class="ui-grid cols-3" style="margin-bottom: 28px;">
                <div class="ui-metric metric-profile">
                    <div class="ui-metric-icon" style="background: linear-gradient(135deg, #e8f1ff, #cde0ff); box-shadow: 0 4px 15px rgba(40,94,168,0.15);"><i class="fa fa-user"></i></div>
                    <div class="ui-metric-value">Profile</div>
                    <div class="ui-metric-label">Keep your personal details updated and accurate.</div>
                </div>
                <div class="ui-metric metric-booking">
                    <div class="ui-metric-icon" style="background: linear-gradient(135deg, #fff3df, #ffe3b7); box-shadow: 0 4px 15px rgba(179,106,19,0.15);"><i class="fa fa-building-o"></i></div>
                    <div class="ui-metric-value">Booking</div>
                    <div class="ui-metric-label">Check your hostel room information and reservation status.</div>
                </div>
                <div class="ui-metric metric-days">
                    <div class="ui-metric-icon" style="background: linear-gradient(135deg, #eafbf3, #c8f5df); box-shadow: 0 4px 15px rgba(22,122,88,0.15);"><i class="fa fa-calendar"></i></div>
                    <div class="ui-metric-value">
                        <?php 
                        if ($activeBooking) {
                            if ($activeBooking['occupancy_status'] === 'expiring_soon') {
                                echo '<span class="text-warning"><i class="fa fa-exclamation-triangle"></i> ' . (int) $activeBooking['remaining_days'] . ' Days Left!</span>';
                            } elseif ($activeBooking['occupancy_status'] === 'expired') {
                                echo '<span class="text-danger"><i class="fa fa-times-circle"></i> Expired</span>';
                            } else {
                                echo (int) $activeBooking['remaining_days'] . ' Days';
                            }
                        } else {
                            echo 'No Booking';
                        }
                        ?>
                    </div>
                    <div class="ui-metric-label">
                        <?php
                        if ($activeBooking) {
                            if ($activeBooking['occupancy_status'] === 'expired') {
                                echo 'Your room booking expired on <strong>' . htmlspecialchars($activeBooking['end_date']) . '</strong>. Please renew to maintain access.';
                            } else {
                                echo $activeBooking['has_started']
                                    ? 'Remaining on your current room booking until <strong>' . htmlspecialchars($activeBooking['end_date']) . '</strong>.'
                                    : 'Your room booking starts in <strong>' . (int) $activeBooking['starts_in_days'] . '</strong> day(s) and ends on <strong>' . htmlspecialchars($activeBooking['end_date']) . '</strong>.';
                            }
                        } else {
                            echo 'No active room booking right now. You can book a new room when needed.';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <h3 class="ui-section-title" style="margin-bottom: 20px; font-weight:600;"><i class="fa fa-th-large" style="color:var(--ui-primary); margin-right:8px;"></i> Quick Actions</h3>
            
            <div class="student-grid">
                <div class="student-card card-profile">
                    <div class="icon"><i class="fa fa-user"></i></div>
                    <h3>My Profile</h3>
                    <p>View and update your registration details, contact information, and student profile in a cleaner form layout.</p>
                    <a href="my-profile.php" class="ui-link">Open profile <i class="fa fa-arrow-right"></i></a>
                </div>
                <div class="student-card card-booking">
                    <div class="icon"><i class="fa fa-building-o"></i></div>
                    <h3>Book Hostel</h3>
                    <p>Complete your hostel booking form with better sectioning, clearer labels, and improved mobile spacing.</p>
                    <a href="book-hostel.php" class="ui-link">Start booking <i class="fa fa-arrow-right"></i></a>
                </div>
                <div class="student-card card-room">
                    <div class="icon"><i class="fa fa-bed"></i></div>
                    <h3>Room Details</h3>
                    <p>Review your allocated room, fee details, guardian information, and saved address records.</p>
                    <a href="room-details.php" class="ui-link">View room details <i class="fa fa-arrow-right"></i></a>
                </div>
                <div class="student-card card-security">
                    <div class="icon"><i class="fa fa-lock"></i></div>
                    <h3>Change Password</h3>
                    <p>Keep your account secure with a simpler password update page and clearer account status messages.</p>
                    <a href="change-password.php" class="ui-link">Update password <i class="fa fa-arrow-right"></i></a>
                </div>
                <div class="student-card card-access">
                    <div class="icon"><i class="fa fa-history"></i></div>
                    <h3>Access Log</h3>
                    <p>Review recent sign-ins in a responsive table that stays readable on phones, tablets, and larger screens.</p>
                    <a href="access-log.php" class="ui-link">See access log <i class="fa fa-arrow-right"></i></a>
                </div>
                <div class="student-card card-logout">
                    <div class="icon"><i class="fa fa-sign-out"></i></div>
                    <h3>Logout</h3>
                    <p>Finish your session safely whenever you are using a shared campus or hostel computer.</p>
                    <a href="logout.php" class="ui-link">Sign out <i class="fa fa-arrow-right"></i></a>
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

<?php if ($showPrefPopup): ?>
<div class="modal fade" id="preferencesModal" tabindex="-1" role="dialog" aria-labelledby="preferencesModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="preferencesModalLabel">Set Your Room Preferences</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="document.getElementById('skipForm').submit();">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="post" action="">
          <input type="hidden" name="action" value="save_preferences">
          <div class="modal-body">
              <p class="text-muted" style="margin-bottom:15px;">Tell us what type of room you are interested in. We'll use this to recommend rooms when you book a hostel.</p>
              
              <div class="form-group">
                  <label for="pref_seater">Preferred Seater</label>
                  <select name="pref_seater" id="pref_seater" class="form-control">
                      <option value="">Any Seater</option>
                      <option value="1">Single Seater</option>
                      <option value="2">Two Seater</option>
                      <option value="3">Three Seater</option>
                      <option value="4">Four Seater</option>
                      <option value="5">Five Seater</option>
                  </select>
              </div>

              <div class="row">
                  <div class="col-sm-6"><label style="font-weight:normal;" class="ui-check-option"><input type="checkbox" name="pref_attached_bathroom" value="1"> Attached Bathroom</label></div>
                  <div class="col-sm-6"><label style="font-weight:normal;" class="ui-check-option"><input type="checkbox" name="pref_air_conditioner" value="1"> Air Conditioner</label></div>
                  <div class="col-sm-6"><label style="font-weight:normal;" class="ui-check-option"><input type="checkbox" name="pref_wifi" value="1"> Wi-Fi</label></div>
                  <div class="col-sm-6"><label style="font-weight:normal;" class="ui-check-option"><input type="checkbox" name="pref_balcony" value="1"> Balcony</label></div>
                  <div class="col-sm-6"><label style="font-weight:normal;" class="ui-check-option"><input type="checkbox" name="pref_study_table" value="1"> Study Table</label></div>
              </div>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-default" onclick="document.getElementById('skipForm').submit();">Skip for Now</button>
              <button type="submit" class="btn btn-primary">Save Preferences</button>
          </div>
      </form>
      <form id="skipForm" method="post" action="" style="display:none;">
          <input type="hidden" name="action" value="skip_preferences">
      </form>
    </div>
  </div>
</div>
<script>
$(document).ready(function() {
    $('#preferencesModal').modal('show');
});
</script>
<?php endif; ?>

</body>
</html>
