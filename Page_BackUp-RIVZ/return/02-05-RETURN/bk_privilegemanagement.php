<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";

switch ($request) {
    case "Update":
        updatePrivilege();
        break;
        
    case "showPrvMenAcc":
        showRoleMenuAccess();
        break;
        
    case "GetRole":
        getRoles();
        break;
        
    case "GetRoleInfo":
        getRoleInfo();
        break;
        
    case "showtblData":
        showPrivilegeTable();
        break;
        
    case "syncRoleMenu":
        syncRoleMenu();
        break;
}

function updatePrivilege() {
    global $userId;
    
    $table = isset($_POST["table"]) ? $_POST["table"] : "Sys_RoleMenu";
    $UpFld = isset($_POST["UpFld"]) ? $_POST["UpFld"] : "";
    $Upval = isset($_POST["Upval"]) ? $_POST["Upval"] : "";
    $FltFld = isset($_POST["FltFld"]) ? $_POST["FltFld"] : "";
    $FltID = isset($_POST["FltID"]) ? $_POST["FltID"] : "";
    $RID = isset($_POST["RID"]) ? $_POST["RID"] : "";
    
    // Check if this role-menu combination exists
    $checkSql = "SELECT COUNT(*) as count FROM $table WHERE RID = ? AND MenID = ?";
    $checkResult = execsqlSRS($checkSql, "Search", [$RID, $FltID]);
    
    if ($checkResult[0]['count'] == 0) {
        // Insert new record if doesn't exist
        $insertSql = "INSERT INTO $table (RID, MenID, UnActive) VALUES (?, ?, ?)";
        execsqlSRS($insertSql, "Insert", [$RID, $FltID, $Upval]);
    } else {
        // Update existing record
        $updateSql = "UPDATE $table SET $UpFld = ? WHERE RID = ? AND MenID = ?";
        execsqlSRS($updateSql, "Update", [$Upval, $RID, $FltID]);
    }
    
    // Log the action
    execsqlSRS("INSERT INTO [tbl_Logs] ([UserID],[parameter],[operations],[status])
                VALUES (?, ?, ?, 'success')",
                "Insert", 
                [$userId, "Role:$RID, Menu:$FltID, Status:$Upval", "Privilege Update"]);
    
    echo "SUCCESS";
}

function showRoleMenuAccess() {
    $RID = isset($_POST["RID"]) ? $_POST["RID"] : "";
    
    if (empty($RID)) {
        echo json_encode([]);
        return;
    }
    
    $sql = "SELECT rm.MenID, rm.UnActive 
            FROM Sys_RoleMenu rm 
            WHERE rm.RID = ?";
    
    $result = execsqlSRS($sql, "Search", [$RID]);
    
    $arr = [];
    foreach ($result as $row) {
        $arr[] = array(
            "MenID" => $row["MenID"],
            "UnActive" => ($row["UnActive"] == 0) // true if active (0), false if inactive (1)
        );
    }
    
    echo json_encode($arr);
}

function getRoles() {
    $sql = "SELECT RID, Role, Rolecode, UnActive 
            FROM Sys_Role 
            ORDER BY Role";
    
    $result = execsqlSRS($sql, "Search", []);
    
    echo "<option value=''>-- Select Role --</option>";
    foreach ($result as $role) {
        $selected = "";
        $status = $role['UnActive'] == 0 ? ' (Active)' : ' (Inactive)';
        echo "<option value='{$role["RID"]}'>{$role["Role"]}$status</option>";
    }
}

function getRoleInfo() {
    $roleID = isset($_POST["roleID"]) ? $_POST["roleID"] : "";
    
    if (empty($roleID)) {
        echo json_encode(["error" => "No role ID provided"]);
        return;
    }
    
    $sql = "SELECT RID, Role, Rolecode, UnActive 
            FROM Sys_Role 
            WHERE RID = ?";
    
    $result = execsqlSRS($sql, "Search", [$roleID]);
    
    if (!empty($result)) {
        echo json_encode($result[0]);
    } else {
        echo json_encode(["error" => "Role not found"]);
    }
}

function showPrivilegeTable() {
    $roleID = isset($_POST["roleID"]) ? $_POST["roleID"] : "";
    
    // Get all menus
    $sql = "SELECT MenID, Menu, Menucode, Description, MotherMenID, Arrangement 
            FROM Sys_Menu 
            ORDER BY MotherMenID, Arrangement, Menu";
    
    $menus = execsqlSRS($sql, "Search", []);
    
    if (empty($menus)) {
        echo "<tr><td colspan='6' class='text-center text-muted'>No menus found</td></tr>";
        return;
    }
    
    $output = "";
    $counter = 1;
    
    foreach ($menus as $menu) {
        $indent = "";
        if ($menu['MotherMenID'] > 0) {
            $indent = "<i class='fas fa-long-arrow-alt-right mr-2 text-secondary'></i> ";
        }
        
        $menuCode = htmlspecialchars($menu['Menucode']);
        $description = htmlspecialchars($menu['Description']);
        if (empty($description)) {
            $description = "<span class='text-muted'>No description</span>";
        }
        
        $output .= "<tr>";
        $output .= "<td>$counter</td>";
        $output .= "<td>$indent" . htmlspecialchars($menu['Menu']) . "</td>";
        $output .= "<td><code>$menuCode</code></td>";
        $output .= "<td>$description</td>";
        $output .= "<td class='text-center'>";
        $output .= "<div class='custom-control custom-switch'>";
        $output .= "<input type='checkbox' 
                         class='custom-control-input toggle-switch' 
                         id='menuAccess_{$menu['MenID']}'
                         data-menid='{$menu['MenID']}'
                         data-menuname='" . htmlspecialchars($menu['Menu']) . "'>";
        $output .= "<label class='custom-control-label' for='menuAccess_{$menu['MenID']}'></label>";
        $output .= "</div>";
        $output .= "</td>";
        $output .= "<td class='text-center'><span class='status-text text-muted'>-</span></td>";
        $output .= "</tr>";
        
        $counter++;
    }
    
    echo $output;
}

function syncRoleMenu() {
    // This function can be called when new roles or menus are added
    // to ensure all combinations exist in Sys_RoleMenu
    
    // Get all roles
    $roles = execsqlSRS("SELECT RID FROM Sys_Role", "Search", []);
    
    // Get all menus
    $menus = execsqlSRS("SELECT MenID FROM Sys_Menu", "Search", []);
    
    foreach ($roles as $role) {
        foreach ($menus as $menu) {
            // Check if combination exists
            $checkSql = "SELECT COUNT(*) as count FROM Sys_RoleMenu 
                         WHERE RID = ? AND MenID = ?";
            $checkResult = execsqlSRS($checkSql, "Search", [$role['RID'], $menu['MenID']]);
            
            if ($checkResult[0]['count'] == 0) {
                // Insert with inactive (1) as default
                $insertSql = "INSERT INTO Sys_RoleMenu (RID, MenID, UnActive) 
                              VALUES (?, ?, 1)";
                execsqlSRS($insertSql, "Insert", [$role['RID'], $menu['MenID']]);
            }
        }
    }
    
    echo json_encode(["status" => "success", "message" => "Role-Menu table synchronized"]);
}
?>