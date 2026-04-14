<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $receiver_email = trim($_POST['receiver_email']);
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);

    if (!empty($receiver_email) && !empty($title) && !empty($message)) {
        if ($receiver_email === 'all') {
            $stmt = $mysqli->prepare('SELECT email FROM userregistration');
            $stmt->execute();
            $res = $stmt->get_result();
            $insertStmt = $mysqli->prepare('INSERT INTO notifications (receiver_email, title, message) VALUES (?, ?, ?)');
            while ($row = $res->fetch_object()) {
                $e = $row->email;
                $insertStmt->bind_param('sss', $e, $title, $message);
                $insertStmt->execute();
            }
            $insertStmt->close();
            $stmt->close();
            echo "<script>alert('Broadcast notification sent successfully!');</script>";
        } else {
            $stmt = $mysqli->prepare('INSERT INTO notifications (receiver_email, title, message) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $receiver_email, $title, $message);
            if ($stmt->execute()) {
                echo "<script>alert('Notification sent successfully!');</script>";
            } else {
                echo "<script>alert('Failed to send notification. Please try again.');</script>";
            }
            $stmt->close();
        }
    } else {
        echo "<script>alert('Please fill out all fields.');</script>";
    }
}
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>Send Notification</title>
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
                            <span class="admin-page-kicker">Comms Workspace</span>
                            <h2 class="page-title">Send Notification</h2>
                            <p class="admin-page-subtitle">Draft custom notices to push directly to registered student dashboards.</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8 col-md-offset-2">
                            <div class="panel panel-default admin-table-card">
                                <div class="panel-heading admin-table-card-head">
                                    <div>
                                        <h3 class="admin-section-title">Compose Message</h3>
                                    </div>
                                </div>
                                <div class="panel-body">
                                    <form method="post" action="" class="form-horizontal">
                                        
                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">Send To</label>
                                            <div class="col-sm-10">
                                                <select name="receiver_email" class="form-control" required>
                                                    <option value="">Select Recipient...</option>
                                                    <option value="all" style="font-weight:bold;">-- Broadcast to All Students --</option>
                                                    <?php
                                                    $ret = "SELECT email, firstName, lastName FROM userregistration";
                                                    $stmt = $mysqli->prepare($ret);
                                                    $stmt->execute();
                                                    $res = $stmt->get_result();
                                                    while ($row = $res->fetch_object()) {
                                                        echo "<option value='{$row->email}'>{$row->firstName} {$row->lastName} ({$row->email})</option>";
                                                    }
                                                    $stmt->close();
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">Title</label>
                                            <div class="col-sm-10">
                                                <input type="text" name="title" class="form-control" placeholder="E.g., Hostle Maintenance Notice" required>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="col-sm-2 control-label">Message</label>
                                            <div class="col-sm-10">
                                                <textarea name="message" class="form-control" rows="5" placeholder="Write the notice details here..." required></textarea>
                                            </div>
                                        </div>

                                        <div class="col-sm-8 col-sm-offset-2">
                                            <button class="btn btn-default" type="reset">Cancel</button>
                                            <button class="btn btn-primary" name="submit" type="submit">Send Notification</button>
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
