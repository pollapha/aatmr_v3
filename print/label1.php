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
// $warter = $_REQUEST['warter'];

/*$doctype = 'GRN1606230006';
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
require('code128.php');
$pdf=new PDF_Code128('L','mm',[101.6,38.1]);
$pdf->SetMargins(3,.5);
$pdf->SetAutoPageBreak(false);
$c = 0;
$sum = 0;
$cBy;

if($result = $mysqli->query("SELECT t1.qty,t2.put,t2.pick,t2.vendorName,t2.snpIn,t2.snpOut,concat(t2.supplierPartNo,'/',t2.partNo)partNo,t2.partName,t1.doc,t1.pus,t1.lot,DATE_FORMAT(t1.dDate,'%d/%m/%Y')date from tbl_inventory_transac t1 left join tbl_partmaster t2 on t1.partID=t2.id
where t1.doc='$doctype' and t1.trantype='IN' and t1.tstatus='YES';")) 
{ 
    $len = $result->num_rows;
    if($len > 0)
    {  
        $totalPage = 0;
        for($i=0;$i<$len;$i++)
        {
            $row = $result->fetch_object();
            $row->c = ++$c;
            $sum += $row->qty;
            createTableBody($pdf,$row);
        }

        if(strlen($printerName) >0)
        {
          $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,5);
          $fileName = $randomString.strtotime(date('Y-m-d H:i:s')).'-'.$printerName.'-'.$copy.'.pdf'; 
          $pdf->Output("C:\\report\\".$fileName,$printType);
          echo '{"ch":1,"data":"เอกสารออกที่เครื่องปริ้นเตอร์ชื่อ '.$printerName.' จำนวน '.$copy.'ชุด"}';
        }else echo '{ch:2,data:"ไม่สามารถปริ้นได้เนื่องจากคุณไม่ได้เลือกปริ้นเตอร์"}';
    }else echo '{"ch":0,"data":"ไม่พบ  <b>'.$doctype.'</b> ในระบบ"}';
}

$mysqli->close();
function createHeader($pdf,$barcode,$totalPage,$headerName)
{
    // $pdf->SetFont('THSarabun','',9);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(10,10);
    $pdf->drawTextBox('Page '.$pdf->PageNo().' / {nb}',190, 3, 'R', 'T',0);
    
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Image('images/abt-logo.gif',10,14,20,12);
    $pdf->SetXY(30,14);
    $pdf->drawTextBox("ALBATROSS LOGISTICS CO., LTD.",90, 5,'L', 'T',0);
    $pdf->SetX(30);
    $pdf->SetFont('Arial', '', 8);
    $pdf->drawTextBox("336/7 MOO 7 BOWIN, SRIRACHA CHONBURI 20230\nPhone +66 38 058 021, +66 38 058 081-2\nFax : +66 38 058 007",90, 10, 'L', 'T',0);

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(138,14);
    $pdf->drawTextBox(wordwrap($barcode,1,'   ', true),65, 11, 'C', 'B',0);
    $pdf->Code128(140,14,$barcode,60,7.5);
    $pdf->Ln(2);
    $pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());
    $pdf->SetFont('Arial', 'B', 15);
    $pdf->drawTextBox($headerName,190, 7, 'C', 'M',0);
    $pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());

}

function createHeaderData($pdf,$row)
{
    $col = 2;
    col2($pdf,$row,3);
}

function col2($pdf,$row,$col=2)
{
    $w = 190/$col;

    $pdf->SetFont('Arial', 'B', 9);
    $txt = 'Ship To : ';
    $pdf->Cell($w/2,5,$txt,0,0,'R',false);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($w/2,5,'ALBATROSS',0,0,'L',false);

    $pdf->SetFont('Arial', 'B', 9);
    $txt = 'Truck License : ';
    $pdf->Cell($w/3+10,5,$txt,0,0,'R',false);
    $pdf->SetFont('THSarabun', '', 14);
    $pdf->Cell($w/3+10,4,iconv( 'UTF-8','TIS-620',$row->tLicense),0,0,'L',false);

    $pdf->SetFont('Arial', 'B', 9);
    $txt = 'Date : ';
    $pdf->Cell($w/4+10,5,$txt,0,0,'R',false);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($w/4+10,5,$row->dDate,0,0,'L',false);

    $pdf->Ln(4);
    
    $pdf->SetFont('Arial', 'B', 9);
    $txt = 'Delivery : ';
    $pdf->Cell($w/2,5,$txt,0,0,'R',false);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($w/2,5,$row->del,0,0,'L',false);

    $pdf->SetFont('Arial', 'B', 9);
    $txt = 'Driver Name : ';
    $pdf->Cell($w/3+10,5,$txt,0,0,'R',false);
    $pdf->SetFont('THSarabun','',14);
    $pdf->Cell($w/3+10,4,iconv( 'UTF-8','TIS-620',$row->driverName),0,0,'L',false);
    
    $pdf->SetFont('Arial', 'B', 9);
    $txt = 'Document No : ';
    $pdf->Cell($w/4+10,5,$txt,0,0,'R',false);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($w/4+10,5,$row->doc,0,0,'L',false);

    $pdf->Ln(4);

    $pdf->SetFont('Arial', 'B', 9);
    $txt = 'Remark : ';
    $pdf->Cell($w/2,5,$txt,0,0,'R',false);
    $pdf->SetFont('THSarabun', '', 14);
    $pdf->Cell($w/2,3,iconv( 'UTF-8','TIS-620',$row->remark),0,0,'L',false);

    $pdf->SetFont('Arial', 'B', 9);
    $txt = '';
    $pdf->Cell($w/3+10,5,$txt,0,0,'R',false);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($w/3+10,5,'',0,0,'L',false);


    $pdf->SetFont('Arial', 'B', 9);
    $txt = 'Create By : ';
    $pdf->Cell($w/4+10,5,$txt,0,0,'R',false);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($w/4+10,5,$row->cBy,0,0,'L',false);
    $pdf->Ln(5);

}

function createTableHeader($pdf)
{
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(15,5,'No',1,0,'C',false);
    $pdf->Cell(40,5,'Part No',1,0,'C',false);
    $pdf->Cell(80,5,'Part Name',1,0,'C',false);
    $pdf->Cell(30,5,'',1,0,'C',false);
    $pdf->Cell(25,5,'Qty',1,0,'C',false);
    $pdf->Ln();
}

function createTableBody($pdf,$row)
{
    $pdf->AddPage();
    if($row->qty*1 != $row->snpIn*1)
    {
        $text = 'PARTIAL';
        $pdf->SetFont('Arial','',20);
        $pdf->SetTextColor(100,100,100);
        $pdf->RotatedText(68,30,$text,45); 
        $pdf->SetTextColor(0,0,0);
        
    }
    $pdf->SetFont('Arial','B',17);
    // part number
    $pdf->drawTextBox($row->partNo,97.5, 8.5, 'C', 'B',1);
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(97.5,5,$row->partName,'LRT',1,'C');
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
    $pdf->drawTextBox(wordwrap($row->lot,1,'   ', true),62, 9, 'C', 'B',1);
    
    $pdf->SetXY(65,14);
    $pdf->drawTextBox('',35.5,12.5, 'C', 'B',1);
    //barcode
    $pdf->Code128(6.5,27.5,$row->lot,55,5);

    
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
    $pdf->drawTextBox($row->qty,34.5,15.5, 'C', 'M',0);
    
    $pdf->SetXY(65,26.5);
    $pdf->SetFont('Arial','',15);
    $pdf->drawTextBox($row->vendorName,35.5,9, 'R', 'B',1);
    
    $pdf->SetFont('Arial','B',13);
    $pdf->SetXY(3,12.5);
    // put loc
    $pdf->drawTextBox($row->put,31, 8, 'R', 'B',0);
    $pdf->SetXY(34,12.5);
    // pick loc
    $pdf->drawTextBox($row->pick,31, 8, 'R', 'B',0);
    $pdf->SetXY(3,18.5);
    //date
    $pdf->drawTextBox($row->date,31, 8, 'R', 'B',0);
    $pdf->SetXY(34,18.5);
    //snp
    $pdf->drawTextBox($row->snpIn,31, 8, 'R', 'B',0);


}

function createTableFooter($pdf,$sum)
{
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(15,5,'',0,0,'C',false);
    $pdf->Cell(40,5,'',0,0,'L',false);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(110,5,'Total : ',0,0,'R',false);
    $pdf->SetFont('','BU');
    $pdf->Cell(25,5,$sum,1,0,'R',false);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(50,5,'',0,0,'L',false);
    $pdf->Ln(5);
    $pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());
    $pdf->Ln(1);
}

function createPageFooter($pdf,$cBy)
{
    $w = 190/3;
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($w,5,'- Data Entry -',0,0,'C',false);
    $pdf->Cell($w,5,'- Delivered By -',0,0,'C',false);
    $pdf->Cell($w,5,'- Received By -',0,0,'C',false);
    $pdf->Ln(7);
    $pdf->drawTextBox('',62, 10, 'C', 'T',0);
    $pdf->Line(15,$pdf->GetY(),$w+5,$pdf->GetY());
    $pdf->Line($w+15,$pdf->GetY(),$w*2+5,$pdf->GetY());
    $pdf->Line($w*2+15,$pdf->GetY(),$w*3+5,$pdf->GetY());
    // $pdf->Ln();
    $pdf->Cell($w,5,'(                 /                    /                  )',0,0,'C',false);
    $pdf->Cell($w,5,'(                 /                    /                  )',0,0,'C',false);
    $pdf->Cell($w,5,'(                 /                    /                  )',0,0,'C',false);
    $pdf->Ln();
    $pdf->Line(15,$pdf->GetY(),$w+5,$pdf->GetY());
    $pdf->Line($w+15,$pdf->GetY(),$w*2+5,$pdf->GetY());
    $pdf->Line($w*2+15,$pdf->GetY(),$w*3+5,$pdf->GetY());
    // $pdf->Line(15,$pdf->GetY(),$w+5,$pdf->GetY());
    // $pdf->Cell($w,5,'________________',0,0,'C',false);

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