<?php
$currentPage = basename(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '');
$baseUrl = 'http://localhost/hostel/';
function hms_sidebar_active($pageName, $currentPage)
{
    return $pageName === $currentPage ? ' class="is-active"' : '';
}

function hms_sidebar_group_active($pages, $currentPage)
{
    return in_array($currentPage, $pages, true) ? ' class="open is-active"' : '';
}
?>
<nav class="ts-sidebar">
    <ul class="ts-sidebar-menu">
        <li class="ts-label">Main Navigation</li>
        <?php if (!empty($_SESSION['user_id'])) { ?>
            <li<?php echo hms_sidebar_active('dashboard.php', $currentPage); ?>>
                <a href="<?php echo $baseUrl; ?>dashboard.php"><i class="fa fa-desktop"></i><span>Dashboard</span></a>
                <span class="menu-note">Overview and quick actions</span>
            </li>
            <li<?php echo hms_sidebar_group_active(array('my-profile.php', 'access-log.php', 'change-password.php'), $currentPage); ?>>
                <a href="#" aria-expanded="<?php echo in_array($currentPage, array('my-profile.php', 'access-log.php', 'change-password.php'), true) ? 'true' : 'false'; ?>"><i class="fa fa-user"></i><span>My Profile</span></a>
                <span class="menu-note">Manage your account details and security</span>
                <ul>
                    <li<?php echo hms_sidebar_active('my-profile.php', $currentPage); ?>>
                        <a href="<?php echo $baseUrl; ?>my-profile.php">My Profile</a>
                    </li>
                    <li<?php echo hms_sidebar_active('access-log.php', $currentPage); ?>>
                        <a href="<?php echo $baseUrl; ?>access-log.php">Access Log</a>
                    </li>
                    <li<?php echo hms_sidebar_active('change-password.php', $currentPage); ?>>
                        <a href="<?php echo $baseUrl; ?>change-password.php">Change Password</a>
                    </li>
                </ul>
            </li>
            <li<?php echo hms_sidebar_active('book-hostel.php', $currentPage); ?>>
                <a href="http://localhost/hostel/book-hostel.php"><i class="fa fa-building-o"></i><span>Book Hostel</span></a>
                <span class="menu-note">Complete your hostel registration</span>
            </li>
            <li<?php echo hms_sidebar_active('room-details.php', $currentPage); ?>>
                <a href="<?php echo $baseUrl; ?>room-details.php"><i class="fa fa-bed"></i><span>Room Details</span></a>
                <span class="menu-note">View your booking summary</span>
            </li>
            <li<?php echo hms_sidebar_active('payment-history.php', $currentPage); ?>>
                <a href="<?php echo $baseUrl; ?>payment-history.php"><i class="fa fa-credit-card"></i><span>Payment History</span></a>
                <span class="menu-note">See payments by room booking</span>
            </li>
            <li>
                <a href="<?php echo $baseUrl; ?>logout.php"><i class="fa fa-sign-out"></i><span>Logout</span></a>
                <span class="menu-note">Sign out safely</span>
            </li>
        <?php } else { ?>
            <li<?php echo hms_sidebar_active('registration.php', $currentPage); ?>>
                <a href="<?php echo $baseUrl; ?>registration.php"><i class="fa fa-user-plus"></i><span>User Registration</span></a>
            </li>
            <li<?php echo hms_sidebar_active('login.php', $currentPage); ?>>
                <a href="<?php echo $baseUrl; ?>login.php"><i class="fa fa-sign-in"></i><span>Login</span></a>
            </li>
        <?php } ?>
    </ul>
</nav>
