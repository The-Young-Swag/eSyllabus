<?php
include "../db/dbconnection.php";

// ============ FUNCTION DEFINITIONS (MOVE THEM UP HERE) ============

function showMenus($isDeleted, $MotherMenID, $tab) {
    $sql = "SELECT MenID, Menu, MotherMenID, Description, Menucode, 
                   MenuLink, Arrangement, MenIcon, UnActive
            FROM Sys_Menu
            WHERE (IsDeleted = 0 OR IsDeleted IS NULL)
            AND MotherMenID = ?
            ORDER BY Arrangement, MenID";
    
    $menus = execsqlSRS($sql, "Search", [$MotherMenID]);
    
    if (empty($menus)) {
        if ($MotherMenID == "0") {
            return "<tr><td colspan='10' class='text-center text-muted'>No menus found</td></tr>";
        }
        return "";
    }
    
    $html = "";
    foreach ($menus as $menu) {
        $isParent = $menu["MotherMenID"] == 0;
        $rowClass = $isParent ? "table-primary font-weight-bold" : "";
        $indent = $MotherMenID != "0" ? $tab . "<i class='fa fa-arrow-right text-muted mr-1'></i>" : "";
        
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
                <button class='btn btn-sm btn-info btnEditMenu mr-1' data-id='" . $menu["MenID"] . "'>
                    <i class='fas fa-edit'></i>
                </button>
                <button class='btn btn-sm btn-danger btnDeleteMenu' data-id='" . $menu["MenID"] . "'>
                    <i class='fas fa-trash'></i>
                </button>
            </td>
        </tr>";
        
        // Recursively add child menus
        $html .= showMenus($isDeleted, $menu["MenID"], $tab . "&nbsp;&nbsp;&nbsp;&nbsp;");
    }
    
    return $html;
}

function showDeletedMenus() {
    $sql = "SELECT MenID, Menu, MotherMenID, Description, Menucode, 
                   MenuLink, Arrangement, MenIcon, UnActive
            FROM Sys_Menu
            WHERE IsDeleted = 1 
            ORDER BY MenID";
    
    $menus = execsqlSRS($sql, "Search", []);
    
    if (empty($menus)) {
        return "<tr><td colspan='10' class='text-center text-muted'>No deleted menus found</td></tr>";
    }
    
    $html = "";
    foreach ($menus as $menu) {
        $html .= "<tr class='table-danger'>
            <td>" . htmlspecialchars($menu["MenID"]) . "</td>
            <td>" . htmlspecialchars($menu["Menu"]) . "</td>
            <td>" . htmlspecialchars($menu["MotherMenID"]) . "</td>
            <td>" . htmlspecialchars($menu["Description"]) . "</td>
            <td>" . htmlspecialchars($menu["Menucode"]) . "</td>
            <td>" . htmlspecialchars($menu["MenuLink"]) . "</td>
            <td>" . htmlspecialchars($menu["Arrangement"]) . "</td>
            <td>" . htmlspecialchars($menu["MenIcon"]) . "</td>
            <td class='text-center'>
                <span class='badge badge-danger'>Deleted</span>
            </td>
            <td class='text-center'>
                <button class='btn btn-sm btn-success btnRestoreMenu' data-id='" . $menu["MenID"] . "'>
                    <i class='fas fa-undo'></i>
                </button>
            </td>
        </tr>";
    }
    
    return $html;
}

function handleAddMenu() {
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
    
    try {
        // Insert menu
        execsqlSRS("
            INSERT INTO Sys_Menu (Menu, MotherMenID, Description, Menucode, 
                                 MenuLink, Arrangement, UnActive, MenIcon) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ", "Insert", [$menu, $mother, $desc, $code, $link, $arrangement, $status, $icon]);
        
        // Get the last inserted ID - Try different methods
        $lastId = 0;
        
        // Method 1: Try SCOPE_IDENTITY() first (best for SQL Server)
        $result = execsqlSRS("SELECT SCOPE_IDENTITY() as MenID", "Search", []);
        if (!empty($result) && isset($result[0]['MenID'])) {
            $lastId = $result[0]['MenID'];
        } 
        // Method 2: Try @@IDENTITY if SCOPE_IDENTITY doesn't work
        else {
            $result = execsqlSRS("SELECT @@IDENTITY as MenID", "Search", []);
            if (!empty($result) && isset($result[0]['MenID'])) {
                $lastId = $result[0]['MenID'];
            }
        }
        
        // Method 3: If still no ID, get by the unique code
        if (!$lastId) {
            $result = execsqlSRS("SELECT MenID FROM Sys_Menu WHERE Menucode = ? ORDER BY MenID DESC", 
                                "Search", [$code]);
            if (!empty($result) && isset($result[0]['MenID'])) {
                $lastId = $result[0]['MenID'];
            }
        }
        
        $menuID = $lastId;
        
        if ($menuID > 0) {
            // Assign to all active roles
            $roles = execsqlSRS("SELECT RID FROM Sys_Role WHERE UnActive = '0'", "Search", []);
            foreach ($roles as $role) {
                execsqlSRS("INSERT INTO Sys_RoleMenu (RID, MenID, UnActive) VALUES (?, ?, '0')",
                          "Insert", [$role['RID'], $menuID]);
            }
            
            // Get the inserted menu data
            $menuData = execsqlSRS("SELECT * FROM Sys_Menu WHERE MenID = ?", "Search", [$menuID]);
            
            if (!empty($menuData)) {
                echo json_encode([
                    "success" => true,
                    "message" => "Menu added successfully!",
                    "menuID" => $menuID,
                    "menuData" => $menuData[0]
                ]);
            } else {
                // Even if we can't get the data, return success if we have the ID
                echo json_encode([
                    "success" => true,
                    "message" => "Menu added successfully!",
                    "menuID" => $menuID
                ]);
            }
        } else {
            // Menu was inserted but we couldn't get the ID
            echo json_encode([
                "success" => true,
                "message" => "Menu added successfully! (Refresh to see it)"
            ]);
        }
        
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "INSERT_ERROR: " . $e->getMessage()]);
    }
}

