<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/checklogin.php';
require_once '../includes/auth.php';
check_login();

$userId = (int) $_SESSION['user_id'];
$legacyId = (int) $_SESSION['id'];
$errors = array();
$successMessage = '';
$passwordMessage = '';

if (isset($_POST['update'])) {
    $email = trim(isset($_POST['emailid']) ? $_POST['emailid'] : '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    } else {
        $checkStmt = $mysqli->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
        $checkStmt->bind_param('si', $email, $userId);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows > 0) {
            $errors[] = 'That email address is already in use.';
        }
        $checkStmt->close();
    }

    if (!$errors) {
        $updateUser = $mysqli->prepare("UPDATE users SET email = ?, updated_at = NOW() WHERE id = ? AND role = 'admin'");
        $updateUser->bind_param('si', $email, $userId);
        $updateUser->execute();
        $updateUser->close();

        $legacyDate = date('Y-m-d');
        $updateAdmin = $mysqli->prepare('UPDATE admin SET email = ?, updation_date = ? WHERE id = ?');
        $updateAdmin->bind_param('ssi', $email, $legacyDate, $legacyId);
        $updateAdmin->execute();
        $updateAdmin->close();

        $_SESSION['email'] = $email;
        $_SESSION['login'] = $email;
        $successMessage = 'Admin email updated successfully.';
    }
}

if (isset($_POST['changepwd'])) {
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
        $stmt = $mysqli->prepare("SELECT password FROM users WHERE id = ? AND role = 'admin' LIMIT 1");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->bind_result($storedPassword);
        $stmt->fetch();
        $stmt->close();

        if (!hms_password_verify($oldPassword, $storedPassword)) {
            $errors[] = 'Old password is incorrect.';
        } else {
            $hash = hms_password_hash($newPassword);
            $legacyDate = date('Y-m-d');

            $updateUsers = $mysqli->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ? AND role = 'admin'");
            $updateUsers->bind_param('si', $hash, $userId);
            $updateUsers->execute();
            $updateUsers->close();

            $updateAdmin = $mysqli->prepare('UPDATE admin SET password = ?, updation_date = ? WHERE id = ?');
            $updateAdmin->bind_param('ssi', $hash, $legacyDate, $legacyId);
            $updateAdmin->execute();
            $updateAdmin->close();

            $passwordMessage = 'Password changed successfully.';
        }
    }
}

$stmt = $mysqli->prepare("SELECT u.username, u.email, u.created_at, a.reg_date, a.updation_date
                          FROM users u
                          LEFT JOIN admin a ON a.id = u.legacy_id
                          WHERE u.id = ? AND u.role = 'admin'
                          LIMIT 1");
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();
$stmt->close();
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="theme-color" content="#3e454c">
    <title>Admin Profile</title>
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
                    <div class="col-md-12">
                        <div class="admin-page-header admin-page-header-management">
                            <div>
                                <span class="admin-page-kicker">Account Settings</span>
                                <h2 class="page-title">Admin Profile</h2>
                                <p class="admin-page-subtitle">Manage your personal information and account settings without changing the current profile update or password change logic.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="admin-inline-alerts">
                    <?php if ($successMessage !== ''): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($passwordMessage !== ''): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($passwordMessage); ?></div>
                    <?php endif; ?>
                    <?php if ($errors): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
                    <?php endif; ?>
                </div>

                <div class="admin-profile-grid">
                    <div>
                        <div class="panel panel-default admin-form-card">
                            <div class="panel-heading">
                                <h3 class="admin-form-title">Admin Account Details</h3>
                                <p class="admin-form-subtitle">Update your email while preserving the current admin account record and session flow.</p>
                            </div>
                            <div class="panel-body">
                                <form method="post" class="admin-form">
                                    <div class="admin-form-section">
                                        <div class="admin-form-grid">
                                            <div class="admin-form-col-12">
                                                <div class="form-group">
                                                    <label>Username</label>
                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($profile['username']); ?>" disabled>
                                                </div>
                                            </div>
                                            <div class="admin-form-col-12">
                                                <div class="form-group">
                                                    <label>Email</label>
                                                    <input type="email" name="emailid" id="emailid" class="form-control" value="<?php echo htmlspecialchars($profile['email']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="admin-form-col-12">
                                                <div class="form-group">
                                                    <label>Created</label>
                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($profile['reg_date'] !== null ? $profile['reg_date'] : $profile['created_at']); ?>" disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="admin-form-actions">
                                        <button type="submit" name="update" class="btn btn-primary">Update Profile</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="panel panel-default admin-form-card">
                            <div class="panel-heading">
                                <h3 class="admin-form-title">Change Password</h3>
                                <p class="admin-form-subtitle">Use the same password validation and availability check flow in a cleaner form layout.</p>
                            </div>
                            <div class="panel-body">
                                <form method="post" class="admin-form">
                                    <div class="admin-form-section">
                                        <div class="admin-form-grid">
                                            <div class="admin-form-col-12">
                                                <div class="form-group">
                                                    <label>Old Password</label>
                                                    <input type="password" name="oldpassword" id="oldpassword" class="form-control" onblur="checkpass()" required>
                                                    <span id="password-availability-status" class="help-block"></span>
                                                </div>
                                            </div>
                                            <div class="admin-form-col-12">
                                                <div class="form-group">
                                                    <label>New Password</label>
                                                    <input type="password" name="newpassword" class="form-control" required>
                                                </div>
                                            </div>
                                            <div class="admin-form-col-12">
                                                <div class="form-group">
                                                    <label>Confirm Password</label>
                                                    <input type="password" name="cpassword" class="form-control" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="admin-form-actions">
                                        <button type="submit" name="changepwd" class="btn btn-primary">Change Password</button>
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
