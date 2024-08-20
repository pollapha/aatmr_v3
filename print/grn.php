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
$sql = <<<EEE

select h.pus,h.pusPlus,h.truckControlNo,h.truckLicense,h.truckType,h.driverName,h.phone,h.planTimeOut,h.planTimeIn,h.routeTrip,h.bol,
h.orderID,h.ld_supplierCode,h.ld_supplierName,h.ld_dueDate,h.ld_dueTime,h.ud_dueDate,h.ud_dueTime,o.TM_DELIVERY,
o.partNo,o.CD_PLANT_DOCK_LOC,o.dockGroup,QT_SHP_DEL qty,o.QT_PKG snp,round(o.QT_SHP_DEL/o.QT_PKG)box,'PTB' boxType
from tbl_pickupheader h left join tbl_ordertransaction o on h.refID = o.refID
left join tbl_partmaster p on o.partNo=p.partNo
where h.ID=$doctype and ediStatus='ACTIVE' order by o.DT_DELIVERY,o.TM_DELIVERY,o.CD_PLANT_DOCK_LOC;

EEE;
/*$result = $mysqli->query($sql);
$data_group = array();
$data = array();
while ($row = $result->fetch_array(MYSQLI_ASSOC)) 
{
    $data[] = $row;
}
foreach($data as $type){
        $data_group[$type['ld_supplierCode']][] = $type;
}
echo json_encode($data_group);
var_dump($data_group);
$mysqli->close();
exit();*/

