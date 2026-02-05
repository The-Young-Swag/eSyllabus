<?php
	date_default_timezone_set('Asia/Manila');
	require "../db/dbconnection.php";
?>

<!-- Main content -->
<section class="content pt-3">

  <div class="container-fluid" id="mainForm">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4><b>Letter</b></h4>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <!-- User Form Start -->

              <div class="row">
			  
                <!-- Left Side: User Details -->
                <div class="col-md-6">
				
				  <div class="form-group" hidden>
                    <label for="doc_document">Document Type</label>
                    <input type="text" class="form-control" id="doc_type" name="doc_type" value="Letter" >
                  </div>
				
                  <div class="form-group">
                    <label for="doc_details">Document Details</label>
                    <input type="text" class="form-control" id="doc_details" name="doc_details" placeholder="Details..." required>
                  </div>
				  
                  <div class="form-group">
                    <label for="doc_date">Document Date</label>
                    <input type="date" class="form-control" id="doc_date" name="doc_date" placeholder="Date..." required>
                  </div>
				  
                </div>

                <!-- Right Side: User Settings -->
                <div class="col-md-6">
				
					<div class="form-group">
						<label for="doc_proponent">Document Proponent</label>
						<input type="text" class="form-control" id="doc_proponent" name="doc_proponent" placeholder="Proponent..." required>
					</div>

                </div>
              </div>
              <div class="form-group">
                <button type="submit" id="doc_review" name="doc_review" class="btn btn-primary">Next</button>
              </div>

            <!-- User Form End -->
          </div>
          <!-- /.card-body -->
        </div>
      </div>
    </div>
  </div>

</section>

<!--=======================================================================================================================================================================-->

<section>

  <div class="container-fluid" id="viewForm" style="display : none;">
    <div class="row">
      <div class="col-12">
        <div class="card">
		
          <div class="card-header">
            <h4><b>Review and Releasing</b></h4>
          </div>

          <!-- /.card-header -->
          <div class="card-body">
            <!-- User Form Start -->

              <div class="row">

                <!-- Left Side: User Details -->
                <div class="col-md-6">
				
				  <div class="form-group">
                    <label for="type">Document Type</label>
                    <div id="view_type"></div>
                  </div>
				
				  <div class="form-group">
                    <label for="details">Document Details</label>
                    <div id="view_details"></div>
                  </div>
				  
				  <div class="form-group">
                    <label for="date">Document Date</label>
                    <div id="view_date"></div>
                  </div>

                </div>

                <!-- Right Side: User Settings -->
                <div class="col-md-6">

				  <div class="form-group">
                    <label for="proponent">Document Proponent</label>
                    <div id="view_proponent"></div>
                  </div>

					<div class="form-group">
						<label for="doc_attachment">Upload File</label>
						<input type="file" class="form-control" id="doc_attachment" name="doc_attachment" placeholder="Choose File..." accept="image/jpeg,image/gif,image/png,application/pdf,image/x-eps">
					</div>

                   <div class="form-group">
					<label for="doc_remarks">Add Remarks</label>
                    <input type="text" class="form-control" id="doc_remarks" name="doc_remarks" placeholder="Remarks..." required>
				   </div>

					<div class="dropdown">
						<label for="destination" class="pr-2">Receiving Department:</label>
							<select class="form-select" aria-label="Default select example" name="" id="send_department">
								<option>Select Department...</option>
									<?php
										$sendTo = execsqlSRS("
										SELECT dept_id, dept_name, dept_desc
										FROM tbl_Departments
										WHERE dept_status = '0'
										ORDER BY dept_name
										", "Select", array());
										foreach ($sendTo as $send) {
													$dept_id = $send['dept_id'];
													$dept_name = $send['dept_name'];
													$dept_desc = $send['dept_desc'];
													echo "<option value = '$dept_id'>$dept_name | $dept_desc</option>";
										}
									?>
							</select>
					</div>
					
					<div class="dropdown" id="show_receiver" style="display: none;">
					<label for="doc_receiver" class="pr-2">Send To:</label>
					<select class="form-select" aria-label="Default select example" id="doc_receiver" name="doc_receiver">
						<option></option>
					</select>
					</div>

                </div>
              </div>
			  
              <div class="form-group">
                <button type="submit" id="doc_previous" name="doc_previous" class="btn btn-danger">Previous</button>
				<button type="submit" id="doc_submit" name="doc_submit" class="btn btn-primary">Send Document</button>
              </div>

            <!-- User Form End -->
          </div>
          <!-- /.card-body -->
        </div>
      </div>
    </div>
  </div>

</section>
<!-- /.content -->