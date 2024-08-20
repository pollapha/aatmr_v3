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
foreach ($dataArraySummaryTrip as $row) {
    $formattedData = [
        //$no,
        $row['Start_Datetime'],
        $row['End_Datetime'],
        $row['common'],
        $row['Load_ID'],
        $row['Route'],
        $row['Route'],
        $row['Internal_Tracking'],
        $row['Pick_GSDB_Code'],
        $row['Pick_GSDB_Name'],
        $row['Des_GSDB_Code'],
        $row['Des_GSDB_Name'],
        $row['bailment'],
        $row['Work_Type'],
        $row['truckType'],
        $row['tripType'],
        $row['distanceBand'],
        $row['unitRate'],
        $row['Qty_Trip'],
        $row['total'],
        $row['truckLicense'],
        $row['driverName'],
        $row['truck_carrier'],
        $row['phone'],
        '',
        '',
        'F'
    ];

    if ($row['bailment'] == 'Bailment' and $row['Work_Type'] != 'Empty Package') {
        $tripBailmentSummaryArray[] = $formattedData;
    } elseif ($row['bailment'] == 'Non Bailment' and $row['Work_Type'] != 'Empty Package') {
        $tripNonBailmentSummaryArray[] = $formattedData;
    } elseif ($row['Work_Type'] == 'Empty Package') {
        $tripEmptySummaryArray[] = $formattedData;
    }
    $tripSummaryArray[] = $formattedData;
    $no++;
}

function sumTableSheet2($worksheet, $row, $dataStartRow, $col)
{
    $index = count($col) - 1;

    $lastDataRow = $row;
    // echo ("=SUBTOTAL(9,S$dataStartRow:S$lastDataRow");
    // exit();

    $worksheet->getStyle('F3')->getNumberFormat()->setFormatCode('#,##0');
    $worksheet->getStyle('G3')->getNumberFormat()->setFormatCode('#,##0');
    $worksheet->getStyle('F4')->getNumberFormat()->setFormatCode('#,##0');
    $worksheet->getStyle('G4')->getNumberFormat()->setFormatCode('#,##0');

    $worksheet->getStyle('S7')->getNumberFormat()->setFormatCode('#,##0');
    $worksheet->setCellValue('S7', "=SUBTOTAL(9,S$dataStartRow:S$lastDataRow)");
    return $row;
}

function setDefaultStylesSheet2($worksheet)
{
    $styles = [
        'A' => ['width' => 15.00], 'B' => ['width' => 15.00], 'C' => ['width' => 15.00], 'D' => ['width' => 10.00], 'E' => ['width' => 15.00],
        'F' => ['width' => 15.00], 'G' => ['width' => 20.00], 'H' => ['width' => 15.00], 'I' => ['width' => 35.00], 'J' => ['width' => 15.00],
        'K' => ['width' => 35.00], 'L' => ['width' => 15.00], 'M' => ['width' => 15.00], 'N' => ['width' => 15.00], 'O' => ['width' => 15.00],
        'P' => ['width' => 15.00], 'Q' => ['width' => 15.00], 'R' => ['width' => 20.00], 'S' => ['width' => 15.00], 'T' => ['width' => 15.00],
        'U' => ['width' => 25.00], 'V' => ['width' => 15.00], 'W' => ['width' => 20.00], 'X' => ['width' => 15.00], 'Y' => ['width' => 15.00],
        'Z' => ['width' => 15.00]
    ];
    $worksheet->getParent()->getDefaultStyle()->getFont()->setName('Microsoft GothicNeo');
    $worksheet->getParent()->getDefaultStyle()->getFont()->setSize(8);
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setVertical('Center');
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setHorizontal('Center');
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setWrapText(true);

    $worksheet->getRowDimension('1')->setRowHeight(10, 'pt');
    $worksheet->getRowDimension('2')->setRowHeight(15, 'pt');
    $worksheet->getRowDimension('5')->setRowHeight(10, 'pt');
    $worksheet->getRowDimension('6')->setRowHeight(10, 'pt');
    $worksheet->getRowDimension('7')->setRowHeight(15, 'pt');
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
        //var_dump($rowData);
        for ($i = 0; $i < count($col) - 1; $i++) {
            if (in_array($col[$i], ['X'])) {
                $worksheet->setCellValue('X' . $row, '=D' . $row);
            } else if (in_array($col[$i], ['Y'])) {
                $worksheet->setCellValue('Y' . $row, '=IF(D' . $row . '="",' . '"",LEN(D' . $row . '))');
            } else {
                $worksheet->setCellValue($col[$i] . $row, $rowData[$i]);
            }
        }
        //applyBorders($worksheet, 'S' . $row, $borderCode);
        $row++;
    }
    //exit();
    return $row;
}


