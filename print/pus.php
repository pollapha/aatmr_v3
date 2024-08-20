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
 
//  $doctype = '4';
//  $copy = 1;
//  $printType = 'I';
//  $printerName = '1401';
//  $warter = 'NO';

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
      $header=new easyTable($this->instance, '%{85, 15}','border:0;font-family:Trirong;font-size:13; font-style:B;');
      $header->easyCell('MAZDA SALES (THAILAND) COMPANY LIMITED', 'valign:B;align:L');
      $header->easyCell('', 'img:images/mazda_logo_new.jpg, w17,h18;valign:B;align:R;rowspan:2');
      $header->printRow();
      $header->easyCell("<s 'font-style:B;font-size:7;>689 Bhiraj Tower at EmQuartier,15th-16th Floor, Sukhumvit Road, North Klongton, Vadhana, Bangkok 10110 Thailand</s>", 'valign:M;align:L');
      $header->printRow();
      $header->easyCell('PICKUP SHEET (MILKRUN PROJECT)', 'valign:M;align:C;colspan:2;font-size:10;border:TB');
      $header->printRow();
      $header->endTable(0);

      $header=new easyTable($this->instance, '%{17,14,17,29,23}','border:0;font-family:Trirong;font-size:7; font-style:B;');
      $header->easyCell('DATE', 'border:TL;valign:M;align:C;');
      $header->easyCell('PROMISED DATE', 'border:TL;valign:M;align:C;');
      $header->easyCell('PICKUP SHEET NUMBER', 'border:TL;valign:M;align:L;font-size:5;');
      $header->easyCell('', 'border:T;valign:M;align:L');
      $header->easyCell(utf8Th("ติดต่อ MST Buyer"), 'border:TLRB;valign:M;align:C;bgcolor:#e0e0e0;');
      $header->printRow();
      $header->easyCell($this->headerData->pusDate, 'border:BL;valign:M;align:C;');
      $header->easyCell($this->headerData->supplier_promised_date, 'border:BL;valign:M;align:C');
      $header->easyCell($this->headerData->pusChild, 'border:BL;valign:M;align:L');
      $header->easyCell('', 'border:BR;valign:M;align:L');
      $header->easyCell(utf8Th($this->headerData->buyer_name_en."\n".$this->headerData->buyer_phone), 'valign:T;align:L;rowspan:5;border:RL');
      $header->printRow();
      $header->easyCell(utf8Th("ติดต่อ Supplier"), 'border:TLR;valign:M;align:L;bgcolor:#e0e0e0;font-size:6;');
      $header->easyCell(utf8Th($this->headerData->supplier_contact.' '.$this->headerData->supplier_phone), 'valign:M;align:L;colspan:3');
      // $header->easyCell(utf8Th($this->headerData->supplier_phone), 'valign:M;align:L;border:L');
      $header->printRow();
      $header->easyCell(utf8Th("<s 'font-style:B;font-size:6;>รหัสผู้ผลิต (Supplier Code)</s>"), 'border:TL;valign:M;align:L;bgcolor:#e0e0e0;');
      $header->easyCell($this->headerData->supplier_code, 'border:TL;valign:M;align:L;colspan:3');
      // $header->easyCell(utf8Th("ติดต่อ MST Buyer"), 'border:TLRB;valign:M;align:C;bgcolor:#e0e0e0;');
      $header->printRow();
      $header->easyCell(utf8Th("<s 'font-style:B;font-size:6;>ชื่อผู้ผลิต (Supplier Name)</s>"), 'border:TL;valign:M;align:L;bgcolor:#e0e0e0;');
      $header->easyCell(utf8Th($this->headerData->supplier_name_en), 'border:TLR;valign:M;align:L;colspan:3');
      // $header->easyCell(utf8Th($this->headerData->buyer_name_en), 'border:TR;valign:M;align:L;');
      $header->printRow();
      $header->easyCell(utf8Th("<s 'font-style:B;font-size:6;>ที่อยู่ผู้ผลิต (Supplier Address)</s>"), 'border:TL;valign:M;align:L;bgcolor:#e0e0e0;');
      $header->easyCell(utf8Th($this->headerData->supplier_address_th), 'border:TL;valign:M;align:L;colspan:3');
      // $header->easyCell(utf8Th($this->headerData->buyer_phone), 'border:LR;valign:M;align:L;');
      $header->printRow();

      // $header=new easyTable($this->instance, '%{5,18,18,8,8,8,10,12.5,12.5}','border:1;font-family:Trirong;font-size:7; font-style:B;');
      $header=new easyTable($this->instance, '%{5,15,26,18,10,8,8,10}','border:1;font-family:Trirong;font-size:7; font-style:B;bgcolor:#e0e0e0;');
      $header->easyCell('NO.', 'valign:M;align:C');
      $header->easyCell(utf8Th("<s 'font-style:B;font-size:5.5;>PO Number / NEED BY DATE</s>"), 'valign:M;align:C');
      $header->easyCell(utf8Th("Part Number / Part Name"), 'valign:M;align:C');
      $header->easyCell(utf8Th("Supplier Item"), 'valign:M;align:C');
      $header->easyCell(utf8Th("Qty จำนวนชิ้นงาน"), 'valign:M;align:C;colspan:3');
      $header->easyCell(utf8Th("Package"), 'valign:M;align:C');
      // $header->easyCell(utf8Th("Package Q'ty (จำนวนแพ็คเกจ)"), 'valign:M;align:C;colspan:2');
      $header->printRow();
      $header->easyCell(utf8Th("ที่"), 'valign:M;align:C');
      $header->easyCell(utf8Th("หมายเลข PO"), 'valign:M;align:C');
      $header->easyCell(utf8Th("รหัสชิ้นงาน"), 'valign:M;align:C');
      $header->easyCell(utf8Th("รหัสชิ้นงานของซัพพลายออร์"), 'valign:M;align:C');
      $header->easyCell(utf8Th("แผนรับ"), 'valign:M;align:C');
      $header->easyCell(utf8Th("พขร. เช็ค	"), 'valign:M;align:C');
      $header->easyCell(utf8Th("W/H เช็ค"), 'valign:M;align:C');
      $header->easyCell(utf8Th("ชื่อบรรจุภัณฑ์"), 'valign:M;align:C');
      // $header->easyCell(utf8Th("Plan to Pickup แผนรับ"), 'valign:M;align:C');
      // $header->easyCell(utf8Th("Actual Pickup รับจริง"), 'valign:M;align:C');
      $header->printRow();
      $header->endTable(0);
      $this->instance->Code128(98.5,38.5,$this->headerData->pusChild,55,5);

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
 $sumLine = 0;
 $sumQty = 0;
 $sql = "SELECT s1.pusChild,t1.pusDate,t3.po_number,t3.qty,t3.line,t3.mst_need_date,t3.supplier_promised_date,
 t4.supplier_name_en,t4.supplier_code,t4.supplier_address_th,t3.supplier_Item,
 t4.supplier_contact,t4.supplier_phone,t5.part_number,t5.part_name,
 t6.buyer_phone,t6.buyer_name_en,t6.buyer_phone
 from tbl_transaction_header t1 
 left join tbl_transaction_body s1 on t1.ID=s1.transaction_headerID
 left join tbl_transaction_order t2 on s1.ID=t2.transaction_bodyID
 left join tbl_orders t3 on t2.order_ID=t3.ID 
 left join tbl_suppliers t4 on t3.supplier_ID=t4.ID
 left join tbl_partmaster t5 on t3.part_ID=t5.ID
 left join tbl_buyers t6 on t4.buyer_ID=t6.ID
 where s1.ID='$doctype' order by t3.po_number*1;";
 if($result = $mysqli->query($sql)) 
 { 
   if($result->num_rows>0)
   {
      $data = array();
      while($row1 = $result->fetch_object())
      {
        $data[] = $row1;
        $sumLine += $row1->line;
        $sumQty += $row1->qty;
      }
   }
 }
 $row2 = null;

 $pdf=new PDF('P');
 $pdf->AddFont('Trirong','','Trirong-Regular.php');
 $pdf->AddFont('Trirong','B','Trirong-Bold.php');
 $pdf->SetAutoPageBreak(true,10);
 $pdf->setInstance($pdf);
 $pdf->setHeaderData($data[0]);
 $pdf->AddPage();

 $header=new easyTable($pdf, '%{5,15,26,18,10,8,8,10}','border:1;font-family:Trirong;font-size:8; font-style:B;');
 $po = '';
 for($i=0,$len=count($data);$i<$len;$i++)
 {
    $header->rowStyle('min-height:15.3');
    $header->easyCell(utf8Th($i+1), 'valign:M;align:C');
    /* $sumLine += $data[$i]->line;
    $sumQty += $data[$i]->qty; */
    
    if($po != $data[$i]->po_number)
    {
      $header->easyCell(utf8Th($data[$i]->po_number."\n".$data[$i]->mst_need_date), 'valign:T;align:C');
    }
    else 
    {
      // $header->easyCell(utf8Th(''), 'valign:T;align:C');
      // $header->easyCell(utf8Th($data[$i]->mst_need_date), 'valign:M;align:C');
      $header->easyCell(utf8Th(''), 'valign:M;align:C');
    }
    
    $header->easyCell('<s font-style:B;font-size:9>'.utf8Th(wordwrap($data[$i]->part_number,1,' ', true).'</s>'."\n".$data[$i]->part_name), 'valign:T;align:C;');
    if(strlen($data[$i]->supplier_Item) >0)
    {
      $header->easyCell('<s font-style:B;font-size:7>'.utf8Th(wordwrap($data[$i]->supplier_Item,1,' ', true).'</s>'."\n".$data[$i]->part_name), 'valign:T;align:C;');
    }
    else
    {
      $header->easyCell(utf8Th(""), 'valign:M;align:C');
    }
    
    $header->easyCell(utf8Th($data[$i]->qty), 'valign:T;align:C;');
    $header->easyCell(utf8Th(""), 'valign:M;align:C');
    $header->easyCell(utf8Th(""), 'valign:M;align:C');
    $header->easyCell(utf8Th(""), 'valign:M;align:C');
    $header->printRow();
    if($po != $data[$i]->po_number)
    {
      $po = $data[$i]->po_number;
      $pdf->Code128(24,$pdf->GetY()-7.2,$data[$i]->po_number,20,5);
    }
    $pdf->Code128(55,$pdf->GetY()-7.2,$data[$i]->part_number,35,5);
    $qtyPlus = $data[$i]->qty*1>9 ? $data[$i]->qty:'0'.$data[$i]->qty;
    $pdf->Code128(133.5,$pdf->GetY()-7.8,$qtyPlus,15,6);
 }
 $header->endTable(0);

 $header=new easyTable($pdf, '%{5,49,10,10,8,8,10}','border:0;font-family:Trirong;font-size:7; font-style:B;');
 $header->rowStyle('min-height:8');
 $header->easyCell(utf8Th('*ทะเบียนรถ*'), 'valign:M;align:L;colspan:2;border:B;font-size:8;');
