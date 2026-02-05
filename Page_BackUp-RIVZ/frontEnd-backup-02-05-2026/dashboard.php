<?php
include "../db/dbconnection.php";
//include "modals.php";
include "../config/config.php";
$EmailAddress = isset($_POST["EmailAddress"])?$_POST["EmailAddress"]:"";
$RID = isset($_POST["RID"])?$_POST["RID"]:"";
$Name = isset($_POST["Name"])?$_POST["Name"]:"";
$Office = isset($_POST["Office_id"])?$_POST["Office_id"]:""; 
//echo $RID;die;
?>

<!-- View Modal -->
<div class="modal fade" id="welcomemodal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title text-success font-weight-bold" id="exampleModalLabel">System Notice:</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div class="font-weight-bold">
				<div class="ml-3 mr-3">
				<p>Thank you for using DocTrax!</p>
				<p>You can send a Feedback under the Profile tab in the Sidebar - for the System's Improvement.</p>
				<p>Thank you!</p>
				</div>
				<div class="ml-3 mr-3">
				<p class="text-success">v<?php echo $vrs; ?> Changelogs | What's New?</p>
				<span class="text-danger">1. Added: </span><span class="text-success">Auto-Logout when Inactive (60 Minutes).</span><br>
				<span class="text-danger">2. Added: </span><span class="text-success">LoadingSpinner when doing a process.</span><br>
				<span class="text-danger">3. Added: </span><span class="text-success">Enduser and Payee on Searching Parameters.</span><br>
				<span class="text-danger">4. Fixed: </span><span class="text-success">Modal Popup calls showing the wrong Modal.</span><br>
				</div>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<!-- Notif Drop Modal -->
<div class="modal fade" id="notifdrop" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-xl" style="min-width:90%">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title text-danger font-weight-bold" id="exampleModalLabel">Notifications</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div id="notifshow">
			
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>	

<body class="hold-transition sidebar-mini layout-fixed">
	<div class="wrapper">

		<!-- Navbar -->
		<nav class="main-header navbar navbar-expand navbar-light" id="navbardarkmode">
			<!-- Left navbar links -->
			<ul class="navbar-nav">
				<li class="nav-item">
					<a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
				</li>
				<!-- <li class="nav-item d-none d-sm-inline-block">
					<h3>Home</h3>
				</li> -->
				<!-- <li class="nav-item d-none d-sm-inline-block">
					<a href="#" class="nav-link">Contact</a>
				</li> -->
			</ul>

			<!-- Right navbar links -->
			<ul class="navbar-nav ml-auto">
				<!-- Navbar Search -->

				<!-- To-Do
				<li class="nav-item">
					<button class="btn" id="todotask">	
						<i class="nav-icon fas fa-solid fa-list-check fa-2x"></i>
						<span class="badge badge-warning badge-secondary" id="">0</span>
					</button>	
				</li>
				-->

				<!-- Notifications Dropdown Menu -->
				<li class="nav-item">
					<button class="btn" id="notifsee">
						<i class="nav-icon far fa-bell fa-2x text-danger"></i>
						<span class="badge badge-warning badge-secondary" id="notifcount"></span>
					</button>
				</li>
					
					
					
					<li class="nav-item">
						<a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button" hidden>
							<i class="fas fa-th-large"></i>
						</a>
					</li>
				</li>
		</ul>
		<!-- Start Dark Mode -->
		<div class="custom-control custom-switch">
			<input type="checkbox" class="custom-control-input" id="customSwitch1">
			<label class="custom-control-label" for="customSwitch1">Dark Mode</label>
		</div>
		<!-- End Dark Mode-->
		
		
		
	</nav>
	<!-- /.navbar -->
	
<?php include '../page/loading.php' ?>
	<?php include 'sidebar.php'; ?>
	<!-- Content Wrapper. Contains page content -->
	<div class="content-wrapper" id='mainContent'>
		<!-- Content Header (Page header) -->
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Dashboard</h1>
						<?php echo "Hello ". $Name; ?>
					</div><!-- /.col -->
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item"><a href="#">Home</a></li>
							<li class="breadcrumb-item active">Dashboard v1</li>
						</ol>
					</div><!-- /.col -->
				</div><!-- /.row -->
				
				<div id="searchcontent">
				</div>
				
			</div><!-- /.container-fluid -->
		</div>
					<!-- /.content-header -->

					
	</div>
					<!-- /.content-wrapper -->
					
					<footer class="main-footer">
					<strong><?php echo $cpy; ?>.</strong>

					<div class="float-right d-none d-sm-inline-block">
					<b>Version</b> <?php echo $vrs; ?>
					</div>
					</footer>
					
					<!-- Control Sidebar -->
					<aside class="control-sidebar control-sidebar-dark">
					<!-- Control sidebar content goes here -->
					</aside>
					<!-- /.control-sidebar -->
					</div>
					<!-- ./wrapper -->
					
					<!-- jQuery -->
					<script src="plugins/jquery/jquery.min.js"></script>
					<!-- jQuery UI 1.11.4 -->
					<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
					<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
					<script>
					$.widget.bridge('uibutton', $.ui.button)
					</script>
					<!-- Bootstrap 4 -->
					<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
					<!-- ChartJS -->
					<script src="plugins/chart.js/Chart.min.js"></script>
					<!-- Sparkline -->
					<script src="plugins/sparklines/sparkline.js"></script>
					<!-- JQVMap -->
					<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
					<script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
					<!-- jQuery Knob Chart -->
					<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
					<!-- daterangepicker -->
					<script src="plugins/moment/moment.min.js"></script>
					<script src="plugins/daterangepicker/daterangepicker.js"></script>
					<!-- Tempusdominus Bootstrap 4 -->
					<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
					<!-- Summernote -->
					<script src="plugins/summernote/summernote-bs4.min.js"></script>
					<!-- overlayScrollbars -->
					<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
					
					<!-- AdminLTE App -->
					<script src="dist/js/adminlte.js"></script>
					
					
					<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
					<script src="dist/js/pages/dashboard.js"></script>

