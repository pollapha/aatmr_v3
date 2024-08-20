
<?php
require('concatpdf/fpdf_merge.php');
$merge = new FPDF_Merge();
$port = 80;
$fd = 'files/';

$data = $_REQUEST['doctype'];
$printType ='I';
$printerName='14';
$copy = 1;
$data = explode(',',$data);
for($i=0,$len=count($data);$i<$len;$i++)
{
    $doctype = $data[$i];
    $pdfBuff = file_get_contents('http://localhost:'.$port.'/aatmr_v3/print/ex.php?doctype='.$doctype.
    '&copy=1&printType=F&printerName=1401&warter=NO');

    $merge->add($fd.$pdfBuff);
    unlink($fd.$pdfBuff);
}

$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,5);
$fileName = $randomString.strtotime(date('Y-m-d H:i:s')).'-'.$printerName.'-'.$copy.'.pdf'; 
$merge->Output();

?>