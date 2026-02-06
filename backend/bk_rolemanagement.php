<?php
include "../db/dbconnection.php";

$request = $_POST["request"] ?? "";

switch ($request) {
    case "viewRoles":
        viewRoles();
        break;
        
    case "addRole":
        addRole();
        break;
        
    case "updateRole":
        updateRole();
        break;
        
    case "toggleRoleStatus":
        toggleRoleStatus();
        break;
        
    default:
        echo json_encode(["status" => "error", "message" => "Invalid request"]);
}

//  FUNCTIONS 

function viewRoles() {
    $query = execsqlSRS("
        SELECT RID, Role, Rolecode, UnActive 
        FROM Sys_Role 
        ORDER BY RID
    ", "Search", []);
    
    foreach ($query as $row) {
        $statusChecked = $row["UnActive"] == 0 ? "checked" : "";
        $switchId = "roleStatus" . $row["RID"];
        
        echo "<tr data-role-id='" . htmlspecialchars($row["RID"]) . "'>
            <td>" . htmlspecialchars($row["RID"]) . "</td>
            <td>" . htmlspecialchars($row["Role"]) . "</td>
            <td>" . htmlspecialchars($row["Rolecode"]) . "</td>
            <td class='text-center'>
                <div class='custom-control custom-switch'>
                    <input type='checkbox' 
                           class='custom-control-input toggleRoleStatus' 
                           id='$switchId'
                           data-id='" . htmlspecialchars($row["RID"]) . "'
                           $statusChecked>
                    <label class='custom-control-label' for='$switchId'></label>
                </div>
            </td>
            <td class='text-center'>
                <button class='btn btn-sm btn-warning btnEditRole' data-id='" . htmlspecialchars($row["RID"]) . "'>
                    <i class='fas fa-edit'></i>
                </button>
            </td>
        </tr>";
    }
}

function addRole() {
    $role = $_POST['r_role'] ?? '';
    $rolecode = $_POST['r_rolecode'] ?? '';
    $status = $_POST['r_status'] ?? 0;
    
    // Check if role already exists
    $check = execsqlSRS("SELECT COUNT(*) as count FROM Sys_Role WHERE Role = ?", "Search", [$role]);
    
    if ($check[0]['count'] > 0) {
        echo json_encode(["status" => "error", "message" => "Role already exists!"]);
        exit;
    }
    
    // Add new role
    execsqlSRS("INSERT INTO Sys_Role (Role, Rolecode, UnActive) VALUES (?, ?, ?)", 
              "Insert", [$role, $rolecode, $status]);
    
    // Get new role ID
    $newRole = execsqlSRS("SELECT TOP 1 * FROM Sys_Role WHERE Role = ? ORDER BY RID DESC", "Search", [$role]);
    
    if (!empty($newRole)) {
        $roleData = $newRole[0];
        $RID = $roleData['RID'];
        
        // Assign all active menus to this role
        $menus = execsqlSRS("SELECT MenID FROM Sys_Menu WHERE UnActive = '0'", "Search", []);
        foreach ($menus as $menu) {
            execsqlSRS("INSERT INTO Sys_RoleMenu (RID, MenID, UnActive) VALUES (?, ?, '0')",
                      "Insert", [$RID, $menu['MenID']]);
        }
        
        // Return success with new role HTML row
        echo json_encode([
            "status" => "success",
            "message" => "Role added successfully!",
            "rowHtml" => generateRoleRow($roleData)
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to add role"]);
    }
}

function updateRole() {
    $RID = $_POST['er_submit'] ?? 0;
    $role = $_POST['er_role'] ?? '';
    $rolecode = $_POST['er_rolecode'] ?? '';
    $status = $_POST['er_status'] ?? 0;
    
    // Update role
    execsqlSRS("UPDATE Sys_Role SET Role = ?, Rolecode = ?, UnActive = ? WHERE RID = ?",
              "Update", [$role, $rolecode, $status, $RID]);
    
    // Return SUCCESS (just a simple string, not JSON)
    echo "SUCCESS";
}

function toggleRoleStatus() {
    $RID = $_POST['RID'] ?? 0;
    $status = $_POST['status'] ?? 0; // 0 = active, 1 = inactive
    
    // Update role status
    execsqlSRS("UPDATE Sys_Role SET UnActive = ? WHERE RID = ?", "Update", [$status, $RID]);
    
    echo json_encode([
        "status" => "success",
        "RID" => $RID,
        "newStatus" => $status,
        "statusText" => $status == 0 ? "Active" : "Inactive"
    ]);
}

function generateRoleRow($role) {
    $statusChecked = $role["UnActive"] == 0 ? "checked" : "";
    $switchId = "roleStatus" . $role["RID"];
    
    return "
    <tr data-role-id='" . htmlspecialchars($role["RID"]) . "'>
        <td>" . htmlspecialchars($role["RID"]) . "</td>
        <td>" . htmlspecialchars($role["Role"]) . "</td>
        <td>" . htmlspecialchars($role["Rolecode"]) . "</td>
        <td class='text-center'>
            <div class='custom-control custom-switch'>
                <input type='checkbox' 
                       class='custom-control-input toggleRoleStatus' 
                       id='$switchId'
                       data-id='" . htmlspecialchars($role["RID"]) . "'
                       $statusChecked>
                <label class='custom-control-label' for='$switchId'></label>
            </div>
        </td>
        <td class='text-center'>
            <button class='btn btn-sm btn-warning btnEditRole' data-id='" . htmlspecialchars($role["RID"]) . "'>
                <i class='fas fa-edit'></i>
            </button>
        </td>
    </tr>";
}
?>