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

/* $doctype = 'PO1910010001';
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
 $sql = "SELECT t1.PO_NO,date_format(t1.Document_Date,'%d/%m/%y') PO_Date,
 if(t1.Delivery_Start_Date=t1.Delivery_End_Date,date_format(t1.Delivery_Start_Date - INTERVAL 0 DAY,'%d/%m/%y'),
 concat(date_format(t1.Delivery_Start_Date - INTERVAL 0 DAY,'%d/%m/%y'),' - ',date_format(t1.Delivery_End_Date - INTERVAL 0 DAY,'%d/%m/%y'))) Delivery_Date,
 t1.Ship_To,t1.Remarks,t5.Trader_Code,t5.Trader_Name,t5.Telephone_Number,t1.Customer_Project,t5.Credit_Term,
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
 )Billing_Address,t21.Item_Grad,t21.Item_Flute,
 t4.Part_No,t21.Item_Code,t4.Project_Code,t21.Dimension_Length,t21.Dimension_Height,t21.Dimension_Width,
 t2.Qty,t2.Unit_Price,t2.Qty*t2.Unit_Price Amount,t1.Vat,
 group_concat(concat(t23.Item_Code,' , ',t23.Item_Name,' , ',t23.Item_Type,' , ',t22.Qty,' , ',t25.UOM_Code) order by t23.Item_Type separator ' | ') component_list,
 t26.user_fName_TH,t26.user_lname_TH,t26.user_fName,t26.user_lname,
 t26.user_lname_Phone,t21.Item_Name,t4.Supplier_Part_No
 from tbl_po_header t1
 left join tbl_po_body t2 on t1.ID=t2.PO_ID
 left join tbl_po_detail t3 on t2.ID=t3.PO_Body_ID
 left join tbl_parts_master t4 on t3.Part_ID=t4.ID
 left join tbl_traders t5 on t1.Trader_ID=t5.ID
 left join tbl_traders_address t6 on t5.Traders_Address_ID=t6.ID
 left join tbl_thailand t7 on t6.Thailand_ID=t7.ID
 left join tbl_items_master t21 on t2.Item_ID=t21.ID
 left join tbl_items_component t22 on t21.ID=t22.Item_Base
 left join tbl_items_master t23 on t22.Item_Component=t23.ID
 left join tbl_uom_master t24 on t21.UOM_ID=t24.ID
 left join tbl_uom_master t25 on t23.UOM_ID=t25.ID
 left join tbl_user t26 on t1.Created_By=t26.user_id
 where t1.PO_NO='$doctype'
 group by t3.PO_Body_ID,t2.Item_ID
 order by t3.PO_Body_ID,t4.Part_No;";
 
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
    $header->easyCell(' <s font-size:16;font-style:B;>PURCHASE ORDER</s>', 'colspan:2;align:C;border:T');
    $header->easyCell('', '', '');
    $header->printRow();  
    $header->endTable(0);

    $header = new easyTable($this->instance, '%{15,35,17,33}', 'border:0;font-family:THSarabun;font-size:10;');
    $header->easyCell('VENDOR CODE :', 'align:L;border:TL;font-style:B;');
    $header->easyCell($v->{'Trader_Code'}, 'align:L;border:TR;');
    $header->easyCell('PURCHASE ORDER NO. :', 'align:L;border:T;font-style:B;');
    $header->easyCell($v->{'PO_NO'}, 'align:L;border:TR;');
    $header->printRow();

    $header->easyCell('VENDOR NAME :', 'align:L;border:L;font-style:B;');
    $header->easyCell($v->{'Trader_Name'}, 'align:L;');
    $header->easyCell('PURCHASE ORDER DATE :', 'align:L;border:L;font-style:B;');
    $header->easyCell($v->{'PO_Date'}, 'align:L;border:R;');
    $header->printRow();

    $header->easyCell(strtoupper($v->{'Billing_Address'}), 'align:L;colspan:2;border:LR;');
    $header->easyCell('DELIVERY DATE :', 'align:L;border:L;font-style:B;');
    $header->easyCell($v->{'Delivery_Date'}, 'align:L;border:R;');
    $header->printRow();

    $header->easyCell('CONTACT :', 'align:L;border:L;font-style:B;');
    $header->easyCell($v->{'Telephone_Number'}, 'align:L;');
    $header->easyCell('CREDIT TERM :', 'align:L;border:L;font-style:B;');
    $header->easyCell($v->{'Credit_Term'}.' DAYS', 'align:L;border:R;');
    $header->printRow();
    
    $header->easyCell('', 'align:L;border:L;font-style:B;');
    $header->easyCell('', 'align:L;');
    $header->easyCell('PROJECT :', 'align:L;border:L;font-style:B;');
    $header->easyCell($v->{'Customer_Project'}, 'align:L;border:R;');
    $header->printRow();

    $header->easyCell('', 'align:L;border:L;font-style:B;');
    $header->easyCell('', 'align:L;');
    $header->easyCell('CONTACT :', 'align:L;border:L;font-style:B;');
    $header->easyCell(strtoupper('KHUN '.$v->{'user_fName'}.' '.$v->{'user_lname_Phone'}), 'align:L;border:R;');
    $header->printRow();
    $header->endTable(0);

  
    
 
    $headdetail = new easyTable(
      $this->instance,
      '{15,44,80,20,20,20,16,16,16,20,28,20,45}','width:300;border:1;font-family:THSarabun;font-size:11; font-style:B;'
    );
    $headdetail->easyCell(utf8Th('ITEM'), 'align:C;font-size:10; ');
    $headdetail->easyCell(utf8Th("ITEM CODE"), 'align:C');
    $headdetail->easyCell(utf8Th('DESCRIPTION'), 'align:C;');
    $headdetail->easyCell(utf8Th('DIMENSION (MM)'), 'align:C;colspan:3');
    $headdetail->easyCell(utf8Th('MATERIAL GRADE'), 'align:C;colspan:3;');
    $headdetail->easyCell(utf8Th('FLUTE'), 'align:C');    
    $headdetail->easyCell(utf8Th('QTY.'), 'align:C');
    $headdetail->easyCell(utf8Th('UNIT PRICE'), 'align:C');
    $headdetail->easyCell(utf8Th('AMOUNT'), 'align:C');
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
$detail = new easyTable($pdf, '{15,44,80,20,20,20,16,16,16,20,28,20,45}', 'width:300;border:1;font-family:THSarabun;font-size:10;');
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
/* t4.Part_No,t21.Item_Code,t4.Project_Code,t21.Dimension_Length,t21.Dimension_Height,t21.Dimension_Width,
t2.Qty,t2.Unit_Price,t2.Qty*t2.Unit_Price Amount */
while ($i <  $bodyLen) {
    if ($countrow > $pagebreak) {
        $pdf->AddPage();
        $countrow = 1;
      }
      
    $countrow++;
    $detail->easyCell(utf8Th($nn), 'align:C');
    $detail->easyCell(utf8Th($data[$i]->{'Supplier_Part_No'}), 'align:C');
    $detail->easyCell(utf8Th($data[$i]->{'Item_Name'}), 'align:C');
    $detail->easyCell(utf8Th(intval($data[$i]->{'Dimension_Length'})), 'align:C');
    $detail->easyCell(utf8Th(intval($data[$i]->{'Dimension_Width'})), 'align:C');
    $detail->easyCell(utf8Th(intval($data[$i]->{'Dimension_Height'})), 'align:C');
    $detail->easyCell(utf8Th($data[$i]->{'Item_Grad'}), 'align:C;colspan:3');
    /* $detail->easyCell(utf8Th(''), 'align:C');
    $detail->easyCell(utf8Th(''), 'align:C'); */
    $detail->easyCell(utf8Th($data[$i]->{'Item_Flute'}), 'align:C');
    
    /* $component_listAr = explode(' | ',$data[$i]->{'component_list'});
    $componentShow = array();
    for($c1=0,$c1Len=count($component_listAr);$c1<$c1Len;$c1++)
    {
      $componentAr = explode(' , ',$component_listAr[$c1]);
      if(count($componentAr) >= 0)
      {
        continue;
      }

      if($componentAr[2] == 'INSERT')
      {
        $componentShow[] = "I ".intval($componentAr[3]);
      }
      else if($componentAr[2] == 'PAD')
      {
        $componentShow[] = "P ".intval($componentAr[3]);
      }
      else if($componentAr[2] == 'ANGLE')
      {
        
        $componentShow[] = "A ".intval($componentAr[3]);
      }
    } */
        
    $detail->easyCell(number_format(utf8Th($data[$i]->{'Qty'}),2), 'align:R');
    $detail->easyCell(number_format(utf8Th($data[$i]->{'Unit_Price'}),2), 'align:R');    
    $detail->easyCell(number_format(utf8Th($data[$i]->{'Amount'}),2), 'align:R');
    $total += $data[$i]->{'Amount'};
    $detail->printRow();
    $i++;
    $nn++;
  }
