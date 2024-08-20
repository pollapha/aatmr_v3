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
 
/*  $doctype = 'MDN190123004';
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
      $header->easyCell('MATERIAL DELIVERY NOTE', 'valign:M;align:C;colspan:2;font-size:10;border:TB');
      $header->printRow();
      $header->endTable(0);
      $this->instance->Code128(145.5,11.5,$v->Document_No,55,5);
      $this->instance->SetFontSize(8);
      $this->instance->Text(155.5,20.5,wordwrap($v->Document_No,1,' ', true));

      $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Document No :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Document_No), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Request By :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Order_By), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Order No :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Order_NO), 'valign:M;align:L;');
      $header->printRow();
      $header->endTable(0);
      
      $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Creation Date :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Creation_Datetime), 'valign:M;align:L;');
      $header->easyCell(utf8Th("To Department"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Shop), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Delivery Date :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Order_Delivery), 'valign:M;align:L;');
      $header->printRow();
      $header->endTable(0);

      $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Remarks :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Remarks_Header), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Material Type :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Material_Type), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Receiver Name :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Order_Receiver), 'valign:M;align:L;');
      $header->printRow();
      $header->endTable(0);
      
      
  
      $header=new easyTable($this->instance, '%{5,20,35,20,20}','border:1;font-family:Trirong;font-size:8; font-style:B;');
      $header->easyCell(utf8Th("No."), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Material No"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Material Name"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Qty"), 'valign:M;align:C;');
      $header->easyCell(utf8Th(""), 'valign:M;align:C;');
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
 $sql = "SELECT t1.Document_No,t3.Part_Number,t3.Part_Name,t3.UOM,t1.Qty*-1 Qty,
 t5.Customs_Declaration_Number,t8.Dep_Name,date_format(t1.Creation_Date,'%d-%m-%y')Creation_Date,
 date_format(t1.Creation_Datetime,'%d-%m-%y %H:%i:%s')Creation_Datetime,Transaction_type,t1.Issued_Type,
 t6.Supplier_Code,t6.Supplier_Name,t2.Remarks_Header issueRemark,t9.Document_No Order_No,t9.Created_Name Order_By,
 t9.Document_Datetime Order_Date,t9.Remarks_Header Order_Remark,t9.Delivery_Txt,t10.Shop,t10.Material_Type,
 t1.Balance_Start,t1.Balance_End,date_format(t1.Document_Date,'%d-%m-%y')Document_Date,
 t1.Created_Name,date_format(t1.Creation_Datetime,'%d-%m-%Y %H:%i')Creation_Datetime,
 t1.Updated_Name,date_format(t1.Updated_Datetime,'%d-%m-%Y %H:%i')Updated_Datetime,
     '' Order_NO,'' Order_By,'' Order_NO,date_format(t5.Delivery_Date,'%d-%m-%Y') Order_Delivery,
     '' Remarks_Header,'' Order_Receiver
 from tbl_nm_submat_transaction t1
 left join tbl_nm_submat_issue_header t2 on t1.Issue_Header_ID=t2.ID
 inner join tbl_partmaster t3 on t1.Part_ID=t3.ID
 inner join tbl_nm_submat_has_po t4 on t1.ID=t4.Transaction_ID
 inner join tbl_nm_submat_po t5 on t4.PO_ID=t5.ID
 inner join tbl_suppliermaster t6 on t1.Supplier_ID=t6.ID
 left join tbl_nm_submat_order_header t9 on t2.Order_ID=t9.ID
 left join tbl_user t7 on t9.Created_By=t7.user_id
 left join tbl_department t8 on t7.user_dep=t8.ID
 left join tbl_nm_partmaster_detail t10 on t1.Part_ID=t10.Part_ID
     where t1.Document_No='$doctype' order by t2.ID;";
 
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
  $header->easyCell(utf8Th(''), 'valign:M;align:C;');
  $header->printRow();
 }
 $header->endTable(0);


 if($pdf->GetY()>258.3610326087)
  $pdf->AddPage();

 $header=new easyTable($pdf,3,'align:L;width:100%;border:0;font-family:Trirong;font-size:8; font-style:B;valign:T;');
 $header->rowStyle('min-height:10');
 $header->easyCell(utf8Th("Picking By"), 'valign:M;align:C;');
 $header->easyCell(utf8Th("Check By"), 'valign:M;align:C;');
 $header->easyCell(utf8Th("Receiver By"), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th("................................................................................."), 'valign:M;align:C;');
 $header->easyCell(utf8Th("................................................................................."), 'valign:M;align:C;');
 $header->easyCell(utf8Th("................................................................................."), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th("(...............................................................................)"), 'valign:M;align:C;');
 $header->easyCell(utf8Th("................................................................................."), 'valign:M;align:C;');
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