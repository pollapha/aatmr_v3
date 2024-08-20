<?php
include('../vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

$dataQuotationArray = array();

$no = 1;
foreach ($dataArray as $row) {
    $formattedData = [
        $row['tripType'],
        $row['truckType'],
        $row['distanceBand'],
        $row['unitRate'],
    ];
    $no++;
    $dataQuotationArray[] = $formattedData;
}

// var_dump($dataQuotationArray);
// exit();


function applyFont($worksheet, $range, $fontSize = 10, $color = 0)
{
    $styleArray = [
        'font' => [
            'bold' => true,
            'size' => $fontSize,
        ],
    ];

    if ($color === 1) {
        $styleArray['font']['color'] = ['rgb' => '0000FF'];
    }

    $worksheet->getStyle($range)->applyFromArray($styleArray);
}

function applyBorders($worksheet, $range, $borderCode, $inside = 1)
{
    $borderDefinitions = [
        'thin' => Border::BORDER_THIN,
        'thick' => Border::BORDER_THICK,
        'double' => Border::BORDER_DOUBLE,
    ];

    $borderStyle = [];
    for ($i = 0; $i < strlen($borderCode); $i += 2) {
        $char = $borderCode[$i];
        $styleCode = $borderCode[$i + 1];
        $borderStyleKey = '';
        if ($char === 'T') {
            $borderStyleKey = 'top';
        } elseif ($char === 'B') {
            $borderStyleKey = 'bottom';
        } elseif ($char === 'L') {
            $borderStyleKey = 'left';
        } elseif ($char === 'R') {
            $borderStyleKey = 'right';
        }

        if ($styleCode === '0') {
            $borderStyle['borders'][$borderStyleKey]['borderStyle'] = $borderDefinitions['thick'];
        } elseif ($styleCode === '2') {
            $borderStyle['borders'][$borderStyleKey]['borderStyle'] = $borderDefinitions['double'];
        } else {
            $borderStyle['borders'][$borderStyleKey]['borderStyle'] = $borderDefinitions['thin'];
        }
    }

    if ($inside === 1) {
        $borderStyle['borders']['inside'] = [
            'borderStyle' => Border::BORDER_THIN,
        ];
    }

    $worksheet->getStyle($range)->applyFromArray($borderStyle);
}

function setDefaultStylesSheet($worksheet)
{
    $styles = [
        'A' => ['width' => 15.00], 'B' => ['width' => 15.00], 'C' => ['width' => 15.00], 'D' => ['width' => 15.00], 'E' => ['width' => 50.00]
    ];
    $worksheet->getParent()->getDefaultStyle()->getFont()->setName('Calibri');
    $worksheet->getParent()->getDefaultStyle()->getFont()->setSize(9);
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setVertical('Center');
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setWrapText(true);
    //$worksheet->getParent()->getDefaultStyle()->getAlignment()->setHorizontal('Center');

    //$worksheet->getRowDimension('2')->setRowHeight(5, 'pt');
    foreach ($styles as $col => $style) {
        $worksheet->getColumnDimension($col)->setWidth($style['width']);
    }
}

function addDetailTableSheet($worksheet, $data, $row, $col, $border = 1)
{
    $row += 1;
    if (empty($data)) {
        return $row;
    }

    //var_dump($data);

    foreach ($data as $rowData) {
        for ($i = 0; $i < count($col); $i++) {
            $worksheet->setCellValue($col[$i] . $row, $rowData[$i]);
        }
        $row++;
    }
    return $row;
}

function addHeaderData($worksheet)
{
    global $start_date;
    global $project_name;
    global $billing_for;


    $date = date_create($start_date);
    $start_date = date_format($date, "Y-m-01");

    $cellData = [
        //header table
        'A1' => ['value' => 'Start Date (วันแรกของเดือนที่เริ่มใช้)', 'alignment' => 'center',],
        'A2' => ['value' => 'Project', 'alignment' => 'center',],
        'A3' => ['value' => 'Biling For', 'alignment' => 'center',],
        'A4' => ['value' => 'Special Trip', 'alignment' => 'center',],

        'D1' => ['value' => "$start_date", 'alignment' => 'center',],
        'D2' => ['value' => "$project_name", 'alignment' => 'center',],
        'D3' => ['value' => "$billing_for", 'alignment' => 'center',],
        'D4' => ['value' => 'No', 'alignment' => 'center',],

        'E2' => ['value' => '*สำหรับ Partner ไม่ต้องใส่ ชื่อ Project ', 'alignment' => 'center',],
        'E3' => ['value' => '* Customer or Partner', 'alignment' => 'center',],
        'E4' => ['value' => '*ใส่ Yes เฉพาะกรณีต้องการอัปโหลด Trip Special', 'alignment' => 'center',],

        'A5' => ['value' => 'No.', 'alignment' => 'center',],
        'B5' => ['value' => 'Truck Control No.', 'alignment' => 'center',],
        'C5' => ['value' => 'Truck Control Date', 'alignment' => 'center',],
        'D5' => ['value' => 'Status', 'alignment' => 'center',],
    ];


    $worksheet->getStyle('A5:D5')
        ->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('def0fc');


    $worksheet->getStyle("F")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);

    $worksheet->mergeCells('A1:C1');
    $worksheet->mergeCells('A2:C2');
    $worksheet->mergeCells('A3:C3');
    $worksheet->mergeCells('A4:C4');


    foreach ($cellData as $cell => $data) {
        $worksheet->setCellValue($cell, $data['value']);
        $cellStyle = $worksheet->getStyle($cell);
        $cellStyle->getAlignment()->setHorizontal($data['alignment']);
        $cellStyle->getAlignment()->setVertical('center');
    }

    //applyBorders($worksheet, 'A3:H3', 'T1B1R1L1');
}


function summary_data($worksheet, $row, $data)
{
    $col = [
        'A', 'B', 'C', 'D',
    ];

    $dataStartRow = $row + 1;
    $lastRow = addDetailTableSheet($worksheet, $data, $row, $col);
    $row = $lastRow;
    $borderCode = 'L1R1T1B1';
    $row_border = $row - 1;
    applyBorders($worksheet, 'A5:D' . $row_border, $borderCode);
    $worksheet->getStyle('A5:D' . $row_border, $borderCode)->getAlignment()->setVertical('center');
    $worksheet->getStyle('A5:D' . $row_border, $borderCode)->getAlignment()->setHorizontal('center');

    return $row;
}


$spreadsheet = new Spreadsheet();
$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setShowGridlines(true);
$worksheet->setTitle('Sheet 1');
setDefaultStylesSheet($worksheet);
addHeaderData($worksheet);
$row = 5;
summary_data($worksheet, $row, $dataQuotationArray);

// $date = date_create($Start_Date);
// $Start_Date = date_format($date, "d-m-Y");

//$date = date('Y-m-d');
$filename = 'excel/fileoutput/template_upload_trip_quotation.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($filename);
