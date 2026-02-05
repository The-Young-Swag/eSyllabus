<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : "";
$user_name = isset($_POST["user_name"]) ? $_POST["user_name"] : "";
$user_office = isset($_POST["user_office"]) ? $_POST["user_office"] : "";
$pagi_limit = isset($_POST["pagi_limit"]) ? $_POST["pagi_limit"] : "";

$limit = intval($pagi_limit);

switch ($request) {

	case "viewtransactions":

		// Fetch all menu items at once
		$queryviewtransactions = execsqlSRS("
			SELECT DISTINCT i.doc_type, i.tracking_id, i.doc_details, i.doc_number, i.doc_date, i.doc_proponent, i.doc_amount, h.highway_id
			FROM tbl_Intersection i

			LEFT JOIN tbl_Highway h
			ON i.tracking_id = h.tracking_id

			WHERE i.doc_status = '0' AND h.doc_active = '0'
			ORDER BY i.tracking_id DESC
			OFFSET 0 ROWS
			FETCH NEXT $limit ROWS ONLY", 
			"Select",
			array()
			);

			foreach ($queryviewtransactions as $transactions) {

				echo "<tr>";
				echo "<td>" . htmlspecialchars($transactions["doc_type"]) . "</td>";
				echo "<td>" . htmlspecialchars($transactions["tracking_id"]) . "</td>";
				echo "<td>" . htmlspecialchars($transactions["doc_details"]) . "</td>";
				echo "<td>" . htmlspecialchars($transactions["doc_number"]) . "</td>";
				echo "<td>" . htmlspecialchars($transactions["doc_date"]) . "</td>";
				echo "<td>" . htmlspecialchars($transactions["doc_proponent"]) . "</td>";
				echo "<td>" . htmlspecialchars($transactions["doc_amount"]) . "</td>";

				echo "<td>
						<button type='button' class='btn btn-info m-1' id='view_doc' value='" . htmlspecialchars($transactions["tracking_id"]) . "'>
							View
						</button>
						<button type='button' class='btn btn-info m-1' id='track_doc' value='" .htmlspecialchars($transactions["tracking_id"]) . "'>
							Track
						</button>
					  </td>";
				echo "</tr>";
			}
	break;
	
	case "viewpr":

		// Fetch all menu items at once
		$queryviewtransactions = execsqlSRS("
			SELECT DISTINCT i.doc_type, i.tracking_id, i.doc_details, i.doc_number, i.doc_date, i.doc_proponent, i.doc_amount, h.highway_id
			FROM tbl_Intersection i

			LEFT JOIN tbl_Highway h
			ON i.tracking_id = h.tracking_id

			WHERE i.doc_status = '0' AND h.doc_active = '0' AND i.doc_type = 'Purchase Request' AND i.doc_issent = '0'
			ORDER BY i.tracking_id DESC
			OFFSET 0 ROWS
			FETCH NEXT $limit ROWS ONLY", 
			"Select",
			array()
			);

			foreach ($queryviewtransactions as $transactions) {

				echo "<tr>";
				echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($transactions["doc_type"]) . "</td>";
				echo "<td>" . htmlspecialchars($transactions["tracking_id"]) . "</td>";
				echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($transactions["doc_details"]) . "</td>";
				echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($transactions["doc_number"]) . "</td>";
				echo "<td>" . htmlspecialchars($transactions["doc_date"]) . "</td>";
				echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($transactions["doc_amount"]) . "</td>";

				echo "<td>
						<button type='button' class='btn btn-info m-1' id='view_doc' value='" . htmlspecialchars($transactions["tracking_id"]) . "'>
							View
						</button>
						<button type='button' class='btn btn-info m-1' id='track_doc' value='" .htmlspecialchars($transactions["tracking_id"]) . "'>
							Track
						</button>
					  </td>";
				echo "</tr>";
			}
	break;
	
	case "viewpo":

		// Fetch all menu items at once
		$queryviewtransactions = execsqlSRS("
			SELECT DISTINCT i.doc_type, i.tracking_id, i.doc_details, i.doc_number, i.doc_date, i.doc_proponent, i.doc_amount, h.highway_id
			FROM tbl_Intersection i

			LEFT JOIN tbl_Highway h
			ON i.tracking_id = h.tracking_id

			WHERE i.doc_status = '0' AND h.doc_active = '0' AND i.doc_type = 'Purchase Order' AND i.doc_issent = '0'
			ORDER BY i.tracking_id DESC
			OFFSET 0 ROWS
			FETCH NEXT $limit ROWS ONLY", 
			"Select",
			array()
			);

			foreach ($queryviewtransactions as $transactions) {

				echo "<tr>";
				echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($transactions["doc_type"]) . "</td>";
				echo "<td>" . htmlspecialchars($transactions["tracking_id"]) . "</td>";
				echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($transactions["doc_details"]) . "</td>";
				echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($transactions["doc_number"]) . "</td>";
				echo "<td>" . htmlspecialchars($transactions["doc_date"]) . "</td>";
				echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($transactions["doc_amount"]) . "</td>";

				echo "<td>
						<button type='button' class='btn btn-info m-1' id='view_doc' value='" . htmlspecialchars($transactions["tracking_id"]) . "'>
							View
						</button>
						<button type='button' class='btn btn-info m-1' id='track_doc' value='" .htmlspecialchars($transactions["tracking_id"]) . "'>
							Track
						</button>
					  </td>";
				echo "</tr>";
			}
	break;
}

?>
