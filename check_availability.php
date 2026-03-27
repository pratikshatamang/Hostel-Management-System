<?php
require_once("includes/config.php");
session_start();
include("includes/auth.php");
if(!empty($_POST["emailid"])) {
	$email= $_POST["emailid"];
	if (filter_var($email, FILTER_VALIDATE_EMAIL)===false) {

		echo "error : You did not enter a valid email.";
	}
	else {
		$result ="SELECT count(*) FROM userregistration WHERE email=?";
		$stmt = $mysqli->prepare($result);
		$stmt->bind_param('s',$email);
		$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();
if($count>0)
{
echo "<span style='color:red'> Email already exist .</span>";
}
else{
	echo "<span style='color:green'> Email available for registration .</span>";
}
}
}

if(!empty($_POST["oldpassword"])) 
{
if(empty($_SESSION['id'])){
	echo "<span style='color:red'>Session expired. Please login again.</span>";
	exit();
}
$pass=$_POST["oldpassword"];
$uid=$_SESSION['id'];
$result ="SELECT password FROM userregistration WHERE id=?";
$stmt = $mysqli->prepare($result);
$stmt->bind_param('i',$uid);
$stmt->execute();
$stmt -> bind_result($dbpass);
$stmt -> fetch();
$stmt->close();
if(hms_password_verify($pass,$dbpass)) 
	echo "<span style='color:green'> Password  matched .</span>";
else echo "<span style='color:red'> Password Not matched</span>";
}


if(!empty($_POST["roomno"])) 
{
$roomno=$_POST["roomno"];
$result ="SELECT count(*) FROM registration WHERE roomno=?";
$stmt = $mysqli->prepare($result);
$stmt->bind_param('i',$roomno);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();
if($count>0)
echo "<span style='color:red'>$count. Seats already full.</span>";
else
	echo "<span style='color:red'>All Seats are Available</span>";
}
?>
