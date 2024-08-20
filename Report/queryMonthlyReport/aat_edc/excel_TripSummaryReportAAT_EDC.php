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


$tripSummaryArray = array();
$tripBailmentSummaryArray = array();
$tripNonBailmentSummaryArray = array();
$tripEmptySummaryArray = array();

$no = 1;
$sh = 1;

$no1 = 1;
$no2 = 1;
$no3 = 1;
$no4 = 1;

foreach ($dataArraySummaryTrip as $row) {
    $formattedData = [
        $no,
        $row['operation_date'],
        $row['Start_Datetime'],
        $row['End_Datetime'],
        $row['Load_ID'],
        $row['Route'],
        $row['bailment'],
        $row['Work_Type'],
        $row['truckType'],
        $row['tripType'],
        $row['distanceBand'],
        $row['unitRate'],
        $row['Qty_Trip'],
        0
    ];
    $tripSummaryArray[] = $formattedData;
    $no++;

    if ($row['bailment'] == 'Bailment' and $row['Work_Type'] != 'Empty Package') {
        $formattedData = [
            $no1,
            $row['operation_date'],
            $row['Start_Datetime'],
            $row['End_Datetime'],
            $row['Load_ID'],
            $row['Route'],
            $row['bailment'],
            $row['Work_Type'],
            $row['truckType'],
            $row['tripType'],
            $row['distanceBand'],
            $row['unitRate'],
            $row['Qty_Trip'],
            0
        ];
        $tripBailmentSummaryArray[] = $formattedData;
        $no1++;
    } elseif ($row['bailment'] == 'Non Bailment' and $row['Work_Type'] != 'Empty Package') {
        $formattedData = [
            $no2,
            $row['operation_date'],
            $row['Start_Datetime'],
            $row['End_Datetime'],
            $row['Load_ID'],
            $row['Route'],
            $row['bailment'],
            $row['Work_Type'],
            $row['truckType'],
            $row['tripType'],
            $row['distanceBand'],
            $row['unitRate'],
            $row['Qty_Trip'],
            0
        ];
        $tripNonBailmentSummaryArray[] = $formattedData;
        $no2++;
    } elseif ($row['Work_Type'] == 'Empty Package') {
        
        $formattedData = [
            $no3,
            $row['operation_date'],
            $row['Start_Datetime'],
            $row['End_Datetime'],
            $row['Load_ID'],
            $row['Route'],
            $row['bailment'],
            $row['Work_Type'],
            $row['truckType'],
            $row['tripType'],
            $row['distanceBand'],
            $row['unitRate'],
            $row['Qty_Trip'],
            0
        ];
        $tripEmptySummaryArray[] = $formattedData;
        $no3++;
    }
}

function sumTableSheet2($worksheet, $row, $dataStartRow, $col)
{
    $index = count($col) - 1;

    $lastDataRow = $row;

    $worksheet->getStyle('P2')->getNumberFormat()->setFormatCode('#,##0');
    $worksheet->getStyle('Q2')->getNumberFormat()->setFormatCode('#,##0');
    $worksheet->getStyle('R2')->getNumberFormat()->setFormatCode('#,##0');
    $worksheet->setCellValue('P2', "=SUBTOTAL(9,P$dataStartRow:P$lastDataRow)");
    $worksheet->setCellValue('Q2', "=SUBTOTAL(9,Q$dataStartRow:Q$lastDataRow)");
    //$worksheet->setCellValue('R2', "=SUBTOTAL(9,R$dataStartRow:R$lastDataRow)");
    return $row;
}

function setDefaultStylesSheet2($worksheet)
{
    $styles = [
        'A' => ['width' => 0.00], 'B' => ['width' => 0.00], 'C' => ['width' => 0.00],
        'D' => ['width' => 10.00], 'E' => ['width' => 15.00], 'F' => ['width' => 15.00], 'G' => ['width' => 15.00],
        'H' => ['width' => 15.00], 'I' => ['width' => 15.00], 'J' => ['width' => 15.00], 'K' => ['width' => 15.00],
        'L' => ['width' => 15.00], 'M' => ['width' => 15.00], 'N' => ['width' => 15.00], 'O' => ['width' => 15.00],
        'P' => ['width' => 15.00], 'Q' => ['width' => 15.00], 'R' => ['width' => 20.00],
    ];
    $worksheet->getParent()->getDefaultStyle()->getFont()->setName('Arial');
    $worksheet->getParent()->getDefaultStyle()->getFont()->setSize(10);
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setVertical('Center');
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setHorizontal('Center');
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setWrapText(true);

    $worksheet->getRowDimension('2')->setRowHeight(25, 'pt');
    $worksheet->getRowDimension('3')->setRowHeight(20, 'pt');
    $worksheet->getRowDimension('4')->setRowHeight(5, 'pt');
    $worksheet->getRowDimension('5')->setRowHeight(20, 'pt');
    foreach ($styles as $col => $style) {
        $worksheet->getColumnDimension($col)->setWidth($style['width']);
    }
}

