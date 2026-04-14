<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
include('../includes/booking.php');
check_login();

$message = '';
$messageType = 'success';
$roomFeatureColumns = hms_get_room_feature_columns($mysqli);
$supportsRoomFeatures = hms_rooms_supports_feature_system($mysqli);

if (isset($_POST['submit'])) {
    $seater = (int) $_POST['seater'];
    $roomno = (int) $_POST['rmno'];
    $fees = (int) $_POST['fee'];
    $roomStatus = isset($_POST['room_status']) ? trim($_POST['room_status']) : 'available';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $features = hms_prepare_room_feature_payload($_POST);

    $sql = "SELECT room_no FROM rooms WHERE room_no=?";
    $stmt1 = $mysqli->prepare($sql);
    $stmt1->bind_param('i', $roomno);
    $stmt1->execute();
    $stmt1->store_result();
    $rowCnt = $stmt1->num_rows;
    $stmt1->close();

    if ($rowCnt > 0) {
        $message = 'Room already exists.';
        $messageType = 'danger';
    } else {
        $columns = array('seater', 'room_no', 'fees');
        $types = 'iii';
        $values = array($seater, $roomno, $fees);

        foreach (hms_get_room_feature_keys() as $key => $label) {
            if (in_array($key, $roomFeatureColumns, true)) {
                $columns[] = $key;
                $types .= 'i';
                $values[] = $features[$key];
            }
        }

        if (in_array('description', $roomFeatureColumns, true)) {
            $columns[] = 'description';
            $types .= 's';
            $values[] = $description;
        }

        if (in_array('room_status', $roomFeatureColumns, true)) {
            $columns[] = 'room_status';
            $types .= 's';
            $values[] = $roomStatus;
        }

        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $query = "INSERT INTO rooms (" . implode(', ', $columns) . ") VALUES(" . $placeholders . ")";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();
        $stmt->close();

        $message = 'Room has been added successfully.';
        $messageType = 'success';
    }
}
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="theme-color" content="#3e454c">
    <title>Create Room</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-social.css">
    <link rel="stylesheet" href="css/bootstrap-select.css">
    <link rel="stylesheet" href="css/fileinput.min.css">
    <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include('includes/header.php'); ?>
    <div class="ts-main-content">
        <?php include('includes/sidebar.php'); ?>
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="admin-page-header admin-page-header-management">
                            <div>
                                <span class="admin-page-kicker">Room Setup</span>
                                <h2 class="page-title">Add a Room</h2>
                                <p class="admin-page-subtitle">Add a new hostel room and configure its details using the same backend save flow already in place.</p>
                            </div>
                        </div>

                        <div class="admin-form-shell">
                            <div class="panel panel-default admin-form-card">
                                <div class="panel-heading">
                                    <h3 class="admin-form-title">Room Setup</h3>
                                    <p class="admin-form-subtitle">Configure room capacity, pricing, status, and feature details in a cleaner layout.</p>
                                </div>
                                <div class="panel-body">
                                    <?php if ($message !== '') { ?>
                                        <div class="admin-inline-alerts">
                                            <div class="alert alert-<?php echo $messageType; ?>"><?php echo htmlspecialchars($message); ?></div>
                                        </div>
                                    <?php } ?>

                                    <form method="post" class="admin-form">
                                        <div class="admin-form-section">
                                            <h4 class="admin-form-section-title">Basic Information</h4>
                                            <p class="admin-form-section-note">Set the room capacity, room number, and fee amount exactly as before.</p>
                                            <div class="admin-form-grid">
                                                <div class="admin-form-col-4">
                                                    <div class="form-group">
                                                        <label for="seater">Select Seater</label>
                                                        <select name="seater" id="seater" class="form-control" required>
                                                            <option value="">Select Seater</option>
                                                            <option value="1">Single Seater</option>
                                                            <option value="2">Two Seater</option>
                                                            <option value="3">Three Seater</option>
                                                            <option value="4">Four Seater</option>
                                                            <option value="5">Five Seater</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="admin-form-col-4">
                                                    <div class="form-group">
                                                        <label for="rmno">Room No.</label>
                                                        <input type="text" class="form-control" name="rmno" id="rmno" required>
                                                    </div>
                                                </div>
                                                <div class="admin-form-col-4">
                                                    <div class="form-group">
                                                        <label for="fee">Fee (Per Student)</label>
                                                        <input type="text" class="form-control" name="fee" id="fee" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if ($supportsRoomFeatures): ?>
                                            <div class="admin-form-section">
                                                <h4 class="admin-form-section-title">Availability and Features</h4>
                                                <p class="admin-form-section-note">Choose room status and any optional facilities supported by the current room schema.</p>
                                                <div class="admin-form-grid">
                                                    <div class="admin-form-col-4">
                                                        <div class="form-group">
                                                            <label for="room_status">Room Status</label>
                                                            <select name="room_status" id="room_status" class="form-control" required>
                                                                <option value="available">Available</option>
                                                                <option value="maintenance">Maintenance</option>
                                                                <option value="inactive">Inactive</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="admin-form-grid">
                                                    <div class="admin-form-col-3">
                                                        <div class="checkbox checkbox-inline">
                                                            <input type="checkbox" id="attached_bathroom" name="attached_bathroom" value="1">
                                                            <label for="attached_bathroom">Attached Bathroom</label>
                                                        </div>
                                                    </div>
                                                    <div class="admin-form-col-3">
                                                        <div class="checkbox checkbox-inline">
                                                            <input type="checkbox" id="air_conditioner" name="air_conditioner" value="1">
                                                            <label for="air_conditioner">Air Conditioner</label>
                                                        </div>
                                                    </div>
                                                    <div class="admin-form-col-3">
                                                        <div class="checkbox checkbox-inline">
                                                            <input type="checkbox" id="wifi" name="wifi" value="1">
                                                            <label for="wifi">Wi-Fi</label>
                                                        </div>
                                                    </div>
                                                    <div class="admin-form-col-3">
                                                        <div class="checkbox checkbox-inline">
                                                            <input type="checkbox" id="balcony" name="balcony" value="1">
                                                            <label for="balcony">Balcony</label>
                                                        </div>
                                                    </div>
                                                    <div class="admin-form-col-3">
                                                        <div class="checkbox checkbox-inline">
                                                            <input type="checkbox" id="study_table" name="study_table" value="1">
                                                            <label for="study_table">Study Table</label>
                                                        </div>
                                                    </div>
                                                    <div class="admin-form-col-8">
                                                        <div class="form-group">
                                                            <label for="description">Description</label>
                                                            <textarea class="form-control" rows="4" name="description" id="description" placeholder="Optional room details, atmosphere, or extra notes"></textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="admin-inline-alerts">
                                                <div class="alert alert-info">
                                                    Run the room feature SQL patch to enable room status, facilities, and room suggestion fields on this form.
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="admin-form-actions">
                                            <input class="btn btn-primary" type="submit" name="submit" value="Create Room">
                                            <a href="manage-rooms.php" class="btn admin-btn-secondary">Back to Rooms</a>
                                        </div>
                                    </form>
                                </div>
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
</body>
</html>
