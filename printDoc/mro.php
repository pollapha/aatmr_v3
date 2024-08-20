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


if(strlen($printerName) == 0 || strlen($doctype) == 0 || strlen($printType) == 0 || strlen($warter) == 0 || $copy == 0) 
   closeDBT($mysqli,2,'ข้อมูลไม่ถูกต้อง 2');

if($printerName == 'NO_PRINT' && $printType == 'F')
{
   echo '{"ch":2,"data":"ไม่สามารถปริ้นได้เนื่องจากคุณไม่ได้เลือกปริ้นเตอร์"}';
   exit();
}

/* $doctype = 'MRO19120001';
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
 $sql = "SELECT t1.Document_NO PO_NO,date_format(t1.Document_Date,'%d/%m/%y') PO_Date,
 if(t1.Delivery_Start_Date=t1.Delivery_End_Date,date_format(t1.Delivery_Start_Date - INTERVAL 0 DAY,'%d/%m/%y'),
 concat(date_format(t1.Delivery_Start_Date - INTERVAL 0 DAY,'%d/%m/%y'),' - ',date_format(t1.Delivery_End_Date - INTERVAL 0 DAY,'%d/%m/%y'))) Delivery_Date,
 t1.Ship_To,t1.Remarks,t5.Trader_Code,t5.Trader_Name,t5.Telephone_Number,t5.Credit_Term,'' Customer_Project,
 concat(
 if(length(t6.Building_or_Village)>0,concat('หมู่บ้าน/อาคาร',t6.Building_or_Village,' '),''),
 if(length(t6.Room_Number)>0,concat('ห้อง',t6.Room_Number,' '),''),
 if(length(t6.Floor)>0,concat('ชั้น ',t6.Floor,' '),''),
 if(length(t6.Number)>0,concat('',t6.Number,' '),''),
 if(length(t6.Village_No)>0,concat('M.',t6.Village_No,' '),''),
 if(length(t6.Alley_or_Lane)>0,concat('S. ',t6.Alley_or_Lane,' '),''),
 if(length(t6.Road)>0,concat('Road ',t6.Road,' '),''),
 concat('T.',t7.District_en,' '),
 concat('A.',t7.Amphoe_en,' '),
 concat('',t7.Province_en,' '),
 concat('',t7.Zipcode)
 )Address,t5.Billing_Address,t5.Tax_ID,t2.Project,t5.Contact,
 t2.Qty,t2.Unit_Price,t2.Qty*t2.Unit_Price Amount,t1.Vat Vat,t2.Description,
 t26.user_fName_TH,t26.user_lname_TH,t26.user_fName,t26.user_lname,
 t26.user_lname_Phone
  from tbl_po_mro_header t1
 left join tbl_po_mro_body t2 on t1.ID=t2.PO_MRO_ID
 left join tbl_traders t5 on t1.Trader_ID=t5.ID
 left join tbl_traders_address t6 on t5.Traders_Address_ID=t6.ID
 left join tbl_thailand t7 on t6.Thailand_ID=t7.ID
 left join tbl_user t26 on t1.Created_By=t26.user_id
 where t1.Document_NO='$doctype'  ORDER BY t2.ID;";
 
 
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
    $header = new easyTable($this->instance, '%{70,30}', 'border:0;font-family:THSarabun;font-size:10;');
    $addreass = '336/11 MOO.7 T.BOWIN A.SRIRACHA CHONBURI 20230  TEL.033-135 018  FAX.033-135 018';
    $tax = "TAX ID 0205562019548";    
    $header->easyCell(utf8Th("<s font-size:16;font-style:B;>GLONG DUANG JAI CO.,LTD</s>\n$addreass\n$tax"), 'align:L;');
    $header->easyCell('', 'img:images/GDJ_BMP.jpg, w30;align:R;valign:T;', '');
    $header->printRow();
    $header->easyCell(' <s font-size:16;font-style:B;>PURCHASE ORDER (MRO)</s>', 'colspan:2;align:C;border:T');
    $header->easyCell('', '', '');
    $header->printRow();  
    $header->endTable(0);

    $x=$this->instance->GetX();
    $y=$this->instance->GetY();

    $header=new easyTable($this->instance,'{25,70}', 'width:95;align:L{LL};border:0;font-family:THSarabun;font-size:10;');
    $header->easyCell('VENDOR CODE :', 'align:L;border:TL;font-style:B;');
    $header->easyCell($v->{'Trader_Code'}, 'align:;border:TR;');
    $header->printRow();
    $header->easyCell('VENDOR NAME :', 'align:L;border:L;font-style:B;');
    $header->easyCell($v->{'Trader_Name'}, 'align:L;border:R;');
    $header->printRow();
    $header->easyCell('ADDRESS :', 'align:L;border:L;font-style:B;');
    $header->easyCell($v->{'Billing_Address'}, 'align:L;border:R;');
    $header->printRow();
    $header->easyCell('CONTACT :', 'align:L;border:L;font-style:B;');
    $header->easyCell($v->{'Contact'}, 'align:L;');
    $header->printRow();
    $header->easyCell('TAX ID :', 'align:L;border:LB;font-style:B;');
    $header->easyCell($v->{'Tax_ID'}, 'align:L;border:B');
    $header->printRow();
    $header->endTable();
    $this->instance->SetY($y);
    $header=new easyTable($this->instance,'{35,60}', 'l-margin:100;width:95;align:L{LL};border:0;font-family:THSarabun;font-size:10;');
    $header->easyCell('PURCHASE ORDER NO. :', 'align:L;border:TL;font-style:B;');
    $header->easyCell($v->{'PO_NO'}, 'align:;border:TR;');
    $header->printRow();
    $header->easyCell('PURCHASE ORDER DATE :', 'align:L;border:L;font-style:B;');
    $header->easyCell($v->{'PO_Date'}, 'align:L;border:R;');
    $header->printRow();
    $header->easyCell('DELIVERY DATE :', 'align:L;border:L;font-style:B;');
    $header->easyCell($v->{'Delivery_Date'}, 'align:L;border:R');
    $header->printRow();
    $header->easyCell('CREDIT TERM :', 'align:L;border:L;font-style:B;');
    $header->easyCell($v->{'Credit_Term'}, 'align:L;;border:R');
    $header->printRow();
    $header->easyCell('CONTACT :', 'align:L;border:LB;font-style:B;');
    $header->easyCell(strtoupper('KHUN '.$v->{'user_fName'}.' '.$v->{'user_lname_Phone'}), 'align:L;border:BR');
    $header->printRow();
    $header->endTable();

    $headdetail = new easyTable(
      $this->instance,
      '%{5,52,10,13,10,10}','border:1;font-family:THSarabun;font-size:11; font-style:B;'
    );
    $headdetail->easyCell(utf8Th('ITEM'), 'align:C;font-size:10; ');
    $headdetail->easyCell(utf8Th('DESCRIPTION'), 'align:C;bgcolor:#E6E6FA');  
    $headdetail->easyCell(utf8Th('QTY.'), 'align:C');
    $headdetail->easyCell(utf8Th('UNIT PRICE'), 'align:C');
    $headdetail->easyCell(utf8Th('AMOUNT'), 'align:C');
    $headdetail->easyCell(utf8Th('PROJECT'), 'align:C');
    $headdetail->printRow();
    $headdetail->endTable(0);
  }

  function Footer()
  {
   
    $this->SetXY(-20, 0);
    $this->SetFont('THSarabun', 'I', 8);
    $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
  }
}

$pdf = new PDF('P');
$pdf->AddFont('THSarabun', '', 'THSarabun.php');
$pdf->AddFont('THSarabun', 'I', 'THSarabun Italic.php');
$pdf->AddFont('THSarabun', 'B', 'THSarabun Bold.php');
$pdf->AddFont('THSarabun', 'BI', 'THSarabun Bold Italic.php');
$pdf->setInstance($pdf);
$pdf->setHeaderData($dataHeader);
$pdf->SetAutoPageBreak(4);
$pdf->AddPage();
$docno = $dataHeader->{'PO_NO'};
$pdf->SetTitle($docno);
$detail = new easyTable($pdf, '%{5,52,10,13,10,10}', 'border:1;font-family:THSarabun;font-size:10;');
// $data = sizeof($ardetail);
// $data = 15;
$pagebreak = 20;
$i = 0;
$countrow = 1;
$nn = 1;
$bodyLen = count($data);
$discount = 0;
$perCenVat = $dataHeader->{'Vat'};
$total = 0;
$grantotal = 0;

$project = '';
$subTotal = 0;
$totalQty = 0;
    // Customer_Project
while ($i <  $bodyLen) 
{
    if ($countrow > $pagebreak) 
    {
      $pdf->AddPage();
    }      
    ++$countrow;
    if($i == 0)
    {
      $project = $data[$i]->{'Customer_Project'};
      /* $detail->easyCell(utf8Th('PROJECT '.$data[$i]->{'Customer_Project'}), 'align:L;colspan:13;font-style:B;border:LR');
      $detail->easyCell(utf8Th(''), 'align:R;border:LR');
      $detail->printRow(); */
    }

    if($project != $data[$i]->{'Customer_Project'})
    {
      /* $detail->easyCell(utf8Th('SUB TOTAL '.number_format($subTotal,2)), 'align:R;colspan:13;font-style:B;border:LRB');
      $detail->printRow();
      $subTotal = 0; */

      $project = $data[$i]->{'Customer_Project'};
      /* $detail->easyCell(utf8Th('PROJECT '.$data[$i]->{'Customer_Project'}), 'align:L;colspan:13;font-style:B;border:LR');
      $detail->printRow(); */
    }
    $detail->easyCell(utf8Th($nn), 'align:C;border:LR');
    
    $detail->easyCell(utf8Th($data[$i]->{'Description'}), 'align:L;border:LR');
    $detail->easyCell(number_format(utf8Th($data[$i]->{'Qty'}),2), 'align:C;border:LR');
    $detail->easyCell(number_format(utf8Th($data[$i]->{'Unit_Price'}),2), 'align:R;border:LR');
    $detail->easyCell(number_format(utf8Th($data[$i]->{'Amount'}),2), 'align:R;border:LR');
    $detail->easyCell(utf8Th($data[$i]->{'Project'}), 'align:R;border:LR');
    
    $subTotal +=$data[$i]->{'Amount'};
    $total += $data[$i]->{'Amount'};
    $totalQty += $data[$i]->{'Qty'};

    $detail->printRow();
    $i++;
    $nn++;

    /* if($bodyLen == $i)
    {
      $detail->easyCell(utf8Th('SUB TOTAL '.number_format($subTotal,2)), 'align:R;colspan:13;font-style:B;border:LRB');
      $detail->printRow();
      $subTotal = 0;
    } */
}
$fixRow = 20;
if($countrow<$fixRow)
{
  for($i=0,$len=$fixRow-$countrow;$i<$len;$i++)
  {
    $detail->rowStyle('min-height:7');
    $detail->easyCell(utf8Th(''), 'align:C;border:LR');
    $detail->easyCell(utf8Th(''), 'align:C;border:LR');
    $detail->easyCell(utf8Th(''), 'align:C;border:LR');
    $detail->easyCell(utf8Th(''), 'align:C;border:LR');
    $detail->easyCell(utf8Th(''), 'align:C;border:LR');
    $detail->easyCell(utf8Th(''), 'align:C;border:LR');
    $detail->easyCell(utf8Th(''), 'align:C;border:LR');
    $detail->easyCell(utf8Th(''), 'align:C;border:LR;colspan:3');
    $detail->easyCell(utf8Th(''), 'align:C;border:LR');
    $detail->easyCell(utf8Th(''), 'align:R;border:LR');
    $detail->easyCell(utf8Th(''), 'align:R;border:LR');    
    $detail->easyCell(utf8Th(''), 'align:R;border:LR');
    $detail->printRow();
  }    
}
$detail->endTable(0);

