<?php $isLoggedIn = !empty($_SESSION['user_id']); ?>
<div class="brand clearfix">
    <a href="<?php echo $isLoggedIn ? 'dashboard.php' : 'login.php'; ?>" class="logo" style="font-size:16px;">Hostel Management System</a>
    <span class="menu-btn"><i class="fa fa-bars"></i></span>
    <?php if ($isLoggedIn): ?>
        <ul class="ts-profile-nav">
            <li class="ts-account">
                <a href="#"><img src="img/ts-avatar.jpg" class="ts-avatar hidden-side" alt=""> <?php echo htmlspecialchars(isset($_SESSION['name']) ? $_SESSION['name'] : 'Account'); ?> <i class="fa fa-angle-down hidden-side"></i></a>
                <ul>
                    <li><a href="my-profile.php">My Account</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </li>
        </ul>
    <?php endif; ?>
</div>