//$trip_summary_report_row = $row;

function addHeaderSheet2($worksheet)
{
    global $start_date;
    global $stop_date;

    //exit($start_date);
    //exit($stop_date);

    $cellData = [
        'A2' => ['value' => 'Trip Data Summary of EDC Project', 'alignment' => 'left', 'bg_color' => 'FFFFFF'],

        'A3' => ['value' => 'Business', 'alignment' => 'center', 'bg_color' => 'FFFFFF'],
        'B3' => ['value' => 'FTM', 'alignment' => 'center', 'bg_color' => 'FFFFFF'],
        'E3' => ['value' => 'Total', 'alignment' => 'center', 'bg_color' => 'FFFFFF'],
        'F3' => ['value' => '=SUM(F4:F4)', 'alignment' => 'right', 'bg_color' => 'FFFFFF'],
        'G3' => ['value' => '=SUM(G4:G4)', 'alignment' => 'right', 'bg_color' => 'FFFFFF'],

        'A4' => ['value' => 'Period', 'alignment' => 'center', 'bg_color' => 'FFFFFF'],
        'B4' => ['value' => $start_date, 'alignment' => 'center', 'bg_color' => 'FFFFFF'],
        'C4' => ['value' => $stop_date, 'alignment' => 'center', 'bg_color' => 'FFFFFF'],
        'E4' => ['value' => 'FTM', 'alignment' => 'center', 'bg_color' => 'FFFFFF'],
        'F4' => ['value' => '=COUNTIF(C:C,E4)', 'alignment' => 'right', 'bg_color' => 'FFFFFF'],
        'G4' => ['value' => '=SUMIFS($S:$S,$C:$C,E4)', 'alignment' => 'right', 'bg_color' => 'FFFFFF'],

        //header table
        'A8' => ['value' => 'Pick Up Date', 'alignment' => 'center', 'bg_color' => '00B0F0'],
        'B8' => ['value' => 'Delivery Date', 'alignment' => 'center', 'bg_color' => '00B0F0'],
        'C8' => ['value' => 'FTM/AAT/ Common', 'alignment' => 'center', 'bg_color' => '00B0F0'],
        'D8' => ['value' => 'Load ID Tracking no.', 'alignment' => 'center', 'bg_color' => '00B0F0'],
        'E8' => ['value' => 'Skeleton Route ID', 'alignment' => 'center', 'bg_color' => '00B0F0'],
        'F8' => ['value' => 'TTVT Route no.', 'alignment' => 'center', 'bg_color' => '00B0F0'],
        'G8' => ['value' => 'Internal Tracking No.', 'alignment' => 'center', 'bg_color' => '00B0F0'],
        'H8' => ['value' => 'Pick Up GSDB Code', 'alignment' => 'center', 'bg_color' => '00B0F0'],
        'I8' => ['value' => 'Pick Up Supplier', 'alignment' => 'center', 'bg_color' => 'FFFF00'],
        'J8' => ['value' => 'Destination GSDB', 'alignment' => 'center', 'bg_color' => 'FFE699'],
        'K8' => ['value' => 'Destination Name', 'alignment' => 'center', 'bg_color' => 'FFE699'],
        'L8' => ['value' => 'Project', 'alignment' => 'center', 'bg_color' => 'FFE699'],
        'M8' => ['value' => 'Deliver Type', 'alignment' => 'center', 'bg_color' => 'FFFF00'],
        'N8' => ['value' => 'Equipment Type', 'alignment' => 'center', 'bg_color' => '00B0F0'],
        'O8' => ['value' => 'One way / Round trip', 'alignment' => 'center', 'bg_color' => 'FFE699'],
        'P8' => ['value' => 'Unit Rate', 'alignment' => 'center', 'bg_color' => '00B0F0'],
        'Q8' => ['value' => 'Distance Band', 'alignment' => 'center', 'bg_color' => '00B0F0'],
        'R8' => ['value' => 'Qty of Trip', 'alignment' => 'center', 'bg_color' => '00B0F0'],
        'S8' => ['value' => 'Total Billed Amount', 'alignment' => 'center', 'bg_color' => 'FFE699'],
        'T8' => ['value' => 'Truck ID', 'alignment' => 'center', 'bg_color' => '00B0F0'],
        'U8' => ['value' => 'Driver Name', 'alignment' => 'center', 'bg_color' => '00B0F0'],
        'V8' => ['value' => 'Partner', 'alignment' => 'center', 'bg_color' => '00B0F0'],
        'W8' => ['value' => 'Telephone', 'alignment' => 'center', 'bg_color' => 'ACB9CA'],
        'X8' => ['value' => 'Check data Load ID', 'alignment' => 'center', 'bg_color' => 'ACB9CA'],
        'Y8' => ['value' => 'Check Digit Load ID', 'alignment' => 'center', 'bg_color' => 'ACB9CA'],
        'Z8' => ['value' => 'Customer', 'alignment' => 'center', 'bg_color' => 'ACB9CA'],
    ];

    // $worksheet->getStyle('D4:R4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('0066FF');

    foreach ($cellData as $cell => $data) {

        $worksheet->setCellValue($cell, $data['value']);
        $cellStyle = $worksheet->getStyle($cell);
        $cellStyle->getAlignment()->setHorizontal($data['alignment']);
        $cellStyle->getAlignment()->setVertical('center');
        $cellStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($data['bg_color']);
    }

    $worksheet->getStyle('A2')->getFont()->setSize(14);
    $worksheet->mergeCells('A2:G2');
    $worksheet->getStyle('A2:G2')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THICK);

    $worksheet->getStyle('A3:G4')->getFont()->setSize(11);
    $worksheet->getStyle('A2:Z8')->getFont()->setBold(true);


    applyBorders($worksheet, 'A8:Z8', 'T1B1R1L1');
    //exit();
}



