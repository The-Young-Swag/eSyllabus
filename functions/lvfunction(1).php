<?php
function getlvAllPosition($slfield = "[PositionId]
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
					  ,[InActive]"
		
	){
	$adduserpos = execsqlES(" 
				SELECT $slfield
				  FROM [tbl_Positions]
				ORDER BY Position
				", "Select", array());
	return $adduserpos;
}
function getEmpNamePicPostion($EmpID){
	$getEmpNamePicPostion = execsqlES("SELECT ep.[Picture],pos.PositionId,pos.Position
									,concat(upper([LastName]),', ' ,[FirstName],' ',iif([MiddleInitial] is not null,upper([MiddleInitial]),upper(LEFT([MiddleName], 1))),'.') EmpName
								FROM db_HRIS.dbo.[tbl_Employees] emp
								left join db_HRIS.dbo.[tbl_EmpPicture] ep  on emp.EmpID = ep.EmpID
								left join [db_HRIS].[dbo].[tbl_Positions] pos on pos.PositionId = emp.[PositionID]
								where emp.EmpID = '{$EmpID}'
								order by [LastName] ,[FirstName]","Select",[]);
	return $getEmpNamePicPostion;
}
?>