<?php
	date_default_timezone_set('Asia/Manila');
	include "../db/dbconnection.php";

$currentDT = date("Y-m-d H:i:s");
$currentDT2 = date("Y-m-dH-i-s");
$currentT = date("h-i-sa");

// Get the request type from POST data
$request = isset($_POST["request"]) ? $_POST["request"] : ""; 
$doc_remarks = isset($_POST["doc_remarks"]) ? $_POST["doc_remarks"] : ""; 
$doc_forward = isset($_POST["doc_forward"]) ? $_POST["doc_forward"] : ""; 
$selectedoptions = isset($_POST["selectedoptions"]) ? $_POST["selectedoptions"] : ""; 

//UserInfo etails
$sender_id = isset($_POST["UserID"]) ? $_POST["UserID"] : ""; 
$sender_email = isset($_POST["UserEmail"]) ? $_POST["UserEmail"] : ""; 
$sender_office = isset($_POST["UserOffice"]) ? $_POST["UserOffice"] : ""; 

//Form Data============================================================================================================================================================
switch ($request) {

	case "forward":
	
 		$Highwayprevioustracking = execsqlSRS (
			"SELECT DISTINCT tracking_id
			FROM tbl_Highway
			WHERE highway_id = '{$doc_forward}'",
			"Select", []);
			
		$tracking_id = $Highwayprevioustracking[0]["tracking_id"];

		if (empty($selectedoptions))
		{
			echo " Please pick an office to send to...";
			break;
		}
		
		//File Validation
		if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
			$fileTmpPath = $_FILES['file']['tmp_name'];
			$fileName = $_FILES['file']['name'];
			$fileNameNew = $currentDT2."-".$sender_id."-".$fileName;

			$uploadDirectory = '../uploads/'; // Ensure this directory exists and is writable

			// Create the uploads directory if it doesn't exist
			if (!is_dir($uploadDirectory)) {
				mkdir($uploadDirectory, 0755, true);
			}

			$destination = $uploadDirectory . $fileNameNew;

			// Move the uploaded file
			move_uploaded_file($fileTmpPath, $destination);
			
//Attachment (File)
        $Attachment = execsqlSRS(
            "INSERT INTO [tbl_Attachment] (att_filename, att_filepath, att_status, att_dt)
             VALUES (:att_filename, :att_filepath, 0, :att_dt)",
            "Insert", [
						":att_filename" => $fileNameNew,
						":att_filepath" => $destination,
						":att_dt" => $currentDT,
					  ]);

		$SelectAttachmentID = execsqlSRS (
			"SELECT DISTINCT att_id, att_filename, att_filepath, att_status, att_dt
			FROM tbl_Attachment
			WHERE att_filename = :att_filename AND att_filepath = :att_filepath AND att_status = '0'",
			"Select", [
						":att_filename" => $fileNameNew,
						":att_filepath" => $destination,
					  ]);

		$attachment_id = $SelectAttachmentID[0]["att_id"];
		
//Highway (Details)

		$selected = explode(",", $selectedoptions);

		foreach ($selected as $options) {

			$Highway = execsqlSRS(
				"INSERT INTO [tbl_Highway] (sender_id, dt_released, doc_remarks, doc_attachment, doc_status, doc_active, sender_office, receiver_office)
				 VALUES ('{$sender_id}', '{$currentDT}', :doc_remarks, '{$attachment_id}', 1, 0, '{$sender_office}', '{$options}')",
				"Insert", [
						":doc_remarks" => $doc_remarks
				]);

		$Highwayselect = execsqlSRS (
			"SELECT DISTINCT sender_id, dt_released, doc_remarks, doc_attachment, sender_office, receiver_office, highway_id
			FROM tbl_Highway
			WHERE sender_id = '{$sender_id}' AND dt_released = '{$currentDT}' AND doc_remarks = :doc_remarks 
			AND doc_attachment = '{$attachment_id}' AND sender_office = '{$sender_office}' AND receiver_office = '{$options}'",
			"Select", [
					":doc_remarks" => $doc_remarks
			]);

		$highway_id = $Highwayselect[0]["highway_id"];

		$Highwayedit = execsqlSRS("
			UPDATE tbl_Highway
			SET tracking_id = '{$tracking_id}'
			WHERE highway_id = '{$highway_id}'", 
			"Update", []);
			
		}
		
			
		echo "Document Forwarded!";

		} 

		else {
//Highway (Details)

			$selected = explode(",", $selectedoptions);

			foreach ($selected as $options) {
			$Highway = execsqlSRS(
				"INSERT INTO [tbl_Highway] (sender_id, dt_released, doc_remarks, doc_status, doc_active, sender_office, receiver_office)
				 VALUES ('{$sender_id}', '{$currentDT}', :doc_remarks, 1, 0, '{$sender_office}', '{$options}')",
				"Insert", [
						":doc_remarks" => $doc_remarks
				]);

			$Highwayselect = execsqlSRS (
				"SELECT DISTINCT sender_id, dt_released, doc_remarks, sender_office, receiver_office, highway_id
				FROM tbl_Highway
				WHERE sender_id = '{$sender_id}' AND dt_released = '{$currentDT}' AND doc_remarks = :doc_remarks
				AND sender_office = '{$sender_office}' AND receiver_office = '{$options}'",
				"Select", [
						":doc_remarks" => $doc_remarks
				]);

			$highway_id = $Highwayselect[0]["highway_id"];

			$Highwayedit = execsqlSRS("
				UPDATE tbl_Highway
				SET tracking_id = '{$tracking_id}'
				WHERE highway_id = '{$highway_id}'", 
				"Update", []);
				
			}
			
				echo "Document Forwarded!";
			
		}
			
		$Highwayprevious = execsqlSRS("
		UPDATE tbl_Highway
		SET doc_status = '3'
		WHERE highway_id = '{$doc_forward}'", 
		"Update", []);
			
	break;

//=======================================================================================================================================================================
}
?>