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
$sql =
"SELECT t1.Load_ID pus,concat(t1.Load_ID,'-',t2.StopSequenceNumber) pusPlus,t1.Load_ID truckControlNo,t1.truckLicense,t1.truckType,t1.driverName,t1.phone,t1.planTimeOut_Origin planTimeOut,t1.planTimeIn_Origin planTimeIn,t1.Load_ID bol,
t2.Supplier_Code ld_supplierCode,substring_index(t2.Supplier_Name, ' ', 3)ld_supplierName,t2.PlanIN_Datetime ld_dueDate,date_format(t2.PlanOut_Datetime,'%H:%i') ld_dueTime,'' ud_dueDate,'' ud_dueTime,
t3.TM_DELIVERY,t3.DT_DELIVERY,t2.route routeTrip,
t3.Part_No partNo,t3.CD_PLANT_DOCK_LOC,'' dockGroup,t3.QT_SHP_DEL qty,t3.QT_PKG snp,round(t3.QT_SHP_DEL/t3.QT_PKG)box,t4.boxType,
round(t3.QT_SHP_DEL*t4.weight) wt,t3.NO_PGM,t3.CD_PLANT,round(t4.box_w*t4.box_l*t4.box_h/1000000,2)*round(t3.QT_SHP_DEL/t3.QT_PKG) cbm
from tbl_204header_api t1
left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID
left join tbl_862order t3 on t1.Load_ID=t3.LOAD_ID
and t2.Route=t3.CD_PICKUP_RTE_NEW and t2.Supplier_Code=t3.CD_SUPPLIER_SHP_FR
left join tbl_partmaster t4 on t3.Part_No=t4.partNo
where t2.ID=$doctype and t2.StopTypeEnumVal='ST_PICKONLY'
order by t3.DT_DELIVERY,t3.TM_DELIVERY,t3.CD_PLANT_DOCK_LOC,t4.partNo,t4.boxType;;
";

