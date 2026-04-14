<?php
$isLoggedIn = !empty($_SESSION['user_id']);
$displayName = isset($_SESSION['name']) && $_SESSION['name'] !== '' ? $_SESSION['name'] : 'Student Account';

$unreadCount = 0;
if ($isLoggedIn && isset($mysqli) && isset($_SESSION['login'])) {
    $email = $_SESSION['login'];
    if (function_exists('hms_run_automatic_expiry_notifications')) {
        hms_run_automatic_expiry_notifications($mysqli, $email);
    }
    $notifStmt = $mysqli->prepare('SELECT count(*) FROM notifications WHERE receiver_email = ? AND is_read = 0');
    $notifStmt->bind_param('s', $email);
    $notifStmt->execute();
    $notifStmt->bind_result($unreadCount);
    $notifStmt->fetch();
    $notifStmt->close();
}
?>
<div class="brand clearfix">
    <div class="logo-wrap">
        <a href="<?php echo $isLoggedIn ? 'dashboard.php' : 'login.php'; ?>" class="logo-badge" aria-label="Hostel Management System">
            <i class="fa fa-building-o"></i>
        </a>
        <a href="<?php echo $isLoggedIn ? 'dashboard.php' : 'login.php'; ?>" class="logo">
            Hostel Management System
            <small>User Portal</small>
        </a>
    </div>

    <span class="menu-btn" role="button" aria-label="Toggle navigation">
        <i class="fa fa-bars"></i>
    </span>

    <?php if ($isLoggedIn): ?>
        <ul class="ts-profile-nav">
            <li class="ts-notification-item">
                <a href="notifications.php" class="ts-notification-link" aria-label="Notifications">
                    <i class="fa fa-bell"></i>
                    <?php if ($unreadCount > 0): ?>
                        <span class="ts-notification-badge"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="ts-account">
                <a href="#">
                    <span class="account-meta">
                        <img src="img/ts-avatar.jpg" class="ts-avatar" alt="User avatar">
                        <span class="account-text">
                            <strong><?php echo htmlspecialchars($displayName); ?></strong>
                            <span>Student Menu</span>
                        </span>
                    </span>
                    <i class="fa fa-angle-down hidden-side"></i>
                </a>
                <ul>
                    <li><a href="my-profile.php">My Profile</a></li>
                    <li><a href="access-log.php">Access Log</a></li>
                    <li><a href="change-password.php">Change Password</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    <?php endif; ?>
</div>
