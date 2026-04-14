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

    $pref_seater = isset($_POST['pref_seater']) ? trim($_POST['pref_seater']) : '';
    $pref_attached_bathroom = isset($_POST['pref_attached_bathroom']) ? 1 : 0;
    $pref_air_conditioner = isset($_POST['pref_air_conditioner']) ? 1 : 0;
    $pref_wifi = isset($_POST['pref_wifi']) ? 1 : 0;
    $pref_balcony = isset($_POST['pref_balcony']) ? 1 : 0;
    $pref_study_table = isset($_POST['pref_study_table']) ? 1 : 0;

    if (!preg_match('/^[0-9]{7,15}$/', $contact)) {
        $errors[] = 'Enter a valid contact number.';
    }

    if (!$errors) {
        $updatedAt = date('d-m-Y h:i:s', time());
        $fullName = trim($fname . ' ' . $mname . ' ' . $lname);

        $pref_seater_val = $pref_seater !== '' ? (int)$pref_seater : null;

        $studentStmt = $mysqli->prepare('UPDATE userregistration SET regNo = ?, firstName = ?, middleName = ?, lastName = ?, gender = ?, contactNo = ?, updationDate = ?, pref_seater = ?, pref_attached_bathroom = ?, pref_air_conditioner = ?, pref_wifi = ?, pref_balcony = ?, pref_study_table = ? WHERE id = ?');
        $studentStmt->bind_param('sssssssiiiiiii', $regno, $fname, $mname, $lname, $gender, $contact, $updatedAt, $pref_seater_val, $pref_attached_bathroom, $pref_air_conditioner, $pref_wifi, $pref_balcony, $pref_study_table, $legacyId);
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

$fullName = trim($profile['firstName'] . ' ' . $profile['middleName'] . ' ' . $profile['lastName']);
?>
<!doctype html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="theme-color" content="#183153">
    <title>Profile Updation</title>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/user-ui.css">
</head>
<body>
<?php include 'includes/header.php'; ?>
<div class="ts-main-content">
    <?php include 'includes/sidebar.php'; ?>
    <div class="content-wrapper">
        <div class="container-fluid">
            <section class="ui-hero" style="padding: 35px 40px; border-radius: 20px;">
                <span class="ui-badge"><i class="fa fa-address-card"></i> Profile Hub</span>
                <h2>My Personal Profile</h2>
                <p style="font-size: 16px;">Review and securely update your core student information and room preferences.</p>
            </section>

            <div class="ui-grid cols-2" style="margin-bottom: 24px;">
                <div class="student-card display-flex" style="flex-direction:column; justify-content:center;">
                    <div class="icon" style="background: linear-gradient(135deg, #e8f1ff, #cde0ff); box-shadow: 0 4px 15px rgba(40,94,168,0.15);"><i class="fa fa-id-card"></i></div>
                    <h3 class="name"><?php echo htmlspecialchars($fullName); ?></h3>
                    <p style="margin-bottom: 15px;">Primary Registration Information</p>
                    <div style="background: #f8fbff; border-radius: 12px; padding: 15px; border: 1px solid #ebf1f6;">
                        <span style="display:block; margin-bottom:5px;"><strong>Reg:</strong> <?php echo htmlspecialchars($profile['regNo']); ?></span>
                        <span style="display:block; margin-bottom:5px;"><strong>Email:</strong> <?php echo htmlspecialchars($profile['email']); ?></span>
                        <span style="display:block;"><strong>Gender:</strong> <span style="text-transform: capitalize;"><?php echo htmlspecialchars($profile['gender']); ?></span></span>
                    </div>
                </div>

                <div class="ui-surface ui-data-card" style="border:0; background:transparent; box-shadow:none; padding:0;">
                    <div class="student-card" style="height:100%;">
                        <div class="icon" style="background: linear-gradient(135deg, #eafbf3, #c8f5df); box-shadow: 0 4px 15px rgba(22,122,88,0.15); color: #167a58;"><i class="fa fa-clock-o"></i></div>
                        <h3 class="ui-section-title" style="margin-top:0;">Account Snapshot</h3>
                        <p style="margin-bottom: 15px;">Current active details linked to your secure login.</p>
                        
                        <div class="ui-data-list">
                            <div class="ui-data-item" style="box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                                <span>Last Update</span>
                                <strong><?php echo htmlspecialchars($profile['updationDate'] !== '' ? $profile['updationDate'] : 'Not available'); ?></strong>
                            </div>
                            <div class="ui-data-item" style="box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                                <span>Contact Number</span>
                                <strong><?php echo htmlspecialchars($profile['contactNo']); ?></strong>
                            </div>
                            <div class="ui-data-item full" style="box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                                <span>Email Address</span>
                                <strong><?php echo htmlspecialchars($profile['email']); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ui-surface">
                <div class="ui-surface-head">
                    <h3>Update Details</h3>
                    <p>The form structure is improved for readability, but the same backend fields and save flow are preserved.</p>
                </div>
                <div class="ui-surface-body">
                    <div class="ui-alert-stack">
                        <?php if ($successMessage !== ''): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
                        <?php endif; ?>
                        <?php if ($errors): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div>
                        <?php endif; ?>
                    </div>

                    <form method="post" class="ui-form">
                        <div class="ui-form-section">
                            <h4 class="ui-section-title">Student Details</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="regno">Registration No</label>
                                        <input type="text" id="regno" name="regno" class="form-control" value="<?php echo htmlspecialchars($profile['regNo']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact">Contact No</label>
                                        <input type="text" id="contact" name="contact" class="form-control" value="<?php echo htmlspecialchars($profile['contactNo']); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="fname">First Name</label>
                                        <input type="text" id="fname" name="fname" class="form-control" value="<?php echo htmlspecialchars($profile['firstName']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="mname">Middle Name</label>
                                        <input type="text" id="mname" name="mname" class="form-control" value="<?php echo htmlspecialchars($profile['middleName']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="lname">Last Name</label>
                                        <input type="text" id="lname" name="lname" class="form-control" value="<?php echo htmlspecialchars($profile['lastName']); ?>" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ui-form-section">
                            <h4 class="ui-section-title">Account Details</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="gender">Gender</label>
                                        <select id="gender" name="gender" class="form-control" required>
                                            <option value="male" <?php echo $profile['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                                            <option value="female" <?php echo $profile['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                                            <option value="others" <?php echo $profile['gender'] === 'others' ? 'selected' : ''; ?>>Others</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" class="form-control input-readonly" value="<?php echo htmlspecialchars($profile['email']); ?>" readonly>
                                        <div class="ui-form-help">Email remains read-only to preserve the current account flow.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ui-form-section">
                            <h4 class="ui-section-title">Room Preferences</h4>
                            <p class="text-muted" style="margin-bottom:15px;">Update your preferences so we can recommend the best matching rooms during booking.</p>
                            
                            <div class="form-group">
                                <label for="pref_seater">Preferred Seater</label>
                                <select name="pref_seater" id="pref_seater" class="form-control" style="max-width: 300px;">
                                    <option value="">Any Seater</option>
                                    <option value="1" <?php echo strval($profile['pref_seater']) === '1' ? 'selected' : ''; ?>>Single Seater</option>
                                    <option value="2" <?php echo strval($profile['pref_seater']) === '2' ? 'selected' : ''; ?>>Two Seater</option>
                                    <option value="3" <?php echo strval($profile['pref_seater']) === '3' ? 'selected' : ''; ?>>Three Seater</option>
                                    <option value="4" <?php echo strval($profile['pref_seater']) === '4' ? 'selected' : ''; ?>>Four Seater</option>
                                    <option value="5" <?php echo strval($profile['pref_seater']) === '5' ? 'selected' : ''; ?>>Five Seater</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-sm-4 col-md-3"><label style="font-weight:normal;" class="ui-check-option"><input type="checkbox" name="pref_attached_bathroom" value="1" <?php echo !empty($profile['pref_attached_bathroom']) ? 'checked' : ''; ?>> Attached Bathroom</label></div>
                                <div class="col-sm-4 col-md-3"><label style="font-weight:normal;" class="ui-check-option"><input type="checkbox" name="pref_air_conditioner" value="1" <?php echo !empty($profile['pref_air_conditioner']) ? 'checked' : ''; ?>> Air Conditioner</label></div>
                                <div class="col-sm-4 col-md-3"><label style="font-weight:normal;" class="ui-check-option"><input type="checkbox" name="pref_wifi" value="1" <?php echo !empty($profile['pref_wifi']) ? 'checked' : ''; ?>> Wi-Fi</label></div>
                                <div class="col-sm-4 col-md-3"><label style="font-weight:normal;" class="ui-check-option"><input type="checkbox" name="pref_balcony" value="1" <?php echo !empty($profile['pref_balcony']) ? 'checked' : ''; ?>> Balcony</label></div>
                                <div class="col-sm-4 col-md-3" style="margin-top:12px;"><label style="font-weight:normal;" class="ui-check-option"><input type="checkbox" name="pref_study_table" value="1" <?php echo !empty($profile['pref_study_table']) ? 'checked' : ''; ?>> Study Table</label></div>
                            </div>
                        </div>

                        <div class="ui-actions">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                            <a href="dashboard.php" class="btn btn-default">Back to Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/main.js"></script>
</body>
</html>
