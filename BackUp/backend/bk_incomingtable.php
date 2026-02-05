<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : "";
$user_name = isset($_POST["user_name"]) ? $_POST["user_name"] : "";
$user_office = isset($_POST["user_office"]) ? $_POST["user_office"] : "";

switch ($request) {

	case "viewincoming":

		// Fetch all menu items at once
		$queryviewincoming = execsqlSRS("
			SELECT i.doc_type, i.tracking_id, i.doc_details, h.dt_released, u.Name, o.unit_desc, h.doc_remarks, h.highway_id
			FROM tbl_Intersection i

			LEFT JOIN tbl_Highway h
			ON i.tracking_id = h.tracking_id

			LEFT JOIN Sys_UserAccount u 
			ON h.sender_id = u.UserID

			LEFT JOIN tbl_Units o
			ON h.sender_office = o.unit_id

			LEFT JOIN tbl_Attachment a 
			ON h.doc_attachment = a.att_id

			WHERE i.doc_status = '0' AND h.doc_active = '0' AND h.receiver_office = $user_office AND h.doc_status = '1'
			ORDER BY i.tracking_id DESC", 
			"Select",
			array()
			);
			
			if (count($queryviewincoming) == 0){
				echo "<div class='d-flex justify-content-center'><p class='font-weight-bold text-danger'>No Incoming Documents...</p></div>";
			}

			else {
				foreach ($queryviewincoming as $incoming) {

					echo "<tr>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($incoming["doc_type"]) . "</td>";
					echo "<td>" . htmlspecialchars($incoming["tracking_id"]) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($incoming["doc_details"]) . "</td>";
					echo "<td>" . substr(htmlspecialchars($incoming["dt_released"]), 0, -4) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($incoming["Name"]) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($incoming["unit_desc"]) . "</td>";
					echo "<td class='text-danger font-weight-bold'>" . htmlspecialchars($incoming["doc_remarks"]) . "</td>";

					echo "<td>
							<button type='button' class='btn btn-info m-1' id='view_doc' value='" . htmlspecialchars($incoming["tracking_id"]) . "'>
								View
							</button>
							<button type='button' class='btn btn-success m-1' id='receive_doc' value='" . htmlspecialchars($incoming["highway_id"]) . "'>
								Receive
							</button>
							<button type='button' class='btn btn-danger m-1' id='return_doc' value='" . htmlspecialchars($incoming["highway_id"]) . "'>
								Return
							</button>
							<button type='button' class='btn btn-info m-1' id='track_doc' value='" .htmlspecialchars($incoming["tracking_id"]) . "'>
								Track
							</button>
						  </td>";
					echo "</tr>";
				}
			}
	break;
}

?>