function addDetailTableSheet2($worksheet, $data, $row, $col, $border = 1)
{
    $row += 1;
    if (empty($data)) {
        return $row;
    }

    foreach ($data as $rowData) {
        //var_dump ($rowData);
        for ($i = 0; $i < count($col) - 1; $i++) {
            //$horizontalAlignment = 'center';
            if (in_array($col[$i], ['Q'])) {
                //$horizontalAlignment = 'right';
                $worksheet->setCellValue('Q' . $row, '=O' . $row . '*P' . $row);
                //$cellStyle->getAlignment()->setHorizontal($horizontalAlignment);
            } else {
                $worksheet->setCellValue($col[$i] . $row, $rowData[$i]);
            }
        }
        //applyBorders($worksheet, 'S' . $row, $borderCode);
        $row++;
    }
    return $row;
}


//$trip_summary_report_row = $row;

function addHeaderSheet2($worksheet)
{
    $cellData = [
        'D2' => ['value' => 'TTVT Trip Summary Report', 'alignment' => 'left'],
        'O2' => ['value' => 'Total', 'alignment' => 'center',],
        'R2' => ['value' => 'Bath', 'alignment' => 'left',],
        'D3' => ['value' => 'Master Data Aligned with JDA', 'alignment' => 'left'],
        'K3' => ['value' => 'Carrier Data (shall be align with approval)', 'alignment' => 'center'],

        //header table
        'D5' => ['value' => 'No', 'alignment' => 'center',],
        'E5' => ['value' => 'Operation Date', 'alignment' => 'center',],
        'F5' => ['value' => 'Pick Up Date', 'alignment' => 'center',],
        'G5' => ['value' => 'Delivery Date', 'alignment' => 'center',],
        'H5' => ['value' => 'Blueyonder Tracking no.', 'alignment' => 'center',],
        'I5' => ['value' => 'Blueyonder Route no.', 'alignment' => 'center',],
        'J5' => ['value' => 'Project', 'alignment' => 'center',],
        'K5' => ['value' => 'Status', 'alignment' => 'center',],
        'L5' => ['value' => 'Equipment Type TTV Use', 'alignment' => 'center',],
        'M5' => ['value' => 'One way / Round trip', 'alignment' => 'center',],
        'N5' => ['value' => 'Distance Band', 'alignment' => 'center',],
        'O5' => ['value' => 'Unit Rate', 'alignment' => 'center',],
        'P5' => ['value' => 'Qty of Trip', 'alignment' => 'center',],
        //'Q5' => ['value' => 'Drop charge', 'alignment' => 'center',],
        'Q5' => ['value' => 'Total Billed Amount', 'alignment' => 'center',],
        'R5' => ['value' => 'Remark', 'alignment' => 'center',],
    ];


    $worksheet->mergeCells('D2:J2');
    $worksheet->mergeCells('D3:J3');
    $worksheet->mergeCells('K3:R3');
    $worksheet->getStyle('D2')->getFont()->setSize(20);
    $worksheet->getStyle('O2:R2')->getFont()->setSize(11);
    $worksheet->getStyle('D3:R3')->getFont()->setSize(12);

    $worksheet->getStyle('D2:R2')->getFont()->setBold(true);
    $worksheet->getStyle('D3:R3')->getFont()->setBold(true);
    $worksheet->getStyle('D5:R5')->getFont()->setBold(true);

    $worksheet->getStyle('O2:R2')->getFont()->setUnderline(2);
    $worksheet->getStyle('D3')
        ->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FCD5B4');
    $worksheet->getStyle('K3')
        ->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('00B050');
    $worksheet->getStyle('D5:R5')
        ->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('0066FF');


    foreach ($cellData as $cell => $data) {
        $worksheet->setCellValue($cell, $data['value']);
        $cellStyle = $worksheet->getStyle($cell);
        $cellStyle->getAlignment()->setHorizontal($data['alignment']);
        $cellStyle->getAlignment()->setVertical('center');
    }

    applyBorders($worksheet, 'D3:J3', 'T0B0R0L0');
    applyBorders($worksheet, 'K3:R3', 'T0B0R0L0');
    applyBorders($worksheet, 'D5:R5', 'T1B1R1L1');
    //exit();
}


