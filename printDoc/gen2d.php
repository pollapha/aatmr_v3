<?php

include 'fpdf.php';
require_once('qrcode.class.php');
$pdf=new FPDF('P');
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);

$txt = "http://122.154.123.149/subaruM/ins/v2.cab";
$pdf->Text(45,38,$txt);
$pdf->Text(72,48,'Install thai language');
$qrcode = new QRcode($txt, 'H');
$qrcode->displayFPDF($pdf, 85,55,30);

$txt = "https://subarutest.titan-vns.com/subaruM/";
$yPlus = 38+70;
$pdf->Text(45,$yPlus,$txt);
$pdf->Text(87,$yPlus+10,'Web test');
$qrcode = new QRcode($txt, 'H');
$qrcode->displayFPDF($pdf, 85,$yPlus+15,30);

$txt = "https://subaru.titan-vns.com/subaruM/";
$yPlus = 38+70*2;
$pdf->Text(50,$yPlus,$txt);
$pdf->Text(85,$yPlus+10,'Web Server');
$qrcode = new QRcode($txt, 'H');
$qrcode->displayFPDF($pdf, 85,$yPlus+15,30);

$pdf->Output();


?>