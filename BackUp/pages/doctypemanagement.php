<?php
	include "../db/dbconnection.php";
	include "modals.php";
?>

<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">DocType Management</h1>
			<ol class="breadcrumb float-sm-left">
			  <button type="button" class="btn btn-primary" id='adddoctype'>
				  Add DocType
				</button>
			</ol>
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
                <h3 class="card-title">List of Document Types</h3>

                <div class="card-tools">
                  <div class="input-group input-group-sm" style="width: 150px;">
                    <input type="text" name="table_search" class="form-control float-right" placeholder="Search">

                    <div class="input-group-append">
                      <button type="submit" class="btn btn-default">
                        <i class="fas fa-search"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive table-bordered p-0">
                <table class="table table-hover text-nowrap">
                  <thead>
                    <tr>
                      <th>Type ID</th>
                      <th>Type Details</th>
                      <th>Status</th>
                      <th>Link</th>	
					  <th>Action</th>					  
                    </tr>
                  </thead>
				<tbody id="tblviewTypes">
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
  // Load Menus into table
  TableLoader.load({
    tableId: "#tblviewTypes",
    url: "backend/bk_doctypemanagement.php",
    request: "viewTypes"
  });
</script>