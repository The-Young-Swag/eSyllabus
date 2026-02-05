<?php
include "../db/dbconnection.php";

$request = $_POST["request"] ?? "";

switch ($request) {
    case "viewMenus":
        echo showAll("0", "");
        exit;
        
    case "addMenu":
        handleAddMenu();
        exit;
        
    case "updateMenu":
        handleUpdateMenu();
        exit;
        
    case "toggleMenuStatus":
        handleToggleStatus();
        exit;
        
    case "getSidebarMenu":
        echo getSidebarMenu();
        exit;
}

// ============ FUNCTIONS ============

function showAll($MenID, $tab) {
    $html = "";
    
    $menus = execsqlSRS("
        SELECT MenID, Menu, MotherMenID, Description, Menucode, 
               MenuLink, Arrangement, MenIcon, UnActive
        FROM Sys_Menu
        WHERE MotherMenID = ?
        ORDER BY Arrangement, MenID
    ", "Search", [$MenID]);
    
    foreach ($menus as $menu) {
        $isParent = $menu["MotherMenID"] == 0;
        $rowClass = $isParent ? "table-primary font-weight-bold" : "";
        $indent = $MenID != "0" ? $tab . "<i class='fa fa-arrow-right text-muted mr-1'></i>" : "";
        
        $html .= "<tr class='$rowClass'>
            <td>" . htmlspecialchars($menu["MenID"]) . "</td>
            <td>$indent" . htmlspecialchars($menu["Menu"]) . "</td>
            <td>" . htmlspecialchars($menu["MotherMenID"]) . "</td>
            <td>" . htmlspecialchars($menu["Description"]) . "</td>
            <td>" . htmlspecialchars($menu["Menucode"]) . "</td>
            <td>" . htmlspecialchars($menu["MenuLink"]) . "</td>
            <td>" . htmlspecialchars($menu["Arrangement"]) . "</td>
            <td>" . htmlspecialchars($menu["MenIcon"]) . "</td>
            <td class='text-center'>
                <div class='custom-control custom-switch'>
                    <input type='checkbox' class='custom-control-input toggleMenuStatus'
                           id='menuStatus" . $menu["MenID"] . "'
                           data-id='" . $menu["MenID"] . "' " . ($menu["UnActive"] == 0 ? "checked" : "") . ">
                    <label class='custom-control-label' for='menuStatus" . $menu["MenID"] . "'></label>
                </div>
            </td>
            <td class='text-center'>
                <button class='btn btn-sm btn-warning btnEditMenu' data-id='" . $menu["MenID"] . "'>
                    <i class='fas fa-edit'></i>
                </button>
            </td>
        </tr>";
        
        // Add child menus
        $html .= showAll($menu["MenID"], $tab . "&nbsp;&nbsp;&nbsp;&nbsp;");
    }
    
    return $html;
}

function handleAddMenu() {
    // Get form data
    $menu = $_POST['menu'] ?? '';
    $mother = $_POST['mother'] ?? 0;
    $desc = $_POST['desc'] ?? '';
    $code = $_POST['code'] ?? '';
    $link = $_POST['link'] ?? '';
    $arrangement = $_POST['arrangement'] ?? 0;
    $status = $_POST['status'] ?? 0;
    $icon = $_POST['icon'] ?? '';
    
    // Check duplicate code
    $check = execsqlSRS("SELECT COUNT(*) as count FROM Sys_Menu WHERE Menucode = ?", 
                       "Search", [$code]);
    
    if ($check[0]['count'] > 0) {
        echo json_encode(["success" => false, "message" => "DUPLICATE_CODE"]);
        exit;
    }
    
    // Insert menu
    execsqlSRS("
        INSERT INTO Sys_Menu (Menu, MotherMenID, Description, Menucode, 
                             MenuLink, Arrangement, UnActive, MenIcon) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ", "Insert", [$menu, $mother, $desc, $code, $link, $arrangement, $status, $icon]);
    
    // Get the new menu
    $newMenu = execsqlSRS("
        SELECT TOP 1 * FROM Sys_Menu WHERE Menucode = ? ORDER BY MenID DESC
    ", "Search", [$code]);
    
    if (!empty($newMenu)) {
        $menuData = $newMenu[0];
        $menuID = $menuData['MenID'];
        
        // Assign to all roles
        $roles = execsqlSRS("SELECT RID FROM Sys_Role WHERE UnActive = '0'", "Search", []);
        foreach ($roles as $role) {
            execsqlSRS("INSERT INTO Sys_RoleMenu (RID, MenID, UnActive) VALUES (?, ?, '0')",
                      "Insert", [$role['RID'], $menuID]);
        }
        
        // Return success
        echo json_encode([
            "success" => true,
            "message" => "Menu added successfully!",
            "rowHtml" => generateMenuRow($menuData, $mother),
            "menuData" => $menuData
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "INSERT_FAILED"]);
    }
}

function generateMenuRow($menu, $motherID = 0) {
    $isParent = $motherID == 0;
    $rowClass = $isParent ? "table-primary font-weight-bold" : "";
    $indent = $motherID != 0 ? "<i class='fa fa-arrow-right text-muted mr-1'></i>" : "";
    
    return "
    <tr class='$rowClass'>
        <td>" . htmlspecialchars($menu["MenID"]) . "</td>
        <td>$indent" . htmlspecialchars($menu["Menu"]) . "</td>
        <td>" . htmlspecialchars($menu["MotherMenID"]) . "</td>
        <td>" . htmlspecialchars($menu["Description"]) . "</td>
        <td>" . htmlspecialchars($menu["Menucode"]) . "</td>
        <td>" . htmlspecialchars($menu["MenuLink"]) . "</td>
        <td>" . htmlspecialchars($menu["Arrangement"]) . "</td>
        <td>" . htmlspecialchars($menu["MenIcon"]) . "</td>
        <td class='text-center'>
            <div class='custom-control custom-switch'>
                <input type='checkbox' class='custom-control-input toggleMenuStatus'
                       id='menuStatus" . $menu["MenID"] . "'
                       data-id='" . $menu["MenID"] . "' " . ($menu["UnActive"] == 0 ? "checked" : "") . ">
                <label class='custom-control-label' for='menuStatus" . $menu["MenID"] . "'></label>
            </div>
        </td>
        <td class='text-center'>
            <button class='btn btn-sm btn-warning btnEditMenu' data-id='" . $menu["MenID"] . "'>
                <i class='fas fa-edit'></i>
            </button>
        </td>
    </tr>";
}

function handleUpdateMenu() {
    // Get data
    $menID = $_POST['menID'] ?? 0;
    $code = $_POST['code'] ?? '';
    
    // Check duplicate code
    $check = execsqlSRS("SELECT COUNT(*) as count FROM Sys_Menu WHERE Menucode = ? AND MenID != ?",
                       "Search", [$code, $menID]);
    
    if ($check[0]['count'] > 0) {
        echo json_encode(["status" => "error", "message" => "DUPLICATE_CODE"]);
        exit;
    }
    
    // Update menu
    execsqlSRS("
        UPDATE Sys_Menu SET
            Menu = ?, Description = ?, Menucode = ?, MenuLink = ?,
            MotherMenID = ?, Arrangement = ?, UnActive = ?, MenIcon = ?
        WHERE MenID = ?
    ", "Update", [
        $_POST['menu'] ?? '',
        $_POST['desc'] ?? '',
        $code,
        $_POST['link'] ?? '',
        $_POST['mother'] ?? 0,
        $_POST['arrangement'] ?? 0,
        $_POST['status'] ?? 0,
        $_POST['icon'] ?? '',
        $menID
    ]);
    
    // Get updated menu data
    $updatedMenu = execsqlSRS("SELECT * FROM Sys_Menu WHERE MenID = ?", "Search", [$menID]);
    
    if (!empty($updatedMenu)) {
        echo json_encode([
            "success" => true,  // Changed from "status" => "success"
            "menID" => $menID,
            "menu" => $_POST['menu'] ?? '',
            "desc" => $_POST['desc'] ?? '',
            "code" => $code,
            "link" => $_POST['link'] ?? '',
            "mother" => $_POST['mother'] ?? 0,
            "arrangement" => $_POST['arrangement'] ?? 0,
            "menuStatus" => $_POST['status'] ?? 0,  // Changed from "status" to "menuStatus"
            "icon" => $_POST['icon'] ?? ''
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Update failed"]);
    }
}

function handleToggleStatus() {
    $menID = $_POST['menID'] ?? 0;
    $status = $_POST['status'] ?? 0;
    
    // Update menu status
    execsqlSRS("UPDATE Sys_Menu SET UnActive = ? WHERE MenID = ?", "Update", [$status, $menID]);
    
    echo json_encode(["success" => true, "message" => "Status updated"]);  // Changed from status to success
}

function getSidebarMenu() {
    $RID = $_POST['RID'] ?? 0;
    
    if (!$RID) {
        // Try to get from session if available
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $RID = $_SESSION['RID'] ?? 0;
    }
    
    // If still no RID, try to get from global UserInfo if available
    if (!$RID && isset($_POST['userRID'])) {
        $RID = $_POST['userRID'];
    }
    
    // If still no RID, return empty
    if (!$RID) {
        return '';
    }
    
    $html = '';
    $UserMenus = execsqlSRS("
        SELECT m.* 
        FROM Sys_Menu m 
        INNER JOIN Sys_RoleMenu rm ON rm.MenID = m.MenID 
        INNER JOIN Sys_Role r ON r.RID = rm.RID 
        WHERE rm.RID = ? 
        AND m.Unactive = 0 
        AND rm.Unactive = 0 
        AND r.Unactive = 0 
        AND MotherMenID = 0
        ORDER BY m.Arrangement ASC
    ", "Select", [$RID]);

    foreach ($UserMenus as $menuItem) {
        // Always include icon field - even if empty
        $icon = !empty($menuItem["MenIcon"]) ? htmlspecialchars($menuItem["MenIcon"]) : 'fas fa-circle';
        
        $html .= "<li class='nav-item' data-read='{$menuItem["MenID"]}'>
                <a href='#' class='nav-link' id='clckdropdown' data-IDsubmenu='{$menuItem["MenID"]}'>
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
                WHERE rm.RID = ? 
                AND m.Unactive = 0 
                AND rm.Unactive = 0 
                AND r.Unactive = 0 
                and MotherMenID = ?
                ORDER BY m.Arrangement ASC
            ", "Select", [$RID, $menuItem["MenID"]]);
    
        foreach ($childMenus as $childMenu) {
            // Always include icon field for child menus too
            $childIcon = !empty($childMenu["MenIcon"]) ? htmlspecialchars($childMenu["MenIcon"]) : 'fas fa-circle';
            $logoutClass = htmlspecialchars($childMenu["Menucode"]) == "u_Logout" ? "bg-danger rounded" : "";
            
            $html .= "<li class='nav-item {$logoutClass}'>
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
?>