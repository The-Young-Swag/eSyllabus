<?php
	date_default_timezone_set('Asia/Manila');
	include "../db/dbconnection.php";

$currentDT = date("Y-m-d H:i:s");
$currentDT2 = date("Y-m-dH-i-s");
$currentT = date("h-i-sa");

// Get the request type from POST data
$request = isset($_POST["request"]) ? $_POST["request"] : ""; 
$doc_type = isset($_POST["doc_type"]) ? $_POST["doc_type"] : ""; 
$doc_details = isset($_POST["doc_details"]) ? $_POST["doc_details"] : ""; 
$doc_number = isset($_POST["doc_number"]) ? $_POST["doc_number"] : ""; 
$doc_amount = isset($_POST["doc_amount"]) ? $_POST["doc_amount"] : ""; 
$doc_date = isset($_POST["doc_date"]) ? $_POST["doc_date"] : ""; 
$doc_proponent = isset($_POST["doc_proponent"]) ? $_POST["doc_proponent"] : ""; 
$doc_remarks = isset($_POST["doc_remarks"]) ? $_POST["doc_remarks"] : ""; 
$doc_receiver = isset($_POST["doc_receiver"]) ? $_POST["doc_receiver"] : ""; 
$doc_enduser = isset($_POST["doc_enduser"]) ? $_POST["doc_enduser"] : ""; 
$doc_payee = isset($_POST["doc_payee"]) ? $_POST["doc_payee"] : ""; 

//UserInfo etails
$sender_id = isset($_POST["UserID"]) ? $_POST["UserID"] : ""; 
$sender_email = isset($_POST["UserEmail"]) ? $_POST["UserEmail"] : ""; 
$sender_office = isset($_POST["UserOffice"]) ? $_POST["UserOffice"] : ""; 


//Form Data============================================================================================================================================================
switch ($request) {

	case "insertHighway":
	
//Select Intersection (Details)
		$SelectTrackingID = execsqlSRS (
			"SELECT TOP 1 tracking_id, doc_type, doc_details, doc_number, doc_amount, doc_date, doc_proponent, doc_status, doc_enduser, doc_payee
			FROM tbl_Intersection
			WHERE doc_type = :doc_type AND doc_details = :doc_details AND doc_number = :doc_number AND doc_amount = :doc_amount 
			AND doc_date = :doc_date AND doc_proponent = :doc_proponent AND doc_enduser = :doc_enduser AND doc_payee = :doc_payee
			ORDER BY tracking_id DESC",
			"Select", [
						":doc_type" => $doc_type,
						":doc_details" => $doc_details,
						":doc_number" => $doc_number,
						":doc_amount" => $doc_amount,
						":doc_date" => $doc_date,	
						":doc_proponent" => $doc_proponent,
						":doc_enduser" => $doc_enduser,
						":doc_payee" => $doc_payee
					  ]);

		$tracking_id = $SelectTrackingID[0]["tracking_id"];
	
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
			
			//Highway (Data Sending/Receiving)	
			
			$document_receiver = explode(",",$doc_receiver);
			
			foreach ($document_receiver as $receiver) {
			
			$Highway = execsqlSRS(
				"INSERT INTO [tbl_Highway] (tracking_id, sender_id, receiver_office, dt_released, doc_status, doc_active, doc_remarks, doc_attachment, sender_office)
				 VALUES (:tracking_id, :sender_id, :receiver, :dt_released, 1, 0, :doc_remarks, :doc_attachment, :sender_office)",
				"Insert", [
							":tracking_id" => $tracking_id,
							":sender_id" => $sender_id,
							":receiver" => $receiver,
							":dt_released" => $currentDT,
							":doc_remarks" => $doc_remarks,
							":doc_attachment" => $attachment_id,
							":sender_office" => $sender_office,
						  ]);
			}
			
			echo "The Process Started Successfully!";

		}
		
		else {
			
			$document_receiver = explode(",",$doc_receiver);
			
			foreach ($document_receiver as $receiver) {
				$Highway = execsqlSRS(
					"INSERT INTO [tbl_Highway] (tracking_id, sender_id, receiver_office, dt_released, doc_status, doc_active, doc_remarks, sender_office)
					 VALUES (:tracking_id, :sender_id, :receiver, :dt_released, 1, 0, :doc_remarks, :sender_office)",
					"Insert", [
								":tracking_id" => $tracking_id,
								":sender_id" => $sender_id,
								":receiver" => $receiver,
								":dt_released" => $currentDT,
								":doc_remarks" => $doc_remarks,
								":sender_office" => $sender_office
							  ]);
			}
			
			echo "The Process Started Successfully!";
		}
			
		//Change the issent to 0 in Intersection
		$updateissent = execsqlSRS (
			"UPDATE [tbl_Intersection]
			SET doc_issent = '0'
			WHERE tracking_id = :tracking_id",
			"Update", [
						":tracking_id" => $tracking_id,
					  ]);
	
			
	break;

//=======================================================================================================================================================================
}
?>