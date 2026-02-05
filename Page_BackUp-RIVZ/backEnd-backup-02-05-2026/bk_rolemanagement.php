<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";

switch ($request) {

	case "viewRoles":
		$queryViewRoles = execsqlSRS("
			SELECT [RID], [Role], [Rolecode], [UnActive] 
			FROM [Sys_Role] 
			ORDER BY RID", 
			"Search", 
			array()
		);

			// In bk_rolemanagement.php, update the table output:
// In bk_rolemanagement.php, update the foreach loop:
foreach ($queryViewRoles as $qViewRole) {
    // ADD THIS LINE - data-role-id attribute
    echo "<tr data-role-id='" . htmlspecialchars($qViewRole["RID"]) . "'>";
    
    echo "<td>" . htmlspecialchars($qViewRole["RID"]) . "</td>";
    echo "<td>" . htmlspecialchars($qViewRole["Role"]) . "</td>";  // FIXED: Changed from Rolecode to Role
    echo "<td>" . htmlspecialchars($qViewRole["Rolecode"]) . "</td>";
    echo "<td>" . (htmlspecialchars($qViewRole["UnActive"]) == 0 ? "Active" : "Inactive") . "</td>";
    
    echo "<td>
            <button type='button' class='btn btn-warning btnEditRole' 
                    data-id='" . htmlspecialchars($qViewRole["RID"]) . "'>
                View/Edit
            </button>
          </td>";
    echo "</tr>";
}
		break;

}
?>
