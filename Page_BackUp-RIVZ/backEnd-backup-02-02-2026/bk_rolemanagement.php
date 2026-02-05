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

		foreach ($queryViewRoles as $qViewRole) {
			echo "<tr>"; // Start table row
			/* echo "<th scope='row'>" . htmlspecialchars($qViewRole["RID"]) . "</th>"; */
			echo "<td>" . htmlspecialchars($qViewRole["RID"]) . "</td>";
			echo "<td>" . htmlspecialchars($qViewRole["Rolecode"]) . "</td>";
			echo "<td>" . htmlspecialchars($qViewRole["Rolecode"]) . "</td>";
			echo "<td>" . htmlspecialchars($qViewRole["UnActive"]) . "</td>";
			echo "<td>
					<button type='button' class='btn btn-warning' id='editrole' 
						value='" . htmlspecialchars($qViewRole["RID"]) . "'>
						View/Edit
					</button>
				  </td>";
			echo "</tr>"; // End table row
		}
		break;

}
?>
