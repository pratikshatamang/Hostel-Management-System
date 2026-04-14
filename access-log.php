<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="theme-color" content="#183153">
    <title>Access Log</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <link rel="stylesheet" href="css/bootstrap-social.css">
    <link rel="stylesheet" href="css/bootstrap-select.css">
    <link rel="stylesheet" href="css/fileinput.min.css">
    <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/user-ui.css">
</head>
<body>
<?php include('includes/header.php'); ?>

<div class="ts-main-content">
    <?php include('includes/sidebar.php'); ?>
    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="ui-page-head">
                <h2 class="page-title">Access Log</h2>
                <p>Review your sign-in history in a more readable table layout with better spacing and mobile overflow handling.</p>
            </div>

            <div class="ui-surface table-card">
                <div class="ui-surface-head">
                    <h3>Recent Login Activity</h3>
                    <p>This keeps the existing log data and DataTables behavior while improving the presentation.</p>
                </div>
                <div class="ui-surface-body">
                    <div class="table-responsive">
                        <table id="zctb" class="display table table-striped table-bordered table-hover" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>Sno.</th>
                                    <th>User Id</th>
                                    <th>User Email</th>
                                    <th>IP</th>
                                    <th>City</th>
                                    <th>Country</th>
                                    <th>Login Time</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>Sno.</th>
                                    <th>User Id</th>
                                    <th>User Email</th>
                                    <th>IP</th>
                                    <th>City</th>
                                    <th>Country</th>
                                    <th>Login Time</th>
                                </tr>
                            </tfoot>
                            <tbody>
                            <?php
                            $aid = $_SESSION['id'];
                            $ret = "select * from userlog where userId=?";
                            $stmt = $mysqli->prepare($ret);
                            $stmt->bind_param('i', $aid);
                            $stmt->execute();
                            $res = $stmt->get_result();
                            $cnt = 1;
                            while ($row = $res->fetch_object()) {
                            ?>
                                <tr>
                                    <td><?php echo $cnt; ?></td>
                                    <td><?php echo htmlspecialchars($row->userId); ?></td>
                                    <td><?php echo htmlspecialchars($row->userEmail); ?></td>
                                    <td><?php echo htmlspecialchars($row->userIp); ?></td>
                                    <td><?php echo htmlspecialchars($row->city); ?></td>
                                    <td><?php echo htmlspecialchars($row->country); ?></td>
                                    <td><?php echo htmlspecialchars($row->loginTime); ?></td>
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
