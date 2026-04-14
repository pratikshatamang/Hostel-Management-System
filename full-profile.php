<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/checklogin.php';
check_login();

$email = isset($_GET['id']) ? trim($_GET['id']) : '';
if ($email === '' || $email !== $_SESSION['email']) {
    exit('Access denied.');
}

$stmt = $mysqli->prepare('SELECT * FROM registration WHERE emailid = ? LIMIT 1');
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    exit('No booking record found.');
}

$totalFee = $row['foodstatus'] == 1 ? (($row['duration'] * $row['feespm']) + 2000) : ($row['duration'] * $row['feespm']);
?>
<script>
function f2() { window.close(); }
function f3() { window.print(); }
</script>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Student Information</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/user-ui.css">
</head>
<body class="print-shell">
    <div class="print-card">
        <div class="print-head">
            <h1><?php echo htmlspecialchars(ucfirst($row['firstName']) . ' ' . ucfirst($row['lastName'])); ?>'s Information</h1>
            <p>Registration date: <?php echo htmlspecialchars($row['postingDate']); ?></p>
        </div>
        <div class="print-body">
            <div class="ui-grid cols-2">
                <div class="ui-data-card">
                    <h3 class="ui-section-title">Room Related Info</h3>
                    <div class="ui-data-list">
                        <div class="ui-data-item">
                            <span>Room No</span>
                            <strong><?php echo htmlspecialchars($row['roomno']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>Seater</span>
                            <strong><?php echo htmlspecialchars($row['seater']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>Fees Per Month</span>
                            <strong><?php echo htmlspecialchars($row['feespm']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>Food Status</span>
                            <strong><?php echo $row['foodstatus'] == 0 ? 'Without Food' : 'With Food'; ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>Staying From</span>
                            <strong><?php echo htmlspecialchars($row['stayfrom']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>Duration</span>
                            <strong><?php echo htmlspecialchars($row['duration']); ?> Months</strong>
                        </div>
                        <div class="ui-data-item full">
                            <span>Total Fee</span>
                            <strong><?php echo htmlspecialchars($totalFee); ?></strong>
                        </div>
                    </div>
                </div>

                <div class="ui-data-card">
                    <h3 class="ui-section-title">Personal Info</h3>
                    <div class="ui-data-list">
                        <div class="ui-data-item">
                            <span>Course</span>
                            <strong><?php echo htmlspecialchars($row['course']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>Registration No</span>
                            <strong><?php echo htmlspecialchars($row['regno']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>First Name</span>
                            <strong><?php echo htmlspecialchars($row['firstName']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>Middle Name</span>
                            <strong><?php echo htmlspecialchars($row['middleName']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>Last Name</span>
                            <strong><?php echo htmlspecialchars($row['lastName']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>Gender</span>
                            <strong><?php echo htmlspecialchars($row['gender']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>Contact No</span>
                            <strong><?php echo htmlspecialchars($row['contactno']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>Email Id</span>
                            <strong><?php echo htmlspecialchars($row['emailid']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>Emergency Contact</span>
                            <strong><?php echo htmlspecialchars($row['egycontactno']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>Guardian Name</span>
                            <strong><?php echo htmlspecialchars($row['guardianName']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>Guardian Relation</span>
                            <strong><?php echo htmlspecialchars($row['guardianRelation']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>Guardian Contact</span>
                            <strong><?php echo htmlspecialchars($row['guardianContactno']); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ui-grid cols-2" style="margin-top: 20px;">
                <div class="ui-data-card">
                    <h3 class="ui-section-title">Correspondence Address</h3>
                    <div class="ui-data-list">
                        <div class="ui-data-item full">
                            <span>Address</span>
                            <strong><?php echo htmlspecialchars($row['corresAddress']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>City</span>
                            <strong><?php echo htmlspecialchars($row['corresCIty']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>State</span>
                            <strong><?php echo htmlspecialchars($row['corresState']); ?></strong>
                        </div>
                        <div class="ui-data-item full">
                            <span>Pincode</span>
                            <strong><?php echo htmlspecialchars($row['corresPincode']); ?></strong>
                        </div>
                    </div>
                </div>

                <div class="ui-data-card">
                    <h3 class="ui-section-title">Permanent Address</h3>
                    <div class="ui-data-list">
                        <div class="ui-data-item full">
                            <span>Address</span>
                            <strong><?php echo htmlspecialchars($row['pmntAddress']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>City</span>
                            <strong><?php echo htmlspecialchars($row['pmntCity']); ?></strong>
                        </div>
                        <div class="ui-data-item">
                            <span>State</span>
                            <strong><?php echo htmlspecialchars($row['pmnatetState']); ?></strong>
                        </div>
                        <div class="ui-data-item full">
                            <span>Pincode</span>
                            <strong><?php echo htmlspecialchars($row['pmntPincode']); ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="print-actions">
                <button type="button" class="btn btn-primary" onclick="return f3();">Print this Document</button>
                <button type="button" class="btn btn-default" onclick="return f2();">Close this Document</button>
            </div>
        </div>
    </div>
</body>
</html>
