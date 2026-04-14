<?php
session_start();
include('includes/config.php');
include('includes/checklogin.php');
include('../includes/booking.php');
check_login();

if(isset($_GET['renew']))
{
    $id=intval($_GET['renew']);
    $adn="UPDATE registration SET duration = duration + 1, renewal_count = renewal_count + 1 WHERE id=?";
    $stmt= $mysqli->prepare($adn);
    $stmt->bind_param('i',$id);
    $stmt->execute();
    $stmt->close();	   
    echo "<script>alert('Student renewed successfully by 1 month'); window.location.href='manage-students.php';</script>" ;
    exit;
}

if(isset($_GET['del']))
{
	$id=intval($_GET['del']);
	$adn="delete from registration where id=?";
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
	<title>Manage Rooms</title>
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
	<link rel="stylesheet" href="css/bootstrap-social.css">
	<link rel="stylesheet" href="css/bootstrap-select.css">
	<link rel="stylesheet" href="css/fileinput.min.css">
	<link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
	<link rel="stylesheet" href="css/style.css">
<script language="javascript" type="text/javascript">
var popUpWin=0;
function popUpWindow(URLStr, left, top, width, height)
{
 if(popUpWin)
{
if(!popUpWin.closed) popUpWin.close();
}
popUpWin = open(URLStr,'popUpWin', 'toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,copyhistory=yes,width='+510+',height='+430+',left='+left+', top='+top+',screenX='+left+',screenY='+top+'');
}
</script>

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
								<span class="admin-page-kicker">Student Records</span>
								<h2 class="page-title">Manage Registered Students</h2>
								<p class="admin-page-subtitle">Review hostel student allocations, contact details, and registration records in a clearer, more usable table view.</p>
							</div>
							<div class="admin-page-actions">
								<a href="registration.php" class="btn admin-btn admin-btn-primary">
									<i class="fa fa-user-plus"></i>
									<span>Add Student</span>
								</a>
							</div>
						</div>

						<div class="panel panel-default admin-table-card">
							<div class="panel-heading admin-table-card-head">
								<div>
									<h3 class="admin-section-title">All Student Records</h3>
									<p class="admin-section-subtitle">View details or remove records using the same existing management actions.</p>
								</div>
							</div>
							<div class="panel-body">
								<div class="table-responsive admin-table-wrap">
								<table id="zctb" class="display table admin-data-table" cellspacing="0" width="100%">
									<thead>
										<tr>
											<th>Sno.</th>
											<th>Student Name</th>
											<th>Reg no</th>
											<th>Contact no </th>
											<th>Room no  </th>
											<th>Seater </th>
											<th>Staying From </th>
											<th>Status</th>
											<th>Action</th>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<th>Sno.</th>
											<th>Student Name</th>
											<th>Reg no</th>
											<th>Contact no </th>
											<th>Room no  </th>
											<th>Seater </th>
											<th>Staying From </th>
											<th>Status</th>
											<th>Action</th>
										</tr>
									</tfoot>
									<tbody>
<?php	
$aid=$_SESSION['id'];
$ret="select * from registration";
$stmt= $mysqli->prepare($ret) ;
//$stmt->bind_param('i',$aid);
$stmt->execute() ;//ok
$res=$stmt->get_result();
$cnt=1;
while($row=$res->fetch_object())
	  {
	  	?>
<tr><td><?php echo $cnt;;?></td>
<td><?php echo $row->firstName;?><?php echo $row->middleName;?><?php echo $row->lastName;?></td>
<td><?php echo $row->regno;?></td>
<td><?php echo $row->contactno;?></td>
<td><?php echo $row->roomno;?></td>
<td><?php echo $row->seater;?></td>
<td><?php echo $row->stayfrom;?></td>
<td>
    <?php
    $timeline = hms_build_booking_timeline((array)$row);
    $statusLabel = htmlspecialchars($timeline['booking_status_label']);
    $statusKey = $timeline['occupancy_status'];
    $badgeClass = '';
    if ($statusKey === 'active') {
        $badgeClass = 'label label-success';
    } elseif ($statusKey === 'expiring_soon') {
        $badgeClass = 'label label-warning';
    } elseif ($statusKey === 'expired') {
        $badgeClass = 'label label-danger';
    } else {
        $badgeClass = 'label label-default';
    }
    echo "<span class=\"$badgeClass\">$statusLabel</span>";
    
    if ($timeline['renewal_count'] > 0) {
        echo "<br><small class='text-muted'>Renewals: " . (int)$timeline['renewal_count'] . "</small>";
    }
    ?>
</td>
<td class="admin-actions-cell">
<a href="manage-students.php?renew=<?php echo $row->id;?>" class="admin-action-btn admin-action-btn-edit" title="Renew Room" onclick="return confirm('Renew this room for 1 more month?');"><i class="fa fa-refresh"></i><span>Renew</span></a>
<a href="javascript:void(0);" class="admin-action-btn admin-action-btn-view" onClick="popUpWindow('http://localhost/hostel/admin/full-profile.php?id=<?php echo $row->id;?>');" title="View Full Details"><i class="fa fa-desktop"></i><span>View</span></a>
<a href="manage-students.php?del=<?php echo $row->id;?>" class="admin-action-btn admin-action-btn-delete" title="Delete Record" onclick="return confirm('Do you want to delete');"><i class="fa fa-close"></i><span>Delete</span></a></td>
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
