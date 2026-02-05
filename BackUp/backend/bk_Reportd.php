<?php
include "../db/dbconnection.php";

  $request = isset($_POST["request"])?$_POST["request"]:"";
  //$valsearch = isset($_POST["valsearch"])?$_POST["valsearch"]:"";
  
  //$AnnualMedicalExam = new AnnualMedicalExam;
  
	switch ($request) {	
		case"Verify":	
			$TM_getEmpDTRSumaary= execsqlES("EXEC [dbo].[TM_getEmpDTRSumaary] '2020-01-01','Planning and Development Office'"
				,"Select"
				,array());
			print_r($TM_getEmpDTRSumaary);
		break;		
	}
?>