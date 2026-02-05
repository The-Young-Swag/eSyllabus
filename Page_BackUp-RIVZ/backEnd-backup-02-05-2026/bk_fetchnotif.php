<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$user_id = isset($_POST["user_id"]) ? $_POST["user_id"] : "";
$user_name = isset($_POST["user_name"]) ? $_POST["user_name"] : "";
$user_office = isset($_POST["user_office"]) ? $_POST["user_office"] : "";

switch ($request) {

	case "fetchnotif":

		// Fetch all menu items at once
		$queryviewincoming = execsqlSRS("
			SELECT i.doc_type, i.tracking_id, i.doc_details, h.dt_released, u.Name, o.unit_name, h.doc_remarks, a.att_filename, a.att_filepath, h.highway_id
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

			$count = count($queryviewincoming);
		echo $count;
	break;
}

?>
