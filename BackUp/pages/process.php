<?php 
	date_default_timezone_set('Asia/Manila');
	require "../db/dbconnection.php";
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
label.ajax-labelprocess {
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
input[type="checkbox"]:checked + label.ajax-labelprocess {
  background-color: #FF0000;
  color: white;
  border-color: #FF0000;
}
</style>

<div class="heading">
	<h1 class="pl-3 pt-2">Start a Process</h1>
</div>

<div class="dropdown pt-2 pl-3 d-flex justify-content-center">
	<label for="destination" class="pr-2" id="dtypelabel">Select Document Type:</label>
		<select class="form-select" aria-label="Default select example" id="dtype">
				<option value="">Select a Type...</option>
				<hr>
				<?php
					$docType = execsqlSRS("
					SELECT document_id, document_details
					FROM tbl_DocumentType
					WHERE document_status = '0'
                    ", "Select", array());
					foreach ($docType as $row) {
							
					            $docName = $row['document_details'];
                                $docID = $row['document_id'];
                                echo "<option value = '$docID'>$docName </option>";
					}
				?>
		</select>
</div>

<!-- Main content -->
<section class="content pt-3">

  <div class="container-fluid" id="mainForm">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4><b>Document Form</b></h4>
			<button id="doc_endusershow" class="btn btn-success">Add End-User</button>
			<button id="doc_payeeshow" class="btn btn-success">Add Payee</button>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <!-- User Form Start -->

              <div class="row">

                <!-- Left Side: User Details -->
                <div class="col-md-6">

				  <div class="form-group">
                    <label for="doc_document">Document Type <span class="text-danger">*Required Field*</span></label>
                    <input type="text" class="form-control font-weight-bold text-success" id="doc_type" placeholder="Please Select a Type First..." disabled>
                  </div>

                  <div class="form-group">
                    <label for="doc_details">Document Details <span class="text-danger">*Required Field*</span></label>
                    <input type="text" class="form-control" id="doc_details" placeholder="Details...">
                  </div>
				  
                  <div class="form-group">
                    <label for="doc_date">Document Date</label>
                    <input type="date" class="form-control" data-dateinput="doc_datedate" id="doc_date" placeholder="Date...">
                  </div>

				  <div class="form-group">
                    <label for="doc_number">Document Number</label>
                    <input type="text" class="form-control" id="doc_number" placeholder="Number...">
                  </div>
				  
                </div>

                <!-- Right Side: User Settings -->
                <div class="col-md-6">

                  <div class="form-group">
                    <label for="doc_amount">Amount</label>
                    <input type="text" class="form-control" id="doc_amount" placeholder="Amount...">
                  </div>

					<div class="form-group">
						<label for="doc_proponent">Document Proponent</label>
						<input type="text" class="form-control" id="doc_proponent" placeholder="Proponent...">
					</div>

					<div class="form-group">
	
						<div style="display: none;" id="enduserpanel">
						<label for="doc_enduser">End-User</label>
						<input type="text" class="form-control" id="doc_enduser" placeholder="End-User...">
						</div>
					</div>

					<div class="form-group">
						
						<div style="display: none;" id="payeepanel">
						<label for="doc_payee">Payee</label>
						<input type="text" class="form-control" id="doc_payee" placeholder="Payee...">
						</div>
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

				  <div class="form-group">
                    <label for="number">Document Number</label>
                    <div id="view_number"></div>
                  </div>

				  <div class="form-group">
                    <label for="amount">Amount</label>
                    <div id="view_amount"></div>
                  </div>
				  
				  <div class="form-group">
                    <label for="proponent">Document Proponent</label>
                    <div id="view_proponent"></div>
                  </div>

				  <div class="form-group">
                    <label for="proponent">Document End-User</label>
                    <div id="view_enduser"></div>
                  </div>

				  <div class="form-group">
                    <label for="proponent">Document Payee</label>
                    <div id="view_payee"></div>
                  </div>

                </div>

                <!-- Right Side: User Settings -->
                <div class="col-md-6">

					<div class="form-group">
						<label for="doc_attachment">Upload File</label>
						<input type="file" class="form-control" id="doc_attachment" name="doc_attachment" placeholder="Choose File..." accept="image/jpeg,image/gif,image/png,application/pdf,image/x-eps">
					</div>

                   <div class="form-group">
					<label for="doc_remarks">Add Remarks</label>
                    <input type="text" class="form-control" id="doc_remarks" name="doc_remarks" placeholder="Remarks..." required>
				   </div>
				   

<!-- Send to Many Units ========================================================================================================================== -->
				   
<div id="sendmany">

	<div class="dropdown" style="display: flex;">
		<label class="mr-2">Send to: </label><button class="btn btn-success">Click here to select Recipients</button>
		<div class="dropdown-content" id="sendmanyhover">

<button type="button" class="btn btn-outline-danger mb-1" id="selectAllBtn_process">Select All</button>
<button type="button" class="btn btn-outline-danger mb-1" id="deselectAllBtn_process">Deselect All</button>
<br>

		<?php
			$sendTo1 = execsqlSRS("
			SELECT unit_id, unit_name, unit_desc
			FROM tbl_Units
			WHERE unit_status = '0'
			ORDER BY unit_desc
			", "Select", array());
			foreach ($sendTo1 as $send) {
						$unit_id = $send['unit_id'];
						$unit_name = $send['unit_name'];
						$unit_desc = $send['unit_desc'];
						echo "<input type='checkbox' 
								class='ajax-optionprocess' 
								id='select_unitprocess_" . htmlspecialchars($unit_id) . "' 
								value='" . htmlspecialchars($unit_id) . "'>
						<label for='select_unitprocess_" . htmlspecialchars($unit_id) . "' 
						class='ajax-labelprocess'>" . htmlspecialchars($unit_desc) . "</label>";
			}
		?>
		</div>
	</div>

<div id="result_selected" class="mt-3"></div>	
	
</div>

<!-- ================================================================================================================================================ -->



			</div>
			</div>
			  


              <div class="form-group">
                <button type="submit" id="doc_previous" name="doc_previous" class="btn btn-danger">Previous</button>
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

<script>
document.getElementById('selectAllBtn_process').addEventListener('click', () => {
  document.querySelectorAll('.ajax-optionprocess').forEach(cb => cb.checked = true);
});

document.getElementById('deselectAllBtn_process').addEventListener('click', () => {
  document.querySelectorAll('.ajax-optionprocess').forEach(cb => cb.checked = false);
});
</script>

