<?php
$adminDisplayName = htmlspecialchars(isset($_SESSION['name']) ? $_SESSION['name'] : 'Admin');
$adminUnreadCount = 0;
if (isset($mysqli) && !empty($_SESSION['login'])) {
    $adminEmail = $_SESSION['login'];
    $notifStmt = $mysqli->prepare('SELECT count(*) FROM notifications WHERE receiver_email = ? AND is_read = 0');
    if ($notifStmt) {
        $notifStmt->bind_param('s', $adminEmail);
        $notifStmt->execute();
        $notifStmt->bind_result($adminUnreadCount);
        $notifStmt->fetch();
        $notifStmt->close();
    }
}
?>
<header class="brand admin-topbar">
    <div class="admin-topbar-left">
        <button type="button" class="menu-btn admin-menu-toggle" aria-label="Toggle navigation" aria-controls="adminSidebar" aria-expanded="false">
            <i class="fa fa-bars"></i>
        </button>
        <a href="dashboard.php" class="logo admin-brand-mark" aria-label="Admin dashboard">
            <span class="admin-brand-badge"><i class="fa fa-building"></i></span>
            <span class="admin-brand-copy">
                <strong>Hostel Management</strong>
                <small>Admin Control Panel</small>
            </span>
        </a>
    </div>

    <div class="admin-topbar-right">
        <span class="admin-topbar-chip hidden-xs">
            <i class="fa fa-shield"></i>
            <span>Workspace</span>
        </span>

        <a href="notifications.php" class="admin-topbar-notification" aria-label="Admin notifications">
            <i class="fa fa-bell"></i>
            <?php if ($adminUnreadCount > 0): ?>
                <span class="admin-topbar-notification-badge"><?php echo $adminUnreadCount; ?></span>
            <?php endif; ?>
        </a>

        <a href="admin-profile.php" class="admin-topbar-account" aria-label="My account" title="<?php echo $adminDisplayName; ?>">
            <img src="img/ts-avatar.jpg" class="ts-avatar" alt="Admin Avatar">
            <span class="admin-account-copy hidden-xs">
                <strong><?php echo $adminDisplayName; ?></strong>
                <small><i class="fa fa-user-secret"></i> Account</small>
            </span>
            <i class="fa fa-user-circle-o admin-account-icon"></i>
        </a>
    </div>
</header>
