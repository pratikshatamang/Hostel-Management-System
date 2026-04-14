<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (hms_is_logged_in()) {
    hms_redirect(hms_user_dashboard_path(hms_current_role()));
}

$flash = hms_pull_flash();
$errors = array();
$loginValue = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginValue = trim(isset($_POST['login_id']) ? $_POST['login_id'] : '');
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($loginValue === '') {
        $errors[] = 'Enter your email or username.';
    }

    if ($password === '') {
        $errors[] = 'Enter your password.';
    }

    if (!$errors) {
        $query = "SELECT id, full_name, email, username, password, role, legacy_id
                  FROM users
                  WHERE email = ? OR username = ?
                  LIMIT 1";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('ss', $loginValue, $loginValue);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && hms_password_verify($password, $user['password'])) {
            if (hms_password_needs_upgrade($user['password'])) {
                $newHash = hms_password_hash($password);
                $updateStmt = $mysqli->prepare('UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?');
                $updateStmt->bind_param('si', $newHash, $user['id']);
                $updateStmt->execute();
                $updateStmt->close();
                $user['password'] = $newHash;
            }

            hms_login_user($user);

            if ($user['role'] === 'user') {
                $legacyId = (int) $_SESSION['id'];
                $email = $_SESSION['email'];
                $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
                $city = '';
                $country = '';
                $geoResponse = @file_get_contents('http://www.geoplugin.net/php.gp?ip=' . urlencode($ip));

                if ($geoResponse !== false) {
                    $address = @unserialize($geoResponse);
                    if (is_array($address)) {
                        $city = isset($address['geoplugin_city']) ? $address['geoplugin_city'] : '';
                        $country = isset($address['geoplugin_countryName']) ? $address['geoplugin_countryName'] : '';
                    }
                }

                $logStmt = $mysqli->prepare('INSERT INTO userlog(userId, userEmail, userIp, city, country) VALUES(?,?,?,?,?)');
                $logStmt->bind_param('issss', $legacyId, $email, $ip, $city, $country);
                $logStmt->execute();
                $logStmt->close();
            }

            hms_redirect(hms_user_dashboard_path($user['role']));
        }

        $errors[] = 'Invalid login details. Please check your email/username and password.';
    }
}
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="theme-color" content="#183153">
    <title>Hostel Login</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/user-ui.css">
</head>
<body>
<div class="auth-shell">
    <div class="container-fluid">
        <div class="auth-card row">
            <div class="col-md-6">
                <div class="auth-showcase">
                    <span class="auth-badge">Shared Login</span>
                    <h1>Hostel Management System</h1>
                    <p>Sign in to manage hostel booking, room details, profile updates, and account security from a cleaner and more responsive student portal.</p>
                    <ul class="auth-list">
                        <li><i class="fa fa-check-circle"></i> Login with your email or username</li>
                        <li><i class="fa fa-check-circle"></i> Continue to the same booking and dashboard flow</li>
                        <li><i class="fa fa-check-circle"></i> Works smoothly on mobile, tablet, and desktop screens</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6">
                <div class="auth-form">
                    <h2>Sign In</h2>
                    <p>Use your student or admin account to continue.</p>

                    <?php if ($flash): ?>
                        <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>"><?php echo htmlspecialchars($flash['message']); ?></div>
                    <?php endif; ?>
                    <?php if ($errors): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="form-group">
                            <label for="login_id">Email or Username</label>
                            <input type="text" id="login_id" name="login_id" class="form-control" value="<?php echo htmlspecialchars($loginValue); ?>" placeholder="Enter email or username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-auth">Login</button>
                    </form>

                    <div class="auth-links">
                        <a href="registration.php">Create student account</a>
                        <a href="forgot-password.php">Forgot password?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
