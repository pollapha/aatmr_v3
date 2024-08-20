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

$no1 = 1;
$no2 = 1;
$no3 = 1;
$no4 = 1;

foreach ($dataArraySummaryTrip as $row) {

    if ($project_name == 'FTM MR' || $project_name == 'SKD-FTM') {
        $distanceBand = str_replace(' ', '', $row['distanceBand']);
    } else {
        $distanceBand = $row['distanceBand'];
    }

    if ($project_name == 'FTM MR' || $project_name == 'SKD-FTM') {
        if (strpos(strtolower($row['tripType']), 'way') !== false) {
            $tripType = '1-WAY';
        } elseif (strpos(strtolower($row['tripType']), 'round') !== false) {
            $tripType = 'Round';
        }
    } else {
        if (strpos(strtolower($row['tripType']), 'way') !== false) {
            $tripType = 'One-way';
        } elseif (strpos(strtolower($row['tripType']), 'round') !== false) {
            $tripType = 'Round Trip';
        }
    }

    $formattedData = [
        $no,
        $row['operation_date'],
        $row['Start_Datetime'],
        $row['End_Datetime'],
        $row['Load_ID'],
        $row['Route'],
        $row['Internal_Tracking'],
        $row['Work_Type'],
        $row['truckType'],
        $tripType,
        $distanceBand,
        $row['unitRate'],
        $row['Qty_Trip'],
        0,
        0
    ];

    $tripSummaryArray[] = $formattedData;
    $no++;

    if ($row['Work_Type'] == 'Normal') {
        $formattedData = [
            $no1,
            $row['operation_date'],
            $row['Start_Datetime'],
            $row['End_Datetime'],
            $row['Load_ID'],
            $row['Route'],
            $row['Internal_Tracking'],
            $row['Work_Type'],
            $row['truckType'],
            $tripType,
            $distanceBand,
            $row['unitRate'],
            $row['Qty_Trip'],
            0,
            0
        ];
        $no1++;
        $normalArray[] = $formattedData;
    } elseif ($row['Work_Type'] == 'Additional' || $row['Work_Type'] == 'Blowout') {
        $formattedData = [
            $no2,
            $row['operation_date'],
            $row['Start_Datetime'],
            $row['End_Datetime'],
            $row['Load_ID'],
            $row['Route'],
            $row['Internal_Tracking'],
            $row['Work_Type'],
            $row['truckType'],
            $tripType,
            $distanceBand,
            $row['unitRate'],
            $row['Qty_Trip'],
            0,
            0
        ];
        $no2++;
        $extraArray[] = $formattedData;
    } elseif ($row['Work_Type'] == 'Empty Package') {
        $formattedData = [
            $no3,
            $row['operation_date'],
            $row['Start_Datetime'],
            $row['End_Datetime'],
            $row['Load_ID'],
            $row['Route'],
            $row['Internal_Tracking'],
            $row['Work_Type'],
            $row['truckType'],
            $tripType,
            $distanceBand,
            $row['unitRate'],
            $row['Qty_Trip'],
            0,
            0
        ];
        $no3++;
        $emptyPackageArray[] = $formattedData;
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
    $worksheet->setCellValue('R2', "=SUBTOTAL(9,R$dataStartRow:R$lastDataRow)");
    return $row;
}

function setDefaultStylesSheet2($worksheet)
{
    $styles = [
        'A' => ['width' => 0.00], 'B' => ['width' => 0.00], 'C' => ['width' => 0.00],
        'D' => ['width' => 10.00], 'E' => ['width' => 15.00], 'F' => ['width' => 15.00], 'G' => ['width' => 15.00],
        'H' => ['width' => 15.00], 'I' => ['width' => 15.00], 'J' => ['width' => 20.00], 'K' => ['width' => 15.00],
        'L' => ['width' => 15.00], 'M' => ['width' => 15.00], 'N' => ['width' => 15.00], 'O' => ['width' => 15.00],
        'P' => ['width' => 15.00], 'Q' => ['width' => 15.00], 'R' => ['width' => 15.00], 'S' => ['width' => 20.00],
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
            $cellStyle = $worksheet->getStyle($col[$i] . $row);
            //$horizontalAlignment = 'center';
            if (in_array($col[$i], ['R'])) {
                //$horizontalAlignment = 'right';
                $worksheet->setCellValue('R' . $row, '=O' . $row . '*P' . $row);
                //$cellStyle->getAlignment()->setHorizontal($horizontalAlignment);
            } else {
                $worksheet->setCellValue($col[$i] . $row, $rowData[$i]);
            }
        }
        $row++;
    }
    return $row;
}

