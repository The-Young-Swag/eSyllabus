<?php
	date_default_timezone_set('Asia/Manila');
	include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$m_menu = isset($_POST["m_menu"]) ? $_POST["m_menu"] : "";
$m_mother = isset($_POST["m_mother"]) ? $_POST["m_mother"] : "";
$m_desc = isset($_POST["m_desc"]) ? $_POST["m_desc"] : "";
$m_code = isset($_POST["m_code"]) ? $_POST["m_code"] : "";
$m_link = isset($_POST["m_link"]) ? $_POST["m_link"] : "";
$m_arrange = isset($_POST["m_arrange"]) ? $_POST["m_arrange"] : "";
$m_icon = isset($_POST["m_icon"]) ? $_POST["m_icon"] : "";
$m_status = isset($_POST["m_status"]) ? $_POST["m_status"] : "";

$em_submit = isset($_POST["em_submit"]) ? $_POST["em_submit"] : "";
$em_menu = isset($_POST["em_menu"]) ? $_POST["em_menu"] : "";
$em_mother = isset($_POST["em_mother"]) ? $_POST["em_mother"] : "";
$em_desc = isset($_POST["em_desc"]) ? $_POST["em_desc"] : "";
$em_code = isset($_POST["em_code"]) ? $_POST["em_code"] : "";
$em_link = isset($_POST["em_link"]) ? $_POST["em_link"] : "";
$em_arrange = isset($_POST["em_arrange"]) ? $_POST["em_arrange"] : "";
$em_icon = isset($_POST["em_icon"]) ? $_POST["em_icon"] : "";
$em_status = isset($_POST["em_status"]) ? $_POST["em_status"] : "";


$MenID = isset($_POST["MenID"]) ? $_POST["MenID"] : "";

switch ($request) {

	case "addmenu":

		$ChckExist = execsqlSRS("
			SELECT Top 1 [MenID]
			  FROM [Sys_Menu]
			  where [Menu] = :m_menu"
			  ,"SELECT"
			  , [":m_menu" => $m_menu]); 
		if(!isset($ChckExist[0])){	
		
			echo json_encode(["status" => "success", "message" => "Menu Added!"]);
			$queryaddrole = execsqlSRS("
				INSERT INTO [Sys_Menu] (Menu, MotherMenID, Description, Menucode, MenuLink, Arrangement, UnActive, MenIcon)
				VALUES (:m_menu, :m_mother, :m_desc, :m_code, :m_link, :m_arrange, :m_status, :m_icon)"
				,"Insert"
				, [":m_menu" => $m_menu, ":m_mother" => $m_mother, ":m_desc" => $m_desc,":m_code" => $m_code,
					":m_link" => $m_link,":m_arrange" => $m_arrange,":m_status" => $m_status,":m_icon" => $m_icon
				  ]); 
							
			$GetLastMenID = execsqlSRS("
				SELECT Top 1 [MenID]
				  FROM [Sys_Menu]
				  where [Menu] = :m_menu"
				  ,"SELECT"
				  , [":m_menu" => $m_menu]); 
			$MenID = isset($GetLastMenID[0]["MenID"])?$GetLastMenID[0]["MenID"]:"";
			$GetAllRole = execsqlSRS("
					SELECT [RID],[Role],[Rolecode],[UnActive]
				  FROM [Sys_Role]"
				  ,"SELECT"
				  , []); 
				  
			foreach($GetAllRole as $AllRole){
				execsqlSRS("INSERT INTO [Sys_RoleMenu] ([RID],[MenID],[UnActive])
					VALUES ('{$AllRole["RID"]}','{$MenID}','1')"
				,"Insert"
				, []);
			}
		}else{
			echo json_encode(["status" => "failed", "message" => "Menu Existed."]);
		}
	//echo "Menu Added!";

	break;

	case "editmenu":

		$queryeditrole = execsqlSRS("
			SELECT MenID, Menu, MotherMenID, Description, Menucode, MenuLink, Arrangement, UnActive, MenIcon
			FROM [Sys_Menu]
			WHERE MenID = :MenID", 
			"Select", [
						":MenID" => $MenID, 
					  ]); 
					  
foreach ($queryeditrole as $edit) {

			echo "<div class='form-group'>
					<label for=''>Menu</label>
                    <input type='text' class='form-control' id='em_menu' value='" . htmlspecialchars($edit["Menu"]) . "'>
				  </div>";
			echo "<div class='form-group'>
					<label for=''>Mother Menu ID(0 if Parent)</label>
                    <input type='text' class='form-control' id='em_mother' value='" . htmlspecialchars($edit["MotherMenID"]) . "'>
				  </div>";

			echo "<div class='form-group'>
					<label for=''>Description</label>
                    <input type='text' class='form-control' id='em_desc' value='" . htmlspecialchars($edit["Description"]) . "'>
				  </div>";
			echo "<div class='form-group'>
					<label for=''>Menu Code</label>
                    <input type='text' class='form-control' id='em_code' value='" . htmlspecialchars($edit["Menucode"]) . "'>
				  </div>";
			echo "<div class='form-group'>
					<label for=''>Menu Link</label>
                    <input type='text' class='form-control' id='em_link' value='" . htmlspecialchars($edit["MenuLink"]) . "'>
				  </div>";
			echo "<div class='form-group'>
					<label for=''>Arrangement</label>
                    <input type='text' class='form-control' id='em_arrange' value='" . htmlspecialchars($edit["Arrangement"]) . "'>
				  </div>";
			echo "<div class='form-group'>
					<label for=''>Icon</label>
                    <input type='text' class='form-control' id='em_icon' value='" . htmlspecialchars($edit["MenIcon"]) . "'>
				  </div>";
			echo "<div class='form-group'>
					<label for=''>Status</label>
                    <input type='text' class='form-control' id='em_status' value='" . htmlspecialchars($edit["UnActive"]) . "'>
				  </div>";
			echo    "<div class='form-group d-flex justify-content-center pt-2'>
						<button type='submit' id='em_submit' name='em_submit' class='btn btn-primary' value='" . htmlspecialchars($edit["MenID"]) . "'>
						Save Changes</button>
					  </div>";
			}

	break;

	case "savemenu":

		$querysavemenu = execsqlSRS("
			UPDATE Sys_Menu
			SET Menu = :em_menu, MotherMenID = :em_mother, Description = :em_desc, Menucode = :em_code, MenuLink = :em_link, Arrangement = :em_arrange, 
			UnActive = :em_status, MenIcon = :em_icon
			WHERE MenID = :em_submit", 
			"Update", [
						":em_menu" => $em_menu, 
						":em_mother" => $em_mother, 
						":em_desc" => $em_desc, 
						":em_code" => $em_code,
						":em_link" => $em_link,
						":em_arrange" => $em_arrange,
						":em_status" => $em_status,
						":em_icon" => $em_icon,
						":em_submit" => $em_submit
					  ]); 

		echo "Changes have been Saved!";

	break;
	
}

?>
