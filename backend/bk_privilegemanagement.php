<?php
include "../db/dbconnection.php";

$request = $_POST["request"] ?? "";

switch ($request) {
    case "UpdatePrivilege":
        $roleID = $_POST["RID"] ?? "";
        $menuID = $_POST["MenID"] ?? "";
        $status = $_POST["status"] ?? "0"; // 0=Active, 1=Inactive
        
        // FIRST: Check if this role-menu combination exists
        $existing = execsqlSRS("
            SELECT URID FROM Sys_RoleMenu 
            WHERE RID = :roleID AND MenID = :menuID",
            "Search", 
            ["roleID" => $roleID, "menuID" => $menuID]
        );
        
        if (!empty($existing)) {
            // EXISTS: Update it
            execsqlSRS("UPDATE Sys_RoleMenu 
                       SET UnActive = :status 
                       WHERE RID = :roleID AND MenID = :menuID",
                "Update",
                ["status" => $status, "roleID" => $roleID, "menuID" => $menuID]
            );
        } else {
            // DOESN'T EXIST: Insert new record
            execsqlSRS("INSERT INTO Sys_RoleMenu (RID, MenID, UnActive) 
                       VALUES (:roleID, :menuID, :status)",
                "Insert",
                ["roleID" => $roleID, "menuID" => $menuID, "status" => $status]
            );
        }
        
        echo "SUCCESS";
        break;
        
    case "GetRole":
        $roles = execsqlSRS("SELECT RID, Role FROM Sys_Role WHERE UnActive = '0' ORDER BY Role",
            "Search", []);
        
        echo "<option value=''>-- All Roles --</option>";
        foreach($roles as $role) {
            echo "<option value='{$role["RID"]}'>{$role["Role"]}</option>";
        }
        break;
		
		case "GetUserMenu":
    $roleID = $_POST["RID"] ?? 0;
    echo generateUserMenuHTML($roleID);
    break;
        
    case "showtblData":
        $roleID = $_POST["RID"] ?? "";
        echo generatePrivilegeTable($roleID);
        break;
        
    case "showAllRolesAndMenus":
        echo generateAllRolesAndMenusTable();
        break;
        
    case "RefreshSidebar":
        // This will be called to refresh sidebar for all users with the updated role
        $roleID = $_POST["RID"] ?? "";
        refreshSidebarForRoleUsers($roleID);
        echo "SIDEBAR_REFRESHED";
        break;
        
    default:
        echo "Invalid request";
        break;
}

function generatePrivilegeTable($roleID) {
    if (empty($roleID)) {
        return "<tr><td colspan='4' class='text-center text-muted py-4'>
                <i class='fas fa-user-tag fa-2x mb-2'></i><br>
                Please select a role to manage privileges
                </td></tr>";
    }
    
    // Get all menus
    $menus = execsqlSRS("
        SELECT m.MenID, m.Menu, m.Description, m.MotherMenID, m.Arrangement
        FROM Sys_Menu m
        WHERE m.Unactive = '0'
        ORDER BY m.MotherMenID, m.Arrangement, m.MenID",
        "Search", []
    );
    
    // Get role privileges
    $privileges = execsqlSRS("
        SELECT MenID, UnActive 
        FROM Sys_RoleMenu 
        WHERE RID = :roleID",
        "Search", ["roleID" => $roleID]
    );
    
    // Create lookup array
    $privilegeStatus = [];
    foreach($privileges as $priv) {
        $privilegeStatus[$priv["MenID"]] = $priv["UnActive"];
    }
    
    // Organize menus by parent
    $menuTree = [];
    foreach($menus as $menu) {
        $parentID = $menu["MotherMenID"];
        if (!isset($menuTree[$parentID])) {
            $menuTree[$parentID] = [];
        }
        $menuTree[$parentID][] = $menu;
    }
    
    return buildMenuRows($menuTree, 0, $roleID, $privilegeStatus);
}

