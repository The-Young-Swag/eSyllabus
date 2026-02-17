<?php
	date_default_timezone_set('Asia/Manila');
	include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$u_username = isset($_POST["u_username"]) ? $_POST["u_username"] : "";
$u_password = isset($_POST["u_password"]) ? $_POST["u_password"] : "";
$u_status = isset($_POST["u_status"]) ? $_POST["u_status"] : "";
$u_role = isset($_POST["u_role"]) ? $_POST["u_role"] : "";
$u_name = isset($_POST["u_name"]) ? $_POST["u_name"] : "";
$u_unit = isset($_POST["u_unit"]) ? $_POST["u_unit"] : "";
$u_position = isset($_POST["u_position"]) ? $_POST["u_position"] : "";

$eu_submit = isset($_POST["eu_submit"]) ? $_POST["eu_submit"] : "";
$eu_email = isset($_POST["eu_email"]) ? $_POST["eu_email"] : "";
$eu_password = isset($_POST["eu_password"]) ? $_POST["eu_password"] : "";
$eu_role = isset($_POST["eu_role"]) ? $_POST["eu_role"] : "";
$eu_status = isset($_POST["eu_status"]) ? $_POST["eu_status"] : "";
$eu_name = isset($_POST["eu_name"]) ? $_POST["eu_name"] : "";
$eu_unit = isset($_POST["eu_unit"]) ? $_POST["eu_unit"] : "";
$eu_position = isset($_POST["eu_position"]) ? $_POST["eu_position"] : "";
$Plantilla = isset($_POST["Plantilla"]) ? $_POST["Plantilla"] : "";

$UserID = isset($_POST["UserID"]) ? $_POST["UserID"] : "";

