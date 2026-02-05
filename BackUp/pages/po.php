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
	<h1 class="pl-3 pt-2">Start a Process | Purchase Order</h1>
</div>

<!-- Main content -->
<section class="content pt-3">

<div class="card">
          <div class="card-header p-2">
            <ul class="nav nav-pills">
              <li class="nav-item">
                <a class="nav-link active" href="#mainForm" data-toggle="tab">Purchase Request Form</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#potable" data-toggle="tab">All PO Transactions</a>
              </li>
            </ul>
          </div>
		  
<div class="card-body">
<div class="tab-content">

  <div class="tab-pane active container-fluid" id="mainForm">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            <h4><b>Purchase Order</b></h4>
			<button id="doc_endusershow" class="btn btn-success">Add End-User</button>
			<button id="doc_payeeshow" class="btn btn-success">Add Payee</button>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <!-- User Form Start -->

              <div class="row">
			  
                <!-- Left Side: User Details -->
                <div class="col-md-6">
				
				  <div class="form-group" hidden>
                    <label for="doc_document">Document Type</label>
                    <input type="text" class="form-control" id="doc_type" name="doc_type" value="Purchase Order" >
                  </div>
				
                  <div class="form-group">
                    <label for="doc_details">Document Details <span class="text-danger">*Required Field*</span></label>
                    <input type="text" class="form-control" id="doc_details" name="doc_details" placeholder="Details..." required>
                  </div>
				  
                  <div class="form-group">
                    <label for="doc_date">Document Date</label>
                    <input type="date" class="form-control" id="doc_date" name="doc_date" placeholder="Date..." required>
                  </div>
				  
				  <div class="form-group">
                    <label for="doc_number">Document Number</label>
                    <input type="text" class="form-control" id="doc_number" name="doc_number" placeholder="Number..." required>
                  </div>
				  
                </div>

                <!-- Right Side: User Settings -->
                <div class="col-md-6">
				
                  <div class="form-group">
                    <label for="doc_amount">Amount</label>
                    <input type="text" class="form-control" id="doc_amount" name="doc_amount" placeholder="Amount..." required>
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
                    <label for="amount">End-User</label>
                    <div id="view_enduser"></div>
                  </div>

				  <div class="form-group">
                    <label for="amount">Payee</label>
                    <div id="view_payee"></div>
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

               <!-- Feedback Table -->
			   <div class="tab-pane" id="potable">
                <div class="card">
                  <div class="card-header">
                    <h3 class="card-title">Purchase Order List</h3><br>
					
				<div class="dropdown mt-1">
					<label for="destination">Data Limit:</label>
						<select class="form-select" aria-label="Default select example" id="pagilimit" 
						data-tableid="#tblviewpo" data-tableurl="backend/bk_transactionstable.php" data-tablereq="viewpo">
								<?php
									$pagi = execsqlSRS("
									SELECT pagi_id, pagi_number
									FROM tbl_Pagination
									WHERE pagi_status = '0' AND pagi_cond = '0'
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
					
                    <div class="card-tools">
                      <div class="input-group input-group-sm" style="width: 150px;">
                      </div>
                    </div>
                  </div>

                  <div class="card-body table-responsive table-bordered p-0" style="max-height:800px;overflow-y:auto">
                    <table class="table table-hover table-striped">
                      <thead class="thead-dark">
                        <tr>
                          <th>Document Type</th>
                          <th>Tracking</th>
                          <th>Description</th>
                          <th>Number</th>
                          <th>Date</th>
						  <th>Amount</th>
						  <th>Action</th>
                        </tr>
                      </thead>
                      <tbody id="tblviewpo"></tbody>
                        <!-- Active roles data -->
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
			  
</div>
</div>

</section>
<!-- /.content -->

<script>
  // Load Users into table
  TableLoader2.load2({

    tableId: "#tblviewpo",
    url: "backend/bk_transactionstable.php",
    request: { request:"viewpo", user_id: UserInfo["UserID"], user_name: UserInfo["Name"], user_office: UserInfo["Office_id"], pagi_limit:50 },
  });
  
 document.getElementById('selectAllBtn_process').addEventListener('click', () => {
  document.querySelectorAll('.ajax-optionprocess').forEach(cb => cb.checked = true);
});

document.getElementById('deselectAllBtn_process').addEventListener('click', () => {
  document.querySelectorAll('.ajax-optionprocess').forEach(cb => cb.checked = false);
});
</script>