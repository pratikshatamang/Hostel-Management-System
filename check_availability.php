<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/booking.php';

if (!empty($_POST['emailid'])) {
    $email = trim($_POST['emailid']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<span style='color:red'>Enter a valid email address.</span>";
        exit();
    }

    $stmt = $mysqli->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    echo $count > 0
        ? "<span style='color:red'>Email already exists.</span>"
        : "<span style='color:green'>Email is available.</span>";
    exit();
}

if (!empty($_POST['oldpassword'])) {
    if (empty($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role'] !== 'user') {
        echo "<span style='color:red'>Session expired. Please log in again.</span>";
        exit();
    }

    $userId = (int) $_SESSION['user_id'];
    $oldPassword = trim($_POST['oldpassword']);
    $stmt = $mysqli->prepare('SELECT password FROM users WHERE id = ? AND role = \'user\' LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->bind_result($storedPassword);
    $stmt->fetch();
    $stmt->close();

    echo hms_password_verify($oldPassword, $storedPassword)
        ? "<span style='color:green'>Password matched.</span>"
        : "<span style='color:red'>Password not matched.</span>";
    exit();
}

if (!empty($_POST['roomno'])) {
    $roomno = (int) $_POST['roomno'];
    $availability = hms_get_room_availability($mysqli, $roomno);

    if (!$availability['exists']) {
        echo "<span style='color:red'>Selected room was not found.</span>";
        exit();
    }

    if ($availability['is_full']) {
        echo "<span style='color:red'>Room is full. Occupancy: " . $availability['occupied'] . "/" . $availability['capacity'] . ".</span>";
        exit();
    }

    echo "<span style='color:green'>Room available. Occupancy: " . $availability['occupied'] . "/" . $availability['capacity'] . ". Remaining seats: " . $availability['remaining'] . ".</span>";
}
?>
