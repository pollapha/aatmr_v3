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

/*$doctype = 'T07-NA2I01-161221-1';
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

SELECT t2.StopSequenceNumber s5_seq,substring_index(t2.Supplier_Name,' ',2)ld_supplierName,'' tripTTV,t1.Load_ID truckControlNo,t1.truckLicense,t1.truckType,t1.driverName,t1.phone,t1.planTimeOut_Origin planTimeOut,t1.planTimeIn_Origin planTimeIn,t1.Load_ID bol,
t2.Route routeTrip,t2.Supplier_Code ld_supplierCode,date_format(t2.PlanIN_Datetime,'%d-%m-%y') ld_dueDate,'' ud_dueTime,
date_format(t2.PlanIN_Datetime,'%H:%i') ld_dueTime
from tbl_204header_api t1
left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID
where t1.Load_ID='$doctype' group by substring_index(t2.Supplier_Code,'-',1) order by t2.StopSequenceNumber ;

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
$pdf->SetAutoPageBreak(true,10);
$pdf->setFillColor(230,230,230); 
$pdf->AliasNbPages();
$c = 0;
$pdf->c = 0;
$pdf->AddFont('THSarabun','','THSarabun.php');
$pdf->AddFont('THSarabun','B','THSarabun Bold.php');
if($result = $mysqli->query($sql)) 
{ 
    $len = $result->num_rows;
    if($len > 0)
    {  
        $totalPage = 0;
        $row = $result->fetch_object();
        $pdf->totalPage = $totalPage;
        $pdf->row = $row;
        $pdf->warter = $warter;
        

        createTableBody($pdf,$row);

        for($i=1;$i<$len;$i++)
        {
            $row = $result->fetch_object();
            createTableBody($pdf,$row);
        }
        // createTableNormal($pdf,++$pdf->c,'AAT',$row->ud_dueTime);
        createTableNormal($pdf,++$pdf->c,'TTV',$row->planTimeIn,'');
        

        if(strlen($printerName) >0)
        {
          $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,5);
          $fileName = $randomString.strtotime(date('Y-m-d H:i:s')).'-'.$printerName.'-'.$copy.'.pdf'; 
          $pdf->Output("files/".$fileName,$printType);
          // echo '{"ch":1,"data":"เอกสารออกที่เครื่องปริ้นเตอร์ชื่อ '.$printerName.' จำนวน '.$copy.'ชุด"}';
          echo $fileName;
        }else echo '{ch:2,data:"ไม่สามารถปริ้นได้เนื่องจากคุณไม่ได้เลือกปริ้นเตอร์"}';
    }else echo '{"ch":0,"data":"ไม่พบ  <b>'.$doctype.'</b> ในระบบ"}';
}

$mysqli->close();
function createHeader($pdf,$totalPage)
{
    // truckControlNo,tripTTV,truckLicense,truckType,driverName,phone,planTimeOut,planTimeIn,routeTrip,ld_dueDate,ld_dueTime,ld_supplierCode
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->drawTextBox('Page '.$pdf->PageNo().' / {nb}',280, 3, 'R', 'T',0);
    $row = $pdf->row;
    $pdf->SetFont('THSarabun','B',32);
    $pdf->Cell(150,6,iconv( 'UTF-8','TIS-620','แบบฟอร์มควบคุมเวลาเดินรถ โดย CS'),0,1,'L',false);
    $pdf->Cell(150,8,iconv( 'UTF-8','TIS-620','(Truck Control Form by CS)'),0,1,'L',false);
    $pdf->Image('images/ttv-logo.gif',246,13,40,12);
    $pdf->Image('images/aatlogopng.png',206,13,35,12);
    // $pdf->Image('images/truckseal.jpg',172.5,38,63,12);
    $pdf->setFillColor(0,0,0); 
    $pdf->Code128(232,28,$row->bol,55,6);
    $pdf->setFillColor(230,230,230); 

    $pdf->Ln(3);
    $pdf->SetFont('THSarabun','',16);
    $pdf->Cell(15,5,iconv( 'UTF-8','TIS-620','วันที่'),1,0,'C',1);
    $pdf->Cell(18,10,iconv( 'UTF-8','TIS-620',$row->ld_dueDate),1,0,'C',false);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620','Penske No.'),1,0,'C',1);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620',$row->routeTrip),1,0,'C',false);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620','ทะเบียนรถ'),1,0,'C',1);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620',$row->truckLicense),1,0,'C',false);
    $pdf->Cell(30,5,iconv( 'UTF-8','TIS-620','พนักงานชับรถ'),1,0,'C',1);
    $pdf->Cell(35,5,iconv( 'UTF-8','TIS-620',$row->driverName),1,0,'C',false);
    $pdf->Cell(20,5,iconv( 'UTF-8','TIS-620','เลขที่'),'LT',0,'C',1);
    $pdf->Cell(0,5,iconv( 'UTF-8','TIS-620',''),'L',1,'C',false);

    $pdf->Cell(15,5,iconv( 'UTF-8','TIS-620','รับงาน'),1,0,'C',1);
    $pdf->Cell(18,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620','TTV No.'),1,0,'C',1);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620',$row->tripTTV),1,0,'C',false);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620','ประเภทรถ'),1,0,'C',1);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620',$row->truckType),1,0,'C',false);
    $pdf->Cell(30,5,iconv( 'UTF-8','TIS-620','เบอร์ติดต่อ'),1,0,'C',1);
    $pdf->Cell(35,5,iconv( 'UTF-8','TIS-620',$row->phone),1,0,'C',false);
    $pdf->Cell(20,5,iconv( 'UTF-8','TIS-620','เอกสาร'),'LB',0,'C',1);
    $pdf->SetFont('THSarabun','B',12);
    $pdf->Cell(0,5,iconv( 'UTF-8','TIS-620',$row->bol),'LB',1,'C',false);


    $pdf->SetFont('THSarabun','B',14);
    $pdf->Cell(0,8,iconv( 'UTF-8','TIS-620',' *ข้อกำหนด: พนักงานขับรถต้องทำการบันทึกข้อมูลในแบบฟอร์มให้ถูกต้อง และครบถ้วน*'),'RBLT',1,'L',false);


    $pdf->SetFont('THSarabun','B',12);
    $pdf->Cell(38,5,iconv('UTF-8','TIS-620','หัวหน้างานกำหนดการเดินรถ'),'RLB',0,'C',1);
    $pdf->Cell(94,5,iconv('UTF-8','TIS-620','แผนติดตามสถานะรถ'),'RLB',0,'C',1);
    $pdf->Cell(52,10,iconv( 'UTF-8','TIS-620','สถานะการเดินรถ'),1,0,'C',1);
    $pdf->Cell(20,5,iconv( 'UTF-8','TIS-620',''),0,0,'C',0);
    $pdf->setFillColor(150,150,150);
    $pdf->Cell(0,5,iconv( 'UTF-8','TIS-620','Packaging and INV Control'),1,0,'C',1);
    $pdf->setFillColor(220,220,220);
    $pdf->Ln();

    $pdf->SetFont('THSarabun','B',14);
    $pdf->Cell(8,5,iconv( 'UTF-8','TIS-620','ลำดับ'),1,0,'C',1);
    $pdf->Cell(30,5,iconv( 'UTF-8','TIS-620','จุดรับ-ส่ง (สินค้า)'),1,0,'C',1);
    $pdf->Cell(8,5,iconv( 'UTF-8','TIS-620','NO.'),1,0,'C',1);
    $pdf->Cell(40,5,iconv( 'UTF-8','TIS-620','รายการติดตาม'),1,0,'C',1);
    $pdf->Cell(26,5,iconv( 'UTF-8','TIS-620','กำหนดการณ์โทร'),1,0,'C',1);
    $pdf->Cell(20,5,iconv( 'UTF-8','TIS-620','เวลาโทรจริง'),1,0,'C',1);
    $pdf->Cell(52,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',0);
    $pdf->Cell(20,5,iconv( 'UTF-8','TIS-620',''),0,0,'C',0);
    
    $pdf->Cell(15,5,iconv( 'UTF-8','TIS-620','PTB'),1,0,'C',1);
    $pdf->Cell(15,5,iconv( 'UTF-8','TIS-620','CPB'),1,0,'C',1);
    $pdf->Cell(15,5,iconv( 'UTF-8','TIS-620','STR'),1,0,'C',1);
    $pdf->Cell(15,5,iconv( 'UTF-8','TIS-620','PPL'),1,0,'C',1);
    $pdf->setFillColor(150,150,150);
    $pdf->Cell(0,5,iconv( 'UTF-8','TIS-620','Q\'ty'),1,1,'C',1);
    $pdf->setFillColor(220,220,220);

}


function createTableBody($pdf,$row)
{
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
        createHeader($pdf,$pdf->totalPage++);
        if($pdf->totalPage == 1)
         createTableF($pdf,++$pdf->c,'TTV',$row->planTimeOut);
    }
    createTableNormal($pdf,++$pdf->c,$row->ld_supplierName,$row->ld_dueTime,$row->ld_supplierCode);
}
function createTableF($pdf,$seq,$supplier,$time)
{
    $h = 5;
    $pdf->SetFont('THSarabun','B',14);
    $pdf->Cell(8,$h*4,iconv( 'UTF-8','TIS-620',$seq),1,0,'C',1);
    $pdf->SetFont('THSarabun','B',16);
    $pdf->Cell(30,$h*4,iconv( 'UTF-8','TIS-620',$supplier),1,0,'C',0);
    $pdf->SetFont('THSarabun','B',14);
    $pdf->Cell(8,$h,iconv( 'UTF-8','TIS-620','1'),1,0,'C',0);
    $pdf->Cell(40,$h,iconv( 'UTF-8','TIS-620','ติดตามความพร้อม'),1,0,'L',0);
    $pdf->Cell(26,$h,iconv( 'UTF-8','TIS-620',date("H:i", strtotime('-61 minutes 2000-01-01 '.$time))),1,0,'C',1);
    $pdf->Cell(20,$h,iconv( 'UTF-8','TIS-620',''),1,0,'C',0);
    $pdf->Cell(52/2,$h,iconv( 'UTF-8','TIS-620','[  ] ไม่ติดปัญหา'),1,0,'C',0);
    $pdf->Cell(52/2,$h,iconv( 'UTF-8','TIS-620','[  ] ติดปัญหา'),1,0,'C',0);
    $pdf->Cell(20,$h,iconv( 'UTF-8','TIS-620','รับมาจาก'),'RLT',0,'C',0);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLT',0,'C',0);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLT',0,'C',0);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLT',0,'C',0);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLT',0,'C',0);
    $pdf->Cell( 0,$h,iconv( 'UTF-8','TIS-620',''),'RLT',1,'C',0);

    $pdf->Cell(8,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',0);
    $pdf->Cell(30,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',0);
    $pdf->Cell(8,$h,iconv( 'UTF-8','TIS-620','2'),1,0,'C',0);
    $pdf->Cell(40,$h,iconv( 'UTF-8','TIS-620','ติดตามความพร้อม'),1,0,'L',0);
    $pdf->Cell(26,$h,iconv( 'UTF-8','TIS-620',date("H:i", strtotime('-31 minutes 2000-01-01 '.$time))),1,0,'C',0);
    $pdf->Cell(20,$h,iconv( 'UTF-8','TIS-620',''),1,0,'C',0);
    $pdf->Cell(52/2,$h,iconv( 'UTF-8','TIS-620','[  ] ไม่ติดปัญหา'),1,0,'C',0);
    $pdf->Cell(52/2,$h,iconv( 'UTF-8','TIS-620','[  ] ติดปัญหา'),1,0,'C',0);
    $pdf->Cell(20,$h,iconv( 'UTF-8','TIS-620','AAT'),'RLB',0,'C',0);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLB',0,'C',0);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLB',0,'C',0);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLB',0,'C',0);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLB',0,'C',0);
    $pdf->Cell( 0,$h,iconv( 'UTF-8','TIS-620',''),'RLB',1,'C',0);

    $pdf->Cell(8,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',0);
    $pdf->Cell(30,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',0);
    $pdf->Cell(8,$h,iconv( 'UTF-8','TIS-620','3'),1,0,'C',0);
    $pdf->Cell(40,$h,iconv( 'UTF-8','TIS-620','เช็ครถออกเดินทาง'),1,0,'L',0);
    $pdf->Cell(26,$h,iconv( 'UTF-8','TIS-620',date("H:i", strtotime('-1 minutes 2000-01-01 '.$time))),1,0,'C',0);
    $pdf->Cell(20,$h,iconv( 'UTF-8','TIS-620',''),1,0,'C',0);
    $pdf->Cell(52/2,$h,iconv( 'UTF-8','TIS-620','[  ] ไม่ติดปัญหา'),1,0,'C',0);
    $pdf->Cell(52/2,$h,iconv( 'UTF-8','TIS-620','[  ] ติดปัญหา'),1,0,'C',0);
    $pdf->Cell(20,$h,iconv( 'UTF-8','TIS-620','รับมาจาก'),'RLT',0,'C',1);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLT',0,'C',1);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLT',0,'C',1);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLT',0,'C',1);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLT',0,'C',1);
    $pdf->Cell( 0,$h,iconv( 'UTF-8','TIS-620',''),'RLT',1,'C',1);

    $pdf->Cell(8,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',0);
    $pdf->Cell(30,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',0);
    $pdf->Cell(8,$h,iconv( 'UTF-8','TIS-620','4'),1,0,'C',0);
    $pdf->Cell(40,$h,iconv( 'UTF-8','TIS-620','เช็ครถระว่างเดินทาง'),1,0,'L',0);
    $pdf->Cell(26,$h,iconv( 'UTF-8','TIS-620',date("H:i", strtotime('+29 minutes 2000-01-01 '.$time))),1,0,'C',0);
    $pdf->Cell(20,$h,iconv( 'UTF-8','TIS-620',''),1,0,'C',0);
    $pdf->Cell(52/2,$h,iconv( 'UTF-8','TIS-620','[  ] ไม่ติดปัญหา'),1,0,'C',0);
    $pdf->Cell(52/2,$h,iconv( 'UTF-8','TIS-620','[  ] ติดปัญหา'),1,0,'C',0);
    $pdf->Cell(20,$h,iconv( 'UTF-8','TIS-620','Supplier'),'RLB',0,'C',1);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLB',0,'C',1);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLB',0,'C',1);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLB',0,'C',1);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLB',0,'C',1);
    $pdf->Cell( 0,$h,iconv( 'UTF-8','TIS-620',''),'RLB',1,'C',1);

    $pdf->timeDiff = date("Y-m-d H:i", strtotime('+29 minutes 2000-01-01 '.$time));
}

function createTableL($pdf,$seq,$supplier,$time)
{
    
}

function createTableNormal($pdf,$seq,$supplier,$time,$supplierCode)
{
    $h = 5;

    $start_date = new DateTime($pdf->timeDiff);
    $end_date = new DateTime(date("Y-m-d H:i",strtotime('-60 minutes 2000-01-01 '.$time)));
    $interval = $start_date->diff($end_date);
    $dateDiff1 = ($interval->format('%h') * 60 + $interval->format('%i'));
    $dateDiff1 = $interval->invert == 0 && $dateDiff1>30 ? date("H:i",strtotime('-60 minutes 2000-01-01 '.$time)):'-';

    $pdf->SetFont('THSarabun','B',14);
    $pdf->Cell(8,$h*4,iconv( 'UTF-8','TIS-620',$seq),1,0,'C',1);

    if($supplierCode =='GRBPA')
        $pdf->Cell(30,$h*4,iconv( 'UTF-8','TIS-620','AAT(POWER TIAN)'),1,0,'C',0);
    else if($supplierCode =='GRBNA')
        $pdf->Cell(30,$h*4,iconv( 'UTF-8','TIS-620','AAT(BODY)'),1,0,'C',0);
    else 
        $pdf->Cell(30,$h*4,iconv( 'UTF-8','TIS-620',$supplier),1,0,'C',0);

    // $pdf->Cell(30,$h*4,iconv( 'UTF-8','TIS-620',$supplier),1,0,'C',0);

    $pdf->Cell(8,$h,iconv( 'UTF-8','TIS-620','1'),1,0,'C',0);
    $pdf->Cell(40,$h,iconv( 'UTF-8','TIS-620','ติดตามสถานะรถก่อนถึง'),1,0,'L',0);
    $pdf->Cell(26,$h,iconv( 'UTF-8','TIS-620',$dateDiff1),1,0,'C',1);
    $pdf->Cell(20,$h,iconv( 'UTF-8','TIS-620',''),1,0,'C',0);
    $pdf->Cell(52/2,$h,iconv( 'UTF-8','TIS-620','[  ] ไม่ติดปัญหา'),1,0,'C',0);
    $pdf->Cell(52/2,$h,iconv( 'UTF-8','TIS-620','[  ] ติดปัญหา'),1,0,'C',0);
    $pdf->Cell(20,$h,iconv( 'UTF-8','TIS-620','รับมาจาก'),'RLT',0,'C',0);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLT',0,'C',0);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLT',0,'C',0);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLT',0,'C',0);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLT',0,'C',0);
    $pdf->Cell( 0,$h,iconv( 'UTF-8','TIS-620',''),'RLT',1,'C',0);

    $start_date = new DateTime($pdf->timeDiff);
    $end_date = new DateTime(date("Y-m-d H:i",strtotime('-30 minutes 2000-01-01 '.$time)));
    $interval = $start_date->diff($end_date);
    $dateDiff1 = ($interval->format('%h') * 60 + $interval->format('%i'));
    $dateDiff1 = $interval->invert == 0 && $dateDiff1>30 ? date("H:i",strtotime('-30 minutes 2000-01-01 '.$time)):'-';

    $pdf->Cell(8,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',0);
    $pdf->Cell(30,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',0);
    $pdf->Cell(8,$h,iconv( 'UTF-8','TIS-620','2'),1,0,'C',0);
    $pdf->Cell(40,$h,iconv( 'UTF-8','TIS-620','ติดตามสถานะรถก่อนถึง'),1,0,'L',0);
    $pdf->Cell(26,$h,iconv( 'UTF-8','TIS-620',$dateDiff1),1,0,'C',0);
    $pdf->Cell(20,$h,iconv( 'UTF-8','TIS-620',''),1,0,'C',0);
    $pdf->Cell(52/2,$h,iconv( 'UTF-8','TIS-620','[  ] ไม่ติดปัญหา'),1,0,'C',0);
    $pdf->Cell(52/2,$h,iconv( 'UTF-8','TIS-620','[  ] ติดปัญหา'),1,0,'C',0);
    $pdf->Cell(20,$h,iconv( 'UTF-8','TIS-620','AAT'),'RLB',0,'C',0);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLB',0,'C',0);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLB',0,'C',0);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLB',0,'C',0);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLB',0,'C',0);
    $pdf->Cell( 0,$h,iconv( 'UTF-8','TIS-620',''),'RLB',1,'C',0);


    $pdf->Cell(8,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',0);
    $pdf->Cell(30,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',0);
    $pdf->Cell(8,$h,iconv( 'UTF-8','TIS-620','3'),1,0,'C',0);
    $pdf->Cell(40,$h,iconv( 'UTF-8','TIS-620','ติดตามสถานะรถเมื่อถึง'),1,0,'L',0);
    $pdf->Cell(26,$h,iconv( 'UTF-8','TIS-620',date("H:i", strtotime('2000-01-01 '.$time))),1,0,'C',0);
    $pdf->Cell(20,$h,iconv( 'UTF-8','TIS-620',''),1,0,'C',0);
    $pdf->Cell(52/2,$h,iconv( 'UTF-8','TIS-620','[  ] ไม่ติดปัญหา'),1,0,'C',0);
    $pdf->Cell(52/2,$h,iconv( 'UTF-8','TIS-620','[  ] ติดปัญหา'),1,0,'C',0);
    $pdf->Cell(20,$h,iconv( 'UTF-8','TIS-620','รับมาจาก'),'RLT',0,'C',1);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLT',0,'C',1);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLT',0,'C',1);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLT',0,'C',1);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLT',0,'C',1);
    $pdf->Cell( 0,$h,iconv( 'UTF-8','TIS-620',''),'RLT',1,'C',1);

    $pdf->Cell(8,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',0);
    $pdf->Cell(30,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',0);
    $pdf->Cell(8,$h,iconv( 'UTF-8','TIS-620','4'),1,0,'C',0);
    $pdf->Cell(40,$h,iconv( 'UTF-8','TIS-620','ติดตามสถานะรถก่อนออก'),1,0,'L',0);
    $pdf->Cell(26,$h,iconv( 'UTF-8','TIS-620',date("H:i", strtotime('+30 minutes 2000-01-01 '.$time))),1,0,'C',0);
    $pdf->Cell(20,$h,iconv( 'UTF-8','TIS-620',''),1,0,'C',0);
    $pdf->Cell(52/2,$h,iconv( 'UTF-8','TIS-620','[  ] ไม่ติดปัญหา'),1,0,'C',0);
    $pdf->Cell(52/2,$h,iconv( 'UTF-8','TIS-620','[  ] ติดปัญหา'),1,0,'C',0);
    $pdf->Cell(20,$h,iconv( 'UTF-8','TIS-620','Supplier'),'RLB',0,'C',1);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLB',0,'C',1);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLB',0,'C',1);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLB',0,'C',1);
    $pdf->Cell(15,$h,iconv( 'UTF-8','TIS-620',''),'RLB',0,'C',1);
    $pdf->Cell( 0,$h,iconv( 'UTF-8','TIS-620',''),'RLB',1,'C',1);

    $pdf->timeDiff = date("Y-m-d H:i", strtotime('+29 minutes 2000-01-01 '.$time));
}


function createPageFooter($pdf)
{
        //Footer

    $pdf->Ln(3);
    $pdf->SetFont('THSarabun','',13);
    $pdf->Cell(65,6,iconv( 'UTF-8','TIS-620','เบอร์ติดต่อหัวหน้างาน '),1,0,'C',1);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);

    $pdf->Cell(140,6,iconv( 'UTF-8','TIS-620','เหตุผลที่เกิดการล่าช้า ในการ รับ-ส่ง สินค้า (ให้ใส่หมายเลขในช่อง สาเหตุที่ล่าช้า)'),1,0,'C',1);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);

    $pdf->Cell(27,6,iconv( 'UTF-8','TIS-620','ผู้ปล่อยรถ'),1,0,'C',1);
    $pdf->Cell(0,6,iconv( 'UTF-8','TIS-620','ผู้ตรวจสอบขากลับ'),1,1,'C',1);


    $pdf->Cell(30,6,iconv( 'UTF-8','TIS-620','CS Controller'),1,0,'L',1);
    $pdf->Cell(35,6,iconv( 'UTF-8','TIS-620','นิสารัตน์ 091-2394577 '),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','1.'),1,0,'C',1);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','ออกจากลานจอดรถช้า หรือได้รับรถช้า'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','7.'),1,0,'C',1);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','ไม่มีช่องจอดรถ'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(27,6,iconv( 'UTF-8','TIS-620',''),'RTL',0,'L',false);
    $pdf->Cell(0,6,iconv( 'UTF-8','TIS-620',''),'RTL',1,'L',false);


    $pdf->Cell(30,6,iconv( 'UTF-8','TIS-620','Transport Controller'),1,0,'L',1);
    $pdf->Cell(35,6,iconv( 'UTF-8','TIS-620','ภัสนี 092-2514170'),1,0,'C',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','2.'),1,0,'C',1);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','ช้ามาจากจุดก่อนหน้า (จุดรับ หรือ ส่งสินค้า)'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','8.'),1,0,'C',1);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','รอเจ้าหน้าที่ตรวจรับสินค้า'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(27,6,iconv( 'UTF-8','TIS-620',''),'RBL',0,'L',false);
    $pdf->Cell(0,6,iconv( 'UTF-8','TIS-620',''),'RBL',1,'L',false);


    $pdf->Cell(30,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);
    $pdf->Cell(20,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);
    $pdf->Cell(15,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','3.'),1,0,'C',1);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','ฝนตก, รถติด'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','9.'),1,0,'C',1);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','ภาชนะเปล่าไม่ได้ถูกจัดเตรียม'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(27,6,iconv( 'UTF-8','TIS-620','หัวหน้างาน'),1,0,'C',1);
    $pdf->Cell(0,6,iconv( 'UTF-8','TIS-620','หัวหน้างาน'),1,1,'C',1);


    $pdf->Cell(30,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);
    $pdf->Cell(35,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','4.'),1,0,'C',1);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','รอคิวรถ เพื่อเรียกเข้ารับ-ส่งสินค้า'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','10.'),1,0,'C',1);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','รอเอกสาร'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(27,6,iconv( 'UTF-8','TIS-620','วันที่ปล่อยรถ'),1,0,'C',1);
    $pdf->Cell(0,6,iconv( 'UTF-8','TIS-620','วันที่ตรวจสอบขากลับ'),1,1,'C',1);


    $pdf->Cell(30,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);
    $pdf->Cell(35,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','5.'),1,0,'C',1);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','สินค้าไม่พร้อมจัดส่ง หรือ รอขึ้นสินค้า'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','11.'),1,0,'C',1);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','รถเสียระหว่างทาง'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(27,6,iconv( 'UTF-8','TIS-620',''),'RTL',0,'L',false);
    $pdf->Cell(0,6,iconv( 'UTF-8','TIS-620',''),'RTL',1,'L',false);


    $pdf->Cell(30,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);
    $pdf->Cell(35,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','6.'),1,0,'C',1);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','ภาชนะบรรจุไม่เพียงพอ'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','12.'),1,0,'C',1);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','รถเกิดอุบัติเหตุ'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(27,6,iconv( 'UTF-8','TIS-620',''),'RBL',0,'L',false);
    $pdf->Cell(0,6,iconv( 'UTF-8','TIS-620',''),'RBL',1,'L',false);

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