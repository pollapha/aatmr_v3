<?php

include 'fpdf.php';
include 'exfpdf.php';
include 'PDF_Code128.php';
include 'easyTable.php';

include('../php/connection.php');
include('../phplib/Currency.php');


error_reporting(E_ALL);
ini_set('display_errors', 1);

if(!isset($_REQUEST['printerName']) || !isset($_REQUEST['copy']) || !isset($_REQUEST['doctype'])
|| !isset($_REQUEST['printType']) || !isset($_REQUEST['warter']) )
   closeDBT($mysqli,2,'ข้อมูลไม่ถูกต้อง 1');
$printerName = checkTXT($mysqli,$_REQUEST['printerName']);
$copy = checkINT($mysqli,$_REQUEST['copy']);
$doctype = checkTXT($mysqli,$_REQUEST['doctype']);
$printType = checkTXT($mysqli,$_REQUEST['printType']);
$warter = checkTXT($mysqli,$_REQUEST['warter']);
$Customer = checkTXT($mysqli,urldecode($_REQUEST['Customer']),0);
$Period = checkTXT($mysqli,urldecode($_REQUEST['Period']),0);
$PeriodAr = getBetweenDate($Period);
$dateStart = $PeriodAr[0];
$dateEnd = $PeriodAr[1];

/* if(strlen($printerName) == 0 || strlen($doctype) == 0 || strlen($printType) == 0 || strlen($warter) == 0 || $copy == 0) 
   closeDBT($mysqli,2,'ข้อมูลไม่ถูกต้อง 2'); */

if($printerName == 'NO_PRINT' && $printType == 'F')
{
   echo '{"ch":2,"data":"ไม่สามารถปริ้นได้เนื่องจากคุณไม่ได้เลือกปริ้นเตอร์"}';
   exit();
}

/* $doctype = 'CLC19070200002';
$copy = 1;
$printType = 'I';
$printerName = '1401';
$warter = 'NO'; */