// function sumTableSheetConditional($worksheet, $row, $dataStartRow, $col)
// {
//     $index = count($col) - 1;

//     $lastDataRow = $row;

//     $worksheet->getStyle('P2')->getNumberFormat()->setFormatCode('#,##0');
//     $worksheet->getStyle('Q2')->getNumberFormat()->setFormatCode('#,##0');
//     $worksheet->getStyle('R2')->getNumberFormat()->setFormatCode('#,##0');
//     $worksheet->setCellValue('P2', "=SUBTOTAL(9,P$dataStartRow:P$lastDataRow)");
//     $worksheet->setCellValue('Q2', "=SUBTOTAL(9,Q$dataStartRow:Q$lastDataRow)");
//     //$worksheet->setCellValue('R2', "=SUBTOTAL(9,R$dataStartRow:R$lastDataRow)");
//     return $row;
// }

// function setDefaultStylesSheetConditional($worksheet)
// {
//     $styles = [
//         'A' => ['width' => 0.00], 'B' => ['width' => 0.00], 'C' => ['width' => 0.00],
//         'D' => ['width' => 10.00], 'E' => ['width' => 15.00], 'F' => ['width' => 15.00], 'G' => ['width' => 15.00],
//         'H' => ['width' => 15.00], 'I' => ['width' => 15.00], 'J' => ['width' => 15.00], 'K' => ['width' => 15.00],
//         'L' => ['width' => 15.00], 'M' => ['width' => 15.00], 'N' => ['width' => 15.00], 'O' => ['width' => 15.00],
//         'P' => ['width' => 15.00], 'Q' => ['width' => 15.00], 'R' => ['width' => 20.00]
//     ];
//     $worksheet->getParent()->getDefaultStyle()->getFont()->setName('Arial');
//     $worksheet->getParent()->getDefaultStyle()->getFont()->setSize(10);
//     $worksheet->getParent()->getDefaultStyle()->getAlignment()->setVertical('Center');
//     $worksheet->getParent()->getDefaultStyle()->getAlignment()->setHorizontal('Center');
//     $worksheet->getParent()->getDefaultStyle()->getAlignment()->setWrapText(true);

