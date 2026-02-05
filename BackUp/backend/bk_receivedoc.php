<?php
	date_default_timezone_set('Asia/Manila');
	include "../db/dbconnection.php";
	
$currentDT = date("Y-m-d H:i:s");
$currentD = date("Y-m-d");
$currentT = date("h-i-sa");

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$receive_doc = isset($_POST["receive_doc"]) ? $_POST["receive_doc"] : "";
$user_id = isset($_POST["UserID"]) ? $_POST["UserID"] : "";
$user_name = isset($_POST["UserEmail"]) ? $_POST["UserEmail"] : "";
$user_office = isset($_POST["UserOffice"]) ? $_POST["UserOffice"] : "";

$data = isset($_POST["data"]) ? $_POST["data"] : "";
$editrec_type = isset($_POST["editrec_type"]) ? $_POST["editrec_type"] : "";
$editrec_details = isset($_POST["editrec_details"]) ? $_POST["editrec_details"] : "";
$editrec_number = isset($_POST["editrec_number"]) ? $_POST["editrec_number"] : "";
$editrec_date = isset($_POST["editrec_date"]) ? $_POST["editrec_date"] : "";
$editrec_proponent = isset($_POST["editrec_proponent"]) ? $_POST["editrec_proponent"] : "";
$editrec_amount = isset($_POST["editrec_amount"]) ? $_POST["editrec_amount"] : "";
$u_id = isset($_POST["u_id"]) ? $_POST["u_id"] : "";

$complete_data = isset($_POST["complete_data"]) ? $_POST["complete_data"] : "";
$savecomplete_data = isset($_POST["savecomplete_data"]) ? $_POST["savecomplete_data"] : "";


