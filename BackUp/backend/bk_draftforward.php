<?php
date_default_timezone_set('Asia/Manila');
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$data = isset($_POST["data"]) ? $_POST["data"] : "";
$selectedoptions = isset($_POST["selectedoptions"]) ? $_POST["selectedoptions"] : "";

switch ($request) {

	case "draftforward":

		// Fetch all menu items at once
		$queryviewdraft = execsqlSRS("
			SELECT doc_type, tracking_id, doc_details, doc_number, doc_date, doc_proponent, doc_amount
			FROM tbl_Intersection 
			
			WHERE tracking_id = :tracking_id", 
			"Select",
						[
							":tracking_id" => $data
						]
			);

				foreach ($queryviewdraft as $draft) {

echo "<div class=''>
              <div class='card mb-3'>
                <div class='card-body'>
                  <div class='row'>
                    <div class='col-sm-3'>
                      <h6 class='mb-0 text-secondary'>Document Type</h6>
                    </div>
                    <div class='col-sm-9'>
                      <input type='text' class='form-control text-success font-weight-bold' id='foredit_type' value='" . htmlspecialchars($draft["doc_type"]) . "' disabled>
                    </div>
                  </div>";

echo                  "<hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Tracking</h6>
                    </div>
                    <div class='col-sm-9'>
					<strong>
						<input type='text' class='form-control text-danger font-weight-bold' id='foredit_id' value='" . htmlspecialchars($draft["tracking_id"]) . "' disabled>
                    </strong>
					</div>
                  </div>";

echo                  "<hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Document Details</h6>
                    </div>
                    <div class='col-sm-9'>
					<strong>
						<input type='text' class='form-control font-weight-bold' id='foredit_desc' value='" . htmlspecialchars($draft["doc_details"]) . "'>
						</strong>
					</div>
                  </div>";
				  
echo                  "<hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Number</h6>
                    </div>
                    <div class='col-sm-9'>
						<input type='text' class='form-control font-weight-bold' id='foredit_number' value='" . htmlspecialchars($draft["doc_number"]) . "'>
                    </div>
                  </div>";
				  
echo                  "<hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Date</h6>
                    </div>
                    <div class='col-sm-9'>
						<input type='date' class='form-control font-weight-bold' id='foredit_date' value='" . htmlspecialchars($draft["doc_date"]) . "'>
                    </div>
                  </div>";

echo                  "<hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Proponent</h6>
                    </div>
                    <div class='col-sm-9'>
						<input type='text' class='form-control font-weight-bold' id='foredit_proponent' value='" . htmlspecialchars($draft["doc_proponent"]) . "'>
                    </div>
                  </div>";

echo                  "<hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Amount</h6>
                    </div>
                    <div class='col-sm-9'>
						<input type='text' class='form-control font-weight-bold' id='foredit_amount' value='" . htmlspecialchars($draft["doc_amount"]) . "'>
                    </div>
                  </div>
                 
                </div>
              </div>
</div>";	

				echo "<div class='form-group'>
						<label for='doc_attachment'>Upload File</label>
						<input type='file' class='form-control' id='doc_attachment' name='doc_attachment' placeholder='Choose File...' accept='image/jpeg,image/gif,image/png,application/pdf,image/x-eps'>
					</div>";	

				echo "<div class='form-group'>
					<label for='doc_remarks'>Add Remarks</label>
                    <input type='text' class='form-control' id='doc_remarks' name='doc_remarks' placeholder='Remarks...' required>
				   </div>";
				   
echo "<div id='draftforwardmany'>

	<div class='dropdown' style='display: flex;'>
		<label class='mr-2'>Send to: </label><button class='btn btn-success'>Click here to select Recipients</button>
		<div class='dropdown-content' id='draftforwardmanyhover' data-trackingid='" . htmlspecialchars($draft["tracking_id"]) . "'>

<button type='button' class='btn btn-outline-danger mb-1' id='selectAllBtn_draft'>Select All</button>
<button type='button' class='btn btn-outline-danger mb-1' id='deselectAllBtn_draft'>Deselect All</button>
<br>";

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
								class='ajax-optiondraft' 
								id='select_unitdraft_" . htmlspecialchars($unit_id) . "' 
								value='" . htmlspecialchars($unit_id) . "'>
						<label for='select_unitdraft_" . htmlspecialchars($unit_id) . "' 
						class='ajax-labeldraft'>" . htmlspecialchars($unit_desc) . "</label>";
			}
			
echo		"</div>
	</div>
	
<div id='result_selecteddraft' class='mt-3'></div>	
		
</div>";	



echo 		"<div class='modal-footer'>
				<button type='button' id='savedraft' class='btn btn-success' data-dismiss='modal' value='" . htmlspecialchars($draft["tracking_id"]) . "'>Save &amp; Close</button>
			</div>";		

			}

	break;

	case "draftforwardselected":
	
	if (!$selectedoptions){
		echo "Please pick Units to send this document to...";
	}
	
	else {
		echo "<b>Selected Units: </b>";
		
		foreach ($selectedoptions as $options) {

			// Fetch all menu items at once
			 $queryselected = execsqlSRS("
				SELECT unit_name, unit_desc, unit_id
				FROM tbl_Units
				
				WHERE unit_status = '0' AND unit_id = :options",
				"Select", [
							":options" => $options
						  ]
				);
				
					foreach ($queryselected as $selected) {
						
						echo "<b class='text-danger'>". htmlspecialchars($selected["unit_desc"]) . ", </b>";
					}
		}

		$implodedselect = implode(", ", $selectedoptions);

		echo "<div><button 
						class = 'btn btn-success mt-3'
						id = 'draft_forward'
						type = 'submit'
						value = '" . htmlspecialchars($implodedselect) . "'>Send to " . htmlspecialchars(count($selectedoptions)) . " Unit/s</button></div>";
	}
}

?>

<script>

 document.getElementById('selectAllBtn_draft').addEventListener('click', () => {
  document.querySelectorAll('.ajax-optiondraft').forEach(cb => cb.checked = true);
});

document.getElementById('deselectAllBtn_draft').addEventListener('click', () => {
  document.querySelectorAll('.ajax-optiondraft').forEach(cb => cb.checked = false);
});

</script>