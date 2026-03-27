<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();

?>
<!doctype html>
<html lang="en" class="no-js">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">
	<meta name="theme-color" content="#3e454c">
	
	<title>DashBoard</title>
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
<?php include("includes/header.php");?>

	<div class="ts-main-content">
		<?php include("includes/sidebar.php");?>
		<div class="content-wrapper">
			<div class="container-fluid">
				<div class="student-hero">
					<h2>Welcome, <?php echo htmlspecialchars(isset($_SESSION['name']) ? $_SESSION['name'] : 'Student'); ?></h2>
					<p>Manage your profile, booking details, password, and hostel activity from one student-friendly dashboard.</p>
				</div>

				<div class="student-grid">
					<div class="student-card">
						<div class="icon"><i class="fa fa-user"></i></div>
						<h3>My Profile</h3>
						<p>Update your basic details and keep your student information current.</p>
						<a href="my-profile.php">Open profile <i class="fa fa-arrow-right"></i></a>
					</div>
					<div class="student-card">
						<div class="icon"><i class="fa fa-building-o"></i></div>
						<h3>My Room</h3>
						<p>View your room assignment, fee details, and complete hostel booking record.</p>
						<a href="room-details.php">View room details <i class="fa fa-arrow-right"></i></a>
					</div>
					<div class="student-card">
						<div class="icon"><i class="fa fa-lock"></i></div>
						<h3>Change Password</h3>
						<p>Keep your account secure by updating your password whenever needed.</p>
						<a href="change-password.php">Update password <i class="fa fa-arrow-right"></i></a>
					</div>
					<div class="student-card">
						<div class="icon"><i class="fa fa-file-text-o"></i></div>
						<h3>Access Log</h3>
						<p>Review your login history and check recent sign-ins to your account.</p>
						<a href="access-log.php">See access log <i class="fa fa-arrow-right"></i></a>
					</div>
				</div>

			</div>
		</div>
	</div>

	<!-- Loading Scripts -->
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
