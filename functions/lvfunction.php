<?php

function getlvAllPosition(


) {
		$slfield = "[position_id]
					  ,[position_code]
					  ,[position_desc]
					  ,[plantilla_num]
					  ,[isActive]";
	$adduserpos = execsqlES(" 
				SELECT $slfield
				  FROM [tbl_Positions]
				ORDER BY position_id
				", "Select", array());
	return $adduserpos;
}
function getEmpNamePicPostion($EmpID)
{
	$getEmpNamePicPostion = execsqlES("SELECT ep.[Picture],pos.PositionId,pos.Position
									,concat(upper([LastName]),', ' ,[FirstName],' ',iif([MiddleInitial] is not null,upper([MiddleInitial]),upper(LEFT([MiddleName], 1))),'.') EmpName
								FROM [tbl_Employees] emp
								left join [tbl_EmpPicture] ep  on emp.EmpID = ep.EmpID
								left join [tbl_Positions] pos on pos.PositionId = emp.[PositionID]
								where emp.EmpID = '{$EmpID}'
								order by [LastName] ,[FirstName]", "Select", []);
	return $getEmpNamePicPostion;
}


function getEmpAll()
{
	$getEmpNamePicPostion = execsqlES("SELECT [EmpId],[CollegeId],[LastName],[FirstName],[MiddleName]
		  ,[MiddleInitial],[ExtName],[DateofBirth],[PlaceofBirth],[GenderId],[CivilStatusId]
		  --,[Citizenship],[Height],[Weight],[BloodTypeId],[GSIS],[PAGIBIG],[PHILHEALTH]
		  --,[SSS],[TIN],[ResAddress],[ResZipCode],[ResTelNo],[PermAddress],[PermZipCode]
		  --,[PermTelNo],[EmailAddress],[CellNo],[DateEntered],[EnteredBy],[DateModified]
		  --,[ModifiedBy],[StatusId],[PositionId],[SalaryGrade],[MonthlySalary],[EmpTypeId]
		  --,[AppointmentDate],[OfficeId],[DepartmentId],[BureauId],[SalaryPerMonth],[InActive]
		  --,[IsFaculty],[IsAccountant],[LWOPSalary],[PERASalary],[Wallpaper],[pin],[flexicode]
		  --,[Picture],[Signature],[QRCode]
		FROM [tbl_Employees]
		where InActive = 0
		order by [LastName] ,[FirstName]", "Select", []);
	return $getEmpNamePicPostion;
}

function getEmpPersonalIformation($EmpID)
{
	$getEmpNamePicPostion = execsqlES("SELECT [EmpId],[CollegeId],[LastName],[FirstName],[MiddleName],[MiddleInitial]
      ,[ExtName],[DateofBirth],[PlaceofBirth],[GenderId],[CivilStatusId],[Citizenship],[Height],[Weight],[BloodTypeId]
      ,[GSIS],[PAGIBIG],[PHILHEALTH],[SSS],[TIN],[ResAddress],[ResZipCode],[ResTelNo],[PermAddress],[PermZipCode]
      ,[PermTelNo],[EmailAddress],[CellNo],[DateEntered],[EnteredBy],[DateModified],[ModifiedBy],[StatusId],[PositionId]
      ,[SalaryGrade],[MonthlySalary],[EmpTypeId],[AppointmentDate],[OfficeId],[DepartmentId],[BureauId],[SalaryPerMonth]
      ,[InActive],[IsFaculty],[IsAccountant],[LWOPSalary],[PERASalary],[Wallpaper],[pin],[flexicode],[Picture]
      ,[Signature],[QRCode]
	  FROM [tbl_Employees]
			where InActive = 0
			order by [LastName] ,[FirstName]", "Select", []);
	return $getEmpNamePicPostion;
}

function getEmpPerEmp($EmpID, $request)
{
	switch ($request) {
		case "personal":
			$getEmpNamePicPostion = execsqlES("select [EmpId],[CollegeId],[LastName],[FirstName],[MiddleName],[MiddleInitial]
				,[ExtName],CONVERT(varchar(10), DateofBirth, 23) AS [DateofBirth]
				,[PlaceofBirth],[GenderId],[CivilStatusId],[Citizenship],[Height]
				,[Weight],[BloodTypeId],[GSIS],[PAGIBIG],[PHILHEALTH],[SSS],[TIN],[ResAddress],[ResZipCode]
				,[ResTelNo],[PermAddress],[PermZipCode],[PermTelNo],[EmailAddress],[CellNo],[DateEntered]
				,[EnteredBy],[DateModified],[ModifiedBy],[StatusId],[PositionId],[SalaryGrade],[MonthlySalary]
				,[EmpTypeId],[AppointmentDate],[OfficeId],[DepartmentId],[BureauId],[SalaryPerMonth],[InActive]
				,[IsFaculty],[IsAccountant],[LWOPSalary],[PERASalary],[Wallpaper],[pin],[flexicode],[Picture]
				,[Signature],[QRCode],[Res_HouseNo],[Res_Street],[Res_SubdivisionVillage],[Res_Province]
				,[Res_CityMunicipality],[Res_Barangay],[Res_ZIPCode],[Per_HouseNo],[Per_Street]
				,[Per_SubdivisionVillage],[Per_Province],[Per_CityMunicipality],[Per_Barangay],[Per_ZIPCode]
				,[NationalID],[Country]
				  FROM [tbl_Employees]
				  where EmpId='{$EmpID}'
					order by [LastName] ,[FirstName]", "Select", []);
			return $getEmpNamePicPostion[0];
			break;
		case "family":
			$getEmpNamePicPostion = execsqlES("SELECT [IndexId]
				  ,[EmpId]
				  ,[SpouseLastName]
				  ,[SpouseFirstName]
				  ,[SpouseMiddleName]
				  ,[SpouseOccupation]
				  ,[SpouseEmployer]
				  ,[SpouseBusinessAddress]
				  ,[SpouseTelNo]
				  ,[FatherLastName]
				  ,[FatherFirstName]
				  ,[FatherMiddleName]
				  ,[FatherNameExtension]
				  ,[FatherOccupation]
				  ,[FatherTelNo]
				  ,[MotherLastName]
				  ,[MotherFirstName]
				  ,[MotherMiddleName]
				  ,[MotherOccupation]
				  ,[MotherTelNo]
				  ,[GLastName]
				  ,[GFirstName]
				  ,[GMiddleName]
				  ,[GAddress]
				  ,[GContact]
			  FROM [db_HRIS].[dbo].[tbl_EmpFamilyBackground]
				  where EmpId='{$EmpID}'
					", "Select", []);
			return $getEmpNamePicPostion[0];
			break;
	}
}
function getEmpPerEmp1($EmpID)
{
	$getEmpNamePicPostion = execsqlES("select [EmpId],[CollegeId],[LastName],[FirstName],[MiddleName],[MiddleInitial]
	,[ExtName],CONVERT(varchar(10), DateofBirth, 23) AS [DateofBirth]
	,[PlaceofBirth],[GenderId],[CivilStatusId],[Citizenship],[Height]
	,[Weight],[BloodTypeId],[GSIS],[PAGIBIG],[PHILHEALTH],[SSS],[TIN],[ResAddress],[ResZipCode]
	,[ResTelNo],[PermAddress],[PermZipCode],[PermTelNo],[EmailAddress],[CellNo],[DateEntered]
	,[EnteredBy],[DateModified],[ModifiedBy],[StatusId],[PositionId],[SalaryGrade],[MonthlySalary]
	,[EmpTypeId],[AppointmentDate],[OfficeId],[DepartmentId],[BureauId],[SalaryPerMonth],[InActive]
	,[IsFaculty],[IsAccountant],[LWOPSalary],[PERASalary],[Wallpaper],[pin],[flexicode],[Picture]
	,[Signature],[QRCode],[Res_HouseNo],[Res_Street],[Res_SubdivisionVillage],[Res_Province]
	,[Res_CityMunicipality],[Res_Barangay],[Res_ZIPCode],[Per_HouseNo],[Per_Street]
	,[Per_SubdivisionVillage],[Per_Province],[Per_CityMunicipality],[Per_Barangay],[Per_ZIPCode]
	,[NationalID],[Country]
	  FROM [tbl_Employees]
	  where EmpId='{$EmpID}'
			order by [LastName] ,[FirstName]", "Select", []);
	return $getEmpNamePicPostion[0];
}
