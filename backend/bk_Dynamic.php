<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$userId = isset($_POST["userId"]) ? $_POST["userId"] : "";

switch ($request) {
	case "Update":
	
		$table = isset($_POST["table"]) ? $_POST["table"] : "Sys_UserAccount";
		$UpFld = isset($_POST["UpFld"]) ? $_POST["UpFld"] : "";
		$Upval = isset($_POST["Upval"]) ? $_POST["Upval"] : "";
		$FltFld = isset($_POST["FltFld"]) ? $_POST["FltFld"] : "";
		$FltID = isset($_POST["FltID"]) ? $_POST["FltID"] : "";
		
		execsqlSRS(" update $table
			set {$UpFld} =:Upval
			where $FltFld =:FltFld",
			"Update",
			["Upval"=>$Upval,"FltFld"=>$FltID]
		);
		 
		execsqlSRS("insert into [tbl_Logs] ([UserID],[parameter],[operations],[status])
		values ('{$userId}','{$UpFld}:$Upval,{$FltFld}:$FltID','Menu Active Status','success')
		","Insert",[]); 
	break;
}

?>
