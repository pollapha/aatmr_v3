<?php
 include('../php/connection.php');
 
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
 
/*  $doctype = 'PO1908280005';
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
      $header=new easyTable($this->instance, '%{15,85}','border:0;font-family:Trirong;font-size:7; font-style:B;');
      $header->easyCell('', 'img:images/ttv-logo.gif, w30,h8;valign:T;align:R');
      $header->easyCell('TITAN-VNS AUTOLOGISTICS CO., LTD.'."\n".'49/63 MOO 5 TUNGSUNKLA SRIRACHA CHONBURI 20230'."\n".
      'Phone +66(0) 3840 1505-6,3804 1787-8'."\n".'Fax : +66(0) 3849 4300'
      , 'valign:T;align:L');
      $header->printRow();
      $header_doc = '';
      if($v->Work_Type == 'LOCAL PART')
      {
        $header_doc = "PICKUP SHEET (MILK RUN - ".$v->Work_Type." )";
      }
      else if($v->Work_Type == 'LOCAL SAP')
      {
        $header_doc = "PICKUP SHEET (MILK RUN - ".$v->Work_Type.")";
      }
      else if($v->Work_Type == 'CKD SAP')
      {
        $header_doc = "SHIPPING MANIFEST (MILK RUN)";
      }
      else if($v->Work_Type == 'PACKEGING RETURN')
      {
        $header_doc = "PICKUP SHEET (MILK RUN - ".$v->Work_Type." )";
      }
      $header->easyCell(utf8Th($header_doc), 'valign:M;align:C;colspan:2;font-size:10;border:TB');
      $header->printRow();
      $header->endTable(0);
      $this->instance->Code128(145.5,11.5,$v->Document_No,55,5);
      $this->instance->SetFontSize(8);
      $this->instance->Text(155.5,20.5,wordwrap($v->Document_No,1,' ', true));
      
      
      $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Document No :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Document_No), 'valign:M;align:L;');
      $header->easyCell(utf8Th("SupplierCode :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Supplier_Code), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Truck No :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Truck_License.' ('.$v->Truck_Type.')'), 'valign:M;align:L;');
      $header->printRow();

      $header->easyCell(utf8Th("Ship Date :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Plan_Date_Show), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Supplier Name"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Supplier_Name), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Driver Name :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Driver_Name), 'valign:M;align:L;');
      $header->printRow();

      $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Ship Time"), 'valign:M;align:R;');
      $header->easyCell(utf8Th('IN '.$v->Plan_Time_In_Show.' OUT '.$v->Plan_Time_Out_Show), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Work Shift :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->WorkShift), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Phone :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Phone), 'valign:M;align:L;');
      $header->printRow();
      
      
      $header->endTable(0);
    
      $header=new easyTable($this->instance, '%{5,15,15,25.5,6,6,10,6,5,6.5}','border:1;font-family:Trirong;font-size:8; font-style:B;');
      $header->easyCell(utf8Th(""), 'valign:M;align:C;colspan:6;border:0');
      $header->easyCell(utf8Th("Total Package"), 'valign:M;align:C;colspan:2');
      $header->easyCell(utf8Th("สถานะรับสินค้า"), 'valign:M;align:C;colspan:2');
      $header->printRow();

      $header->easyCell(utf8Th("No."), 'valign:M;align:C;');
      $header->easyCell(utf8Th("PO Number"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Part Number"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Part Name"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Qty"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("SNP"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Package"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Qty"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("ครบ"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("ไม่ครบ"), 'valign:M;align:C;');
      $header->printRow();

      
      $header->endTable(0);

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
 $sql = "SELECT 
 date_format(t2.Plan_Time_In,'%H:%i')Plan_Time_In_Show,date_format(t2.Actual_Time_In,'%H:%i')Actual_Time_In_Show,
 date_format(t2.Plan_Time_Out,'%H:%i')Plan_Time_Out_Show,date_format(t2.Actual_Time_Out,'%H:%i')Actual_Time_Out_Show,
 date_format(t2.Plan_Time_In,'%d-%m-%Y')Plan_Date_Show,
 date_format(t2.Plan_Time_In,'%H:%i')Plan_Time_In,
 date_format(t2.Plan_Time_Out,'%H:%i')Plan_Time_Out,
 t2.Status_Type,t2.Work_Type,t2.Transac_header_ID,t2.Remarks_Body,
 t2.Status_Body,t2.Supplier_ID,t2.Distance,t2.Unload_Point,
 t2.Document_No,t1.Run_Type,
 t1.Truck_License,t1.Truck_Type,t1.Driver_Name,t1.Phone,t1.Route,t1.WorkShift,
 t3.Supplier_Code,t3.Supplier_Name,t2.ID Transac_Body_ID,
 t4.PO_NO,t4.Qty,t4.SNP,t4.Package_Type,
 t5.Part_Number,t5.Part_Name
 from tbl_nm_milkrun_transaction_header t1
 left join tbl_nm_milkrun_transaction_body t2 on t1.ID=t2.Transac_header_ID
 left join tbl_nm_milkrun_suppliermaster t3 on t2.Supplier_ID=t3.ID
 inner join tbl_nm_milkrun_order t4 on t2.ID=t4.Transac_Body_ID
 left join tbl_partmaster t5 on t4.Part_ID=t5.ID
 where t2.Document_No='$doctype' and t1.Status_Header not in('PLANNING','CANCELED','CANCELED BY TTV') and 
 t2.Status_Body not in('PLANNING','CANCELED','CANCELED BY TTV') and t2.Status_Type='LOAD'
 order by t1.Document_No,t2.Status_TypeSort,t2.Actual_Time_In;";
 
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

 $header=new easyTable($pdf, '%{5,15,15,25.5,6,6,10,6,5,6.5}','border:1;font-family:Trirong;font-size:8; font-style:B;');
 $numRow = 0;
 $sum_Qty = 0;
 $package_Qty = 0;
 for($i=0,$len=count($data);$i<$len;$i++)
 {
    $rowObj = $data[$i];
    $header->easyCell(utf8Th(++$numRow), 'valign:M;align:C;');
    $header->easyCell(utf8Th($rowObj->PO_NO), 'valign:M;align:C;');
    $header->easyCell(utf8Th($rowObj->Part_Number), 'valign:M;align:C;');
    $header->easyCell(utf8Th($rowObj->Part_Name), 'valign:M;align:C;');
    $header->easyCell(utf8Th($rowObj->Qty), 'valign:M;align:C;');
    $header->easyCell(utf8Th($rowObj->SNP), 'valign:M;align:C;');
    $header->easyCell(utf8Th($rowObj->Package_Type), 'valign:M;align:C;');
    $header->easyCell(utf8Th(ceil($rowObj->Qty/$rowObj->SNP)), 'valign:M;align:C;');
    $header->easyCell(utf8Th(""), 'valign:M;align:C;');
    $header->easyCell(utf8Th(""), 'valign:M;align:C;');
    $header->printRow();
    $sum_Qty += $rowObj->Qty;
    $package_Qty += ceil($rowObj->Qty/$rowObj->SNP);
 }
 
  $header->easyCell(utf8Th("Total :"), 'valign:M;align:R;colspan:4;border:0');
  $header->easyCell(utf8Th($sum_Qty), 'valign:M;align:C;');
  $header->easyCell(utf8Th(""), 'valign:M;align:C;colspan:2;border:0');
  $header->easyCell(utf8Th($package_Qty), 'valign:M;align:C;');
  $header->easyCell(utf8Th(""), 'valign:M;align:C;colspan:2;border:0');
  $header->printRow();
  $header->endTable(0);


  $header=new easyTable($pdf, '%{38,10,13,13,13,13}','border:1;font-family:Trirong;font-size:8; font-style:B;');

  $header->easyCell(utf8Th("Remark :"), 'valign:M;align:L;');
  $header->easyCell(utf8Th("Packaging Type"), 'valign:M;align:C;colspan:5');
  $header->printRow();
  $header->easyCell(utf8Th(""), 'valign:M;align:C;');
  $header->easyCell(utf8Th("ยอดรับ"), 'valign:M;align:C;rowspan:2');
  $header->easyCell(utf8Th("กล่องพลาสติก"), 'valign:M;align:C;');
  $header->easyCell(utf8Th("กล่องลูกฟูก"), 'valign:M;align:C;');
  $header->easyCell(utf8Th("พาเลทพลาสติก"), 'valign:M;align:C;');
  $header->easyCell(utf8Th("แร็ค"), 'valign:M;align:C;');
  $header->printRow();

  $header->easyCell(utf8Th(""), 'valign:M;align:C;');
  $header->easyCell(utf8Th("PTB"), 'valign:M;align:C;');
  $header->easyCell(utf8Th("CBM"), 'valign:M;align:C;');
  $header->easyCell(utf8Th("PTP"), 'valign:M;align:C;');
  $header->easyCell(utf8Th("RACK"), 'valign:M;align:C;');
  $header->printRow();

  $header->easyCell(utf8Th(""), 'valign:M;align:C;');
  $header->easyCell(utf8Th("แผนรับ"), 'valign:M;align:C;');
  $header->easyCell(utf8Th(""), 'valign:M;align:C;');
  $header->easyCell(utf8Th(""), 'valign:M;align:C;');
  $header->easyCell(utf8Th(""), 'valign:M;align:C;');
  $header->easyCell(utf8Th(""), 'valign:M;align:C;');
  $header->printRow();

  $header->easyCell(utf8Th(""), 'valign:M;align:C;');
  $header->easyCell(utf8Th("รับจริง"), 'valign:M;align:C;');
  $header->easyCell(utf8Th(""), 'valign:M;align:C;');
  $header->easyCell(utf8Th(""), 'valign:M;align:C;');
  $header->easyCell(utf8Th(""), 'valign:M;align:C;');
  $header->easyCell(utf8Th(""), 'valign:M;align:C;');
  $header->printRow();
  $header->endTable(0);

 if($pdf->GetY()>258.3610326087)
  $pdf->AddPage();

  $header=new easyTable($pdf,3,'align:L;width:100%;border:0;font-family:Trirong;font-size:8; font-style:B;valign:T;');
  $header->rowStyle('min-height:10');
  if($dataHeader->Work_Type == 'LOCAL PART')
  {
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
    $header->easyCell(utf8Th("ผู้รับสินค้า (Receiver TTV-WH)"), 'valign:M;align:C;');
  }
  else if($dataHeader->Work_Type == 'LOCAL SAP')
  {
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
    $header->easyCell(utf8Th("ผู้จัดส่งสินค้า (TTV-WH)"), 'valign:M;align:C;');
    $header->easyCell(utf8Th("พนักงานขับรถ (Driver)"), 'valign:M;align:C;');
    $header->easyCell(utf8Th("ผู้รับสินค้า (Supplier)"), 'valign:M;align:C;');
  }
  else if($dataHeader->Work_Type == 'CKD SAP')
  {
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
    $header->easyCell(utf8Th("ผู้จัดส่งสินค้า (Supplier A)"), 'valign:M;align:C;');
    $header->easyCell(utf8Th("พนักงานขับรถ (Driver)"), 'valign:M;align:C;');
    $header->easyCell(utf8Th("ผู้รับสินค้า (Supplier B)"), 'valign:M;align:C;');
  }
  else if($dataHeader->Work_Type == 'PACKEGING RETURN')
  {
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
    $header->easyCell(utf8Th("ผู้จัดส่งสินค้า (TTV-WH)"), 'valign:M;align:C;');
    $header->easyCell(utf8Th("พนักงานขับรถ (Driver)"), 'valign:M;align:C;');
    $header->easyCell(utf8Th("ผู้รับสินค้า (Supplier)"), 'valign:M;align:C;');
  }
  $header->printRow();
  $header->endTable(5);

  $header=new easyTable($pdf,3,'align:L;width:100%;border:0;font-family:Trirong;font-size:8; font-style:B;valign:T;');
  $header->rowStyle('min-height:10');

    $header->easyCell(utf8Th("เลขที่เอกสารอินวอยซ์"), 'valign:M;align:R;');
    $header->easyCell(utf8Th("................................................................................."), 'valign:M;align:C;');
    $header->easyCell(utf8Th(""), 'valign:M;align:C;');
    $header->printRow();
    $header->easyCell(utf8Th(""), 'valign:M;align:C;');
    $header->easyCell(utf8Th("(...............................................................................)"), 'valign:M;align:C;');
    $header->easyCell(utf8Th(""), 'valign:M;align:C;');
    $header->printRow();
    $header->easyCell(utf8Th(""), 'valign:M;align:C;');
    $header->easyCell(utf8Th("ผู้รับอินวอยซ์ Return (Supplier)"), 'valign:M;align:C;');
    $header->easyCell(utf8Th(""), 'valign:M;align:C;');
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

function utf8Th($v)
{
  return iconv( 'UTF-8','TIS-620//IGNORE',$v);
}

 
?>