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
                           data-id='" . $menu["MenID"] . "' " . ($menu["UnActive"] == 0 ? "checked" : "") . ">
                    <label class='custom-control-label'></label>
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
        echo json_encode(["status" => "error", "message" => "DUPLICATE_CODE"]);
        exit;
    }
    
    // Insert menu (NO try-catch needed)
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
        $menu = $newMenu[0];
        $menuID = $menu['MenID'];
        
        // Assign to all roles
        $roles = execsqlSRS("SELECT RID FROM Sys_Role WHERE UnActive = '0'", "Search", []);
        foreach ($roles as $role) {
            execsqlSRS("INSERT INTO Sys_RoleMenu (RID, MenID, UnActive) VALUES (?, ?, '0')",
                      "Insert", [$role['RID'], $menuID]);
        }
        
        // Return success
        echo json_encode([
            "status" => "success",
            "message" => "Menu added successfully!",
            "rowHtml" => generateMenuRow($menu, $mother)
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "INSERT_FAILED"]);
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
                       data-id='" . $menu["MenID"] . "' " . ($menu["UnActive"] == 0 ? "checked" : "") . ">
                <label class='custom-control-label'></label>
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
        echo "DUPLICATE_CODE";
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
    
    echo "SUCCESS";
}
?>