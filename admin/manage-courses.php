<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
check_login();

if(isset($_GET['del']))
{
	$id=intval($_GET['del']);
	$adn="delete from courses where id=?";
		$stmt= $mysqli->prepare($adn);
		$stmt->bind_param('i',$id);
        $stmt->execute();
        $stmt->close();	   
        echo "<script>alert('Data Deleted');</script>" ;
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
	<title>Manage Courses</title>
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
	<?php include('includes/header.php');?>

	<div class="ts-main-content">
			<?php include('includes/sidebar.php');?>
		<div class="content-wrapper">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-12">
						<div class="admin-page-header admin-page-header-management">
							<div>
								<span class="admin-page-kicker">Course Management</span>
								<h2 class="page-title">Manage Courses</h2>
								<p class="admin-page-subtitle">Organize available academic courses and keep the hostel registration catalog clean and up to date.</p>
							</div>
							<div class="admin-page-actions">
								<a href="add-courses.php" class="btn admin-btn admin-btn-primary">
									<i class="fa fa-plus"></i>
									<span>Add Course</span>
								</a>
							</div>
						</div>

						<div class="panel panel-default admin-table-card">
							<div class="panel-heading admin-table-card-head">
								<div>
									<h3 class="admin-section-title">All Course Details</h3>
									<p class="admin-section-subtitle">Edit or remove course entries using the same existing actions.</p>
								</div>
							</div>
							<div class="panel-body">
								<div class="table-responsive admin-table-wrap">
								<table id="zctb" class="display table admin-data-table" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>Sno.</th>
											<th>Course Code</th>
											<th>Course Name(Short)</th>
											<th>Course Name(Full)</th>
											<th>Reg Date </th>
											<th>Action</th>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<th>Sl No</th>
											<th>Course Code</th>
											<th>Course Name(Short)</th>
											<th>Course Name(Full)</th>
											<th>Regd Date</th>
											<th>Action</th>										</tr>
									</tfoot>
									<tbody>
<?php	
$aid=$_SESSION['id'];
$ret="select * from courses";
$stmt= $mysqli->prepare($ret) ;
//$stmt->bind_param('i',$aid);
$stmt->execute() ;//ok
$res=$stmt->get_result();
$cnt=1;
while($row=$res->fetch_object())
	  {
	  	?>
<tr><td><?php echo $cnt;;?></td>
<td><?php echo $row->course_code;?></td>
<td><?php echo $row->course_sn;?></td>
<td><?php echo $row->course_fn;?></td>
<td><?php echo $row->posting_date;?></td>
<td class="admin-actions-cell"><a href="edit-course.php?id=<?php echo $row->id;?>" class="admin-action-btn admin-action-btn-edit"><i class="fa fa-edit"></i><span>Edit</span></a>
<a href="manage-courses.php?del=<?php echo $row->id;?>" class="admin-action-btn admin-action-btn-delete" onclick="return confirm('Do you want to delete');"><i class="fa fa-close"></i><span>Delete</span></a></td>
										</tr>
									<?php
$cnt=$cnt+1;
									 } ?>
											
										
									</tbody>
								</table>
								</div>

								
							</div>
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

</body>

</html>
