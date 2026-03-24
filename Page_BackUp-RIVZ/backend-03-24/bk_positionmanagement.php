<?php
	date_default_timezone_set('Asia/Manila');
	include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";

$poscode = isset($_POST["poscode"]) ? $_POST["poscode"] : "";
$posdesc = isset($_POST["posdesc"]) ? $_POST["posdesc"] : "";
$posplant = isset($_POST["posplant"]) ? $_POST["posplant"] : "";
$posstat = isset($_POST["posstat"]) ? $_POST["posstat"] : "";

$pos_data = isset($_POST["pos_data"]) ? $_POST["pos_data"] : "";

$pos_code = isset($_POST["pos_code"]) ? $_POST["pos_code"] : "";
$pos_desc = isset($_POST["pos_desc"]) ? $_POST["pos_desc"] : "";
$pos_plant = isset($_POST["pos_plant"]) ? $_POST["pos_plant"] : "";
$pos_stat = isset($_POST["pos_stat"]) ? $_POST["pos_stat"] : "";

$tagged_data = isset($_POST["tagged_data"]) ? $_POST["tagged_data"] : "";

switch ($request) {
	
	case "viewPositions":

		// Fetch all menu items at once
		 $queryviewpositions = execsqlSRS("
			SELECT position_id, position_code, position_desc, plantilla_num, IsActive
			FROM tbl_Positions
			
			ORDER BY position_desc",
			"Select",
			array()
			);

			foreach ($queryviewpositions as $pos) {

				echo "<tr>";
				echo "<td>" . htmlspecialchars($pos["position_id"]) . "</td>";
				echo "<td>" . htmlspecialchars($pos["position_code"]) . "</td>";
				echo "<td>" . htmlspecialchars($pos["position_desc"]) . "</td>";
				echo "<td>" . htmlspecialchars($pos["plantilla_num"]) . "</td>";
				echo "<td>" . htmlspecialchars($pos["IsActive"]) . "</td>";
				echo "<td>
						<button type='button' class='btn btn-info' id='view_position' value='" . htmlspecialchars($pos["position_id"]) . "'>
							View
						</button>
						<button type='button' class='btn btn-warning' id='edit_position' value='" . htmlspecialchars($pos["position_id"]) . "'>
							Edit
						</button>
						</td>";
			} 
	break;

	case "addposition":

		$queryaddposition = execsqlSRS("
			INSERT INTO [tbl_Positions] (position_code, position_desc, plantilla_num, IsActive)
			VALUES (:poscode, :posdesc, :posplant, :posstat)", 
			"Insert", [
						":poscode" => $poscode, 
						":posdesc" => $posdesc, 
						":posplant" => $posplant, 
						":posstat" => $posstat
					  ]); 

	echo "Position Added!";

	break;

	case "editposition":

		$queryeditposition = execsqlSRS("
			SELECT position_id, position_code, position_desc, plantilla_num, IsActive
			FROM tbl_Positions
			WHERE position_id = :pos_data", 
			"Select", [
						":pos_data" => $pos_data, 
					  ]);

			foreach ($queryeditposition as $edit) {

			echo "<div class='form-group'>
					<label for='doc_remarks'>Position Code</label>
                    <input type='text' class='form-control' id='posedit_code' value='" . htmlspecialchars($edit["position_code"]) . "'>
				  </div>";
			echo "<div class='form-group'>
					<label for='doc_remarks'>Position Description</label>
                    <input type='text' class='form-control' id='posedit_desc' value='" . htmlspecialchars($edit["position_desc"]) . "'>
				  </div>";
				  
			echo "<div class='form-group'>
					<label for='doc_remarks'>Plantilla</label>
                    <input type='text' class='form-control' id='posedit_plant' value='" . htmlspecialchars($edit["plantilla_num"]) . "'>
				  </div>";

			echo "<div class='form-group'>
					<label for='doc_remarks'>Is Active</label>
                    <input type='text' class='form-control' id='posedit_status' value='" . htmlspecialchars($edit["IsActive"]) . "'>
				  </div>";
			echo    "<div class='form-group d-flex justify-content-center pt-2'>
						<button type='submit' id='posedit_submit' class='btn btn-primary' value='" . htmlspecialchars($edit["position_id"]) . "'>
						Save Changes</button>
					  </div>";
			}

	break;
	
	case "saveposition":

		$querysaveposition = execsqlSRS("
			UPDATE tbl_Positions
			SET position_code = :pos_code, position_desc = :pos_desc, plantilla_num = :pos_plant, IsActive = :pos_stat
			WHERE position_id = :pos_data" , 
			"Update", [
						":pos_code" => $pos_code, 
						":pos_desc" => $pos_desc, 
						":pos_plant" => $pos_plant, 
						":pos_stat" => $pos_stat, 
						":pos_data" => $pos_data
					  ]); 

		echo "Changes have been Saved!";

	break;

	case "viewPositionsTagged":

		// Fetch all menu items at once
		 $querypositiontag = execsqlSRS("
			SELECT ua.Name, u.unit_name, ua.EmailAddress, r.Role, ua.IsActive
			FROM Sys_UserAccount ua

			LEFT JOIN tbl_Units u
			ON u.unit_id = ua.Office_id

			LEFT JOIN Sys_Role r
			ON r.RID = ua.RID

			WHERE ua.Position_id = :tagged_data
			ORDER BY ua.Name",
			"Select",
			[":tagged_data" => $tagged_data]
			);

			foreach ($querypositiontag as $tag) {

				echo "<tr>";
				echo "<td>" . htmlspecialchars($tag["Name"]) . "</td>";
				echo "<td>" . htmlspecialchars($tag["unit_name"]) . "</td>";
				echo "<td>" . htmlspecialchars($tag["EmailAddress"]) . "</td>";
				echo "<td>" . htmlspecialchars($tag["Role"]) . "</td>";
				echo "<td>" . htmlspecialchars($tag["IsActive"]) . "</td>";
				echo "</tr>";
			} 
	break;
}

?>
