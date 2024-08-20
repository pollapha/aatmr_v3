<?php
 include('../php/connection.php');
 error_reporting(E_ALL);
ini_set('display_errors', 1);
 
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

 
 
/*  $doctype = 'INV1712120005';
 $copy = 1;
 $printType = 'I';
 $printerName = '1401';
 $warter = 'NO'; */
 $sql = "SELECT t1.ID,t1.doc,invoiceDate,issuedDate,supID,t2.code,t2.name nameEn,t1.remark,t2.locationName address,'' ttvContact
from tbl_invoiceheader t1 left join tbl_supplier t2 on t1.supID=t2.ID
where t1.doc='$doctype';";
 if($result = $mysqli->query($sql)) 
 { 
   if($result->num_rows>0)
   {
    
   }
   else
   {
      echo '{"ch":2,"data":"ไม่สามารถปริ้นได้เนื่องจากคุณไม่ได้เลือกปริ้นเตอร์"}';
      exit();
   }
 }
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
      $header=new easyTable($this->instance, '%{25, 50, 25,}','border:1;font-family:Trirong;font-size:12; font-style:B;');
      $header->easyCell('', 'img:images/ttv-logo.gif, w35;align:L','');
      $header->easyCell("TITAN-VNS AUTOLOGISTICS CO., LTD. \n Invoice Return Supplier", 'valign:M;align:C');
      $header->easyCell('', 'img:images/aat-ford.jpg, w35;align:R');
      $header->printRow();
      $header->endTable(0);

      $header=new easyTable($this->instance, '%{50,25,25}','border:1;font-family:Trirong;font-size:8; font-style:B;valign:M;');
      $header->easyCell('', 'align:R;rowspan:2');
      $header->easyCell('Document no. :', 'align:R;bgcolor:#e0e0e0;');
      $header->easyCell($v->doc, 'align:L');
      $header->printRow();
      
      $header->easyCell('Return to Supplier Date :', 'align:R;bgcolor:#e0e0e0;');
      $header->easyCell('', '');
      $header->printRow();
      $header->endTable(0);

      $header=new easyTable($this->instance, '%{50,25,25}','border:1;font-family:Trirong;font-size:8; font-style:B;valign:M;');
      $header->easyCell(utf8Th("รับของที่\n--- ".$v->address."\n*** ".$v->ttvContact." ***"), 'align:L;valign:T;rowspan:3;font-size:7;');
      $header->easyCell(utf8Th(" :"), 'align:R;bgcolor:#e0e0e0;');
      $header->easyCell('', 'align:L');
      $header->printRow();
      
      $header->easyCell(utf8Th("ชื่อพนักงานขับรถ :"), 'align:R;bgcolor:#e0e0e0;');
      $header->easyCell('', '');
      $header->printRow();

      $header->easyCell(utf8Th("เบอร์โทรศัพท์ :"), 'align:R;bgcolor:#e0e0e0;');
      $header->easyCell('', '');
      $header->printRow();
      $header->endTable(0);

      $header=new easyTable($this->instance, '%{5,15,15,15,35,15}','border:1;font-family:Trirong;font-size:8; font-style:B;valign:M;bgcolor:#e0e0e0;');
      $header->easyCell('NO.', 'align:C');
      $header->easyCell('Invoice Date', 'align:C');
      $header->easyCell('Invoice No.', 'align:C');
      $header->easyCell('Supplier Code', 'align:C');
      $header->easyCell('Suppiler Name', 'align:C');
      $header->easyCell('Remark', 'align:C');
      $header->printRow();
      $header->endTable(0);

      $this->instance->Code128(30,23.5,$v->doc,55,6);

    }
    function Footer()
    {
      $this->SetXY(-20,-10);
      $this->SetFont('Arial','I',8);
      $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
 }
/*  MakeFont('Trirong-Bold.ttf','cp874');
 MakeFont('Trirong-BoldItalic.ttf','cp874');
 MakeFont('Trirong-Italic.ttf','cp874');
 MakeFont('Trirong-Regular.ttf','cp874'); */
 $tbody = array();
 $rowCount = 0;
 $printDate = '';
 $cbm = 0;
 $totalBox = 0;
 $numPallet = 0;
 $row = $result->fetch_object();
 $sql = "SELECT t2.invoiceNo,t1.invoiceDate,t3.code,t3.name nameEn,'' nameTh