$pdf=new PDF_Code128();
$pdf->SetAutoPageBreak(true,20);
$pdf->AliasNbPages();
$c = 0;
$sumBox = 0;
$sumQty = 0;
$cBy;
$pdf->AddFont('THSarabun','','THSarabun.php');
$calBox = array('PTB'=>0,'CPB'=>0,'STP'=>0,'PTP'=>0,'PPB'=>0);
if($result = $mysqli->query($sql)) 
{ 
    $len = $result->num_rows;
    if($len > 0)
    {  
        $totalPage = 0;
        $row = $result->fetch_object();
        $pusPlus = $row->pusPlus;
        $pdf->pusPlus = $pusPlus;
        $pdf->truckControlNo = $row->truckControlNo;
        $pdf->ld_dueDate = $row->ld_dueDate;
        $pdf->pus = $row->pus;
        $pdf->bol = $row->bol;
        $pdf->totalPage = $totalPage;
        $pdf->row = $row;
        $pdf->warter = $warter;
        $row->c = ++$c;
        $sumQty += $row->qty;
        $sumBox += $row->box;
        // $cBy = $row->cBy;

        $calBox[$row->boxType] += $row->box;
        createTableBody($pdf,$row);
        for($i=1;$i<$len;$i++)
        {
            $row = $result->fetch_object();
            $calBox[$row->boxType] += $row->box;
            $row->c = ++$c;
            $sumQty += $row->qty;
            $sumBox += $row->box;
            createTableBody($pdf,$row);
        }
        createTableFooter($pdf,$sumQty,$sumBox);
        createPageFooter($pdf,$calBox);

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
function createHeader($pdf,$barcode,$totalPage,$headerName,$pusPlus,$truckControlNo,$ld_dueDate)
{
    // $pdf->SetFont('THSarabun','',9);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(10,10);
    $pdf->drawTextBox('Page '.$pdf->PageNo().' / {nb}',190, 3, 'R', 'T',0);
    
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Image('images/ttv-logo.gif',175,14,25,5);
    $pdf->Image('images/aatlogopng.png',157,14,15,5);
    $pdf->SetXY(48,14);
    $pdf->SetX(10);
    $pdf->drawTextBox("TITAN-VNS AUTO LOGISTICS CO.,LTD.",90, 5,'L', 'T',0);
    $pdf->SetX(10);
    $pdf->SetFont('Arial', '', 6);
    $pdf->drawTextBox("49/66 MOO 5 TUNGSUNKLA SRIRACHA CHONBURI 20230",90, 5, 'L', 'T',0);

    $pdf->SetFont('Arial', '', 7);
    $pdf->SetXY(78,13);
    $pdf->drawTextBox(wordwrap($barcode,1,'   ', true),65, 10, 'C', 'B',0);
    $pdf->Code128(80,14,$barcode,60,6);
    $pdf->Ln(1);
    $pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());
    $pdf->SetFont('Arial', 'B', 15);
    // $pdf->drawTextBox($headerName,190, 7, 'C', 'M',0);
    
    // $pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());
    $pdf->SetFont('Arial', 'BU', 9);
    $pdf->Cell(40,5,$headerName,0,0,'L',false);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetX(115);
    $pdf->Cell(40,5,'Pus No.: '.$pusPlus,1,1,'R',false);
    $pdf->SetFont('Arial', 'BU', 8);
    $pdf->Cell(40,5,'Due Date : '.$ld_dueDate,0,0,'L',false);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Code128(157,25,$pusPlus,44,3);
    $pdf->Code128(157,30,$truckControlNo,44,3);
    $pdf->SetX(115);
    $pdf->Cell(40,5,'Truck Control: '.$truckControlNo,1,0,'R',false);
    $pdf->Ln(7);

// truckControlNo

}

function createHeaderData($pdf,$row)
{
    $col = 2;
    col2($pdf,$row,3);
}

function col2($pdf,$row,$col=2)
{
    $w = 210/$col;

    $pdf->SetFont('Arial', 'B', 8);
    $txt = 'Route Code : ';
    $pdf->Cell($w/2,5,$txt,0,0,'R',false);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($w/2,5,$row->routeTrip,0,0,'L',false);

    $pdf->SetFont('Arial', 'B', 8);
    $txt = 'Leave TTV : ';
    $pdf->Cell($w/3+10,5,$txt,0,0,'R',false);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($w/3+10,4,substr($row->planTimeOut,0,5),0,0,'L',false);

    $pdf->SetFont('Arial', 'B', 8);
    $txt = 'Truck License : ';
    $pdf->Cell($w/4+10,5,$txt,0,0,'R',false);
    $pdf->SetFont('THSarabun','',12);
    $pdf->Cell($w/4+10,4.2,iconv( 'UTF-8','TIS-620',$row->truckLicense),0,0,'L',false);

    $pdf->Ln(4);
    
    $pdf->SetFont('Arial', 'B', 8);
    $txt = 'Supplier Code : ';
    $pdf->Cell($w/2,5,$txt,0,0,'R',false);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($w/2,5,$row->ld_supplierCode,0,0,'L',false);

    $pdf->SetFont('Arial', 'B', 8);
    $txt = 'Arrive At Supplier : ';
    $pdf->Cell($w/3+10,5,$txt,0,0,'R',false);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($w/3+10,4,substr($row->ld_dueTime,0,5),0,0,'L',false);
    
    $pdf->SetFont('Arial', 'B', 8);
    $txt = 'Truck Type : ';
    $pdf->Cell($w/4+10,5,$txt,0,0,'R',false);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($w/4+10,5,$row->truckType,0,0,'L',false);

    $pdf->Ln(4);

    $pdf->SetFont('Arial', 'B', 8);
    $txt = 'Supplier Name : ';
    $pdf->Cell($w/2,5,$txt,0,0,'R',false);
     $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($w/2,5,substr($row->ld_supplierName,0,23),0,0,'L',false);
    // $pdf->SetFont('THSarabun', '', 14);
    // $pdf->Cell($w/2,3,iconv( 'UTF-8','TIS-620',$row->truckLicense),0,0,'L',false);

    $pdf->SetFont('Arial', 'B', 8);
    $txt = 'Leave Supplier : ';
    $pdf->Cell($w/3+10,5,$txt,0,0,'R',false);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($w/3+10,5,substr($row->ld_dueTime,0,5),0,0,'L',false);


    $pdf->SetFont('Arial', 'B', 8);
    $txt = 'Driver Name : ';
    $pdf->Cell($w/4+10,5,$txt,0,0,'R',false);
    $pdf->SetFont('THSarabun','',12);
    $pdf->Cell($w/4+10,5,iconv( 'UTF-8','TIS-620',$row->driverName),0,0,'L',false);

    $pdf->Ln(4);

    $pdf->SetFont('Arial', 'B', 8);
    $txt = 'BOL : ';
    $pdf->Cell($w/2,5,$txt,0,0,'R',false);
     $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($w/2,5,$row->bol,0,0,'L',false);
    // $pdf->SetFont('THSarabun', '', 14);
    // $pdf->Cell($w/2,3,iconv( 'UTF-8','TIS-620',$row->truckLicense),0,0,'L',false);

    $pdf->SetFont('Arial', 'B', 8);
    $txt = 'Arrive At AAT : ';
    $pdf->Cell($w/3+10,5,$txt,0,0,'R',false);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($w/3+10,5,substr($row->ud_dueTime,0,5),0,0,'L',false);


    $pdf->SetFont('Arial', 'B', 8);
    $txt = 'Phone : ';
    $pdf->Cell($w/4+10,5,$txt,0,0,'R',false);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($w/4+10,5,$row->phone,0,0,'L',false);
    $pdf->Ln(5);

}

function createTableBody($pdf,$row)
{
    $getX = $pdf->GetY();
    if($getX < 15 || $getX > 270)
    {
        $pdf->AddPage('L');
        if($pdf->warter == 'YES')
        {
            $mid_x = 140;
            $text = 'COPY';
            $pdf->SetFont('Arial','',150);
            $pdf->SetTextColor(220,220,220);
            $pdf->RotatedText($mid_x - ($pdf->GetStringWidth($text) / 2),200,$text,45); 
            $pdf->SetTextColor(0,0,0);
        }
        

        createHeader($pdf,$pdf->bol,$pdf->totalPage++,'PICKUP SHEET',$pdf->pusPlus,$pdf->truckControlNo,$pdf->ld_dueDate);
        createHeaderData($pdf,$pdf->row);
        createTableHeader($pdf);
    }
    
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(10,5,$row->c,1,0,'C',false);
    $pdf->Cell(15,5,date('H:i',strtotime($row->TM_DELIVERY)),1,0,'C',false);
    $pdf->Cell(36,5,$row->partNo,1,0,'C',false);
    $pdf->Cell(18,5,$row->dockGroup,1,0,'C',false);
    $pdf->Cell(13,5,$row->CD_PLANT_DOCK_LOC,1,0,'C',false);
    $pdf->Cell(15,5,$row->boxType,1,0,'C',false);
    $pdf->Cell(13,5,$row->snp,1,0,'R',false);
    $pdf->Cell(13,5,$row->qty,1,0,'R',false);
    $pdf->Cell(17,5,$row->box,1,0,'R',false);
    $pdf->Cell(20,5,'',1,0,'R',false);
    $pdf->Cell(20,5,'',1,0,'R',false);
    $pdf->Ln();
}

function createTableHeader($pdf)
{
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(10,5,'No',1,0,'C',false);
    $pdf->Cell(15,5,'Due Time',1,0,'C',false);
    $pdf->Cell(36,5,'Part No',1,0,'C',false);
    $pdf->Cell(18,5,'Dock Group',1,0,'C',false);
    $pdf->Cell(13,5,'Dock',1,0,'C',false);
    $pdf->Cell(15,5,'Box Type',1,0,'C',false);
    $pdf->Cell(13,5,'SNP',1,0,'C',false);
    $pdf->Cell(13,5,'Q\'ty',1,0,'C',false);
    $pdf->Cell(17,5,'Total Box',1,0,'C',false);
    $pdf->Cell(20,5,'Actual Box',1,0,'C',false);
    $pdf->Cell(20,5,'Remarks',1,0,'C',false);
    $pdf->Ln();
}

function createTableFooter($pdf,$sumQty,$sumBox)
{
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(15,5,'',0,0,'C',false);
    $pdf->Cell(40,5,'',0,0,'L',false);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(65,5,'Grand Totals : ',0,0,'R',false);
    $pdf->SetFont('','BU');
    $pdf->Cell(13,5,$sumQty,1,0,'R',false);
    $pdf->Cell(17,5,$sumBox,1,0,'R',false);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(50,5,'',0,0,'L',false);
    $pdf->Ln(5);
    $pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());
    $pdf->Ln(1);
}

