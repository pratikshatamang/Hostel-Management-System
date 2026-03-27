<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/checklogin.php';
check_login();

$legacyId = (int) $_SESSION['id'];
$userId = (int) $_SESSION['user_id'];
$errors = array();
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $regno = trim(isset($_POST['regno']) ? $_POST['regno'] : '');
    $fname = trim(isset($_POST['fname']) ? $_POST['fname'] : '');
    $mname = trim(isset($_POST['mname']) ? $_POST['mname'] : '');
    $lname = trim(isset($_POST['lname']) ? $_POST['lname'] : '');
    $gender = trim(isset($_POST['gender']) ? $_POST['gender'] : '');
    $contact = trim(isset($_POST['contact']) ? $_POST['contact'] : '');

    if ($regno === '' || $fname === '' || $lname === '' || $gender === '' || $contact === '') {
        $errors[] = 'Please fill all required fields.';
    }

    if (!preg_match('/^[0-9]{7,15}$/', $contact)) {
        $errors[] = 'Enter a valid contact number.';
    }

    if (!$errors) {
        $updatedAt = date('d-m-Y h:i:s', time());
        $fullName = trim($fname . ' ' . $mname . ' ' . $lname);

        $studentStmt = $mysqli->prepare('UPDATE userregistration SET regNo = ?, firstName = ?, middleName = ?, lastName = ?, gender = ?, contactNo = ?, updationDate = ? WHERE id = ?');
        $studentStmt->bind_param('sssssssi', $regno, $fname, $mname, $lname, $gender, $contact, $updatedAt, $legacyId);
        $studentStmt->execute();
        $studentStmt->close();

        $authStmt = $mysqli->prepare('UPDATE users SET full_name = ?, phone = ?, updated_at = NOW() WHERE id = ?');
        $authStmt->bind_param('ssi', $fullName, $contact, $userId);
        $authStmt->execute();
        $authStmt->close();

        $_SESSION['name'] = $fullName;
        $successMessage = 'Profile updated successfully.';
    }
}

$stmt = $mysqli->prepare('SELECT * FROM userregistration WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $legacyId);
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
    <title>Profile Updation</title>
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
                        <h2 class="page-title"><?php echo htmlspecialchars($profile['firstName']); ?>'s Profile</h2>
                        <div class="panel panel-primary">
                            <div class="panel-heading">Last update: <?php echo htmlspecialchars($profile['updationDate'] !== '' ? $profile['updationDate'] : 'Not available'); ?></div>
                            <div class="panel-body">
                                <?php if ($successMessage !== ''): ?>
                                    <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
                                <?php endif; ?>
                                <?php if ($errors): ?>
                                    <div class="alert alert-danger"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
                                <?php endif; ?>
                                <form method="post" class="form-horizontal">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Registration No</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="regno" class="form-control" value="<?php echo htmlspecialchars($profile['regNo']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">First Name</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="fname" class="form-control" value="<?php echo htmlspecialchars($profile['firstName']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Middle Name</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="mname" class="form-control" value="<?php echo htmlspecialchars($profile['middleName']); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Last Name</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="lname" class="form-control" value="<?php echo htmlspecialchars($profile['lastName']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Gender</label>
                                        <div class="col-sm-8">
                                            <select name="gender" class="form-control" required>
                                                <option value="male" <?php echo $profile['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                                                <option value="female" <?php echo $profile['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                                                <option value="others" <?php echo $profile['gender'] === 'others' ? 'selected' : ''; ?>>Others</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Contact No</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($profile['contactNo']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Email</label>
                                        <div class="col-sm-8">
                                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($profile['email']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-sm-offset-4">
                                        <button type="submit" class="btn btn-primary">Update Profile</button>
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
</body>
</html>
