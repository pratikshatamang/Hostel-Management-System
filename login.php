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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; min-height: 100vh; background: radial-gradient(circle at top left, rgba(255,183,3,.16), transparent 32%), linear-gradient(135deg, #eef5fb 0%, #dfeaf5 50%, #edf4fb 100%); color: #17324d; }
        .auth-shell { min-height: 100vh; display: flex; align-items: center; padding: 30px 15px; }
        .auth-card { max-width: 1020px; margin: 0 auto; background: #fff; border-radius: 20px; box-shadow: 0 22px 50px rgba(18,44,69,.16); overflow: hidden; }
        .auth-showcase { background: linear-gradient(160deg, #1c4e80, #163b5f); color: #fff; padding: 42px 36px; min-height: 100%; position: relative; }
        .auth-showcase::after { content: ""; position: absolute; width: 220px; height: 220px; right: -70px; bottom: -80px; border-radius: 50%; background: rgba(255,255,255,.12); }
        .auth-showcase h1 { margin: 0 0 14px; font-size: 34px; font-weight: 700; }
        .auth-showcase p { font-size: 14px; line-height: 1.8; max-width: 420px; }
        .auth-list { list-style: none; padding: 0; margin: 24px 0 0; }
        .auth-list li { margin-bottom: 12px; font-size: 14px; }
        .auth-list i { width: 22px; color: #ffb703; }
        .auth-form { padding: 42px 36px; }
        .auth-form h2 { margin: 0 0 8px; font-size: 28px; font-weight: 700; }
        .auth-form p { color: #5f7183; margin-bottom: 26px; }
        .form-control { height: 46px; border-radius: 12px; border: 1px solid #d5e1eb; box-shadow: none; }
        .form-control:focus { border-color: #7fb0db; box-shadow: 0 0 0 3px rgba(28,78,128,.12); }
        .btn-auth { height: 46px; border: 0; border-radius: 12px; font-weight: 600; background: linear-gradient(135deg, #1c4e80, #2a6aa8); }
        .auth-links { margin-top: 18px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
        .auth-links a { color: #1c4e80; font-weight: 500; text-decoration: none; }
        .auth-badge { display: inline-block; margin-bottom: 14px; padding: 6px 12px; border-radius: 999px; background: rgba(255,255,255,.14); font-size: 12px; text-transform: uppercase; letter-spacing: .08em; }
        @media (max-width: 991px) { .auth-showcase, .auth-form { padding: 28px 24px; } .auth-showcase h1 { font-size: 28px; } }
    </style>
</head>
<body>
    <div class="auth-shell">
        <div class="container-fluid">
            <div class="auth-card row">
                <div class="col-md-6">
                    <div class="auth-showcase">
                        <span class="auth-badge">Shared Login</span>
                        <h1>Hostel Management System</h1>
                        <p>Admins and students now use one secure login form. After sign-in, each account is sent to the correct dashboard automatically.</p>
                        <ul class="auth-list">
                            <li><i class="fa fa-check-circle"></i> Login with email or username</li>
                            <li><i class="fa fa-check-circle"></i> Passwords protected with secure hashing</li>
                            <li><i class="fa fa-check-circle"></i> Responsive layout for phone, tablet, and desktop</li>
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