function trip_summary_report_data($worksheet, $row, $data)
{
    $col = ['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S'];
    $dataStartRow = $row + 1;
    $lastRow = addDetailTableSheet2($worksheet, $data, $row, $col);
    $row = $lastRow;
    $borderCode = 'L1R1T1B1';
    $row_border = $row - 1;
    applyBorders($worksheet, 'D5:S' . $row_border, $borderCode);
    $row = sumTableSheet2($worksheet, $row, $dataStartRow, $col);
    return $row;
}


//$trip_summary_report_row = $row;

function addHeaderSheet2($worksheet)
{
    $cellData = [
        'D2' => ['value' => 'TTVT Trip Summary Report', 'alignment' => 'left'],
        'O2' => ['value' => 'Total', 'alignment' => 'center',],
        'S2' => ['value' => 'Bath', 'alignment' => 'left',],
        'D3' => ['value' => 'Master Data Aligned with JDA', 'alignment' => 'left'],
        'K3' => ['value' => 'Carrier Data (shall be align with approval)', 'alignment' => 'center'],

        //header table
        'D5' => ['value' => 'No', 'alignment' => 'center',],
        'E5' => ['value' => 'Operation Date', 'alignment' => 'center',],
        'F5' => ['value' => 'Pick Up Date', 'alignment' => 'center',],
        'G5' => ['value' => 'Delivery Date', 'alignment' => 'center',],
        'H5' => ['value' => 'JDA Tracking no.', 'alignment' => 'center',],
        'I5' => ['value' => 'JDA Route no.', 'alignment' => 'center',],
        'J5' => ['value' => 'Internal Tracking No.', 'alignment' => 'center',],
        'K5' => ['value' => 'Status', 'alignment' => 'center',],
        'L5' => ['value' => 'Equipment Type TTV Use', 'alignment' => 'center',],
        'M5' => ['value' => 'One way / Round trip', 'alignment' => 'center',],
        'N5' => ['value' => 'Distance Band', 'alignment' => 'center',],
        'O5' => ['value' => 'Unit Rate', 'alignment' => 'center',],
        'P5' => ['value' => 'Qty of Trip', 'alignment' => 'center',],
        'Q5' => ['value' => 'Drop charge', 'alignment' => 'center',],
        'R5' => ['value' => 'Total Billed Amount', 'alignment' => 'center',],
        'S5' => ['value' => 'Remark', 'alignment' => 'center',],
    ];


    $worksheet->mergeCells('D2:J2');
    $worksheet->mergeCells('D3:J3');
    $worksheet->mergeCells('K3:S3');
    $worksheet->getStyle('D2')->getFont()->setSize(20);
    $worksheet->getStyle('O2:S2')->getFont()->setSize(11);
    $worksheet->getStyle('D3:S3')->getFont()->setSize(12);

    $worksheet->getStyle('D2:S2')->getFont()->setBold(true);
    $worksheet->getStyle('D3:S3')->getFont()->setBold(true);
    $worksheet->getStyle('D5:S5')->getFont()->setBold(true);

    $worksheet->getStyle('O2:R2')->getFont()->setUnderline(2);
    $worksheet->getStyle('D3')
        ->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FCD5B4');
    $worksheet->getStyle('K3')
        ->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('00B050');
    $worksheet->getStyle('D5:S5')
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
    applyBorders($worksheet, 'K3:S3', 'T0B0R0L0');
    applyBorders($worksheet, 'D5:S5', 'T1B1R1L1');
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
    $worksheet->setCellValue('R2', "=SUBTOTAL(9,R$dataStartRow:R$lastDataRow)");
    return $row;
}

