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

/* $doctype = 'BPO2003180022';
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
 $sql = "SELECT 
 t1.BPO_NO,dateth(t1.BPO_Date)BPO_Date,t1.Remarks,
 dateth(t1.Start_Delivery_Plan)Start_Delivery_Plan,dateth(t1.End_Delivery_Plan)End_Delivery_Plan,
 if(t1.Start_Delivery_Plan=t1.End_Delivery_Plan,dateth(t1.Start_Delivery_Plan),
 concat(dateth(t1.Start_Delivery_Plan),' - ',dateth(t1.End_Delivery_Plan)))Delivery_Plan,
 t1.Status,t1.Creation_Date,t1.Creation_DateTime,
 t2.Qty_Ordered,t2.Qty_Confirm_Order,t2.Qty_Delivered,t2.Revision,t2.Qty_Ordered-t2.Qty_Delivered Qty_Balance,
 t25.Vendor_Code,t25.Vendor_Name,t25.Contact,t25.Billing_Address,
 t26.Warehouse_Code,
 t28.Cus_Code,t28.Cus_Name,t28.SNP,
 t29.Owner_Name,
 t27.Item_Code,t27.Item_Name,t27.Item_Flute,t27.Item_Grad,t27.Dimension_Height,t27.Dimension_Length,t27.Dimension_Width,
 concat(t30.user_fName,' ',t30.user_lname) Created_By,
 concat(t31.user_fName,' ',t31.user_lname) Updated_By
 from tbl_bpo t1
 left join tbl_bpo_line t2 on t1.BPO_ID=t2.BPO_ID
 left join tbl_vendor_master t25 on t1.Vendor_ID=t25.Vendor_ID
 left join tbl_warehouse_master t26 on t1.Warehouse_ID=t26.Warehouse_ID
 left join tbl_customer_items t28 on t2.Product_ID=t28.Cus_Item_ID
 left join tbl_items_master t27 on t28.Item_ID=t27.Item_ID
 left join tbl_product_owner t29 on t2.Owner_ID=t29.Owner_ID
 left join tbl_user t30 on t1.Created_By_ID=t30.user_id
 left join tbl_user t31 on t1.Updated_By_ID=t31.user_id
 where t1.BPO_NO='$doctype';";
 
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
    $tax = "TAX ID ";    
    $header->easyCell(utf8Th("<s font-size:16;font-style:B;>GLONG DUANG JAI CO.,LTD</s>\n$addreass"), 'align:L;');
    $header->easyCell('', 'img:images/GDJ_BMP.jpg, w30;align:R;valign:T;', '');
    $header->printRow();
    $header->easyCell(' <s font-size:16;font-style:B;>BLANKET PURCHASE ORDER</s>', 'colspan:2;align:C;border:T');
    $header->easyCell('', '', '');
    $header->printRow();  
    $header->endTable(0);

    $header = new easyTable($this->instance, '%{15,35,17,33}', 'border:0;font-family:THSarabun;font-size:10;');
    $header->easyCell('VENDOR CODE :', 'align:L;border:TL;font-style:B;');
    $header->easyCell($v->{'Vendor_Code'}, 'align:L;border:TR;');
    $header->easyCell('PURCHASE ORDER NO. :', 'align:L;border:T;font-style:B;');
    $header->easyCell($v->{'BPO_NO'}, 'align:L;border:TR;');
    $header->printRow();

    $header->easyCell('VENDOR NAME :', 'align:L;border:L;font-style:B;');
    $header->easyCell($v->{'Vendor_Name'}, 'align:L;');
    $header->easyCell('PURCHASE ORDER DATE :', 'align:L;border:L;font-style:B;');
    $header->easyCell($v->{'BPO_Date'}, 'align:L;border:R;');
    $header->printRow();

    $header->easyCell(strtoupper($v->{'Billing_Address'}), 'align:L;colspan:2;border:LR;');
    $header->easyCell('DELIVERY DATE :', 'align:L;border:L;font-style:B;');
    $header->easyCell($v->{'Delivery_Plan'}, 'align:L;border:R;');
    $header->printRow();

    $header->easyCell('CONTACT :', 'align:L;border:L;font-style:B;');
    $header->easyCell($v->{'Contact'}, 'align:L;');
    $header->easyCell('MRD NO :', 'align:L;border:L;font-style:B;');
    $header->easyCell('', 'align:L;border:R;');
    $header->printRow();

    $header->easyCell('', 'align:L;border:L;font-style:B;');
    $header->easyCell('', 'align:L;');
    $header->easyCell('SHIPPING ADDRESS :', 'align:L;border:L;font-style:B;');
    $header->easyCell($v->{'Warehouse_Code'}, 'align:L;border:R;');
    $header->printRow();

    $header->easyCell('', 'align:L;border:L;font-style:B;');
    $header->easyCell('', 'align:L;');
    $header->easyCell('CONTACT :', 'align:L;border:L;font-style:B;');
    $header->easyCell(strtoupper('KHUN '.$v->{'Created_By'}), 'align:L;border:R;');
    $header->printRow();
    $header->endTable(0);
 
    $headdetail = new easyTable(
      $this->instance,
      '{15,65,20,90,20,20,16,16,16,20,34,28}','width:300;border:1;font-family:THSarabun;font-size:11; font-style:B;'
    );
    $headdetail->easyCell(utf8Th('Item'), 'align:C;font-size:10; ');
    $headdetail->easyCell(utf8Th("Part NO."), 'align:C');
    $headdetail->easyCell(utf8Th('SNP'), 'align:C'); 
    $headdetail->easyCell(utf8Th('Package Code/ Description'), 'align:C;');
    $headdetail->easyCell(utf8Th('Dimension (MM)'), 'align:C;colspan:3');
    $headdetail->easyCell(utf8Th('Material Grade'), 'align:C;colspan:3;');
    $headdetail->easyCell(utf8Th('Flute'), 'align:C');
    $headdetail->easyCell(utf8Th('Qty'), 'align:C');
       
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
$docno = $dataHeader->{'BPO_NO'};
$pdf->SetTitle($docno);
$detail = new easyTable($pdf, '{15,65,20,90,20,20,16,16,16,20,34,28}', 'width:300;border:1;font-family:THSarabun;font-size:10;');
// $data = sizeof($ardetail);
// $data = 15;
$pagebreak = 20;
$i = 0;
$countrow = 1;
$nn = 1;
$bodyLen = count($data);
$discount = 0;
$perCenVat = 7;
$total = 0;
$grantotal = 0;
$itemCode = '';
$subTotal = 0;
$begin = 0;
$end = 0;
/* t4.Part_No,t21.Item_Code,t4.Project_Code,t21.Dimension_Length,t21.Dimension_Height,t21.Dimension_Width,
t2.Qty,t2.Unit_Price,t2.Qty*t2.Unit_Price Amount */
while ($i <  $bodyLen) 
{
    if ($countrow > $pagebreak)
    {
        $pdf->AddPage();
        $countrow = 1;
    }
    if($i == 0)
    {
      $itemCode = $data[$i]->{'Item_Code'};
    }

    if($itemCode != $data[$i]->{'Item_Code'})
    {
      $end = 1;
      $itemCode = $data[$i]->{'Item_Code'};
    }
    else
    {      
      $subTotal += $data[$i]->{'Qty_Ordered'};
    }
    
    $countrow++;
    if($end == 1)
    {
      $detail->easyCell(utf8Th(''), 'align:C');
      $detail->easyCell(utf8Th(''), 'align:C');
      $detail->easyCell('', 'align:R');
      $detail->easyCell(utf8Th(''));
      $detail->easyCell(utf8Th(''), 'align:C');
      $detail->easyCell(utf8Th(''), 'align:C');
      $detail->easyCell(utf8Th(''), 'align:C');
      $detail->easyCell(utf8Th(''), 'align:C;colspan:3');
      $detail->easyCell(utf8Th(''), 'align:C');    
      $detail->easyCell(number_format(utf8Th($subTotal),2), 'align:R;font-style:B;');
      $detail->printRow();
      $subTotal = 0;
      $subTotal += $data[$i]->{'Qty_Ordered'};
      $end = 0;
    }
    $detail->easyCell(utf8Th($nn), 'align:C');
      $detail->easyCell(utf8Th($data[$i]->{'Cus_Code'}), 'align:C');
      $detail->easyCell($data[$i]->{'SNP'}, 'align:R');
      $detail->easyCell(utf8Th($data[$i]->{'Item_Code'}.' '.$data[$i]->{'Item_Name'}), 'align:C');
      $detail->easyCell(utf8Th(intval($data[$i]->{'Dimension_Length'})), 'align:C');
      $detail->easyCell(utf8Th(intval($data[$i]->{'Dimension_Width'})), 'align:C');
      $detail->easyCell(utf8Th(intval($data[$i]->{'Dimension_Height'})), 'align:C');
      $detail->easyCell(utf8Th($data[$i]->{'Item_Grad'}), 'align:C;colspan:3');
      $detail->easyCell(utf8Th($data[$i]->{'Item_Flute'}), 'align:C');    
      $detail->easyCell(number_format(utf8Th($data[$i]->{'Qty_Ordered'}),2), 'align:R');
      // $total += $data[$i]->{'Amount'};
      $total += 1;
      $detail->printRow();
    $i++;
    $nn++;
  }
  $detail->easyCell(utf8Th(''), 'align:C;');
      $detail->easyCell(utf8Th(''), 'align:C');
      $detail->easyCell('', 'align:R');
      $detail->easyCell(utf8Th(''));
      $detail->easyCell(utf8Th(''), 'align:C');
      $detail->easyCell(utf8Th(''), 'align:C');
      $detail->easyCell(utf8Th(''), 'align:C');
      $detail->easyCell(utf8Th(''), 'align:C;colspan:3');
      $detail->easyCell(utf8Th(''), 'align:C');    
      $detail->easyCell(number_format(utf8Th($subTotal),2), 'align:R;font-style:B;');
      $detail->printRow();
$detail->endTable(0);
$vat = $total*($perCenVat/100);
$grantotal = (float)$total+$vat;

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
 