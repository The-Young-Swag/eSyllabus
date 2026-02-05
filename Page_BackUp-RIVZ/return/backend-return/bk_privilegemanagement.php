<?php
include "../db/dbconnection.php";

$request = isset($_POST["request"]) ? $_POST["request"] : "";

switch ($request) {
	case "Update":
		$table = isset($_POST["table"]) ? $_POST["table"] : "Sys_UserAccount";
		$UpFld = isset($_POST["UpFld"]) ? $_POST["UpFld"] : "";
		$Upval = isset($_POST["Upval"]) ? $_POST["Upval"] : "";
		$FltFld = isset($_POST["FltFld"]) ? $_POST["FltFld"] : "";
		$FltID = isset($_POST["FltID"]) ? $_POST["FltID"] : "";
		$RID = isset($_POST["RID"]) ? $_POST["RID"] : "";
		
		execsqlSRS(" update $table
			set {$UpFld} =:Upval
			where $FltFld =:FltFld and RID='{$RID}'",
			"Update",
			["Upval"=>$Upval,"FltFld"=>$FltID]
		);
		 
		execsqlSRS("insert into [tbl_Logs] ([UserID],[parameter],[operations],[status])
		values ('{$userId}','{$UpFld}:$Upval,{$FltFld}:$FltID','Menu Active Status','success')
		","Insert",[]); 
	break;
	case "showPrvMenAcc":
		$RID = isset($_POST["RID"]) ? $_POST["RID"] : "";

		$arr=[];
		$showPrvMenAcc = execsqlSRS("
				SELECT rm.[URID],rm.[RID],rm.[MenID],rm.[UnActive]
				FROM [Sys_RoleMenu] rm
				where rm.[RID]='{$RID}'", 
			"Search", 
			[]
		);
		foreach($showPrvMenAcc as $PrvMenAcc){
			$arr []= array("MenID"=>$PrvMenAcc["MenID"],"UnActive"=>($PrvMenAcc["UnActive"]==1?false:true));
		}
		echo json_encode($arr);
	break;
	case "GetRole":
		$GetRole = execsqlSRS("
			SELECT [RID]
				  ,[Role]
				  ,[Rolecode]
				  ,[UnActive]
			  FROM [Sys_Role]", 
			"Search", 
			array()
		);
		echo "<option></option>";
		foreach($GetRole as $Role){
			echo "<option value='{$Role["RID"]}'>{$Role["Role"]}</option>";
		}
	break;
	case "displayRole":
		$tbleDetails = "";$tab = "";
		 $tbleDetails = showAll("0",$tbleDetails,$tab);
		 //echo $tbleDetails;
		echo json_encode(["tbleDetails" => $tbleDetails
						, "status" => true
						, "OfStaffID" => ""]);
	break;
	case "showtblData":
		$tbleDetails = "";$tab = "";
		 $tbleDetails = showAll("0",$tbleDetails,$tab);
		 echo $tbleDetails;
		/* echo json_encode(["tbleDetails" => $tbleDetails
						, "status" => true
						, "OfStaffID" => ""]); */
	break;
}



function showAll($MenID,$tbleDetails,$tab){
	if(!$MenID=="0"){
		$tab .= "<i class='fa fa-long-arrow-right' aria-hidden='true' style='color:red;'></i> ";
	}
	$bld = $MenID=="0"?"style='color: #FFFFFF; font-weight: bold; background-color: #07861f;'":"";
	//$tab = "";
	$GetAllShow = execsqlSRS("
			SELECT [MenID], [Menu], [Description], [UnActive]
			FROM [Sys_Menu]
			where MotherMenID = '{$MenID}'
			ORDER BY MotherMenID, Arrangement, MenID", 
			"Search", 
			array()
		);
	foreach($GetAllShow as $AllShow){
		$indent = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", 0);
		$tbleDetails .=  "<tr $bld >";
			$tbleDetails .=  "<td style='white-space: nowrap;'>" . $tab. htmlspecialchars($AllShow["MenID"]) . "</td>";
			$tbleDetails .=  "<td style='white-space: nowrap;'>" .   htmlspecialchars($AllShow["Menu"]) . "</td>";
			$tbleDetails .=  "<td>" . htmlspecialchars($AllShow["Description"]) . "</td>";
			
			$isActive = $AllShow["UnActive"] == 1 ? "" : "checked";
			
			$tbleDetails .=  "<td>
					<label class='custom-switch'>
						<input type='checkbox' class='toggle-switch' 
							name='PrvStat{$AllShow["MenID"]}'
							id='UpdatePrvRole'
							data-table='Sys_RoleMenu'
							data-UpFld='UnActive'
							data-FltFld='MenID'
							data-FltID='{$AllShow["MenID"]}'
							data-MenID='{$AllShow["MenID"]}' $isActive >
						<span class='slider'></span>
					</label>
					<span class='status-text'></span>
				  </td>";
			
		$tbleDetails .=  "</tr>";

		$tbleDetails = showAll($AllShow["MenID"],$tbleDetails,$tab);
	}
	return $tbleDetails;
	  
}
?>
