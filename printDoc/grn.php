<?php
 include('../php/connection.php');

 
/*  if(!isset($_REQUEST['printerName']) || !isset($_REQUEST['copy']) || !isset($_REQUEST['doctype'])
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
 } */
 
 $doctype = 'GRN2003200019';
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
      $header->easyCell('', 'img:images/GDJ_BMP.jpg, w30,h20;valign:T;align:R');
      $header->easyCell('GLONG DUANG JAI CO.,LTD'."\n".'336/11 MOO 7 Bowin Sriracha Chonburi 20230'."\n".
      'Phone +66(0) 38-110910-2,3804 1787-8'."\n".'Fax : +66(0) 38-110916'
      , 'valign:C;align:L');
      // 336/11 Moo. 7 T.Bowin A.Sriracha Chonburi 20230 Tel.038-110910-2 Fax.038-110916
      $header->printRow();
      $header->easyCell('GOODS RECEIPT NOTE', 'valign:M;align:C;colspan:2;font-size:10;border:TB');
      $header->printRow();
      $header->endTable(0);
      $this->instance->Code128(145.5,11.5,$v->Document_NO,55,5);
      $this->instance->SetFontSize(8);
      $this->instance->Text(155.5,20.5,wordwrap($v->Document_NO,1,' ', true));

      $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Document No :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Document_NO), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Vendor Code"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Vendor_Code), 'valign:M;align:L;');
      $header->easyCell(utf8Th("DN No :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->DN_NO), 'valign:M;align:L;');
      $header->printRow();
      $header->endTable(0);
      
      $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Document Date :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Document_Date), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Vendor Name"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Vendor_Name), 'valign:M;align:L;');
      $header->easyCell(utf8Th("DN Date :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->DN_Date), 'valign:M;align:L;');
      $header->printRow();
      $header->endTable(0);
            
      $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Remarks :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Remarks), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Project :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Warehouse_Code), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Replen NO :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->RPN_NO), 'valign:M;align:L;');
      $header->printRow();
      $header->endTable(0);
      
      // t4.Trader_Code,t4.Trader_Name,
  
      $header=new easyTable($this->instance, '%{5,35,35,15,10}','border:1;font-family:Trirong;font-size:8; font-style:B;');
      $header->easyCell(utf8Th("No."), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Item No"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Item Name"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Qty"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("UOM"), 'valign:M;align:C;');
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
 $sql = "SELECT t32.Document_NO,dateth(t32.Document_Date)Document_Date,t32.DN_NO,dateth(t32.DN_Date)DN_Date,
 t32.Remarks,t32.Status,t32.Transaction_Type,t32.Remarks,
 datetimeth(t32.Creation_DateTime)Creation_DateTime,datetimeth(t32.Last_Updated_DateTime)Last_Updated_DateTime,
 t25.Vendor_Code,t25.Vendor_Name,t25.Vendor_Name_TH,t25.Billing_Address,t25.Contact,
 t35.Company_Code,t35.Company_Name,
 t34.RPN_NO,dateth(t34.RPN_Date)RPN_Date,
 t33.Qty,t33.Status,
 t26.Warehouse_Code,
 t28.Cus_Code,t28.Cus_Name,
 t27.Item_Code,t27.Item_Name,t27.Item_Flute,t27.Item_Grad,t27.Dimension_Height,t27.Dimension_Length,t27.Dimension_Width,t27.UOM
 from
 tbl_transaction_inbound t32
 left join tbl_transaction_inbound_line t33 on t32.Transaction_In_ID=t33.Transaction_In_ID
 left join tbl_customer_items t28 on t33.Product_ID=t28.Cus_Item_ID
 left join tbl_items_master t27 on t28.Item_ID=t27.Item_ID
 left join tbl_warehouse_master t26 on t33.Warehouse_ID=t26.Warehouse_ID
 left join tbl_bpo_detail t3 on t33.BPO_Detail_ID=t3.BPO_Detail_ID
 left join tbl_vendor_order t34 on t32.Vendor_Order_ID=t34.Vendor_Order_ID
 left join tbl_companys t35 on t32.Company_ID=t35.Company_ID
 left join tbl_vendor_master t25 on t32.Vendor_ID=t25.Vendor_ID
 left join tbl_user t30 on t32.Created_By_ID=t30.user_id
 left join tbl_user t31 on t32.Updated_By_ID=t31.user_id
 where t32.Document_NO='$doctype' and t33.Status='ACTIVE';";
 
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

 $header=new easyTable($pdf, '%{5,35,35,15,10}','border:1;font-family:Trirong;font-size:8; font-style:B;');
 $numRow = 0;
 $Cus_Code = '';
 $totalQty = 0;
 $totalQtyReturn = 0;
 for($i=0,$len=count($data);$i<$len;$i++)
 {
   $rowObj = $data[$i];
   if($Cus_Code != $rowObj->Cus_Code)
   {
    $Cus_Code = $rowObj->Cus_Code;
    if($numRow >0)
    {      
      subTotal($header,$totalQtyReturn,$totalQty);
      $totalQty = 0;
      $totalQtyReturn = 0;
    }
   }
  $header->easyCell(utf8Th(++$numRow), 'valign:M;align:C;');
  $header->easyCell(utf8Th($rowObj->Cus_Code.'('.$rowObj->Item_Code.')'), 'valign:M;align:L;');
  $header->easyCell(utf8Th($rowObj->Cus_Name), 'valign:M;align:L;');
  $header->easyCell(utf8Th($rowObj->Qty), 'valign:M;align:R;');
  $header->easyCell(utf8Th($rowObj->UOM), 'valign:M;align:L;');
  $header->printRow();
/*   Cus_Code
  Cus_Name
  Item_Code
  Item_Name */
  if($rowObj->Status == 'RETURN')
  {
    $totalQtyReturn += $rowObj->Qty;
  }
  else if($rowObj->Status == 'APPLY')
  {
    $totalQty += $rowObj->Qty;
  }

 }
 subTotal($header,$totalQtyReturn,$totalQty);
 $header->endTable(0);


 if($pdf->GetY()>258.3610326087)
  $pdf->AddPage();

 $header=new easyTable($pdf,3,'align:L;width:100%;border:0;font-family:Trirong;font-size:8; font-style:B;valign:T;');
 $header->rowStyle('min-height:10');
 $header->easyCell(utf8Th(""), 'valign:M;align:C;');
 $header->easyCell(utf8Th("Check By"), 'valign:M;align:C;');
 $header->easyCell(utf8Th("Receiver By"), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th(""), 'valign:M;align:C;');
 $header->easyCell(utf8Th("................................................................................."), 'valign:M;align:C;');
 $header->easyCell(utf8Th("................................................................................."), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th(""), 'valign:M;align:C;');
 $header->easyCell(utf8Th("(...............................................................................)"), 'valign:M;align:C;');
 $header->easyCell(utf8Th("(...............................................................................)"), 'valign:M;align:C;');
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

function subTotal($header,$totalQtyReturn,$totalQty)
{
  /* $header->easyCell(utf8Th('Goods Return'), 'valign:M;align:R;colspan:3');
  $header->easyCell(utf8Th($totalQtyReturn), 'valign:M;align:R;');
  $header->easyCell(utf8Th(''), 'valign:M;align:C;');
  $header->printRow(); */

  $header->easyCell(utf8Th('Total Qty'), 'valign:M;align:R;colspan:3');
  $header->easyCell(utf8Th($totalQty), 'valign:M;align:R;');
  $header->easyCell(utf8Th(''), 'valign:M;align:C;');
  $header->printRow();
}

//###############################################
function utf8Th($v)
{
  return iconv( 'UTF-8','TIS-620//IGNORE',$v);
}

 
?>