$vat = $total*($perCenVat/100);
$grantotal = (float)$total+$vat;
/* $total = $total;
$vat = $vat; */

$ft = new easyTable($pdf->instance, '%{57,10,13,10,10}', 'border:1;font-family:THSarabun;font-size:11;');
$ft->easyCell(utf8Th(''), 'valign:T;align:L;border:1;rowspan:2;font-style:B');
$ft->easyCell(utf8Th(number_format($totalQty,2)), 'valign:T;align:C;border:1;rowspan:2;font-style:B');
// $ft->easyCell(utf8Th(''), 'valign:T;align:L;border:1;');
$ft->easyCell(utf8Th('TOTAL'), 'valign:T;align:L;font-style:B;');
$ft->easyCell(utf8Th(number_format($total,2)), 'valign:T;align:R;font-style:B;');
$ft->easyCell(utf8Th(''), 'valign:T;align:L;font-style:B;');
$ft->printRow();
// $ft->easyCell(utf8Th(''), 'valign:T;align:L;border:1;colspan:2');
// $ft->easyCell(utf8Th(''), 'valign:T;align:L;border:1;');
// $ft->easyCell(utf8Th(''), 'valign:T;align:L;border:1;colspan:2');
// $ft->easyCell(utf8Th(''), 'valign:T;align:L;border:1;');