function handleUpdateMenu() {
    $menID = $_POST['menID'] ?? 0;
    $code = $_POST['code'] ?? '';
    
    // Check duplicate code
    $check = execsqlSRS("SELECT COUNT(*) as count FROM Sys_Menu WHERE Menucode = ? AND MenID != ?",
                       "Search", [$code, $menID]);
    
    if ($check[0]['count'] > 0) {
        echo json_encode(["success" => false, "message" => "DUPLICATE_CODE"]);
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
    
    echo json_encode([
        "success" => true,
        "menID" => $menID,
        "menu" => $_POST['menu'] ?? '',
        "desc" => $_POST['desc'] ?? '',
        "code" => $code,
        "link" => $_POST['link'] ?? '',
        "mother" => $_POST['mother'] ?? 0,
        "arrangement" => $_POST['arrangement'] ?? 0,
        "menuStatus" => $_POST['status'] ?? 0,
        "icon" => $_POST['icon'] ?? ''
    ]);
}

function handleToggleStatus() {
    $menID = $_POST['menID'] ?? 0;
    $status = $_POST['status'] ?? 0;
    
    execsqlSRS("UPDATE Sys_Menu SET UnActive = ? WHERE MenID = ?", 
               "Update", [$status, $menID]);
    
    echo json_encode(["success" => true, "message" => "Status updated"]);
}

function handleSoftDelete() {
    $menID = $_POST['menID'] ?? 0;
    
    if (!$menID) {
        echo json_encode(["success" => false, "message" => "Invalid menu ID"]);
        exit;
    }
    
    // Soft delete: set IsDeleted = 1
    execsqlSRS("UPDATE Sys_Menu SET IsDeleted = 1 WHERE MenID = ?", 
               "Update", [$menID]);
    
    echo json_encode(["success" => true, "message" => "Menu moved to deleted"]);
}

function handleRestoreMenu() {
    $menID = $_POST['menID'] ?? 0;
    
    if (!$menID) {
        echo json_encode(["success" => false, "message" => "Invalid menu ID"]);
        exit;
    }
    
    // Restore: set IsDeleted = 0
    execsqlSRS("UPDATE Sys_Menu SET IsDeleted = 0 WHERE MenID = ?", 
               "Update", [$menID]);
    
    echo json_encode(["success" => true, "message" => "Menu restored"]);
}

function getSidebarMenu() {
    $RID = $_POST['RID'] ?? 0;
    
    if (!$RID) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $RID = $_SESSION['RID'] ?? 0;
    }
    
    if (!$RID && isset($_POST['userRID'])) {
        $RID = $_POST['userRID'];
    }
    
    if (!$RID) {
        return '';
    }
    
    $UserMenus = execsqlSRS("
        SELECT m.* 
        FROM Sys_Menu m 
        INNER JOIN Sys_RoleMenu rm ON rm.MenID = m.MenID 
        INNER JOIN Sys_Role r ON r.RID = rm.RID 
        WHERE rm.RID = ? 
        AND m.Unactive = 0 
        AND rm.Unactive = 0 
        AND r.Unactive = 0 
        AND (m.IsDeleted = 0 OR m.IsDeleted IS NULL)
        AND MotherMenID = 0
        ORDER BY m.Arrangement ASC
    ", "Select", [$RID]);

    $html = '';
    foreach ($UserMenus as $menuItem) {
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
                AND (m.IsDeleted = 0 OR m.IsDeleted IS NULL)
                AND MotherMenID = ?
                ORDER BY m.Arrangement ASC
            ", "Select", [$RID, $menuItem["MenID"]]);
    
        foreach ($childMenus as $childMenu) {
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

// ============ SWITCH STATEMENT (AFTER FUNCTION DEFINITIONS) ============

$request = $_POST["request"] ?? "";

switch ($request) {
    case "getAllMenus":
        echo showMenus(false, "0", ""); // Non-deleted, hierarchical
        exit;
        
    case "getDeletedMenus":
        echo showDeletedMenus(); // Deleted menus flat list
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
        
    case "softDeleteMenu":
        handleSoftDelete();
        exit;
        
    case "restoreMenu":
        handleRestoreMenu();
        exit;
        
    case "getSidebarMenu":
        echo getSidebarMenu();
        exit;
        
    default:
        echo json_encode(["success" => false, "message" => "Invalid request"]);
        exit;
}
?>