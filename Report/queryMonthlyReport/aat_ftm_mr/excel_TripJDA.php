<?php
include('../vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Workshhet\wo;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;


$normalArray = array();
$extraArray = array();
$emptyPackageArray = array();
$addNewArray = array();
$tripSummaryArray = array();

$no = 1;
$sh = 1;


foreach ($dataArraySummaryTrip as $row) {

    // if ($project_name == 'FTM MR') {
    //     $distanceBand = str_replace(' ', '', $row['distanceBand']);
    // } else {
    //     $distanceBand = $row['distanceBand'];
    // }

    // if ($project_name == 'FTM MRF') {
    //     if (strpos(strtolower($row['tripType']), 'way') !== false) {
    //         $tripType = '1-WAY';
    //     } elseif (strpos(strtolower($row['tripType']), 'round') !== false) {
    //         $tripType = 'Round';
    //     }
    // } else {
    //     if (strpos(strtolower($row['tripType']), 'way') !== false) {
    //         $tripType = 'One-way';
    //     } elseif (strpos(strtolower($row['tripType']), 'round') !== false) {
    //         $tripType = 'Round Trip';
    //     }
    // }

    //truck_carrier, Work_Type, Load_ID, tripType, operation_date, Start_Datetime, End_Datetime, Route, distanceBand, projectName, truckType
    $formattedData = [
        $no,
        $row['truck_carrier'],
        $row['Work_Type'],
        $row['Load_ID'],
        $row['tripType'],
        $row['operation_date'],
        $row['Start_Datetime'],
        $row['End_Datetime'],
        $row['Route'],
        $row['distanceBand'],
        $row['projectName'],
        $row['truckType'],
    ];

    // var_dump($formattedData);
    // exit();

    $tripSummaryArray[] = $formattedData;
    $no++;
}


function setDefaultStylesSheet($worksheet)
{
    $styles = [
        'A' => ['width' => 10.00], 'B' => ['width' => 15.00], 'C' => ['width' => 15.00],
        'D' => ['width' => 15.00], 'E' => ['width' => 15.00], 'F' => ['width' => 15.00], 'G' => ['width' => 15.00],
        'H' => ['width' => 15.00], 'I' => ['width' => 15.00], 'J' => ['width' => 15.00], 'K' => ['width' => 15.00],
        'L' => ['width' => 15.00],
    ];
    $worksheet->getParent()->getDefaultStyle()->getFont()->setName('Arial');
    $worksheet->getParent()->getDefaultStyle()->getFont()->setSize(10);
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setVertical('Center');
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setHorizontal('Center');
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setWrapText(true);

    // $worksheet->getRowDimension('2')->setRowHeight(25, 'pt');
    // $worksheet->getRowDimension('3')->setRowHeight(20, 'pt');
    // $worksheet->getRowDimension('4')->setRowHeight(5, 'pt');
    // $worksheet->getRowDimension('5')->setRowHeight(20, 'pt');
    foreach ($styles as $col => $style) {
        $worksheet->getColumnDimension($col)->setWidth($style['width']);
    }
}

function addDetailTableSheet($worksheet, $data, $row, $col, $border = 1)
{
    if (empty($data)) {
        return $row;
    }

    foreach ($data as $rowData) {
        //var_dump ($rowData);
        for ($i = 0; $i < count($col); $i++) {
            $worksheet->setCellValue($col[$i] . $row, $rowData[$i]);
        }
        $row++;
    }
    return $row;
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

function trip_summary_report_data($worksheet, $row, $data)
{
    $col = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'];
    $dataStartRow = $row + 1;
    $lastRow = addDetailTableSheet($worksheet, $data, $row, $col);
    $row = $lastRow;
    $borderCode = 'L1R1T1B1';
    $row_border = $row - 1;
    applyBorders($worksheet, 'A2:L' . $row_border, $borderCode);
    return $row;
}


//$trip_summary_report_row = $row;

function addHeaderSheet($worksheet)
{
    $cellData = [
        //header table
        'A1' => ['value' => 'No', 'alignment' => 'center',],
        'B1' => ['value' => 'Carrier', 'alignment' => 'center',],
        'C1' => ['value' => 'Order Status', 'alignment' => 'center',],
        'D1' => ['value' => 'Load ID', 'alignment' => 'center',],
        'E1' => ['value' => 'Status Route', 'alignment' => 'center',],
        'F1' => ['value' => 'Operation Date', 'alignment' => 'center',],
        'G1' => ['value' => 'Pick Up Date', 'alignment' => 'center',],
        'H1' => ['value' => 'Delivery Date', 'alignment' => 'center',],
        'I1' => ['value' => 'Route Number', 'alignment' => 'center',],
        'J1' => ['value' => 'Distance Band', 'alignment' => 'center',],
        'K1' => ['value' => 'Route Refer', 'alignment' => 'center',],
        'L1' => ['value' => 'Truck Type TTV Use', 'alignment' => 'center',],
    ];


    $worksheet->getStyle('A1:L1')
        ->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('EDEDED');

    $worksheet->getStyle('A1:L1')->getFont()->setBold(true);


    foreach ($cellData as $cell => $data) {
        $worksheet->setCellValue($cell, $data['value']);
        $cellStyle = $worksheet->getStyle($cell);
        $cellStyle->getAlignment()->setHorizontal($data['alignment']);
        $cellStyle->getAlignment()->setVertical('center');
    }

    applyBorders($worksheet, 'A1:L1', 'T1B1R1L1');
    //exit();
}

$spreadsheet = new Spreadsheet();
$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setShowGridlines(false);
$worksheet->setTitle('Data JDA (System)');
setDefaultStylesSheet($worksheet);
addHeaderSheet($worksheet);
$row = 2;
trip_summary_report_data($worksheet, $row, $tripSummaryArray);
$worksheet->setAutoFilter('A1:L1');


$date = date_create($start_date);
$start_date = date_format($date, "M y");
$filename = 'fileoutput/JDA ' . $project_name . ' ' . $start_date . '.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($filename);
