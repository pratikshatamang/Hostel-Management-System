<?php
session_start();
include('includes/config.php');
include('includes/auth.php');
if(isset($_POST['login']))
{
$email=$_POST['email'];
$password=$_POST['password'];
$stmt=$mysqli->prepare("SELECT id,email,password FROM userregistration WHERE email=? LIMIT 1");
$stmt->bind_param('s',$email);
$stmt->execute();
$stmt->bind_result($id,$dbEmail,$dbPassword);
$rs=$stmt->fetch();
$stmt->close();

if($rs && hms_password_verify($password,$dbPassword))
{
	session_regenerate_id(true);
	$_SESSION['id']=$id;
	$_SESSION['login']=$dbEmail;

	if(hms_password_needs_upgrade($dbPassword)){
		$newHash=hms_password_hash($password);
		$up=$mysqli->prepare("UPDATE userregistration SET password=? WHERE id=?");
		$up->bind_param('si',$newHash,$id);
		$up->execute();
		$up->close();
	}

	$uid=$_SESSION['id'];
	$uemail=$_SESSION['login'];
	$ip=$_SERVER['REMOTE_ADDR'];
	$city='';
	$country='';
	$geopluginURL='http://www.geoplugin.net/php.gp?ip='.$ip;
	$geoResponse=@file_get_contents($geopluginURL);
	if($geoResponse!==false){
		$addrDetailsArr = @unserialize($geoResponse);
		if(is_array($addrDetailsArr)){
			$city = isset($addrDetailsArr['geoplugin_city']) ? $addrDetailsArr['geoplugin_city'] : '';
			$country = isset($addrDetailsArr['geoplugin_countryName']) ? $addrDetailsArr['geoplugin_countryName'] : '';
		}
	}
	$logStmt=$mysqli->prepare("INSERT INTO userlog(userId,userEmail,userIp,city,country) VALUES(?,?,?,?,?)");
	$logStmt->bind_param('issss',$uid,$uemail,$ip,$city,$country);
	$logStmt->execute();
	$logStmt->close();
	header("location:dashboard.php");
	exit();
}
else
{
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
	<meta name="theme-color" content="#3e454c">
	<title>Student Hostel Registration</title>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
	<link rel="stylesheet" href="css/bootstrap-social.css">
	<link rel="stylesheet" href="css/bootstrap-select.css">
	<link rel="stylesheet" href="css/fileinput.min.css">
	<link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
	<link rel="stylesheet" href="css/style.css">
	<style>
		:root {
			--accent-start: #ff7a18;
			--accent-end: #af002d;
			--card-shadow: 0 18px 40px rgba(0, 0, 0, 0.18);
			--card-radius: 16px;
			--bg-soft: #fff9f1;
			--text-main: #2b2b2b;
		}

		body {
			font-family: 'Poppins', sans-serif;
			background:
				radial-gradient(circle at 15% 10%, rgba(255, 122, 24, 0.18), transparent 42%),
				radial-gradient(circle at 85% 20%, rgba(175, 0, 45, 0.14), transparent 35%),
				linear-gradient(145deg, #fffdf9 0%, #fff7ec 52%, #fff3e2 100%);
			min-height: 100vh;
		}

		.login-shell {
			min-height: calc(100vh - 72px);
			display: flex;
			align-items: center;
			padding: 20px 0 40px;
		}

		.login-card-wrap {
			max-width: 980px;
			margin: 0 auto;
		}

		.login-card {
			background: #fff;
			border-radius: var(--card-radius);
			box-shadow: var(--card-shadow);
			overflow: hidden;
			animation: fadeUp .7s ease-out;
		}

		.login-showcase {
			background: linear-gradient(160deg, var(--accent-start), var(--accent-end));
			color: #fff;
			padding: 36px 34px;
			height: 100%;
			position: relative;
		}

		.login-showcase:after {
			content: "";
			position: absolute;
			width: 200px;
			height: 200px;
			right: -70px;
			bottom: -70px;
			border-radius: 50%;
			background: rgba(255, 255, 255, 0.14);
		}

		.login-showcase h2 {
			font-size: 30px;
			font-weight: 700;
			margin-top: 0;
			margin-bottom: 10px;
		}

		.login-showcase p {
			font-size: 14px;
			line-height: 1.7;
			opacity: 0.95;
			max-width: 360px;
		}

		.login-points {
			margin-top: 26px;
			padding-left: 0;
			list-style: none;
		}

		.login-points li {
			font-size: 13px;
			margin-bottom: 10px;
		}

		.login-points i {
			width: 18px;
		}

		.login-form-panel {
			background: var(--bg-soft);
			padding: 34px 34px 28px;
		}

		.login-heading {
			margin-top: 0;
			margin-bottom: 20px;
			color: var(--text-main);
			font-size: 24px;
			font-weight: 600;
		}

		.login-form-panel .form-control {
			height: 44px;
			border-radius: 10px;
			border: 1px solid #f0d7bf;
			box-shadow: none;
			font-size: 14px;
		}

		.login-form-panel .form-control:focus {
			border-color: #ff9d52;
			box-shadow: 0 0 0 3px rgba(255, 157, 82, 0.2);
		}

		.btn-login {
			height: 44px;
			border: 0;
			border-radius: 10px;
			font-weight: 600;
			text-transform: uppercase;
			letter-spacing: .6px;
			background: linear-gradient(135deg, var(--accent-start), var(--accent-end));
			transition: transform .2s ease, box-shadow .2s ease;
		}

		.btn-login:hover {
			transform: translateY(-1px);
			box-shadow: 0 10px 18px rgba(175, 0, 45, 0.24);
		}

		.forgot-wrap {
			margin-top: 14px;
			font-size: 13px;
		}

		.forgot-wrap a {
			color: #6f3d20;
			font-weight: 500;
		}

		.forgot-wrap a:hover {
			color: #af002d;
			text-decoration: none;
		}

		@keyframes fadeUp {
			from {
				opacity: 0;
				transform: translateY(16px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		@media (max-width: 991px) {
			.login-shell {
				min-height: auto;
			}
			.login-showcase {
				padding: 26px 24px 28px;
			}
			.login-showcase h2 {
				font-size: 24px;
			}
			.login-form-panel {
				padding: 26px 24px 24px;
			}
		}
	</style>
<script type="text/javascript">
function valid()
{
if(document.registration.password.value!= document.registration.cpassword.value)
{
alert("Password and Re-Type Password Field do not match  !!");
document.registration.cpassword.focus();
return false;
}
return true;
}
</script>
</head>
<body>
	<?php include('includes/header.php');?>
	<div class="ts-main-content">
		<?php include('includes/sidebar.php');?>
		<div class="content-wrapper">
			<div class="container-fluid">
				<div class="login-shell">
					<div class="row login-card-wrap">
						<div class="col-md-10 col-md-offset-1">
							<div class="login-card row">
								<div class="col-md-6">
									<div class="login-showcase">
										<h2>Welcome Back</h2>
										<p>Sign in to continue your hostel journey. Manage room booking, profile details, and access logs in one simple place.</p>
										<ul class="login-points">
											<li><i class="fa fa-check-circle"></i> Clean and simple login flow</li>
											<li><i class="fa fa-check-circle"></i> Fully responsive on mobile and desktop</li>
											<li><i class="fa fa-check-circle"></i> Better readability and lively visual feel</li>
										</ul>
									</div>
								</div>
								<div class="col-md-6">
									<div class="login-form-panel">
										<h2 class="login-heading">User Login</h2>
										<form action="" class="mt" method="post">
											<label for="email" class="text-uppercase text-sm">Email</label>
											<input id="email" type="text" placeholder="Email" name="email" class="form-control mb" required>
											<label for="password" class="text-uppercase text-sm">Password</label>
											<input id="password" type="password" placeholder="Password" name="password" class="form-control mb" required>
											<input type="submit" name="login" class="btn btn-primary btn-block btn-login" value="Login">
										</form>
										<div class="forgot-wrap text-center">
											<a href="forgot-password.php">Forgot password?</a>
										</div>
									</div>
								</div>
							</div>
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
