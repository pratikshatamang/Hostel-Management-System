<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();

$adminEmail = isset($_SESSION['login']) ? $_SESSION['login'] : '';

$updateStmt = $mysqli->prepare("UPDATE notifications SET is_read = 1 WHERE receiver_email = ? AND is_read = 0");
if ($updateStmt) {
    $updateStmt->bind_param('s', $adminEmail);
    $updateStmt->execute();
    $updateStmt->close();
}
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="theme-color" content="#3e454c">
    <title>Admin Notifications</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include('includes/header.php'); ?>
<div class="ts-main-content">
    <?php include('includes/sidebar.php'); ?>
    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="admin-page-header admin-page-header-management">
                <div>
                    <span class="admin-page-kicker">Admin Alerts</span>
                    <h2 class="page-title">Notifications</h2>
                    <p class="admin-page-subtitle">Review automatic alerts including room expiry reminders for students.</p>
                </div>
            </div>

            <div class="panel panel-default admin-table-card">
                <div class="panel-heading admin-table-card-head">
                    <div>
                        <h3 class="admin-section-title">Recent Notifications</h3>
                        <p class="admin-section-subtitle">New alerts are marked as read when you open this page.</p>
                    </div>
                </div>
                <div class="panel-body">
                    <?php
                    $stmt = $mysqli->prepare("SELECT title, message, created_at FROM notifications WHERE receiver_email = ? ORDER BY id DESC");
                    $stmt->bind_param('s', $adminEmail);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $hasRows = false;

                    while ($row = $res->fetch_assoc()) {
                        $hasRows = true;
                        ?>
                        <div class="alert alert-info" style="margin-bottom:15px;">
                            <strong><?php echo htmlspecialchars($row['title']); ?></strong><br>
                            <small><?php echo htmlspecialchars($row['created_at']); ?></small>
                            <p style="margin:10px 0 0;"><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                        </div>
                        <?php
                    }
                    $stmt->close();

                    if (!$hasRows) {
                        echo '<div class="alert alert-warning">No admin notifications yet.</div>';
                    }
                    ?>
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