$detail->endTable(0);

$vat = $total*($perCenVat/100);
$grantotal = (float)$total+$vat;
/* $total = $total;
$vat = $vat; */

$ft = new easyTable($pdf->instance, '%{45,29.1,13.4,12.5}', 'border:1;font-family:THSarabun;font-size:11;');
$ft->easyCell(utf8Th(''), 'valign:T;align:L;border:1;colspan:2;rowspan:2;font-style:B');
// $ft->easyCell(utf8Th(''), 'valign:T;align:L;border:1;');
$ft->easyCell(utf8Th('TOTAL'), 'valign:T;align:L;font-style:B;');
$ft->easyCell(utf8Th(number_format($total,2)), 'valign:T;align:R;font-style:B;');
$ft->printRow();
// $ft->easyCell(utf8Th(''), 'valign:T;align:L;border:1;colspan:2');
// $ft->easyCell(utf8Th(''), 'valign:T;align:L;border:1;');
// $ft->easyCell(utf8Th(''), 'valign:T;align:L;border:1;colspan:2');
// $ft->easyCell(utf8Th(''), 'valign:T;align:L;border:1;');

$ft->easyCell(utf8Th("VAT ".intval($perCenVat)."%"), 'valign:T;align:L;font-style:B;');
$ft->easyCell(utf8Th(number_format($vat,2)), 'valign:T;align:R;font-style:B;');
$ft->printRow();

