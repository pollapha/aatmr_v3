<?php
include('../php/connection.php');

  if(!isset($_REQUEST['printerName']) || !isset($_REQUEST['copy']) || !isset($_REQUEST['doctype'])
  || !isset($_REQUEST['printType']) || !isset($_REQUEST['warter']))
     closeDBT($mysqli,2,'ข้อมูลไม่ถูกต้อง 1');
 $printerName = checkTXT($mysqli,$_REQUEST['printerName']);
 $copy = checkINT($mysqli,$_REQUEST['copy']);
 $doctype = checkTXT($mysqli,$_REQUEST['doctype']);
 $printType = checkTXT($mysqli,$_REQUEST['printType']);
 $warter = checkTXT($mysqli,$_REQUEST['warter']);
 
 if(strlen($printerName) == 0 || strlen($doctype) == 0 || strlen($printType) == 0 || strlen($warter) == 0 || $copy == 0) 
     closeDBT($mysqli,2,'ข้อมูลไม่ถูกต้อง 2');
 
 if($printerName == 'NO_PRINT' && $printType == 'F')
 {
     echo '{"ch":2,"data":"ไม่สามารถปริ้นได้เนื่องจากคุณไม่ได้เลือกปริ้นเตอร์"}';
     exit();
 }
 
/*  $doctype = '2018-08-23';
 $copy = 1;
 $printType = 'I';
 $printerName = '1401';
 $warter = 'NO'; */

 include 'fpdf.php';
 include 'exfpdf.php';
 include 'PDF_Code128.php';
 include 'easyTable.php';
 include('../vendor/autoload.php');
use \Curl\Curl;
 

 class PDF extends PDF_Code128
 {
    var $headerData;
    var $instance;
    function __construct($orientation='P', $unit='mm', $format='A4') 
    {
      parent::__construct($orientation,$unit,$format);
      $this->AliasNbPages();
    }
    public function setHeaderData($v)
    {
      $this->headerData = $v;
    }
    public function setInstance($v)
    {
      $this->instance = $v;
    }
    function Header()
    {

      /* $v = $this->headerData;
      $header=new easyTable($this->instance, '%{25,50,25}','border:0;font-family:Trirong;font-size:9; font-style:B;');
      $header->easyCell(utf8Th(''), 'valign:B;align:L');
      $header->easyCell(utf8Th('เอกสารควบคุมการเดินรถขนส่งสินค้า'."\n".'TRUCK CONTROL of MST Milkrun'), 'valign:B;align:C');
      $header->easyCell(utf8Th(''), 'valign:B;align:L');
      $header->printRow();
      $header->endTable(0);

      $header=new easyTable($this->instance, '%{10,13,10,15,15,17,20}','border:1;font-family:Trirong;font-size:8; font-style:B;');
      $header->easyCell(utf8Th('วันที่รับงาน'), 'valign:M;align:C;rowspan:2;bgcolor:#e0e0e0;');
      $header->easyCell(utf8Th($v->pusDate), 'valign:M;align:C;rowspan:2;');
      $header->easyCell(utf8Th('ทะเบียนรถ'), 'valign:B;align:C;bgcolor:#e0e0e0;');
      $header->easyCell(utf8Th($v->truckLicense), 'valign:B;align:C');
      $header->easyCell(utf8Th('พนักงานขับรถรับงาน'), 'valign:B;align:C;bgcolor:#e0e0e0;');
      $header->easyCell(utf8Th($v->driverName), 'valign:B;align:C');
      $header->easyCell(utf8Th('เลขที่เอกสาร'), 'valign:B;align:L;border:BL;');
      $header->printRow();
      
      $header->easyCell(utf8Th('ประเภทรถ'), 'valign:B;align:C;bgcolor:#e0e0e0;');
      $header->easyCell(utf8Th($v->truckType), 'valign:B;align:C');
      $header->easyCell(utf8Th('เบอร์ติดต่อ'), 'valign:B;align:C;bgcolor:#e0e0e0;');
      $header->easyCell(utf8Th($v->phone), 'valign:B;align:C');
      $header->easyCell(utf8Th($v->pus), 'valign:B;align:L;');
      $header->printRow();
      $header->endTable(0);


      $header=new easyTable($this->instance, '%{100}','border:0;font-family:Trirong;font-size:8; font-style:B;');
      $header->rowStyle('min-height:8.5');
      $header->easyCell(utf8Th('*ข้อกำหนด: พนักงานขับรถต้องทำการบันทึกข้อมูลในแบบฟอร์มให้ถูกต้อง และครบถ้วน*'), 'valign:T;align:L');
      $header->printRow();
      $header->endTable(0);

      $header=new easyTable($this->instance, '%{4.5,7,7,7.5,8,7,12,5,10,10,10,12}','border:1;font-family:Trirong;font-size:7; font-style:B;bgcolor:#e0e0e0;');
      $header->easyCell(utf8Th('1. บันทึกเวลาเดินรถและเลขไมล์จริง'), 'valign:M;align:C;colspan:8;');
      $header->easyCell(utf8Th(''), 'valign:M;align:C;colspan:4;border:BL;bgcolor:#ffffff;');
      $header->printRow();
      $header->easyCell(utf8Th('จุดรับ'), 'valign:M;align:C');
      $header->easyCell(utf8Th('แผนเข้ารับ'), 'valign:M;align:C');
      $header->easyCell(utf8Th('เวลาเข้า'), 'valign:M;align:C');
      $header->easyCell(utf8Th("เวลาเริ่มขึ้น\nหรือลงของ"), 'valign:M;align:C');
      $header->easyCell(utf8Th("เวลาขึ้นหรือ\nลงเสร็จ"), 'valign:M;align:C');
      $header->easyCell(utf8Th('เวลาออก'), 'valign:M;align:C');
      $header->easyCell(utf8Th('เลขไมล์'), 'valign:M;align:C');
      $header->easyCell(utf8Th('2. seal'), 'valign:M;align:C');
      $header->easyCell(utf8Th('เลขซีล 1'), 'valign:M;align:C');
      $header->easyCell(utf8Th('เลขซีล 2'), 'valign:M;align:C');
      $header->easyCell(utf8Th('เลขซีล 3'), 'valign:M;align:C');
      $header->easyCell(utf8Th("ลายเซ็น\nเจ้าหน้าที่/ลูกค้า"), 'valign:M;align:C');
      $header->printRow();
      $header->endTable(0); */

      

    }
    function Footer()
    {
      /* $this->SetXY(-20,-10);
      $this->SetFont('Arial','I',8);
      $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C'); */
    }
 }


 $pdf=new PDF('L','mm','A3');
 $pdf->AddFont('Trirong','','Trirong-Regular.php');
 $pdf->AddFont('Trirong','B','Trirong-Bold.php');
 $pdf->SetAutoPageBreak(true,10);
 $pdf->setInstance($pdf);
