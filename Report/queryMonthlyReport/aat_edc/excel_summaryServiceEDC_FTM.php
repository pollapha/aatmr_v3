<?php
include('../vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Workshhet\wo;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

$normalBailmentArray = array();
$normalNonBailmentArray = array();
$blowoutBailmentArray = array();
$blowoutNonBailmentArray = array();
$emptyPackageArray = array();

//$addNewArray = array();

$tripSummaryArray = array();

$sumNormalBailment = 0;
$sumNormalNonBailment = 0;
$sumBlowoutBailment = 0;
$sumBlowoutNonBailment = 0;
$sumEmpty = 0;

$no = 1;
$sh = 1;
foreach ($dataArray as $row) {
    //$row['description_show'],
    $formattedData = [
        $sh,
        $row['description_show'],
        $row['truckType'],
        $row['distanceBand'],
        $row['unit'],
        $row['unitRate'],
        $row['qty'],
    ];

    if ($row['bailment'] == 'Bailment' && strpos(strtolower($row['description_show']), 'normal') !== false) {
        $normalBailmentArray[] = $formattedData;
    } else if ($row['bailment'] == 'Non Bailment' && strpos(strtolower($row['description_show']), 'normal') !== false) {
        $normalNonBailmentArray[] = $formattedData;
    } else if (strpos(strtolower($row['description_show']), 'empty package') !== false) {
        $emptyPackageArray[] = $formattedData;
    } else if ($row['bailment'] == 'Bailment' && strpos(strtolower($row['description_show']), 'blowout') !== false) {
        $blowoutBailmentArray[] = $formattedData;
    } else if ($row['bailment'] == 'Non Bailment' && strpos(strtolower($row['description_show']), 'blowout') !== false) {
        $blowoutNonBailmentArray[] = $formattedData;
    }

    if ($row['bailment'] == 'Bailment' && strpos(strtolower($row['description_show']), 'normal') !== false && $row['qty']) {
        $sumNormalBailment += $row['qty'];
    } elseif ($row['bailment'] == 'Non Bailment' && strpos(strtolower($row['description_show']), 'normal') !== false && $row['qty']) {
        $sumNormalNonBailment += $row['qty'];
    } elseif ($row['bailment'] == 'Bailment' && strpos(strtolower($row['description_show']), 'blowout') !== false && $row['qty']) {
        $sumBlowoutBailment += $row['qty'];
    } elseif ($row['bailment'] == 'Non Bailment' && strpos(strtolower($row['description_show']), 'blowout') !== false && $row['qty']) {
        $sumBlowoutNonBailment += $row['qty'];
    } elseif (strpos(strtolower($row['description_show']), 'empty package') !== false && $row['qty']) {
        $sumEmpty += $row['qty'];
    }

    if ($no == 4) {
        $no = 0;
        $sh++;
    }
    if ($sh == 7) {
        $sh = 1;
    }
    $no++;
}

$no = 1;
$sh = 1;


function setDefaultStyles($worksheet)
{
    $styles = [
        'A' => ['width' => 0.00], 'B' => ['width' => 0.00],
        'D' => ['width' => 10.00], 'E' => ['width' => 15.00], 'F' => ['width' => 15.00], 'G' => ['width' => 15.00],
        'H' => ['width' => 15.00], 'I' => ['width' => 20.00], 'J' => ['width' => 15.00], 'K' => ['width' => 20.00],
        'L' => ['width' => 20.00], 'M' => ['width' => 20.00], 'N' => ['width' => 20.00],
    ];
    $worksheet->getParent()->getDefaultStyle()->getFont()->setName('Arial');
    $worksheet->getParent()->getDefaultStyle()->getFont()->setSize(8);
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setVertical('Center');
    $worksheet->getParent()->getDefaultStyle()->getAlignment()->setWrapText(true);
    foreach ($styles as $col => $style) {
        $worksheet->getColumnDimension($col)->setWidth($style['width']);
    }
}

function addHeaderData($worksheet)
{
    global $start_date;
    global $stop_date;
    global $project_name;
    global $project;
    global $approve;
    global $position;


    // $explode = explode(' ', $project_name);
    // $project = $explode[0];

    if ($project_name == 'EDC-FTM') {
        $project = 'Export Distribution Center (EDC) of FTM';
        $customer_name = 'Ford Motor (Thailand) co. ltd';
        $contact = 'K.Makawet  Chanyawongsawang';
        $email = 'mchanyaw@ford.com';
        $approve = 'K.Makawet Chanyawongsawang';
        $position = 'FTM : IMG - RTM Operation';
    } else {
        $project = 'AAT EDC';
        $customer_name = 'Auto Alliance (Thailand) Co.,Ltd.';
        $contact = 'K.Wiratch Jaroensuk';
        $email = 'wjaroen1@aat.ford.com';
        $approve = 'K.Peechchayapa Kalong';
        $position = 'AAT : Plant MFE / Plant RTM';
    }


    $date1 = date_create($start_date);
    $date2 = date_create($stop_date);
    $start_date = date_format($date1, "d-M-y");
    $stop_date = date_format($date2, "d-M-y");

    $cellData = [
        'D2' => ['value' => 'TTV SUPPLY CHAIN CO., LTD.', 'alignment' => 'center'],
        'D3' => ['value' => '336/11 Moo 7 Borwin, Sriracha, Chonburi 20230', 'alignment' => 'center'],
        'D4' => ['value' => 'TEL: 033-135020 TAX ID: 0205552019111 HEAD OFFICE', 'alignment' => 'center'],
        'D6' => ['value' => 'Customer :', 'alignment' => 'right'],
        'D7' => ['value' => 'Project :', 'alignment' => 'right'],
        'D8' => ['value' => 'Period :', 'alignment' => 'right'],
        'F6' => ['value' => $customer_name, 'alignment' => 'left'],
        'F7' => ['value' => $project, 'alignment' => 'left'],
        'F8' => ['value' => $start_date, 'alignment' => 'left'],
        'G8' => ['value' => $stop_date, 'alignment' => 'left'],
        'L6' => ['value' => 'Contact to :', 'alignment' => 'right'],
        'L7' => ['value' => 'E-mail :', 'alignment' => 'right'],
        'M6' => ['value' => $contact, 'alignment' => 'left'],
        'M7' => ['value' => $email, 'alignment' => 'left'],
    ];

    $worksheet->mergeCells('D2:N2');
    $worksheet->mergeCells('D3:N3');
    $worksheet->mergeCells('D4:N4');
    $worksheet->mergeCells('D6:E6');
    $worksheet->mergeCells('D7:E7');
    $worksheet->mergeCells('D8:E8');
    $worksheet->mergeCells('F6:I6');
    $worksheet->mergeCells('F7:I7');
    $worksheet->mergeCells('M6:O6');
    $worksheet->mergeCells('M7:N7');
    $worksheet->getStyle('D2:N2')->getFont()->setSize(16);
    $worksheet->getStyle('D6:N9')->getFont()->setSize(10);
    $worksheet->getStyle('D2:N9')->getFont()->setBold(true);

    foreach ($cellData as $cell => $data) {
        $worksheet->setCellValue($cell, $data['value']);
        $cellStyle = $worksheet->getStyle($cell);
        $cellStyle->getAlignment()->setHorizontal($data['alignment']);
        $cellStyle->getAlignment()->setVertical('center');
    }
}

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


function addHeaderTable($worksheet, $data, $row, $col)
{
    $index = count($col) - 1;
    $worksheet->setCellValue($col[0] . ($row), $data[0]);
    $worksheet->getStyle($col[0] . ($row))->getFont()->setBold(true);
    $worksheet->getStyle($col[0] . ($row))->getAlignment()->setWrapText(false);

    for ($i = 1; $i < count($data); $i++) {
        // echo ($col[$i-1] . ($row+1) . " ");
        // echo ($data[$i]);
        // echo ("<br>");
        $worksheet->setCellValue($col[$i - 1] . ($row + 1), $data[$i]);
    }
    $range = $col[0] . ($row + 1) . ':' . $col[$index] . ($row + 1);
    applyFont($worksheet, $range, 10, 1);
    applyFont($worksheet, $col[0] . ($row) . ':' . $col[$index] . ($row + 1), 10, 2);
    $worksheet->getStyle($range)->getAlignment()->setHorizontal('center');
    return $range;
}

function addDetailTable($worksheet, $data, $row, $col, $border = 1)
{
    $row += 2;
    if (empty($data)) {
        return $row;
    }
    $previousData1 = $data[0][1];
    $index = count($col) - 1;

    foreach ($data as $rowData) {
        for ($i = 0; $i < count($col); $i++) {

            $horizontalAlignment = 'center';
            $cellStyle = $worksheet->getStyle($col[$i] . $row);
            if ($col[$i] === 'E') {
                $horizontalAlignment = 'left';
            } else if (in_array($col[$i], ['K', 'L', 'M', 'N'])) {
                $cellStyle->getNumberFormat()->setFormatCode('_-* #,##0_-;-* #,##0_-;_-* "-"??_-;_-@_-');
                $horizontalAlignment = 'right';
            }
            $cellStyle->getAlignment()->setHorizontal($horizontalAlignment);

            if (in_array($col[$i], ['M', 'N'])) {
                $worksheet->setCellValue('M' . $row, '=L' . $row . '*K' . $row);
                $worksheet->setCellValue('N' . $row, '=M' . $row);
            } else {
                $worksheet->setCellValue($col[$i] . $row, $rowData[$i]);
            }
        }

        $borderCode = 'L0R0T1B1';

        $cellRange = $col[0] . $row . ':' . $col[$index] . $row;
        if ($border !== 0) {
            applyBorders($worksheet, $cellRange, $borderCode);
            $worksheet->getStyle('K' . $row . ':L' . $row)->getFont()->setBold(true);
        }

        if (($rowData[1] !== $previousData1) && $border !== 0) {
            $borderCode = 'L0R0T1B0';
            $cellRange = $col[0] . ($row - 1) . ':' . $col[$index] . ($row - 1);
            applyBorders($worksheet, $cellRange, $borderCode);
        }

        $previousData1 = $rowData[1];
        $row++;
    }

    return $row;
}

function sumTable($worksheet, $row, $dataStartRow, $col)
{
    $index = count($col) - 1;
    applyBorders($worksheet, $col[0] . ($row - 1) . ':' . $col[$index] . ($row - 1), 'B0');

    $lastDataRow = $row - 1;
    $worksheet->getRowDimension(($row))->setRowHeight(5);
    $row++;
    applyBorders($worksheet, $col[0] . $row . ':' . $col[$index] . $row, 'T1', 0);

    $worksheet->setCellValue('K' . ($row), "TOTAL");
    $worksheet->setCellValue('L' . ($row), "=SUM(L$dataStartRow:L$lastDataRow)");
    $worksheet->setCellValue('N' . ($row), "=SUM(N$dataStartRow:N$lastDataRow)");
    $worksheet->getStyle('K' . ($row))->getFont()->setBold(true);
    $worksheet->getStyle('K' . ($row))->getAlignment()->setHorizontal('right');
    $worksheet->getStyle('K' . ($row) . ':N' . ($row))->getFont()->setSize(10);
    applyFont($worksheet, 'L' . ($row) . ':N' . ($row));
    $worksheet->getStyle('L' . ($row) . ':N' . ($row))->getNumberFormat()->setFormatCode('#,##0');
    applyBorders($worksheet, 'L' . ($row) . ':N' . ($row), 'B2', 0);
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

$spreadsheet = new Spreadsheet();
$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setShowGridlines(false);
$worksheet->setTitle('Draft inv of FTM');
setDefaultStyles($worksheet);
addHeaderData($worksheet);
$row = 10;

function Normal_Trip_Delivery_Bailment($worksheet, $row, $Normal_Trip_Delivery_Bailment)
{
    $col = ['D', 'E', 'H', 'I', 'J', 'K', 'L', 'M', 'N'];
    $data = ['EDC Bailment Normal Trip Delivery (Normal Trip and Additional)', 'Item', 'Description', 'Truck Type', 'Distance Band', 'Unit', 'Service Rate', 'Qty', 'Amount', 'Total'];
    $range = addHeaderTable($worksheet, $data, $row, $col);
    applyBorders($worksheet, $range, 'T0B0R0L0');
    $worksheet->mergeCells('E' . ($row + 1) . ':' . 'G' . ($row + 1));

    $dataStartRow = $row + 2;
    $lastRow = addDetailTable($worksheet, $Normal_Trip_Delivery_Bailment, $row, $col);
    for ($i = $dataStartRow; $i < $lastRow; $i++) {
        $worksheet->mergeCells('E' . ($i) . ':G' . ($i));
    }
    $row = $lastRow;
    $row = sumTable($worksheet, $row, $dataStartRow, $col);
    return $row;
}

function Normal_Trip_Delivery_NonBailment($worksheet, $row, $Normal_Trip_Delivery_NonBailment)
{
    $row += 2;
    $col = ['D', 'E', 'H', 'I', 'J', 'K', 'L', 'M', 'N'];
    $data = ['EDC Non Bailment Normal Trip Delivery (Normal Trip and Additional)', 'Item', 'Description', 'Truck Type', 'Distance Band', 'Unit', 'Service Rate', 'Qty', 'Amount', 'Total'];
    $range = addHeaderTable($worksheet, $data, $row, $col);
    applyBorders($worksheet, $range, 'T0B0R0L0');
    $worksheet->mergeCells('E' . ($row + 1) . ':' . 'G' . ($row + 1));

    $dataStartRow = $row + 2;
    $lastRow = addDetailTable($worksheet, $Normal_Trip_Delivery_NonBailment, $row, $col);
    for ($i = $dataStartRow; $i < $lastRow; $i++) {
        $worksheet->mergeCells('E' . ($i) . ':G' . ($i));
    }
    $row = $lastRow;
    $row = sumTable($worksheet, $row, $dataStartRow, $col);
    return $row;
}

function Extra_Trip_Delivery_Blow_Outs_Bailment($worksheet, $row, $Extra_Trip_Delivery_Blow_Outs)
{
    $row += 2;
    $col = ['D', 'E', 'H', 'I', 'J', 'K', 'L', 'M', 'N'];
    $data = ['EDC Bailment Extra Trip Delivery (Blow Out)', 'Item', 'Description', 'Truck Type', 'Distance Band', 'Unit', 'Service Rate', 'Qty', 'Amount', 'Total'];
    $range = addHeaderTable($worksheet, $data, $row, $col);
    applyBorders($worksheet, $range, 'T0B0R0L0');
    $worksheet->mergeCells('E' . ($row + 1) . ':' . 'G' . ($row + 1));
    $dataStartRow = $row + 2;
    $lastRow = addDetailTable($worksheet, $Extra_Trip_Delivery_Blow_Outs, $row, $col);
    for ($i = $dataStartRow; $i < $lastRow; $i++) {
        $worksheet->mergeCells('E' . ($i) . ':G' . ($i));
    }
    $row = $lastRow;
    $row = sumTable($worksheet, $row, $dataStartRow, $col);
    return $row;
}

function Extra_Trip_Delivery_Blow_Outs_NonBailment($worksheet, $row, $Extra_Trip_Delivery_Blow_Outs)
{
    $row += 2;
    $col = ['D', 'E', 'H', 'I', 'J', 'K', 'L', 'M', 'N'];
    $data = ['EDC Non Bailment Extra Trip Delivery (Blow Out)', 'Item', 'Description', 'Truck Type', 'Distance Band', 'Unit', 'Service Rate', 'Qty', 'Amount', 'Total'];
    $range = addHeaderTable($worksheet, $data, $row, $col);
    applyBorders($worksheet, $range, 'T0B0R0L0');
    $worksheet->mergeCells('E' . ($row + 1) . ':' . 'G' . ($row + 1));
    $dataStartRow = $row + 2;
    $lastRow = addDetailTable($worksheet, $Extra_Trip_Delivery_Blow_Outs, $row, $col);
    for ($i = $dataStartRow; $i < $lastRow; $i++) {
        $worksheet->mergeCells('E' . ($i) . ':G' . ($i));
    }
    $row = $lastRow;
    $row = sumTable($worksheet, $row, $dataStartRow, $col);
    return $row;
}


function Empty_Package_Return($worksheet, $row, $Empty_Package_Return)
{
    $row += 2;
    $col = ['D', 'E', 'H', 'I', 'J', 'K', 'L', 'M', 'N'];
    $data = ['Empty Package Return', 'Item', 'Description', 'Truck Type', 'Distance Band', 'Unit', 'Service Rate', 'Qty', 'Amount', 'Total'];
    $range = addHeaderTable($worksheet, $data, $row, $col);
    applyBorders($worksheet, $range, 'T0B0R0L0');
    $worksheet->mergeCells('E' . ($row + 1) . ':' . 'G' . ($row + 1));
    $dataStartRow = $row + 2;
    $lastRow = addDetailTable($worksheet, $Empty_Package_Return, $row, $col);
    for ($i = $dataStartRow; $i < $lastRow; $i++) {
        $worksheet->mergeCells('E' . ($i) . ':G' . ($i));
    }
    $row = $lastRow;
    $row = sumTable($worksheet, $row, $dataStartRow, $col);
    return $row;
}

if (count($normalBailmentArray) > 0) {
    if ($sumNormalBailment > 0) {
        $row = Normal_Trip_Delivery_Bailment($worksheet, $row, $normalBailmentArray);
        $normal_bailment_trip_row = $row;
        $normal_bailment_trip_row = '=N' . $normal_bailment_trip_row;
    }
}

if (count($normalNonBailmentArray) > 0) {
    if ($sumNormalNonBailment > 0) {
        $row = Normal_Trip_Delivery_NonBailment($worksheet, $row, $normalNonBailmentArray);
        $normal_nonbailment_trip_row = $row;
        $normal_nonbailment_trip_row = '=N' . $normal_nonbailment_trip_row;
    }
}

if (count($blowoutBailmentArray) > 0) {
    if ($sumNormalNonBailment > 0) {
        $row = Extra_Trip_Delivery_Blow_Outs_Bailment($worksheet, $row, $blowoutBailmentArray);
        $extra_blow_outs_bailment_row = $row;
        $extra_blow_outs_bailment_row = '=N' . $extra_blow_outs_bailment_row;
    }
}

if (count($blowoutNonBailmentArray) > 0) {
    if ($sumBlowoutNonBailment > 0) {
        $row = Extra_Trip_Delivery_Blow_Outs_NonBailment($worksheet, $row, $blowoutNonBailmentArray);
        $extra_blow_outs_nonbailment_row = $row;
        $extra_blow_outs_nonbailment_row =  '=N' . $extra_blow_outs_nonbailment_row;
    }
}

// if (count($emptyPackageArray) > 0) {
//     $row = Empty_Package_Return($worksheet, $row, $emptyPackageArray);
//     $empty_package_row = $row;
//     $empty_package_row =  '=N' . $empty_package_row;
// }



$Management_Fees = [
    [1, 'Investment', 'THB/Month',  1216, 1, 1216, 1216],
    [2, 'Manpower Cost Summary', 'THB/Month', 268795, 1, 268795, 268795],
    [3, 'IT & Communication', 'THB/Month', 35024, 1, 35024, 35024],
    [4, 'Other Expenses', 'THB/Month', 43006, 1, 43006, 43006],
    [5, 'Adminstrative & OH', 'THB/Month', 19143, 1, 19143, 19143],
    [6, 'Margin', 'THB/Month', 27843, 1, 27843, 27843],
];


if ($sumNormalBailment == 0) {
    $normal_bailment_trip_row = 0;
}
if ($sumNormalNonBailment == 0) {
    $normal_nonbailment_trip_row = 0;
}
if ($sumBlowoutBailment == 0) {
    $extra_blow_outs_bailment_row = 0;
}
if ($sumBlowoutNonBailment == 0) {
    $extra_blow_outs_nonbailment_row = 0;
}

if ($sumEmpty > 0) {
    $Summary_Service_Charge = [
        [1, 'EDC Bailment Normal Trip Delivery (Normal and Additional)', $normal_bailment_trip_row],
        [2, 'EDC Non Bailment Normal Trip Delivery (Normal and Additional)', $normal_nonbailment_trip_row],
        [3, 'EDC Bailment Extra Trip Delivery (Blow Out)', $extra_blow_outs_bailment_row],
        [4, 'EDC Non Bailment Extra Trip Delivery (Blow Out)', $extra_blow_outs_nonbailment_row],
        [5, 'EDC Bailment Empty Package Return Service', '=N' . $empty_package_row],
    ];
} else {
    $Summary_Service_Charge = [
        [1, 'EDC Bailment Normal Trip Delivery (Normal and Additional)', $normal_bailment_trip_row],
        [2, 'EDC Non Bailment Normal Trip Delivery (Normal and Additional)', $normal_nonbailment_trip_row],
        [3, 'EDC Bailment Extra Trip Delivery (Blow Out)', $extra_blow_outs_bailment_row],
        [4, 'EDC Non Bailment Extra Trip Delivery (Blow Out)', $extra_blow_outs_nonbailment_row],
    ];
}







function Management_Fees($worksheet, $row, $Management_Fees)
{
    $row += 2;
    $col = ['D', 'E', 'J', 'K', 'L', 'M', 'N'];
    $data = ['Management Fees ( Year 3 )', 'Item', 'Description', 'Unit', 'Service Rate', 'Qty', 'Amount', 'Total'];
    $range = addHeaderTable($worksheet, $data, $row, $col);
    applyBorders($worksheet, $range, 'T0B0R0L0');
    $worksheet->mergeCells('E' . ($row + 1) . ':' . 'I' . ($row + 1));
    $dataStartRow = $row + 2;
    $lastRow = addDetailTable($worksheet, $Management_Fees, $row, $col, 0);
    applyBorders($worksheet, 'D' . ($row + 2) . ':N' . ($row + 7), 'T1B1R0L0');
    for ($i = $dataStartRow; $i < $lastRow; $i++) {
        $worksheet->mergeCells('E' . ($i) . ':I' . ($i));
    }
    $row = $lastRow;
    $row = sumTable($worksheet, $row, $dataStartRow, $col);
    return $row;
}



function Summary_Service_Charge($worksheet, $row, $Summary_Service_Charge)
{
    $row += 2;
    $col = ['D', 'E', 'J'];
    $data = ['', 'Item', 'Summary Service Charge', 'Cost'];
    $range = addHeaderTable($worksheet, $data, $row, $col);
    $worksheet->mergeCells('E' . ($row + 1) . ':' . 'I' . ($row + 1));
    $dataStartRow = $row + 2;
    $lastRow = addDetailTable($worksheet, $Summary_Service_Charge, $row, $col, 0);
    for ($i = $dataStartRow; $i < $lastRow; $i++) {
        $worksheet->mergeCells('E' . ($i) . ':I' . ($i));
    }

    $row = $lastRow;
    applyBorders($worksheet, 'D' . ($dataStartRow - 1) . ':' . 'J' . ($row - 1), 'T1B1R1L1');
    $worksheet->getStyle('D' . ($dataStartRow) . ':J' . ($row - 1))->getFont()->setBold(true);
    $worksheet->getRowDimension(($row))->setRowHeight(5);
    $worksheet->setCellValue('I' . ($row + 1), "TOTAL Cost");
    $worksheet->setCellValue('J' . ($row + 1), '=SUM(J' . $dataStartRow . ':J' . ($row - 1) . ')');
    $worksheet->getStyle('I' . ($row + 1) . ':J' . ($row + 1))->getFont()->setBold(true);
    $worksheet->getStyle('I' . ($row + 1) . ':J' . ($row + 1))->getAlignment()->setHorizontal('right');
    $worksheet->getStyle('I' . ($row + 1) . ':J' . ($row + 1))->getFont()->setSize(10);
    $worksheet->getStyle('J' . $dataStartRow . ':J' . ($row + 1))->getNumberFormat()->setFormatCode('#,##0');
    $worksheet->getStyle('J' . $dataStartRow . ':J' . ($row + 1))->getAlignment()->setHorizontal('right');
    applyBorders($worksheet, 'I' . ($row + 1) . ':' . 'J' . ($row + 1), 'T1B1R1L1');
    return $row;
}

$row = Summary_Service_Charge($worksheet, $row, $Summary_Service_Charge);

$Management_Fees = [
    ['Issued Draft Invoice', 'Approve Draft Invoice'], ['', ''],
    ['Sign__________________________', 'Sign__________________________'], ['', ''],
    ['Date__________________________', 'Date__________________________'], ['', ''],
    ['K.Worapong Chamnanpana', $approve],
    ['TTV : Senior Transport Manager', $position],
];


function lastfooter($worksheet, $row, $Management_Fees)
{
    $row += 2;
    $dataStartRow = $row;
    $col = ['E', 'K'];

    $lastRow = addDetailTable($worksheet, $Management_Fees, $row, $col, 0);
    for ($i = $dataStartRow; $i < $lastRow; $i++) {
        $worksheet->mergeCells('E' . ($i) . ':I' . ($i));
        $worksheet->mergeCells('K' . ($i) . ':M' . ($i));
    }

    $worksheet->getStyle('E' . ($row) . ':M' . ($lastRow))->getFont()->setSize(10);
    $worksheet->getStyle('E' . ($row + 2) . ':M' . ($row + 2))->getFont()->setBold(true);
    $worksheet->getStyle('E' . ($lastRow - 2) . ':M' . ($lastRow - 1))->getFont()->setBold(true);
    $worksheet->getStyle('E' . ($row) . ':M' . ($lastRow))->getAlignment()->setHorizontal('center');
    return $row;
}

$row = lastfooter($worksheet, $row, $Management_Fees);

function box($worksheet)
{
    $drawing = new Drawing();
    $drawing->setName('Logo');
    $drawing->setDescription('Logo');
    $drawing->setPath('../images/TTVNEW.jpg');
    $drawing->setCoordinates('M2');
    $drawing->setOffsetX(10);
    $drawing->setOffsetY(5);
    $drawing->setWidth(100.40);
    $drawing->setHeight(60.90);
    $drawing->setWorksheet($worksheet);
}

box($worksheet);

$date = date_create($start_date);
$start_date_filename = date_format($date, "M y");
if ($project_name == 'EDC-FTM') {
    $project = $project_name;
}
$filename = 'fileoutput/Summary Service Charge ' . $project . ' ' . $start_date_filename . '.xlsx';
// $writer = new Xlsx($spreadsheet);
// $writer->save($filename);
