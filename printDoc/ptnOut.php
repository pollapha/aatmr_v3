<?php
 include('../php/connection.php');
 include('../php/nm_common.php');
 
 if(!isset($_REQUEST['printerName']) || !isset($_REQUEST['copy']) || !isset($_REQUEST['doctype'])
  || !isset($_REQUEST['printType']) || !isset($_REQUEST['warter']) )
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
 
/*  $doctype = 'PTNI19020400010';
 $copy = 1;
 $printType = 'I';
 $printerName = '1401';
 $warter = 'NO'; */

 include 'fpdf.php';
 include 'exfpdf.php';
 include 'PDF_Code128.php';
 include 'easyTable.php';
 

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

      $v = $this->headerData;
      if(!$v) return;
      $header=new easyTable($this->instance, '%{10,90}','border:0;font-family:Trirong;font-size:7; font-style:B;');
      $header->easyCell('', 'img:images/tcLogo.png, w30,h25;valign:T;align:L');
      $header->easyCell(
      'TCZero Logistics (Thailand) Co.,Ltd'."\n".
      '122/1-2 Soi Chalongkrung tew Lamplatew'."\n".
      'Ladkrabang Bangkok 10520'."\n".
      'http://www.tanchong.com'
      , 'valign:T;align:L');
      $header->printRow();
      $header_doc = '';

      $header_doc = "Packaging Return Control Sheet \nใบผ่านเขตปลอดอากร (Custom Gate Pass )";

      $header->easyCell(utf8Th($header_doc), 'valign:M;align:C;colspan:2;font-size:10;border:1');
      $header->printRow();
      $header->endTable();
      $this->instance->Code128(232.5-85,11.5,$v->Document_No,55,5);
      $this->instance->SetFontSize(8);
      $this->instance->Text(242.5-85,20.5,wordwrap($v->Document_No,1,' ', true));


      $header=new easyTable($this->instance,'%{17,30,23,30}','border:0;font-family:Trirong;font-size:8; font-style:B;');
      
      $header->easyCell(utf8Th('ส่งจาก (Ship From) :'), 'valign:M;align:R;');
      $header->easyCell(utf8Th('TCZero Logistic Warehouse (TCSAT)'), 'valign:M;align:L;');
      $header->easyCell(utf8Th('ถึง (Ship To)Supplier Name :'), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Supplier_Name), 'valign:M;align:L;');
      $header->printRow();

      $header->easyCell(utf8Th('ทะเบียนรถ :'), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Truck_License), 'valign:M;align:L;');
      $header->easyCell(utf8Th('วันที่ (Date) :'), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->shipDate), 'valign:M;align:L;');
      $header->printRow();
      
      $header->easyCell(utf8Th('เบอร์โทร :'), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Phone), 'valign:M;align:L;');
      $header->easyCell(utf8Th('เวลา :'), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->shipTime), 'valign:M;align:L;');
      $header->printRow();

      $header->easyCell(utf8Th('ประเภทรถที่รับภาชนะ (Truck Control type)'), 'valign:M;align:R;colspan:2');
      $header->easyCell(utf8Th('Route no / Trip  No. :'), 'valign:M;align:R;');
      $header->easyCell(utf8Th(''), 'valign:M;align:L;');
      $header->printRow();
      $header->endTable();




      $header=new easyTable($this->instance,'%{14.5,3.5,25,3.5,25,3.5,25}','border:0;font-family:Trirong;font-size:7; font-style:B;');
      $header->easyCell(utf8Th(''), 'valign:M;align:C;border:0;');
      $header->easyCell($v->Truck_Type == '4W' ?'4':'', 'valign:M;align:C;border:1;font-family:ZapfDingbats;');
      $header->easyCell(utf8Th('4  ล้อ (4 Wheel)'), 'valign:B;align:L;');
      $header->easyCell($v->Truck_Type == '6W' ?'4':'', 'valign:M;align:C;border:1;font-family:ZapfDingbats;');
      $header->easyCell(utf8Th('6  ล้อ (6 Wheel)'), 'valign:B;align:L;');
      $header->easyCell($v->Truck_Type == '10W' ?'4':'', 'valign:M;align:C;border:1;font-family:ZapfDingbats;');
      $header->easyCell(utf8Th('10  ล้อ (10 Wheel)'), 'valign:B;align:L;');
      $header->printRow();
      $header->endTable();
      
    }
    function Footer()
    {
      $this->SetXY(-20,-10);
      $this->SetFont('Arial','I',8);
      $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
 }


 $tbody = array();
 $rowCount = 0;
 $printDate = '';
 $cbm = 0;
 $totalBox = 0;
 $numPallet = 0;
 $sumLine = 0;
 $sumQty = 0;
 $sql = "SELECT t1.Document_No,t1.Document_No_Ref,t1.Document_Date,t1.Truck_License,t1.Truck_Type,t1.Driver_Name,t1.Phone,t1.Remarks,t1.Status_Header,
 t2.Qty,t2.Status_Body,t2.Supplier_ID,t2.Package_ID,t2.Package_Header_ID,t2.ID Package_Body_ID,
 t3.Package_Type,t3.Package_Name,t1.Confirm_Name,t1.Confirm_Status,t1.Confirm_Datetime,
 t4.Supplier_Code,t4.Supplier_Name,t1.Creation_Datetime,
   date_format(t1.Document_Date,'%d/%m/%Y') shipDate,'' shipTime
 from tbl_nm_milkrun_package_out_header t1 
 inner join tbl_nm_milkrun_package_out_body t2 on t1.ID=t2.Package_Header_ID
 left join tbl_nm_milkrun_packagemaster t3 on t2.Package_ID=t3.ID
 left join tbl_nm_milkrun_suppliermaster t4 on t2.Supplier_ID=t4.ID
   where t1.Document_No='$doctype';";
 
 $data = array();
 if($result = $mysqli->query($sql)) 
 { 
   if($result->num_rows>0)
   {
      
      while($row1 = $result->fetch_object())
      {
        $data[] = $row1;
      }
   }
 }
 $row2 = null;
 if(count($data) == 0)
  $dataHeader = 0;
 else
 $dataHeader = $data[0];

 $pdf=new PDF('P');
 $pdf->AddFont('Trirong','','Trirong-Regular.php');
 $pdf->AddFont('Trirong','B','Trirong-Bold.php');
 $pdf->SetAutoPageBreak(true,10);
 $pdf->setInstance($pdf);
 $pdf->setHeaderData($dataHeader);
 $pdf->AddPage();

 if(count($data) == 0)
 {
    $pdf->SetFont('Trirong');
    $pdf->Text(10,10,utf8Th("ไม่พบรายการ Order"));
    pdfOut($pdf,$copy,$printType,$printerName,$warter);
    exit();
 }

 $dataPack = array('PTB'=>0,'CPB'=>0,'STR'=>0,'PPL'=>0,'WPL'=>0,'DRUM'=>0,'PAIL'=>0,'GALLON'=>0,'BUCKET'=>0,'GAS TANK'=>0,'GAS TUBE'=>0);
 for($i=0,$len=count($data);$i<$len;$i++)
 {
    $rowObj = $data[$i];
    $dataPack[$rowObj->Package_Type] += $rowObj->Qty;
 }


 $header=new easyTable($pdf,'%{10,30,20,20,20}','border:1;font-family:Trirong;font-size:10; font-style:B;');

 $header->easyCell(utf8Th('ลำดับ (No.)'), 'valign:M;align:C;rowspan:2');
 $header->easyCell(utf8Th('ชนิดบรรจุภัณฑ์'), 'valign:M;align:C;colspan:2');
 $header->easyCell(utf8Th('จำนวน'), 'valign:M;align:C;');
 $header->easyCell(utf8Th('หมายเหตุ'), 'valign:M;align:C;');
 $header->printRow();
 				

 $header->easyCell(utf8Th('Packaging Type'), 'valign:M;align:C;colspan:2');
 $header->easyCell(utf8Th('Quantity'), 'valign:M;align:C;');
 $header->easyCell(utf8Th('Remark'), 'valign:M;align:C;');
 $header->printRow();

 $header->easyCell(utf8Th('1'), 'valign:M;align:C;');
 $header->easyCell(utf8Th(''), 'img:images/package/str.png,h15');
 $header->easyCell(utf8Th('STR (Steel Rack) ภาชนะใส่ชิ้นงาน ชนิดชั้นเหล็ก'), 'valign:M;align:C;');
 $header->easyCell(utf8Th(checkNumber($dataPack['STR'])), 'valign:M;align:C;');
 $header->easyCell(utf8Th(''), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th('2'), 'valign:M;align:C;');
 $header->easyCell(utf8Th(''), 'img:images/package/ptb.png,h15');
 $header->easyCell(utf8Th('PTB (Plastic box) พลาสติก กล่องแข็ง'), 'valign:M;align:C;');
 $header->easyCell(utf8Th(checkNumber($dataPack['PTB'])), 'valign:M;align:C;');
 $header->easyCell(utf8Th(''), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th('3'), 'valign:M;align:C;');
 $header->easyCell(utf8Th(''), 'img:images/package/cpb.png,h15');
 $header->easyCell(utf8Th('CPB  (Corrugate box) พลาสติก กล่องลูกฟูก'), 'valign:M;align:C;');
 $header->easyCell(utf8Th(checkNumber($dataPack['CPB'])), 'valign:M;align:C;');
 $header->easyCell(utf8Th(''), 'valign:M;align:C;');
 $header->printRow();

 $header->easyCell(utf8Th('4'), 'valign:M;align:C;');
 $header->easyCell(utf8Th(''), 'img:images/package/ppl.png,w25,h15');
 $header->easyCell(utf8Th('PPL (Plastic Pallet) พาเลท พลาสติก'), 'valign:M;align:C;');
 $header->easyCell(utf8Th(checkNumber($dataPack['PPL'])), 'valign:M;align:C;');
 $header->easyCell(utf8Th(''), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th('5'), 'valign:M;align:C;');
 $header->easyCell(utf8Th(''), 'img:images/package/wpl.png,w30,h15');
 $header->easyCell(utf8Th('WPL (Wooden Pallet) พาเลท ไม้'), 'valign:M;align:C;');
 $header->easyCell(utf8Th(checkNumber($dataPack['WPL'])), 'valign:M;align:C;');
 $header->easyCell(utf8Th(''), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th('6'), 'valign:M;align:C;');
 $header->easyCell(utf8Th(''), 'img:images/package/drum.png,w15,h15;');
 $header->easyCell(utf8Th('Drum (ถัง)'), 'valign:M;align:C;');
 $header->easyCell(utf8Th(checkNumber($dataPack['DRUM'])), 'valign:M;align:C;');
 $header->easyCell(utf8Th(''), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th('7'), 'valign:M;align:C;');
 $header->easyCell(utf8Th(''), 'img:images/package/pail.png,h15');
 $header->easyCell(utf8Th('Pail (ปิ๊บ)'), 'valign:M;align:C;');
 $header->easyCell(utf8Th(checkNumber($dataPack['PAIL'])), 'valign:M;align:C;');
 $header->easyCell(utf8Th(''), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th('8'), 'valign:M;align:C;');
 $header->easyCell(utf8Th(''), 'img:images/package/gallon.png,h15');
 $header->easyCell(utf8Th('Gallon (แกลลอน)/Bucket / ถังพลาสติก'), 'valign:M;align:C;');
 $header->easyCell(utf8Th(checkNumber($dataPack['GALLON']+$dataPack['BUCKET'])), 'valign:M;align:C;');
 $header->easyCell(utf8Th(''), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th('9'), 'valign:M;align:C;');
 $header->easyCell(utf8Th(''), 'img:images/package/gas.png,w20,h15');
 $header->easyCell(utf8Th('ถังแก๊ส,ท่อแก๊ส'), 'valign:M;align:C;');
 $header->easyCell(utf8Th(checkNumber($dataPack['GAS TANK']+$dataPack['GAS TUBE'])), 'valign:M;align:C;');
 $header->easyCell(utf8Th(''), 'valign:M;align:C;');
 $header->printRow();
 $header->endTable();
 $numRow = 0;

 if($pdf->GetY()>258.3610326087)
  $pdf->AddPage();

  $header=new easyTable($pdf,'%{29,51,20}','align:L;width:100%;border:0;font-family:Trirong;font-size:9; font-style:B;valign:T;');
  $header->rowStyle('min-height:8');
  $header->easyCell(utf8Th('ชื่อ ผู้จ่าย / ตรวจสอบ ภาชนะออก  :'), 'valign:B;align:L;border:TL');
  $header->easyCell(utf8Th('____________________________________________________'), 'valign:B;align:L;border:T');
  $header->easyCell(utf8Th('  (Warehouse)'), 'valign:B;align:L;border:TR');
  $header->printRow();
  $header->rowStyle('min-height:6');
  $header->easyCell(utf8Th('ชื่อ ผู้จ่าย / ตรวจสอบ ภาชนะออก  :'), 'valign:B;align:L;border:L');
  $header->easyCell(utf8Th('____________________________________________________'), 'valign:B;align:L;');
  $header->easyCell(utf8Th('  (Warehouse)'), 'valign:B;align:L;border:R');
  $header->printRow();
  $header->rowStyle('min-height:6');
  $header->easyCell(utf8Th('ชื่อ ผู้นำภาชนะออก  :'), 'valign:B;align:L;border:L');
  $header->easyCell(utf8Th('____________________________________________________'), 'valign:B;align:L;');
  $header->easyCell(utf8Th('    (Transportation)'), 'valign:B;align:L;border:R');
  $header->printRow();
  $header->rowStyle('min-height:6');
  $header->easyCell(utf8Th('ชื่อ ผู้รับภาชนะ  :'), 'valign:B;align:L;border:L');
  $header->easyCell(utf8Th('____________________________________________________'), 'valign:B;align:L;border:');
  $header->easyCell(utf8Th('    (Supplier)'), 'valign:B;align:L;border:R');
  $header->printRow();
  $header->endTable(0);

  $header=new easyTable($pdf,'%{10,90}','align:L;width:100%;border:1;font-family:Trirong;font-size:8; font-style:B;valign:B;');
  $header->easyCell(utf8Th('หมายเหตุ : '), 'valign:T;align:L;rowspan:4;border:LB');
  $header->easyCell(utf8Th('(1) พนักงานฝ่ายคลังสินค้า (ผู้นำ) บรรจุภัณฑ์และเอกสาร (ต้นฉบับ สำหรับ คลังสินค้าเก็บ) '), 'border:R');
  $header->printRow();
  $header->easyCell(utf8Th('(2) พนักงานฝ่ายคลังสินค้า (ผู้นำ) ส่งบรรจุภัณฑ์และเอกสาร (สำเนา2 ส่งกลับคลังสินค้าหลังจากผู้รับบรรจุภัณฑ์เช็นรับแล้ว) '), 'border:R');
  $header->printRow();
  $header->easyCell(utf8Th('(3) ผู้นำ ส่งบรรจุภัณฑ์และเอกสาร (สำเนา 3 พนักงานขับรถ เก็บ )'), 'border:R');
  $header->printRow();
  $header->easyCell(utf8Th('(4) ผู้รับ บรรจุภัณฑ์และเอกสาร (สำเนา4 ผู้รับบรรจุภัณฑ์เก็บ) (Copy 4 for Supplier)'), 'border:RB');
  $header->printRow();
  $header->endTable(0);
  

  pdfOut($pdf,$copy,$printType,$printerName,$warter);
//###############################################
function pdfOut($pdf,$copy,$printType,$printerName,$warter)
{
  if(strlen($printerName) >0)
  {
    $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,5);
    $fileName = $randomString.strtotime(date('Y-m-d H:i:s')).'-'.$printerName.'-'.$copy.'.pdf'; 
    $fileName = "vendor/".$fileName;
    $pdf->Output($fileName,$printType);
    if($printType == 'F')
      echo $fileName;
  }else echo '{ch:2,data:"ไม่สามารถปริ้นได้เนื่องจากคุณไม่ได้เลือกปริ้นเตอร์"}';
}

function checkPackage($rowObj,$type)
{
  $data = array();
  if($rowObj->Package_Type == $type)
  {
    $data[] = $rowObj->Qty;
    $data[] = $rowObj->Balance_End;
  }
  else
  {
    $data[] = '';
    $data[] = '';
  }
  return $data; 
}

function utf8Th($v)
{
  return iconv( 'UTF-8','TIS-620//IGNORE',$v);
}

function checkNumber($data)
{
  return $data>0 ? $data:'';
}

 
?>