<?php
date_default_timezone_set('Asia/Manila');
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$forward_doc = isset($_POST["forward_doc"]) ? $_POST["forward_doc"] : "";
$selectedoptions = isset($_POST["selectedoptions"]) ? $_POST["selectedoptions"] : "";

switch ($request) {

	case "forwarddoc":

		// Fetch all menu items at once
		$queryviewforward = execsqlSRS("
			SELECT i.doc_type, i.tracking_id, i.doc_details, i.doc_number, i.doc_date, i.doc_proponent, i.doc_amount, i.doc_enduser, i.doc_payee, h.highway_id, h.receiver_office
			FROM tbl_Highway h
			
			LEFT JOIN tbl_Intersection i
			ON h.tracking_id = i.tracking_id
			
			WHERE h.highway_id = ?", 
			"Select",
			[$forward_doc]
			);

				foreach ($queryviewforward as $forward) {
					
echo "<div class=''>
              <div class='card mb-3'>
                <div class='card-body'>
                  <div class='row'>
                    <div class='col-sm-3'>
                      <h6 class='mb-0 text-secondary'>Document Type</h6>
                    </div>
                    <div class='col-sm-9'>
                      ";
echo					  htmlspecialchars($forward["doc_type"]);
echo				  "
                    </div>
                  </div>
                  <hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Tracking</h6>
                    </div>
                    <div class='col-sm-9'>
					<strong>";
echo                      htmlspecialchars($forward["tracking_id"]);
echo                    "</strong>
					</div>
                  </div>
                  <hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Document Details</h6>
                    </div>
                    <div class='col-sm-9'>
					<strong>";
echo                      htmlspecialchars($forward["doc_details"]);
echo                    "</strong>
					</div>
                  </div>
                  <hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Number</h6>
                    </div>
                    <div class='col-sm-9'>";
echo                      htmlspecialchars($forward["doc_number"]);
echo                    "</div>
                  </div>
                  <hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Date</h6>
                    </div>
                    <div class='col-sm-9'>";
echo                      htmlspecialchars($forward["doc_date"]);
echo                    "</div>
                  </div>
                  <hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Proponent</h6>
                    </div>
                    <div class='col-sm-9'>";
echo                      htmlspecialchars($forward["doc_proponent"]);
echo                    "</div>
                  </div>
                  <hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Amount</h6>
                    </div>
                    <div class='col-sm-9'>";
echo                      htmlspecialchars($forward["doc_amount"]);
echo                    "</div>
                  </div>
                  <hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>End-User</h6>
                    </div>
                    <div class='col-sm-9'>";
echo                      htmlspecialchars($forward["doc_enduser"]);
echo                    "</div>
                  </div>
                  <hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Payee</h6>
                    </div>
                    <div class='col-sm-9'>";
echo                      htmlspecialchars($forward["doc_payee"]);
echo                    "</div>
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
					
					
echo "<div id='forwardmany'>

	<div class='dropdown' style='display: flex;'>
		<label class='mr-2'>Send to: </label><button class='btn btn-success'>Click here to select Recipients</button>
		<div class='dropdown-content' id='forwardmanyhover' data-highwayid='" . htmlspecialchars($forward["highway_id"]) . "' 
		data-trackingid='" . htmlspecialchars($forward["tracking_id"]) . "'>

<button type='button' class='btn btn-outline-danger mb-1' id='selectAllBtn_forward'>Select All</button>
<button type='button' class='btn btn-outline-danger mb-1' id='deselectAllBtn_forward'>Deselect All</button>
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
								class='ajax-optionforward' 
								id='select_unitforward_" . htmlspecialchars($unit_id) . "' 
								value='" . htmlspecialchars($unit_id) . "'>
						<label for='select_unitforward_" . htmlspecialchars($unit_id) . "' 
						class='ajax-labelforward'>" . htmlspecialchars($unit_desc) . "</label>";
			}
			
echo		"</div>
	</div>
	
<div id='result_selectedforward' class='mt-3'></div>	
		
</div>";			
			}

	break;
	

	case "forwardselected":
	
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
						id = 'doc_forward'
						type = 'submit'
						value = '" . htmlspecialchars($implodedselect) . "'>Send to " . htmlspecialchars(count($selectedoptions)) . " Unit/s</button></div>";
		
	}

	break;
}

?>

<script>

document.getElementById('selectAllBtn_forward').addEventListener('click', () => {
  document.querySelectorAll('.ajax-optionforward').forEach(cb => cb.checked = true);
});

document.getElementById('deselectAllBtn_forward').addEventListener('click', () => {
  document.querySelectorAll('.ajax-optionforward').forEach(cb => cb.checked = false);
});

</script>