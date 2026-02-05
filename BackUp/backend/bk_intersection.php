<?php
	date_default_timezone_set('Asia/Manila');
	include "../db/dbconnection.php";

$currentDT = date("Y-m-d H:i:s");

// Get the request type from POST data
$request = isset($_POST["request"]) ? $_POST["request"] : ""; 
$doc_type = isset($_POST["doc_type"]) ? $_POST["doc_type"] : ""; 
$doc_details = isset($_POST["doc_details"]) ? $_POST["doc_details"] : ""; 
$doc_number = isset($_POST["doc_number"]) ? $_POST["doc_number"] : ""; 
$doc_amount = isset($_POST["doc_amount"]) ? $_POST["doc_amount"] : ""; 
$doc_date = isset($_POST["doc_date"]) ? $_POST["doc_date"] : ""; 
$doc_proponent = isset($_POST["doc_proponent"]) ? $_POST["doc_proponent"] : ""; 
$doc_enduser = isset($_POST["doc_enduser"]) ? $_POST["doc_enduser"] : ""; 
$doc_payee = isset($_POST["doc_payee"]) ? $_POST["doc_payee"] : ""; 

$UserID = isset($_POST["UserID"]) ? $_POST["UserID"] : ""; 

//Form Data============================================================================================================================================================
switch ($request) {

	case "insertIntersection":

		if (empty($doc_details))
		{
			echo "Please fill out at least the Details Field...";
			break;
		}

//Intersection (Details)
        $Intersection = execsqlSRS(
            "INSERT INTO [tbl_Intersection] (doc_type, doc_details, doc_number, doc_amount, doc_date, doc_proponent, doc_status, doc_issent, dt_created, UserID, doc_enduser, doc_payee)
             VALUES (:doc_type, :doc_details, :doc_number, :doc_amount, :doc_date, :doc_proponent, 0, 1, :dt_created, :UserID, :doc_enduser, :doc_payee)",
            "Insert", [
						":doc_type" => $doc_type,
						":doc_details" => $doc_details,
						":doc_number" => $doc_number,
						":doc_amount" => $doc_amount,
						":doc_date" => $doc_date,	
						":doc_proponent" => $doc_proponent,
						":dt_created" => $currentDT,
						":UserID" => $UserID,
						":doc_enduser" => $doc_enduser,
						":doc_payee" => $doc_payee
					  ]);

			echo "System: Document Created. Proceed to Forward.";
	break;

//=======================================================================================================================================================================
}
?>