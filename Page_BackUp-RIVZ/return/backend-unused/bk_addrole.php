<?php
	date_default_timezone_set('Asia/Manila');
	include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";
$r_role = isset($_POST["r_role"]) ? $_POST["r_role"] : "";
$r_rolecode = isset($_POST["r_rolecode"]) ? $_POST["r_rolecode"] : "";
$r_status = isset($_POST["r_status"]) ? $_POST["r_status"] : "";
$RID = isset($_POST["RID"]) ? $_POST["RID"] : "";

$er_submit = isset($_POST["er_submit"]) ? $_POST["er_submit"] : "";
$er_role = isset($_POST["er_role"]) ? $_POST["er_role"] : "";
$er_rolecode = isset($_POST["er_rolecode"]) ? $_POST["er_rolecode"] : "";
$er_status = isset($_POST["er_status"]) ? $_POST["er_status"] : "";


switch ($request) {

	case "addrole":
    $ChckExist = execsqlSRS("
        SELECT TOP 1 [RID]
              ,[Role]
              ,[Rolecode]
              ,[UnActive]
          FROM [Sys_Role]
          where Role=:Role",
          "SELECT",
          [":Role" => $r_role]); 
    
    if(!isset($ChckExist[0])){    
        $queryaddrole = execsqlSRS("
        INSERT INTO [Sys_Role] (Role, Rolecode, UnActive)
        VALUES (:r_role, :r_rolecode, :r_status)", 
        "Insert", [
                    ":r_role" => $r_role, 
                    ":r_rolecode" => $r_rolecode, 
                    ":r_status" => $r_status
                  ]); 
        
        // Get the newly inserted Role ID
        $GetLastRID = execsqlSRS("
            SELECT Top 1 [RID]
              FROM [Sys_Role]
              where [Role] = :Role",
              "SELECT",
              [":Role" => $r_role]); 
        
        $RID = isset($GetLastRID[0]["RID"]) ? $GetLastRID[0]["RID"] : "";
        
        if ($RID) {
            // Get all ACTIVE menus (only where UnActive = '0')
            $GetAllMenu = execsqlSRS("
                SELECT [MenID]
                FROM [Sys_Menu]
                WHERE UnActive = '0'
                ORDER BY MenID",
                "SELECT",
                []); 
            
            // Assign all active menus to the new role
            foreach($GetAllMenu as $AllMenu){
                execsqlSRS("
                    INSERT INTO [Sys_RoleMenu] ([RID],[MenID],[UnActive])
                    VALUES (:RID, :MenID, '0')",
                    "Insert",
                    [":RID" => $RID, ":MenID" => $AllMenu["MenID"]]
                );
            }
            
            echo json_encode(["status" => "success", "message" => "Role Added and assigned to all active menus!"]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Failed to get Role ID."]);
        }
    } else {
        echo json_encode(["status" => "failed", "message" => "Role already exists."]);
    }
    break;

	case "editrole":

		$queryeditrole = execsqlSRS("
			SELECT RID, Role, Rolecode, UnActive
			FROM [Sys_Role]
			WHERE RID = :RID", 
			"Select", [
						":RID" => $RID, 
					  ]);

			foreach ($queryeditrole as $edit) {

			echo "<div class='form-group'>
					<label for='doc_remarks'>Role</label>
                    <input type='text' class='form-control' id='er_role' value='" . htmlspecialchars($edit["Role"]) . "'>
				  </div>";
			echo "<div class='form-group'>
					<label for='doc_remarks'>Rolecode</label>
                    <input type='text' class='form-control' id='er_rolecode' value='" . htmlspecialchars($edit["Rolecode"]) . "'>
				  </div>";

			echo "<div class='form-group'>
					<label for='doc_remarks'>Status</label>
                    <input type='text' class='form-control' id='er_status' value='" . htmlspecialchars($edit["UnActive"]) . "'>
				  </div>";
			echo    "<div class='form-group d-flex justify-content-center pt-2'>
						<button type='submit' id='er_submit' name='er_submit' class='btn btn-primary' value='" . htmlspecialchars($edit["RID"]) . "'>
						Save Changes</button>
					  </div>";
			}

	break;
	
// In addrole.php, change the saverole case:
case "saverole":
    // Get POST data
    $er_submit = isset($_POST["er_submit"]) ? $_POST["er_submit"] : "";
    $er_role = isset($_POST["er_role"]) ? $_POST["er_role"] : "";
    $er_rolecode = isset($_POST["er_rolecode"]) ? $_POST["er_rolecode"] : "";
    $er_status = isset($_POST["er_status"]) ? $_POST["er_status"] : "";
    
    // Execute the update - SIMPLE AND CLEAN
    $querysaverole = execsqlSRS("
        UPDATE Sys_Role
        SET Role = :er_role, Rolecode = :er_rolecode, UnActive = :er_status
        WHERE RID = :er_submit", 
        "Update", [
            ":er_role" => $er_role, 
            ":er_rolecode" => $er_rolecode, 
            ":er_status" => $er_status, 
            ":er_submit" => $er_submit
        ]); 
    
    // Return SIMPLE success message
    echo "SUCCESS";
    break;
}

?>