function setDefaultStylesSheetConditional($worksheet)
{
    $styles = [
        'A' => ['width' => 0.00], 'B' => ['width' => 0.00], 'C' => ['width' => 0.00],
        'D' => ['width' => 10.00], 'E' => ['width' => 15.00], 'F' => ['width' => 15.00], 'G' => ['width' => 15.00],
        'H' => ['width' => 15.00], 'I' => ['width' => 15.00], 'J' => ['width' => 20.00], 'K' => ['width' => 15.00],
        'L' => ['width' => 15.00], 'M' => ['width' => 15.00], 'N' => ['width' => 15.00], 'O' => ['width' => 15.00],
        'P' => ['width' => 15.00], 'Q' => ['width' => 15.00], 'R' => ['width' => 15.00], 'S' => ['width' => 20.00],
    ];
    $worksheet->getParent()->getDefaultStyle()->getFont()->setName('Arial');
    $worksheet->getParent()->getDefaultStyle()->getFont()->setSize(10);
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setVertical('Center');
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setHorizontal('Center');
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setWrapText(true);

    $worksheet->getRowDimension('2')->setRowHeight(25, 'pt');
    $worksheet->getRowDimension('3')->setRowHeight(5, 'pt');
    $worksheet->getRowDimension('4')->setRowHeight(20, 'pt');
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
        //var_dump($rowData);
        for ($i = 0; $i < count($col) - 1; $i++) {

            if (in_array($col[$i], ['R'])) {
                $worksheet->setCellValue('R' . $row, '=O' . $row . '*P' . $row);
            } else {
                $worksheet->setCellValue($col[$i] . $row, $rowData[$i]);
            }


            // $horizontalAlignment = 'center';
            // $cellStyle = $worksheet->getStyle($col[$i] . $row);
            // if (in_array($col[$i], ['O', 'P', 'Q', 'R'])) {
            //     $cellStyle->getNumberFormat()->setFormatCode('_-* #,##0_-;-* #,##0_-;_-* "-"??_-;_-@_-');
            //     $horizontalAlignment = 'right';
            // }
            // $cellStyle->getAlignment()->setHorizontal($horizontalAlignment);
            // if (in_array($col[$i], ['R'])) {
            //     $worksheet->setCellValue('R' . $row, '=O' . $row . '*P' . $row);
            //     //$worksheet->setCellValue('N' . $row, '=M' . $row);
            // } else if (in_array($col[$i], ['Q'])) {
            //     $worksheet->setCellValue('Q' . $row, '=IF(O' . $row . '="","","0")');
            // } else {
            //     $worksheet->setCellValue($col[$i] . $row, $rowData[$i]);
            // }

            // $borderCode = 'L1R1T1B1';
            // applyBorders($worksheet, $col[$i] . $row, $borderCode);
        }
        //applyBorders($worksheet, 'S' . $row, $borderCode);
        $row++;
    }
    //exit();
    return $row;
}


