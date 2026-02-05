<?php
	include "modals.php";
?>

<div id="searchcontent">
</div>

<style>
.dropdown {
  position: relative;
  display: inline-block;
}

.dropdown-content {
  display: none;
  position: absolute;
  background-color: white;
  border: 1px solid #ccc;
  z-index: 1;
  padding: 10px;
}

.dropdown:hover .dropdown-content {
  display: block;
}

/* Hide native checkbox */
input[type="checkbox"] {
  display: none;
}

/* Base label style */
label.ajax-labeldraft {
  display: inline-block;
  padding: 5px 11px;
  margin: 1px;
  border: 2px solid #ccc;
  border-radius: 6px;
  background-color: #f8f8f8;
  cursor: pointer;
  transition: all 0.2s ease;
}

/* Highlight label when checkbox is checked */
input[type="checkbox"]:checked + label.ajax-labeldraft {
  background-color: #FF0000;
  color: white;
  border-color: #FF0000;
}
</style>

<!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
		<div class="heading">
			<h1 class="pl-3 pt-2">Drafts</h1>
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
                <h3 class="card-title">Drafted Documents</h3>

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
                      <th>Number</th>
                      <th>Date</th>
					  <th>Proponent</th>
					  <th>Amount</th>
					  <th>Action</th>
                    </tr>
                  </thead>
                  <tbody id="tblviewdrafts">
                <!-- Table rows will be injected here -->
              </tbody>
                </table>
              </div>
              <!-- /.card-body -->
			  <!--
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

    tableId: "#tblviewdrafts",
    url: "backend/bk_draftstable.php",
    request: { request:"viewdrafts", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"] }
  });
  

</script>