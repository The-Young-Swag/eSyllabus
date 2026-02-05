<?php
	date_default_timezone_set('Asia/Manila');
	include "../db/dbconnection.php";

$currentDT = date("Y-m-d H:i:s");
$currentDT2 = date("Y-m-dH-i-s");
$currentT = date("h-i-sa");

// Get the request type from POST data
$request = isset($_POST["request"]) ? $_POST["request"] : ""; 
$doc_remarks = isset($_POST["doc_remarks"]) ? $_POST["doc_remarks"] : ""; 
$doc_receiver = isset($_POST["doc_receiver"]) ? $_POST["doc_receiver"] : ""; 
$data = isset($_POST["data"]) ? $_POST["data"] : ""; 

//UserInfo etails
$sender_id = isset($_POST["UserID"]) ? $_POST["UserID"] : ""; 
$sender_email = isset($_POST["UserEmail"]) ? $_POST["UserEmail"] : ""; 
$sender_office = isset($_POST["UserOffice"]) ? $_POST["UserOffice"] : ""; 

//Edit
$foredit_desc = isset($_POST["foredit_desc"]) ? $_POST["foredit_desc"] : ""; 
$foredit_number = isset($_POST["foredit_number"]) ? $_POST["foredit_number"] : ""; 
$foredit_date = isset($_POST["foredit_date"]) ? $_POST["foredit_date"] : ""; 
$foredit_proponent = isset($_POST["foredit_proponent"]) ? $_POST["foredit_proponent"] : ""; 
$foredit_amount = isset($_POST["foredit_amount"]) ? $_POST["foredit_amount"] : ""; 


//Form Data============================================================================================================================================================
switch ($request) {

	case "forward":

		if (empty($doc_receiver))
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
		
		//Edit
		$Editcontentatt = execsqlSRS("
			UPDATE tbl_Intersection
			SET 	doc_details = :foredit_desc,
					doc_number = :foredit_number,
					doc_date = :foredit_date,
					doc_proponent = :foredit_proponent,
					doc_amount = :foredit_amount
			WHERE tracking_id = '{$data}'", 
			"Update", [
						":foredit_desc" => $foredit_desc,
						":foredit_number" => $foredit_number,
						":foredit_date" => $foredit_date,
						":foredit_proponent" => $foredit_proponent,
						":foredit_amount" => $foredit_amount
					  ]);	
		
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

		$document_receiver = explode(",",$doc_receiver);
		
		foreach ($document_receiver as $receiver) {
			
        $Highway = execsqlSRS(
            "INSERT INTO [tbl_Highway] (sender_id, dt_released, doc_remarks, doc_attachment, doc_status, doc_active, sender_office, receiver_office, tracking_id)
             VALUES ('{$sender_id}', '{$currentDT}', :doc_remarks, '{$attachment_id}', 1, 0, '{$sender_office}', '{$receiver}', '{$data}')",
            "Insert", [
				":doc_remarks" => $doc_remarks
			]);	
			
		}
			
		echo "Draft Forwarded!";

	} 
	
	else {

		//Edit
		$Editcontent = execsqlSRS("
			UPDATE tbl_Intersection
			SET 	doc_details = :foredit_desc,
					doc_number = :foredit_number,
					doc_date = :foredit_date,
					doc_proponent = :foredit_proponent,
					doc_amount = :foredit_amount
			WHERE tracking_id = '{$data}'", 
			"Update", [
						":foredit_desc" => $foredit_desc,
						":foredit_number" => $foredit_number,
						":foredit_date" => $foredit_date,
						":foredit_proponent" => $foredit_proponent,
						":foredit_amount" => $foredit_amount
					  ]);	

//Highway (Details)

		$document_receiver = explode(",",$doc_receiver);
		
		foreach ($document_receiver as $receiver) {
			
        $Highway = execsqlSRS(
            "INSERT INTO [tbl_Highway] (sender_id, dt_released, doc_remarks, doc_status, doc_active, sender_office, receiver_office, tracking_id)
             VALUES ('{$sender_id}', '{$currentDT}', :doc_remarks, 1, 0, '{$sender_office}', '{$receiver}', '{$data}')",
            "Insert", [
				":doc_remarks" => $doc_remarks
			]);	
			
		}

		echo "Draft Forwarded!";
			
	}
	
		$Intersectionedit = execsqlSRS("
			UPDATE tbl_Intersection
			SET doc_issent = '0'
			WHERE tracking_id = '{$data}'", 
			"Update", []);	
			
	break;

//=======================================================================================================================================================================
}
?>