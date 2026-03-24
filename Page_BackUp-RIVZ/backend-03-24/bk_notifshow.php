<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : "";
$user_name = isset($_POST["user_name"]) ? $_POST["user_name"] : "";
$user_office = isset($_POST["user_office"]) ? $_POST["user_office"] : "";

switch ($request) {

	case "notifsee":

		// Fetch all menu items at once
		$querynotifsee = execsqlSRS("
			SELECT i.doc_type, i.tracking_id, i.doc_details, h.dt_released, u.Name, o.unit_desc, h.doc_remarks, h.highway_id, h.doc_status
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

			if(empty($querynotifsee)){
				echo "<p class='pl-3 pt-2'>No Notifications</p>";
				die();
			}

			else{

			echo "<div class='card-body table-responsive table-bordered p-0 text-center'>
                <table class='table table-hover text-center table-striped'>
                  <thead class='thead-dark'>
                    <tr>
                      <th>Document Type</th>
                      <th>Tracking ID</th>
                      <th style='width: 280px;'>Description</th>
                      <th>D/T Released</th>
                      <th>By</th>
					  <th>From</th>
					  <th>Remarks</th>
					  <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>";

				foreach ($querynotifsee as $notif) {

					echo "<tr>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($notif["doc_type"]) . "</td>";
					echo "<td>" . htmlspecialchars($notif["tracking_id"]) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($notif["doc_details"]) . "</td>";
					echo "<td>" . substr(htmlspecialchars($notif["dt_released"]), 0, -4) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($notif["Name"]) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($notif["unit_desc"]) . "</td>";
					echo "<td class='text-danger font-weight-bold'>" . htmlspecialchars($notif["doc_remarks"]) . "</td>";
					echo "<td>
						<button type='button' class='btn btn-info' id='incredirect' data-pagename='incoming.php'>
								Check
						</button>
						</td>";
					echo "</tr>";
				}
			echo	  "</tbody>
				</table>
			</div>";
			}
	break;
}

?>

