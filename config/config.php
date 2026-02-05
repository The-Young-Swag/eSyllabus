<?php

$srsServer = "LAPTOP-BRJH0IKI\MSSQLSERVER2022"; //SysServer  SysDB
$srsDB = "eSyllabus";

#HRMO live connection
$SysServer = "LAPTOP-BRJH0IKI\MSSQLSERVER2022";
$SysDB = "eSyllabus";
/*
#HRMO live connection
$SysLVServer = "LAPTOP-1309CGUP";
$SysLVDB = "db_HRIS"; */

# dashboard 
$dashTitle = "eSyllabus";
$cpy = "Tarlac Agricultural University";
$vrs = "0.0.0";

$taboffice = [
	"0" =>
	[
		"tabID" => "offices",
		"tabCode" => "dtOffice",
		"TabLabel" => "Offices",
		"dttarget" => "addOfficeModal"
	],
	"1" =>
	[
		"tabID" => "departments",
		"tabCode" => "dtDep",
		"TabLabel" => "Departments",
		"dttarget" => "addDepartmentModal"
	],
	"2" =>
	[
		"tabID" => "units",
		"tabCode" => "dtunits",
		"TabLabel" => "Units",
		"dttarget" => "addUnitModal"
	]
];
