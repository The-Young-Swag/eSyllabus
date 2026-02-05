<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : "";
$user_name = isset($_POST["user_name"]) ? $_POST["user_name"] : "";
$user_office = isset($_POST["user_office"]) ? $_POST["user_office"] : "";
$pagi_limit = isset($_POST["pagi_limit"]) ? $_POST["pagi_limit"] : "";

$limit = intval($pagi_limit);

switch ($request) {
	case "viewoutgoing":

		// Fetch all menu items at once
		 $queryviewoutgoing = execsqlSRS("
			SELECT i.doc_type, i.tracking_id, i.doc_details, h.dt_released, n.Name, o.unit_desc, 
			h.doc_remarks, a.att_filename, a.att_filepath, d.status_description, h.doc_status, h.highway_id
			FROM tbl_Intersection i

			LEFT JOIN tbl_Highway h
			ON i.tracking_id = h.tracking_id

			LEFT JOIN Sys_UserAccount n
			ON h.sender_id = n.UserID

			LEFT JOIN tbl_Units o
			ON h.receiver_office = o.unit_id

			LEFT JOIN tbl_Attachment a
			ON h.doc_attachment = a.att_id

			LEFT JOIN tbl_DocumentStatus d
			ON h.doc_status = d.status_id

			WHERE (
			(h.doc_active = '0' AND i.doc_status = '0') 
			AND 
			(h.sender_office = $user_office)
			)
			ORDER BY h.highway_id DESC
			OFFSET 0 ROWS
			FETCH NEXT $limit ROWS ONLY",
			"Select",
			array()
			);
		

			if (count($queryviewoutgoing) == 0){
				echo "<div class='d-flex justify-content-center'><p class='font-weight-bold text-danger'>No Outgoing Documents...</p></div>";
			}
			
			else {

				foreach ($queryviewoutgoing as $outgoing) {
					
					
					echo "<tr>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($outgoing["doc_type"]) . "</td>";
					echo "<td>" . htmlspecialchars($outgoing["tracking_id"]) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($outgoing["doc_details"]) . "</td>";
					echo "<td>" . substr(htmlspecialchars($outgoing["dt_released"]), 0, -4) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($outgoing["Name"]) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($outgoing["unit_desc"]) . "</td>";
					echo "<td class='text-danger font-weight-bold'>" . htmlspecialchars($outgoing["doc_remarks"]) . "</td>";

					if (htmlspecialchars($outgoing["doc_status"]) == 0) {
					echo "<td class='text-success'><b>" . htmlspecialchars($outgoing["status_description"]) . "</b></td>";
					}
					else if (htmlspecialchars($outgoing["doc_status"]) == 1) {
					echo "<td class='text-info'><b>" . htmlspecialchars($outgoing["status_description"]) . "</b></td>";
					}
					else if (htmlspecialchars($outgoing["doc_status"]) == 2) {
					echo "<td class='text-danger'><b>" . htmlspecialchars($outgoing["status_description"]) . "</b></td>";
					}
					else if (htmlspecialchars($outgoing["doc_status"]) == 3) {
					echo "<td class='text-success'><b>" . htmlspecialchars($outgoing["status_description"]) . "</b></td>";
					}
					else if (htmlspecialchars($outgoing["doc_status"]) == 4) {
					echo "<td class='text-success'><b>" . htmlspecialchars($outgoing["status_description"]) . "</b></td>";
					}
					echo "<td>
							<button type='button' class='btn btn-info m-1' id='view_doc' value='" . htmlspecialchars($outgoing["tracking_id"]) . "'>
								View
							</button>
							<button type='button' class='btn btn-info m-1' id='track_doc' value='" . htmlspecialchars($outgoing["tracking_id"]) . "'>
								Track
							</button>
							</td>";
				} 
			}
	break;
}