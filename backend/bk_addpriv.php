<?php
	date_default_timezone_set('Asia/Manila');
	include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$p_role = isset($_POST["p_role"]) ? $_POST["p_role"] : "";
$p_menu = isset($_POST["p_menu"]) ? $_POST["p_menu"] : "";
$p_status = isset($_POST["p_status"]) ? $_POST["p_status"] : "";

$ep_submit = isset($_POST["ep_submit"]) ? $_POST["ep_submit"] : "";
$ep_role = isset($_POST["ep_role"]) ? $_POST["ep_role"] : "";
$ep_menu = isset($_POST["ep_menu"]) ? $_POST["ep_menu"] : "";
$ep_status = isset($_POST["ep_status"]) ? $_POST["ep_status"] : "";

$URID = isset($_POST["URID"]) ? $_POST["URID"] : "";

switch ($request) {

	case "addpriv":

		$queryaddpriv = execsqlSRS("
			INSERT INTO [Sys_RoleMenu] (RID, MenID, UnActive)
		VALUES (:p_role, :p_menu, :p_status)", 
			"Insert", [
						":p_role" => $p_role, 
						":p_menu" => $p_menu, 
						":p_status" => $p_status,
					  ]); 	

	echo "Privilege Added!";

	break;

	case "editpriv":
	
	$querypriv = execsqlSRS("
	SELECT RID, MenID
	FROM Sys_RoleMenu
	WHERE URID = :URID", 
	"Select", [
				":URID" => $URID, 
			  ]); 

	$qrid = $querypriv[0]["RID"];
	$qmenu = $querypriv[0]["MenID"];

	$querypriv = execsqlSRS("
		SELECT Role
		FROM Sys_Role
		
		WHERE RID = :qrid", 
		"Select", [
					":qrid" => $qrid, 
				  ]); 
	$realrid = $querypriv[0]["Role"];

	$querymenu = execsqlSRS("
		SELECT Menu
		FROM Sys_Menu
		
		WHERE MenID = :qmenu", 
		"Select", [
					":qmenu" => $qmenu, 
				  ]); 
	$realmenu = $querymenu[0]["Menu"];

		$queryeditpriv = execsqlSRS("
			SELECT URID, RID, MenID, UnActive
			FROM [Sys_RoleMenu]
			WHERE URID = :URID", 
			"Select", [
						":URID" => $URID, 
					  ]); 

			foreach ($queryeditpriv as $edit) {

				echo 
					"<div class='dropdown'>
						<label for='destination' class='pr-2'>Role:</label>
							<select class='form-select' aria-label='Default select example' name='' id='ep_role'>
								<option value='" . htmlspecialchars($edit["RID"]) . "'>" . $realrid . "</option>";
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

				echo 
					"<div class='dropdown'>
						<label for='destination' class='pr-2'>Menu:</label>
							<select class='form-select' aria-label='Default select example' name='' id='ep_menu'>
								<option value='" . htmlspecialchars($edit["MenID"]) . "'>" . $realmenu . "</option>";
										$q2 = execsqlSRS("
										SELECT MenID, Menu
										FROM Sys_Menu
										WHERE UnActive = '0'
										ORDER BY MenID
										", "Select", array());
										foreach ($q2 as $q2q) {
													$MenID = $q2q['MenID'];
													$Menu_name = $q2q['Menu'];
													echo "<option value = '$MenID'>$Menu_name</option>";
										}
				echo    "</select>
					</div>";
					
			echo "<div class='form-group'>
					<label for='doc_remarks'>Status</label>
                    <input type='text' class='form-control' id='ep_status' value='" . htmlspecialchars($edit["UnActive"]) . "'>
				  </div>";
			echo    "<div class='form-group d-flex justify-content-center pt-2'>
						<button type='submit' id='ep_submit' name='ep_submit' class='btn btn-primary' value='" . htmlspecialchars($edit["URID"]) . "'>
						Save Changes</button>
					  </div>";
			}
			
	break;
	
	case "savepriv":

		$querysavepriv = execsqlSRS("
			UPDATE Sys_RoleMenu
			SET RID = :ep_role, MenID = :ep_menu, UnActive = :ep_status
			WHERE URID = :ep_submit", 
			"Update", [
						":ep_role" => $ep_role, 
						":ep_menu" => $ep_menu, 
						":ep_status" => $ep_status, 
						":ep_submit" => $ep_submit,
					  ]); 

		echo "Changes have been Saved!";

	break;
}
?>
