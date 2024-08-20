<?php
 include('../php/connection.php');
//  include('../php/nm_common.php');
 
/*  if(!isset($_REQUEST['printerName']) || !isset($_REQUEST['copy']) || !isset($_REQUEST['doctype'])
  || !isset($_REQUEST['printType']) || !isset($_REQUEST['warter']) )
     closeDBT($mysqli,2,'ข้อมูลไม่ถูกต้อง 1');
 $printerName = checkTXT($mysqli,$_REQUEST['printerName']);
 $copy = checkINT($mysqli,$_REQUEST['copy']);
 $doctype = checkTXT($mysqli,$_REQUEST['doctype']);
 $printType = checkTXT($mysqli,$_REQUEST['printType']);
 $warter = checkTXT($mysqli,$_REQUEST['warter']); */

 
/*  if(strlen($printerName) == 0 || strlen($doctype) == 0 || strlen($printType) == 0 || strlen($warter) == 0 || $copy == 0) 
     closeDBT($mysqli,2,'ข้อมูลไม่ถูกต้อง 2');
 
 if($printerName == 'NO_PRINT' && $printType == 'F')
 {
     echo '{"ch":2,"data":"ไม่สามารถปริ้นได้เนื่องจากคุณไม่ได้เลือกปริ้นเตอร์"}';
     exit();
 } */
 
 $doctype = 'GTN1909030001';
 $copy = 1;
 $printType = 'I';
 $printerName = '1401';
 $warter = 'NO';

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
      $header->easyCell('GOODS TRANSFER NOTE', 'valign:M;align:C;colspan:2;font-size:10;border:TB');
      $header->printRow();
      $header->endTable(0);
      $this->instance->Code128(145.5,11.5,$v->Document_NO,55,5);
      $this->instance->SetFontSize(8);
      $this->instance->Text(155.5,20.5,wordwrap($v->Document_NO,1,' ', true));

      $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Document No :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Document_NO), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Request By :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th(''), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Order No :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th(''), 'valign:M;align:L;');
      $header->printRow();
      $header->endTable(0);
      
      $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Creation Date :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Creation_DateTime), 'valign:M;align:L;');
      $header->easyCell(utf8Th("To Department"), 'valign:M;align:R;');
      $header->easyCell(utf8Th(''), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Delivery Date :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th(''), 'valign:M;align:L;');
      $header->printRow();
      $header->endTable(0);

      $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Remarks :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th(''), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Material Type :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th(''), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Receiver Name :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th(''), 'valign:M;align:L;');
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
 $sql = "SELECT t1.Document_NO,date_format(t1.Document_Date,'%d/%m/%Y')Document_Date,
 t1.DN_NO,date_format(t1.DN_Date,'%d/%m/%Y')DN_Date,t1.Invoice_NO,date_format(t1.Invoice_Date,'%d/%m/%Y')Invoice_Date,
 t1.Remarks,t1.Transaction_Type,t1.Status,
 t2.Qty,t2.Unit_Price,t2.Qty*t2.Unit_Price Amount,
 t4.Trader_Code,t4.Trader_Name,
 t5.Company_Code,t5.Company_Name,
 t6.Project_Code Project,
 t7.PO_NO,date_format(t7.Document_Date,'%d/%m/%Y')PO_Date,
 t8.Item_Code,t8.Item_Name,
 t9.UOM_Code UOM,
 concat(t10.user_fName,' ',t10.user_lname)Created_By_Name,
 concat(t11.user_fName,' ',t11.user_lname)Updated_By_Name,
 concat(t12.user_fName,' ',t12.user_lname)Confirm_By_Name,
 date_format(t1.Creation_DateTime,'%d/%m/%Y %H:%i')Creation_DateTime,
 ifnull(date_format(t1.Last_Updated_DateTime,'%d/%m/%Y %H:%i'),'')Last_Updated_DateTime,
 ifnull(date_format(t1.Confirm_DateTime,'%d/%m/%Y %H:%i'),'')Confirm_DateTime
 from tbl_transaction_header t1
 left join tbl_transaction_body t2 on t1.ID=t2.Transaction_ID
 left join tbl_transaction_detail t3 on t2.ID=t3.Transaction_Body_ID
 left join tbl_traders t4 on t1.Trader_ID=t4.ID
 left join tbl_companys t5 on t1.Company_ID=t5.ID
 left join tbl_projects t6 on t1.Project_ID=t6.ID
 left join tbl_po_header t7 on t2.PO_ID=t7.ID
 left join tbl_items_master t8 on t2.Item_ID=t8.ID
 left join tbl_uom_master t9 on t8.UOM_ID=t9.ID
 left join tbl_user t10 on t1.Created_By=t10.user_id
 left join tbl_user t11 on t1.Updated_By=t11.user_id 
 left join tbl_user t12 on t1.Confirm_By=t12.user_id
where t1.Document_NO='$doctype';";
 
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
  $header->easyCell(utf8Th($rowObj->Item_Code), 'valign:M;align:C;');
  $header->easyCell(utf8Th($rowObj->Item_Name), 'valign:M;align:C;');
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