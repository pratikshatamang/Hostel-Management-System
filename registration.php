<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

hms_bootstrap_auth_schema($mysqli);

if (hms_is_logged_in()) {
    hms_redirect(hms_user_dashboard_path(hms_current_role()));
}

$errors = array();
$successMessage = '';
$formData = array(
    'regno' => '',
    'username' => '',
    'fname' => '',
    'mname' => '',
    'lname' => '',
    'gender' => '',
    'contact' => '',
    'email' => '',
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($formData as $key => $value) {
        $formData[$key] = trim(isset($_POST[$key]) ? $_POST[$key] : '');
    }

    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirmPassword = isset($_POST['cpassword']) ? trim($_POST['cpassword']) : '';

    if ($formData['regno'] === '') {
        $errors[] = 'Registration number is required.';
    }

    if ($formData['username'] === '') {
        $errors[] = 'Username is required.';
    } elseif (!preg_match('/^[A-Za-z0-9_.-]{4,30}$/', $formData['username'])) {
        $errors[] = 'Username must be 4 to 30 characters and may contain letters, numbers, dot, underscore, or hyphen.';
    }

    if ($formData['fname'] === '' || $formData['lname'] === '') {
        $errors[] = 'First name and last name are required.';
    }

    if ($formData['gender'] === '') {
        $errors[] = 'Please select gender.';
    }

    if ($formData['contact'] === '') {
        $errors[] = 'Contact number is required.';
    } elseif (!preg_match('/^[0-9]{7,15}$/', $formData['contact'])) {
        $errors[] = 'Enter a valid contact number.';
    }

    if ($formData['email'] === '') {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Password and confirm password do not match.';
    }

    if (!$errors) {
        if (!hms_table_exists($mysqli, 'users')) {
            $errors[] = 'Authentication setup is incomplete. Please reload the page and try again.';
        }
    }

    if (!$errors) {
        $checkUserStmt = $mysqli->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
        $checkUserStmt->bind_param('ss', $formData['email'], $formData['username']);
        $checkUserStmt->execute();
        $checkUserStmt->store_result();
        if ($checkUserStmt->num_rows > 0) {
            $errors[] = 'Email or username already exists.';
        }
        $checkUserStmt->close();

        $checkRegStmt = $mysqli->prepare('SELECT id FROM userregistration WHERE email = ? OR regNo = ? LIMIT 1');
        $checkRegStmt->bind_param('ss', $formData['email'], $formData['regno']);
        $checkRegStmt->execute();
        $checkRegStmt->store_result();
        if ($checkRegStmt->num_rows > 0) {
            $errors[] = 'Email or registration number is already registered.';
        }
        $checkRegStmt->close();
    }

    if (!$errors) {
        $fullName = trim($formData['fname'] . ' ' . $formData['mname'] . ' ' . $formData['lname']);
        $passwordHash = hms_password_hash($password);

        $mysqli->begin_transaction();

        try {
            $studentStmt = $mysqli->prepare('INSERT INTO userregistration(regNo, firstName, middleName, lastName, gender, contactNo, email, password) VALUES(?,?,?,?,?,?,?,?)');
            $studentStmt->bind_param(
                'ssssssss',
                $formData['regno'],
                $formData['fname'],
                $formData['mname'],
                $formData['lname'],
                $formData['gender'],
                $formData['contact'],
                $formData['email'],
                $passwordHash
            );
            $studentStmt->execute();
            $legacyId = $studentStmt->insert_id;
            $studentStmt->close();

            $authStmt = $mysqli->prepare('INSERT INTO users(full_name, email, username, phone, password, role, legacy_id, created_at, updated_at) VALUES(?,?,?,?,?,\'user\',?,NOW(),NOW())');
            $authStmt->bind_param(
                'sssssi',
                $fullName,
                $formData['email'],
                $formData['username'],
                $formData['contact'],
                $passwordHash,
                $legacyId
            );
            $authStmt->execute();
            $authStmt->close();

            $mysqli->commit();
            $successMessage = 'Registration completed successfully. You can now log in with your email or username.';
            $formData = array_fill_keys(array_keys($formData), '');
        } catch (Exception $exception) {
            $mysqli->rollback();
            $errors[] = 'Registration failed. Please try again.';
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
    <meta name="theme-color" content="#0d4d4a">
    <title>User Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; min-height: 100vh; background: linear-gradient(135deg, #edf8f7 0%, #e0f0ef 48%, #f8fcfc 100%); color: #1f3c3a; }
        .register-shell { padding: 28px 15px; }
        .register-card { max-width: 1080px; margin: 0 auto; background: #fff; border-radius: 22px; overflow: hidden; box-shadow: 0 22px 48px rgba(19, 64, 60, .14); }
        .register-side { background: linear-gradient(160deg, #0f766e, #0b4f57); color: #fff; padding: 38px 34px; min-height: 100%; }
        .register-side h1 { margin-top: 0; font-size: 32px; font-weight: 700; }
        .register-side p { line-height: 1.8; font-size: 14px; max-width: 360px; }
        .register-side ul { list-style: none; padding: 0; margin: 24px 0 0; }
        .register-side li { margin-bottom: 12px; font-size: 14px; }
        .register-side i { width: 22px; color: #ffd166; }
        .register-form { padding: 38px 34px; }
        .register-form h2 { margin-top: 0; font-size: 28px; font-weight: 700; }
        .register-form p { color: #557370; margin-bottom: 22px; }
        .form-control { height: 46px; border-radius: 12px; border: 1px solid #d8e7e5; box-shadow: none; }
        textarea.form-control { height: auto; }
        .form-control:focus { border-color: #57a59d; box-shadow: 0 0 0 3px rgba(15, 118, 110, .12); }
        .btn-register { height: 48px; border: 0; border-radius: 12px; font-weight: 600; background: linear-gradient(135deg, #0f766e, #15988d); }
        .helper-link { margin-top: 16px; }
        .helper-link a { color: #0f766e; font-weight: 600; text-decoration: none; }
        @media (max-width: 991px) { .register-side, .register-form { padding: 28px 24px; } }
    </style>
</head>
<body>
    <div class="register-shell">
        <div class="register-card row">
            <div class="col-md-4">
                <div class="register-side">
                    <h1>Create Student Account</h1>
                    <p>This is  registration page for Hostel Management System. Register </p>
                    <ul>
                        <li><i class="fa fa-check-circle"></i> Public registration always saves role as user</li>
                        <li><i class="fa fa-check-circle"></i> Email and username duplicates are blocked</li>
                        <li><i class="fa fa-check-circle"></i> Passwords are stored securely with hashing</li>
                    </ul>
                </div>
            </div>
            <div class="col-md-8">
                <div class="register-form">
                    <h2>Student Registration</h2>
                    <p>Fill in the required details to create your hostel login account.</p>

                    <?php if ($successMessage !== ''): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php endif; ?>

                    <?php if ($errors): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="regno">Registration No</label>
                                    <input type="text" name="regno" id="regno" class="form-control" value="<?php echo htmlspecialchars($formData['regno']); ?>" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" name="username" id="username" class="form-control" value="<?php echo htmlspecialchars($formData['username']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="fname">First Name</label>
                                    <input type="text" name="fname" id="fname" class="form-control" value="<?php echo htmlspecialchars($formData['fname']); ?>" required>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="mname">Middle Name</label>
                                    <input type="text" name="mname" id="mname" class="form-control" value="<?php echo htmlspecialchars($formData['mname']); ?>">
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">
                                    <label for="lname">Last Name</label>
                                    <input type="text" name="lname" id="lname" class="form-control" value="<?php echo htmlspecialchars($formData['lname']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="gender">Gender</label>
                                    <select name="gender" id="gender" class="form-control" required>
                                        <option value="">Select Gender</option>
                                        <option value="male" <?php echo $formData['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="female" <?php echo $formData['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="others" <?php echo $formData['gender'] === 'others' ? 'selected' : ''; ?>>Others</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="contact">Phone</label>
                                    <input type="text" name="contact" id="contact" class="form-control" value="<?php echo htmlspecialchars($formData['contact']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" name="email" id="email" class="form-control" value="<?php echo htmlspecialchars($formData['email']); ?>" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" name="password" id="password" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="cpassword">Confirm Password</label>
                            <input type="password" name="cpassword" id="cpassword" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-register">Register</button>
                    </form>

                    <div class="helper-link">
                        Already have an account? <a href="login.php">Go to login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