function sumTableSheetConditional($worksheet, $row, $dataStartRow, $col)
{
    $index = count($col) - 1;

    $lastDataRow = $row;

    $worksheet->getStyle('P2')->getNumberFormat()->setFormatCode('#,##0');
    $worksheet->getStyle('Q2')->getNumberFormat()->setFormatCode('#,##0');
    $worksheet->getStyle('R2')->getNumberFormat()->setFormatCode('#,##0');
    $worksheet->setCellValue('P2', "=SUBTOTAL(9,P$dataStartRow:P$lastDataRow)");
    $worksheet->setCellValue('Q2', "=SUBTOTAL(9,Q$dataStartRow:Q$lastDataRow)");
    //$worksheet->setCellValue('R2', "=SUBTOTAL(9,R$dataStartRow:R$lastDataRow)");
    return $row;
}

function setDefaultStylesSheetConditional($worksheet)
{
    $styles = [
        'A' => ['width' => 0.00], 'B' => ['width' => 0.00], 'C' => ['width' => 0.00],
        'D' => ['width' => 10.00], 'E' => ['width' => 15.00], 'F' => ['width' => 15.00], 'G' => ['width' => 15.00],
        'H' => ['width' => 15.00], 'I' => ['width' => 15.00], 'J' => ['width' => 15.00], 'K' => ['width' => 15.00],
        'L' => ['width' => 15.00], 'M' => ['width' => 15.00], 'N' => ['width' => 15.00], 'O' => ['width' => 15.00],
        'P' => ['width' => 15.00], 'Q' => ['width' => 15.00], 'R' => ['width' => 20.00]
    ];
    $worksheet->getParent()->getDefaultStyle()->getFont()->setName('Arial');
    $worksheet->getParent()->getDefaultStyle()->getFont()->setSize(10);
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setVertical('Center');
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setHorizontal('Center');
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setWrapText(true);

    $worksheet->getRowDimension('2')->setRowHeight(25, 'pt');
    $worksheet->getRowDimension('3')->setRowHeight(5, 'pt');
    $worksheet->getRowDimension('4')->setRowHeight(25, 'pt');
    //$worksheet->getRowDimension('5')->setRowHeight(20, 'pt');
    foreach ($styles as $col => $style) {
        $worksheet->getColumnDimension($col)->setWidth($style['width']);
    }
}

function addDetailTableSheetConditional($worksheet, $data, $row, $col, $border = 1)
{
    $row += 1;
    if (empty($data)) {
        return $row;
    }

    foreach ($data as $rowData) {
        //var_dump ($rowData);
        for ($i = 0; $i < count($col) - 1; $i++) {
            //$horizontalAlignment = 'center';
            if (in_array($col[$i], ['Q'])) {
                //$horizontalAlignment = 'right';
                $worksheet->setCellValue('Q' . $row, '=O' . $row . '*P' . $row);
                //$cellStyle->getAlignment()->setHorizontal($horizontalAlignment);
            } else {
                $worksheet->setCellValue($col[$i] . $row, $rowData[$i]);
            }
        }
        //applyBorders($worksheet, 'S' . $row, $borderCode);
        $row++;
    }
    return $row;
}




function addHeaderSheetConditional($worksheet)
{
    $cellData = [
        'D2' => ['value' => 'TTVT Trip Summary Report', 'alignment' => 'left'],
        'O2' => ['value' => 'Total', 'alignment' => 'center',],
        'R2' => ['value' => 'Bath', 'alignment' => 'left',],

        //header table
        'D4' => ['value' => 'No', 'alignment' => 'center',],
        'E4' => ['value' => 'Operation Date', 'alignment' => 'center',],
        'F4' => ['value' => 'Pick Up Date', 'alignment' => 'center',],
        'G4' => ['value' => 'Delivery Date', 'alignment' => 'center',],
        'H4' => ['value' => 'JDA Tracking no.', 'alignment' => 'center',],
        'I4' => ['value' => 'JDA Route no.', 'alignment' => 'center',],
        'J4' => ['value' => 'Project', 'alignment' => 'center',],
        'K4' => ['value' => 'Status', 'alignment' => 'center',],
        'L4' => ['value' => 'Equipment Type TTV Use', 'alignment' => 'center',],
        'M4' => ['value' => 'One way / Round trip', 'alignment' => 'center',],
        'N4' => ['value' => 'Distance Band', 'alignment' => 'center',],
        'O4' => ['value' => 'Unit Rate', 'alignment' => 'center',],
        'P4' => ['value' => 'Qty of Trip', 'alignment' => 'center',],
        'Q4' => ['value' => 'Total Billed Amount', 'alignment' => 'center',],
        'R4' => ['value' => 'Remark', 'alignment' => 'center',],
    ];


    $worksheet->mergeCells('D2:J2');
    $worksheet->getStyle('D2')->getFont()->setSize(20);
    $worksheet->getStyle('O2:R2')->getFont()->setSize(11);

    $worksheet->getStyle('D2:R2')->getFont()->setBold(true);
    $worksheet->getStyle('D4:R4')->getFont()->setBold(true);

    $worksheet->getStyle('O2:R2')->getFont()->setUnderline(2);

    $worksheet->getStyle('D4:R4')
        ->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('0066FF');


    foreach ($cellData as $cell => $data) {
        $worksheet->setCellValue($cell, $data['value']);
        $cellStyle = $worksheet->getStyle($cell);
        $cellStyle->getAlignment()->setHorizontal($data['alignment']);
        $cellStyle->getAlignment()->setVertical('center');
    }

    applyBorders($worksheet, 'D4:R4', 'T1B1R1L1');
    //exit();
}