$tbody = array();
 $rowCount = 0;
 $printDate = '';
 $cbm = 0;
 $totalBox = 0;
 $numPallet = 0;
 $sumLine = 0;
 $sumQty = 0;
 $sql = "SELECT s1.*,s2.Customer_Part_No
 from(SELECT t1.Customer_Project,t1.Document_NO,date_format(t1.Invoice_Date,'%d/%m/%Y')Document_Date,
  t1.DN_NO,date_format(t1.DN_Date,'%d/%m/%Y')DN_Date,t1.Invoice_NO,date_format(t1.Invoice_Date,'%d/%m/%y')Invoice_Date,
  t1.Remarks,t1.Transaction_Type,t1.Status,t1.Invoice_Txt,
  round(sum(t2.Qty)*-1) Qty,t2.Unit_Price,round(sum(t2.Qty)*t2.Unit_Price*-1,2) Amount,
  t4.Trader_Code,t4.Trader_Name,t4.Tax_ID,t4.Credit_Term,t4.Telephone_Number,t4.Branch,
  t5.Company_Code,t5.Company_Name,t5.Tax_ID Company_Tax_ID,
  t6.Project_Code Project,t2.Item_ID,
  t7.PO_NO,date_format(t7.Document_Date,'%d/%m/%Y')PO_Date,
  t8.Item_Code,t8.Item_Name,
  t9.UOM_Code UOM,
  concat(t10.user_fName,' ',t10.user_lname)Created_By_Name,
  concat(t11.user_fName,' ',t11.user_lname)Updated_By_Name,
  concat(t12.user_fName,' ',t12.user_lname)Confirm_By_Name,
  date_format(t1.Creation_DateTime,'%d/%m/%Y %H:%i')Creation_DateTime,
  ifnull(date_format(t1.Last_Updated_DateTime,'%d/%m/%Y %H:%i'),'')Last_Updated_DateTime,
  ifnull(date_format(t1.Confirm_DateTime,'%d/%m/%Y %H:%i'),'')Confirm_DateTime,
  concat(
  if(length(t13.Building_or_Village)>0,concat('หมู่บ้าน/อาคาร',t13.Building_or_Village,' '),''),
  if(length(t13.Room_Number)>0,concat('ห้อง',t13.Room_Number,' '),''),
  if(length(t13.Floor)>0,concat('ชั้น ',t13.Floor,' '),''),
  if(length(t13.Number)>0,concat('',t13.Number,' '),''),
  if(length(t13.Village_No)>0,concat('M.',t13.Village_No,' '),''),
  if(length(t13.Alley_or_Lane)>0,concat('S. ',t13.Alley_or_Lane,' '),''),
  if(length(t13.Road)>0,concat('Road ',t13.Road,' '),''),
  concat('T.',t14.District_en,' '),
  concat('A.',t14.Amphoe_en,' '),
  concat('',t14.Province_en,' '),
  concat('',t14.Zipcode)
  )Billing_Address,
  t13.Village_No,
  t13.Number,
 t14.District_en,
 t14.Amphoe_en,
 t14.Province_en,
 t14.Zipcode
  from tbl_transaction_header t1
  left join tbl_transaction_body t2 on t1.ID=t2.Transaction_ID
  left join tbl_traders t4 on t1.Trader_ID=t4.ID
  left join tbl_companys t5 on t1.Company_ID=t5.ID
  left join tbl_projects t6 on t1.Project_ID=t6.ID
  left join tbl_po_header t7 on t2.PO_ID=t7.ID
  left join tbl_items_master t8 on t2.Item_ID=t8.ID
  left join tbl_uom_master t9 on t8.UOM_ID=t9.ID
  left join tbl_user t10 on t1.Created_By=t10.user_id
  left join tbl_user t11 on t1.Updated_By=t11.user_id 
  left join tbl_user t12 on t1.Confirm_By=t12.user_id
  left join tbl_traders_address t13 on t4.Traders_Address_ID=t13.ID
  left join tbl_thailand t14 on t13.Thailand_ID=t14.ID
  where t1.Document_Date between '$dateStart' and '$dateEnd' and
  t1.Transaction_Type='SOLD' and t1.Invoice_NO='' and t1.Status='CONFIRMED' and t2.Status='APPLY'
  group by t1.Invoice_NO,t1.Customer_Project,t2.Item_ID,t2.Unit_Price) s1
  left join
  (SELECT t1.Document_NO,t4.Customer_Part_No,t2.Item_ID,t2.Unit_Price
  from tbl_transaction_header t1
  left join tbl_transaction_body t2 on t1.ID=t2.Transaction_ID
  left join tbl_transaction_detail t3 on t2.ID=t3.Transaction_Body_ID
  left join tbl_parts_master t4 on t3.Part_ID=t4.ID
  where t1.Document_Date between '$dateStart' and '$dateEnd' and
  t1.Transaction_Type='SOLD' and t1.Invoice_NO='' and t1.Status='CONFIRMED' and t2.Status='APPLY'
  group by t1.Invoice_NO,t1.Customer_Project,t2.Item_ID,t2.Unit_Price)s2 
  on s1.Document_NO=s2.Document_NO and s1.Item_ID=s2.Item_ID and s1.Unit_Price=s2.Unit_Price;"; 

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

  
  /* $ardetail = array(
  array("1",22,18),
  array("2",15,13),
  ); */
// var_dump($ardetail);exit();

