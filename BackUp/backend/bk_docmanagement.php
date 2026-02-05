<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : "";
$user_name = isset($_POST["user_name"]) ? $_POST["user_name"] : "";
$user_office = isset($_POST["user_office"]) ? $_POST["user_office"] : "";
$pagi_limit = isset($_POST["pagi_limit"]) ? $_POST["pagi_limit"] : "";

$limit = intval($pagi_limit);

$data = isset($_POST["data"]) ? $_POST["data"] : "";

$savedata = isset($_POST["savedata"]) ? $_POST["savedata"] : "";
$de_desc = isset($_POST["de_desc"]) ? $_POST["de_desc"] : "";
$de_number = isset($_POST["de_number"]) ? $_POST["de_number"] : "";
$de_date = isset($_POST["de_date"]) ? $_POST["de_date"] : "";
$de_proponent = isset($_POST["de_proponent"]) ? $_POST["de_proponent"] : "";
$de_amount = isset($_POST["de_amount"]) ? $_POST["de_amount"] : "";
$de_status = isset($_POST["de_status"]) ? $_POST["de_status"] : "";
$de_issent = isset($_POST["de_issent"]) ? $_POST["de_issent"] : "";


switch ($request) {

	case "viewDocs":

		// Fetch all menu items at once
		 $queryviewdocs = execsqlSRS("
			SELECT i.tracking_id, i.doc_type, i.doc_details, i.doc_number, i.doc_date, i.doc_proponent, i.doc_amount, i.doc_status, i.doc_issent, u.Name
			FROM tbl_Intersection i
			
			LEFT JOIN Sys_UserAccount u
			ON u.UserID = i.UserID
			
			ORDER BY tracking_id DESC
			OFFSET 0 ROWS
			FETCH NEXT $limit ROWS ONLY",
			"Select",
			array()
			);

			foreach ($queryviewdocs as $docs) {

				echo "<tr>";
				echo "<td>" . htmlspecialchars($docs["tracking_id"]) . "</td>";
				echo "<td>" . htmlspecialchars($docs["doc_type"]) . "</td>";
				echo "<td>" . htmlspecialchars($docs["doc_details"]) . "</td>";
				echo "<td>" . htmlspecialchars($docs["doc_number"]) . "</td>";
				echo "<td>" . htmlspecialchars($docs["doc_date"]) . "</td>";
				echo "<td>" . htmlspecialchars($docs["doc_proponent"]) . "</td>";
				echo "<td>" . htmlspecialchars($docs["doc_amount"]) . "</td>";
				echo "<td>" . htmlspecialchars($docs["doc_status"]) . "</td>";
				echo "<td>" . htmlspecialchars($docs["doc_issent"]) . "</td>";
				echo "<td>" . htmlspecialchars($docs["Name"]) . "</td>";
				echo "<td>
						<button type='button' class='btn btn-info m-1' id='track_doc' value='" . htmlspecialchars($docs["tracking_id"]) . "'>
							Track
						</button>
						<button type='button' class='btn btn-warning m-1' id='edit_doc' value='" . htmlspecialchars($docs["tracking_id"]) . "'>
							Edit
						</button>
						</td>";
			} 
	break;
	
	case "editdoc":

		// Fetch all menu items at once
		 $queryeditdoc = execsqlSRS("
			SELECT i.tracking_id, i.doc_type, i.doc_details, i.doc_number, i.doc_date, i.doc_proponent, i.doc_amount, i.doc_status, 
			i.doc_issent, u.Name, i.doc_status, i.doc_issent
			FROM tbl_Intersection i
			
			LEFT JOIN Sys_UserAccount u
			ON u.UserID = i.UserID
			
			WHERE i.tracking_id = :data",
			"Select", [
					":data" => $data
			]);

				foreach ($queryeditdoc as $edit) {

echo "<div class=''>
              <div class='card mb-3'>
                <div class='card-body'>
                  <div class='row'>
                    <div class='col-sm-3'>
                      <h6 class='mb-0 text-secondary'>Document Type</h6>
                    </div>
                    <div class='col-sm-9'>
                      <input type='text' class='form-control text-success font-weight-bold' id='de_type' value='" . htmlspecialchars($edit["doc_type"]) . "' disabled>
                    </div>
                  </div>";

echo                  "<hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Tracking</h6>
                    </div>
                    <div class='col-sm-9'>
					<strong>
						<input type='text' class='form-control text-danger font-weight-bold' id='de_id' value='" . htmlspecialchars($edit["tracking_id"]) . "' disabled>
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
						<input type='text' class='form-control font-weight-bold' id='de_desc' value='" . htmlspecialchars($edit["doc_details"]) . "'>
						</strong>
					</div>
                  </div>";
				  
echo                  "<hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Number</h6>
                    </div>
                    <div class='col-sm-9'>
						<input type='text' class='form-control font-weight-bold' id='de_number' value='" . htmlspecialchars($edit["doc_number"]) . "'>
                    </div>
                  </div>";
				  
echo                  "<hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Date</h6>
                    </div>
                    <div class='col-sm-9'>
						<input type='date' class='form-control font-weight-bold' id='de_date' value='" . htmlspecialchars($edit["doc_date"]) . "'>
                    </div>
                  </div>";

echo                  "<hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Proponent</h6>
                    </div>
                    <div class='col-sm-9'>
						<input type='text' class='form-control font-weight-bold' id='de_proponent' value='" . htmlspecialchars($edit["doc_proponent"]) . "'>
                    </div>
                  </div>";

echo                  "<hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Amount</h6>
                    </div>
                    <div class='col-sm-9'>
						<input type='text' class='form-control font-weight-bold' id='de_amount' value='" . htmlspecialchars($edit["doc_amount"]) . "'>
                    </div>
                  </div>";

echo                  "<hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Active</h6>
                    </div>
                    <div class='col-sm-9'>
						<input type='text' class='form-control font-weight-bold' id='de_status' value='" . htmlspecialchars($edit["doc_status"]) . "'>
                    </div>
                  </div>";
				  
echo                  "<hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Is Sent</h6>
                    </div>
                    <div class='col-sm-9'>
						<input type='text' class='form-control font-weight-bold' id='de_issent' value='" . htmlspecialchars($edit["doc_issent"]) . "'>
                    </div>
                  </div>
    
                </div>
              </div>
</div>";	

				echo    "<div class='form-group d-flex justify-content-center pt-2'>
				<button type='submit' id='save_doc' name='save_doc' class='btn btn-primary' value='" . htmlspecialchars($edit["tracking_id"]) . "'>Save Changes</button>
              </div>";

			}

	break;
	
	case "savedoc":

		// Fetch all menu items at once
		 $querysavedoc = execsqlSRS("
			UPDATE tbl_Intersection
			SET 	doc_details = :de_desc,
					doc_number = :de_number,
					doc_date = :de_date,
					doc_proponent = :de_proponent,
					doc_amount = :de_amount,
					doc_status = :de_status,
					doc_issent = :de_issent
			WHERE tracking_id = :savedata",
			"Update",
					  [
						":de_desc" => $de_desc,
						":de_number" => $de_number,
						":de_date" => $de_date,
						":de_proponent" => $de_proponent,
						":de_amount" => $de_amount,
						":de_status" => $de_status,
						":de_issent" => $de_issent,
						":savedata" => $savedata
					  ]);	
		
		echo "Changes have been Saved!";

	break;

	case "viewupdatelogs":
		$queryviewupdatelogs = execsqlSRS("
			SELECT l.tracking_id, u.Name, l.before_logs, l.after_logs, l.dt_updated
			FROM tbl_Logs l 
			
			LEFT JOIN Sys_UserAccount u
			ON u.UserID = l.UserID
					
			ORDER BY l.logs_id DESC", 
			"Select", 
			array()
		);

		foreach ($queryviewupdatelogs as $logs) {
			echo "<tr>";
			echo "<td>" . htmlspecialchars($logs["tracking_id"]) . "</td>";
			echo "<td>" . htmlspecialchars($logs["Name"]) . "</td>";
			echo "<td>" . htmlspecialchars($logs["before_logs"]) . "</td>";
			echo "<td>" . htmlspecialchars($logs["after_logs"]) . "</td>";
			echo "<td>" . htmlspecialchars($logs["dt_updated"]) . "</td>";
			echo "</tr>";
		}
		break;
}