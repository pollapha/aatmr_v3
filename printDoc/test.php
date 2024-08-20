<?php
echo getCount('A');
function getCount($teamNumber)
{
	$data = array('A'=>1,'B'=>2,'C'=>3,'D'=>4,'E'=>5,'F'=>6);
    if(!isset($data[$teamNumber])) return 0;
    else return $data[$teamNumber];
}
exit();
include('../php/connection.php');
include('../php/nm_common.php');
include('fastPDF.php');

$file = 'doc.pdf';
$pdf = new FPDF2File();
$pdf->Open($file);
$pdf->SetAutoPageBreak(false, 0);
$pdf->SetMargins(10,10,10);
$pdf->SetFont('Arial','',14);
$a4Width = 190;
for($i=0,$len=100;$i<$len;$i++)
{
    createHeader($pdf,[]);
    for($j=0,$len2=32;$j<$len2;$j++)
    {
        $pdf->Cell($a4Width*.05,7,$j+1,1,0,'C');
        $pdf->Cell($a4Width*.20,7,'',1,0,'C');
        $pdf->Cell($a4Width*.35,7,'',1,0,'C');
        $pdf->Cell($a4Width*.20,7,'',1,0,'C');
        $pdf->Cell($a4Width*.20,7,'',1,0,'C');
        $pdf->Ln();
    }
    // createBody($pdf,[]);
}

/* createHeader($pdf,[]);
createBody($pdf,[]); */
$pdf->Output();


function createHeader($pdf,$data)
{
    $pdf->SetFont('Arial','',7);
    $a4Width = $GLOBALS['a4Width'];
    $w = $a4Width/6;
    $pdf->AddPage();    
    $pdf->Image('images/ttv-logo.gif',10,10,30);
    $pdf->Text(41.5,12,'TITAN-VNS AUTOLOGISTICS CO., LTD.');
    $pdf->Text(41.5,12+2.5*1,'49/63 MOO 5 TUNGSUNKLA SRIRACHA CHONBURI 20230');
    $pdf->Text(41.5,12+2.5*2,'Phone +66(0) 3840 1505-6,3804 1787-8 Fax : +66(0) 3849 4300');
    $pdf->Code128($a4Width-45,10,'CYC19021900001',55,5);
    $pdf->Text($a4Width-35,18,wordwrap('CYC19021900001',1,' ', true));
    $pdf->SetXY(10,18.5);
    $pdf->SetFont('Arial','',14);
    $pdf->Cell($a4Width,7,'COUNT PART LIST','TB',2,'C');
    $pdf->SetFont('Arial','',7);
    $pdf->Cell($a4Width*.05,7,'NO',1,0,'C');
    $pdf->Cell($a4Width*.20,7,'Part No',1,0,'C');
    $pdf->Cell($a4Width*.35,7,'Part Name',1,0,'C');
    $pdf->Cell($a4Width*.20,7,'Actual Qty',1,0,'C');
    $pdf->Cell($a4Width*.20,7,'Remark',1,0,'C');
    $pdf->Ln();
}

function createBody($pdf,$data)
{
    $a4Width = $GLOBALS['a4Width'];
    $pdf->Cell($a4Width,7,'Check By',0,2,'C');
    $pdf->Cell($a4Width,7,'................................................................................. .',0,2,'C');
    // $pdf->Line($pdf->GetX(),$pdf->GetY(),$pdf->GetX()+30,$pdf->GetY()+30);
    
}

ignore_user_abort(true);
header('Content-type: application/pdf');
header('Content-Disposition: inline; filename="' . $file . '"');
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');
readfile($file);
unlink($file);
if (connection_aborted()) {
    unlink($f);
}
?>