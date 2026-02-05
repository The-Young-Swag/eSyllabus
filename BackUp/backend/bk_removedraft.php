<?php
	date_default_timezone_set('Asia/Manila');
	include "../db/dbconnection.php";

$currentDT = date("Y-m-d H:i:s");
$currentDT2 = date("Y-m-dH-i-s");
$currentT = date("h-i-sa");

// Get the request type from POST data
$request = isset($_POST["request"]) ? $_POST["request"] : ""; 
$data = isset($_POST["data"]) ? $_POST["data"] : ""; 

//Form Data============================================================================================================================================================
switch ($request) {

	case "removedraft":
			
		$removedraft = execsqlSRS("
			UPDATE tbl_Intersection
			SET doc_issent = '2'
			WHERE tracking_id = '{$data}'", 
			"Update", []);

			echo json_encode([
			"System Message:" => "Draft Removed!"]); 
			
	break;

//=======================================================================================================================================================================
}
?>