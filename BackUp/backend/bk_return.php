<?php
	date_default_timezone_set('Asia/Manila');
	include "../db/dbconnection.php";

$currentDT = date("Y-m-d H:i:s");
$currentD = date("Y-m-d");
$currentT = date("h-i-sa");

// Get the request type from POST data
$request = isset($_POST["request"]) ? $_POST["request"] : ""; 
$doc_remarks = isset($_POST["doc_remarks"]) ? $_POST["doc_remarks"] : ""; 
$doc_return = isset($_POST["doc_return"]) ? $_POST["doc_return"] : ""; 

//UserInfo etails
$sender_id = isset($_POST["UserID"]) ? $_POST["UserID"] : ""; 
$sender_email = isset($_POST["UserEmail"]) ? $_POST["UserEmail"] : ""; 
$sender_office = isset($_POST["UserOffice"]) ? $_POST["UserOffice"] : ""; 

//Form Data============================================================================================================================================================
switch ($request) {

	case "return":

		if (empty($doc_remarks))
		{
			echo "Remarks: Why are you returning this document?";
			break;
		}

//Highway (Details)

 		$Highwayprevioustracking = execsqlSRS (
			"SELECT DISTINCT tracking_id
			FROM tbl_Highway
			WHERE highway_id = :doc_return",
			"Select", [
					":doc_return" => $doc_return
			]);
			
		$tracking_id = $Highwayprevioustracking[0]["tracking_id"];

		$Returnedit = execsqlSRS("
			UPDATE tbl_Highway
			SET doc_status = '2'
			WHERE highway_id = :doc_return", 
			"Update", [
					":doc_return" => $doc_return
			]);

 		$Highwayprevioussender = execsqlSRS (
			"SELECT sender_office
			FROM tbl_Highway
			WHERE highway_id = :doc_return",
			"Select", [
					":doc_return" => $doc_return
			]);

		$return_id = $Highwayprevioussender[0]["sender_office"];

        $Highway = execsqlSRS(
            "INSERT INTO [tbl_Highway] (sender_id, dt_released, doc_remarks, doc_status, doc_active, sender_office, receiver_office)
             VALUES (:sender_id, :currentDT, :doc_remarks, 1, 0, :sender_office, :return_id)",
            "Insert", [
						":sender_id" => $sender_id,
						":currentDT" => $currentDT,
						":doc_remarks" => $doc_remarks,
						":sender_office" => $sender_office,
						":return_id" => $return_id	
			]);

 		$Highwayselect = execsqlSRS (
			"SELECT TOP 1 sender_id, dt_released, doc_remarks, doc_attachment, sender_office, receiver_office, highway_id
			FROM tbl_Highway
			WHERE sender_id = :sender_id AND dt_released = :currentDT AND doc_remarks = :doc_remarks
			AND sender_office = :sender_office AND receiver_office = :return_id
			ORDER BY highway_id DESC",
			"Select", [
					":sender_id" => $sender_id,
					":currentDT" => $currentDT,
					":doc_remarks" => $doc_remarks,
					":sender_office" => $sender_office,
					":return_id" => $return_id
			]);

		$highway_id = $Highwayselect[0]["highway_id"];

		$Highwayedit = execsqlSRS("
			UPDATE tbl_Highway
			SET tracking_id = :tracking_id
			WHERE highway_id = :highway_id", 
			"Update", [
					":tracking_id" => $tracking_id,
					":highway_id" => $highway_id
			]);

			echo json_encode([
			"System Message:" => "Document Forwarded!"]); 
			
	break;

//=======================================================================================================================================================================
}
?>