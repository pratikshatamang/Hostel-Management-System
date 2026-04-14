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
<header class="brand user-topbar">
    <div class="user-topbar-left">
        <a href="<?php echo $isLoggedIn ? 'dashboard.php' : 'login.php'; ?>" class="logo user-brand-mark" aria-label="Hostel Management System">
            <span class="user-brand-badge"><i class="fa fa-building-o"></i></span>
            <span class="user-brand-copy">
                <strong>Hostel Management</strong>
                <small>User Portal</small>
            </span>
        </a>
    </div>

    <?php if ($isLoggedIn): ?>
        <div class="user-topbar-right">
            <span class="user-topbar-chip hidden-xs">
                <i class="fa fa-graduation-cap"></i>
                <span>Student Workspace</span>
            </span>
            <a href="notifications.php" class="user-topbar-notification" aria-label="Notifications">
                <i class="fa fa-bell"></i>
                <?php if ($unreadCount > 0): ?>
                    <span class="user-topbar-notification-badge"><?php echo $unreadCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="my-profile.php" class="user-topbar-account" aria-label="My profile" title="<?php echo htmlspecialchars($displayName); ?>">
                <img src="img/ts-avatar.jpg" class="ts-avatar" alt="User avatar">
                <span class="user-account-copy hidden-xs">
                    <strong><?php echo htmlspecialchars($displayName); ?></strong>
                    <small><i class="fa fa-user-circle-o"></i> Account</small>
                </span>
                <i class="fa fa-user-circle-o user-account-icon"></i>
            </a>
        </div>
    <?php endif; ?>
</header>
