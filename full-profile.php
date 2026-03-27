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
<link href="style.css" rel="stylesheet" type="text/css" />
<link href="hostel.css" rel="stylesheet" type="text/css">
</head>
<body>
<table width="100%" border="0">
    <tr><td colspan="2" align="center" class="font1">&nbsp;</td></tr>
    <tr><td colspan="2" align="center" class="font1">&nbsp;</td></tr>
    <tr><td colspan="2" class="font"><?php echo ucfirst($row['firstName']); ?> <?php echo ucfirst($row['lastName']); ?>'S <span class="font1">information &raquo;</span></td></tr>
    <tr><td colspan="2" class="font"><div align="right">Reg Date : <span class="comb-value"><?php echo htmlspecialchars($row['postingDate']); ?></span></div></td></tr>
    <tr><td colspan="2" class="heading" style="color:red;">Room Related Info &raquo;</td></tr>
    <tr><td colspan="2" class="font1">
        <table width="100%" border="0">
            <tr><td class="heading">Room no :</td><td class="comb-value1"><?php echo htmlspecialchars($row['roomno']); ?></td></tr>
            <tr><td class="heading">Seater :</td><td class="comb-value1"><?php echo htmlspecialchars($row['seater']); ?></td></tr>
            <tr><td class="heading">Fees PM :</td><td class="comb-value1"><?php echo htmlspecialchars($row['feespm']); ?></td></tr>
            <tr><td class="heading">Food Status:</td><td class="comb-value1"><?php echo $row['foodstatus'] == 0 ? 'Without Food' : 'With Food'; ?></td></tr>
            <tr><td class="heading">Staying From:</td><td class="comb-value1"><?php echo htmlspecialchars($row['stayfrom']); ?></td></tr>
            <tr><td class="heading">Duration:</td><td class="comb-value1"><?php echo htmlspecialchars($row['duration']); ?></td></tr>
            <tr><td class="heading">Total Fee:</td><td class="comb-value1"><?php echo $row['foodstatus'] == 1 ? (($row['duration'] * $row['feespm']) + 2000) : ($row['duration'] * $row['feespm']); ?></td></tr>
            <tr><td colspan="2" class="heading" style="color:red;">Personal Info &raquo;</td></tr>
            <tr><td class="heading">Course:</td><td class="comb-value1"><?php echo htmlspecialchars($row['course']); ?></td></tr>
            <tr><td class="heading">Reg no:</td><td class="comb-value1"><?php echo htmlspecialchars($row['regno']); ?></td></tr>
            <tr><td class="heading">First Name:</td><td class="comb-value1"><?php echo htmlspecialchars($row['firstName']); ?></td></tr>
            <tr><td class="heading">Middle Name:</td><td class="comb-value1"><?php echo htmlspecialchars($row['middleName']); ?></td></tr>
            <tr><td class="heading">Last Name:</td><td class="comb-value1"><?php echo htmlspecialchars($row['lastName']); ?></td></tr>
            <tr><td class="heading">Gender:</td><td class="comb-value1"><?php echo htmlspecialchars($row['gender']); ?></td></tr>
            <tr><td class="heading">Contact No:</td><td class="comb-value1"><?php echo htmlspecialchars($row['contactno']); ?></td></tr>
            <tr><td class="heading">Email id:</td><td class="comb-value1"><?php echo htmlspecialchars($row['emailid']); ?></td></tr>
            <tr><td class="heading">Emergency Contact:</td><td class="comb-value1"><?php echo htmlspecialchars($row['egycontactno']); ?></td></tr>
            <tr><td class="heading">Guardian Name:</td><td class="comb-value1"><?php echo htmlspecialchars($row['guardianName']); ?></td></tr>
            <tr><td class="heading">Guardian Relation:</td><td class="comb-value1"><?php echo htmlspecialchars($row['guardianRelation']); ?></td></tr>
            <tr><td class="heading">Guardian Contact:</td><td class="comb-value1"><?php echo htmlspecialchars($row['guardianContactno']); ?></td></tr>
            <tr><td colspan="2" class="heading" style="color:red;">Correspondence Address &raquo;</td></tr>
            <tr><td class="heading">Address:</td><td class="comb-value1"><?php echo htmlspecialchars($row['corresAddress']); ?></td></tr>
            <tr><td class="heading">City:</td><td class="comb-value1"><?php echo htmlspecialchars($row['corresCIty']); ?></td></tr>
            <tr><td class="heading">State:</td><td class="comb-value1"><?php echo htmlspecialchars($row['corresState']); ?></td></tr>
            <tr><td class="heading">Pincode:</td><td class="comb-value1"><?php echo htmlspecialchars($row['corresPincode']); ?></td></tr>
            <tr><td colspan="2" class="heading" style="color:red;">Permanent Address &raquo;</td></tr>
            <tr><td class="heading">Address:</td><td class="comb-value1"><?php echo htmlspecialchars($row['pmntAddress']); ?></td></tr>
            <tr><td class="heading">City:</td><td class="comb-value1"><?php echo htmlspecialchars($row['pmntCity']); ?></td></tr>
            <tr><td class="heading">State:</td><td class="comb-value1"><?php echo htmlspecialchars($row['pmnatetState']); ?></td></tr>
            <tr><td class="heading">Pincode:</td><td class="comb-value1"><?php echo htmlspecialchars($row['pmntPincode']); ?></td></tr>
        </table>
    </td></tr>
    <tr>
        <td colspan="2" align="right">
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="35%" class="comb-value"><input type="submit" class="txtbox4" value="Print this Document" onclick="return f3();" /></td>
                    <td width="26%"><input type="submit" class="txtbox4" value="Close this Document" onclick="return f2();" /></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