class PDF extends PDF_Code128
{
  function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
  {
    parent::__construct($orientation, $unit, $format);
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
    $header = new easyTable($this->instance, '%{70,30}', 'border:0;font-family:Trirong;font-size:8;');
    $addreass = "336/11 MOO.7 \nBOWIN, SRIRACHA \nCHONBURI 20230  \nTEL:+66 33-135 018  FAX: +66 33-135 018";
    $tax = "TAX ID ".$v->{'Company_Tax_ID'};
    $page = 'Page ' . $this->instance->PageNo() . ' of {nb}';
    $header->easyCell(utf8Th("<s font-size:12;font-style:B;>GLONG DUANG JAI CO.,LTD</s>\n$addreass\n$tax\nHEADOFFICE"), 'align:L;');
    $header->easyCell($page, 'img:images/GDJ_BMP.jpg, w30;align:R;valign:B;', '');
    $header->printRow();
    $header->easyCell('<s font-size:12;font-style:B;>TAX INVOICE</s>', 'colspan:2;align:C;border:T');
    $header->printRow();  
    $header->endTable(0);


    $header = new easyTable($this->instance, '%{13,56,13,18}', 'border:0;font-family:Trirong;font-size:8;');
    $header->easyCell('Customer Code :', 'align:L;');
    $header->easyCell($v->{'Trader_Code'}, 'align:L;font-style:B;font-size:8;');
    $header->easyCell('Invoice Number :', 'align:L;');
    $header->easyCell($v->{'Invoice_NO'}, 'align:L;font-style:B;font-size:8;');
    $header->printRow();
    
    
    $header->easyCell('Customer Name :', 'align:L;');
    $header->easyCell($v->{'Trader_Name'}, 'align:L;');
    $header->easyCell('Invoice Date :', 'align:L;');
    $header->easyCell($v->{'Invoice_Date'}, 'align:L;font-style:B;font-size:8;');
    $header->printRow();
    
    
    $padding = 0.3;
    $Address = $v->{'Number'}.' MOO '.$v->{'Village_No'};
    $header->easyCell('Address :', 'align:L;');
    $header->easyCell(strtoupper($Address), 'colspan:3;');
    $header->printRow();

    $header->easyCell('', 'align:L;paddingY:0.4;');
    $header->easyCell(strtoupper('T.'.$v->{'District_en'}.' A.'.$v->{'Amphoe_en'}), 'colspan:3;valign:T;paddingY:0.4;');
    $header->printRow();
    $header->easyCell('', 'align:L;paddingY:0.4;');
    $header->easyCell(strtoupper($v->{'Province_en'}.' '.$v->{'Zipcode'}."\n"), 'colspan:3;valign:T;paddingY:0.4;');
    $header->printRow();
    $header->easyCell('', 'align:L;paddingY:0.4;');
    $header->easyCell(strtoupper($v->{'Branch'}."\n"), 'colspan:3;valign:T;paddingY:0.4;');
    $header->printRow();
    $header->easyCell('', 'align:L;paddingY:0.4;');
    $header->easyCell(strtoupper('TAX ID : '.$v->{'Tax_ID'}), 'colspan:3;valign:T;paddingY:0.4;');
    $header->printRow();
    $header->easyCell('Remarks : '.strtoupper($v->{'Invoice_Txt'}), 'colspan:4;valign:T;paddingY:0.4;');
    $header->printRow();
    $header->endTable(0);
  

    $headdetail = new easyTable(
      $this->instance,
      '%{5,15,48,8,12,12}',
      'width:300;border:1;font-family:Trirong;font-size:8; font-style:B;'
    );
    $headdetail->easyCell(utf8Th('Item'), 'align:C; ');
    $headdetail->easyCell(utf8Th('Product Code'), 'align:C;');
    $headdetail->easyCell(utf8Th('Product Description'), 'align:C;');
    $headdetail->easyCell(utf8Th('Quantity'), 'align:C;');
    $headdetail->easyCell(utf8Th('Unit Price'), 'align:C');
    $headdetail->easyCell(utf8Th('Amount'), 'align:C');
    $headdetail->printRow();
    $headdetail->endTable(0);
  }
  
  function Footer()
  {
   
    /* $this->SetXY(-20, 0);
    $this->SetFont('Trirong', 'I', 8);
    $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C'); */
  }
}
// FPDF('P','mm',array(100,150));
$pdf = new PDF('P','mm',array(8.5*25.4,11*25.4));
$pdf->SetMargins(10,0,10);
// $pdf->SetMargins(10,10,17);
/* $pdf->AddFont('THSarabun', '', 'THSarabun.php');
$pdf->AddFont('THSarabun', 'I', 'THSarabun Italic.php');
$pdf->AddFont('THSarabun', 'B', 'THSarabun Bold.php');
$pdf->AddFont('THSarabun', 'BI', 'THSarabun Bold Italic.php'); */

$pdf->AddFont('Trirong', '', 'tahoma.php');
// $pdf->AddFont('Trirong', 'I', 'Trirong-Italic.php');
$pdf->AddFont('Trirong', 'B', 'tahomabd.php');
// $pdf->AddFont('Trirong', 'BI', 'Trirong-BoldItalic.php');

$pdf->setInstance($pdf);
$pdf->setHeaderData($dataHeader);
$pdf->SetAutoPageBreak(4);
$pdf->AddPage();
$detail = new easyTable($pdf, '{15,60,30,20,20,20,16,16,16,20,34,28,20,45}', 'width:300;border:1;font-family:Trirong;font-size:8;');
$pagebreak = 20;
$i = 0;
$countrow = 1;
$nn = 1;
$bodyLen = count($data);
$totalQty = 0;
$discount = 0;
$perCenVat = 7;
$total = 0;
$grantotal = 0;
$headdetail = new easyTable(
  $pdf->instance,
  '%{5,15,48,8,12,12}',
  'width:300;border:0;font-family:Trirong;font-size:8;valign:B;'
);

