<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$highway = isset($_POST["highway"]) ? $_POST["highway"] : "";

$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : "";
$user_name = isset($_POST["user_name"]) ? $_POST["user_name"] : "";
$user_office = isset($_POST["user_office"]) ? $_POST["user_office"] : "";
$pagi_limit = isset($_POST["pagi_limit"]) ? $_POST["pagi_limit"] : "";

$limit = intval($pagi_limit);

$sname = isset($_POST["sname"]) ? $_POST["sname"] : "";
$soffice = isset($_POST["soffice"]) ? $_POST["soffice"] : "";
$rname = isset($_POST["rname"]) ? $_POST["rname"] : "";
$roffice = isset($_POST["roffice"]) ? $_POST["roffice"] : "";
$dstatus = isset($_POST["dstatus"]) ? $_POST["dstatus"] : "";

$edt_tracking = isset($_POST["edt_tracking"]) ? $_POST["edt_tracking"] : "";
$edt_sender = isset($_POST["edt_sender"]) ? $_POST["edt_sender"] : "";
$edt_senderoffice = isset($_POST["edt_senderoffice"]) ? $_POST["edt_senderoffice"] : "";
$edt_released = isset($_POST["edt_released"]) ? $_POST["edt_released"] : "";
$edt_remarks = isset($_POST["edt_remarks"]) ? $_POST["edt_remarks"] : "";
$edt_receiver = isset($_POST["edt_receiver"]) ? $_POST["edt_receiver"] : "";
$edt_receiveroffice = isset($_POST["edt_receiveroffice"]) ? $_POST["edt_receiveroffice"] : "";
$edt_received = isset($_POST["edt_received"]) ? $_POST["edt_received"] : "";
$edt_status = isset($_POST["edt_status"]) ? $_POST["edt_status"] : "";
$edt_active = isset($_POST["edt_active"]) ? $_POST["edt_active"] : "";

