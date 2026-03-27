<?php
session_start();
require_once 'includes/auth.php';

hms_logout_user();
hms_set_flash('success', 'You have been logged out successfully.');
hms_redirect('login.php');
?>
