<?php
date_default_timezone_set('Asia/Manila');
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$return_doc = isset($_POST["return_doc"]) ? $_POST["return_doc"] : "";

switch ($request) {

	case "returndoc":

		// Fetch all menu items at once
		$queryviewreturn = execsqlSRS("
			SELECT i.doc_type, i.tracking_id, i.doc_details, i.doc_number, i.doc_date, i.doc_proponent, i.doc_amount, h.highway_id
			FROM tbl_Highway h
			
			LEFT JOIN tbl_Intersection i
			ON h.tracking_id = i.tracking_id
			
			WHERE h.highway_id = ?", 
			"Select",
			[$return_doc]
			);

				foreach ($queryviewreturn as $return) {
					
				echo "<div class='form-group'>
						<label>Document Type: </label>" .
							htmlspecialchars($return["doc_type"])
					  . "</div>";
					  
				echo "<div class='form-group'>
						<label>Tracking ID: </label>" .
							htmlspecialchars($return["tracking_id"])
					  . "</div>";
					  
				echo "<div class='form-group'>
						<label>Details: </label>" .
							htmlspecialchars($return["doc_details"])
					  . "</div>";
					  
				echo "<div class='form-group'>
						<label>Number: </label>" .
							htmlspecialchars($return["doc_number"])
					  . "</div>";
					  
				echo "<div class='form-group'>
						<label>Date: </label>" .
							htmlspecialchars($return["doc_date"])
					  . "</div>";
					  
				echo "<div class='form-group'>
						<label>Proponent: </label>" .
							htmlspecialchars($return["doc_proponent"])
					  . "</div>";

				echo "<div class='form-group'>
						<label>Amount: </label>" .
							htmlspecialchars($return["doc_amount"])
					  . "</div>";		

				echo "<div class='form-group'>
					<label for='doc_remarks'>Add Remarks</label>
                    <input type='text' class='form-control' id='doc_remarks' name='doc_remarks' placeholder='Remarks...' required>
				   </div>";

				echo    "<div class='form-group'>
				<button type='submit' id='doc_return' name='doc_return' class='btn btn-danger' value='" . htmlspecialchars($return["highway_id"]) . "'>Return Document</button>
              </div>";
			}
				
	break;
}

?>