function trip_summary_report_data($worksheet, $row, $data)
{
    $col = ['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R'];
    $dataStartRow = $row + 1;
    $lastRow = addDetailTableSheet2($worksheet, $data, $row, $col);
    $row = $lastRow;
    $borderCode = 'L1R1T1B1';
    $row_border = $row - 1;
    applyBorders($worksheet, 'D6:R' . $row_border, $borderCode);
    $row = sumTableSheet2($worksheet, $row, $dataStartRow, $col);
    return $row;
}


$worksheet2 = new Worksheet($spreadsheet, 'Data Summary Report');
$spreadsheet->addSheet($worksheet2, 1);
$worksheet2->setShowGridlines(false);
addHeaderSheet2($worksheet2);
setDefaultStylesSheet2($worksheet2);
$row = 5;
trip_summary_report_data($worksheet2, $row, $tripSummaryArray);
$worksheet2->setAutoFilter('D5:R5');



function trip_summary_report_conditional($worksheet, $row, $data)
{
    $col = ['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R'];
    $dataStartRow = $row + 1;
    $lastRow = addDetailTableSheet2($worksheet, $data, $row, $col);
    $row = $lastRow;
    $borderCode = 'L1R1T1B1';
    $row_border = $row - 1;
    applyBorders($worksheet, 'D5:R' . $row_border, $borderCode);
    $row = sumTableSheet2($worksheet, $row, $dataStartRow, $col);
    return $row;
}

if (count($tripBailmentSummaryArray) > 0) {
    $worksheet_bailment = new Worksheet($spreadsheet, 'Bailment');
    $spreadsheet->addSheet($worksheet_bailment, 2);
    $worksheet_bailment->setShowGridlines(false);
    addHeaderSheetConditional($worksheet_bailment);
    setDefaultStylesSheetConditional($worksheet_bailment);
    $row = 4;
    trip_summary_report_conditional($worksheet_bailment, $row, $tripBailmentSummaryArray);
    $worksheet_bailment->setAutoFilter('D4:R4');
}

if (count($tripNonBailmentSummaryArray) > 0) {
    $worksheet_nonbailment = new Worksheet($spreadsheet, 'Non Bailment');
    $spreadsheet->addSheet($worksheet_nonbailment, 3);
    $worksheet_nonbailment->setShowGridlines(false);
    addHeaderSheetConditional($worksheet_nonbailment);
    setDefaultStylesSheetConditional($worksheet_nonbailment);
    $row = 4;
    trip_summary_report_conditional($worksheet_nonbailment, $row, $tripNonBailmentSummaryArray);
    $worksheet_nonbailment->setAutoFilter('D4:R4');
}

if (count($tripEmptySummaryArray) > 0) {
    $worksheet_bailment_empty = new Worksheet($spreadsheet, 'Empty Package');
    $spreadsheet->addSheet($worksheet_bailment_empty, 4);
    $worksheet_bailment_empty->setShowGridlines(false);
    addHeaderSheetConditional($worksheet_bailment_empty);
    setDefaultStylesSheetConditional($worksheet_bailment_empty);
    $row = 4;
    trip_summary_report_conditional($worksheet_bailment_empty, $row, $tripEmptySummaryArray);
    $worksheet_bailment_empty->setAutoFilter('D4:R4');
}

$spreadsheet->setActiveSheetIndex(0);
$writer = new Xlsx($spreadsheet);
$writer->save($filename);
