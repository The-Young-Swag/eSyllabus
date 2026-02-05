<?php
	date_default_timezone_set('Asia/Manila');
	include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$department = isset($_POST["department"]) ? $_POST["department"] : "";
$office = isset($_POST["office"]) ? $_POST["office"] : "";
$unit = isset($_POST["unit"]) ? $_POST["unit"] : "";
$doctype = isset($_POST["doctype"]) ? $_POST["doctype"] : "";
$selectedoptions = isset($_POST["selectedoptions"]) ? $_POST["selectedoptions"] : "";

		if (!empty($office)) {

			$sendTo = execsqlSRS("
				SELECT d.dept_id, d.dept_name, d.dept_desc
				FROM tbl_Departments d
				WHERE d.dept_status = '0' and d.office_id = $office
				ORDER BY d.dept_name
				", "Select", array());

			if (count($sendTo) > 0) {

				foreach ($sendTo as $send) {
							$dept_id = $send['dept_id'];
							$dept_name = $send['dept_name'];
							$dept_desc = $send['dept_desc'];
							echo "<option value = '$dept_id'>$dept_desc | $dept_name</option>";
				}
			}
		}

		if (!empty($department)) {

			$sendTo = execsqlSRS("
				SELECT u.unit_id, u.unit_name, u.unit_desc
				FROM tbl_Units u
				
				LEFT JOIN tbl_Departments d
				ON d.dept_id = u.dept_id
				
				LEFT JOIN tbl_Offices o
				ON o.office_id = d.office_id
				
				WHERE u.unit_status = '0' AND u.dept_id = $department
				ORDER BY u.unit_name
				", "Select", array());

			if (count($sendTo) > 0) {

				foreach ($sendTo as $send) {
							$unit_id = $send['unit_id'];
							$unit_name = $send['unit_name'];
							$unit_desc = $send['unit_desc'];
							echo "<option value = '$unit_id'>$unit_desc | $unit_name</option>";
				}
			}
		}
		
		if (!empty($unit)) {

			$sendTo = execsqlSRS("
				SELECT u.unit_name, u.unit_desc
				FROM tbl_Units u
				
				WHERE u.unit_status = '0' AND u.unit_id = $unit
				", "Select", array());

			if (count($sendTo) > 0) {

				foreach ($sendTo as $send) {
					echo htmlspecialchars($send["unit_desc"]);
				}
			}
		}

switch ($request) {

	case "dype":
	
		if (!$doctype) {
			echo "";
			break;
		}
		
		else {

			// Fetch all menu items at once
			 $querydype = execsqlSRS("
				SELECT document_details
				FROM tbl_DocumentType
				
				WHERE document_id = :doctype AND document_status = '0'",
				"Select", [
							":doctype" => $doctype
						  ]
				);
				
					foreach ($querydype as $type) {
						
							echo htmlspecialchars($type["document_details"]);
							
					}
		}

	break;

	case "selected":

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
						id = 'doc_submit'
						type = 'submit'
						value = '" . htmlspecialchars($implodedselect) . "'>Send to " . htmlspecialchars(count($selectedoptions)) . " Unit/s</button></div>";
		

	}

	break;
}

?>