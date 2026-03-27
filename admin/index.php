<?php
session_start();
include('includes/config.php');
include('../includes/auth.php');
if (isset($_POST['login'])) {
	$username = $_POST['username'];
	$password = $_POST['password'];
	$stmt = $mysqli->prepare("SELECT id,username,email,password FROM admin WHERE (username=? OR email=?) LIMIT 1");
	$stmt->bind_param('ss', $username, $username);
	$stmt->execute();
	$stmt->bind_result($id, $dbUsername, $dbEmail, $dbPassword);
	$rs = $stmt->fetch();
	$stmt->close();
	if ($rs && hms_password_verify($password, $dbPassword)) {
		session_regenerate_id(true);
		$_SESSION['id'] = $id;
		if(hms_password_needs_upgrade($dbPassword)){
			$newHash=hms_password_hash($password);
			$up=$mysqli->prepare("UPDATE admin SET password=? WHERE id=?");
			$up->bind_param('si',$newHash,$id);
			$up->execute();
			$up->close();
		}
		//  $insert="INSERT into admin(adminid,ip)VALUES(?,?)";
		// $stmtins = $mysqli->prepare($insert);
		// $stmtins->bind_param('sH',$id,$uip);
		//$res=$stmtins->execute();
		header("location:admin-profile.php");
	} else {
		echo "<script>alert('Invalid Username/Email or password');</script>";
	}
}
?>

<!doctype html>
<html lang="en" class="no-js">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>Admin login</title>

	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
	<link rel="stylesheet" href="css/bootstrap-social.css">
	<link rel="stylesheet" href="css/bootstrap-select.css">
	<link rel="stylesheet" href="css/fileinput.min.css">
	<link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
	<link rel="stylesheet" href="css/style.css">
</head>

<body>

	<div class="login-page bk-img" style="background-image: url(img/login-bg.jpg);">
		<div class="form-content">
			<div class="container">
				<div class="row">
					<div class="col-md-6 col-md-offset-3" style="margin-top:4%">
						<h1 class="text-center text-bold text-light mt-4x">Hostel Management System</h1>
						<div class="well row pt-2x pb-3x bk-light">
							<div class="col-md-8 col-md-offset-2">

								<form action="" class="mt" method="post">
									<label for="" class="text-uppercase text-sm">Your Username or Email</label>
									<input type="text" placeholder="Username" name="username" class="form-control mb">
									<label for="" class="text-uppercase text-sm">Password</label>
									<input type="password" placeholder="Password" name="password"
										class="form-control mb">


									<input type="submit" name="login" class="btn btn-primary btn-block" value="login">
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script src="js/jquery.min.js"></script>
	<script src="js/bootstrap-select.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script src="js/jquery.dataTables.min.js"></script>
	<script src="js/dataTables.bootstrap.min.js"></script>
	<script src="js/Chart.min.js"></script>
	<script src="js/fileinput.js"></script>
	<script src="js/chartData.js"></script>
	<script src="js/main.js"></script>
</body>

</html>
