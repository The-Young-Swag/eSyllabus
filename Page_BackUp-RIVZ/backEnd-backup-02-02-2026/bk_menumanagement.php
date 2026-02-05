<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";

switch ($request) {
	case "viewMenus":
		$tbleDetails = "";
		$tab = "";
		$tbleDetails = showAll("0", $tbleDetails, $tab);

		//  PURE HTML OUTPUT (Option A)
		echo $tbleDetails;
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
		$tbleDetails .= "<td></td>";
		$tbleDetails .= "<td>" . htmlspecialchars($AllShow["Description"]) . "</td>";
		$tbleDetails .= "<td>" . htmlspecialchars($AllShow["Menucode"]) . "</td>";
		$tbleDetails .= "<td>" . htmlspecialchars($AllShow["MenuLink"]) . "</td>";
		$tbleDetails .= "<td></td>";
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
		<td>
			<button type='button' class='btn btn-warning' id='editmenu'
				value='{$AllShow["MenID"]}'>
				View/Edit
			</button>
		</td>";

		$tbleDetails .= "</tr>";

		// recursion
		$tbleDetails = showAll($AllShow["MenID"], $tbleDetails, $tab);
	}

	return $tbleDetails;
}