//     $worksheet->getRowDimension('2')->setRowHeight(25, 'pt');
//     $worksheet->getRowDimension('3')->setRowHeight(5, 'pt');
//     $worksheet->getRowDimension('4')->setRowHeight(25, 'pt');
//     //$worksheet->getRowDimension('5')->setRowHeight(20, 'pt');
//     foreach ($styles as $col => $style) {
//         $worksheet->getColumnDimension($col)->setWidth($style['width']);
//     }
// }

// function addDetailTableSheetConditional($worksheet, $data, $row, $col, $border = 1)
// {
//     $row += 1;
//     if (empty($data)) {
//         return $row;
//     }

//     foreach ($data as $rowData) {
//         //var_dump ($rowData);
//         for ($i = 0; $i < count($col) - 1; $i++) {
//             //$horizontalAlignment = 'center';
//             if (in_array($col[$i], ['Q'])) {
//                 //$horizontalAlignment = 'right';
//                 $worksheet->setCellValue('Q' . $row, '=O' . $row . '*P' . $row);
//                 //$cellStyle->getAlignment()->setHorizontal($horizontalAlignment);
//             } else {
//                 $worksheet->setCellValue($col[$i] . $row, $rowData[$i]);
//             }
//         }
//         //applyBorders($worksheet, 'S' . $row, $borderCode);
//         $row++;
//     }
//     return $row;
// }




// function addHeaderSheetConditional($worksheet)
// {
//     $cellData = [
//         'A2' => ['value' => 'Trip Data Summary of EDC Project', 'alignment' => 'left'],
//         'O2' => ['value' => 'Total', 'alignment' => 'center',],
//         'R2' => ['value' => 'Bath', 'alignment' => 'left',],

//         //header table
//         'D4' => ['value' => 'No', 'alignment' => 'center',],
//         'E4' => ['value' => 'Operation Date', 'alignment' => 'center',],
//         'F4' => ['value' => 'Pick Up Date', 'alignment' => 'center',],
//         'G4' => ['value' => 'Delivery Date', 'alignment' => 'center',],
//         'H4' => ['value' => 'JDA Tracking no.', 'alignment' => 'center',],
//         'I4' => ['value' => 'JDA Route no.', 'alignment' => 'center',],
//         'J4' => ['value' => 'Project', 'alignment' => 'center',],
//         'K4' => ['value' => 'Status', 'alignment' => 'center',],
//         'L4' => ['value' => 'Equipment Type TTV Use', 'alignment' => 'center',],
//         'M4' => ['value' => 'One way / Round trip', 'alignment' => 'center',],
//         'N4' => ['value' => 'Distance Band', 'alignment' => 'center',],
//         'O4' => ['value' => 'Unit Rate', 'alignment' => 'center',],
//         'P4' => ['value' => 'Qty of Trip', 'alignment' => 'center',],
//         'Q4' => ['value' => 'Total Billed Amount', 'alignment' => 'center',],
//         'R4' => ['value' => 'Remark', 'alignment' => 'center',],
//     ];


// $worksheet->mergeCells('D2:J2');
// $worksheet->getStyle('D2')->getFont()->setSize(20);
// $worksheet->getStyle('O2:R2')->getFont()->setSize(11);

// $worksheet->getStyle('D2:R2')->getFont()->setBold(true);
// $worksheet->getStyle('D4:R4')->getFont()->setBold(true);

// $worksheet->getStyle('O2:R2')->getFont()->setUnderline(2);

