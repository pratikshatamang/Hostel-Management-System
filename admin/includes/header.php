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
        <a href="dashboard.php" class="logo admin-brand-mark">
            <span class="admin-brand-badge">HMS</span>
            <span class="admin-brand-copy">
                <strong>Hostel Management</strong>
                <small>Admin Control Panel</small>
            </span>
        </a>
    </div>

    <div class="admin-topbar-right">
        <div class="admin-topbar-pill hidden-xs">
            <i class="fa fa-circle admin-topbar-pill-dot"></i>
            <span>Admin Workspace</span>
        </div>
        <div class="admin-topbar-meta hidden-xs">
            <span class="admin-topbar-label">Administration</span>
            <strong class="admin-topbar-title">Operations Dashboard</strong>
        </div>

        <ul class="ts-profile-nav">
            <li class="ts-notification-item">
                <a href="notifications.php" class="ts-notification-link admin-notification-link" aria-label="Admin notifications">
                    <i class="fa fa-bell"></i>
                    <?php if ($adminUnreadCount > 0): ?>
                        <span class="ts-notification-badge"><?php echo $adminUnreadCount; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="ts-account">
                <a href="#">
                    <img src="img/ts-avatar.jpg" class="ts-avatar" alt="Admin Avatar">
                    <span class="admin-account-copy">
                        <strong><?php echo $adminDisplayName; ?></strong>
                        <small>Administrator</small>
                    </span>
                    <i class="fa fa-angle-down"></i>
                </a>
                <ul>
                    <li><a href="admin-profile.php">My Account</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    </div>
</header>