//  $header->easyCell(utf8Th(''), 'valign:T;align:C');
 $header->easyCell(utf8Th('Line'."\n".$sumLine), 'border:BLR;valign:B;align:C;font-style:B;font-size:8;');
 $header->easyCell(utf8Th($sumQty), 'border:BLR;valign:B;align:C;font-style:B;font-size:8;');
 $header->easyCell(utf8Th(''), 'valign:M;align:C');
 $header->easyCell(utf8Th(''), 'valign:M;align:C');
 $header->easyCell(utf8Th(''), 'valign:M;align:C');
 $header->printRow();

 if($pdf->GetY()>278.3610326087)
  $pdf->AddPage();

 $header=new easyTable($pdf,3,'align:L;width:100%;border:0;font-family:Trirong;font-size:8; font-style:B;valign:T;');
 $header->rowStyle('min-height:10');
 $header->easyCell(utf8Th("ลงลายมือชื่อ - ต้นทาง"), 'valign:M;align:C;');
 $header->easyCell(utf8Th("ลงลายมือชื่อ - ต้นทาง"), 'valign:M;align:C;');
 $header->easyCell(utf8Th("ลงลายมือชื่อ - ปลายทาง"), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th("................................................................................."), 'valign:M;align:C;');
 $header->easyCell(utf8Th("................................................................................."), 'valign:M;align:C;');
 $header->easyCell(utf8Th("................................................................................."), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th("(...............................................................................)"), 'valign:M;align:C;');
 $header->easyCell(utf8Th("(...............................................................................)"), 'valign:M;align:C;');
 $header->easyCell(utf8Th("(...............................................................................)"), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th("พนักงานขับรถ (Driver)"), 'valign:M;align:C;');
 $header->easyCell(utf8Th("ผู้จัดส่งสินค้า (Supplier)"), 'valign:M;align:C;');
 $header->easyCell(utf8Th("ผู้รับสินค้า (MAZDA)"), 'valign:M;align:C;');
 $header->printRow();
 $header->endTable(0);
 

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