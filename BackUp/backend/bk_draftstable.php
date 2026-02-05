<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : "";
$user_name = isset($_POST["user_name"]) ? $_POST["user_name"] : "";
$user_office = isset($_POST["user_office"]) ? $_POST["user_office"] : "";

switch ($request) {

	case "viewdrafts":

		// Fetch all menu items at once
		 $queryviewdrafts = execsqlSRS("
			SELECT tracking_id, doc_type, doc_details, doc_number, doc_date, doc_proponent, doc_amount
			FROM tbl_Intersection
			WHERE doc_status = '0' AND UserID = $user_id AND doc_issent = '1'
			ORDER BY tracking_id DESC",
			"Select",
			array()
			);
			
			if (count($queryviewdrafts) == 0){
				echo "<div class='d-flex justify-content-center'><p class='font-weight-bold text-danger'>No Drafts...</p></div>";
			}
			
			else {

				foreach ($queryviewdrafts as $drafts) {

					echo "<tr>";
					echo "<td>" . htmlspecialchars($drafts["doc_type"]) . "</td>";
					echo "<td>" . htmlspecialchars($drafts["tracking_id"]) . "</td>";
					echo "<td>" . htmlspecialchars($drafts["doc_details"]) . "</td>";
					echo "<td>" . htmlspecialchars($drafts["doc_number"]) . "</td>";
					echo "<td>" . htmlspecialchars($drafts["doc_date"]) . "</td>";
					echo "<td>" . htmlspecialchars($drafts["doc_proponent"]) . "</td>";
					echo "<td>" . htmlspecialchars($drafts["doc_amount"]) . "</td>";
					echo "<td>
							<button type='button' class='btn btn-warning m-1' id='forward_draft' value='" . htmlspecialchars($drafts["tracking_id"]) . "'>
								Edit/Forward
							</button>
							<button type='button' class='btn btn-danger m-1' id='remove_draft' value='" . htmlspecialchars($drafts["tracking_id"]) . "'>
								Remove
							</button>
							</td>";
				} 
			}
	break;
}