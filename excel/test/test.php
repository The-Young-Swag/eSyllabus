<?php
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Sample data (replace with MSSQL query result)
$pds = [
    'surname'       => 'SANTIAGO',
    'firstname'     => 'BRYAN ANTHONY',
    'middlename'    => 'D.',
    'birthdate'     => '1990-01-15',
    'birthplace'    => 'Pangasinan',
    'sex'           => 'Male',
    'civil_status'  => 'Single',
    'citizenship'   => 'Filipino',
    'height'        => '170 cm',
    'weight'        => '65 kg',
    'blood_type'    => 'O+',
];

// Create spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('PDS');

// Column widths
$sheet->getColumnDimension('A')->setWidth(35);
$sheet->getColumnDimension('B')->setWidth(45);

// Title
$sheet->mergeCells('A1:B1');
$sheet->setCellValue('A1', 'PERSONAL DATA SHEET (CS Form No. 212, Revised 2025)');
$sheet->getStyle('A1')->applyFromArray([
    'font' => ['bold' => true, 'size' => 14],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);

// Section Header
$sheet->mergeCells('A3:B3');
$sheet->setCellValue('A3', 'I. PERSONAL INFORMATION');
$sheet->getStyle('A3')->applyFromArray([
    'font' => ['bold' => true],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'D9D9D9']
    ]
]);

// Helper function
$row = 4;
function field($sheet, &$row, $label, $value)
{
    $sheet->setCellValue("A{$row}", $label);
    $sheet->setCellValue("B{$row}", $value);
    $sheet->getStyle("A{$row}:B{$row}")->applyFromArray([
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN]
        ]
    ]);
    $row++;
}

// Fields
field($sheet, $row, 'SURNAME', $pds['surname']);
field($sheet, $row, 'FIRST NAME', $pds['firstname']);
field($sheet, $row, 'MIDDLE NAME', $pds['middlename']);
field($sheet, $row, 'DATE OF BIRTH', date('F d, Y', strtotime($pds['birthdate'])));
field($sheet, $row, 'PLACE OF BIRTH', $pds['birthplace']);
field($sheet, $row, 'SEX', $pds['sex']);
field($sheet, $row, 'CIVIL STATUS', $pds['civil_status']);
field($sheet, $row, 'CITIZENSHIP', $pds['citizenship']);
field($sheet, $row, 'HEIGHT', $pds['height']);
field($sheet, $row, 'WEIGHT', $pds['weight']);
field($sheet, $row, 'BLOOD TYPE', $pds['blood_type']);

// Download headers
$filename = "PDS_CS_Form_212_Rev_2025.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

// Output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>