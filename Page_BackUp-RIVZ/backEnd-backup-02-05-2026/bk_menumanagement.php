<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";

switch ($request) {
	case "viewMenus":
		$tbleDetails = "";
		$tab = "";
		$tbleDetails = showAll("0", $tbleDetails, $tab);

		echo $tbleDetails;
		exit;
		
		// Add this case to your existing switch statement
case "addMenu":
    // Get POST data
    $menu = $_POST['menu'] ?? '';
    $mother = $_POST['mother'] ?? 0;
    $desc = $_POST['desc'] ?? '';
    $code = $_POST['code'] ?? '';
    $link = $_POST['link'] ?? '';
    $arrangement = $_POST['arrangement'] ?? 0;
    $status = $_POST['status'] ?? 0;
    $icon = $_POST['icon'] ?? '';
    
    // Check if menu code already exists first
    $checkSql = "SELECT COUNT(*) as count FROM Sys_Menu WHERE Menucode = ?";
    $checkResult = execsqlSRS($checkSql, "Search", [$code]);
    
    if($checkResult[0]['count'] > 0) {
        echo "DUPLICATE_CODE";
        exit;
    }
    
    // Try to insert - since execsqlSRS doesn't return true/false for Insert operations,
    // we need to check differently
    try {
        // Execute the insert
        execsqlSRS(
            "INSERT INTO Sys_Menu 
             (Menu, MotherMenID, Description, Menucode, MenuLink, Arrangement, UnActive, MenIcon) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            "Insert",
            [$menu, $mother, $desc, $code, $link, $arrangement, $status, $icon]
        );
        
        // If we get here without error, assume success
        echo "SUCCESS";
    } catch (Exception $e) {
        // If there's an exception, return error
        echo "INSERT_ERROR";
    }
    exit;

case "updateMenu":
    // Get all POST data
    $menID = $_POST['menID'] ?? 0;
    $menu = $_POST['menu'] ?? '';
    $desc = $_POST['desc'] ?? '';
    $code = $_POST['code'] ?? '';
    $link = $_POST['link'] ?? '';
    $mother = $_POST['mother'] ?? 0;
    $arrangement = $_POST['arrangement'] ?? 0;
    $status = $_POST['status'] ?? 0;
    $icon = $_POST['icon'] ?? '';
    
    // Check if menu code already exists (but not for the current menu)
    $checkSql = "SELECT COUNT(*) as count FROM Sys_Menu WHERE Menucode = ? AND MenID != ?";
    $checkResult = execsqlSRS($checkSql, "Search", [$code, $menID]);
    
    if($checkResult[0]['count'] > 0) {
        echo "DUPLICATE_CODE";
        exit;
    }
    
    // Try to update
    try {
        // Perform the update in DB
        execsqlSRS(
            "UPDATE Sys_Menu
             SET Menu = ?, 
                 Description = ?, 
                 Menucode = ?, 
                 MenuLink = ?,
                 MotherMenID = ?,
                 Arrangement = ?,
                 UnActive = ?,
                 MenIcon = ?
             WHERE MenID = ?",
            "Update",
            [
                $menu,
                $desc,
                $code,
                $link,
                $mother,
                $arrangement,
                $status,
                $icon,
                $menID
            ]
        );
        
        // If we get here without error, assume success
        echo "SUCCESS";
        
    } catch (Exception $e) {
        // If there's an exception, return error
        echo "UPDATE_ERROR";
    }
    exit;
}


/* ===============================
   FUNCTION
================================ */
function showAll($MenID, $tbleDetails, $tab)
{
	if ($MenID != "0") {
		$tab .= "<i class='fa fa-long-arrow-right' aria-hidden='true' style='color:red;'></i> ";
	}

	$bld = $MenID == "0"
		? "style='font-weight: bold; background-color: lightgreen;'"
		: "";

	$GetAllShow = execsqlSRS(
		"
		SELECT [MenID], [Menu], [MotherMenID], [Description], [Menucode],
			   [MenuLink], [Arrangement], [MenIcon], [UnActive]
		FROM [Sys_Menu]
		WHERE MotherMenID = '{$MenID}'
		ORDER BY MotherMenID, Arrangement, MenID
		",
		"Search",
		array()
	);

	foreach ($GetAllShow as $AllShow) {

		$tbleDetails .= "<tr $bld>";
		$tbleDetails .= "<td>" . htmlspecialchars($AllShow["MenID"]) . "</td>";
		$tbleDetails .= "<td style='white-space: nowrap;'>" . $tab . htmlspecialchars($AllShow["Menu"]) . "</td>";
		$tbleDetails .= "<td>" . htmlspecialchars($AllShow["MotherMenID"]) . "</td>";
		$tbleDetails .= "<td>" . htmlspecialchars($AllShow["Description"]) . "</td>";
		$tbleDetails .= "<td>" . htmlspecialchars($AllShow["Menucode"]) . "</td>";
		$tbleDetails .= "<td>" . htmlspecialchars($AllShow["MenuLink"]) . "</td>";
		$tbleDetails .= "<td>" . htmlspecialchars($AllShow["Arrangement"]) . "</td>";
		$tbleDetails .= "<td>" . htmlspecialchars($AllShow["MenIcon"]) . "</td>";

		$isActive = $AllShow["UnActive"] == 1 ? "" : "checked";

		$tbleDetails .= "
		<td>
			<label class='custom-switch'>
				<input type='checkbox' class='toggle-switch'
					data-table='Sys_Menu'
					data-UpFld='UnActive'
					data-FltFld='MenID'
					data-FltID='{$AllShow["MenID"]}'
					data-MenID='{$AllShow["MenID"]}' $isActive>
				<span class='slider'></span>
			</label>
			<span class='status-text'></span>
		</td>";

$tbleDetails .= "
<td class='text-center'>
    <button type='button' class='btn btn-warning btn-sm btnEditMenu'
            data-id='{$AllShow["MenID"]}'>
        <i class='fas fa-edit'></i> Edit
    </button>
</td>";

		$tbleDetails .= "</tr>";

		// recursion
		$tbleDetails = showAll($AllShow["MenID"], $tbleDetails, $tab);
	}

	return $tbleDetails;
}
