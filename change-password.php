<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/checklogin.php';
check_login();

$userId = (int) $_SESSION['user_id'];
$legacyId = (int) $_SESSION['id'];
$errors = array();
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = trim(isset($_POST['oldpassword']) ? $_POST['oldpassword'] : '');
    $newPassword = trim(isset($_POST['newpassword']) ? $_POST['newpassword'] : '');
    $confirmPassword = trim(isset($_POST['cpassword']) ? $_POST['cpassword'] : '');

    if ($oldPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $errors[] = 'All password fields are required.';
    }

    if ($newPassword !== $confirmPassword) {
        $errors[] = 'New password and confirm password do not match.';
    }

    if (strlen($newPassword) < 6) {
        $errors[] = 'New password must be at least 6 characters long.';
    }

    if (!$errors) {
        $stmt = $mysqli->prepare("SELECT password, updated_at FROM users WHERE id = ? AND role = 'user' LIMIT 1");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user || !hms_password_verify($oldPassword, $user['password'])) {
            $errors[] = 'Old password is incorrect.';
        } else {
            $newHash = hms_password_hash($newPassword);
            $passUpdateDate = date('d-m-Y h:i:s', time());

            $updateAuth = $mysqli->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?');
            $updateAuth->bind_param('si', $newHash, $userId);
            $updateAuth->execute();
            $updateAuth->close();

            $updateStudent = $mysqli->prepare('UPDATE userregistration SET password = ?, passUdateDate = ? WHERE id = ?');
            $updateStudent->bind_param('ssi', $newHash, $passUpdateDate, $legacyId);
            $updateStudent->execute();
            $updateStudent->close();

            $successMessage = 'Password changed successfully.';
        }
    }
}

$detailsStmt = $mysqli->prepare("SELECT passUdateDate FROM userregistration WHERE id = ? LIMIT 1");
$detailsStmt->bind_param('i', $legacyId);
$detailsStmt->execute();
$detailsStmt->bind_result($lastPasswordUpdate);
$detailsStmt->fetch();
$detailsStmt->close();
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="theme-color" content="#3e454c">
    <title>Change Password</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="ts-main-content">
        <?php include 'includes/sidebar.php'; ?>
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-10">
                        <h2 class="page-title">Change Password</h2>
                        <div class="panel panel-default">
                            <div class="panel-heading">Last password update: <?php echo htmlspecialchars($lastPasswordUpdate !== '' ? $lastPasswordUpdate : 'Not available'); ?></div>
                            <div class="panel-body">
                                <?php if ($successMessage !== ''): ?>
                                    <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
                                <?php endif; ?>
                                <?php if ($errors): ?>
                                    <div class="alert alert-danger"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
                                <?php endif; ?>
                                <form method="post" class="form-horizontal">
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">Old Password</label>
                                        <div class="col-sm-8">
                                            <input type="password" name="oldpassword" id="oldpassword" class="form-control" onblur="checkpass()" required>
                                            <span id="password-availability-status" class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">New Password</label>
                                        <div class="col-sm-8">
                                            <input type="password" name="newpassword" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">Confirm Password</label>
                                        <div class="col-sm-8">
                                            <input type="password" name="cpassword" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-sm-offset-4">
                                        <button type="submit" class="btn btn-primary">Change Password</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
        function checkpass() {
            $.ajax({
                url: "check_availability.php",
                data: { oldpassword: $("#oldpassword").val() },
                type: "POST",
                success: function(data) {
                    $("#password-availability-status").html(data);
                }
            });
        }
    </script>
</body>
</html>
