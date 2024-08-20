<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');


/*$printerName = $_REQUEST['printerName'];
$copy = $_REQUEST['copy'];
$doctype = $_REQUEST['doctype'];
$printType = $_REQUEST['printType'];
$warter = $_REQUEST['warter'];*/

$doctype = 'GRN1606230006';
$copy = 1;
$printType = 'I';
$printerName = '1401';
$warter = 'NO';
if($printerName == 'NO_PRINT' && $printType == 'F')
{
    echo '{ch:2,data:"ไม่สามารถปริ้นได้เนื่องจากคุณไม่ได้เลือกปริ้นเตอร์"}';
    exit();
}


include('../php/connection.php');
require('code128.php');
$sql = <<<EEE

select truckControlNo,tripTTV,truckLicense,truckType,driverName,phone,planTimeOut,planTimeIn,routeTrip,ld_dueTime,ld_supplierCode,date_format(ld_dueDate,'%d-%m-%y') ld_dueDate,ud_dueTime
from tbl_pickupheader where truckControlNo='T07-NA2I02-161219-1' and ediStatus='ACTIVE' order by ld_dueDate,ld_dueTime;

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
        createTableNormal($pdf,++$pdf->c,'AAT',$row->ud_dueTime);
        createTableNormal($pdf,++$pdf->c,'TTV',$row->planTimeIn);
        createPageFooter($pdf);

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
function createHeader($pdf,$totalPage)
{
    // truckControlNo,tripTTV,truckLicense,truckType,driverName,phone,planTimeOut,planTimeIn,routeTrip,ld_dueDate,ld_dueTime,ld_supplierCode
    $row = $pdf->row;
    $pdf->SetFont('THSarabun','B',32);
    $pdf->Cell(150,6,iconv( 'UTF-8','TIS-620','แบบฟอร์มควบคุมการเดินรถขนส่งสินค้า'),0,1,'L',false);
    $pdf->Cell(150,8,iconv( 'UTF-8','TIS-620','(Truck Control Form)'),0,1,'L',false);
    $pdf->Image('images/ttv-logo.gif',246,10,40,12);
    $pdf->Image('images/aatlogopng.png',206,10,35,12);
    $pdf->Image('images/truckseal.jpg',172.5,38,63,12);
    $pdf->Code128(232,26,$row->truckControlNo,55,6);

    $pdf->Ln(3);
    $pdf->SetFont('THSarabun','',16);
    $pdf->Cell(15,5,iconv( 'UTF-8','TIS-620','วันที่'),1,0,'C',false);
    $pdf->Cell(18,10,iconv( 'UTF-8','TIS-620',$row->ld_dueDate),1,0,'C',false);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620','Penske No.'),1,0,'C',false);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620',$row->routeTrip),1,0,'C',false);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620','ทะเบียนรถ'),1,0,'C',false);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620',$row->truckLicense),1,0,'C',false);
    $pdf->Cell(30,5,iconv( 'UTF-8','TIS-620','พนักงานชับรถ'),1,0,'C',false);
    $pdf->Cell(35,5,iconv( 'UTF-8','TIS-620',$row->driverName),1,0,'C',false);
    $pdf->Cell(20,5,iconv( 'UTF-8','TIS-620','เลขที่'),'LT',0,'C',false);
    $pdf->Cell(0,5,iconv( 'UTF-8','TIS-620',''),'L',1,'C',false);

    $pdf->Cell(15,5,iconv( 'UTF-8','TIS-620','รับงาน'),1,0,'C',false);
    $pdf->Cell(18,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620','TTV No.'),1,0,'C',false);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620',$row->tripTTV),1,0,'C',false);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620','ประเภทรถ'),1,0,'C',false);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620',$row->truckType),1,0,'C',false);
    $pdf->Cell(30,5,iconv( 'UTF-8','TIS-620','เบอร์ติดต่อ'),1,0,'C',false);
    $pdf->Cell(35,5,iconv( 'UTF-8','TIS-620',$row->phone),1,0,'C',false);
    $pdf->Cell(20,5,iconv( 'UTF-8','TIS-620','เอกสาร'),'LB',0,'C',false);
    $pdf->SetFont('THSarabun','B',12);
    $pdf->Cell(0,5,iconv( 'UTF-8','TIS-620',$row->truckControlNo),'LB',1,'C',false);

    $pdf->SetFont('THSarabun','B',15);
    $pdf->Cell(225,7,iconv( 'UTF-8','TIS-620',' *ข้อกำหนด: พนักงานขับรถต้องทำการบันทึกข้อมูลในแบบฟอร์มให้ถูกต้อง และครบถ้วน*'),'RLT',0,'L',false);
    $pdf->SetFont('THSarabun','B',14);
    $pdf->Cell(0,7,iconv( 'UTF-8','TIS-620','ส่วนของลูกค้า/เจ้าหน้าที่หน้างาน'),1,1,'C',false);

    $pdf->Cell(50,6,iconv( 'UTF-8','TIS-620','หัวหน้างาน กำหนดการเดินรถ'),1,0,'C',false);
    $pdf->Cell(100,6,iconv( 'UTF-8','TIS-620','1. พนักงานขับรถ บันทึกเวลาเดินรถและเลขไมล์จริง'),1,0,'C',false);
    $pdf->Cell(12,6,iconv( 'UTF-8','TIS-620','2.'),1,0,'C',false);
    $pdf->Cell(63,6,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);
    $pdf->Cell(27,6,iconv( 'UTF-8','TIS-620','สภาพงาน'),1,0,'C',false);
    $pdf->Cell(0,6,iconv( 'UTF-8','TIS-620','ลายเซ็น'),1,1,'C',false);

    $pdf->SetFont('THSarabun','',13);
    $pdf->Cell(10,7,iconv( 'UTF-8','TIS-620','ลำดับ'),1,0,'C',false);
    $pdf->Cell(25,7,iconv( 'UTF-8','TIS-620','จุดรับ-ส่ง (สินค้า)'),1,0,'C',false);
    $pdf->Cell(15,7,iconv( 'UTF-8','TIS-620','กำหนดการ'),1,0,'C',false);
    $pdf->Cell(15,7,iconv( 'UTF-8','TIS-620','เวลาเข้า'),1,0,'C',false);
    $pdf->Cell(15,3.5,iconv( 'UTF-8','TIS-620','เวลาเริ่มขึ้น'),'R',0,'C',false);
    $pdf->Cell(15,3.5,iconv( 'UTF-8','TIS-620','เวลาขึ้น'),'R',0,'C',false);
    $pdf->Cell(15,7,iconv( 'UTF-8','TIS-620','เวลาออก'),1,0,'C',false);
    $pdf->Cell(20,7,iconv( 'UTF-8','TIS-620','เลขไมล์'),1,0,'C',false);
    $pdf->Cell(20,7,iconv( 'UTF-8','TIS-620','สาเหตุที่ล่าช้า'),1,0,'C',false);
    $pdf->Cell(12,7,iconv( 'UTF-8','TIS-620','seal'),1,0,'C',false);
    $pdf->Cell(63/3,7,iconv( 'UTF-8','TIS-620','เลขซีล 1'),1,0,'C',false);
    $pdf->Cell(63/3,7,iconv( 'UTF-8','TIS-620','เลขซีล 2'),1,0,'C',false);
    $pdf->Cell(63/3,7,iconv( 'UTF-8','TIS-620','เลขซีล 3'),1,0,'C',false);
    $pdf->Cell(27/2,7,iconv( 'UTF-8','TIS-620','สมบูรณ์'),1,0,'C',false);
    $pdf->Cell(27/2,7,iconv( 'UTF-8','TIS-620','ไม่สมบูรณ์'),1,0,'C',false);
    $pdf->Cell(0,7,iconv( 'UTF-8','TIS-620','เจ้าหน้าที่/ลูกค้า'),1,1,'C',false);

    $pdf->Cell(10,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);
    $pdf->Cell(25,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);
    $pdf->Cell(15,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);
    $pdf->Cell(15,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);
    $pdf->Cell(15,-3.5,iconv( 'UTF-8','TIS-620','หรือลงของ'),'RT',0,'C',false);
    $pdf->Cell(15,-3.5,iconv( 'UTF-8','TIS-620','หรือลงเสร็จ'),'RT',0,'C',false);
    $pdf->Cell(15,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);
    $pdf->Cell(20,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);
    $pdf->Cell(20,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);
    $pdf->Cell(12,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);
    $pdf->Cell(63/3,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);
    $pdf->Cell(63/3,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);
    $pdf->Cell(63/3,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);
    $pdf->Cell(27/2,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);
    $pdf->Cell(27/2,0,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);
    $pdf->Cell(0,0,iconv( 'UTF-8','TIS-620',''),0,1,'C',false);

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
        createHeader($pdf,$pdf->totalPage++);
        createTableF($pdf,++$pdf->c,'TTV',$row->planTimeOut);
    }

    createTableNormal($pdf,++$pdf->c,$row->ld_supplierCode,$row->ld_dueTime);
}
function createTableF($pdf,$seq,$supplier,$time)
{
    $pdf->SetFont('THSarabun','B',20);
    $pdf->Cell(10,10,iconv( 'UTF-8','TIS-620',$seq),'RBL',0,'C',false);
    $pdf->Cell(25,10,iconv( 'UTF-8','TIS-620',$supplier),'RBL',0,'C',false);
    $pdf->Cell(60,10,iconv( 'UTF-8','TIS-620','ออกก่อนเวลา     '.substr($time,0,5)),'RBL',0,'C',false);
    $pdf->Cell(15,10,iconv( 'UTF-8','TIS-620',''),'RBL',0,'C',false);
    $pdf->Cell(20,10,iconv( 'UTF-8','TIS-620',''),'RBL',0,'C',false);
    $pdf->Cell(20,10,iconv( 'UTF-8','TIS-620',''),'RBL',0,'C',false);

    $pdf->SetFont('THSarabun','',13);
    $pdf->Cell(12,5,iconv( 'UTF-8','TIS-620','เข้า'),1,0,'C',false);
    $pdf->Cell(63/3,5,iconv( 'UTF-8','TIS-620',''),1,0,'C',false);
    $pdf->Cell(63/3,5,iconv( 'UTF-8','TIS-620',''),1,0,'C',false);
    $pdf->Cell(63/3,5,iconv( 'UTF-8','TIS-620',''),1,0,'C',false);
    $pdf->Cell(27/2,5,iconv( 'UTF-8','TIS-620',''),1,0,'C',false);
    $pdf->Cell(27/2,5,iconv( 'UTF-8','TIS-620',''),1,0,'C',false);
    $pdf->Cell(0,5,iconv( 'UTF-8','TIS-620',''),'RTL',1,'C',false);


    $pdf->Cell(10,0,iconv( 'UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell(25,0,iconv( 'UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell(60,0,iconv( 'UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell(15,0,iconv( 'UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell(20,0,iconv( 'UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell(20,0,iconv( 'UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->SetFont('THSarabun','',13);
    $pdf->Cell(12,5,iconv( 'UTF-8','TIS-620','ออก'),1,0,'C',false);
    $pdf->SetFont('THSarabun','B',15);
    $pdf->Cell(63/3,5,iconv( 'UTF-8','TIS-620',''),1,0,'C',false);
    $pdf->Cell(63/3,5,iconv( 'UTF-8','TIS-620',''),1,0,'C',false);
    $pdf->Cell(63/3,5,iconv( 'UTF-8','TIS-620',''),1,0,'C',false);
    $pdf->Cell(27/2,5,iconv( 'UTF-8','TIS-620','[   ]'),1,0,'C',false);
    $pdf->Cell(27/2,5,iconv( 'UTF-8','TIS-620','[   ]'),1,0,'C',false);
    $pdf->Cell(0,5,iconv( 'UTF-8','TIS-620',''),'RBL',1,'C',false);
}
function createTableNormal($pdf,$seq,$supplier,$time)
{
    $pdf->SetFont('THSarabun','B',20);
    $pdf->Cell(10,10,iconv( 'UTF-8','TIS-620',$seq),'RBL',0,'C',false);
    $pdf->Cell(25,10,iconv( 'UTF-8','TIS-620',$supplier),'RBL',0,'C',false);
    $pdf->Cell(15,10,iconv( 'UTF-8','TIS-620',substr($time,0,5)),'RBL',0,'C',false);
    $pdf->Cell(15,10,iconv( 'UTF-8','TIS-620',''),'RBL',0,'C',false);
    $pdf->Cell(15,10,iconv( 'UTF-8','TIS-620',''),'RBL',0,'C',false);
    $pdf->Cell(15,10,iconv( 'UTF-8','TIS-620',''),'RBL',0,'C',false);
    $pdf->Cell(15,10,iconv( 'UTF-8','TIS-620',''),'RBL',0,'C',false);
    $pdf->Cell(20,10,iconv( 'UTF-8','TIS-620',''),'RBL',0,'C',false);
    $pdf->Cell(20,10,iconv( 'UTF-8','TIS-620',''),'RBL',0,'C',false);

    $pdf->SetFont('THSarabun','',13);
    $pdf->Cell(12,5,iconv( 'UTF-8','TIS-620','เข้า'),1,0,'C',false);
    $pdf->SetFont('THSarabun','B',15);
    $pdf->Cell(63/3,5,iconv( 'UTF-8','TIS-620',''),1,0,'C',false);
    $pdf->Cell(63/3,5,iconv( 'UTF-8','TIS-620',''),1,0,'C',false);
    $pdf->Cell(63/3,5,iconv( 'UTF-8','TIS-620',''),1,0,'C',false);
    $pdf->Cell(27/2,5,iconv( 'UTF-8','TIS-620','[   ]'),1,0,'C',false);
    $pdf->Cell(27/2,5,iconv( 'UTF-8','TIS-620','[   ]'),1,0,'C',false);
    $pdf->Cell(0,5,iconv( 'UTF-8','TIS-620',''),'RTL',1,'C',false);


    $pdf->Cell(10,0,iconv( 'UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell(25,0,iconv( 'UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell(15,0,iconv( 'UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell(15,0,iconv( 'UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell(15,0,iconv( 'UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell(15,0,iconv( 'UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell(15,0,iconv( 'UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell(20,0,iconv( 'UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->Cell(20,0,iconv( 'UTF-8','TIS-620',''),'',0,'C',false);
    $pdf->SetFont('THSarabun','',13);
    $pdf->Cell(12,5,iconv( 'UTF-8','TIS-620','ออก'),1,0,'C',false);
    $pdf->SetFont('THSarabun','B',15);
    $pdf->Cell(63/3,5,iconv( 'UTF-8','TIS-620','"'),1,0,'C',false);
    $pdf->Cell(63/3,5,iconv( 'UTF-8','TIS-620','"'),1,0,'C',false);
    $pdf->Cell(63/3,5,iconv( 'UTF-8','TIS-620','"'),1,0,'C',false);
    $pdf->Cell(27/2,5,iconv( 'UTF-8','TIS-620','[   ]'),1,0,'C',false);
    $pdf->Cell(27/2,5,iconv( 'UTF-8','TIS-620','[   ]'),1,0,'C',false);
    $pdf->Cell(0,5,iconv( 'UTF-8','TIS-620',''),'RBL',1,'C',false);
}


function createPageFooter($pdf)
{
        //Footer

    $pdf->Ln(3);
    $pdf->SetFont('THSarabun','',13);
    $pdf->Cell(65,6,iconv( 'UTF-8','TIS-620','บันทึกการเติมเชื้อเพลิง'),1,0,'C',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);

    $pdf->Cell(140,6,iconv( 'UTF-8','TIS-620','เหตุผลที่เกิดการล่าช้า ในการ รับ-ส่ง สินค้า (ให้ใส่หมายเลขในช่อง สาเหตุที่ล่าช้า)'),1,0,'C',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'C',false);

    $pdf->Cell(27,6,iconv( 'UTF-8','TIS-620','ผู้ปล่อยรถ'),1,0,'C',false);
    $pdf->Cell(0,6,iconv( 'UTF-8','TIS-620','ผู้ตรวจสอบขากลับ'),1,1,'C',false);


    $pdf->Cell(30,6,iconv( 'UTF-8','TIS-620','สถานี (ปั๊ม-บริเวณ)'),1,0,'L',false);
    $pdf->Cell(35,6,iconv( 'UTF-8','TIS-620',''),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','1.'),1,0,'C',false);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','ออกจากลานจอดรถช้า หรือได้รับรถช้า'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','7.'),1,0,'C',false);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','ไม่มีช่องจอดรถ'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(27,6,iconv( 'UTF-8','TIS-620',''),'RTL',0,'L',false);
    $pdf->Cell(0,6,iconv( 'UTF-8','TIS-620',''),'RTL',1,'L',false);


    $pdf->Cell(30,6,iconv( 'UTF-8','TIS-620','วัน-เวลา ขณะเติม'),1,0,'L',false);
    $pdf->Cell(20,6,iconv( 'UTF-8','TIS-620','___/___/___'),1,0,'C',false);
    $pdf->Cell(15,6,iconv( 'UTF-8','TIS-620',':'),1,0,'C',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','2.'),1,0,'C',false);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','ช้ามาจากจุดก่อนหน้า (จุดรับ หรือ ส่งสินค้า)'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','8.'),1,0,'C',false);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','รอเจ้าหน้าที่ตรวจรับสินค้า'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(27,6,iconv( 'UTF-8','TIS-620',''),'RBL',0,'L',false);
    $pdf->Cell(0,6,iconv( 'UTF-8','TIS-620',''),'RBL',1,'L',false);


    $pdf->Cell(30,6,iconv( 'UTF-8','TIS-620','วัน-เวลา ขณะเติม'),1,0,'L',false);
    $pdf->Cell(20,6,iconv( 'UTF-8','TIS-620','แก๊ส NGV  [  ]'),1,0,'L',false);
    $pdf->Cell(15,6,iconv( 'UTF-8','TIS-620','ดีเซล  [  ]'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','3.'),1,0,'C',false);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','ฝนตก, รถติด'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','9.'),1,0,'C',false);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','ภาชนะเปล่าไม่ได้ถูกจัดเตรียม'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(27,6,iconv( 'UTF-8','TIS-620','หัวหน้างาน'),1,0,'C',false);
    $pdf->Cell(0,6,iconv( 'UTF-8','TIS-620','หัวหน้างาน'),1,1,'C',false);


    $pdf->Cell(30,6,iconv( 'UTF-8','TIS-620','เลขไมล์ขณะเติม'),1,0,'L',false);
    $pdf->Cell(35,6,iconv( 'UTF-8','TIS-620',''),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','4.'),1,0,'C',false);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','รอคิวรถ เพื่อเรียกเข้ารับ-ส่งสินค้า'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','10.'),1,0,'C',false);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','รอเอกสาร'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(27,6,iconv( 'UTF-8','TIS-620','วันที่ปล่อยรถ'),1,0,'C',false);
    $pdf->Cell(0,6,iconv( 'UTF-8','TIS-620','วันที่ตรวจสอบขากลับ'),1,1,'C',false);


    $pdf->Cell(30,6,iconv( 'UTF-8','TIS-620','ปริมาณ (ลิตร, กิโลฯ)'),1,0,'L',false);
    $pdf->Cell(35,6,iconv( 'UTF-8','TIS-620',''),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','5.'),1,0,'C',false);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','สินค้าไม่พร้อมจัดส่ง หรือ รอขึ้นสินค้า'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','11.'),1,0,'C',false);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','รถเสียระหว่างทาง'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(27,6,iconv( 'UTF-8','TIS-620',''),'RTL',0,'L',false);
    $pdf->Cell(0,6,iconv( 'UTF-8','TIS-620',''),'RTL',1,'L',false);


    $pdf->Cell(30,6,iconv( 'UTF-8','TIS-620','จำนวนเงินที่เติม (บาท)'),1,0,'L',false);
    $pdf->Cell(35,6,iconv( 'UTF-8','TIS-620',''),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620',''),0,0,'L',false);

    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','6.'),1,0,'C',false);
    $pdf->Cell(140/2-10,6,iconv( 'UTF-8','TIS-620','ภาชนะบรรจุไม่เพียงพอ'),1,0,'L',false);
    $pdf->Cell(10,6,iconv( 'UTF-8','TIS-620','12.'),1,0,'C',false);
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