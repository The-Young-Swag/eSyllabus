<?php
include "../db/dbconnection.php";
$adduserpos = exeLiveDbQuery("
										SELECT [PositionId]
											  ,[SalaryGradeId]
											  ,[PositionCode]
											  ,[Position]
											  ,[Authorized]
											  ,[Actual]
											  ,[Unfilled]
											  ,[PlantillaNo]
											  ,[SalaryPerAnnum]
											  ,[DateEntered]
											  ,[DateEnteredBy]
											  ,[DateUpdate]
											  ,[DateUpdateBy]
											  ,[InActive]
										  FROM [tbl_Positions]
										ORDER BY Position
										", "Select", array());
										foreach ($adduserpos as $pos) {
													$position_id = $pos['PositionId'];
													$position_desc = $pos['Position'];
													echo "<option value = '$position_id'>$position_desc</option>";
										}
?>