from tbl_invoiceheader t1 left join tbl_invoicebody t2 on t1.ID=t2.headerID
left join tbl_supplier t3 on t1.supID=t3.ID
where t1.doc='$doctype' and t2.status <> 'CANCEL';";
 $result= $mysqli->query($sql);
 $row2 = null;

 $pdf=new PDF('P');
 $pdf->AddFont('Trirong','','Trirong-Regular.php');
 $pdf->AddFont('Trirong','B','Trirong-Bold.php');
 $pdf->SetAutoPageBreak(true,10);
 $pdf->setInstance($pdf);
 $pdf->setHeaderData($row);
 $pdf->AddPage();
 
 $c=0;
 $header=new easyTable($pdf, '%{5,15,15,15,35,15}','border:1;font-family:Trirong;font-size:8; font-style:B;valign:M;');
 while($row1 = $result->fetch_object())
 {
  $c++;
  $header->easyCell($c, 'align:C');
  $header->easyCell($row1->invoiceDate, 'align:C');
  $header->easyCell($row1->invoiceNo, 'align:C');
  $header->easyCell($row1->code, 'align:C');
  $header->easyCell(utf8Th($row1->nameEn."\n".$row1->nameTh), 'align:L');
  $header->easyCell(utf8Th($row->remark), 'align:C');
  $header->printRow();
 }
 $header->endTable(0);

 if($pdf->GetY()>278.3610326087)
  $pdf->AddPage();

 $pdf->Ln(3);
 $header=new easyTable($pdf,'%{70,30,}','align:L;width:100%;border:0;font-family:Trirong;font-size:8; font-style:B;valign:T;');
 $header->rowStyle('min-height:7');
 $header->easyCell(utf8Th("ลงชื่อผู้ส่งเอกสาร__________________________วันที่_______/_______/____________"), 'valign:M;align:L');
 $header->easyCell(utf8Th("ทะเบียนรถ__________________________"), 'valign:M;align:R;');
 $header->printRow();
 $header->rowStyle('min-height:7');
 $header->easyCell(utf8Th("ตัวบรรจง_________________________________ตำแหน่ง________________________"), 'valign:M;align:L;');
 $header->printRow();
 $header->rowStyle('min-height:7');
 $header->easyCell(utf8Th("ลงชื่อผู้รับเอกสาร__________________________วันที่_______/_______/____________"), 'valign:M;align:L;');
 $header->printRow();
 $header->rowStyle('min-height:7');
 $header->easyCell(utf8Th("ตัวบรรจง_________________________________ตำแหน่ง________________________"), 'valign:M;align:L;');
 $header->printRow();
/*  $header->easyCell(utf8Th("ลงลายมือชื่อ - ต้นทาง"), 'valign:M;align:C;');
 $header->easyCell(utf8Th("ลงลายมือชื่อ - ต้นทาง"), 'valign:M;align:C;');
 $header->easyCell(utf8Th("ลงลายมือชื่อ - ปลายทาง"), 'valign:M;align:C;'); */
 $header->printRow();
/*  $header->easyCell(utf8Th("............................................................................................"), 'valign:M;align:C;');
 $header->easyCell(utf8Th("............................................................................................"), 'valign:M;align:C;');
 $header->easyCell(utf8Th("............................................................................................"), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th("ผู้จัดส่งสินค้า (Supplier)"), 'valign:M;align:C;');
 $header->easyCell(utf8Th("พนักงานขับรถ (Driver)"), 'valign:M;align:C;');
 $header->easyCell(utf8Th("ผู้รับสินค้า (EDC)"), 'valign:M;align:C;');
 $header->printRow(); */
 $header->endTable(0);
 $pdf->Output(); 
 

//###############################################
function utf8Th($v)
{
  return iconv( 'UTF-8','TIS-620//TRANSLIT',$v);
}

 
?>