<?php 
session_start();
$aid=$_SESSION['id'];
require_once("includes/config.php");
include('../includes/auth.php');
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

if(!empty($_POST["oldpassword"])) 
{
if(empty($_SESSION['id'])){
	echo "<span style='color:red'>Session expired. Please login again.</span>";
	exit();
}
$pass=$_POST["oldpassword"];
$result ="SELECT password FROM admin WHERE id=?";
$stmt = $mysqli->prepare($result);
$stmt->bind_param('i',$aid);
$stmt->execute();
$stmt -> bind_result($dbpass);
$stmt -> fetch();
$stmt->close();
if(hms_password_verify($pass,$dbpass)) 
echo "<span style='color:green'> Password  matched .</span>";
else echo "<span style='color:red'> Password Not matched</span>";
}
?>
