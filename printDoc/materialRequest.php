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
 
/*  $doctype = 'ORD19110001';
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
      $header->easyCell('', 'img:images/GDJ_BMP.jpg, w30,h20;valign:T;align:R');
      $header->easyCell('GLONG DUANG JAI CO.,LTD'."\n".'336/11 MOO 7 Bowin Sriracha Chonburi 20230'."\n".
      'Phone +66(0) 38-110910-2,3804 1787-8'."\n".'Fax : +66(0) 38-110916'
      , 'valign:C;align:L');
      // 336/11 Moo. 7 T.Bowin A.Sriracha Chonburi 20230 Tel.038-110910-2 Fax.038-110916
      $header->printRow();
      $header->easyCell('Material Request', 'valign:M;align:C;colspan:2;font-size:10;border:TB');
      $header->printRow();
      $header->endTable(0);
      $this->instance->Code128(145.5,11.5,$v->Document_NO,55,5);
      $this->instance->SetFontSize(8);
      $this->instance->Text(155.5,20.5,wordwrap($v->Document_NO,1,' ', true));

/*       Document_NO
Trader_Code
Trader_Name
Remarks
Document_Date
Delivery_Date
Customer_Project */
      $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Document No :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Document_NO), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Customer Code :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Trader_Code), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Project :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Customer_Project), 'valign:M;align:L;');
      $header->printRow();
      $header->endTable(0);
      
      $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Document Date :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Document_Date), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Customer Name :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Trader_Name), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Delivery Date :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Delivery_Date), 'valign:M;align:L;');
      $header->printRow();
      $header->endTable(0);

      $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Remarks :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Remarks), 'valign:M;align:L;colspan:3');
      /* $header->easyCell(utf8Th(""), 'valign:M;align:R;');
      $header->easyCell(utf8Th(''), 'valign:M;align:L;'); */
      $header->easyCell(utf8Th("Request By :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Created_By_Name), 'valign:M;align:L;');
      $header->printRow();
      $header->endTable(0);
      
      // t4.Trader_Code,t4.Trader_Name,
  
      $header=new easyTable($this->instance, '%{5,20,35,20,20}','border:1;font-family:Trirong;font-size:8; font-style:B;');
      $header->easyCell(utf8Th("No."), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Item No"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Item Name"), 'valign:M;align:C;');
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
 $sql = "SELECT t1.Document_NO,t3.Item_Code,t3.Item_Name,t2.Qty Item_Qty,
 concat(t5.Part_No,' | ',t5.Part_Type,' | ',t5.GSDB_Code)Part_Data,t4.Qty Part_Qty,
 t6.Trader_Code,t6.Trader_Name,t1.Remarks,t1.Document_Date,t1.Delivery_Date,t1.Customer_Project,
 concat(t10.user_fName,' ',t10.user_lname)Created_By_Name
   from tbl_order_request_header t1 
   inner join tbl_order_request_body t2 on t1.ID=t2.Order_Request_Head_ID
   left join tbl_items_master t3 on t2.Item_ID=t3.ID
     left join tbl_order_request_detail t4 on t2.ID=t4.Order_Request_Body_ID
     left join tbl_parts_master t5 on t4.Part_ID=t5.ID
     left join tbl_traders t6 on t1.Trader_ID=t6.ID
     left join tbl_user t10 on t1.Created_By=t10.user_id
   where t1.Document_NO='$doctype' order by t3.Item_Code,t5.Part_No,t5.GSDB_Code,t5.Part_Type;";
 
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

 $header=new easyTable($pdf, '%{5,20,35,20,20}','border:1;font-family:Trirong;font-size:8;');
 $numRow = 0;
 $Item_Code = '';
 $Item_Code_Show = '';
 $totalQty = 0;
 $subQty = 0;
/*  Item_Code
Item_Qty
Part_Data
Part_Qty */
 for($i=0,$len=count($data);$i<$len;$i++)
 {
  ++$numRow;
   $rowObj = $data[$i];
   if($i==0)
   {
    $Item_Code = $rowObj->Item_Code;
    $Item_Code_Show = $rowObj->Item_Code;
   }
   else
   {
    if($Item_Code != $rowObj->Item_Code)
    {
      $Item_Code = $rowObj->Item_Code;
      $Item_Code_Show = $rowObj->Item_Code;
      subTotal($header,$subQty);
      $subQty = 0;
    }
    else
    {    
      $Item_Code_Show = '';
    }
   }
   $subQty += $rowObj->Part_Qty;
   $totalQty += $rowObj->Part_Qty;

  $header->easyCell(utf8Th($numRow), 'valign:M;align:C;');
  $header->easyCell(utf8Th($Item_Code_Show), 'valign:M;align:C;');
  $header->easyCell(utf8Th($rowObj->Part_Data), 'valign:M;align:C;');
  $header->easyCell(utf8Th($rowObj->Part_Qty), 'valign:M;align:C;');
  $header->easyCell(utf8Th(''), 'valign:M;align:C;');
  $header->printRow();
  }  
 subTotal($header,$subQty);
 grandTotal($header,$totalQty);
 $header->endTable(0);


 if($pdf->GetY()>258.3610326087)
  $pdf->AddPage();

 $header=new easyTable($pdf,3,'align:L;width:100%;border:0;font-family:Trirong;font-size:8; font-style:B;valign:T;');
 $header->rowStyle('min-height:10');
 $header->easyCell(utf8Th(""), 'valign:M;align:C;');
 $header->easyCell(utf8Th("ผู้เบิก"), 'valign:M;align:C;');
 $header->easyCell(utf8Th("ผู้จ่าย"), 'valign:M;align:C;');
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
  
function subTotal($header,$qty)
{
  $header->easyCell(utf8Th('Sub Total'), 'valign:M;align:R;colspan:3;font-style:B;');
  $header->easyCell(utf8Th($qty), 'valign:M;align:C;font-style:B;');
  $header->easyCell(utf8Th(''), 'valign:M;align:C;font-style:B;');
  $header->printRow();
}
function grandTotal($header,$qty)
{
  $header->easyCell(utf8Th('Grand Total'), 'valign:M;align:R;colspan:3;font-style:B;');
  $header->easyCell(utf8Th($qty), 'valign:M;align:C;font-style:B;');
  $header->easyCell(utf8Th(''), 'valign:M;align:C;font-style:B;');
  $header->printRow();
}
//###############################################
function utf8Th($v)
{
  return iconv( 'UTF-8','TIS-620//IGNORE',$v);
}

 
?>