function generateAllRolesAndMenusTable() {
    // Get all active roles
    $roles = execsqlSRS("SELECT RID, Role FROM Sys_Role WHERE UnActive = '0' ORDER BY Role",
        "Search", []);
    
    // Get all active menus
    $menus = execsqlSRS("
        SELECT m.MenID, m.Menu, m.Description, m.MotherMenID, m.Arrangement
        FROM Sys_Menu m
        WHERE m.Unactive = '0'
        ORDER BY m.MotherMenID, m.Arrangement, m.MenID",
        "Search", []
    );
    
    // Get all role-menu privileges
    $allPrivileges = execsqlSRS("
        SELECT RID, MenID, UnActive 
        FROM Sys_RoleMenu",
        "Search", []
    );
    
    // Create lookup array for privileges
    $privilegeStatus = [];
    foreach($allPrivileges as $priv) {
        $privilegeStatus[$priv["RID"]][$priv["MenID"]] = $priv["UnActive"];
    }
    
    // Organize menus by parent
    $menuTree = [];
    foreach($menus as $menu) {
        $parentID = $menu["MotherMenID"];
        if (!isset($menuTree[$parentID])) {
            $menuTree[$parentID] = [];
        }
        $menuTree[$parentID][] = $menu;
    }
    
    $html = "";
    
    foreach($roles as $role) {
        $roleID = $role["RID"];
        
        // Role Header - Clean and simple
        $html .= "<tr class='table-success'>";
        $html .= "<td colspan='4' class='py-3'>";
        $html .= "<h6 class='mb-0 font-weight-bold'><i class='fas fa-user-tag mr-2'></i>" . htmlspecialchars($role["Role"]) . "</h6>";
        $html .= "</td>";
        $html .= "</tr>";
        
        // Get privileges for this role
        $rolePrivileges = isset($privilegeStatus[$roleID]) ? $privilegeStatus[$roleID] : [];
        
        // Build menu rows for this role
        $html .= buildMenuRowsForAllRoles($menuTree, 0, $roleID, $rolePrivileges);
    }
    
    return $html;
}

function buildMenuRows($menuTree, $parentID, $roleID, $privilegeStatus, $level = 0) {
    $html = "";
    $counter = 1;
    
    if (!isset($menuTree[$parentID])) return $html;
    
    foreach($menuTree[$parentID] as $menu) {
        $menuID = $menu["MenID"];
        $isMotherMenu = ($menu["MotherMenID"] == 0);
        $menuName = htmlspecialchars($menu["Menu"]);
        
        // Simplified Mother Menu badge - bold and clean
        if ($isMotherMenu) {
            $menuName .= " <span class='badge badge-info badge-pill font-weight-bold'>(Mother Menu)</span>";
        }
        
        // Indentation using Bootstrap spacing classes
        $indentClass = "";
        if ($level > 0) {
            $indentClass = "ps-" . ($level * 4);
        }
        
        // Row styling for mother menus
        $rowClass = $isMotherMenu ? "table-primary font-weight-bold" : "";
        
        // Check if this menu has access for selected role
        $hasAccess = isset($privilegeStatus[$menuID]);
        $isActive = $hasAccess && ($privilegeStatus[$menuID] == "0");
        
        // If no record exists, it means NO ACCESS (UnActive = 1)
        $isChecked = $hasAccess ? ($privilegeStatus[$menuID] == "0") : false;
        
        $html .= "<tr class='$rowClass'>";
        $html .= "<td>$counter</td>";
        $html .= "<td class='$indentClass'>$menuName</td>";
        $html .= "<td>" . htmlspecialchars($menu["Description"]) . "</td>";
        
        // Simple toggle switch
        $html .= "<td class='text-center align-middle'>";
        $html .= "<div class='form-check form-switch d-inline-block'>";
        $html .= "<input type='checkbox' 
                class='form-check-input togglePrivilege' 
                id='priv_{$roleID}_{$menuID}'
                data-roleid='$roleID'
                data-menid='$menuID'
                " . ($isChecked ? "checked" : "") . "
                style='cursor: pointer; transform: scale(1.3);'>";
        $html .= "<label class='form-check-label' for='priv_{$roleID}_{$menuID}'></label>";
        $html .= "</div>";
        $html .= "</td>";
        
        $html .= "</tr>";
        
        // Add child menus
        $html .= buildMenuRows($menuTree, $menuID, $roleID, $privilegeStatus, $level + 1);
        $counter++;
    }
    
    return $html;
}

