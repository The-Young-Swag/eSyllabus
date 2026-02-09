<?php
include "../db/dbconnection.php";

// Get the request type from POST data
$request = isset($_POST["request"]) ? $_POST["request"] : "";

switch ($request) {

case "verifyLogin":
    $lgtxtpassword = isset($_POST["lgtxtpassword"]) ? $_POST["lgtxtpassword"] : "";
    $lgtxtEmail = isset($_POST["lgtxtEmail"]) ? $_POST["lgtxtEmail"] : "";

    // Debug: Log what's being received
    error_log("Login attempt - Email: $lgtxtEmail, Password: $lgtxtpassword");

    $getUA = execsqlSRS(
        "SELECT top 1 ua.UserID, ua.EmpID, ua.EmailAddress, ua.Password, ua.RID, 
                ua.Office_id, ua.Name, ua.AllOfficeAcess, ua.ChangePass
         FROM Sys_UserAccount ua
         WHERE ua.EmailAddress = :EmailAddress 
               AND ua.Password = :Password 
               AND ua.IsActive = '0'",
        "Select",
        array(
            ":EmailAddress" => $lgtxtEmail,
            ":Password" => $lgtxtpassword
        )
    );

    // Debug: Log query results
    error_log("Query result count: " . count($getUA));
    if (isset($getUA[0])) {
        error_log("User found: " . json_encode($getUA[0]));
    } else {
        error_log("No user found or not active");
    }

    if (isset($getUA[0])) {
        echo json_encode(array(
            "status" => "Registered",
            "EmpID" => $getUA[0]["EmpID"],
            "UserID" => $getUA[0]["UserID"],
            "RID" => $getUA[0]["RID"],
            "EmailAddress" => $getUA[0]["EmailAddress"],
            "Office_id" => $getUA[0]["Office_id"],
            "Name" => $getUA[0]["Name"],
            "Password" => $getUA[0]["Password"],
            "ChangePass" => $getUA[0]["ChangePass"],
            "AllOfficeAccess" => $getUA[0]["AllOfficeAcess"],
        ));
    } else {
        echo json_encode(array("status" => "unrecognized"));
    }
    break;

    case "RegNewPassword":
        
        $txtNewPassword = isset($_POST["txtNewPassword"]) ? $_POST["txtNewPassword"] : "";
        $txtRePassword = isset($_POST["txtRePassword"]) ? $_POST["txtRePassword"] : "";
        $UserID = isset($_POST["UserID"]) ? $_POST["UserID"] : "";

        $getUA = execsqlSRS(
            "SELECT top 1 ua.UserID, ua.EmailAddress, ua.Password, ua.RID, os.[OfficeID] Office_id, ua.Name
				,ua.AllOfficeAcess,ua.ChangePass
             FROM Sys_UserAccount  ua
			 left join [tbl_OfficeStaff] os on os.[EmpID] = ua.EmpID
             WHERE ua.UserID = :UserID AND ua.Password = :Password AND isActive = '0'",
            "Select",
            array(
                ":UserID" => $UserID,
                ":Password" => $txtNewPassword
            )
        );

        if (isset($getUA[0])) {
            echo json_encode(array(
                "status" => "PassExist"
            ));
        } else {
			execsqlSRS(" update Sys_UserAccount
					set [Password] = :txtNewPassword,ChangePass=0
					where UserID=:UserID",
					"Update",
					["UserID"=>$UserID,"txtNewPassword"=>$txtNewPassword]
				);
				
			 $GetUserInfo = execsqlSRS(
				"SELECT top 1 ua.UserID, ua.EmailAddress, ua.Password, ua.RID, os.[OfficeID] Office_id, ua.Name
					,ua.AllOfficeAcess,ua.ChangePass
				 FROM Sys_UserAccount  ua
				 left join [tbl_OfficeStaff] os on os.[EmpID] = ua.EmpID
				 WHERE ua.UserID = :UserID and isActive = '0'",
				"Select",[":UserID" => $UserID]);
				
				
				if (isset($GetUserInfo[0])) {
					echo json_encode(array(
						"status" => "Registered",
						"UserID" => $GetUserInfo[0]["UserID"],
						"RID" => $GetUserInfo[0]["RID"],
						"EmailAddress" => $GetUserInfo[0]["EmailAddress"],
						"Office_id" => $GetUserInfo[0]["Office_id"],
						"Name" => $GetUserInfo[0]["Name"],
						"Password" => $GetUserInfo[0]["Password"],
						"ChangePass" => $GetUserInfo[0]["ChangePass"],
						"AllOfficeAcess" => $GetUserInfo[0]["AllOfficeAcess"],
					));
				} 
        }

        break;
}
?>
