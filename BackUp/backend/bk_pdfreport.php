<?php
// Include the mPDF autoloader
require_once '../vendor/autoload.php';

include "../db/dbconnection.php";

$request = isset($_POST["request"])?$_POST["request"]:"InsertNewRec";
$details="";

$details .= "
	<html>
		<head>
		<style>
		table, th, td {
		  border: 1px solid black;
		  border-collapse: collapse;padding: 70px;
		}
		</style>
		</head>
		<body>
		<table border=1>
			<thead>
			<tr class='border'>
			  <th rowspan='3' class='border'><img src='../image/reportBanner.png' alt='Flowers in Chania'></th></tr>
			</tbody>
		  </table>
		  
		  ";

switch ($request) {
	
	case"SummaryDTRReport":	
	
	$details .= "
		<table border=1>
			<thead>
			<tr class='border'>
			  <th rowspan='3' class='border'></th>
			  <th  rowspan='3' class='border'>NAME</th>
			  <th  rowspan='3' class='border'>POSITION</th>
			  <th rowspan='3' class='border'>Date Late</th>
			  <th colspan='4' class='border'>No. of Days Late</th>
			  
			  <!-- Undertime -->
			  <th rowspan='3' class='border'>Date Undertime</th>
			  <th colspan='4' class='border'>No. of Days Undertime</th>
			  <!-- End Undertime -->
			  
			  <th class='border'>Has CS </th>
			  <th  rowspan='3' class='border'>REMARKS</th>
			</tr>
			<tr class='border'>
			  <th class='border' colspan='3'>Late</th>
			  <th class='border'>Equivalent</th>
			  
			  
			  <!-- Undertime -->
			  <th class='border' colspan='3'>Undertime</th>
			  <th class='border'>Equivalent</th>					  
			  <!-- End Undertime -->
			  
			  <th class='border'>Form 6 been</th>
			  
			</tr>
			<tr class='border'>
			  <th class='border'>times of late</th>
			  <th class='border'>hrs</th>
			  <th class='border'>mins</th>
			  <th class='border'>Days</th>
			  
			  
			  <!-- Undertime -->
			  <th class='border'>times of undertime</th>
			  <th class='border'>hrs</th>
			  <th class='border'>mins</th>
			  <th class='border'>Days</th>
			  <!-- End Undertime -->
			  
			  <th class='border'>submitted </th>
			</tr>
			</thead>
			<tbody id='DataSummaryDTRReport'>
	 
	 ";
		
		
		
		$Month = isset($_POST["Month"])?$_POST["Month"]:"";
			$Year = isset($_POST["Year"])?$_POST["Year"]:"";
			$office = isset($_POST["office"])?$_POST["office"]:"";
			
			$date = $Year . "-". str_pad($Month, 2, '0', STR_PAD_LEFT)."-01";
			//echo $date;
			$TM_getEmpDTRSumaary= execsqlES("EXEC [dbo].[TM_getEmpDTRSumaary] '{$date}','{$office}'"
				,"Select"
				,array());
			$nmRow = 0;
			
			foreach($TM_getEmpDTRSumaary as $EmpDTRSumaary){
				$nmRow ++;
				$TotalHoursLate = $EmpDTRSumaary["TotalHoursLate"]==0?0:$EmpDTRSumaary["TotalHoursLate"];
				$TotalMinuteLate = $EmpDTRSumaary["TotalMinuteLate"]==0?0:$EmpDTRSumaary["TotalMinuteLate"];
				
				$TotalHoursUnderTime = $EmpDTRSumaary["TotalHoursUnderTime"]==0?0:$EmpDTRSumaary["TotalHoursUnderTime"];
				$UnderTimeMinute = $EmpDTRSumaary["UnderTimeMinute"]==0?0:$EmpDTRSumaary["UnderTimeMinute"];
				$details .= "
					<tr>
                      <td class='border'>{$nmRow}</td>
                      <td class='border'>{$EmpDTRSumaary["empName"]}</td>
                      <td class='border'>{$EmpDTRSumaary["Position"]}</td>
                      <td class='border'>{$EmpDTRSumaary["DateTardy"]}</td>
                      <td class='border'>{$EmpDTRSumaary["TardyCount"]}</td>
                      <td class='border'>{$TotalHoursLate}</td>
                      <td class='border'>{$TotalMinuteLate}</td>";
					$Days = number_format((($TotalHoursLate+($TotalMinuteLate/60))/8),2);
				$details .= "
                      <td class='border'>{$Days}</td>
					  
                      <td class='border'>{$EmpDTRSumaary["DateUnderTime"]}</td>
                      <td class='border'>{$EmpDTRSumaary["UnderTimeCount"]}</td>
                      <td class='border'>{$TotalHoursUnderTime}</td>
                      <td class='border'>{$UnderTimeMinute}</td>";
					$UDays =  number_format((($TotalHoursUnderTime+($UnderTimeMinute/60))/8),2);
				$details .= "
                      <td class='border'>{$UDays}</td>
					  
                      <td class='border'></td>
                      <td class='border'>REMARKS</td>
                    </tr>
				";
			}
		
		
		
        $details .=  "
                    </tbody>
                  </table>";
		
	break;
}					

    $details .=  "
		</body>
		</html>";
$mpdf = new \Mpdf\Mpdf(['orientation' => 'L']);

// Write HTML content
$html = "
    <h1>Welcome to mPDF!</h1>
    <p>This is a simple example to create a PDF using mPDF library.</p>
    <p><strong>mPDF is a PHP library which generates PDF files from HTML content.</strong></p>
";

// Write the HTML to the PDF
$mpdf->WriteHTML($details);

// Output the PDF to the browser
$mpdf->Output('../vendor/DirFile/PDFGen.pdf', 'F'); // 'I' means output to browser, 'D' for download, 'F' for file saving

?>