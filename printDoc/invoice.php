<?php
include('../php/connection.php');
include 'fpdf.php';
include 'exfpdf.php';
include 'PDF_Code128.php';
include 'easyTable.php';
include '../Report/queryMonthlyReport/dataSummaryReport.php';

$doc = $mysqli->real_escape_string(trim(strtoupper($_REQUEST['data'])));
$dataset = array();
$q1  = "SELECT  
	invoice_no, date_format(invoice_date, '%d/%m/%y') as invoice_date, 
	remarks, start_date, stop_date, billing_for, project,
	updateBy
FROM
	tbl_invoice_header
WHERE
	invoice_no = '$doc';";
$re1 = sqlError($mysqli, __LINE__, $q1, 1);
while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
	$start_date = $row['start_date'];
	$stop_date = $row['stop_date'];
	$project = $row['project'];
	$billing_for = $row['billing_for'];
	$updateBy = $row['updateBy'];
}

$array = array(
	'start_date' => $start_date,
	'stop_date' => $stop_date,
	'project_name' => $project,
	'billing_for' => $billing_for,
	'creater' => $updateBy,
	'pr_no' => '',
	'invoice_no' => $doc,
	'type' => 12
);

$q1  .= sql_data_pr($mysqli, $array);
//exit($q1);
if (!$mysqli->multi_query($q1)) {
	echo "Multi query failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
do {
	if ($res = $mysqli->store_result()) {
		array_push($dataset, $res->fetch_all(MYSQLI_ASSOC));
		$res->free();
	}
} while ($mysqli->more_results() && $mysqli->next_result());
$headerData = $dataset[0];
$detailData = $dataset[1];


class PDF extends PDF_Code128
{
	function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
	{
		parent::__construct($orientation, $unit, $format);
		$this->AliasNbPages();
	}
	public function setHeaderData($v)
	{
		$this->headerData = $v;
	}
	public function setInstance($v)
	{
		$this->instance = $v;
	}
	function Header()
	{
		$v = $this->headerData;
		$header = new easyTable($this->instance, '%{80,20}', 'border:0;font-family:Arial;');
		$header->easyCell("<s font-size:16; font-style:B;>" . 'TTV SUPPLYCHAIN CO., LTD.' . "</s>" . '
		336/11 MOO 7  							
		BOWIN SRIRACHA							
		CHONBURI 20230							
		Tel: 033-401-505-6  Fax: 033-494-300							
		TAX ID. 0205552019111							
		HEAD OFFICE', 'valign:M;align:L;font-size:10; font-style:B;');
		$header->easyCell('', 'img:images/ttv.png, w30;valign:T;align:R;', '');
		$header->printRow();
		$header->easyCell('', 'valign:M;align:C;border:B;');
		$header->easyCell('Page ' . $this->PageNo() . ' of {nb}', 'valign:M;align:R;font-size:9;border:B;');
		$header->printRow();

		// $ip_server = $_SERVER['SERVER_NAME'];
		// $this->instance->Image('http://' . $ip_server . "/tspkb/print/qr_generator.php?code=" . $v[0]['invoice_no'], 150, 12, 20, 20, "png");

		$header->endTable(3);
		$header = new easyTable($this->instance, '%{100}', 'border:0;font-family:Arial;font-size:16; font-style:B;');
		$header->easyCell(utf8Th('INVOICE'), 'valign:M;align:C');
		$header->printRow();

		$header = new easyTable($this->instance, '%{15,35,25,25}', 'border:0;font-family:Arial;font-size:9;');
		$header->easyCell("Customer Code :", 'valign:T;align:L;font-style:B;');
		$header->easyCell(utf8Th($v[0]['project']), 'valign:T;align:L;font-style:B;');
		$header->easyCell("Invoice Number :", 'valign:T;align:R;');
		$header->easyCell(utf8Th($v[0]['invoice_no']), 'valign:T;align:L;font-style:B;');
		$header->printRow();
		$header->easyCell("Customer Name :", 'valign:T;align:L;font-style:B;');
		//$v[0]['customer_name']
		$customer_name = 'Auto Alliance (Thailand) Co.,Ltd.';
		$header->easyCell(utf8Th($customer_name), 'valign:T;align:L;');
		$header->easyCell("Invoice Date :", 'valign:T;align:R;');
		$header->easyCell(utf8Th($v[0]['invoice_date']), 'valign:T;align:L;font-style:B;');
		$header->printRow();

		$header = new easyTable($this->instance, '%{14,40,46}', 'border:0;font-family:Arial;font-size:9;');
		$header->easyCell("Address :", 'valign:T;align:L;font-style:B;');
		//$v[0]['address']
		$address = 'Eastern Seaboard Industrial Estate (Rayong)
		No. 49, Moo 4, Pluakdaeng, 
		Rayong, Thailand 21140
		Tel.(66 38) 954-111, 954-222';
		$header->easyCell(utf8Th($address), 'valign:T;align:L;');
		$header->easyCell('', 'valign:T;align:R;');
		$header->printRow();
		$header = new easyTable($this->instance, '%{9,91}', 'border:0;font-family:Arial;font-size:9;');
		$header->easyCell("Remarks :", 'valign:T;align:L;');
		$header->easyCell(utf8Th($v[0]['remarks']), 'valign:T;align:L;');
		$header->printRow();

		$headdetail = new easyTable(
			$this->instance,
			'%{7,57,10,10,16}',
			'border:1;font-family:Arial;font-size:9; font-style:B; valign:M;'
		);
		$headdetail->easyCell(utf8Th('Item'), 'align:C');
		$headdetail->easyCell(utf8Th('Description'), 'align:C');
		$headdetail->easyCell(utf8Th('Quantity'), 'align:C');
		$headdetail->easyCell(utf8Th('Unit Price'), 'align:C');
		$headdetail->easyCell(utf8Th('Amount (THB)'), 'align:C');
		$headdetail->printRow();
		$headdetail->endTable(0);
	}
	function Footer()
	{
		//$this->SetXY(-30, 2);
		//$this->SetFont('Arial', 'I', 8);
		//$this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
	}
}

$pdf = new PDF('P');
$pdf->AddFont('Arial', '', 'ARIAL.php');
$pdf->AddFont('Arial', 'I', 'ARIALI.php');
$pdf->AddFont('Arial', 'B', 'ARIALBD.php');
$pdf->AddFont('Arial', 'BI', 'ARIALBI.php');
$pdf->setInstance($pdf);
$pdf->setHeaderData($headerData);
$pdf->AddPage();
$docno = $headerData[0]['invoice_no'];
$pdf->SetTitle($docno);
$detail = new easyTable($pdf, '%{7,57,10,10,16}', 'border:LR;font-family:Arial;font-size:9;valign:M;line-height:1.25;');
$data = sizeof($detailData);
// หน้าละ15row
$pagebreak = 20;
$i = 0;
$countrow = 1;
$nn = 1;
$sumqty = 0;
$sumamount = 0;


while ($i <  $data) {
	if ($countrow > $pagebreak) {
		$pdf->AddPage();
		$countrow = 1;
	}
	$countrow++;
	$x = $pdf->GetX();
	$y = $pdf->GetY();
	$detail->easyCell(utf8Th($nn), 'align:C');
	$detail->easyCell(utf8Th('Transportation ' . $detailData[$i]["trip_detail"]), 'align:L;');
	$detail->easyCell(utf8Th($detailData[$i]["quantity"]), 'align:C;');
	$detail->easyCell(utf8Th($detailData[$i]["unit_price"]), 'align:R;');
	$detail->easyCell(utf8Th($detailData[$i]["amount"]), 'align:R;');
	$detail->printRow();
	$sumqty += $detailData[$i]['quantity'];
	$sumamount += $detailData[$i]['amount'];
	$i++;
	$nn++;
}
$fixRow = 19;
if ($countrow < $fixRow) {
	for ($i = 0, $len = $fixRow - $countrow; $i < $len; $i++) {
		$detail->rowStyle('min-height:7');
		$detail->easyCell(utf8Th(''), 'align:C;border:LR');
		$detail->easyCell(utf8Th(''), 'align:C;border:LR');
		$detail->easyCell(utf8Th(''), 'align:C;border:LR');
		$detail->easyCell(utf8Th(''), 'align:C;border:LR');
		$detail->easyCell(utf8Th(''), 'align:C;border:LR');
		$detail->easyCell(utf8Th(''), 'align:C;border:LR');
		$detail->printRow();
	}
}

$vat = 7 / 100;
$number = round($sumamount + ($sumamount * $vat), 2);
$number_word = convertNumberToString($number);

$detail->easyCell(utf8Th(''), 'align:C;border:TLR;');
$detail->easyCell(utf8Th(''), 'align:C;border:T;');
$detail->easyCell(utf8Th($sumqty), 'align:C;font-style:B;font-size:9;border:TLR;');
$detail->easyCell(utf8Th('Total'), 'align:R;font-size:9;border:TLR;');
$detail->easyCell(utf8Th(number_format($sumamount, 2, '.', ',')), 'align:R;font-size:9;border:TLR;');
$detail->printRow();
$detail->easyCell(utf8Th(''), 'align:C;border:LR;');
$detail->easyCell(utf8Th($number_word), 'valign:B;align:L;border:LRB;rowspan:2;font-size:9;');
$detail->easyCell(utf8Th(''), 'align:C;border:LR;');
$detail->easyCell(utf8Th('VAT 7%'), 'align:R;font-size:9;');
$detail->easyCell(utf8Th(number_format($sumamount * $vat, 2, '.', ',')), 'align:R;font-size:9;');
$detail->printRow();
$detail->easyCell(utf8Th(''), 'align:C;border:LRB;');
$detail->easyCell(utf8Th(''), 'align:C;border:LRB;');
$detail->easyCell(utf8Th('Grand Total'), 'align:R;font-size:8;border:LRB;');
$detail->easyCell(utf8Th(number_format($sumamount + ($sumamount * $vat), 2, '.', ',')), 'align:R;font-size:8;border:LRB;');
$detail->printRow();


$lastfooter = new easyTable($pdf, '%{64,36}', 'border:1;font-family:Arial;font-size:8;');
$lastfooter->easyCell(utf8Th('RECEIVED THE ABOVE MENTIONED GOOD IN GOOD ORDER AND CONDITION'), 'align:C;line-height:2.5;border:TLR;');
$lastfooter->easyCell(utf8Th('FOR TTV SUPPLYCHAIN CO., LTD.'), 'align:C;line-height:2.5;border:TLR;');
$lastfooter->printRow();
$lastfooter->rowStyle('min-height:10');
$lastfooter->easyCell(utf8Th(''), 'align:C;border:LR;');
$lastfooter->easyCell(utf8Th(''), 'align:C;border:LR;');
$lastfooter->printRow();
$lastfooter->easyCell(utf8Th('_____________________________
CUSTOMER SIGNATURE'), 'align:C;border:BLR;');
$lastfooter->easyCell(utf8Th('_____________________________
AUTHORIZED SIGNATURE'), 'align:C;border:BLR;');
$lastfooter->printRow();

$pdf->Output();

function utf8Th($v)
{
	return iconv('UTF-8', 'TIS-620//TRANSLIT', $v);
}

function convertNumberToString($num)
{
	$decones = array(
		'01' => "One",
		'02' => "Two",
		'03' => "Three",
		'04' => "Four",
		'05' => "Five",
		'06' => "Six",
		'07' => "Seven",
		'08' => "Eight",
		'09' => "Nine",
		10 => "Ten",
		11 => "Eleven",
		12 => "Twelve",
		13 => "Thirteen",
		14 => "Fourteen",
		15 => "Fifteen",
		16 => "Sixteen",
		17 => "Seventeen",
		18 => "Eighteen",
		19 => "Nineteen"
	);

	$ones = array(
		0 => " ",
		1 => "One",
		2 => "Two",
		3 => "Three",
		4 => "Four",
		5 => "Five",
		6 => "Six",
		7 => "Seven",
		8 => "Eight",
		9 => "Nine",
		10 => "Ten",
		11 => "Eleven",
		12 => "Twelve",
		13 => "Thirteen",
		14 => "Fourteen",
		15 => "Fifteen",
		16 => "Sixteen",
		17 => "Seventeen",
		18 => "Eighteen",
		19 => "Nineteen"
	);

	$tens = array(
		0 => "",
		2 => "Twenty",
		3 => "Thirty",
		4 => "Forty",
		5 => "Fifty",
		6 => "Sixty",
		7 => "Seventy",
		8 => "Eighty",
		9 => "Ninety"
	);

	$hundreds = array(
		"Hundred",
		"Thousand",
		"Million",
		"Billion",
		"Trillion",
		"Quadrillion"
	); //limit till quadrillion 

	// format a number upto 2 decimals
	$num = number_format($num, 2, ".", ",");
	$num_arr = explode(".", $num);
	$wholenum = $num_arr[0];
	$decnum = $num_arr[1];
	$whole_arr = array_reverse(explode(",", $wholenum));
	// sorts an associative array in descending order 
	krsort($whole_arr);
	$rettxt = "";

	// iterate through the array
	foreach ($whole_arr as $key => $i) {
		if ($i < 20) {
			$rettxt .= $ones[$i];
		} elseif ($i < 100) {
			// the substr function returns part of string
			$rettxt .= $tens[substr($i, 0, 1)];
			$rettxt .= " " . $ones[substr($i, 1, 1)];
		} else {
			$rettxt .= $ones[substr($i, 0, 1)] . " " . $hundreds[0];
			$rettxt .= " " . $tens[substr($i, 1, 1)];
			$rettxt .= " " . $ones[substr($i, 2, 1)];
		}
		if ($key > 0) {
			$rettxt .= " " . $hundreds[$key] . " ";
		}
	}

	// if float is found, show point
	if ($decnum > 0) {
		$rettxt .= " Bath and ";
		if ($decnum < 20) {
			$rettxt .= $decones[$decnum];
		} elseif ($decnum < 100) {
			$rettxt .= $tens[substr($decnum, 0, 1)];
			$rettxt .= " " . $ones[substr($decnum, 1, 1)];
		}
		$rettxt .= " Satang ";
	}
	return $rettxt;
}
