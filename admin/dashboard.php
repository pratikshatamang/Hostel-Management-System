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
	<meta name="theme-color" content="#6d5dfc">

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

<?php
$result ="SELECT count(*) FROM registration ";
$stmt = $mysqli->prepare($result);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

$result1 ="SELECT count(*) FROM rooms ";
$stmt1 = $mysqli->prepare($result1);
$stmt1->execute();
$stmt1->bind_result($count1);
$stmt1->fetch();
$stmt1->close();

$result2 ="SELECT count(*) FROM courses ";
$stmt2 = $mysqli->prepare($result2);
$stmt2->execute();
$stmt2->bind_result($count2);
$stmt2->fetch();
$stmt2->close();
?>

				<div class="admin-page-header">
					<div>
						<span class="admin-page-kicker">Admin Overview</span>
						<h2 class="page-title">Dashboard</h2>
						<p class="admin-page-subtitle">Monitor hostel operations, review student activity, and jump into the most important management areas from a cleaner, more responsive admin workspace.</p>
						<div class="admin-overview-chips">
							<span class="admin-overview-chip"><i class="fa fa-users"></i> <?php echo $count;?> Students</span>
							<span class="admin-overview-chip"><i class="fa fa-bed"></i> <?php echo $count1;?> Rooms</span>
							<span class="admin-overview-chip"><i class="fa fa-book"></i> <?php echo $count2;?> Courses</span>
						</div>
						<div class="admin-quick-actions">
							<a href="registration.php" class="btn btn-primary"><i class="fa fa-user-plus"></i><span>Register Student</span></a>
							<a href="create-room.php" class="btn admin-btn-secondary"><i class="fa fa-plus-square"></i><span>Add Room</span></a>
							<a href="add-courses.php" class="btn admin-btn-secondary"><i class="fa fa-bookmark"></i><span>Add Course</span></a>
						</div>
					</div>
					<div class="admin-page-badge">
						<i class="fa fa-shield"></i>
						<span>Admin Panel</span>
					</div>
				</div>

				<div class="admin-surface">
					<div class="admin-metric-grid">
						<div class="admin-stat-card admin-stat-card-students panel panel-default">
							<div class="panel-body text-light">
								<div class="admin-stat-copy">
									<div class="admin-stat-label">Registered Students</div>
									<h3 class="admin-stat-number"><?php echo $count;?></h3>
									<p class="admin-stat-note">Track all student hostel registrations from one place.</p>
									<div class="admin-stat-trend"><i class="fa fa-line-chart"></i> Core student occupancy metric</div>
								</div>
								<div class="admin-stat-icon">
									<i class="fa fa-graduation-cap"></i>
								</div>
							</div>
							<a href="manage-students.php" class="block-anchor panel-footer">
								<span>View student records</span>
								<i class="fa fa-arrow-right"></i>
							</a>
						</div>

						<div class="admin-stat-card admin-stat-card-rooms panel panel-default">
							<div class="panel-body text-light">
								<div class="admin-stat-copy">
									<div class="admin-stat-label">Total Rooms</div>
									<h3 class="admin-stat-number"><?php echo $count1;?></h3>
									<p class="admin-stat-note">Review available hostel room inventory and capacity.</p>
									<div class="admin-stat-trend"><i class="fa fa-home"></i> Room inventory overview</div>
								</div>
								<div class="admin-stat-icon">
									<i class="fa fa-bed"></i>
								</div>
							</div>
							<a href="manage-rooms.php" class="block-anchor panel-footer">
								<span>Manage room listings</span>
								<i class="fa fa-arrow-right"></i>
							</a>
						</div>

						<div class="admin-stat-card admin-stat-card-courses panel panel-default">
							<div class="panel-body text-light">
								<div class="admin-stat-copy">
									<div class="admin-stat-label">Total Courses</div>
									<h3 class="admin-stat-number"><?php echo $count2;?></h3>
									<p class="admin-stat-note">Maintain course options linked with hostel registration.</p>
									<div class="admin-stat-trend"><i class="fa fa-folder-open"></i> Academic catalog summary</div>
								</div>
								<div class="admin-stat-icon">
									<i class="fa fa-book"></i>
								</div>
							</div>
							<a href="manage-courses.php" class="block-anchor panel-footer">
								<span>Open course management</span>
								<i class="fa fa-arrow-right"></i>
							</a>
						</div>
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

	<script>
	window.onload = function(){

		// Line chart from swirlData for dashReport
		var dashReport = document.getElementById("dashReport");
		if (dashReport) {
			var ctx = dashReport.getContext("2d");
			window.myLine = new Chart(ctx).Line(swirlData, {
				responsive: true,
				scaleShowVerticalLines: false,
				scaleBeginAtZero : true,
				multiTooltipTemplate: "<%if (label){%><%=label%>: <%}%><%= value %>",
			});
		}

		// Pie Chart from doughutData
		var chartArea3 = document.getElementById("chart-area3");
		if (chartArea3) {
			var doctx = chartArea3.getContext("2d");
			window.myDoughnut = new Chart(doctx).Pie(doughnutData, {responsive : true});
		}

		// Dougnut Chart from doughnutData
		var chartArea4 = document.getElementById("chart-area4");
		if (chartArea4) {
			var doctx2 = chartArea4.getContext("2d");
			window.myDoughnut = new Chart(doctx2).Doughnut(doughnutData, {responsive : true});
		}

	}
	</script>

</body>

</html>
