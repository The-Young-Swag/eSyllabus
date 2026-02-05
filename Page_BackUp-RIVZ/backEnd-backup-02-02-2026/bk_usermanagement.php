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
			--pos.PositionId,pos.Position,
			ofc.[OfficeName],
			ofc.[OfficeMenID],
			os.[OfStaffID],
			os.[Plantilla],
			r.[Role],ua.[Office_id]
		FROM 
			[Sys_UserAccount] ua
		LEFT JOIN 
			[Sys_Role] r ON ua.RID = r.RID
		
		left join [tbl_OfficeStaff] os on os.[EmpID] = ua.[EmpID] and  os.[OfficeID]=ua.[Office_id]
		left join [Sys_Office] ofc on ofc.[OfficeMenID] = os.[OfficeID]
		--left join [db_HRIS].[dbo].[tbl_Positions] pos on pos.PositionId = os.[Position_id]
	
		
		ORDER BY 
			ua.[UserID]",
			"Search",
			array()
		);
		$color = "";
		$tbleDetails = "";
		foreach ($queryViewUsers as $user) {

			if ($user["IsActive"]) {
				$color = "danger";
			} else{
				$color = "green";
			}

			$tbleDetails .=  "<tr class='bg-$color'>";
			$tbleDetails .=  "<td>" . htmlspecialchars($user["UserID"]) . "</td>";
			$tbleDetails .=  "<td>" . htmlspecialchars($user["EmailAddress"]) . "</td>";

			$password = htmlspecialchars($user["Password"], ENT_QUOTES);

			$tbleDetails .=  "<td>
						<div class='d-flex align-items-center'>
							<input type='password' 
								   class='form-control-plaintext mb-0' 
								   value='********' 
								   data-password='" . $password . "'
								   id='password_" . $user["UserID"] . "' 
								   readonly 
								   style='width: 120px;' />

							<button type='button' 
									class='btn btn-sm btn-outline-secondary ml-2' 
									onclick='passwordToggler.toggle(" . $user["UserID"] . ")' 
									id='btn_" . $user["UserID"] . "' 
									data-toggle='tooltip' 
									data-placement='top' 
									title='Show Password'>
								<i class='fas fa-eye' id='eye_" . $user["UserID"] . "'></i>
							</button>
						</div>
					  </td>";

			$tbleDetails .=  "<td>" . htmlspecialchars($user["Name"]) . "</td>";
			$tbleDetails .=  "<td>" . htmlspecialchars($user["OfficeName"]) . "</td>";

			$getlvAllPosition = getlvAllPosition();
			$user["Position"] = isset($getlvAllPosition[0]) ? $getlvAllPosition[0]["position_id"] : "";
			$user["position_id"] = isset($getlvAllPosition[0]) ? $getlvAllPosition[0]["position_id"] : "";


			$tbleDetails .=  "<td>" . htmlspecialchars($user["Position"]) . "</td>";
			$tbleDetails .=  "<td>" . htmlspecialchars($user["Role"]) . "</td>";

			$isActive = $user["AllOfficeAcess"] == 1 ? "" : "checked";
			$statusText = $isActive ? "Active" : "Inactive";
			$tbleDetails .=  "<td>
						<label class='custom-switch'>
							<input type='checkbox' class='toggle-switch' 
								data-updateField='AllOfficeAcess'
								data-userid='{$user["UserID"]}' $isActive id='mgtActiveStat'>
							<span class='slider'></span>
						</label>
						<span class='status-text'></span>
					  </td>";

			$isActive = $user["IsActive"] == 1 ? "" : "checked";
			$statusText = $isActive ? "Active" : "Inactive";
			$tbleDetails .=  "<td>
						<label class='custom-switch'>
							<input type='checkbox' class='toggle-switch' 
								data-updateField='IsActive'
								data-userid='{$user["UserID"]}' $isActive 
								name='mgtActiveStat{$user["EmpID"]}'
								id='mgtActiveStat'>
							<span class='slider'></span>
						</label>
						<span class='status-text'></span>
					  </td>";

			$tbleDetails .=  "
                      <td style='position: relative;'>
						  <i id='ppof' 
							data-showppof = 'showppof{$user["UserID"]}'
							class='fas fa-exclamation-circle text-danger ml-2 popup-toggle' 
							style='cursor: pointer;'></i>
						  
						  <div id='showppof{$user["UserID"]}'
								class='popup-list' 
								style='display: none; position: absolute; top: 5px; right: 0; z-index: 10; 
									   background: #fff; border: 1px solid #ccc; padding: 10px; 
									   box-shadow: 0 0 10px rgba(0,0,0,0.1); 
									   width: auto; min-width: max-content; white-space: nowrap;'>
							 <div style='display: flex; justify-content: flex-end; margin-bottom: 5px; top: 5px;'>
								<button id='ppofhide' 
									onclick ='document.getElementById(\"showppof{$user["UserID"]}\").style.display=\"none\";'
									style='border: none; background: none; font-size: 16px; cursor: pointer;'>&times;</button>
							  </div>
							
							<ul style='margin: 0; padding: 0; list-style: none;'>
							  <li id='adduser'
								  data-label='Edit User'
								  data-EmpID='{$user["EmpID"]}'
								  data-PositionId='{$user["position_id"]}'
								  data-OfficeMenID='{$user["OfficeMenID"]}'
								  data-IsActive='{$user["IsActive"]}'
								  data-RID='{$user["RID"]}'
								  data-Plantilla='{$user["Plantilla"]}'
								  data-OfStaffID='{$user["OfStaffID"]}'
								  data-request='UpdateUser'>
								  <i class='fas fa-user-edit mr-2'></i> View/Edit
							  </li>

							  <li data-Edituserid='{$user["UserID"]}' id='ResetPass'>
								  <i class='fas fa-key mr-2'></i> Reset Password
							  </li>

							  <li data-EmpID='{$user["EmpID"]}' id='OfficeAccess'>
								  <i class='fas fa-building mr-2'></i> Office Access
							  </li>
							</ul>
						  </div>
						</td>";


			$tbleDetails .=  "</tr>";
		}
		$OfStaffID = isset($queryViewUsers[0]["OfStaffID"]) ? $queryViewUsers[0]["OfStaffID"] : "";
		echo $tbleDetails;
		
		/* json_encode([
			"tbleDetails" => $tbleDetails,
			"status" => true,
			"OfStaffID" => $OfStaffID
		]); */
		break;
}