function createPageFooter($pdf,$calBox)
{
    $pdf->Ln(1);
    $pdf->Cell(95,5,'Remark','RTL',0,'L',false);
    $pdf->Cell(95,5,'Packaging','RTL',1,'C',false);

    $pdf->Cell(95,5,'',1,0,'C',false);
    $pdf->SetFont('THSarabun','',12);
    $pdf->Cell(95/5,10,iconv( 'UTF-8','TIS-620','ยอดเช้ารับ'),'RTL',0,'C',false);

    $pdf->Cell(95/5,5,iconv( 'UTF-8','TIS-620','กล่องพลาสติก'),1,0,'C',false);
    $pdf->Cell(95/5,5,iconv( 'UTF-8','TIS-620','กล่องลูกฟูก'),1,0,'C',false);
    $pdf->Cell(95/5,5,iconv( 'UTF-8','TIS-620','พาเลทพลาสติก'),1,0,'C',false);
    $pdf->Cell(95/5,5,iconv( 'UTF-8','TIS-620','แร็ค'),1,1,'C',false);
    $pdf->SetFont('Arial', '', 9);

    $pdf->Cell(95,5,'',1,0,'C',false);
    $pdf->Cell(95/5,0,'','RL',0,'C',false);
    $pdf->Cell(95/5,5,'PTB',1,0,'C',false);
    $pdf->Cell(95/5,5,'CPB',1,0,'C',false);
    $pdf->Cell(95/5,5,'PTP',1,0,'C',false);
    $pdf->Cell(95/5,5,'STP',1,1,'C',false);

    $pdf->Cell(95,5,'',1,0,'C',false);
    $pdf->SetFont('THSarabun','',12);
    $pdf->Cell(95/5,5,iconv( 'UTF-8','TIS-620','แผนรับ'),1,0,'C',false);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(95/5,5,$calBox['PTB']==0? '-':$calBox['PTB'],1,0,'C',false);
    $pdf->Cell(95/5,5,$calBox['CPB']==0? '-':$calBox['CPB'],1,0,'C',false);
    $pdf->Cell(95/5,5,$calBox['PTP']==0? '-':$calBox['PTP'],1,0,'C',false);
    $pdf->Cell(95/5,5,$calBox['STP']==0? '-':$calBox['STP'],1,1,'C',false);

    $pdf->Cell(95,5,'',1,0,'C',false);
    $pdf->SetFont('THSarabun','',12);
    $pdf->Cell(95/5,5,iconv( 'UTF-8','TIS-620','รับจริง'),1,0,'C',false);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(95/5,5,'',1,0,'C',false);
    $pdf->Cell(95/5,5,'',1,0,'C',false);
    $pdf->Cell(95/5,5,'',1,0,'C',false);
    $pdf->Cell(95/5,5,'',1,1,'C',false);

    // $pdf->Ln(2);

    $w = 190/3;
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($w,5,'- Supplier -',0,0,'C',false);
    $pdf->Cell($w,5,'- Driver -',0,0,'C',false);
    $pdf->Cell($w,5,'- AAT -',0,0,'C',false);
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