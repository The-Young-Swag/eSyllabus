<?php
include "../db/dbcon.classes.php";
include "../config/config.php";
include "../classes/AnnualMedicalExam/AnnualMedicalExam.classes.php";

  
  $request = isset($_GET["request"])?$_GET["request"]:(isset($_POST["request"])?$_POST["request"]:"");
  $valsearch = isset($_POST["valsearch"])?$_POST["valsearch"]:"";
  
  $AnnualMedicalExam = new AnnualMedicalExam;
  
	switch ($request) {	
		case"RevPrescri":	
			$AMPresID = isset($_POST["AMPresID"])?$_POST["AMPresID"]:"";
			$AnnualMedicalExam->DelRec($AMPresID);
		break;			
		case"updatePerField":	
			$AMPresID = isset($_POST["AMPresID"])?$_POST["AMPresID"]:"";
			$prescription = isset($_POST["prescription"])?$_POST["prescription"]:"";
			$AnnualMedicalExam->updatePerField($AMPresID,$prescription);
		break;			
		case"DoneConsult":	
			$elem = $_POST;
			
			$anExist = $AnnualMedicalExam->GetClientAnnualRec($_POST["elemAppno"],$_POST["elemAnnualYear"]);
			
			if(isset($anExist[0])){				
				echo json_encode(array("status"=>"Existed"));
			}else{
				$AnnualMedicalExam->SaveRec($_POST);	
				echo json_encode(array("status"=>"Inserted"));
			}
		break;		
		
		case"AddPrescriptions":	
			$elem = $_POST;
			
			$anExist = $AnnualMedicalExam->GetClientAnnualRec($_POST["elemAppno"],$_POST["elemAnnualYear"]);
			
			if(isset($anExist[0])){				
				
				$AMEID = isset($anExist[0])?$anExist[0]["AMEID"]:"";
				$AnnualMedicalExam->InsertRec($AMEID);
				PrescriptionsDetails($AnnualMedicalExam->GetAnnualMedicalPrescription($AMEID));
			}else{
				
				$AnnualMedicalExam->SaveRec($_POST);	
				$anExist = $AnnualMedicalExam->GetClientAnnualRec($_POST["elemAppno"],$_POST["elemAnnualYear"]);
				$AMEID = isset($anExist[0])?$anExist[0]["AMEID"]:"";
				$AnnualMedicalExam->InsertRec($AMEID);
				PrescriptionsDetails($AnnualMedicalExam->GetAnnualMedicalPrescription($AMEID));
				
			}
		break;	
		case"ViewPrescrip":	
			$anExist = $AnnualMedicalExam->GetClientAnnualRec($_POST["elemAppno"],$_POST["elemAnnualYear"]);
			
			if(isset($anExist[0])){						
				$AMEID = isset($anExist[0])?$anExist[0]["AMEID"]:"";
				PrescriptionsDetails($AnnualMedicalExam->GetAnnualMedicalPrescription($AMEID));
			}else{
				echo "No Record/s found...";
				
			}
		break;	
	} 

	function PrescriptionsDetails($GetAnnualMedicalPrescription){
		
		foreach($GetAnnualMedicalPrescription as $AnnualMedPres){
			echo "		
				<div class='col-sm-4'  style='border:1px solid gray;' id='dvPres{$AnnualMedPres["AMPresID"]}'>					
					<label class='form-label form-control-sm' for='inputsm'>Prescription :</label>
					<textarea id='txtarea' 
						name='txtarea' 
						data-AMPresID='{$AnnualMedPres["AMPresID"]}'
						class='form-control form-control-sm'>{$AnnualMedPres["prescription"]}</textarea>				
					<button 
						class='form-label form-control-sm' 
						id='btnRv' 
						data-AMPresID='{$AnnualMedPres["AMPresID"]}'>Remove</button>
					<br>
				</div>
			";
		}
	}
?>