switch ($request) {

	case "viewDocTrans":

		 $queryviewdoctrans = execsqlSRS("
			SELECT h.highway_id, h.tracking_id, ua1.Name name1, u1.unit_name unit1, h.dt_released, h.doc_remarks,
			ua2.Name name2, u2.unit_name unit2, h.dt_received, s.status_description, h.doc_active,
			
			ua1.UserID sid, u1.unit_id soff, ua2.UserID rid, u2.unit_id roff, s.status_id
			
			FROM tbl_Highway h
			
			LEFT JOIN Sys_UserAccount ua1
			ON ua1.UserID = h.sender_id
			
			LEFT JOIN tbl_Units u1
			ON u1.unit_id = h.sender_office
			
			LEFT JOIN Sys_UserAccount ua2
			ON ua2.UserID = h.receiver_id
			
			LEFT JOIN tbl_Units u2
			ON u2.unit_id = h.receiver_office
			
			LEFT JOIN tbl_DocumentStatus s
			ON s.status_id = h.doc_status
			
			ORDER BY h.highway_id DESC
			OFFSET 0 ROWS
			FETCH NEXT $limit ROWS ONLY",
			"Select",
			array()
			);

			foreach ($queryviewdoctrans as $doctrans) {

				echo "<tr>";
				echo "<td>" . htmlspecialchars($doctrans["highway_id"]) . "</td>";
				echo "<td>" . htmlspecialchars($doctrans["tracking_id"]) . "</td>";
				echo "<td>" . htmlspecialchars($doctrans["name1"]) . "</td>";
				echo "<td>" . htmlspecialchars($doctrans["unit1"]) . "</td>";
				echo "<td>" . htmlspecialchars($doctrans["dt_released"]) . "</td>";
				echo "<td>" . htmlspecialchars($doctrans["doc_remarks"]) . "</td>";
				echo "<td>" . htmlspecialchars($doctrans["name2"]) . "</td>";
				echo "<td>" . htmlspecialchars($doctrans["unit2"]) . "</td>";
				echo "<td>" . htmlspecialchars($doctrans["dt_received"]) . "</td>";
				echo "<td>" . htmlspecialchars($doctrans["status_description"]) . "</td>";
				echo "<td>" . htmlspecialchars($doctrans["doc_active"]) . "</td>";
				echo "<td>
						<button 
								type='button' 
								class='btn btn-warning m-1' 
								id='doctrans_edit' 
								data-sname='" . htmlspecialchars($doctrans["sid"]) . "'
								data-soffice='" . htmlspecialchars($doctrans["soff"]) . "'
								data-rname='" . htmlspecialchars($doctrans["rid"]) . "'
								data-roffice='" . htmlspecialchars($doctrans["roff"]) . "'
								data-status='" . htmlspecialchars($doctrans["status_id"]) . "'
								value='" . htmlspecialchars($doctrans["highway_id"]) . "'>
							Edit
						</button>
						</td>";
			} 
	break;
	
	case "editdoctrans":
	
		 $query1 = execsqlSRS("
			SELECT Name
			FROM Sys_UserAccount
			
			WHERE UserID = :sname",
			"Select",
			[":sname" => $sname]
			);
		$sender_name = $query1[0]["Name"];
		
		if (empty($rname)) {
			
			$receiver_name = NULL;
		}
		
		else if (!empty($rname)) {

			 $query2 = execsqlSRS("
				SELECT Name
				FROM Sys_UserAccount
				
				WHERE UserID = :rname",
				"Select",
				[":rname" => $rname]
				);
			$receiver_name = $query2[0]["Name"];
		}	
		
		 $query3 = execsqlSRS("
			SELECT unit_name
			FROM tbl_Units
			
			WHERE unit_id = :soffice",
			"Select",
			[":soffice" => $soffice]
			);
		$sender_office = $query3[0]["unit_name"];
		
		 $query4 = execsqlSRS("
			SELECT unit_name
			FROM tbl_Units
			
			WHERE unit_id = :roffice",
			"Select",
			[":roffice" => $roffice]
			);
		$receiver_office = $query4[0]["unit_name"];
		
		 $query5 = execsqlSRS("
			SELECT status_description
			FROM tbl_DocumentStatus
			
			WHERE status_id = :dstatus",
			"Select",
			[":dstatus" => $dstatus]
			);
		$docstatus = $query5[0]["status_description"];

		 $queryeditdoctrans = execsqlSRS("
			SELECT h.highway_id, h.tracking_id, ua1.Name name1, u1.unit_name unit1, h.dt_released, h.doc_remarks,
			ua2.Name name2, u2.unit_name unit2, h.dt_received, s.status_description, h.doc_active
			FROM tbl_Highway h
			
			LEFT JOIN Sys_UserAccount ua1
			ON ua1.UserID = h.sender_id
			
			LEFT JOIN tbl_Units u1
			ON u1.unit_id = h.sender_office
			
			LEFT JOIN Sys_UserAccount ua2
			ON ua2.UserID = h.receiver_id
			
			LEFT JOIN tbl_Units u2
			ON u2.unit_id = h.receiver_office
			
			LEFT JOIN tbl_DocumentStatus s
			ON s.status_id = h.doc_status
			
			WHERE h.highway_id = :highway",
			"Select",
			[":highway" => $highway]
			);

			foreach ($queryeditdoctrans as $edit) {

			echo "<div class='form-group'>
					<label for=''>ID</label>
                    <input type='text' class='form-control' value='" . htmlspecialchars($edit["highway_id"]) . "' disabled>
				  </div>";
				  
			echo "<div class='form-group'>
					<label for=''>Tracking</label>
                    <input type='text' class='form-control' id='edt_tracking' value='" . htmlspecialchars($edit["tracking_id"]) . "'>
				  </div>";

				echo 
					"<div class='dropdown'>
						<label for='' class='pr-2'>Sender</label>
							<select class='form-select' aria-label='Default select example' name='' id='edt_sender'>
								<option value='" . htmlspecialchars($sname) . "'>" . htmlspecialchars($sender_name) . "</option>";
										$q1 = execsqlSRS("
										SELECT UserID, Name
										FROM Sys_UserAccount
										WHERE IsActive = '0'
										ORDER BY Name
										", "Select", array());
										foreach ($q1 as $q1q) {
													$UserID1 = $q1q['UserID'];
													$Name1 = $q1q['Name'];
													echo "<option value = '$UserID1'>$Name1</option>";
										}
				echo    "</select>
					</div>";
					
				echo 
					"<div class='dropdown'>
						<label for='' class='pr-2'>Sender Office</label>
							<select class='form-select' aria-label='Default select example' name='' id='edt_senderoffice'>
								<option value='" . htmlspecialchars($soffice) . "'>" . htmlspecialchars($sender_office) . "</option>";
										$q2 = execsqlSRS("
										SELECT unit_id, unit_name
										FROM tbl_units
										WHERE unit_status = '0'
										ORDER BY unit_name
										", "Select", array());
										foreach ($q2 as $q2q) {
													$unit_id1 = $q2q['unit_id'];
													$unit_name1 = $q2q['unit_name'];
													echo "<option value = '$unit_id1'>$unit_name1</option>";
										}
				echo    "</select>
					</div>";
					
			echo "<div class='form-group mt-2'>
					<label for=''>D/T Released</label>
                    <input type='text' class='form-control' id='edt_released' value='" . htmlspecialchars($edit["dt_released"]) . "'>
				  </div>";

			echo "<div class='form-group mt-2'>
					<label for=''>Remarks</label>
                    <input type='text' class='form-control' id='edt_remarks' value='" . htmlspecialchars($edit["doc_remarks"]) . "'>
				  </div>";
				  
				echo 
					"<div class='dropdown'>
						<label for='' class='pr-2'>Receiver</label>
							<select class='form-select' aria-label='Default select example' name='' id='edt_receiver'>
								<option value='" . htmlspecialchars($rname) . "'>" . htmlspecialchars($receiver_name) . "</option>";
										$q3 = execsqlSRS("
										SELECT UserID, Name
										FROM Sys_UserAccount
										WHERE IsActive = '0'
										ORDER BY Name
										", "Select", array());
										foreach ($q3 as $q3q) {
													$UserID2 = $q3q['UserID'];
													$Name2 = $q3q['Name'];
													echo "<option value = '$UserID2'>$Name2</option>";
										}
				echo    "</select>
					</div>";
					
				echo 
					"<div class='dropdown'>
						<label for='' class='pr-2'>Receiver Office</label>
							<select class='form-select' aria-label='Default select example' name='' id='edt_receiveroffice'>
								<option value='" . htmlspecialchars($roffice) . "'>" . htmlspecialchars($receiver_office) . "</option>";
										$q4 = execsqlSRS("
										SELECT unit_id, unit_name
										FROM tbl_units
										WHERE unit_status = '0'
										ORDER BY unit_name
										", "Select", array());
										foreach ($q4 as $q4q) {
													$unit_id2 = $q4q['unit_id'];
													$unit_name2 = $q4q['unit_name'];
													echo "<option value = '$unit_id2'>$unit_name2</option>";
										}
				echo    "</select>
					</div>";
					
			echo "<div class='form-group mt-2'>
					<label for=''>D/T Received</label>
                    <input type='text' class='form-control' id='edt_received' value='" . htmlspecialchars($edit["dt_received"]) . "'>
				  </div>";
				  
				echo 
					"<div class='dropdown'>
						<label for='' class='pr-2'>Document Status</label>
							<select class='form-select' aria-label='Default select example' name='' id='edt_status'>
								<option value='" . htmlspecialchars($dstatus) . "'>" . htmlspecialchars($docstatus) . "</option>";
										$q5 = execsqlSRS("
										SELECT status_id, status_description
										FROM tbl_DocumentStatus
										ORDER BY status_id
										", "Select", array());
										foreach ($q5 as $q5q) {
													$status_id = $q5q['status_id'];
													$status_description = $q5q['status_description'];
													echo "<option value = '$status_id'>$status_description</option>";
										}
				echo    "</select>
					</div>";
					
			echo "<div class='form-group mt-2'>
					<label for=''>IsActive</label>
                    <input type='text' class='form-control' id='edt_active' value='" . htmlspecialchars($edit["doc_active"]) . "'>
				  </div>";
				  
			echo    "<div class='form-group d-flex justify-content-center pt-2'>
						<button type='submit' id='edt_submit' class='btn btn-primary' value='" . htmlspecialchars($edit["highway_id"]) . "'>
						Save Changes</button>
					  </div>";
			} 
	break;
	
	case "savedoctrans":

		$querysavedoctrans = execsqlSRS("
			UPDATE tbl_Highway
			SET tracking_id = :edt_tracking, sender_id = :edt_sender, sender_office = :edt_senderoffice, dt_released = :edt_released, doc_remarks = :edt_remarks,
			receiver_id = :edt_receiver, receiver_office = :edt_receiveroffice, dt_received = :edt_received, doc_status = :edt_status, doc_active = :edt_active
			
			WHERE highway_id = :highway", 
			"Update", [
						":edt_tracking" => $edt_tracking, 
						":edt_sender" => $edt_sender, 
						":edt_senderoffice" => $edt_senderoffice, 
						":edt_released" => $edt_released,
						":edt_remarks" => $edt_remarks,
						":edt_receiver" => $edt_receiver,
						":edt_receiveroffice" => $edt_receiveroffice,
						":edt_received" => $edt_received,
						":edt_status" => $edt_status,
						":edt_active" => $edt_active,
						":highway" => $highway
					  ]); 

		echo "Changes have been Saved!";

	break;

}