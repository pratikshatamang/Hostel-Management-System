<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();
//code for add courses
if($_POST['submit'])
{
$coursecode=$_POST['cc'];
$coursesn=$_POST['cns'];
$coursefn=$_POST['cnf'];

$query="insert into  courses (course_code,course_sn,course_fn) values(?,?,?)";
$stmt = $mysqli->prepare($query);
$rc=$stmt->bind_param('sss',$coursecode,$coursesn,$coursefn);
$stmt->execute();
echo"<script>alert('Course has been added successfully');</script>";
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
	<title>Add Courses</title>
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/dataTables.bootstrap.min.css">>
	<link rel="stylesheet" href="css/bootstrap-social.css">
	<link rel="stylesheet" href="css/bootstrap-select.css">
	<link rel="stylesheet" href="css/fileinput.min.css">
	<link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
	<link rel="stylesheet" href="css/style.css">
<script type="text/javascript" src="js/jquery-1.11.3-jquery.min.js"></script>
<script type="text/javascript" src="js/validation.min.js"></script>
</head>
<body>
	<?php include('includes/header.php');?>
	<div class="ts-main-content">
		<?php include('includes/sidebar.php');?>
		<div class="content-wrapper">
			<div class="container-fluid">

				<div class="row">
					<div class="col-md-12">
						<div class="admin-page-header admin-page-header-management">
							<div>
								<span class="admin-page-kicker">Course Setup</span>
								<h2 class="page-title">Add Courses</h2>
								<p class="admin-page-subtitle">Create a new course entry for hostel registration while keeping the existing save behavior unchanged.</p>
							</div>
						</div>

						<div class="admin-form-shell">
							<div class="panel panel-default admin-form-card">
								<div class="panel-heading">
									<h3 class="admin-form-title">Add Course</h3>
									<p class="admin-form-subtitle">Enter the course code, short name, and full name using the same field names already used by the page.</p>
								</div>
								<div class="panel-body">
									<form method="post" class="admin-form">
										<div class="admin-form-section">
											<h4 class="admin-form-section-title">Course Information</h4>
											<p class="admin-form-section-note">These fields map to the same insert query and submission action as before.</p>
											<div class="admin-form-grid">
												<div class="admin-form-col-4">
													<div class="form-group">
														<label>Course Code</label>
														<input type="text" value="" name="cc" class="form-control">
													</div>
												</div>
												<div class="admin-form-col-4">
													<div class="form-group">
														<label>Course Name (Short)</label>
														<input type="text" class="form-control" name="cns" id="cns" value="" required="required">
													</div>
												</div>
												<div class="admin-form-col-4">
													<div class="form-group">
														<label>Course Name(Full)</label>
														<input type="text" class="form-control" name="cnf" value="">
													</div>
												</div>
											</div>
										</div>
										<div class="admin-form-actions">
											<input class="btn btn-primary" type="submit" name="submit" value="Add course">
											<a href="manage-courses.php" class="btn admin-btn-secondary">Back to Courses</a>
										</div>
									</form>
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

</script>
</body>

</html>
