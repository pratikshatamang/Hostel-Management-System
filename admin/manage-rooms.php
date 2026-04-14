<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
include('../includes/booking.php');
check_login();

if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $adn = "DELETE FROM rooms WHERE id=?";
    $stmt = $mysqli->prepare($adn);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('Data Deleted');</script>";
}

$rooms = hms_get_rooms_with_occupancy($mysqli);
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
    <title>Manage Rooms</title>
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
                                <span class="admin-page-kicker">Room Management</span>
                                <h2 class="page-title">Manage Rooms</h2>
                                <p class="admin-page-subtitle">Review room inventory, occupancy, availability, and facility details from a cleaner management view.</p>
                            </div>
                            <div class="admin-page-actions">
                                <a href="create-room.php" class="btn admin-btn admin-btn-primary">
                                    <i class="fa fa-plus"></i>
                                    <span>Add Room</span>
                                </a>
                            </div>
                        </div>

                        <div class="panel panel-default admin-table-card">
                            <div class="panel-heading admin-table-card-head">
                                <div>
                                    <h3 class="admin-section-title">Room Occupancy and Features</h3>
                                    <p class="admin-section-subtitle">All room records remain fully editable from the action buttons below.</p>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="table-responsive admin-table-wrap">
                                <table id="zctb" class="display table admin-data-table" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Sno.</th>
                                            <th>Room No.</th>
                                            <th>Capacity</th>
                                            <th>Occupied</th>
                                            <th>Available Beds</th>
                                            <th>Occupancy Status</th>
                                            <th>Room Status</th>
                                            <th>Fees (PM)</th>
                                            <th>Features</th>
                                            <th>Posting Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th>Sno.</th>
                                            <th>Room No.</th>
                                            <th>Capacity</th>
                                            <th>Occupied</th>
                                            <th>Available Beds</th>
                                            <th>Occupancy Status</th>
                                            <th>Room Status</th>
                                            <th>Fees (PM)</th>
                                            <th>Features</th>
                                            <th>Posting Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>
                                    <?php
                                    $cnt = 1;
                                    foreach ($rooms as $row) {
                                        $occupancyClass = 'admin-badge admin-badge-success';
                                        if ($row['status_key'] === 'full') {
                                            $occupancyClass = 'admin-badge admin-badge-danger';
                                        } elseif ($row['status_key'] === 'partial') {
                                            $occupancyClass = 'admin-badge admin-badge-warning';
                                        } elseif ($row['status_key'] === 'inactive') {
                                            $occupancyClass = 'admin-badge admin-badge-muted';
                                        }

                                        $roomStatusClass = $row['room_status'] === 'available' ? 'admin-badge admin-badge-success' : 'admin-badge admin-badge-muted';
                                        if ($row['room_status'] === 'maintenance') {
                                            $roomStatusClass = 'admin-badge admin-badge-warning';
                                        }

                                        $featureList = hms_get_room_feature_list($row);
                                    ?>
                                        <tr>
                                            <td><?php echo $cnt; ?></td>
                                            <td><?php echo htmlspecialchars($row['room_no']); ?></td>
                                            <td><?php echo htmlspecialchars($row['seater']); ?></td>
                                            <td><?php echo htmlspecialchars($row['occupied']); ?></td>
                                            <td><?php echo htmlspecialchars($row['remaining']); ?></td>
                                            <td><span class="<?php echo $occupancyClass; ?>"><?php echo htmlspecialchars($row['status_label']); ?></span></td>
                                            <td><span class="<?php echo $roomStatusClass; ?>"><?php echo htmlspecialchars(ucfirst($row['room_status'])); ?></span></td>
                                            <td><?php echo htmlspecialchars($row['fees']); ?></td>
                                            <td><?php echo htmlspecialchars(!empty($featureList) ? implode(', ', $featureList) : 'Basic Room'); ?></td>
                                            <td><?php echo htmlspecialchars($row['posting_date']); ?></td>
                                            <td class="admin-actions-cell">
                                                <a href="edit-room.php?id=<?php echo $row['id']; ?>" class="admin-action-btn admin-action-btn-edit" title="Edit Room">
                                                    <i class="fa fa-edit"></i>
                                                    <span>Edit</span>
                                                </a>
                                                <a href="manage-rooms.php?del=<?php echo $row['id']; ?>" class="admin-action-btn admin-action-btn-delete" onclick="return confirm('Do you want to delete');" title="Delete Room">
                                                    <i class="fa fa-close"></i>
                                                    <span>Delete</span>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php
                                        $cnt = $cnt + 1;
                                    }
                                    ?>
                                    </tbody>
                                </table>
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
