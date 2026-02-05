<?php
include "../db/dbconnection.php";
include "../functions/lvfunction.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$userId = isset($_POST["userId"]) ? $_POST["userId"] : "";
$chval = isset($_POST["chval"]) ? $_POST["chval"] : "";
$updateField = isset($_POST["updateField"]) ? $_POST["updateField"] : "";
$lgtxtpassword = isset($_POST["lgtxtpassword"]) ? $_POST["lgtxtpassword"] : "";
$Edituserid = isset($_POST["Edituserid"]) ? $_POST["Edituserid"] : "";

switch ($request) {
	case "AUAOffice":
		$EditEmpID = isset($_POST["EditEmpID"]) ? $_POST["EditEmpID"] : "";
		$EditOfficeID = isset($_POST["EditOfficeID"]) ? $_POST["EditOfficeID"] : "";
		$where = "";
		$checkExist = execsqlSRS(
			"Select * from [Sys_UserOfficeAccess] 
					where EmpID = :EmpID and OfficeID=:OfficeID",
			"Select",
			["EmpID" => $EditEmpID, "OfficeID" => $EditOfficeID]
		);
		if (!isset($checkExist[0])) {
			execsqlSRS(
				"insert into [Sys_UserOfficeAccess] 
						(EmpID,OfficeID)
						values
						(:EmpID,:OfficeID)",
				"Insert",
				["EmpID" => $EditEmpID, "OfficeID" => $EditOfficeID]
			);

			execsqlSRS("insert into [tbl_Logs] ([UserID],[parameter],[operations],[status])
					values ('{$userId}','[Sys_UserOfficeAccess] EmpID={$EditEmpID} and OfficeID={$EditOfficeID}','New Access Office','success')
				  ", "Insert", []);

			$where = "and OfficeID='{$EditOfficeID}'";
			echo json_encode(["status" => "success", "where" => $where]);
		} else {
			echo json_encode(["status" => "failed", "where" => Null]);
		}
		break;
        
    case "rvmUAOffice":
		$EditEmpID = isset($_POST["EditEmpID"]) ? $_POST["EditEmpID"] : "";
		$EditOfficeID = isset($_POST["EditOfficeID"]) ? $_POST["EditOfficeID"] : "";

		execsqlSRS(
			"delete
				  FROM [Sys_UserOfficeAccess] 
				where EmpID=:EmpID and OfficeID=:OfficeID",
			"Delete",
			["EmpID" => $EditEmpID, "OfficeID" => $EditOfficeID]
		);

		execsqlSRS("insert into [tbl_Logs] ([UserID],[parameter],[operations],[status])
			values ('{$userId}','delete FROM [Sys_UserOfficeAccess] EmpID={$EditEmpID} and OfficeID={$EditOfficeID}','Remove','success')
		  ", "Insert", []);

		break;case "rvmUAOffice":
		$EditEmpID = isset($_POST["EditEmpID"]) ? $_POST["EditEmpID"] : "";
		$EditOfficeID = isset($_POST["EditOfficeID"]) ? $_POST["EditOfficeID"] : "";

		execsqlSRS(
			"delete
				  FROM [Sys_UserOfficeAccess] 
				where EmpID=:EmpID and OfficeID=:OfficeID",
			"Delete",
			["EmpID" => $EditEmpID, "OfficeID" => $EditOfficeID]
		);

		execsqlSRS("insert into [tbl_Logs] ([UserID],[parameter],[operations],[status])
			values ('{$userId}','delete FROM [Sys_UserOfficeAccess] EmpID={$EditEmpID} and OfficeID={$EditOfficeID}','Remove','success')
		  ", "Insert", []);

		break;
        
    case "GetUserOfficeAccess":
		$EditEmpID = isset($_POST["EditEmpID"]) ? $_POST["EditEmpID"] : "";
		$where = isset($_POST["where"]) ? $_POST["where"] : "";

		$UserOfficeAccess = execsqlSRS(
			"SELECT [EmpID],[OfficeID],ofc.[OfficeName]
				  FROM [Sys_UserOfficeAccess] ua
				  left join [Sys_Office] ofc on ofc.[OfficeMenID] = ua.[OfficeID]
				where EmpID=:EmpID $where",
			"select",
			["EmpID" => $EditEmpID]
		);
		foreach ($UserOfficeAccess as $UserOffAccess) {
			echo "
				<div class='form-group border rounded p-2 mt-3 d-flex justify-content-between align-items-center'>
					<div class='d-flex align-items-center'>
						<i class='fas fa-building text-primary mr-2'></i>
						<label for='' class='mb-0'>{$UserOffAccess["OfficeName"]}</label>
					</div>
					<button type='button' id='rvmUAOffice'
							name='rvmUAOffice{$UserOffAccess["OfficeID"]}'
							class='btn btn-sm btn-danger' 
							data-EmpID='{$UserOffAccess["EmpID"]}' 
							data-OfficeID='{$UserOffAccess["OfficeID"]}' 
						>✖</button>
				</div>
				";
		}
		break;
        
    case "Update":
		execsqlSRS(
			" update Sys_UserAccount
				set {$updateField} =:IsActive
				where UserID=:UserID",
			"Update",
			["IsActive" => $chval, "UserID" => $userId]
		);
		break;
        
    case "chckExistPosition":
		$chckExistPosition = execsqlSRS("SELECT OfStaffID,[OfficeID],[EmpID],[PositionID],[Plantilla]
			  FROM [tbl_OfficeStaff]
				where EmpID	= :EmpID
			  ", "Select", [":EmpID" => $userId]);

		if (isset($chckExistPosition[0])) {
			echo json_encode([
				"status" => "Success",
				"u_unit" => $chckExistPosition[0]["OfficeID"],
				"u_position" => $chckExistPosition[0]["PositionID"]
			]);
		} else {
			echo json_encode(["status" => "failed", "message" => "Password not recognized."]);
		}

		break;
        
    case "ResetPass":
		$veifyUserpassword = execsqlSRS("SELECT [EmpId]
			  FROM [Sys_UserAccount]
			  where [UserID]=:UserID and [Password]=:Password
			  ", "Select", [":UserID" => $userId, ":Password" => $lgtxtpassword]);

		if (isset($veifyUserpassword[0])) {
			execsqlSRS(
				" update Sys_UserAccount
					set [Password] = [EmpId],ChangePass=1
					where UserID=:UserID",
				"Update",
				["UserID" => $Edituserid]
			);

			execsqlSRS("insert into [tbl_Logs] ([UserID],[parameter],[operations],[status])
					values ('{$userId}','Edituserid:$Edituserid','Reset Password','success')
				  ", "Insert", []);

			echo json_encode(["status" => "Success", "message" => "Successfully Reset!"]);
		} else {

			execsqlSRS("insert into [tbl_Logs] ([UserID],[parameter],[operations],[status])
					values ('{$userId}','Edituserid:$Edituserid','Reset Password','Failed:Password not recognized')
				  ", "Insert", []);

			echo json_encode(["status" => "failed", "message" => "Password not recognized."]);
		}


		break;
        
    case "viewUsers":
        $queryViewUsers = execsqlSRS(
            "
            SELECT 
                ua.[EmpID], 
                ua.[UserID], 
                ua.[EmailAddress], 
                ua.[Password], 
                ua.[Name], 
                ua.[RID], 
                ua.[IsActive], 
                ua.[AllOfficeAcess], 
                ofc.[OfficeName],
                ofc.[OfficeMenID],
                os.[OfStaffID],
                os.[Plantilla],
                r.[Role],
                ua.[Office_id]
            FROM 
                [Sys_UserAccount] ua
            LEFT JOIN 
                [Sys_Role] r ON ua.RID = r.RID
            LEFT JOIN 
                [tbl_OfficeStaff] os ON os.[EmpID] = ua.[EmpID] AND os.[OfficeID] = ua.[Office_id]
            LEFT JOIN 
                [Sys_Office] ofc ON ofc.[OfficeMenID] = os.[OfficeID]
            ORDER BY 
                ua.[UserID]",
            "Search",
            array()
        );
        
        $tbleDetails = "";
        $counter = 1;
        
        foreach ($queryViewUsers as $user) {
            // Determine status color
            $statusClass = $user["IsActive"] == 1 ? "table-danger" : "table-success";
            
            $tbleDetails .= "<tr class='{$statusClass}'>";
            $tbleDetails .= "<td>" . $counter . "</td>";
            $tbleDetails .= "<td>" . htmlspecialchars($user["EmpID"]) . "</td>";
            
            // Password field with show/hide toggle
            $password = htmlspecialchars($user["Password"], ENT_QUOTES);
            $tbleDetails .= "<td>
                        <div class='d-flex align-items-center'>
                            <input type='password' 
                                   class='form-control-plaintext mb-0' 
                                   value='" . str_repeat('*', min(8, strlen($password))) . "' 
                                   data-password='" . $password . "'
                                   id='password_" . $user["UserID"] . "' 
                                   readonly 
                                   style='width: 120px;' />
                            <button type='button' 
                                    class='btn btn-sm btn-outline-secondary ml-2' 
                                    onclick='togglePassword(" . $user["UserID"] . ")' 
                                    id='btn_" . $user["UserID"] . "' 
                                    data-toggle='tooltip' 
                                    title='Show Password'>
                                <i class='fas fa-eye' id='eye_" . $user["UserID"] . "'></i>
                            </button>
                        </div>
                      </td>";
            
            $tbleDetails .= "<td>" . htmlspecialchars($user["Name"]) . "</td>";
            $tbleDetails .= "<td>" . (!empty($user["OfficeName"]) ? htmlspecialchars($user["OfficeName"]) : "N/A") . "</td>";
            
            
            $position = "N/A"; 
            $tbleDetails .= "<td>" . htmlspecialchars($position) . "</td>";
            
            $tbleDetails .= "<td>" . htmlspecialchars($user["Role"]) . "</td>";
            $tbleDetails .= "<td>" . htmlspecialchars($user["EmailAddress"]) . "</td>";
            
            // Status toggle switch
             $isActiveChecked = $user["IsActive"] == 1 ? "" : "checked";
$tbleDetails .= "<td class='text-center'>
    <div class='custom-control custom-switch'>
        <input type='checkbox' 
               class='custom-control-input toggleStatus' 
               id='status_" . $user["UserID"] . "' 
               data-id='" . $user["UserID"] . "' 
               " . $isActiveChecked . ">  
        <label class='custom-control-label' 
               for='status_" . $user["UserID"] . "'></label>
    </div>
</td>";
            
            // Action buttons
            $tbleDetails .= "<td class='text-center'>
                        <button class='btn btn-sm btn-info btn-edit' 
                                data-userid='" . $user["UserID"] . "'
                                data-empId='" . $user["EmpID"] . "'
                                data-toggle='modal' 
                                data-target='#mdlEmployeeEdit'>
                            <i class='fas fa-edit'></i>
                        </button>
                        <button class='btn btn-sm btn-danger btn-delete' 
                                data-id='" . $user["UserID"] . "'>
                            <i class='fas fa-trash'></i>
                        </button>
                      </td>";
            
            $tbleDetails .= "</tr>";
            $counter++;
        }
        
        echo $tbleDetails;
        break;
        
    default:
        echo json_encode(["status" => "error", "message" => "Invalid request"]);
        break;
}
?>