// $worksheet->getStyle('D4:R4')
//     ->getFill()
//     ->setFillType(Fill::FILL_SOLID)
//     ->getStartColor()->setARGB('0066FF');


// foreach ($cellData as $cell => $data) {
//     $worksheet->setCellValue($cell, $data['value']);
//     $cellStyle = $worksheet->getStyle($cell);
//     $cellStyle->getAlignment()->setHorizontal($data['alignment']);
//     $cellStyle->getAlignment()->setVertical('center');
// }

//applyBorders($worksheet, 'D4:R4', 'T1B1R1L1');
//exit();
//}


function trip_summary_report_data($worksheet, $row, $data)
{
    $col = [
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
        'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
    ];
    $dataStartRow = $row + 1;
    $lastRow = addDetailTableSheet2($worksheet, $data, $row, $col);
    $row = $lastRow;
    $borderCode = 'L1R1T1B1';
    $row_border = $row - 1;
    applyBorders($worksheet, 'A9:Z' . $row_border, $borderCode);
    $row = sumTableSheet2($worksheet, $row, $dataStartRow, $col);
    return $row;
}


$worksheet2 = new Worksheet($spreadsheet, 'Data of FTM');
$spreadsheet->addSheet($worksheet2, 1);
$worksheet2->setShowGridlines(false);
setDefaultStylesSheet2($worksheet2);
addHeaderSheet2($worksheet2);
$row = 8;
trip_summary_report_data($worksheet2, $row, $tripSummaryArray);
$worksheet2->setAutoFilter('A8:Z8');

$spreadsheet->setActiveSheetIndex(0);
$writer = new Xlsx($spreadsheet);
$writer->save($filename);

// function trip_summary_report_conditional($worksheet, $row, $data)
// {
//     $col = ['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R'];
//     $dataStartRow = $row + 1;
//     $lastRow = addDetailTableSheet2($worksheet, $data, $row, $col);
//     $row = $lastRow;
//     $borderCode = 'L1R1T1B1';
//     $row_border = $row - 1;
//     applyBorders($worksheet, 'D5:R' . $row_border, $borderCode);
//     $row = sumTableSheet2($worksheet, $row, $dataStartRow, $col);
//     return $row;
// }

// if (count($tripBailmentSummaryArray) > 0) {
//     $worksheet_bailment = new Worksheet($spreadsheet, 'Bailment');
//     $spreadsheet->addSheet($worksheet_bailment, 2);
//     $worksheet_bailment->setShowGridlines(false);
//     addHeaderSheetConditional($worksheet_bailment);
//     setDefaultStylesSheetConditional($worksheet_bailment);
//     $row = 4;
//     trip_summary_report_data($worksheet_bailment, $row, $tripBailmentSummaryArray);
//     $worksheet_bailment->setAutoFilter('D4:R4');
// }

// if (count($tripNonBailmentSummaryArray) > 0) {
//     $worksheet_nonbailment = new Worksheet($spreadsheet, 'Non Bailment');
//     $spreadsheet->addSheet($worksheet_nonbailment, 3);
//     $worksheet_nonbailment->setShowGridlines(false);
//     addHeaderSheetConditional($worksheet_nonbailment);
//     setDefaultStylesSheetConditional($worksheet_nonbailment);
//     $row = 4;
//     trip_summary_report_conditional($worksheet_nonbailment, $row, $tripNonBailmentSummaryArray);
//     $worksheet_nonbailment->setAutoFilter('D4:R4');
// }

// if (count($tripEmptySummaryArray) > 0) {
//     $worksheet_bailment_empty = new Worksheet($spreadsheet, 'Empty Package');
//     $spreadsheet->addSheet($worksheet_bailment_empty, 4);
//     $worksheet_bailment_empty->setShowGridlines(false);
//     addHeaderSheetConditional($worksheet_bailment_empty);
//     setDefaultStylesSheetConditional($worksheet_bailment_empty);
//     $row = 4;
//     trip_summary_report_conditional($worksheet_bailment_empty, $row, $tripEmptySummaryArray);
//     $worksheet_bailment_empty->setAutoFilter('D4:R4');
// }
