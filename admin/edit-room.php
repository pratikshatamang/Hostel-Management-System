<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
include('../includes/booking.php');
check_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$message = '';
$roomFeatureColumns = hms_get_room_feature_columns($mysqli);
$supportsRoomFeatures = hms_rooms_supports_feature_system($mysqli);

if (isset($_POST['submit'])) {
    $seater = (int) $_POST['seater'];
    $fees = (int) $_POST['fees'];
    $roomStatus = isset($_POST['room_status']) ? trim($_POST['room_status']) : 'available';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $features = hms_prepare_room_feature_payload($_POST);

    $setClauses = array('seater=?', 'fees=?');
    $types = 'ii';
    $values = array($seater, $fees);

    foreach (hms_get_room_feature_keys() as $key => $label) {
        if (in_array($key, $roomFeatureColumns, true)) {
            $setClauses[] = $key . '=?';
            $types .= 'i';
            $values[] = $features[$key];
        }
    }

    if (in_array('description', $roomFeatureColumns, true)) {
        $setClauses[] = 'description=?';
        $types .= 's';
        $values[] = $description;
    }

    if (in_array('room_status', $roomFeatureColumns, true)) {
        $setClauses[] = 'room_status=?';
        $types .= 's';
        $values[] = $roomStatus;
    }

    $types .= 'i';
    $values[] = $id;
    $query = "UPDATE rooms SET " . implode(', ', $setClauses) . " WHERE id=?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param($types, ...$values);
    $stmt->execute();
    $stmt->close();
    $message = 'Room details have been updated successfully.';
}

$ret = "SELECT * FROM rooms WHERE id=?";
$stmt = $mysqli->prepare($ret);
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$room = $res->fetch_assoc();
$stmt->close();

if (!$room) {
    exit('Room not found.');
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
    <title>Edit Room Details</title>
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
                                <span class="admin-page-kicker">Room Update</span>
                                <h2 class="page-title">Edit Room Details</h2>
                                <p class="admin-page-subtitle">Update room information without affecting the existing update flow or stored room values.</p>
                            </div>
                        </div>

                        <div class="admin-form-shell">
                            <div class="panel panel-default admin-form-card">
                                <div class="panel-heading">
                                    <h3 class="admin-form-title">Update Room</h3>
                                    <p class="admin-form-subtitle">The room number stays read-only while the rest of the room configuration remains editable.</p>
                                </div>
                                <div class="panel-body">
                                    <?php if ($message !== '') { ?>
                                        <div class="admin-inline-alerts">
                                            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                                        </div>
                                    <?php } ?>

                                    <form method="post" class="admin-form">
                                        <div class="admin-form-section">
                                            <h4 class="admin-form-section-title">Room Details</h4>
                                            <p class="admin-form-section-note">Review and update the current room values below. Prefilled data is unchanged.</p>
                                            <div class="admin-form-grid">
                                                <div class="admin-form-col-4">
                                                    <div class="form-group">
                                                        <label for="seater">Seater</label>
                                                        <select name="seater" id="seater" class="form-control" required>
                                                            <option value="1" <?php echo (int) $room['seater'] === 1 ? 'selected' : ''; ?>>Single Seater</option>
                                                            <option value="2" <?php echo (int) $room['seater'] === 2 ? 'selected' : ''; ?>>Two Seater</option>
                                                            <option value="3" <?php echo (int) $room['seater'] === 3 ? 'selected' : ''; ?>>Three Seater</option>
                                                            <option value="4" <?php echo (int) $room['seater'] === 4 ? 'selected' : ''; ?>>Four Seater</option>
                                                            <option value="5" <?php echo (int) $room['seater'] === 5 ? 'selected' : ''; ?>>Five Seater</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="admin-form-col-4">
                                                    <div class="form-group">
                                                        <label for="rmno">Room No</label>
                                                        <input type="text" class="form-control" name="rmno" id="rmno" value="<?php echo htmlspecialchars($room['room_no']); ?>" disabled>
                                                        <span class="help-block">Room number can't be changed.</span>
                                                    </div>
                                                </div>
                                                <div class="admin-form-col-4">
                                                    <div class="form-group">
                                                        <label for="fees">Fees (PM)</label>
                                                        <input type="text" class="form-control" name="fees" id="fees" value="<?php echo htmlspecialchars($room['fees']); ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if ($supportsRoomFeatures): ?>
                                            <div class="admin-form-section">
                                                <h4 class="admin-form-section-title">Availability and Features</h4>
                                                <p class="admin-form-section-note">Adjust room status and facilities while keeping the same update request and data handling.</p>
                                                <div class="admin-form-grid">
                                                    <div class="admin-form-col-4">
                                                        <div class="form-group">
                                                            <label for="room_status">Room Status</label>
                                                            <select name="room_status" id="room_status" class="form-control" required>
                                                                <option value="available" <?php echo $room['room_status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                                                                <option value="maintenance" <?php echo $room['room_status'] === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                                                <option value="inactive" <?php echo $room['room_status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="admin-form-grid">
                                                    <div class="admin-form-col-3">
                                                        <div class="checkbox checkbox-inline">
                                                            <input type="checkbox" id="attached_bathroom" name="attached_bathroom" value="1" <?php echo !empty($room['attached_bathroom']) ? 'checked' : ''; ?>>
                                                            <label for="attached_bathroom">Attached Bathroom</label>
                                                        </div>
                                                    </div>
                                                    <div class="admin-form-col-3">
                                                        <div class="checkbox checkbox-inline">
                                                            <input type="checkbox" id="air_conditioner" name="air_conditioner" value="1" <?php echo !empty($room['air_conditioner']) ? 'checked' : ''; ?>>
                                                            <label for="air_conditioner">Air Conditioner</label>
                                                        </div>
                                                    </div>
                                                    <div class="admin-form-col-3">
                                                        <div class="checkbox checkbox-inline">
                                                            <input type="checkbox" id="wifi" name="wifi" value="1" <?php echo !empty($room['wifi']) ? 'checked' : ''; ?>>
                                                            <label for="wifi">Wi-Fi</label>
                                                        </div>
                                                    </div>
                                                    <div class="admin-form-col-3">
                                                        <div class="checkbox checkbox-inline">
                                                            <input type="checkbox" id="balcony" name="balcony" value="1" <?php echo !empty($room['balcony']) ? 'checked' : ''; ?>>
                                                            <label for="balcony">Balcony</label>
                                                        </div>
                                                    </div>
                                                    <div class="admin-form-col-3">
                                                        <div class="checkbox checkbox-inline">
                                                            <input type="checkbox" id="study_table" name="study_table" value="1" <?php echo !empty($room['study_table']) ? 'checked' : ''; ?>>
                                                            <label for="study_table">Study Table</label>
                                                        </div>
                                                    </div>
                                                    <div class="admin-form-col-8">
                                                        <div class="form-group">
                                                            <label for="description">Description</label>
                                                            <textarea class="form-control" rows="4" name="description" id="description"><?php echo htmlspecialchars($room['description']); ?></textarea>
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
                                            <input class="btn btn-primary" type="submit" name="submit" value="Update Room Details">
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