$ft->easyCell(utf8Th("VAT ".intval($perCenVat)."%"), 'valign:T;align:L;font-style:B;');
$ft->easyCell(utf8Th(number_format($vat,2)), 'valign:T;align:R;font-style:B;');
$ft->easyCell(utf8Th(''), 'valign:T;align:L;font-style:B;');
$ft->printRow();

$ft->easyCell(strtoupper(Currency::bahtEng($grantotal)), 'valign:T;align:C;border:1;colspan:2;');
$ft->easyCell(utf8Th('GRAND TOTAL'), 'valign:T;align:L;font-style:B');
$ft->easyCell(utf8Th(number_format($grantotal,2)), 'valign:T;align:R;font-style:B');
$ft->easyCell(utf8Th(''), 'valign:T;align:L;font-style:B;');
$ft->printRow();
$ft->endTable(1);

$ft = new easyTable($pdf->instance, '%{19,19,20,22,20}', 'border:1;font-family:THSarabun;font-size:11;font-style:B');
$ft->rowStyle('min-height:15');
$ft->easyCell(utf8Th(''), 'valign:T;align:C;');
$ft->easyCell(utf8Th(''), 'valign:T;align:C;');
$ft->easyCell(utf8Th(''), 'valign:T;align:C;');
$ft->easyCell(utf8Th(''), 'valign:T;align:C;');
$ft->easyCell(utf8Th(''), 'valign:T;align:C;');
$ft->printRow();
$ft->easyCell(utf8Th('Prepared by : PU'), 'valign:T;align:C;');
$ft->easyCell(utf8Th('Verify by : Owner MGR'), 'valign:T;align:C;');
$ft->easyCell(utf8Th('Verify by : PU MGR'), 'valign:T;align:C;');
$ft->easyCell(utf8Th('Verify by : Financial Controller'), 'valign:T;align:C;');
$ft->easyCell(utf8Th('Approved by : Ops. Director'), 'valign:T;align:C;');
$ft->printRow();
$ft->endTable(0);

$type = 'I';
if ($type == 'F') 
{
  $path = 'C:\\Backup_File_Print\\';
  // $path = 'D:\\printfile\\';
  $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 3);
  $pdf->Output($path . $docno . '-' . $randomString . '.pdf', 'F');
  echo '{"ch":1,"data":"DONE"}';
} 
else 
{
  $pdf->Output($docno . '.pdf', 'I');
}
function utf8Th($v)
{
  return iconv('UTF-8', 'TIS-620//TRANSLIT', $v);
}
 