<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$inputFile = __DIR__ . '/PDS.xlsx'; // <-- fixed slashes
$outputFile = __DIR__ . '/PDS_UPDATED.xlsx';

// Make sure the file exists
if (!file_exists($inputFile)) {
    die("File not found: $inputFile");
}

// Load existing Excel
$spreadsheet = IOFactory::load($inputFile);
//$sheet = $spreadsheet->getActiveSheet();

$sheet = $spreadsheet->getSheetByName('C1');
// Set values
$sheet->setCellValue('D10', 'SANTIAGO');
$sheet->setCellValue('D11', 'BRYAN ANTHONY');
$sheet->setCellValue('D12', 'D.');


$sheet = $spreadsheet->getSheetByName('C2');
$searchValue = 'CES/CSEE/CAREER SERVICE/RA 1080 (BOARD/ BAR)/UNDER SPECIAL LAWS/CATEGORY II/ IV ELIGIBILITY and ELIGIBILITIES FOR UNIFORMED PERSONNEL';

$foundCells = [];

foreach ($sheet->getRowIterator() as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);

    foreach ($cellIterator as $cell) {
        if (trim((string)$cell->getValue()) === $searchValue) {
            $foundCells[] = [
                'cell'   => $cell->getCoordinate(),   // e.g. A4
                'row'    => $cell->getRow(),           // 4
                'column' => $cell->getColumn(),        // A
            ];
        }
    }
}
//print_r($foundCells);
 // create new row
$sheet->insertNewRowBefore($foundCells[0]['row']+2, 1);
$sheet->setCellValue('A6', 'CES/CSEE');



$writer = new Xlsx($spreadsheet);
$writer->save($outputFile);

echo "File updated successfully: $outputFile";

/* header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="PDS_UPDATED.xlsx"');
header('Cache-Control: max-age=0');
 */
?>