switch ($request) {

	case "receivedoc":

		$queryrecceivedoc = execsqlSRS("
			UPDATE tbl_Highway
			SET receiver_id = '{$user_id}', dt_received = '{$currentDT}', doc_status = '0'
			WHERE highway_id = '{$receive_doc}'", 
			"Update", []); 

	echo "Document Received.";

	break;

	case "editrecdoc":

		$queryeditreceivedoc = execsqlSRS("
			SELECT doc_type, doc_details, doc_number, doc_date, doc_proponent, doc_amount, tracking_id
			FROM [tbl_Intersection]
			WHERE tracking_id = :data", 
			"Select", [
						":data" => $data, 
					  ]); 
  
foreach ($queryeditreceivedoc as $edit) {

			echo "<div class='form-group'>
					<label for='doc_remarks'>Document Type</label>
                    <input type='text' class='form-control' id='editrec_type' value='" . htmlspecialchars($edit["doc_type"]) . "' disabled>
				  </div>";
			echo "<div class='form-group'>
					<label for='doc_remarks'>Details</label>
                    <input type='text' class='form-control' id='editrec_details' value='" . htmlspecialchars($edit["doc_details"]) . "'>
				  </div>";

			echo "<div class='form-group'>
					<label for='doc_remarks'>Number</label>
                    <input type='text' class='form-control' id='editrec_number' value='" . htmlspecialchars($edit["doc_number"]) . "'>
				  </div>";
			echo "<div class='form-group'>
					<label for='doc_remarks'>Date</label>
                    <input type='date' class='form-control' id='editrec_date' value='" . htmlspecialchars($edit["doc_date"]) . "'>
				  </div>";
			echo "<div class='form-group'>
					<label for='doc_remarks'>Proponent</label>
                    <input type='text' class='form-control' id='editrec_proponent' value='" . htmlspecialchars($edit["doc_proponent"]) . "'>
				  </div>";
			echo "<div class='form-group'>
					<label for='doc_remarks'>Amount</label>
                    <input type='text' class='form-control' id='editrec_amount' value='" . htmlspecialchars($edit["doc_amount"]) . "'>
				  </div>";
				  
			echo    "<div class='form-group d-flex justify-content-center pt-2'>
						<button type='submit' id='editrec_submit' class='btn btn-primary' value='" . htmlspecialchars($edit["tracking_id"]) . "'>
						Save Changes</button>
					  </div>";
			}

	
	break;
	
	case "saverecdoc":
	
		$queryselectdetails = execsqlSRS(
            "SELECT doc_type, doc_details, doc_number, doc_date, doc_proponent, doc_amount
             FROM [tbl_Intersection]
			 WHERE tracking_id = :data",
            "Select", [
						":data" => $data
					  ]);
					  
		$before_details = $queryselectdetails[0]["doc_type"] . "|" . $queryselectdetails[0]["doc_details"] . "|" . $queryselectdetails[0]["doc_number"]
		. "|" . $queryselectdetails[0]["doc_date"] . "|" . $queryselectdetails[0]["doc_proponent"] . "|" . $queryselectdetails[0]["doc_amount"];
		
		$after_details = $editrec_type . "|" . $editrec_details . "|" . $editrec_number . "|" . $editrec_date . "|" . $editrec_proponent . "|" . $editrec_amount;
	
		$queryinsertlogs = execsqlSRS(
            "INSERT INTO [tbl_Logs] (tracking_id, UserID, before_logs, after_logs, dt_updated, status)
             VALUES (:data, :u_id, :before_details, :after_details, :currentDT, 0)",
            "Insert", [
						":data" => $data,
						":u_id" => $u_id,
						":before_details" => $before_details,
						":after_details" => $after_details,
						":currentDT" => $currentDT
					  ]);

		$querysaverecdoc = execsqlSRS("
			UPDATE tbl_Intersection
			SET doc_type = :editrec_type, doc_details = :editrec_details, doc_number = :editrec_number, 
			doc_date = :editrec_date, doc_proponent = :editrec_proponent, doc_amount = :editrec_amount
			
			WHERE tracking_id = :data", 
			"Update", [
						":editrec_type" => $editrec_type,
						":editrec_details" => $editrec_details,
						":editrec_number" => $editrec_number,
						":editrec_date" => $editrec_date,
						":editrec_proponent" => $editrec_proponent,
						":editrec_amount" => $editrec_amount,
						":data" => $data
					  ]); 

	echo "Changes have been saved!";

	break;
	
	case "previewcomplete":

		// Fetch all menu items at once
		$selecttrack = execsqlSRS("
			SELECT tracking_id
			FROM tbl_Highway
			WHERE highway_id = :complete_data", 
			"Select",
			[":complete_data" => $complete_data]
			);
			
		$track = $selecttrack[0]["tracking_id"];
		
		$queryviewdetails = execsqlSRS("
			SELECT doc_type, tracking_id, doc_details, doc_number, doc_date, doc_proponent, doc_amount
			FROM tbl_Intersection 
			WHERE tracking_id = :track", 
			"Select",
			[":track" => $track]
			);

				foreach ($queryviewdetails as $details) {
					
		echo "<div class=''>
					  <div class='card mb-3'>
						<div class='card-body'>
						  <div class='row'>
							<div class='col-sm-3'>
							  <h6 class='mb-0 text-secondary'>Document Type</h6>
							</div>
							<div class='col-sm-9'>
							  ";
		echo					  htmlspecialchars($details["doc_type"]);
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
		echo                      htmlspecialchars($details["tracking_id"]);
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
		echo                      htmlspecialchars($details["doc_details"]);
		echo                    "</strong>
							</div>
						  </div>
						  <hr>
						  <div class='row'>
							<div class='col-sm-3 text-secondary'>
							  <h6 class='mb-0'>Number</h6>
							</div>
							<div class='col-sm-9'>";
		echo                      htmlspecialchars($details["doc_number"]);
		echo                    "</div>
						  </div>
						  <hr>
						  <div class='row'>
							<div class='col-sm-3 text-secondary'>
							  <h6 class='mb-0'>Date</h6>
							</div>
							<div class='col-sm-9'>";
		echo                      htmlspecialchars($details["doc_date"]);
		echo                    "</div>
						  </div>
						  <hr>
						  <div class='row'>
							<div class='col-sm-3 text-secondary'>
							  <h6 class='mb-0'>Proponent</h6>
							</div>
							<div class='col-sm-9'>";
		echo                      htmlspecialchars($details["doc_proponent"]);
		echo                    "</div>
						  </div>
						  <hr>
						  <div class='row'>
							<div class='col-sm-3 text-secondary'>
							  <h6 class='mb-0'>Amount</h6>
							</div>
							<div class='col-sm-9'>";
		echo                      htmlspecialchars($details["doc_amount"]);
		echo                    "</div>
						  </div>
					<div class='d-flex justify-content-center pt-3'>
						<div>
						<p class='font-weight-bold text-danger'>* Note: Marking this Document Completed will break its Process Chain *</p>
						</div>
					</div>
					<div class='d-flex justify-content-center'>
						<div>
						<button type='button' class='btn btn-danger' id='complete_doc' value='" .htmlspecialchars($complete_data) . "'>
							Mark as Completed
						</button>
						</div>
					</div>
						</div>
					  </div>
		</div>";	
						}
				
	break;
	
	case "savecomplete":

		$querysavecomplete = execsqlSRS("
			UPDATE tbl_Highway
			SET doc_status = '4'
			WHERE highway_id = :savecomplete_data", 
			"Update", [
						":savecomplete_data" => $savecomplete_data
					  ]); 

		echo "Marked as Complete!";

	break;
}

?>
