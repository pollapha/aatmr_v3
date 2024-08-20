<?php
require('code128.php');
$pdf=new PDF_Code128('L','mm',[101.6,38.1]);
$pdf->SetMargins(3,.5);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();
$text = 'PARTIAL';
$pdf->SetFont('Arial','',20);
$pdf->SetTextColor(100,100,100);
$pdf->RotatedText(68,30,$text,45); 
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','B',17);
// part number
$pdf->drawTextBox('2SV-352/72735-TEA-T011-C3',97.5, 8.5, 'C', 'B',1);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(97.5,5,'RUNCHANNEL R RR DOOR ','LRT',1,'C');
// put loc
$pdf->Cell(62/2,6.3,'','LRT',0,'R');
// pick loc
$pdf->Cell(62/2,6.3,'','LRT',1,'R');
//date
$pdf->Cell(62/2,6.3,'','LRT',0,'R');
//snp
$pdf->Cell(62/2,6.3,'','LRT',1,'R');
$pdf->SetFont('Arial','B',7);
//barcode txt
$pdf->drawTextBox(wordwrap('16062300001',1,'   ', true),62, 9, 'C', 'B',1);

$pdf->SetXY(65,14);
$pdf->drawTextBox('',35.5,12.5, 'C', 'B',1);
//barcode
$pdf->Code128(6.5,27.5,'16062300001',55,5);

$pdf->SetFont('Arial','B',6);
$pdf->Text(4,2.5,'Part Number.');
$pdf->Text(4,11,'Part Name.');
$pdf->Text(4,16.5,'Put Loc.');
$pdf->Text(34.5,16.5,'Pick Loc.');
$pdf->Text(4,22.5,'Date.');
$pdf->Text(34.5,22.5,'SNP.');
$pdf->Text(66,16,'Qty.');
$pdf->Text(66,29,'Supplier Name.');

$pdf->SetXY(65.5,13);
$pdf->SetFont('Arial','',40);
//qty
$pdf->drawTextBox('1234',34.5,15.5, 'C', 'M',0);

$pdf->SetXY(65,26.5);
$pdf->SetFont('Arial','',15);
$pdf->drawTextBox('TGRT',35.5,9, 'R', 'B',1);

$pdf->SetFont('Arial','B',13);
$pdf->SetXY(3,12.5);
// put loc
$pdf->drawTextBox('SP-10',31, 8, 'R', 'B',0);
$pdf->SetXY(34,12.5);
// pick loc
$pdf->drawTextBox('SP-10',31, 8, 'R', 'B',0);
$pdf->SetXY(3,18.5);
//date
$pdf->drawTextBox('23/06/2016',31, 8, 'R', 'B',0);
$pdf->SetXY(34,18.5);
//snp
$pdf->drawTextBox('10',31, 8, 'R', 'B',0);



$pdf->Output();

?>