<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');


$printerName = $_REQUEST['printerName'];
$copy = $_REQUEST['copy'];
$doctype = $_REQUEST['doctype'];
$printType = $_REQUEST['printType'];
$warter = $_REQUEST['warter'];

/*$doctype = '5705A576';
$copy = 1;
$printType = 'I';
$printerName = '1401';
$warter = 'NO';*/
if($printerName == 'NO_PRINT' && $printType == 'F')
{
    echo '{ch:2,data:"ไม่สามารถปริ้นได้เนื่องจากคุณไม่ได้เลือกปริ้นเตอร์"}';
    exit();
}

include('../php/connection.php');

if(!$re1 = $mysqli->query("SELECT concat(supplierPartNo,'/',partNo)partNo,kanbanID from tbl_partmaster where partNo='$doctype' limit 1;")) 
{echo '{ch:2,data:"Error Code 1"}';closeDB($mysqli);}
if($re1->num_rows == 0) echo '{"ch":0,"data":"ไม่พบ Part No '.$doctype.' ในระบบ"}';
$row1 = $re1->fetch_object();
$partNo = $row1->partNo;
$kanbanID = 'TV'.$row1->kanbanID;

$re2 = $mysqli->query("SELECT concat(
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0),',',
func_GenRuningNumber('serial',0)
)serial;");
$serialTxt = $re2->fetch_object()->serial;
$serialAr = explode(',', $serialTxt);

require('code128.php');
$pdf=new PDF_Code128();
$pdf->SetAutoPageBreak(true,20);
$pdf->SetMargins(5,10);
$pdf->AliasNbPages();
$c = 0;
$sum = 0;
$cBy;
$pdf->AddFont('THSarabun','','THSarabun.php');
$pdf->SetFont('Arial', 'B', 13);

$pageW = 190;
$w = 22;
$pdf->AddPage();
$pageStartX = $pdf->getX();
$pageStartY = $pdf->getY();
$pageX = $pdf->getX();
$pageY = $pdf->getY();
$len = 0;
for($i=0;$i<12;$i++)
{

    $serial = $kanbanID.$serialAr[$len++];
    //left
    $pdf->setXY($pageStartX,$pageY+$w*$i);
    $pdf->Cell(63,5,$partNo,1,0,'C',false);
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(17.5,18,substr($serial,2,4),'TLR',0,'C',false);
    $pdf->SetFont('Arial', 'B', 13);
    $pdf->Cell(17.5,5,'Qty',1,1,'C',false);
    $pdf->Cell(63,10,'','RLT',0,'C',false);
    $pdf->Cell(17.5,10,'','',0,'C',false);
    $pdf->Cell(17.5,10,'','RLT',1,'C',false);
    $pdf->Code128($pageStartX+4,$pageY+7+$w*$i,$serial,56,6);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(63,3,$serial,'RLB',0,'C',false);
    $pdf->Cell(17.5,3,'','RLB',0,'C',false);
    $pdf->Cell(17.5,3,'','RLB',1,'C',false);

    $serial = $kanbanID.$serialAr[$len++];
    //right
    $pdf->setXY($pageStartX+102,$pageY+$w*$i);
    $pdf->Cell(63,5,$partNo,1,0,'C',false);
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(17.5,18,substr($serial,2,4),'TLR',0,'C',false);
    $pdf->SetFont('Arial', 'B', 13);
    $pdf->Cell(17.5,5,'Qty',1,1,'C',false);
    $pdf->setX($pageX+102);
    $pdf->Cell(63,10,'','RLT',0,'C',false);
    $pdf->Cell(17.5,10,'','',0,'C',false);
    $pdf->Cell(17.5,10,'','RLT',1,'C',false);
    $pdf->Code128($pageStartX+4+102,$pageY+7+$w*$i,$serial,56,6);
    $pdf->setX($pageX+102); 
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(63,3,$serial,'RLB',0,'C',false);
    $pdf->Cell(17.5,3,'','RLB',0,'C',false);
    $pdf->Cell(17.5,3,'','RLB',1,'C',false);
}
$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,5);
          $fileName = $randomString.strtotime(date('Y-m-d H:i:s')).'-'.$printerName.'-'.$copy.'.pdf'; 
          $pdf->Output("C:\\report\\".$fileName,$printType);
          echo '{"ch":1,"data":"เอกสารออกที่เครื่องปริ้นเตอร์ชื่อ '.$printerName.' จำนวน '.$copy.'ชุด"}';

$mysqli->close();
function closeDB($mysqli)
{
    $mysqli->close();exit();
}
function Rotate($pdf,$angle,$x=-1,$y=-1) { 

    if($x==-1) 
        $x=$pdf->x; 
    if($y==-1) 
        $y=$pdf->y; 
    if($pdf->angle!=0) 
        $pdf->_out('Q'); 
    $pdf->angle=$angle; 
    if($angle!=0) 

    { 
        $angle*=M_PI/180; 
        $c=cos($angle); 
        $s=sin($angle); 
        $cx=$x*$pdf->k; 
        $cy=($pdf->h-$y)*$pdf->k; 

        $pdf->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
     } 
  } 


?>