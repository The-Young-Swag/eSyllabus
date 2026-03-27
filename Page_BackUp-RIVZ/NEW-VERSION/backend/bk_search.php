<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$input = isset($_POST["input"]) ? $_POST["input"] : "";
$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : "";
$user_name = isset($_POST["user_name"]) ? $_POST["user_name"] : "";
$user_office = isset($_POST["user_office"]) ? $_POST["user_office"] : "";

switch ($request) {

	case "docsearch":

		// Fetch all menu items at once
		$querysearch = execsqlSRS("
			SELECT i.doc_type, i.tracking_id, i.doc_details, i.doc_number, i.doc_date, i.doc_proponent, i.doc_amount, i.doc_enduser, i.doc_payee, h.highway_id
			FROM tbl_Intersection i

			LEFT JOIN tbl_Highway h
			ON h.tracking_id = i.tracking_id

			WHERE (
	
				  (i.doc_status = '0' AND h.doc_active = '0') 

				  AND 

				  (h.receiver_office LIKE $user_office OR h.sender_office = $user_office)

				  AND
				  
				  (i.doc_type LIKE '%$input%' OR i.tracking_id LIKE '%$input%' OR i.doc_details LIKE '%$input%' OR
				  i.doc_number LIKE '%$input%' OR i.doc_date LIKE '%$input%' OR i.doc_proponent LIKE '%$input%' OR
				  i.doc_amount LIKE '%$input%' OR i.doc_enduser LIKE '%$input%' OR i.doc_payee LIKE '%$input%')

				  )

			ORDER BY h.highway_id DESC", 
			"Select",
			array()
			);
			
			if (empty($input)){
				echo "<p class='pt-3 pl-3 font-weight-bold text-danger'>Please enter something...</p>";
				break;
			}

			else if (count($querysearch) > 0) {

					echo "
					<div class='card-body table-responsive table-bordered pl-3'>
					<p class='font-weight-bold text-success'>Search Results...</p>
						<ol class='float-sm-right'>
						  <button type='button' class='btn btn-danger' id='closesearch'>
							  Close Results
							</button>
						</ol>	
						<table class='table table-hover text-center table-striped text-center pt-2'>
						  <thead class='bg-success'>
							<tr>
							  <th>Document Type</th>
							  <th>Tracking ID</th>
							  <th style='width: 280px;'>Description</th>
							  <th>Number</th>
							  <th>Date</th>
							  <th>Proponent</th>
							  <th>Amount</th>
							  <th>End-User</th>
							  <th>Payee</th>
							  <th>Action</th>
							</tr>
						  </thead>
						<tbody>";

				foreach ($querysearch as $search) {
					
					echo "<tr>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($search["doc_type"]) . "</td>";
					echo "<td>" . htmlspecialchars($search["tracking_id"]) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($search["doc_details"]) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($search["doc_number"]) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($search["doc_date"]) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($search["doc_proponent"]) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($search["doc_amount"]) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($search["doc_enduser"]) . "</td>";
					echo "<td class='text-success font-weight-bold'>" . htmlspecialchars($search["doc_payee"]) . "</td>";

					echo "<td>
							<button type='button' class='btn btn-info m-1' id='view_doc' value='" . htmlspecialchars($search["tracking_id"]) . "'>
								View
							</button>
							<button type='button' class='btn btn-info m-1' id='track_doc' value='" .htmlspecialchars($search["tracking_id"]) . "'>
								Track
							</button>
						  </td>";
					echo "</tr>";
				}

				echo  "</tbody>
						</table>
					</div>";
			}

			else {
				echo "<p class='pt-3 pl-3 font-weight-bold text-danger'>No Results Found...</p>";
			}
	break;
}

?>