/*$result = $mysqli->query($sql);  and t1.ediStatus='ACTIVE'
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
$pdf->SetAutoPageBreak(true,10);
$pdf->AliasNbPages();
$pdf->setFillColor(230,230,230); 
$c = 0;
$sumBox = 0;
$sumQty = 0;
$sumWt = 0;
$sumCBM = 0;
$totalRack = 0;
$totalBox = 0;
$totalPallet = 0;
$cBy;
$pdf->AddFont('THSarabun','','THSarabun.php');
$pdf->AddFont('THSarabun','B','THSarabun Bold.php');
$calBox = array('CORRUGATE BOX'=>0,'CARDBOARD BOX'=>0,'PLASTIC BOX'=>0,'PLASTIC PALLET'=>0,'WOODEN PALLET'=>0,'PLASTIC BAG'=>0,'STEEL PALLET'=>0,'STEEL RACK'=>0
    ,'null'=>0,''=>0);



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
        $pdf->gateIn = date("Y-m-d H:i", strtotime('-15 minutes '.$row->DT_DELIVERY.' '.$row->TM_DELIVERY));

        $pdf->pus = $row->pus;
        $pdf->bol = $row->bol;
        $pdf->totalPage = $totalPage;
        $pdf->row = $row;
        $pdf->warter = $warter;
        $row->c = ++$c;
        $sumQty += $row->qty;
        $sumBox += $row->box;
        $sumWt += $row->wt;
        $sumCBM += $row->cbm;
        

        if($row->boxType == 'PLASTIC BOX' || $row->boxType == 'CARDBOARD BOX' || $row->boxType == 'CORRUGATE BOX')
            $totalBox += $row->box;
    
        if($row->boxType == 'PLASTIC PALLET' || $row->boxType == 'WOODEN PALLET' || $row->boxType == 'STEEL PALLET')
            $totalPallet += $row->box;
    
        if($row->boxType == 'STEEL RACK')
            $totalRack += $row->box;

        $calBox[$row->boxType] += $row->box;
        createTableBody($pdf,$row);
        for($i=1;$i<$len;$i++)
        {
            $row = $result->fetch_object();
            $calBox[$row->boxType] += $row->box;
            $row->c = ++$c;
            $sumQty += $row->qty;
            $sumBox += $row->box;
            $sumWt += $row->wt;
            $sumCBM += $row->cbm;

            if($row->boxType == 'PLASTIC BOX' || $row->boxType == 'CARDBOARD BOX' || $row->boxType == 'CORRUGATE BOX')
            $totalBox += $row->box;
    
            if($row->boxType == 'PLASTIC PALLET' || $row->boxType == 'WOODEN PALLET' || $row->boxType == 'STEEL PALLET')
                $totalPallet += $row->box;
        
            if($row->boxType == 'STEEL RACK')
                $totalRack += $row->box;

            createTableBody($pdf,$row);
        }
        createTableFooter($pdf,$sumQty,$sumBox,$sumWt,$totalBox,$totalPallet,$totalRack,$sumCBM);
        createPageFooter($pdf,$calBox);

        if(strlen($printerName) >0)
        {
          $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,5);
          $fileName = $randomString.strtotime(date('Y-m-d H:i:s')).'-'.$printerName.'-'.$copy.'.pdf'; 
          $pdf->Output("files/".$fileName,$printType);
          echo $fileName;
        }else echo '{ch:2,data:"ไม่สามารถปริ้นได้เนื่องจากคุณไม่ได้เลือกปริ้นเตอร์"}';
    }else echo '{"ch":0,"data":"ไม่พบ  <b>'.$doctype.'</b> ในระบบ"}';
}

$mysqli->close();
function createHeader($pdf,$barcode,$totalPage,$headerName,$pusPlus,$truckControlNo,$ld_dueDate)
{
    // $pdf->SetFont('THSarabun','',9);

    $pdf->setFillColor(0,0,0); 
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetXY(10,10);
    $pdf->drawTextBox('Page '.$pdf->PageNo().' / {nb}',280, 3, 'R', 'T',0);
    
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Image('images/ttv-logo.gif',260,14,25,5);
    $pdf->Image('images/aatlogopng.png',244,14,15,5);
    $pdf->SetXY(48,14);
    $pdf->SetX(10);
    $pdf->drawTextBox("TITAN-VNS AUTO LOGISTICS CO.,LTD.",90, 5,'L', 'T',0);
    $pdf->SetX(10);
    $pdf->SetFont('Arial', '', 6);
    $pdf->drawTextBox("49/66 MOO 5 TUNGSUNKLA SRIRACHA CHONBURI 20230",90, 5, 'L', 'T',0);

    $pdf->SetFont('Arial', '', 7);
    $pdf->SetXY(118,13);
    $pdf->drawTextBox(wordwrap($barcode,1,'   ', true),65, 10, 'C', 'B',0);
    $pdf->Code128(120,14,$barcode,60,6);
    $pdf->Ln(1);
    $pdf->Line(10,$pdf->GetY(),285,$pdf->GetY());
    $pdf->SetFont('Arial', 'B', 15);
    // $pdf->drawTextBox($headerName,190, 7, 'C', 'M',0);
    
    // $pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());
    $pdf->SetFont('Arial', 'BU', 9);
    $pdf->Cell(40,5,$headerName,0,0,'L',false);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetX(190);
    $pdf->Cell(48,5,'Pus No.: '.$pusPlus,1,1,'R',false);
    $pdf->SetFont('Arial', 'BU', 8);
    $pdf->Cell(40,5,'Due Date : '.$ld_dueDate,0,0,'L',false);
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Code128(241,25,$pusPlus,44,3);
    $pdf->Code128(241,30,$truckControlNo,44,3);
    $pdf->SetX(190);
    $pdf->Cell(48,5,'Truck Control: '.$truckControlNo,1,0,'R',false);
    $pdf->Ln(7);
    $pdf->setFillColor(230,230,230); 

// truckControlNo

}

function createHeaderData($pdf,$row)
{
    $col = 2;
    col2($pdf,$row,3);
}

function col2($pdf,$row,$col=2)
{
    $w = 300/$col;
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(40,5,'JDA TRACKING NO :',0,0,'R',false);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(40,5,$row->bol,'B',0,'L',false);

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(23,5,'SCAC CODE :',0,0,'R',false);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(23,5,'TTVT','B',0,'L',false);

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(23,5,'PLANT CODE :',0,0,'R',false);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(23,5,$row->CD_PLANT,'B',0,'L',false);

    $pdf->SetFont('THSarabun','B',12);
    $pdf->Cell(20,5,iconv('UTF-8','TIS-620','DRIVER รับ :'),0,0,'R',false);
    $pdf->Cell(80,5,iconv('UTF-8','TIS-620',$row->driverName.'              TEL : '.$row->phone),'B',1,'L',false);


    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(40,5,'BOL NO :',0,0,'R',false);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(40,5,'','B',0,'L',false);

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(23,5,'TRAILER NO :',0,0,'R',false);
    $pdf->SetFont('THSarabun','B',12);
    $pdf->Cell(23,5,iconv('UTF-8','TIS-620',substr($row->truckLicense,strlen($row->truckLicense)-4,4)),'B',0,'L',false);

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(23,5,'GATE IN TIME :',0,0,'R',false);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(23,5,$pdf->gateIn,'B',0,'L',false);

    $pdf->SetFont('THSarabun','B',12);
    $pdf->Cell(20,5,iconv('UTF-8','TIS-620','DRIVER ส่ง :'),0,0,'R',false);
    $pdf->Cell(80,5,iconv('UTF-8','TIS-620',$row->driverName.'              TEL : '.$row->phone),'B',1,'L',false);


    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(40,5,'ROUTE NO :',0,0,'R',false);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(40,5,$row->routeTrip,'B',0,'L',false);

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(23,5,'TRUCK NO :',0,0,'R',false);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(23,5,iconv('UTF-8','TIS-620',$row->truckLicense),'B',0,'L',false);

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(23,5,'TRUCK TYPE :',0,0,'R',false);
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(23,5,$row->truckType,'B',0,'L',false);
    $pdf->Ln(7);

}

function createTableBody($pdf,$row)
{
    $col = array
    (
        5,
        19.5,
        16,
        30,
        11,
        25,
        10,
        8,
        18,
        19.5,
        8,
        7,
        7,
        7,
        7,
        7,
        10,
        20,
        20,
        20,
    );
    $getX = $pdf->GetY();
    if($getX < 15 || $getX > 190)
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
        

        createHeader($pdf,$pdf->bol,$pdf->totalPage++,'PICKUP SHEET',$pdf->pusPlus,$pdf->bol,$pdf->ld_dueDate);
        createHeaderData($pdf,$pdf->row);
        createTableHeader($pdf,$col);
    }

    $pdf->SetFont('Arial', '', 6.5);
    $pdf->Cell($col[0],5,$row->c,1,0,'C',false);
    $pdf->Cell($col[1],5,$row->ld_dueTime,1,0,'C',false);
    $pdf->Cell($col[2],5,$row->ld_supplierCode,1,0,'C',false);
    $pdf->SetFont('Arial', '', 5);
    $pdf->Cell($col[3],5,$row->ld_supplierName,1,0,'C',false);
    $pdf->SetFont('Arial', '', 6.5);
    $pdf->Cell($col[4],5,$row->NO_PGM,1,0,'C',false);
    $pdf->Cell($col[5],5,$row->partNo,1,0,'C',false);
    $pdf->Cell($col[6],5,$row->dockGroup,1,0,'C',false);
    $pdf->Cell($col[7],5,$row->CD_PLANT_DOCK_LOC,1,0,'C',false);
    $pdf->SetFont('Arial', '', 5.5);
    $pdf->Cell($col[8],5,$row->boxType,1,0,'C',false);
    $pdf->SetFont('Arial', '', 6.5);
    $pdf->Cell($col[9],5,date('Y-m-d H:i',strtotime($row->DT_DELIVERY.' '.$row->TM_DELIVERY)),1,0,'C',false);
    $pdf->Cell($col[10],5,$row->qty,1,0,'R',false);
    $pdf->Cell($col[11],5,$row->snp,1,0,'R',false);

    if($row->boxType == 'PLASTIC BOX' || $row->boxType == 'CARDBOARD BOX' || $row->boxType == 'CORRUGATE BOX')
        $pdf->Cell($col[12],5,$row->box,1,0,'R',false);
    else 
        $pdf->Cell($col[12],5,'-',1,0,'R',false);

    if($row->boxType == 'STEEL RACK')
        $pdf->Cell($col[13],5,$row->box,1,0,'R',false);
    else 
        $pdf->Cell($col[13],5,'-',1,0,'R',false);

    if($row->boxType == 'PLASTIC PALLET' || $row->boxType == 'WOODEN PALLET' || $row->boxType == 'STEEL PALLET')
        $pdf->Cell($col[14],5,$row->box,1,0,'R',false);
    else 
        $pdf->Cell($col[14],5,'-',1,0,'R',false);

    $pdf->Cell($col[15],5,$row->cbm,1,0,'R',false);
    $pdf->Cell($col[16],5,$row->wt,1,0,'R',false);
    $pdf->Cell($col[17]/2,5,'',1,0,'R',false);
    $pdf->Cell($col[17]/2,5,'',1,0,'R',false);
    $pdf->Cell($col[18],5,'',1,0,'R',false);
    $pdf->Cell($col[19],5,'',1,0,'R',false);
    $pdf->Ln();

}


function createTableHeader($pdf,$col)
{
    $pdf->SetFont('Arial', 'B', 6.5);
    $pdf->Cell($col[0],10,'No','RLT',0,'C',true);
    $pdf->Cell($col[1],5,'Pickup Time','RLT',0,'C',true);
    $pdf->Cell($col[2],5,'GSDB_Code','RLT',0,'C',true);
    $pdf->Cell($col[3],5,'Supplier Name','RLT',0,'C',true);
    $pdf->Cell($col[4],5,'PGM No','RLT',0,'C',true);
    $pdf->Cell($col[5],5,'Part No','RLT',0,'C',true);
    $pdf->Cell($col[6],5,'Parking','RLT',0,'C',true);
    $pdf->Cell($col[7],5,'Dock','RLT',0,'C',true);
    $pdf->Cell($col[8],5,'Packaging','RLT',0,'C',true);
    $pdf->Cell($col[9],5,'Dock In Time','RLT',0,'C',true);
    $pdf->Cell($col[10],10,'Q\'ty','RLT',0,'C',true);
    $pdf->Cell($col[11],10,'SNP','RLT',0,'C',true);
    $pdf->Cell($col[12],10,'Box','RLT',0,'C',true);
    $pdf->Cell($col[13],10,'Rack','RLT',0,'C',true);
    $pdf->Cell($col[14],10,'Pallet','RLT',0,'C',true);
    $pdf->Cell($col[15],10,'CBM','RLT',0,'C',true);
    $pdf->Cell($col[16],10,'WT','RLT',0,'C',true);
    
    $pdf->SetFont('THSarabun','B',8.5);
    $pdf->Cell($col[17],5,iconv('UTF-8','TIS-620','สถานะการรับสินค้า'),1,0,'C',true);
    $pdf->Cell($col[18],5,iconv('UTF-8','TIS-620','ลายมือชื่อ Supplier'),'RLT',0,'C',true);
    $pdf->Cell($col[19],5,iconv('UTF-8','TIS-620','รายมือชื่อ AAT'),'RLT',0,'C',true);
    $pdf->Ln();
    $pdf->SetFont('Arial', 'B', 6.5);

    $pdf->SetFont('Arial', 'B', 6.5);
    $pdf->Cell($col[0] ,0,'','',0,'C',false);
    $pdf->SetFont('THSarabun','B',8.5);
    $pdf->Cell($col[1] ,5,iconv('UTF-8','TIS-620','เวลาที่รับงาน'),'RLB',0,'C',true);

     // $pdf->SetFont('Arial', 'B', 6.5);
    $pdf->Cell($col[2] ,5,iconv('UTF-8','TIS-620','รหัส ซัพพลายเออร์'),'RLB',0,'C',true);
    $pdf->Cell($col[3] ,5,iconv('UTF-8','TIS-620','ชื่อซัพพลายเออร์'),'RLB',0,'C',true);
    $pdf->Cell($col[4] ,5,iconv('UTF-8','TIS-620','เลขที่โปรแกรม'),'RLB',0,'C',true);
    $pdf->Cell($col[5] ,5,iconv('UTF-8','TIS-620','รหัสชิ้นงาน'),'RLB',0,'C',true);
    $pdf->Cell($col[6] ,5,iconv('UTF-8','TIS-620','จุดจอด'),'RLB',0,'C',true);
    $pdf->Cell($col[7] ,5,iconv('UTF-8','TIS-620','จุดส่ง'),'RLB',0,'C',true);
    $pdf->Cell($col[8] ,5,iconv('UTF-8','TIS-620','ประเภทบรรจุภัณฑ์'),'RLB',0,'C',true);
    $pdf->Cell($col[9] ,5,iconv('UTF-8','TIS-620','วันที่ส่งงาน'),'RLB',0,'C',true);
    $pdf->Cell($col[10],0,iconv('UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell($col[11],0,iconv('UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell($col[12],0,iconv('UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell($col[13],0,iconv('UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell($col[14],0,iconv('UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell($col[15],0,iconv('UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell($col[16],0,iconv('UTF-8','TIS-620',''),'',0,'C',false);
    
    $pdf->SetFont('THSarabun','B',8.5);
    $pdf->Cell($col[17]/2,5,iconv('UTF-8','TIS-620','ไม่ได้รับ'),1,0,'C',true);
    $pdf->Cell($col[17]/2,5,iconv('UTF-8','TIS-620','ได้รับจำนวน'),1,0,'C',true);
    $pdf->Cell($col[18],5,iconv('UTF-8','TIS-620','ตัวบรรจง'),'RLB',0,'C',true);
    $pdf->Cell($col[19],5,iconv('UTF-8','TIS-620','ตัวบรรจง'),'RLB',0,'C',true);
    $pdf->Ln();
    $pdf->SetFont('Arial', 'B', 6.5);
}

function createTableFooter($pdf,$sumQty,$sumBox,$sumWt,$totalBox,$totalPallet,$totalRack,$sumCBM)
{
    $pdf->SetFont('Arial', '', 6.5);
    $pdf->Cell(15,5,'',0,0,'C',false);
    $pdf->Cell(40,5,'',0,0,'L',false);
    $pdf->SetFont('Arial', 'B', 6.5);
    $pdf->Cell(107,5,'Grand Totals : ',0,0,'R',false);
    $pdf->SetFont('','BU');
    $pdf->Cell(8,5,$sumQty,1,0,'R',true);
    $pdf->Cell(7,5,'',1,0,'R',true);
    $pdf->Cell(7,5,$totalBox,1,0,'R',true);
    $pdf->Cell(7,5,$totalRack,1,0,'R',true);
    $pdf->Cell(7,5,$totalPallet,1,0,'R',true);
    $pdf->Cell(7,5,$sumCBM,1,0,'R',true);
    $pdf->Cell(10,5,$sumWt,1,0,'R',true);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(50,5,'',0,0,'L',false);
    $pdf->Ln(5);
    $pdf->Line(10,$pdf->GetY(),200,$pdf->GetY());
    $pdf->Ln(1);
}

function createPageFooter($pdf,$calBox)
{
    $getX = $pdf->GetY();
    if($getX > 175)
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
        createHeader($pdf,$pdf->bol,$pdf->totalPage++,'PICKUP SHEET',$pdf->pusPlus,$pdf->bol,$pdf->ld_dueDate);
        createHeaderData($pdf,$pdf->row);
    }
    else $pdf->Ln(1);
    
    $pdf->Cell(180,5,'Remark','RTL',0,'L',false);
    $pdf->Cell(95,5,'Packaging','RTL',1,'C',true);

    $pdf->Cell(180,5,'',1,0,'C',false);
    $pdf->SetFont('THSarabun','',12);
    $pdf->Cell(95/5,10,iconv( 'UTF-8','TIS-620','ยอดเช้ารับ'),'RTL',0,'C',true);

    $pdf->Cell(95/5,5,iconv( 'UTF-8','TIS-620','กล่องพลาสติก'),1,0,'C',true);
    $pdf->Cell(95/5,5,iconv( 'UTF-8','TIS-620','กล่องลูกฟูก'),1,0,'C',true);
    $pdf->Cell(95/5,5,iconv( 'UTF-8','TIS-620','พาเลทพลาสติก'),1,0,'C',true);
    $pdf->Cell(95/5,5,iconv( 'UTF-8','TIS-620','แร็ค'),1,1,'C',true);
    $pdf->SetFont('Arial', '', 9);

    $pdf->Cell(180,5,'',1,0,'C',false);
    $pdf->Cell(95/5,0,'','RL',0,'C',false);
    $pdf->Cell(95/5,5,'PTB',1,0,'C',true);
    $pdf->Cell(95/5,5,'CBM',1,0,'C',true);
    $pdf->Cell(95/5,5,'PTP',1,0,'C',true);
    $pdf->Cell(95/5,5,'STD',1,1,'C',true);
    // PLASTIC BOX,CORRUGATE BOX,RACK,PALLET    

    $pdf->Cell(180,5,'',1,0,'C',false);
    $pdf->SetFont('THSarabun','',12);
    $pdf->Cell(95/5,5,iconv( 'UTF-8','TIS-620','แผนรับ'),1,0,'C',true);
    $pdf->SetFont('Arial', '', 9);

    $box1 = $calBox['PLASTIC BOX']+$calBox['CARDBOARD BOX'];
    $box2 = $calBox['CORRUGATE BOX'];
    $pallet = $calBox['PLASTIC PALLET']+$calBox['WOODEN PALLET']+$calBox['STEEL PALLET'];
    $rack = $calBox['STEEL RACK'];

    $pdf->Cell(95/5,5,$box1==0? '-':$box1,1,0,'C',false);
    $pdf->Cell(95/5,5,$box2==0? '-':$box2,1,0,'C',false);
    $pdf->Cell(95/5,5,$pallet==0? '-':$pallet,1,0,'C',false);
    $pdf->Cell(95/5,5,$rack==0? '-':$rack,1,1,'C',false);

    $pdf->Cell(180,5,'',1,0,'C',false);
    $pdf->SetFont('THSarabun','',12);
    $pdf->Cell(95/5,5,iconv( 'UTF-8','TIS-620','รับจริง'),1,0,'C',true);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(95/5,5,'',1,0,'C',false);
    $pdf->Cell(95/5,5,'',1,0,'C',false);
    $pdf->Cell(95/5,5,'',1,0,'C',false);
    $pdf->Cell(95/5,5,'',1,1,'C',false);

    // $pdf->Ln(2);
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