<?php

// define('CLASS_PATH','../../');

require_once('tcpdf/tcpdf.php');
require_once("label/class.label.php");
require_once("class.labelGen.php");
// require_once('../php/connection.php');
/*$param1  = $_POST['param1'];
$printerName = $param1['printerName'];
$copy = $param1['copy'];
$txt = $param1['txt'];
$num = $param1['num'];*/
$copy = 1;
$printType = 'I';
$printerName = '1401';
$txt = '11111111';
$num = 1;

$data = array();
for($i=0,$len=$num;$i<$len;$i++)
{
	$data[] = array($txt);
}


$mysqli->close();
$pdf = new labelGen("1",$data,'label/', '', false);

$pdf->border = false;
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('TTV');
$pdf->SetTitle("TTV");
$pdf->SetSubject("TTV");

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetHeaderMargin(0);
$pdf->SetFooterMargin(0);

$pdf->SetAutoPageBreak( true, 0);

$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);  


$pdf->Addlabel();

if(strlen($printerName) >0)
{
    $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,5);
	$fileName = $randomString.strtotime(date('Y-m-d H:i:s')).'-'.$printerName.'-'.$copy.'.pdf'; 
	$pdf->Output("C:\\report\\".$fileName, "I");
	echo '{"ch":1,"data":"เอกสารออกที่เครื่องปริ้นเตอร์ชื่อ '.$printerName.' จำนวน '.$copy.'ชุด"}';
}
else echo '{ch:2,data:"ไม่สามารถปริ้นได้เนื่องจากคุณไม่ได้เลือกปริ้นเตอร์"}';
//>>showpage, press <return> to continue<<
?>