function buildMenuRowsForAllRoles($menuTree, $parentID, $roleID, $privilegeStatus, $level = 0) {
    $html = "";
    
    if (!isset($menuTree[$parentID])) return $html;
    
    foreach($menuTree[$parentID] as $menu) {
        $menuID = $menu["MenID"];
        $isMotherMenu = ($menu["MotherMenID"] == 0);
        $menuName = htmlspecialchars($menu["Menu"]);
        
        // Simplified Mother Menu badge - bold and clean
        if ($isMotherMenu) {
            $menuName .= " <span class='badge badge-info badge-pill font-weight-bold'>(Mother Menu)</span>";
        }
        
        // Indentation using Bootstrap spacing classes
        $indentClass = "";
        if ($level > 0) {
            $indentClass = "ps-" . ($level * 4);
        }
        
        // Row styling for mother menus
        $rowClass = $isMotherMenu ? "table-primary font-weight-bold" : "";
        
        // Check if this menu has access for selected role
        $hasAccess = isset($privilegeStatus[$menuID]);
        $isActive = $hasAccess && ($privilegeStatus[$menuID] == "0");
        
        $html .= "<tr class='$rowClass' data-roleid='$roleID'>";
        $html .= "<td class='align-middle'>
                    <div class='d-flex align-items-center'>
                        <span class='text-muted small'>$menuID</span>
                    </div>
                  </td>";
        $html .= "<td class='$indentClass'>$menuName</td>";
        $html .= "<td class='text-muted'>" . htmlspecialchars($menu["Description"]) . "</td>";
        
        // Simple toggle for all roles view
        $html .= "<td class='text-center align-middle'>";
        $html .= "<div class='form-check form-switch d-inline-block'>";
        $html .= "<input type='checkbox' 
                class='form-check-input togglePrivilege' 
                data-roleid='$roleID'
                data-menid='$menuID'
                " . ($isActive ? "checked" : "") . "
                style='cursor: pointer; transform: scale(1.3);'>";
        $html .= "<label class='form-check-label'></label>";
        $html .= "</div>";
        $html .= "</td>";
        
        $html .= "</tr>";
        
        // Add child menus
        $html .= buildMenuRowsForAllRoles($menuTree, $menuID, $roleID, $privilegeStatus, $level + 1);
    }
    
    return $html;
}

function generateUserMenuHTML($roleID) {
    // Fetch user-specific menus based on their role ID
    $UserMenus = execsqlSRS("
        SELECT m.* 
        FROM Sys_Menu m 
        INNER JOIN Sys_RoleMenu rm ON rm.MenID = m.MenID 
        INNER JOIN Sys_Role r ON r.RID = rm.RID 
        WHERE rm.RID = :rid 
        AND m.Unactive = 0 
        AND rm.Unactive = 0 
        AND r.Unactive = 0 
        AND MotherMenID = 0
        ORDER BY m.Arrangement ASC
    ", "Select", array(":rid" => $roleID));

    $html = '';
    
    foreach ($UserMenus as $menuItem) {
        // Always include icon field - even if empty
        $icon = !empty($menuItem["MenIcon"]) ? htmlspecialchars($menuItem["MenIcon"]) : 'fas fa-circle';
        
        $html .= "<li class='nav-item' id='dropdown' data-read='{$menuItem["MenID"]}'>
                    <a href='#' class='nav-link' id='clckdropdown' data-IDsubmenu='{$menuItem["MenID"]}' data-Page='namepage'>
                        <i class='{$icon}'></i>
                        <p>
                            {$menuItem["Menu"]}
                            <i class='right fas fa-angle-left'></i>
                        </p>
                    </a>
                    <ul class='nav nav-treeview' id='{$menuItem["MenID"]}'>";

        $childMenus = execsqlSRS("SELECT m.* 
                FROM Sys_Menu m 
                INNER JOIN Sys_RoleMenu rm ON rm.MenID = m.MenID 
                INNER JOIN Sys_Role r ON r.RID = rm.RID 
                WHERE rm.RID = :rid 
                AND m.Unactive = 0 
                AND rm.Unactive = 0 
                AND r.Unactive = 0 
                and MotherMenID = :motherID
                ORDER BY m.Arrangement ASC
            ", "Select", array(":rid" => $roleID, ":motherID" => $menuItem["MenID"]));
    
        foreach ($childMenus as $childMenu) {
            // Always include icon field for child menus too
            $childIcon = !empty($childMenu["MenIcon"]) ? htmlspecialchars($childMenu["MenIcon"]) : 'fas fa-circle';
            $isLogout = (htmlspecialchars($childMenu["Menucode"]) == "u_Logout");
            $bgClass = $isLogout ? "bg-danger rounded" : "";
            
            $html .= "<li class='nav-item {$bgClass}'>
                        <a href='#' class='nav-link' id='callpages' data-pagename='{$childMenu["MenuLink"]}'>
                            <i class='{$childIcon}'></i>
                            <p>{$childMenu["Menu"]}</p>
                        </a>
                      </li>";
        }

        $html .= "</ul></li>";
    }
    
    return $html;
}

function refreshSidebarForRoleUsers($roleID) {
    return true;
}
?>