$countrow = 0;
$project = '';
$subTotal = 0;
while ($i <  $bodyLen) {
    /* if ($countrow > $pagebreak) {
        $pdf->AddPage();
        $countrow = 0;
      } */
      ++$countrow;
    if($i == 0)
    {
      $project = $data[$i]->{'Customer_Project'};
      $headdetail->rowStyle('min-height:7');
      $headdetail->easyCell('', 'align:LR;border:LR;');
      $headdetail->easyCell('', 'align:LR;border:LR;');
      $headdetail->easyCell('PROJECT '.$project, 'align:L;border:LR;;font-style:B;');
      $headdetail->easyCell('', 'align:LR;border:LR;');
      $headdetail->easyCell('', 'align:LR;border:LR;');
      $headdetail->easyCell('', 'align:LR;border:LR;');
      $headdetail->printRow();
    }

    if($project != $data[$i]->{'Customer_Project'})
    {
      $headdetail->rowStyle('min-height:7');
      $headdetail->easyCell('', 'align:LR;border:LR;');              
      $headdetail->easyCell('', 'align:LR;border:LR;');
      $headdetail->easyCell('SUB TOTAL '.number_format($subTotal,2), 'align:R;border:LR;;font-style:B;');
      $headdetail->easyCell('', 'align:LR;border:LR;');
      $headdetail->easyCell('', 'align:LR;border:LR;');
      $headdetail->easyCell('', 'align:LR;border:LR;');
      $headdetail->printRow();
      $subTotal = 0;

      $project = $data[$i]->{'Customer_Project'};
      $headdetail->rowStyle('min-height:7');
      $headdetail->easyCell('', 'align:LR;border:LR;');      
      $headdetail->easyCell('', 'align:LR;border:LR;');
      $headdetail->easyCell('PROJECT '.$project, 'align:L;border:LR;;font-style:B;');
      $headdetail->easyCell('', 'align:LR;border:LR;');
      $headdetail->easyCell('', 'align:LR;border:LR;');
      $headdetail->easyCell('', 'align:LR;border:LR;');
      $headdetail->printRow();      
    }
    $headdetail->rowStyle('min-height:7');
    $headdetail->easyCell(utf8Th($countrow), 'align:C;border:LR');
    $headdetail->easyCell(utf8Th($data[$i]->{'Customer_Part_No'}), 'align:L;border:LR;');
    $headdetail->easyCell(utf8Th($data[$i]->{'Item_Name'}), 'align:L;');
    $headdetail->easyCell(utf8Th(number_format($data[$i]->{'Qty'},2)), 'align:C;border:LR');
    $headdetail->easyCell(utf8Th(number_format($data[$i]->{'Unit_Price'},2)), 'align:R;border:LR');
    $headdetail->easyCell(utf8Th(number_format($data[$i]->{'Amount'},2)), 'align:R;border:LR');
    $headdetail->printRow();
    $subTotal +=$data[$i]->{'Amount'};
    $total +=$data[$i]->{'Amount'};
    $totalQty +=$data[$i]->{'Qty'};
    $i++;
    $nn++;

    if($bodyLen == $i)
    {
      $headdetail->rowStyle('min-height:7');
      $headdetail->easyCell('', 'align:LR;border:LR;');              
      $headdetail->easyCell('', 'align:LR;border:LR;');
      $headdetail->easyCell('SUB TOTAL '.number_format($subTotal,2), 'align:R;border:LR;;font-style:B;');
      $headdetail->easyCell('', 'align:LR;border:LR;');
      $headdetail->easyCell('', 'align:LR;border:LR;');
      $headdetail->easyCell('', 'align:LR;border:LR;');
      $headdetail->printRow();
      $subTotal = 0;
    }

    if($pdf->GetY() > 250)
    {
      $pdf->Line($pdf->GetX(),$pdf->GetY(),206,$pdf->GetY());
      $pdf->AddPage();
    }
    else
    {
      $lastItem =  $bodyLen-$i;
      if($lastItem == 2 && $pdf->PageNo()>1 && $pdf->GetY() > 210)
      {
        $pdf->Line($pdf->GetX(),$pdf->GetY(),206,$pdf->GetY());
        $pdf->AddPage();
      }
    }

  }
  
  $fixRow = 15;
  if($countrow<$fixRow)
  {    
    /* for($i=0,$len=$fixRow-$countrow;$i<$len;$i++)
    {      
      $headdetail->rowStyle('min-height:7');
      $headdetail->easyCell('', 'align:C;border:LR');
      $headdetail->easyCell('', 'align:L;border:LR');
      $headdetail->easyCell('', 'align:L;border:LR');
      $headdetail->easyCell('', 'align:R;border:LR');
      $headdetail->easyCell('', 'align:R;border:LR');
      $headdetail->easyCell('', 'align:R;border:LR');
      $headdetail->printRow();
    }   */  
  }

  while($y = $pdf->GetY() < 210)
    {
      $headdetail->rowStyle('min-height:7');
      $headdetail->easyCell('', 'align:C;border:LR');
      $headdetail->easyCell('', 'align:L;border:LR');
      $headdetail->easyCell('', 'align:L;border:LR');
      $headdetail->easyCell('', 'align:R;border:LR');
      $headdetail->easyCell('', 'align:R;border:LR');
      $headdetail->easyCell('', 'align:R;border:LR');
      $headdetail->printRow();
    }
  
  $headdetail->rowStyle('min-height:7');
  $headdetail->easyCell('', 'align:C;border:1');
  $headdetail->easyCell('', 'align:L;border:1');
  $headdetail->easyCell('', 'align:L;border:1');
  $headdetail->easyCell(number_format($totalQty,2), 'align:C;border:1');
  $headdetail->easyCell('', 'align:R;border:1');
  $headdetail->easyCell('', 'align:R;border:1');
  $headdetail->printRow();

  $vat = $total*($perCenVat/100);
  $grantotal = (float)$total+$vat;
  $headdetail->easyCell(Currency::bahtEng($grantotal), 'valign:B;align:L;colspan:3;rowspan:3;font-style:B;font-size:8;border:LRT');
  $headdetail->easyCell('', 'border:LRT');
  $headdetail->easyCell(utf8Th('Total'), 'align:R;font-style:B;border:LRT');
  $headdetail->easyCell(utf8Th(number_format($total,2)), 'align:R;font-style:B;border:LRT');
  $headdetail->printRow();

  $headdetail->easyCell(utf8Th(''), '');
  $headdetail->easyCell(utf8Th('VAT '.$perCenVat.'%'), 'align:R;font-style:B;border:LR');
  $headdetail->easyCell(utf8Th(number_format($vat,2)), 'align:R;font-style:B;border:LR');
  $headdetail->printRow();

  $headdetail->easyCell(utf8Th(''), '');
  $headdetail->easyCell(utf8Th('Grand Total'), 'align:R;font-style:B;border:LR');
  $headdetail->easyCell(utf8Th(number_format($grantotal,2)), 'align:R;font-style:B;border:LR');
  $headdetail->printRow();
  
  $headdetail->easyCell('RECEIVED THE ABOVE MENTIONED GOODS IN GOOD ORDER AND CONDITION', 'valign:T;align:C;colspan:3;rowspan:2;border:LR;font-size:8;');
  $headdetail->easyCell('FOR GLONG DUANG JAI', 'valign:T;align:C;colspan:3;rowspan:2;font-size:8;border:LRT');
  $headdetail->printRow();
  $headdetail->rowStyle('min-height:18');
  $headdetail->easyCell('', 'colspan:3;border:TLR;valign:B;align:C;font-style:B;');
  $headdetail->easyCell('', 'colspan:3;valign:B;align:C;font-style:B;');
  $headdetail->printRow();
  $headdetail->easyCell("_________________________________\nCUSTOMER SIGNATURE", 'colspan:3;valign:T;align:C;border:BLR');
  $headdetail->easyCell("_________________________________\nAUTHORIZED SIGNATURE", 'colspan:3;valign:T;align:C;border:BLR');
  $headdetail->printRow();

