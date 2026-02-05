<?php
date_default_timezone_set('Asia/Manila');
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$track_doc = isset($_POST["track_doc"]) ? $_POST["track_doc"] : "";

switch ($request) {

	case "trackdoc":

		// Fetch all menu items at once
		$queryviewtrack1 = execsqlSRS("
			SELECT i.doc_type, i.tracking_id, u.Name name, o.unit_desc unit, h.doc_remarks, h.dt_released, 
			uu.Name name1, oo.unit_desc unit1, a.att_filename, a.att_filepath, h.dt_received, h.doc_status, h.highway_id

			FROM tbl_Highway h

			LEFT JOIN tbl_Intersection i
			ON i.tracking_id = h.tracking_id

			LEFT JOIN Sys_UserAccount u 
			ON u.UserID = h.sender_id

			LEFT JOIN Sys_UserAccount uu 
			ON uu.UserID = h.receiver_id		

			LEFT JOIN tbl_Units o
			ON o.unit_id = h.sender_office

			LEFT JOIN tbl_Units oo
			ON oo.unit_id = h.receiver_office

			LEFT JOIN tbl_Attachment a
			ON a.att_id = h.doc_attachment

			WHERE h.tracking_id = ?
			ORDER BY h.highway_id", 
			"Select",
			[$track_doc]
			);
				foreach ($queryviewtrack1 as $track1) {
					
						if (!$queryviewtrack1) {
							echo "Tracking Error: Contact an Admin";
						}
						else {
					
							$diff = abs(strtotime($track1["dt_received"]) - strtotime($track1["dt_released"]));

							$years   = floor($diff / (365*60*60*24)); 
							$months  = floor(($diff - $years * 365*60*60*24) / (30*60*60*24)); 
							$days    = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
							$hours   = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60)); 
							$minutes  = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ 60); 
							$seconds = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $minutes*60)); 
							echo "<tr id='trackhead'>";
							
							echo "<td class='text-success font-weight-bold' id='tracksender'>" . htmlspecialchars($track1["name"]) . "</td>";
							echo "<td class='text-success font-weight-bold' id='tracksenderu'>" . htmlspecialchars($track1["unit"]) . "</td>";
							echo "<td id='trackremarks'>" . htmlspecialchars($track1["doc_remarks"]) . "</td>";
							echo "<td id='trackreleased'>" . substr(htmlspecialchars($track1["dt_released"]), 0, -4) . "</td>";
							
							if (htmlspecialchars($track1["doc_status"]) == 2){
								echo "<td class='text-danger font-weight-bold' id='trackreceiver'>---</td>";
							}
							else {
								echo "<td class='text-success font-weight-bold' id='trackreceiver'>" . htmlspecialchars($track1["name1"]) . "</td>";
							}

							echo "<td class='text-success font-weight-bold' id='trackreceiveru'>" . htmlspecialchars($track1["unit1"]) . "</td>";
							echo "<td id='trackreceived'>" . substr(htmlspecialchars($track1["dt_received"]), 0, -4) . "</td>";

							if (!empty($track1["dt_received"])){
							echo "<td class='text-info font-weight-bold' id='trackelapsed'>". htmlspecialchars($months) . "mon, " . htmlspecialchars($days) . "d, "
							. htmlspecialchars($hours) . "h, " . htmlspecialchars($minutes) . "min, "
							. htmlspecialchars($seconds) . "s" .
							"</td>";
							}
							else if (htmlspecialchars($track1["doc_status"]) == 2){
								echo "<td class='text-danger font-weight-bold' id='trackelapsed'>Returned</td>";
							}
							else {
								echo "<td class='text-danger font-weight-bold' id='trackelapsed'>Not Yet Received</td>";
							}

							$ogpath = "DocTrax/".htmlspecialchars($track1["att_filepath"]);

							if (empty(htmlspecialchars($track1["att_filepath"]))){
								echo "<td class='text-danger font-weight-bold'>No Attachment</td>";
							}
							else {
							$highway = htmlspecialchars($track1["highway_id"]);
							echo "<td id='view_attachmenthead'>
									<input type='submit' class='btn btn-info' 
											id='view_attachment' 
											data-openattach='".$ogpath."' 
											data-highwayid='".$highway."' 
											name='".$highway."' value='View'>
								  </td>";
							echo "</tr>";
							}
						}
				}

	break;
}

?>
