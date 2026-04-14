<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<nav class="ts-sidebar" id="adminSidebar">
    <div class="admin-sidebar-head">
        <span class="admin-sidebar-eyebrow">Navigation</span>
        <h3>Admin Workspace</h3>
        <p>Manage hostel operations from one streamlined panel.</p>
    </div>

    <ul class="ts-sidebar-menu">
        <li class="ts-label">Main</li>
        <li class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
            <a href="dashboard.php"><i class="fa fa-dashboard"></i><span>Dashboard</span></a>
        </li>

        <li class="ts-label">Management</li>
        <li class="<?php echo in_array($currentPage, array('add-courses.php', 'manage-courses.php'), true) ? 'open active' : ''; ?>">
            <a href="#"><i class="fa fa-files-o"></i><span>Courses</span></a>
            <ul>
                <li class="<?php echo $currentPage === 'add-courses.php' ? 'active' : ''; ?>"><a href="add-courses.php">Add Courses</a></li>
                <li class="<?php echo $currentPage === 'manage-courses.php' ? 'active' : ''; ?>"><a href="manage-courses.php">Manage Courses</a></li>
            </ul>
        </li>

        <li class="<?php echo in_array($currentPage, array('create-room.php', 'manage-rooms.php', 'edit-room.php'), true) ? 'open active' : ''; ?>">
            <a href="#"><i class="fa fa-desktop"></i><span>Rooms</span></a>
            <ul>
                <li class="<?php echo $currentPage === 'create-room.php' ? 'active' : ''; ?>"><a href="create-room.php">Add a Room</a></li>
                <li class="<?php echo in_array($currentPage, array('manage-rooms.php', 'edit-room.php'), true) ? 'active' : ''; ?>"><a href="manage-rooms.php">Manage Rooms</a></li>
            </ul>
        </li>

        <li class="<?php echo $currentPage === 'registration.php' ? 'active' : ''; ?>">
            <a href="registration.php"><i class="fa fa-user"></i><span>Student Registration</span></a>
        </li>
        <li class="<?php echo in_array($currentPage, array('manage-students.php', 'full-profile.php'), true) ? 'active' : ''; ?>">
            <a href="manage-students.php"><i class="fa fa-users"></i><span>Manage Students</span></a>
        </li>
        <li class="<?php echo $currentPage === 'access-log.php' ? 'active' : ''; ?>">
            <a href="access-log.php"><i class="fa fa-file"></i><span>User Access Logs</span></a>
        </li>
        <li class="<?php echo $currentPage === 'send-notification.php' ? 'active' : ''; ?>">
            <a href="send-notification.php"><i class="fa fa-bell"></i><span>Send Notification</span></a>
        </li>
        <li class="<?php echo $currentPage === 'notifications.php' ? 'active' : ''; ?>">
            <a href="notifications.php"><i class="fa fa-inbox"></i><span>Notifications</span></a>
        </li>

        <li class="ts-label">Account</li>
        <li class="<?php echo $currentPage === 'admin-profile.php' ? 'active' : ''; ?>">
            <a href="admin-profile.php"><i class="fa fa-lock"></i><span>Admin Account</span></a>
        </li>
    </ul>
</nav>
<div class="admin-sidebar-backdrop"></div>
