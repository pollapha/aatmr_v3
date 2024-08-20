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
 
/*  $doctype = 'ORD19011700006';
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
      $header=new easyTable($this->instance, '%{15,85}','border:0;font-family:Trirong;font-size:7; font-style:B;');
      $header->easyCell('', 'img:images/ttv-logo.gif, w30,h8;valign:T;align:R');
      $header->easyCell('TITAN-VNS AUTOLOGISTICS CO., LTD.'."\n".'49/63 MOO 5 TUNGSUNKLA SRIRACHA CHONBURI 20230'."\n".
      'Phone +66(0) 3840 1505-6,3804 1787-8'."\n".'Fax : +66(0) 3849 4300'
      , 'valign:T;align:L');
      $header->printRow();
      $header->easyCell('PICKING ORDER SHEET (SUBMAT)', 'valign:M;align:C;colspan:2;font-size:10;border:TB');
      $header->printRow();
      $header->endTable(0);
      $this->instance->Code128(145.5,11.5,$v->Document_No,55,5);
      $this->instance->SetFontSize(8);
      $this->instance->Text(155.5,20.5,wordwrap($v->Document_No,1,' ', true));

      $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Document No :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Document_No), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Document Date :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Document_Datetime), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Delivery Date :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Delivery_Txt), 'valign:M;align:L;');
      $header->printRow();
      $header->endTable(0);

      $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Creation Date :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Creation_Datetime), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Material Type :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Material_Type), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Receiver Name :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Receiver_Name), 'valign:M;align:L;');
      $header->printRow();
      $header->endTable(0);

      $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Remarks :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Remarks_Header), 'valign:M;align:L;');
      $header->easyCell(utf8Th(""), 'valign:M;align:R;');
      $header->easyCell(utf8Th(""), 'valign:M;align:L;');
      $header->easyCell(utf8Th(""), 'valign:M;align:R;');
      $header->easyCell(utf8Th(""), 'valign:M;align:L;');
      $header->printRow();
      $header->endTable(0);

    
      /* $header=new easyTable($this->instance,5,'align:L;width:100%;border:1;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("No."), 'valign:M;align:R;');
      $header->easyCell(utf8Th("Part No"), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Part Name"), 'valign:M;align:R;');
      $header->easyCell(utf8Th("Qty"), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Location"), 'valign:M;align:L;');
      $header->printRow();
      $header->endTable(0); */
      $header=new easyTable($this->instance, '%{5,20,35,20,20}','border:1;font-family:Trirong;font-size:8; font-style:B;');
      $header->easyCell(utf8Th("No."), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Part No"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Part Name"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Qty"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Location"), 'valign:M;align:C;');
      $header->printRow();
      $header->endTable(0);
      
      
      

      /* TITAN-VNS AUTOLOGISTICS CO., LTD. 49/63
      MOO 5 TUNGSUNKLA SRIRACHA CHONBURI 20230
      Phone +66(0) 3840 1505-6,3804 1787-8
      Fax : +66(0) 3849 4300 */

      /* $header=new easyTable($this->instance, '%{17,14,17,29,23}','border:0;font-family:Trirong;font-size:7; font-style:B;');
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
      
      $header->printRow();
      $header->easyCell(utf8Th("<s 'font-style:B;font-size:6;>รหัสผู้ผลิต (Supplier Code)</s>"), 'border:TL;valign:M;align:L;bgcolor:#e0e0e0;');
      $header->easyCell($this->headerData->supplier_code, 'border:TL;valign:M;align:L;colspan:3');
      
      $header->printRow();
      $header->easyCell(utf8Th("<s 'font-style:B;font-size:6;>ชื่อผู้ผลิต (Supplier Name)</s>"), 'border:TL;valign:M;align:L;bgcolor:#e0e0e0;');
      $header->easyCell(utf8Th($this->headerData->supplier_name_en), 'border:TLR;valign:M;align:L;colspan:2');
      $header->easyCell(utf8Th('Truck No. '.$this->headerData->truckLicense), 'border:TLR;valign:M;align:L;');
      
      $header->printRow();
      $header->easyCell(utf8Th("<s 'font-style:B;font-size:6;>ที่อยู่ผู้ผลิต (Supplier Address)</s>"), 'border:TL;valign:M;align:L;bgcolor:#e0e0e0;');
      $header->easyCell(utf8Th($this->headerData->supplier_address_th), 'border:TL;valign:M;align:L;colspan:3');
      
      $header->printRow();

      
      $header=new easyTable($this->instance, '%{5,15,26,18,10,8,8,10}','border:1;font-family:Trirong;font-size:7; font-style:B;bgcolor:#e0e0e0;');
      $header->easyCell('NO.', 'valign:M;align:C');
      $header->easyCell(utf8Th("<s 'font-style:B;font-size:5.5;>PO Number / NEED BY DATE</s>"), 'valign:M;align:C');
      $header->easyCell(utf8Th("Part Number / Part Name"), 'valign:M;align:C');
      $header->easyCell(utf8Th("Supplier Item"), 'valign:M;align:C');
      $header->easyCell(utf8Th("Qty จำนวนชิ้นงาน"), 'valign:M;align:C;colspan:3');
      $header->easyCell(utf8Th("Package"), 'valign:M;align:C');
      
      $header->printRow();
      $header->easyCell(utf8Th("ที่"), 'valign:M;align:C');
      $header->easyCell(utf8Th("หมายเลข PO"), 'valign:M;align:C');
      $header->easyCell(utf8Th("รหัสชิ้นงาน"), 'valign:M;align:C');
      $header->easyCell(utf8Th("รหัสชิ้นงานของซัพพลายออร์"), 'valign:M;align:C');
      $header->easyCell(utf8Th("แผนรับ"), 'valign:M;align:C');
      $header->easyCell(utf8Th("พขร. เช็ค	"), 'valign:M;align:C');
      $header->easyCell(utf8Th("W/H เช็ค"), 'valign:M;align:C');
      $header->easyCell(utf8Th("ชื่อบรรจุภัณฑ์"), 'valign:M;align:C');

      $header->printRow();
      $header->endTable(0);
      $this->instance->Code128(98.5,38.5,$this->headerData->pusChild,55,5); */

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
 $sql = "SELECT t1.Document_No,t1.Document_Type,t1.Material_Type,date_format(t1.Document_Datetime,'%d-%m-%Y %H:%i')Document_Datetime,t1.Document_Date,
 t1.Delivery_Start_Datetime,t1.Delivery_End_Datetime,t1.Delivery_Start_Date,t1.Delivery_End_Date,t1.Receiver_Name,
 t1.Delivery_Txt,t1.Status,t1.Remarks_Header,date_format(t1.Creation_Datetime,'%d-%m-%Y %H:%i')Creation_Datetime,
 t2.Qty,t2.Qty_Picking,t2.Remarks_Body,
 t3.Part_Number,t3.Part_Name,t3.UOM,t5.Location
 from tbl_nm_submat_order_header t1
 left join tbl_nm_submat_order_body t2 on t1.ID=t2.Order_header_ID
 left join tbl_partmaster t3 on t2.Part_ID=t3.ID
 left join tbl_suppliermaster t4 on t2.Supplier_ID=t4.ID
 left join tbl_storagemaster t5 on t3.Pick_Location_ID=t5.ID
 where t1.Document_No='$doctype' order by t2.ID";
 
 if($result = $mysqli->query($sql)) 
 { 
   if($result->num_rows>0)
   {
      $data = array();
      while($row1 = $result->fetch_object())
      {
        $data[] = $row1;
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

 $header=new easyTable($pdf, '%{5,20,35,20,20}','border:1;font-family:Trirong;font-size:8; font-style:B;');
 $numRow = 0;
 for($i=0,$len=count($data);$i<$len;$i++)
 {
   $rowObj = $data[$i];
  $header->easyCell(utf8Th(++$numRow), 'valign:M;align:C;');
  $header->easyCell(utf8Th($rowObj->Part_Number), 'valign:M;align:C;');
  $header->easyCell(utf8Th($rowObj->Part_Name), 'valign:M;align:C;');
  $header->easyCell(utf8Th($rowObj->Qty), 'valign:M;align:C;');
  $header->easyCell(utf8Th($rowObj->Location), 'valign:M;align:C;');
  $header->printRow();
 }
 $header->endTable(0);


 if($pdf->GetY()>258.3610326087)
  $pdf->AddPage();

 $header=new easyTable($pdf,3,'align:L;width:100%;border:0;font-family:Trirong;font-size:8; font-style:B;valign:T;');
 $header->rowStyle('min-height:10');
 $header->easyCell(utf8Th("Data Entry By"), 'valign:M;align:C;');
 $header->easyCell(utf8Th(""), 'valign:M;align:C;');
 $header->easyCell(utf8Th("Check By"), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th("................................................................................."), 'valign:M;align:C;');
 $header->easyCell(utf8Th(""), 'valign:M;align:C;');
 $header->easyCell(utf8Th("................................................................................."), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th("(...............................................................................)"), 'valign:M;align:C;');
 $header->easyCell(utf8Th(""), 'valign:M;align:C;');
 $header->easyCell(utf8Th("(...............................................................................)"), 'valign:M;align:C;');
 $header->printRow();
//  $header->easyCell(utf8Th("พนักงานขับรถ (Driver)"), 'valign:M;align:C;');
//  $header->easyCell(utf8Th("ผู้จัดส่งสินค้า (Supplier)"), 'valign:M;align:C;');
//  $header->easyCell(utf8Th("ผู้รับสินค้า (MAZDA)"), 'valign:M;align:C;');
//  $header->printRow();
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