//  $pdf->setHeaderData($data[0]);
 $pdf->AddPage();
 $pdf->SetY(15);
 $header=new easyTable($pdf,1,'border:0;font-family:Trirong;font-size:20; font-style:B;border-width:.5;');
 $header->easyCell(utf8Th('Weekly Report'), 'valign:M;align:C;');
 $header->printRow();
 $header->endTable();

 $curl = new Curl();
 $param = array('obj'=>array('date1' =>$doctype));
 $curl->post('http://localhost/mazdamr/report/weeklyReport_common.php',$param);
 if (!$curl->error) 
 {
    
    $header=new easyTable($pdf,16,'border:1;font-family:Trirong;font-size:7; font-style:B;border-width:.5;');
    $dataAr = json_decode($curl->response);
    
    $header->rowStyle('min-height:8;bgcolor:#27AE60;font-color:#ffffff;line-height:1.2;font-size:10;');
    $header->easyCell(utf8Th("Delivery\n Time"), 'valign:M;align:C;rowspan:2;');
    $header->easyCell(utf8Th($dataAr->dateShow[0]), 'valign:M;align:C;colspan:2;border:LTB');
    $header->easyCell(utf8Th('Total Trip '.$dataAr->totalTrip[0]), 'valign:M;align:R;border:TR');
    $header->easyCell(utf8Th($dataAr->dateShow[1]), 'valign:M;align:C;colspan:2;border:LTB');
    $header->easyCell(utf8Th('Total Trip '.$dataAr->totalTrip[1]), 'valign:M;align:R;border:TR');
    $header->easyCell(utf8Th($dataAr->dateShow[2]), 'valign:M;align:C;colspan:2;border:LTB');
    $header->easyCell(utf8Th('Total Trip '.$dataAr->totalTrip[2]), 'valign:M;align:R;border:TR');
    $header->easyCell(utf8Th($dataAr->dateShow[3]), 'valign:M;align:C;colspan:2;border:LTB');
    $header->easyCell(utf8Th('Total Trip '.$dataAr->totalTrip[3]), 'valign:M;align:R;border:TR');
    $header->easyCell(utf8Th($dataAr->dateShow[4]), 'valign:M;align:C;colspan:2;border:LTB');
    $header->easyCell(utf8Th('Total Trip '.$dataAr->totalTrip[4]), 'valign:M;align:R;border:TR');
    $header->printRow();

    $header->rowStyle('min-height:8;bgcolor:#D2E3EF;font-size:9;');
    // $header->easyCell(utf8Th(''), 'valign:M;align:C;');
    $header->easyCell(utf8Th('DOCK 2'), 'valign:M;align:C;');
    $header->easyCell(utf8Th('DOCK 5'), 'valign:M;align:C;');
    $header->easyCell(utf8Th('DOCK 6'), 'valign:M;align:C;');
    $header->easyCell(utf8Th('DOCK 2'), 'valign:M;align:C;');
    $header->easyCell(utf8Th('DOCK 5'), 'valign:M;align:C;');
    $header->easyCell(utf8Th('DOCK 6'), 'valign:M;align:C;');
    $header->easyCell(utf8Th('DOCK 2'), 'valign:M;align:C;');
    $header->easyCell(utf8Th('DOCK 5'), 'valign:M;align:C;');
    $header->easyCell(utf8Th('DOCK 6'), 'valign:M;align:C;');
    $header->easyCell(utf8Th('DOCK 2'), 'valign:M;align:C;');
    $header->easyCell(utf8Th('DOCK 5'), 'valign:M;align:C;');
    $header->easyCell(utf8Th('DOCK 6'), 'valign:M;align:C;');
    $header->easyCell(utf8Th('DOCK 2'), 'valign:M;align:C;');
    $header->easyCell(utf8Th('DOCK 5'), 'valign:M;align:C;');
    $header->easyCell(utf8Th('DOCK 6'), 'valign:M;align:C;');
    $header->printRow();
    for($i=0,$len=count($dataAr->data);$i<$len;$i++)
    {
      $row = $dataAr->data[$i];
      $header->rowStyle('min-height:5');
      $header->easyCell(utf8Th($row->deliveryTime), 'valign:M;align:C;;font-size:10;');
      $header->easyCell(utf8Th($row->dock2_1), '');
      $header->easyCell(utf8Th($row->dock5_1), '');
      $header->easyCell(utf8Th($row->dock6_1), '');

      $header->easyCell(utf8Th($row->dock2_2), '');
      $header->easyCell(utf8Th($row->dock5_2), '');
      $header->easyCell(utf8Th($row->dock6_2), '');

      $header->easyCell(utf8Th($row->dock2_3), '');
      $header->easyCell(utf8Th($row->dock5_3), '');
      $header->easyCell(utf8Th($row->dock6_3), '');

      $header->easyCell(utf8Th($row->dock2_4), '');
      $header->easyCell(utf8Th($row->dock5_4), '');
      $header->easyCell(utf8Th($row->dock6_4), '');

      $header->easyCell(utf8Th($row->dock2_5), '');
      $header->easyCell(utf8Th($row->dock5_5), '');
      $header->easyCell(utf8Th($row->dock6_5), '');
      $header->printRow();
    }
    $header->endTable();
    $pdf->Image('images/mazda_logo_new.jpg',10,8.5,17);
    $pdf->Image('images/ttv-logo.gif',360,12.5,50);
 	
 } else 
 {
 	
 }


 

 

 
/*  if($pdf->GetY()>278.3610326087)
  $pdf->AddPage(); */
  
  if(strlen($printerName) >0)
  {
    $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,5);
    $fileName = $randomString.strtotime(date('Y-m-d H:i:s')).'-'.$printerName.'-'.$copy.'.pdf'; 
    $fileName = "vendor/".$fileName;
    $pdf->Output($fileName,$printType);
    if($printType == 'F')
      echo $fileName;
  }else echo '{ch:2,data:"ไม่สามารถปริ้นได้เนื่องจากคุณไม่ได้เลือกปริ้นเตอร์"}';

//###############################################
function utf8Th($v)
{
  return iconv( 'UTF-8','TIS-620//IGNORE',$v);
}

 
?>