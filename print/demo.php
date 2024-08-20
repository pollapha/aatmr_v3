<?php
require('code128.php');
$pdf=new PDF_Code128('L','mm',[101.6,38.1]);
$pdf->SetMargins(3,.5);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();
$pdf->SetFont('Arial','B',18);
// part number
$pdf->drawTextBox('TEAA-351/72735-TGG-A010-M1',97.5, 10, 'C', 'B',1);
$pdf->SetFont('Arial','B',15);
// put loc
$pdf->Cell(62/2,8,'','LRT',0,'R');
// pick loc
$pdf->Cell(62/2,8,'','LRT',1,'R');
//date
$pdf->Cell(62/2,8,'','LRT',0,'R');
//snp
$pdf->Cell(62/2,8,'','LRT',1,'R');
$pdf->SetFont('Arial','B',7);
//barcode txt
$pdf->drawTextBox(wordwrap('16062300001',1,'   ', true),62, 9, 'C', 'B',1);

$pdf->SetXY(65,10.5);
$pdf->drawTextBox('',35.5,9*3-2, 'C', 'B',1);
//barcode
$pdf->Code128(6.5,27.5,'16062300001',55,5);

$pdf->SetFont('Arial','B',8);
$pdf->Text(4,3,'Part Number.');
$pdf->Text(4,13,'Put Loc.');
$pdf->Text(34.5,13,'Pick Loc.');
$pdf->Text(4,21,'Date.');
$pdf->Text(34.5,21,'SNP.');
$pdf->Text(66,13,'Qty.');
$pdf->Text(66,29,'Supplier Name.');

$pdf->SetXY(65.5,12.5);
$pdf->SetFont('Arial','',40);
$pdf->drawTextBox('1000',34.5,15.5, 'C', 'M',0);

$pdf->SetXY(65,26.5);
$pdf->SetFont('Arial','',15);
$pdf->drawTextBox('TGRT',35.5,9, 'R', 'B',1);

$pdf->SetFont('Arial','B',15);
$pdf->SetXY(3,10.5);
// put loc
$pdf->drawTextBox('SP-10',31, 8, 'R', 'B',0);
$pdf->SetXY(34,10.5);
// pick loc
$pdf->drawTextBox('SP-10',31, 8, 'R', 'B',0);
$pdf->SetXY(3,18.5);
//date
$pdf->drawTextBox('23/06/2016',31, 8, 'R', 'B',0);
$pdf->SetXY(34,18.5);
//snp
$pdf->drawTextBox('1000',31, 8, 'R', 'B',0);

/*$text = 'COPY';
$pdf->SetFont('Arial','',150);
$pdf->SetTextColor(220,220,220);
$pdf->RotatedText(60,200,$60,45); 
$pdf->SetTextColor(0,0,0);*/

$pdf->Output();

?>