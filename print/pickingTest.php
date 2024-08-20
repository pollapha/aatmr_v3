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

/*$doctype = 'PIC1607180001';
$copy = 1;
$printType = 'I';
$printerName = '1401';*/
$warter = 'NO';
if($printerName == 'NO_PRINT' && $printType == 'F')
{
    echo '{ch:2,data:"ไม่สามารถปริ้นได้เนื่องจากคุณไม่ได้เลือกปริ้นเตอร์"}';
    exit();
}


include('../php/connection.php');
require('code128.php');
$pdf=new PDF_Code128();
$pdf->SetAutoPageBreak(true,20);
$pdf->AliasNbPages();
$c = 0;
$sum = 0;
$cBy;
$pdf->AddFont('THSarabun','','THSarabun.php');
$pdf->AddFont('THSarabun','B','THSarabun Bold.php');

if($result = $mysqli->query("SELECT `order`,cby_pick cBy,cdate_pick date,ddate,TIME_FORMAT(t1.dTime,'%H:%i') dtime,concat(t2.supplierPartNo,' | ',t2.partNo)partNo,t1.doc,t1.inv,t1.dock,sum(t1.qty)qty,floor(sum(t1.qty)/t2.snpIN)box,t2.snpIN,if(sum(t1.qty)<t2.snpIN,0,MOD(sum(t1.qty),t2.snpIN)) partial,t2.pick  from tbl_order t1 left join tbl_partmaster t2 on t1.partID=t2.id
where doc='$doctype' group by t1.inv,t1.partID order by t1.dDate,t1.dTime,t1.inv;")) 
{ 
    $len = $result->num_rows;
    if($len > 0)
    {  
        $pdf->sum = 0;
        $pdf->sumBox = 0;
        $totalPage = 0;
        $row = $result->fetch_object();
        $doc = $row->doc;
        $pdf->doc = $doc;
        $pdf->totalPage = $totalPage;
        $pdf->row = $row;
        $pdf->warter = $warter;
        $row->c = ++$c;
        $pdf->sum += $row->qty;
        $pdf->sumBox += $row->box;
        $cBy = $row->cBy;

        
        createTableBody($pdf,$row);
        for($i=1;$i<$len;$i++)
        {
            $row = $result->fetch_object();
            $row->c = ++$c;
            $pdf->sum += $row->qty;
            $pdf->sumBox += $row->box;
            createTableBody($pdf,$row);
        }
        createTableFooter($pdf,$sum);
        createPageFooter($pdf,$cBy);

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
    $pdf->Image('images/ttv-logo.gif',10,14,38,12);
    $pdf->SetXY(48,14);
    $pdf->drawTextBox("TITAN-VNS AUTO LOGISTICS CO.,LTD.",90, 5,'L', 'T',0);
    $pdf->SetX(48);
    $pdf->SetFont('Arial', '', 8);
    $pdf->drawTextBox("49/66 MOO 5 TUNGSUNKLA SRIRACHA CHONBURI 20230\nPhone +66(0) 3840 1505-6,3804 1787-8\nFax : +66(0) 3849 4300",90, 10, 'L', 'T',0);

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

    $pdf->Ln(2);

    $pdf->SetFont('Arial', 'B', 9);
    $txt = 'Document No : ';
    $pdf->Cell($w/2,5,$txt,0,0,'R',false);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($w/2,5,$row->doc,0,0,'L',false);

    $pdf->SetFont('Arial', 'B', 9);
    $txt = 'Create By : ';
    $pdf->Cell($w/3+10,5,$txt,0,0,'R',false);
    $pdf->SetFont('THSarabun', '', 14);
    $pdf->Cell($w/3+10,4,$row->cBy,0,0,'L',false);

    $pdf->SetFont('Arial', 'B', 9);
    $txt = 'Create Date : ';
    $pdf->Cell($w/4+10,5,$txt,0,0,'R',false);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($w/4+10,5,$row->date,0,0,'L',false);

    $pdf->Ln();
    $pdf->SetFont('Arial', 'B', 9);
    $txt = 'Order Date : ';
    $pdf->Cell($w/2,5,$txt,0,0,'R',false);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($w/2,5,$row->ddate.' '.$row->dtime,0,0,'L',false);


    $pdf->Ln(7);

}

function createTableHeader($pdf)
{
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(15,5,'No',1,0,'C',false);
    $pdf->Cell(55,5,'Part No',1,0,'C',false);
    $pdf->Cell(25,5,'Order No',1,0,'C',false);
    $pdf->Cell(40,5,'Dock',1,0,'C',false);
    $pdf->SetFont('THSarabun', 'B', 11);
    $pdf->Cell(15,5,iconv( 'UTF-8','TIS-620','จำนวนกล่อง'),1,0,'C',false);
    $pdf->Cell(15,5,iconv( 'UTF-8','TIS-620','จำนวนเศษ'),1,0,'C',false);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(15,5,'Pick Loc',1,0,'C',false);
    $pdf->Cell(10,5,'Qty',1,0,'C',false);
    $pdf->Ln();
    

}

function createTableBody($pdf,$row)
{
    $getX = $pdf->GetY();
    if($getX < 15 || $getX > 270)
    {
        $pdf->AddPage();
        if($pdf->warter == 'YES')
        {
            $mid_x = 140;
            $text = 'COPY';
            $pdf->SetFont('Arial','',150);
            $pdf->SetTextColor(220,220,220);
            $pdf->RotatedText($mid_x - ($pdf->GetStringWidth($text) / 2),200,$text,45); 
            $pdf->SetTextColor(0,0,0);
        }
        
        createHeader($pdf,$pdf->doc,$pdf->totalPage++,'PICKING LIST (TGRT)');
        createHeaderData($pdf,$pdf->row);
        createTableHeader($pdf);
    }


    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(15,5,$row->c,1,0,'C',false);
    $pdf->Cell(55,5,$row->partNo,1,0,'L',false);
    $pdf->Cell(25,5,$row->order,1,0,'L',false);
    $pdf->Cell(40,5,$row->dock,1,0,'L',false);
    $pdf->Cell(15,5,$row->box,1,0,'R',false);
    $pdf->Cell(15,5,$row->partial,1,0,'R',false);
    $pdf->Cell(15,5,$row->pick,1,0,'R',false);
    $pdf->Cell(10,5,$row->qty,1,0,'R',false);
    $pdf->Ln();

}

function createTableFooter($pdf,$sum)
{
    $pdf->SetFont('Arial', 'BU', 9);
    $pdf->Cell(15,5,'',0,0,0,false);
    $pdf->Cell(55,5,'',0,0,0,false);
    $pdf->Cell(35,5,'',0,0,0,false);
    $pdf->Cell(40,5,'',0,0,0,false);
    $pdf->Cell(15,5,'',0,0,'R',false);
    $pdf->Cell(15,5,'',0,0,'C',false);
    // $pdf->Cell(15,5,'',0,0,'C',false);
    $pdf->Cell(15,5,$pdf->sum,0,0,'R',false);
    $pdf->Ln(5);
    $pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());
    $pdf->Ln(1);
    

}

function createPageFooter($pdf,$cBy)
{
    $w = 190/3;
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($w,5,'- Picker -',0,0,'C',false);
    $pdf->Cell($w,5,'',0,0,'C',false);
    $pdf->Cell($w,5,'- QA -',0,0,'C',false);
    $pdf->Ln(7);
    $pdf->drawTextBox('',62, 10, 'C', 'T',0);
    $pdf->Line(15,$pdf->GetY(),$w+5,$pdf->GetY());
    // $pdf->Line($w+15,$pdf->GetY(),$w*2+5,$pdf->GetY());
    $pdf->Line($w*2+15,$pdf->GetY(),$w*3+5,$pdf->GetY());
    $pdf->Ln();
    $pdf->Cell($w,5,'(                 /                    /                  )',0,0,'C',false);
    // $pdf->Cell($w,5,'(                 /                    /                  )',0,0,'C',false);
    $pdf->Cell($w,5,'',0,0,'C',false);
    $pdf->Cell($w,5,'(                 /                    /                  )',0,0,'C',false);
    $pdf->Ln();
    $pdf->Line(15,$pdf->GetY(),$w+5,$pdf->GetY());
    // $pdf->Line($w+15,$pdf->GetY(),$w*2+5,$pdf->GetY());
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