$detail->endTable(0);

/* $headdetail = new easyTable(
  $pdf->instance,
  '%{5,15,48,8,12,12}',
  'width:300;border:1;font-family:Trirong;font-size:8;'
);
$headdetail->easyCell('CUSTOMER SIGNATURE', 'valign:T;align:C;colspan:3;border:LRB');
$headdetail->easyCell('AUTHORIZED SIGNATURE', 'valign:T;align:C;colspan:3;border:LRB');
$headdetail->printRow();

$headdetail->endTable(0); */


$type = 'I';
$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 3);
if ($type == 'F') 
{
  $pdf->Output($randomString . '.pdf', 'F');
  echo '{"ch":1,"data":"DONE"}';
} 
else 
{
  $pdf->Output($randomString . '.pdf', 'I');
}
function utf8Th($v)
{
  return iconv('UTF-8', 'TIS-620//TRANSLIT', $v);
}

function getBetweenDate($Period)
{
	$monthAr = array();
	for($m=1; $m<=12; ++$m)
	{
	    $monthAr[date('F', mktime(0, 0, 0, $m, 1))] = $m;
	}
	
	$Period = explode(' ',$Period);
	$dayAr = explode('-',$Period[0]);
	$startDay = $dayAr[0];
	$endDay = $dayAr[1];
	$month = $monthAr[$Period[1]];
	$year = $Period[2];
	$startDate = join('-',array($year,$month,$startDay));
	$endDate = join('-',array($year,$month,$endDay));
	return array($startDate,$endDate);
}
?>