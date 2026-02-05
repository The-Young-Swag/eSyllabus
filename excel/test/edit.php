<?php
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$inputFile = __DIR__ . '/../PDS25.xlsx';
$outputFile = __DIR__ . '/../PDS_UPDATED.xlsx';

echo __DIR__ ."<br>";
// Make sure the file exists
if (!file_exists($inputFile)) {
    die("File not found: $inputFile");
}

// Load existing Excel
$spreadsheet = IOFactory::load($inputFile);
$sheet = $spreadsheet->getActiveSheet();

// Set values
$sheet->setCellValue('D10', 'SANTIAGO');
$sheet->setCellValue('D11', 'BRYAN ANTHONY');
$sheet->setCellValue('D12', 'D.');

// Save updated file
$writer = new Xlsx($spreadsheet);
$writer->save($outputFile);

echo "File updated successfully: $outputFile";
?>