switch ($request) {
// add to [tbl_OfficeStaff] and not add th office
	case "adduser":
		$chckUserExist = execsqlSRS("
			select EmpID from  [Sys_UserAccount] 
			where EmpID = :EmpID", 
			"Select", [":EmpID" => $u_username]); 
		
		if(!isset($chckUserExist[0])){
			execsqlSRS("
				INSERT INTO [Sys_UserAccount] ([EmpID],EmailAddress, Password, RID, IsActive, Name, Office_id, Position_id)
				VALUES (:EmpID,:u_username, :u_password, :u_role, :u_status, :u_name, :u_unit, :u_position)", 
				"Insert", [
							":EmpID" => $u_username, 
							":u_username" => $u_username, 
							":u_password" => $u_username, 
							":u_status" => $u_status, 
							":u_role" => $u_role, 
							":u_name" => $u_username, 
							":u_unit" => $u_unit,
							":u_position" => $u_position
						  ]); 
			$chckOfStaffID = execsqlSRS("
				select [OfStaffID] from  [tbl_OfficeStaff] 
				where EmpID = :EmpID and OfficeID=:OfficeID and PositionID=:PositionID", 
				"Select", [":EmpID" => $u_username, 
							":OfficeID" => $u_unit,
							":PositionID" => $u_position]); 
							
			if(!isset($chckOfStaffID[0])){
				execsqlSRS("
					INSERT INTO [tbl_OfficeStaff] ([OfficeID],[EmpID],[PositionID],[Plantilla])
					VALUES (:u_unit,:EmpID, :u_position, :Plantilla)", 
					"Insert", [
								":EmpID" => $u_username, 
								":u_unit" => $u_unit,
								":u_position" => $u_position
								,":Plantilla" => $Plantilla
							  ]); 
			}
			 echo json_encode(["status" => "Success", "message" => "Successfully Added!"]);
		}else{
			 echo json_encode(["status" => "failed", "message" => "User Existed!"]);
		}

	break;
	case "UpdateUser":
	
		$OfStaffID = isset($_POST["OfStaffID"]) ? $_POST["OfStaffID"] : "";
		$chckOfStaffID = execsqlSRS("
				select [OfStaffID] from  [tbl_OfficeStaff] 
				where OfStaffID = :OfStaffID", 
				"Select", [":OfStaffID" => $OfStaffID]); 
							
		if(isset($chckOfStaffID[0])){
			
			$alreadyassigned = execsqlSRS("
				select [OfStaffID] from  [tbl_OfficeStaff] 
				where EmpID = :EmpID and OfficeID=:OfficeID and PositionID=:PositionID", 
				"Select", [":EmpID" => $u_username, 
							":OfficeID" => $u_unit,
							":PositionID" => $u_position]); 
							
			if(isset($alreadyassigned[0])){
				
				 echo json_encode(["status" => "Success", "message" => "Office and position already assigned to this employee!"]);
			}else{
				execsqlSRS("
					update tbl_OfficeStaff 
					set [OfficeID]=:u_unit,PositionID=:u_position, Plantilla=:Plantilla
					where OfStaffID = :OfStaffID", 
					"Update", [
								":OfStaffID" => $OfStaffID, 
								":u_unit" => $u_unit,
								":u_position" => $u_position
								,":Plantilla" => $Plantilla
							  ]);
					
				 echo json_encode(["status" => "Success", "message" => "Office/position already assigned to this employee!"]);
				  execsqlSRS("insert into [tbl_Logs] ([UserID],[parameter],[operations],[status])
					values ('{$UserID}','OfStaffID:$OfStaffID','Update','OfficeID:{$u_unit},PositionID:{$u_position},Plantilla:{$Plantilla}')
				  ","Insert",[]); 
			}
		}else{
			
			$alreadyassigned = execsqlSRS("
				select [OfStaffID],OfficeID from  [tbl_OfficeStaff] 
				where EmpID = :EmpID and OfficeID=:OfficeID and PositionID=:PositionID", 
				"Select", [":EmpID" => $u_username, 
							":OfficeID" => $u_unit,
							":PositionID" => $u_position]); 
							
			if(isset($alreadyassigned[0])){
			
							 
				 echo json_encode(["status" => "Success", "message" => "Office and position already assigned to this employee!"]);
			}else{
				execsqlSRS("
					INSERT INTO [tbl_OfficeStaff] ([OfficeID],[EmpID],[PositionID],[Plantilla])
					VALUES (:u_unit,:EmpID, :u_position, :Plantilla)", 
					"Insert", [
								":EmpID" => $u_username, 
								":u_unit" => $u_unit,
								":u_position" => $u_position
								,":Plantilla" => $Plantilla
							  ]); 
					
				 echo json_encode(["status" => "Success", "message" => "Office/position already assigned to this employee!"]);
			}
			echo json_encode(["status" => "Success", "message" => "Successfully Update!"]);
		}
		
		execsqlSRS("
			update [Sys_UserAccount] 
			set  RID=:u_role, IsActive=:u_status, Office_id=:u_unit
			where [EmpID]=:EmpID", 
		"Update", [":EmpID" => $u_username, 
					":u_status" => $u_status, 
					":u_role" => $u_role, 
					":u_unit" => $u_unit]); 
		
	  execsqlSRS("insert into [tbl_Logs] ([UserID],[parameter],[operations],[status])
		values ('{$UserID}','OfStaffID:$OfStaffID','Update','OfficeID:{$u_unit},PositionID:{$u_position},Plantilla:{$Plantilla}')
	  ","Insert",[]); 
		

	break;

	case "edituser":
	
			//Select Role
		$selectrole = execsqlSRS("
			SELECT r.Role
			FROM Sys_UserAccount u

			LEFT JOIN Sys_Role r
			ON r.RID = u.RID

			WHERE u.UserID = :UserID", 
			"Select", [
						":UserID" => $UserID, 
					  ]); 
		$rolename = $selectrole[0]["Role"];

		//Select Unit
		$selectoffice = execsqlSRS("
			SELECT o.unit_name
			FROM Sys_UserAccount u

			LEFT JOIN tbl_Units o
			ON o.unit_id = u.Office_id

			WHERE u.UserID = :UserID", 
			"Select", [
						":UserID" => $UserID, 
					  ]); 
		$officename = $selectoffice[0]["unit_name"];
		
		//Select Unit
		$selectposition = execsqlSRS("
			SELECT p.position_desc
			FROM Sys_UserAccount u

			LEFT JOIN tbl_Positions p
			ON p.position_id = u.Position_id

			WHERE u.UserID = :UserID", 
			"Select", [
						":UserID" => $UserID, 
					  ]); 
		$positionname = $selectposition[0]["position_desc"];

		$queryedituser = execsqlSRS("
			SELECT EmailAddress, Password, RID, IsActive, Name, Office_id, UserID, Position_id
			FROM [Sys_UserAccount]
			WHERE UserID = :UserID", 
			"Select", [
						":UserID" => $UserID
					  ]); 
					  
			foreach ($queryedituser as $edit) {

			echo "<div class='form-group'>
					<label for='doc_remarks'>Email Address</label>
                    <input type='text' class='form-control' id='eu_email' value='" . htmlspecialchars($edit["EmailAddress"]) . "'>
				  </div>";
			echo "<div class='form-group'>
					<label for='doc_remarks'>Password</label>
                    <input type='text' class='form-control' id='eu_password' value='" . htmlspecialchars($edit["Password"]) . "'>
				  </div>";

				echo 
					"<div class='dropdown'>
						<label for='destination' class='pr-2'>Role:</label>
							<select class='form-select' aria-label='Default select example' name='' id='eu_role'>
								<option value='" . htmlspecialchars($edit["RID"]) . "'>" . htmlspecialchars($rolename) . "</option>";
										$q1 = execsqlSRS("
										SELECT RID, Role
										FROM Sys_Role
										WHERE UnActive = '0'
										ORDER BY RID
										", "Select", array());
										foreach ($q1 as $q1q) {
													$RID = $q1q['RID'];
													$Role = $q1q['Role'];
													echo "<option value = '$RID'>$Role</option>";
										}
				echo    "</select>
					</div>";

			echo "<div class='form-group'>
					<label for='doc_remarks'>Status</label>
                    <input type='text' class='form-control' id='eu_status' value='" . htmlspecialchars($edit["IsActive"]) . "'>
				  </div>";
			echo "<div class='form-group'>
					<label for='doc_remarks'>Name</label>
                    <input type='text' class='form-control' id='eu_name' value='" . htmlspecialchars($edit["Name"]) . "'>
				  </div>";

				echo 
					"<div class='dropdown'>
						<label for='destination' class='pr-2'>Unit:</label>
							<select class='form-select' aria-label='Default select example' name='' id='eu_unit'>
								<option value='" . htmlspecialchars($edit["Office_id"]) . "'>" . $officename . "</option>";
										$q2 = execsqlSRS("
										SELECT unit_id, unit_name
										FROM tbl_Units
										WHERE unit_status = '0'
										ORDER BY unit_name
										", "Select", array());
										foreach ($q2 as $q2q) {
													$unit_id = $q2q['unit_id'];
													$unit_name = $q2q['unit_name'];
													echo "<option value = '$unit_id'>$unit_name</option>";
										}
				echo    "</select>
					</div>";
					
				echo 
					"<div class='dropdown mt-2'>
						<label for='destination' class='pr-2'>Position:</label>
							<select class='form-select' aria-label='Default select example' name='' id='eu_position'>
								<option value='" . htmlspecialchars($edit["Position_id"]) . "'>" . $positionname . "</option>";
										$q3 = execsqlSRS("
										SELECT position_id, position_desc
										FROM tbl_Positions
										WHERE IsActive = '0'
										ORDER BY position_desc
										", "Select", array());
										foreach ($q3 as $q3q) {
													$position_id = $q3q['position_id'];
													$position_desc = $q3q['position_desc'];
													echo "<option value = '$position_id'>$position_desc</option>";
										}
				echo    "</select>
					</div>";

				echo    "<div class='form-group d-flex justify-content-center pt-2'>
				<button type='submit' id='eu_submit' name='eu_submit' class='btn btn-primary' value='" . htmlspecialchars($edit["UserID"]) . "'>
				Save Changes</button>
              </div>";
			}

	break;

	case "saveuser":

		$querysaveuser = execsqlSRS("
			UPDATE Sys_UserAccount
			SET EmailAddress = :eu_email, Password = :eu_password, RID = :eu_role, IsActive = :eu_status, Name = :eu_name, Office_id = :eu_unit,
			Position_id = :eu_position
			WHERE UserID = :eu_submit" , 
			"Update", [
						":eu_email" => $eu_email, 
						":eu_password" => $eu_password, 
						":eu_role" => $eu_role, 
						":eu_status" => $eu_status, 
						":eu_name" => $eu_name, 
						":eu_unit" => $eu_unit,
						":eu_position" => $eu_position,
						":eu_submit" => $eu_submit
					  ]); 

	echo "Changes have been Saved!";

	break;
}

?>
