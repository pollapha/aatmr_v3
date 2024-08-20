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
 
/*  $doctype = '2019-01-30';
 $copy = 1;
 $printType = 'I';
 $printerName = '1401';
 $warter = 'NO'; */

 include 'fpdf.php';
 include 'exfpdf.php';
 include 'PDF_Code128.php';
 include 'easyTable.php';
 
 $sql = "SELECT 1
 from tbl_nm_milkrun_package_transaction t1
 left join tbl_nm_milkrun_packagemaster t2 on t1.Package_ID=t2.ID
 left join tbl_nm_milkrun_suppliermaster t3 on t1.Supplier_ID=t3.ID
 where t1.Document_Date='$doctype' and t1.Transaction_type in('OUT') and t2.Package_Type in('PTB','CPB','STR','PPL','WPL')";
 $re1 = sqlError($mysqli,__LINE__,$sql,0);
 if($re1->num_rows==0) closeDBT($mysqli,2,'ไม่พบข้อมูลวันที่ '.$doctype);

 $sql="SELECT Document_Date from tbl_nm_milkrun_package_tc_running where Document_Date='$doctype' limit 1";
 $re1 = sqlError($mysqli,__LINE__,$sql,0);
 if($re1->num_rows==0)
 {
  sqlError($mysqli,__LINE__,"INSERT INTO tbl_nm_milkrun_package_tc_running(Document_No,Document_Date)
  values(nm_FN_GenRuningNumber('milktunTcPackReturn',0),'$doctype')",0);
 }

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
      $header=new easyTable($this->instance, '%{5,95}','border:0;font-family:Trirong;font-size:7; font-style:B;');
      $header->easyCell('', 'img:images/tcLogo.png, w15,h15;valign:T;align:L');
      $header->easyCell(
        'TCZero Logistics (Thailand) Co.,Ltd'."\n".
        '122/1-2 Soi Chalongkrung tew Lamplatew'."\n".
        'Ladkrabang Bangkok 10520'."\n".
        'http://www.tanchong.com'
        , 'valign:T;align:L');
      $header->printRow();
      $header_doc = '';

      $header_doc = "Packaging Return Declaration";

      $header->easyCell(utf8Th($header_doc), 'valign:M;align:C;colspan:2;font-size:10;border:0');
      $header->printRow();
      $header->easyCell(utf8Th('รายการบรรจุภัณฑ์ผ่านเขตปลอดอากร (Free Zone Gate Pass)'), 'valign:M;align:C;colspan:2;font-size:10;border:0');
      $header->printRow();
      $header->endTable(1);
      
      $header=new easyTable($this->instance, '%{85,15}','border:0;font-family:Trirong;font-size:8; font-style:B;');
      $header->easyCell(utf8Th('วันที่ (Date) : '.$v->DocDate), 'valign:M;align:R;');
      $header->easyCell(utf8Th('เลขที่ : '.$v->TC), 'valign:M;align:R;');
      $header->printRow();
      $header->endTable(1);
     /*  $this->instance->Code128(232.5,11.5,$v->Document_No,55,5);
      $this->instance->SetFontSize(8);
      $this->instance->Text(242.5,20.5,wordwrap($v->Document_No,1,' ', true)); */

      $header=new easyTable($this->instance, '%{3,2,3.5,3.5,6.5,4.5,4,4,9.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5}','border:1;font-family:Trirong;font-size:5; font-style:B;');
      $header->easyCell(utf8Th(""), 'valign:M;align:C;colspan:10;border:0');
      $header->easyCell(utf8Th("Package Type / Quantity (Q'TY)"), 'valign:M;align:C;colspan:16');
      $header->printRow();

      $header->easyCell(utf8Th("Route \n No."), 'valign:M;align:C;rowspan:3');
      $header->easyCell(utf8Th("ลำดับ \n No."), 'valign:M;align:C;rowspan:3');
      $header->easyCell(utf8Th("Truck \n Type \n (Wheel)"), 'valign:M;align:C;rowspan:3');
      $header->easyCell(utf8Th("Truck \n No."), 'valign:M;align:C;rowspan:3');
      $header->easyCell(utf8Th("Driver Name"), 'valign:M;align:C;rowspan:3');
      $header->easyCell(utf8Th("Tel Nol"), 'valign:M;align:C;rowspan:3');
      $header->easyCell(utf8Th("Time"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Rack \n Supplier \n Code"), 'valign:M;align:C;rowspan:3');
      $header->easyCell(utf8Th("Supplier Name"), 'valign:M;align:C;rowspan:3');
      $header->easyCell(utf8Th("ยอดตั้งต้น\nวันที่\n".$v->FirstDate), 'valign:M;align:C;rowspan:3');

      $header->easyCell(utf8Th("PTB (PLASTIC BOX)"), 'valign:M;align:C;colspan:3');
      $header->easyCell(utf8Th("CPB (CORUGATE BOX)"), 'valign:M;align:C;colspan:3');
      $header->easyCell(utf8Th("STR (STEEL RACK)"), 'valign:M;align:C;colspan:3');
      $header->easyCell(utf8Th("PPL (PLASTIC PALLET)"), 'valign:M;align:C;colspan:3');
      $header->easyCell(utf8Th("WPL (WOODEN PALLET)"), 'valign:M;align:C;colspan:3');
      $header->easyCell(utf8Th("Remark"), 'valign:M;align:C;rowspan:2');
      $header->printRow();

      $header->easyCell(utf8Th("Arrival- \n Departure"), 'valign:M;align:C;rowspan:2');


      $header->easyCell(utf8Th("พลาสติก กล่องแข็ง"), 'valign:M;align:C;colspan:3');
      $header->easyCell(utf8Th("พลาสติก กล่องลูกฟูก"), 'valign:M;align:C;colspan:3');
      $header->easyCell(utf8Th("ภาชนะใส่ชิ้นงาน ชนิดชั้นเหล็ก"), 'valign:M;align:C;colspan:3');
      $header->easyCell(utf8Th("พาเลท พลาสติก"), 'valign:M;align:C;colspan:3');
      $header->easyCell(utf8Th("พาเลทไม้"), 'valign:M;align:C;colspan:3');
  
      $header->printRow();

      $header->easyCell(utf8Th("Receive\n(รับ)"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Remain\n(คงเหลือ)"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Return\n(ส่งกลับ)"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Receive\n(รับ)"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Remain\n(คงเหลือ)"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Return\n(ส่งกลับ)"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Receive\n(รับ)"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Remain\n(คงเหลือ)"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Return\n(ส่งกลับ)"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Receive\n(รับ)"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Remain\n(คงเหลือ)"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Return\n(ส่งกลับ)"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Receive\n(รับ)"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Remain\n(คงเหลือ)"), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Return\n(ส่งกลับ)"), 'valign:M;align:C;');
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
 $sql = "SELECT group_concat(concat(t2.Package_Type,':',t1.Balance_Start,'-',t1.Qty*-1,'-',t1.Balance_End) separator ',') groupPackage,
 t1.Document_No,t1.Document_Date,t1.Truck_License,t1.Truck_Type,t1.Driver_Name,t1.Phone,t1.Remarks,
 t1.Document_No_Ref,t1.Creation_Datetime,t1.Created_Name,t1.Updated_Datetime,t1.Updated_Name,
 t2.Package_Type,t2.Package_Name,t1.Balance_Start,t1.Balance_End,
 t3.Supplier_Code,t3.Supplier_Name,t1.Transaction_type,t4.Document_No TC,
 date_format(t1.Document_Date,'%m/%d/%Y')DocDate,date_format(t1.Document_Date,'1 %b %y')FirstDate
 from tbl_nm_milkrun_package_transaction t1
 left join tbl_nm_milkrun_packagemaster t2 on t1.Package_ID=t2.ID
 left join tbl_nm_milkrun_suppliermaster t3 on t1.Supplier_ID=t3.ID
 left join tbl_nm_milkrun_package_tc_running t4 on t1.Document_Date=t4.Document_Date
 where t1.Document_Date='$doctype' and t1.Transaction_type in('OUT') and t2.Package_Type in('PTB','CPB','STR','PPL','WPL')
 group by t1.Truck_License,t1.Supplier_ID;";
 
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

 $pdf=new PDF('L');
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

 $header=new easyTable($pdf,'%{3,2,3.5,3.5,6.5,4.5,4,4,9.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5,3.5}','border:1;font-family:Trirong;font-size:5; font-style:B;');
 $numRow = 0;
 $truckNumber = '';
 for($i=0,$len=count($data);$i<$len;$i++)
 {
    $rowObj = $data[$i];
    if($truckNumber != $rowObj->Truck_License)
    {
      $truckNumber = $rowObj->Truck_License;
      ++$numRow;
      $header->easyCell(utf8Th($numRow), 'valign:M;align:C;');
      $header->easyCell(utf8Th($numRow), 'valign:M;align:C;');
      $header->easyCell(utf8Th($rowObj->Truck_Type), 'valign:M;align:C;');
      $header->easyCell(utf8Th($rowObj->Truck_License), 'valign:M;align:C;');
      $header->easyCell(utf8Th($rowObj->Driver_Name), 'valign:M;align:C;');
      $header->easyCell(utf8Th($rowObj->Phone), 'valign:M;align:C;');
    }
    else
    {
      $header->easyCell(utf8Th(''), 'valign:M;align:C;');
      $header->easyCell(utf8Th(''), 'valign:M;align:C;');
      $header->easyCell(utf8Th(''), 'valign:M;align:C;');
      $header->easyCell(utf8Th(''), 'valign:M;align:C;');
      $header->easyCell(utf8Th(''), 'valign:M;align:C;');
      $header->easyCell(utf8Th(''), 'valign:M;align:C;');
    }
    
    $header->easyCell(utf8Th(''), 'valign:M;align:C;');
    $header->easyCell(utf8Th($rowObj->Supplier_Code), 'valign:M;align:C;');
    $header->easyCell(utf8Th($rowObj->Supplier_Name), 'valign:M;align:C;');
    
    $dataPackage = array('PTB'=>[0,0,0,0,0],'CPB'=>[0,0,0,0,0],'STR'=>[0,0,0,0,0],'PPL'=>[0,0,0,0,0],'WPL'=>[0,0,0,0,0]);
    $groupPackage = $rowObj->groupPackage;
    $groupPackageAr  = explode(',',$groupPackage);
    for($j=0,$len2=count($groupPackageAr);$j<$len2;$j++)
    {
      $packageData = explode(':',$groupPackageAr[$j]);
      $allQty = explode('-',$packageData[1]);
      $dataPackage[$packageData[0]][1] += $allQty[0];
      $dataPackage[$packageData[0]][2] += $allQty[1];
      $dataPackage[$packageData[0]][3] += $allQty[2];
    }
    $sumReceive = 0;
    $sumReturn = 0;
    foreach ($dataPackage as $value) 
    {
      $sumReceive += $value[1];
      $sumReturn += $value[2];
    }

    $header->easyCell(utf8Th($sumReceive), 'valign:M;align:C;');
    $header->easyCell(utf8Th(checkNumber($dataPackage['PTB'][1])), 'valign:M;align:C;');
    $header->easyCell(utf8Th(checkNumber($dataPackage['PTB'][2])), 'valign:M;align:C;');
    $header->easyCell(utf8Th(checkNumber($dataPackage['PTB'][3])), 'valign:M;align:C;');
    $header->easyCell(utf8Th(checkNumber($dataPackage['CPB'][1])), 'valign:M;align:C;');
    $header->easyCell(utf8Th(checkNumber($dataPackage['CPB'][2])), 'valign:M;align:C;');
    $header->easyCell(utf8Th(checkNumber($dataPackage['CPB'][3])), 'valign:M;align:C;');
    $header->easyCell(utf8Th(checkNumber($dataPackage['STR'][1])), 'valign:M;align:C;');
    $header->easyCell(utf8Th(checkNumber($dataPackage['STR'][2])), 'valign:M;align:C;');
    $header->easyCell(utf8Th(checkNumber($dataPackage['STR'][3])), 'valign:M;align:C;');
    $header->easyCell(utf8Th(checkNumber($dataPackage['PPL'][1])), 'valign:M;align:C;');
    $header->easyCell(utf8Th(checkNumber($dataPackage['PPL'][2])), 'valign:M;align:C;');
    $header->easyCell(utf8Th(checkNumber($dataPackage['PPL'][3])), 'valign:M;align:C;');
    $header->easyCell(utf8Th(checkNumber($dataPackage['WPL'][1])), 'valign:M;align:C;');
    $header->easyCell(utf8Th(checkNumber($dataPackage['WPL'][2])), 'valign:M;align:C;');
    $header->easyCell(utf8Th(checkNumber($dataPackage['WPL'][3])), 'valign:M;align:C;');
    $header->easyCell(utf8Th($sumReturn), 'valign:M;align:C;');
    $header->printRow();
 }
  /* $header->easyCell(utf8Th("Total :"), 'valign:M;align:R;colspan:5;border:0');
  $header->easyCell(utf8Th($sum_Qty), 'valign:M;align:C;');
  $header->printRow(); */
  $header->endTable(5);


  /* $header=new easyTable($pdf, '%{20,20,20,20,20}','border:1;font-family:Trirong;font-size:8; font-style:B;');
  
  $header->easyCell(utf8Th("พลาสติก กล่องแข็ง"), 'valign:M;align:C;');
  $header->easyCell(utf8Th("พลาสติก กล่องลูกฟูก"), 'valign:M;align:C;');
  $header->easyCell(utf8Th("ภาชนะใส่ชิ้นงาน ชนิดชั้นเหล็ก"), 'valign:M;align:C;');
  $header->easyCell(utf8Th("พาเลท พลาสติก"), 'valign:M;align:C;');
  $header->easyCell(utf8Th("พาเลทไม้"), 'valign:M;align:C;');
  $header->printRow();

  
  $header->easyCell(utf8Th("PTB (PLASTIC BOX)"), 'valign:M;align:C;');
  $header->easyCell(utf8Th("CPB (CORUGATE BOX)"), 'valign:M;align:C;');
  $header->easyCell(utf8Th("STR (STEEL RACK)"), 'valign:M;align:C;');
  $header->easyCell(utf8Th("PPL (PLASTIC PALLET)"), 'valign:M;align:C;');
  $header->easyCell(utf8Th("WPL (WOODEN PALLET)"), 'valign:M;align:C;');
  $header->printRow();

  
  $header->easyCell(utf8Th(checkNum($packageType['PTB'])), 'valign:M;align:C;');
  $header->easyCell(utf8Th(checkNum($packageType['CPB'])), 'valign:M;align:C;');
  $header->easyCell(utf8Th(checkNum($packageType['STR'])), 'valign:M;align:C;');
  $header->easyCell(utf8Th(checkNum($packageType['PPL'])), 'valign:M;align:C;');
  $header->easyCell(utf8Th(checkNum($packageType['WPL'])), 'valign:M;align:C;');
  $header->printRow();

  $header->endTable(0); */

 if($pdf->GetY()>258.3610326087)
  $pdf->AddPage();

  $header=new easyTable($pdf,2,'align:L;width:100%;border:0;font-family:Trirong;font-size:8; font-style:B;valign:T;');
  $header->easyCell(utf8Th('ชื่อ ผู้จ่ายภาชนะ :____________________________________________________________________'), 'valign:M;align:L;font-size:8;border:0');
  $header->easyCell(utf8Th('ชื่อ เจ้าหน้าที่ตรวจผ่าน :____________________________________________________________________'), 'valign:M;align:L;font-size:8;border:0');
  $header->printRow();
  $header->easyCell(utf8Th('(Warehouse)'), 'valign:M;align:C;font-size:8;border:0');
  $header->easyCell(utf8Th(''), 'valign:M;align:R;font-size:8;border:0');
  $header->printRow();
  $header->easyCell(utf8Th('หมายเหตุ : (1) พนักงานผ่ายคลังสินค้า (ผู้นำ) ส่งภาชนะและเอกสาร (ต้นขั้ว) (Orginal)'), 'valign:M;align:L;font-size:8;border:0');
  $header->easyCell(utf8Th('(2) เจ้าหน้าที่ตรวจผ่าน ภาชนะและเอกสาร เขตปลอดอากร (สำเนา) (Copy for Custom)'), 'valign:M;align:L;font-size:8;border:0');
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

function checkNumber($data)
{
  if($data>0) return $data;
  else return '';
}

function utf8Th($v)
{
  return iconv( 'UTF-8','TIS-620//IGNORE',$v);
}

 
?>