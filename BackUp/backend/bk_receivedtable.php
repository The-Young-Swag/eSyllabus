<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : "";
$user_name = isset($_POST["user_name"]) ? $_POST["user_name"] : "";
$user_office = isset($_POST["user_office"]) ? $_POST["user_office"] : "";

switch ($request) {

	case "viewreceived":

		// Fetch all menu items at once
		$queryviewreceived = execsqlSRS("
			SELECT i.doc_type, i.tracking_id, i.doc_details, h.dt_received, u.Name, o.unit_desc, h.doc_remarks, a.att_filename, a.att_filepath, h.highway_id
			FROM tbl_Intersection i

			LEFT JOIN tbl_Highway h
			ON i.tracking_id = h.tracking_id

			LEFT JOIN Sys_UserAccount u 
			ON h.receiver_id = u.UserID

			LEFT JOIN tbl_Units o
			ON h.receiver_office = o.unit_id

			LEFT JOIN tbl_Attachment a 
			ON h.doc_attachment = a.att_id

			WHERE i.doc_status = '0' AND h.doc_active = '0' AND h.receiver_office = $user_office AND h.doc_status = '0'
			ORDER BY h.highway_id DESC", 
			"Select",
			array()
			);

			if (count($queryviewreceived) == 0){
				echo "<div class='d-flex justify-content-center'><p class='font-weight-bold text-danger'>All Documents have been Released...</p></div>";
			}

			else {
				foreach ($queryviewreceived as $received) {

					echo "<tr>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($received["doc_type"]) . "</td>";
					echo "<td>" . htmlspecialchars($received["tracking_id"]) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($received["doc_details"]) . "</td>";
					echo "<td>" . substr(htmlspecialchars($received["dt_received"]), 0, -4) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($received["Name"]) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($received["unit_desc"]) . "</td>";
					echo "<td class='text-danger font-weight-bold'>" . htmlspecialchars($received["doc_remarks"]) . "</td>";

					echo "<td>
							<button type='button' class='btn btn-info m-1' id='view_doc' value='" . htmlspecialchars($received["tracking_id"]) . "'>
								View
							</button>
							<button type='button' class='btn btn-warning m-1' id='editrec_doc' value='" . htmlspecialchars($received["tracking_id"]) . "'>
								Edit
							</button>
							<button type='button' class='btn btn-success m-1' id='forward_doc' value='" . htmlspecialchars($received["highway_id"]) . "'>
								Forward
							</button>
							<button type='button' class='btn btn-info m-1' id='track_doc' value='" .htmlspecialchars($received["tracking_id"]) . "'>
								Track
							</button>
							<button type='button' class='btn btn-danger m-1' id='complete_docpreview' value='" .htmlspecialchars($received["highway_id"]) . "'>
								Mark as Completed
							</button>
						  </td>";
					echo "</tr>";
				}
			}
	break;
}

?>
