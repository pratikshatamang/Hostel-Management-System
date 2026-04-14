<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/checklogin.php';
check_login();

$recordId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($recordId <= 0) {
    exit('Invalid record.');
}

$stmt = $mysqli->prepare('SELECT * FROM registration WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $recordId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    exit('No booking record found.');
}
?>
<script>
function f2() { window.close(); }
function f3() { window.print(); }
</script>
<!DOCTYPE html>
<html>
<head>
<meta charset="iso-8859-1" />
<title>Student Information</title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<link href="hostel.css" rel="stylesheet" type="text/css">
</head>
<body class="admin-profile-popup-body">
<?php $totalFee = $row['foodstatus'] == 1 ? (($row['duration'] * $row['feespm']) + 2000) : ($row['duration'] * $row['feespm']); ?>
<div class="admin-profile-popup">
    <div class="admin-profile-popup-card">
        <div class="admin-profile-popup-head">
            <div>
                <span class="admin-page-kicker">Student Record</span>
                <h1 class="admin-profile-popup-title"><?php echo ucfirst($row['firstName']); ?> <?php echo ucfirst($row['lastName']); ?></h1>
                <p class="admin-profile-popup-subtitle">Printable booking and profile summary generated from the existing registration record.</p>
            </div>
            <div class="admin-profile-popup-meta">
                <span class="admin-profile-popup-pill">Reg Date: <?php echo htmlspecialchars($row['postingDate']); ?></span>
                <span class="admin-profile-popup-pill">Reg No: <?php echo htmlspecialchars($row['regno']); ?></span>
            </div>
        </div>

        <div class="admin-profile-popup-grid">
            <section class="admin-profile-popup-section">
                <h2>Room Related Info</h2>
                <div class="admin-info-list">
                    <div class="admin-info-item"><span>Room No</span><strong><?php echo htmlspecialchars($row['roomno']); ?></strong></div>
                    <div class="admin-info-item"><span>Seater</span><strong><?php echo htmlspecialchars($row['seater']); ?></strong></div>
                    <div class="admin-info-item"><span>Fees PM</span><strong><?php echo htmlspecialchars($row['feespm']); ?></strong></div>
                    <div class="admin-info-item"><span>Food Status</span><strong><?php echo $row['foodstatus'] == 0 ? 'Without Food' : 'With Food'; ?></strong></div>
                    <div class="admin-info-item"><span>Staying From</span><strong><?php echo htmlspecialchars($row['stayfrom']); ?></strong></div>
                    <div class="admin-info-item"><span>Duration</span><strong><?php echo htmlspecialchars($row['duration']); ?> Month(s)</strong></div>
                    <div class="admin-info-item"><span>Total Fee</span><strong><?php echo htmlspecialchars($totalFee); ?></strong></div>
                </div>
            </section>

            <section class="admin-profile-popup-section">
                <h2>Personal Info</h2>
                <div class="admin-info-list">
                    <div class="admin-info-item"><span>Course</span><strong><?php echo htmlspecialchars($row['course']); ?></strong></div>
                    <div class="admin-info-item"><span>First Name</span><strong><?php echo htmlspecialchars($row['firstName']); ?></strong></div>
                    <div class="admin-info-item"><span>Middle Name</span><strong><?php echo htmlspecialchars($row['middleName']); ?></strong></div>
                    <div class="admin-info-item"><span>Last Name</span><strong><?php echo htmlspecialchars($row['lastName']); ?></strong></div>
                    <div class="admin-info-item"><span>Gender</span><strong><?php echo htmlspecialchars($row['gender']); ?></strong></div>
                    <div class="admin-info-item"><span>Contact No</span><strong><?php echo htmlspecialchars($row['contactno']); ?></strong></div>
                    <div class="admin-info-item"><span>Email Id</span><strong><?php echo htmlspecialchars($row['emailid']); ?></strong></div>
                    <div class="admin-info-item"><span>Emergency Contact</span><strong><?php echo htmlspecialchars($row['egycontactno']); ?></strong></div>
                    <div class="admin-info-item"><span>Guardian Name</span><strong><?php echo htmlspecialchars($row['guardianName']); ?></strong></div>
                    <div class="admin-info-item"><span>Guardian Relation</span><strong><?php echo htmlspecialchars($row['guardianRelation']); ?></strong></div>
                    <div class="admin-info-item"><span>Guardian Contact</span><strong><?php echo htmlspecialchars($row['guardianContactno']); ?></strong></div>
                </div>
            </section>

            <section class="admin-profile-popup-section">
                <h2>Correspondence Address</h2>
                <div class="admin-info-list">
                    <div class="admin-info-item"><span>Address</span><strong><?php echo htmlspecialchars($row['corresAddress']); ?></strong></div>
                    <div class="admin-info-item"><span>City</span><strong><?php echo htmlspecialchars($row['corresCIty']); ?></strong></div>
                    <div class="admin-info-item"><span>State</span><strong><?php echo htmlspecialchars($row['corresState']); ?></strong></div>
                    <div class="admin-info-item"><span>Pincode</span><strong><?php echo htmlspecialchars($row['corresPincode']); ?></strong></div>
                </div>
            </section>

            <section class="admin-profile-popup-section">
                <h2>Permanent Address</h2>
                <div class="admin-info-list">
                    <div class="admin-info-item"><span>Address</span><strong><?php echo htmlspecialchars($row['pmntAddress']); ?></strong></div>
                    <div class="admin-info-item"><span>City</span><strong><?php echo htmlspecialchars($row['pmntCity']); ?></strong></div>
                    <div class="admin-info-item"><span>State</span><strong><?php echo htmlspecialchars($row['pmnatetState']); ?></strong></div>
                    <div class="admin-info-item"><span>Pincode</span><strong><?php echo htmlspecialchars($row['pmntPincode']); ?></strong></div>
                </div>
            </section>
        </div>

        <div class="admin-profile-popup-actions">
            <button type="button" class="btn btn-primary" onclick="return f3();">Print this Document</button>
            <button type="button" class="btn admin-btn-secondary" onclick="return f2();">Close this Document</button>
        </div>
    </div>
</div>
</body>
</html>