function addHeaderSheetConditional($worksheet)
{
    $cellData = [
        'D2' => ['value' => 'TTVT Trip Summary Report', 'alignment' => 'left'],
        'O2' => ['value' => 'Total', 'alignment' => 'center',],
        'S2' => ['value' => 'Bath', 'alignment' => 'left',],

        //header table
        'D4' => ['value' => 'No', 'alignment' => 'center',],
        'E4' => ['value' => 'Operation Date', 'alignment' => 'center',],
        'F4' => ['value' => 'Pick Up Date', 'alignment' => 'center',],
        'G4' => ['value' => 'Delivery Date', 'alignment' => 'center',],
        'H4' => ['value' => 'JDA Tracking no.', 'alignment' => 'center',],
        'I4' => ['value' => 'JDA Route no.', 'alignment' => 'center',],
        'J4' => ['value' => 'Internal Tracking No.', 'alignment' => 'center',],
        'K4' => ['value' => 'Status', 'alignment' => 'center',],
        'L4' => ['value' => 'Equipment Type TTV Use', 'alignment' => 'center',],
        'M4' => ['value' => 'One way / Round trip', 'alignment' => 'center',],
        'N4' => ['value' => 'Distance Band', 'alignment' => 'center',],
        'O4' => ['value' => 'Unit Rate', 'alignment' => 'center',],
        'P4' => ['value' => 'Qty of Trip', 'alignment' => 'center',],
        'Q4' => ['value' => 'Drop charge', 'alignment' => 'center',],
        'R4' => ['value' => 'Total Billed Amount', 'alignment' => 'center',],
        'S4' => ['value' => 'Remark', 'alignment' => 'center',],
    ];


    $worksheet->mergeCells('D2:J2');
    $worksheet->getStyle('D2')->getFont()->setSize(20);
    $worksheet->getStyle('O2:S2')->getFont()->setSize(11);

    $worksheet->getStyle('D2:S2')->getFont()->setBold(true);
    $worksheet->getStyle('D4:S4')->getFont()->setBold(true);

    $worksheet->getStyle('O2:R2')->getFont()->setUnderline(2);

    $worksheet->getStyle('D4:S4')
        ->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('0066FF');


    foreach ($cellData as $cell => $data) {
        $worksheet->setCellValue($cell, $data['value']);
        $cellStyle = $worksheet->getStyle($cell);
        $cellStyle->getAlignment()->setHorizontal($data['alignment']);
        $cellStyle->getAlignment()->setVertical('center');
    }

    applyBorders($worksheet, 'D4:S4', 'T1B1R1L1');
    //exit();
}


$worksheet2 = new Worksheet($spreadsheet, 'Data Summary Report');
$spreadsheet->addSheet($worksheet2, 1);
$worksheet2->setShowGridlines(false);
addHeaderSheet2($worksheet2);
setDefaultStylesSheet2($worksheet2);
$row = 5;
trip_summary_report_data($worksheet2, $row, $tripSummaryArray);
$worksheet2->setAutoFilter('D5:S5');

function trip_summary_report_conditional($worksheet, $row, $data)
{
    $col = ['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S'];
    $dataStartRow = $row + 1;
    $lastRow = addDetailTableSheetConditional($worksheet, $data, $row, $col);
    $row = $lastRow;
    $borderCode = 'L1R1T1B1';
    $row_border = $row - 1;
    applyBorders($worksheet, 'D5:S' . $row_border, $borderCode);
    $row = sumTableSheetConditional($worksheet, $row, $dataStartRow, $col);
    return $row;
}

if (count($normalArray) > 0) {
    $worksheet_normal = new Worksheet($spreadsheet, 'Normal Trip');
    $spreadsheet->addSheet($worksheet_normal, 2);
    $worksheet_normal->setShowGridlines(false);
    addHeaderSheetConditional($worksheet_normal);
    setDefaultStylesSheetConditional($worksheet_normal);
    $row = 4;
    trip_summary_report_conditional($worksheet_normal, $row, $normalArray);
    $worksheet_normal->setAutoFilter('D4:S4');
}

if (count($extraArray) > 0) {
    $worksheet_extra = new Worksheet($spreadsheet, 'Extra Trip');
    $spreadsheet->addSheet($worksheet_extra, 3);
    $worksheet_extra->setShowGridlines(false);
    addHeaderSheetConditional($worksheet_extra);
    setDefaultStylesSheetConditional($worksheet_extra);
    $row = 4;
    trip_summary_report_conditional($worksheet_extra, $row, $extraArray);
    $worksheet_extra->setAutoFilter('D4:S4');
}

if (count($emptyPackageArray) > 0) {
    $worksheet_empty = new Worksheet($spreadsheet, 'Empty Package');
    $spreadsheet->addSheet($worksheet_empty, 4);
    $worksheet_empty->setShowGridlines(false);
    addHeaderSheetConditional($worksheet_empty);
    setDefaultStylesSheetConditional($worksheet_empty);
    $row = 4;
    trip_summary_report_conditional($worksheet_empty, $row, $emptyPackageArray);
    $worksheet_empty->setAutoFilter('D4:S4');
}

$spreadsheet->setActiveSheetIndex(0);
$writer = new Xlsx($spreadsheet);
$writer->save($filename);
