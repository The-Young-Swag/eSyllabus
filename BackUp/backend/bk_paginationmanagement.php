<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : "";
$user_name = isset($_POST["user_name"]) ? $_POST["user_name"] : "";
$user_office = isset($_POST["user_office"]) ? $_POST["user_office"] : "";

$pagi_id = isset($_POST["pagi_id"]) ? $_POST["pagi_id"] : "";
$pagi_number = isset($_POST["pagi_number"]) ? $_POST["pagi_number"] : "";
$pagi_status = isset($_POST["pagi_status"]) ? $_POST["pagi_status"] : "";
$pagi_cond = isset($_POST["pagi_cond"]) ? $_POST["pagi_cond"] : "";

$data = isset($_POST["data"]) ? $_POST["data"] : "";

$pagi_data = isset($_POST["pagi_data"]) ? $_POST["pagi_data"] : "";
$pagiedit_number = isset($_POST["pagiedit_number"]) ? $_POST["pagiedit_number"] : "";
$pagiedit_status = isset($_POST["pagiedit_status"]) ? $_POST["pagiedit_status"] : "";
$pagiedit_cond = isset($_POST["pagiedit_cond"]) ? $_POST["pagiedit_cond"] : "";

switch ($request) {

	case "viewpagination":

		// Fetch all menu items at once
		 $queryviewpagination = execsqlSRS("
			SELECT pagi_id, pagi_number, pagi_status, pagi_cond
			FROM tbl_Pagination
			ORDER BY pagi_id",
			"Select",
			array()
			);
			
			if (count($queryviewpagination) == 0){
				echo "<div class='d-flex justify-content-center'><p class='font-weight-bold text-danger'>No Pagination Numbers...</p></div>";
			}
			
			else {

				foreach ($queryviewpagination as $pagi) {

					echo "<tr>";
					echo "<td>" . htmlspecialchars($pagi["pagi_id"]) . "</td>";
					echo "<td>" . htmlspecialchars($pagi["pagi_number"]) . "</td>";
					echo "<td>" . htmlspecialchars($pagi["pagi_status"]) . "</td>";
					echo "<td>" . htmlspecialchars($pagi["pagi_cond"]) . "</td>";
					echo "<td>
							<button type='button' class='btn btn-warning m-1' id='pagi_edit' value='" . htmlspecialchars($pagi["pagi_id"]) . "'>
								Edit
							</button>
							</td>";
				} 
			}
	break;
	
	case "addpagi":
	
		if (empty($pagi_id)) {
			echo "ID Needed...";
			break;
		}
		
		else {

			$queryaddpagi = execsqlSRS("
				INSERT INTO [tbl_Pagination] (pagi_id, pagi_number, pagi_status, pagi_cond)
				VALUES (:pagi_id, :pagi_number, :pagi_status, :pagi_cond)", 
				"Insert", [
							":pagi_id" => $pagi_id, 
							":pagi_number" => $pagi_number, 
							":pagi_status" => $pagi_status,
							":pagi_cond" => $pagi_cond
						  ]); 
					  
		}

	echo "Pagination Number Added!";

	break;
	
	case "editpagination":

		$queryeditpagi = execsqlSRS("
			SELECT pagi_id, pagi_number, pagi_status, pagi_cond
			FROM [tbl_Pagination]
			WHERE pagi_id = :data", 
			"Select", [
						":data" => $data, 
					  ]);

			foreach ($queryeditpagi as $edit) {

			echo "<div class='form-group'>
					<label for='doc_remarks'>ID</label>
                    <input type='text' class='form-control' id='pagiedit_id' value='" . htmlspecialchars($edit["pagi_id"]) . "' disabled>
				  </div>";
			echo "<div class='form-group'>
					<label for='doc_remarks'>Number/Limit</label>
                    <input type='text' class='form-control' id='pagiedit_number' value='" . htmlspecialchars($edit["pagi_number"]) . "'>
				  </div>";

			echo "<div class='form-group'>
					<label for='doc_remarks'>Status</label>
                    <input type='text' class='form-control' id='pagiedit_status' value='" . htmlspecialchars($edit["pagi_status"]) . "'>
				  </div>";
				  
			echo "<div class='form-group'>
					<label for='doc_remarks'>User Tagged</label>
                    <input type='text' class='form-control' id='pagiedit_cond' value='" . htmlspecialchars($edit["pagi_cond"]) . "'>
				  </div>";
				  
			echo    "<div class='form-group d-flex justify-content-center pt-2'>
						<button type='submit' id='pagiedit_submit' class='btn btn-primary' value='" . htmlspecialchars($edit["pagi_id"]) . "'>
						Save Changes</button>
					  </div>";
			}

	break;
	
	case "savepagi":

		$querysavepagi = execsqlSRS("
			UPDATE tbl_Pagination
			SET pagi_number = :pagiedit_number, pagi_status = :pagiedit_status, pagi_cond = :pagiedit_cond
			WHERE pagi_id = :pagi_data" , 
			"Update", [
						":pagiedit_number" => $pagiedit_number, 
						":pagiedit_status" => $pagiedit_status, 
						":pagiedit_cond" => $pagiedit_cond,
						":pagi_data" => $pagi_data
					  ]); 

		echo "Changes have been Saved!";

	break;
}