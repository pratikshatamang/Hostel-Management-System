<?php
require_once __DIR__ . '/auth.php';

function check_login()
{
    if (!hms_is_logged_in()) {
        hms_set_flash('warning', 'Please log in to continue.');
        hms_redirect('login.php');
    }

    if (hms_current_role() !== 'user') {
        hms_set_flash('danger', 'You are not allowed to access the student area.');
        hms_redirect('admin/dashboard.php');
    }
}
?>
