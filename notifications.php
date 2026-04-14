<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();

$email = $_SESSION['login'];

// Mark all unread notifications as read
$updateStmt = $mysqli->prepare("UPDATE notifications SET is_read = 1 WHERE receiver_email = ? AND is_read = 0");
$updateStmt->bind_param('s', $email);
$updateStmt->execute();
$updateStmt->close();
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <title>My Notifications</title>
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
        .notice-card { 
            background: rgba(255, 255, 255, 0.7); 
            backdrop-filter: blur(16px);
            padding: 25px; 
            border-radius: 16px; 
            margin-bottom: 24px; 
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-left: 6px solid var(--ui-primary);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .notice-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(31, 38, 135, 0.12);
        }
        .notice-card.read { border-left-color: #cbd5e0; }
        .notice-date { color: #8898aa; font-size: 13px; font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
        .notice-title { font-weight: 700; font-size: 20px; margin: 0 0 12px 0; color: #163954; }
        .notice-message { color: #4f6478; line-height: 1.6; margin: 0; font-size: 15px; }
        .empty-state { 
            text-align: center; 
            padding: 60px 40px; 
            color: #8898aa; 
            background: rgba(255, 255, 255, 0.7); 
            backdrop-filter: blur(16px);
            border-radius: 20px; 
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
        }
    </style>
</head>
<body>
<?php include('includes/header.php'); ?>
<div class="ts-main-content">
    <?php include('includes/sidebar.php'); ?>
    <div class="content-wrapper">
        <div class="container-fluid">
            <section class="ui-hero" style="margin-bottom:20px;">
                <span class="ui-badge"><i class="fa fa-bell"></i> Official Notices</span>
                <h2>My Notifications</h2>
                <p>Read the latest communications and alerts sent from the hostel administration.</p>
            </section>

            <div class="row">
                <div class="col-md-10">
                    <?php
                    $stmt = $mysqli->prepare("SELECT title, message, created_at, is_read FROM notifications WHERE receiver_email = ? ORDER BY id DESC");
                    $stmt->bind_param('s', $email);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $hasNotices = false;

                    while ($row = $res->fetch_object()) {
                        $hasNotices = true;
                        // For styling logic visually if we want. But they are technically ALL read now.
                        $dateStr = date('F j, Y, g:i a', strtotime($row->created_at));
                        ?>
                        <div class="notice-card">
                            <span class="notice-date"><i class="fa fa-calendar-o"></i> <?php echo $dateStr; ?></span>
                            <h4 class="notice-title"><?php echo htmlspecialchars($row->title); ?></h4>
                            <p class="notice-message"><?php echo nl2br(htmlspecialchars($row->message)); ?></p>
                        </div>
                        <?php
                    }

                    if (!$hasNotices) {
                        echo '<div class="empty-state">
                                <i class="fa fa-inbox" style="font-size:40px; margin-bottom:15px; color:#ddd;"></i>
                                <h4>No Notifications</h4>
                                <p>You have not received any notices from the administrator yet.</p>
                              </div>';
                    }

                    $stmt->close();
                    ?>
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
