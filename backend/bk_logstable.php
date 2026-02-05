<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : "";
$user_name = isset($_POST["user_name"]) ? $_POST["user_name"] : "";
$user_office = isset($_POST["user_office"]) ? $_POST["user_office"] : "";
$pagi_limit = isset($_POST["pagi_limit"]) ? $_POST["pagi_limit"] : "";

$limit = intval($pagi_limit);

switch ($request) {

	case "viewlogs":

		// Fetch all menu items at once
		$queryviewlogs = execsqlSRS("
			SELECT DISTINCT i.doc_type, i.tracking_id, i.doc_details, i.doc_number, i.doc_date, i.doc_proponent, i.doc_amount
			FROM tbl_Intersection i

			LEFT JOIN tbl_Highway h
			ON i.tracking_id = h.tracking_id

			WHERE 
			(
			(i.doc_status = '0' AND h.doc_active = '0') 
			AND 
			(h.receiver_office = $user_office OR h.sender_office = $user_office)
			)

			ORDER BY i.tracking_id DESC
			OFFSET 0 ROWS
			FETCH NEXT $limit ROWS ONLY", 
			"Select",
			array()
			);
			
			if (count($queryviewlogs) == 0){
				echo "<div class='d-flex justify-content-center'><p class='font-weight-bold text-danger'>No Logs...</p></div>";
			}
			
			else {

				foreach ($queryviewlogs as $logs) {

					echo "<tr>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($logs["doc_type"]) . "</td>";
					echo "<td>" . htmlspecialchars($logs["tracking_id"]) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($logs["doc_details"]) . "</td>";
					echo "<td>" . htmlspecialchars($logs["doc_number"]) . "</td>";
					echo "<td>" . htmlspecialchars($logs["doc_date"]) . "</td>";
					echo "<td>" . htmlspecialchars($logs["doc_proponent"]) . "</td>";
					echo "<td>" . htmlspecialchars($logs["doc_amount"]) . "</td>";

					echo "<td>
							<button type='button' class='btn btn-info m-1' id='view_doc' value='" . htmlspecialchars($logs["tracking_id"]) . "'>
								View
							</button>
							<button type='button' class='btn btn-info m-1' id='track_doc' value='" .htmlspecialchars($logs["tracking_id"]) . "'>
								Track
							</button>
						  </td>";
					echo "</tr>";
				}
			}
	break;
}

?>
