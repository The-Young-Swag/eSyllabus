<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : "";
$user_name = isset($_POST["user_name"]) ? $_POST["user_name"] : "";
$user_office = isset($_POST["user_office"]) ? $_POST["user_office"] : "";

$type_id = isset($_POST["type_id"]) ? $_POST["type_id"] : "";
$type_details = isset($_POST["type_details"]) ? $_POST["type_details"] : "";
$type_status = isset($_POST["type_status"]) ? $_POST["type_status"] : "";
$type_link = isset($_POST["type_link"]) ? $_POST["type_link"] : "";

$editdata = isset($_POST["editdata"]) ? $_POST["editdata"] : "";

$savedata = isset($_POST["savedata"]) ? $_POST["savedata"] : "";
$te_details = isset($_POST["te_details"]) ? $_POST["te_details"] : "";
$te_status = isset($_POST["te_status"]) ? $_POST["te_status"] : "";
$te_link = isset($_POST["te_link"]) ? $_POST["te_link"] : "";


switch ($request) {

	case "viewTypes":

		// Fetch all menu items at once
		 $queryviewtypes = execsqlSRS("
			SELECT document_id, document_details, document_status, document_link
			FROM tbl_DocumentType
			ORDER BY document_id",
			"Select",
			array()
			);

			foreach ($queryviewtypes as $types) {

				echo "<tr>";
				echo "<td>" . htmlspecialchars($types["document_id"]) . "</td>";
				echo "<td>" . htmlspecialchars($types["document_details"]) . "</td>";
				echo "<td>" . htmlspecialchars($types["document_status"]) . "</td>";
				echo "<td>" . htmlspecialchars($types["document_link"]) . "</td>";
				echo "<td>
						<button type='button' class='btn btn-warning' id='edit_type' value='" . htmlspecialchars($types["document_id"]) . "'>
							Edit
						</button>
						</td>";
			} 
	break;
	
	case "adddoctype":

		// Fetch all menu items at once
		 $queryaddtypes = execsqlSRS("
			SELECT document_id, document_details, document_status, document_link
			FROM tbl_DocumentType
			ORDER BY document_id",
			"Select",
			array()
			);

	break;
	
	case "saveaddedtype":

		// Fetch all menu items at once
		 $saveaddedtypes = execsqlSRS("
			INSERT INTO tbl_DocumentType (document_id, document_details, document_status, document_link)
			VALUES (:type_id, :type_details, :type_status, :type_link)",
			"Insert", [
						":type_id" => $type_id,
						":type_details" => $type_details,
						":type_status" => $type_status,
						":type_link" => $type_link
					  ]
			);
			
		echo "Document Type Added!";

	break;
	
	case "edittype":

		 $queryedittype = execsqlSRS("
			SELECT document_id, document_details, document_status, document_link
			FROM tbl_DocumentType
			
			WHERE document_id = '{$editdata}'",
			"Select",
			array()
			);

				foreach ($queryedittype as $edit) {

echo "<div class=''>
              <div class='card mb-3'>
                <div class='card-body'>
                  <div class='row'>
                    <div class='col-sm-3'>
                      <h6 class='mb-0 text-secondary'>Document ID</h6>
                    </div>
                    <div class='col-sm-9'>
                      <input type='text' class='form-control font-weight-bold' id='te_id' value='" . htmlspecialchars($edit["document_id"]) . "' disabled>
                    </div>
                  </div>";

echo                  "<hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Details</h6>
                    </div>
                    <div class='col-sm-9'>
					<strong>
						<input type='text' class='form-control font-weight-bold' id='te_details' value='" . htmlspecialchars($edit["document_details"]) . "'>
                    </strong>
					</div>
                  </div>";

echo                  "<hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Status</h6>
                    </div>
                    <div class='col-sm-9'>
					<strong>
						<input type='text' class='form-control font-weight-bold' id='te_status' value='" . htmlspecialchars($edit["document_status"]) . "'>
						</strong>
					</div>
                  </div>";
				  
echo                  "<hr>
                  <div class='row'>
                    <div class='col-sm-3 text-secondary'>
                      <h6 class='mb-0'>Link</h6>
                    </div>
                    <div class='col-sm-9'>
						<input type='text' class='form-control font-weight-bold' id='te_link' value='" . htmlspecialchars($edit["document_link"]) . "'>
                    </div>
                  </div>";
				  

				echo    "<div class='form-group d-flex justify-content-center pt-3'>
				<button type='submit' id='save_type' class='btn btn-primary' value='" . htmlspecialchars($edit["document_id"]) . "'>Save Changes</button>
              </div>";

			}

	break;
	
	case "savetype":

		// Fetch all menu items at once
		 $querysavetype = execsqlSRS("
			UPDATE tbl_DocumentType
			SET document_details = :te_details, document_status = :te_status, document_link = :te_link
			WHERE document_id = :savedata",
			"Update",
				[
					":te_details" => $te_details,
					":te_status" => $te_status,
					":te_link" => $te_link,
					":savedata" => $savedata
				]
			);
			
		echo "Changes have been Saved!";

	break;
}