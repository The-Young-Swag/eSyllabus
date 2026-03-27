<?php
include "../db/dbconnection.php";

// Get the request type from POST data
$request = isset($_POST["request"]) ? $_POST["request"] : "";

switch ($request) {

case "verifyLogin":
    $lgtxtpassword = $_POST["lgtxtpassword"] ?? "";
    $lgtxtEmail    = $_POST["lgtxtEmail"] ?? "";

    error_log("Login attempt - Email: $lgtxtEmail, Password: $lgtxtpassword");

    try {
        $getUA = execsqlSRS(
            "SELECT TOP 1 ua.UserID, ua.EmpID, ua.EmailAddress, ua.Password, ua.RID, 
                    ua.Office_id, ua.Name, ua.AllOfficeAcess, ua.ChangePass
             FROM Sys_UserAccount ua
             WHERE ua.EmailAddress = :EmailAddress 
                   AND ua.Password = :Password 
                   AND ua.IsActive = '0'
                   AND (ua.IsDeleted IS NULL OR ua.IsDeleted = 0)",
            "Select",
            [":EmailAddress" => $lgtxtEmail, ":Password" => $lgtxtpassword]
        );

        error_log("Query executed. Result type: " . gettype($getUA));

        if (is_array($getUA) && isset($getUA[0])) {
            error_log("User found: " . json_encode($getUA[0]));
            echo json_encode([
                "status"        => "Registered",
                "EmpID"         => $getUA[0]["EmpID"],
                "UserID"        => $getUA[0]["UserID"],
                "RID"           => $getUA[0]["RID"],
                "EmailAddress"  => $getUA[0]["EmailAddress"],
                "Office_id"     => $getUA[0]["Office_id"],
                "Name"          => $getUA[0]["Name"],
                "Password"      => $getUA[0]["Password"],
                "ChangePass"    => $getUA[0]["ChangePass"],
                "AllOfficeAccess" => $getUA[0]["AllOfficeAcess"], // note JSON key vs column name
            ]);
        } elseif ($getUA === "No DB") {
            error_log("Database connection failed");
            echo json_encode(["status" => "error", "message" => "Database connection failed"]);
        } else {
            error_log("No user found or query returned empty");
            echo json_encode(["status" => "unrecognized"]);
        }
    } catch (Exception $e) {
        error_log("Exception in login: " . $e->getMessage());
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
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
