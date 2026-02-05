	<?php
	include "../db/dbconnection.php";
	include "modals.php";
?>

<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Document Management</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Dashboard v1</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
	<!-- /.content-header -->
	
	<!-- Main content -->

	<!-- Main content -->
	<?php include 'loading.php' ?>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
			<div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Documents</h3><br>

				<div class="dropdown mt-1">
					<label for="destination">Data Limit:</label>
						<select class="form-select" aria-label="Default select example" id="pagilimit" 
						data-tableid="#tblviewDocs" data-tableurl="backend/bk_docmanagement.php" data-tablereq="viewDocs">
								<?php
									$pagi = execsqlSRS("
									SELECT pagi_id, pagi_number
									FROM tbl_Pagination
									WHERE pagi_status = '0'
									ORDER BY pagi_id
									", "Select", array());
									foreach ($pagi as $pag) {
												$pagi_number = $pag['pagi_number'];
												echo "<option value = '$pagi_number'>$pagi_number</option>";
									}
								?>
						</select>
				</div>

				<div id="loadercount">
					<i class="text-danger">Showing 50 rows...</i>
				</div>

              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive table-bordered p-0" style="max-height:800px;overflow-y:auto">
                <table class="table table-hover table-striped">
                  <thead class="thead-dark">
                    <tr>
                      <th>Tracking</th>
                      <th>Type</th>
                      <th style="width: 280px;">Details</th>
                      <th>Number</th>
					  <th>Date</th>
					  <th>Proponent</th>
					  <th>Amount</th>
					  <th>Active</th>
					  <th>Is Sent</th>
					  <th>Created by</th>		
					  <th>Action</th>					  
                    </tr>
                  </thead>
				<tbody id="tblviewDocs">
                <!-- Table rows will be injected here -->
				</tbody>
                </table>
              </div>
              <!-- /.card-body -->
			  <div class="card-footer clearfix">
                <ul class="pagination pagination-sm m-0 float-right">
                  <li class="page-item"><a class="page-link" href="#">&laquo;</a></li>
                  <li class="page-item"><a class="page-link" href="#">1</a></li>
                  <li class="page-item"><a class="page-link" href="#">2</a></li>
                  <li class="page-item"><a class="page-link" href="#">3</a></li>
                  <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
                </ul>
              </div>
            </div>
			
            <!-- /.card -->
          </div>
        </div>
        <!-- /.row -->
        
        
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->

<script>
  // Load Users into table
  TableLoader2.load2({
    tableId: "#tblviewDocs",
    url: "backend/bk_docmanagement.php",
    request: { request:"viewDocs", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"], pagi_limit:50, },
  });
</script>