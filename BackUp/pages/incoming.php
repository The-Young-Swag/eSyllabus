<?php
	include "modals.php";
?>

<div id="searchcontent">

</div>

<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
		<div class="heading">
			<h1 class="pl-3 pt-2">Incoming</h1>
		</div>
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->
	
	<!-- Main content -->
	<!-- /Loading Start -->
	<?php //include 'loading.php' ?>
	<!-- /Loading End -->
	
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">List of Incoming Documents</h3>

                <div class="card-tools">
                  <div class="input-group input-group-sm" style="width: 150px;">
                   <!-- <input type="text" name="table_search" class="form-control float-right" placeholder="Search">

                    <div class="input-group-append">
                       <button type="submit" class="btn btn-default">
                        <i class="fas fa-search"></i>
                      </button>
                    </div> -->
                  </div>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive table-bordered p-0 text-center">
                <table class="table table-hover text-center table-striped">
                  <thead class="thead-dark">
                    <tr>
                      <th>Document Type</th>
                      <th>Tracking ID</th>
                      <th style="width: 280px;">Description</th>
                      <th>D/T Released</th>
                      <th>Released By</th>
					  <th>From</th>
					  <th>Remarks</th>
					  <th>Action</th>
                    </tr>
                  </thead>
                  <tbody id="tblviewincoming">
                <!-- Table rows will be injected here -->
              </tbody>
                </table>
              </div>
              <!-- /.card-body -->
			  <!-- Pagination
				<div class="card-footer clearfix">
                <ul class="pagination pagination-sm m-0 float-right">
                  <li class="page-item"><a class="page-link" href="#">&laquo;</a></li>
                  <li class="page-item"><a class="page-link" href="#">1</a></li>
                  <li class="page-item"><a class="page-link" href="#">2</a></li>
                  <li class="page-item"><a class="page-link" href="#">3</a></li>
                  <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
                </ul>
              </div>
			  -->
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

    tableId: "#tblviewincoming",
    url: "backend/bk_incomingtable.php",
    request: { request:"viewincoming", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"] },
  });
</script>