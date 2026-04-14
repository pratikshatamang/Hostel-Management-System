<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (hms_is_logged_in()) {
    hms_redirect(hms_user_dashboard_path(hms_current_role()));
}

$errors = array();
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $contact = trim(isset($_POST['contact']) ? $_POST['contact'] : '');
    $newPassword = trim(isset($_POST['newpassword']) ? $_POST['newpassword'] : '');
    $confirmPassword = trim(isset($_POST['cpassword']) ? $_POST['cpassword'] : '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if ($contact === '' || !preg_match('/^[0-9]{7,15}$/', $contact)) {
        $errors[] = 'Enter a valid contact number.';
    }

    if ($newPassword === '' || strlen($newPassword) < 6) {
        $errors[] = 'New password must be at least 6 characters long.';
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = 'New password and confirm password do not match.';
    }

    if (!$errors) {
        $stmt = $mysqli->prepare("SELECT u.id, u.legacy_id
                                  FROM users u
                                  INNER JOIN userregistration ur ON ur.id = u.legacy_id
                                  WHERE u.role = 'user' AND u.email = ? AND ur.contactNo = ?
                                  LIMIT 1");
        $stmt->bind_param('ss', $email, $contact);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            $hash = hms_password_hash($newPassword);
            $updateUsers = $mysqli->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?');
            $updateUsers->bind_param('si', $hash, $user['id']);
            $updateUsers->execute();
            $updateUsers->close();

            $passUpdateDate = date('d-m-Y h:i:s', time());
            $updateStudent = $mysqli->prepare('UPDATE userregistration SET password = ?, passUdateDate = ? WHERE id = ?');
            $updateStudent->bind_param('ssi', $hash, $passUpdateDate, $user['legacy_id']);
            $updateStudent->execute();
            $updateStudent->close();

            $successMessage = 'Password reset successfully. You can now log in.';
        } else {
            $errors[] = 'No student account matched the provided email and contact number.';
        }
    }
}
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="theme-color" content="#274f8c">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/user-ui.css">
</head>
<body>
<div class="reset-shell">
    <div class="reset-card">
        <h1>Reset Password</h1>
        <p>Confirm your student account with your email and contact number, then set a new password.</p>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>
        <?php if ($errors): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="contact">Contact Number</label>
                <input type="text" name="contact" id="contact" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="newpassword">New Password</label>
                <input type="password" name="newpassword" id="newpassword" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="cpassword">Confirm Password</label>
                <input type="password" name="cpassword" id="cpassword" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-reset">Reset Password</button>
        </form>

        <p style="margin-top:16px; margin-bottom:0;"><a href="login.php">Back to login</a></p>
    </div>
</div>
</body>
</html>