$ft->easyCell(strtoupper(Currency::bahtEng($grantotal)), 'valign:T;align:C;border:1;colspan:2;');
$ft->easyCell(utf8Th('GRAND TOTAL'), 'valign:T;align:L;font-style:B');
$ft->easyCell(utf8Th(number_format($grantotal,2)), 'valign:T;align:R;font-style:B');
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
/* $ft = new easyTable($pdf->instance, '%{40,30,30}', 'border:1;font-family:THSarabun;font-size:11;');
$ft->rowStyle('min-height:7');
$ft->easyCell(utf8Th('เงื่อนใขอื่นๆ'), 'valign:T;align:L;rowspan:4;');
$ft->easyCell(utf8Th('ผู้จัดทำ'), 'valign:T;align:C;rowspan:2;');
$ft->easyCell(utf8Th('ผู้มีอำนาจลงนาม'), 'valign:T;align:L;rowspan:3;');
$ft->printRow();
$ft->rowStyle('min-height:7');
$ft->printRow();
$ft->rowStyle('min-height:7');
$ft->easyCell(utf8Th('ผู้ตรวจสอบ'), 'valign:T;align:C;rowspan:2;');

$ft->printRow();
$ft->rowStyle('min-height:7');
$ft->easyCell(utf8Th("ผู้มีอำนาจลงนาม"), 'valign:T;align:C;');
$ft->printRow();
$ft->endTable(0); */



 /* $ft = new easyTable($pdf->instance, '%{100}', 'border:0;font-family:THSarabun;font-size:11;');
  $ft->easyCell(utf8Th('ราคานี้ไม่รวมภาษีมูลค่าเพิ่ม 7% (Excluded VAT 7%) 
    เงื่อนไขการช่าระหนี้ (Term of payment ) <b>เครดิต 60 วัน</b> ( Days.) 
    ระยะเวลาในการผลิต (Period of production) First Order Within 15 วัน (Days.)'), 'valign:T;align:L;');
  $ft->printRow();
  $ft->easyCell(utf8Th('
    บริษัทฯ หวังว่าจะได้รับการพิจารณาจากท่าน จึงขอขอบคุณมา ณ โอกาสนี้ 
    We thank you for kind support and looking forward to hearing from you.Yours sincerely'), 'valign:T;align:L;');
  $ft->printRow();
  $ft->endTable(0); */
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
 