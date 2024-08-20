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
 
/*  $doctype = 'CLC19062800003';
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
      $header->easyCell('DAILY CYCLE COUNT (BY LOCATION)', 'valign:M;align:C;colspan:2;font-size:10;border:TB');
      $header->printRow();
      $header->endTable(0);
      $this->instance->Code128(145.5,11.5,$v->Document_No,55,5);
      $this->instance->SetFontSize(8);
      $this->instance->Text(155.5,20.5,wordwrap($v->Document_No,1,' ', true));

      $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Document No :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Document_No), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Document Date :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Document_Date), 'valign:M;align:L;');
      $header->easyCell(utf8Th("Created :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Created_Name), 'valign:M;align:L;');      
      $header->printRow();
      $header->easyCell(utf8Th("Section :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Station), 'valign:M;align:L;colspan:5');
      $header->printRow();
      $header->endTable(0);
      /* Document_Date
Document_StartDate
Document_EndDate */
      /* $header=new easyTable($this->instance,6,'align:L;width:100%;border:0;font-family:Trirong;font-size:6.5; font-style:B;valign:T;');
      $header->easyCell(utf8Th("Creation Date :"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Creation_Datetime), 'valign:M;align:L;');
      $header->easyCell(utf8Th("To Department"), 'valign:M;align:R;');
      $header->easyCell(utf8Th($v->Dep_Name), 'valign:M;align:L;');
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
      $header->endTable(0); */
      
      $header=new easyTable($this->instance, '%{5,20,35,20,20}','border:1;font-family:Trirong;font-size:8; font-style:B;');
      $header->easyCell(utf8Th("No."), 'valign:M;align:C;');
      $header->easyCell(utf8Th("Location"), 'valign:M;align:C;');
      $header->easyCell(utf8Th(""), 'valign:M;align:C;');
      $header->easyCell(utf8Th(""), 'valign:M;align:C;');
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

/* $sql = "SELECT Check_Type from tbl_nm_cyclecount_header 
where Document_No='$doctype' limit 1;";
$re1 = sqlError($mysqli,__LINE__,$sql,0);
if($re1->num_rows==0) closeDBT($mysqli,2,'ไม่พบข้อมูล '.$doctype);
$row = $re1->fetch_array(MYSQLI_ASSOC);
$Check_Type = $row['Check_Type']; */


 $tbody = array();
 $rowCount = 0;
 $printDate = '';
 $cbm = 0;
 $totalBox = 0;
 $numPallet = 0;
 $sumLine = 0;
 $sumQty = 0;
/*  $sql = "SELECT t4.Location,substring_index(t4.Location,'-',2) index2,t1.Document_No,t1.Document_Date,
 t1.Document_StartDate,t1.Document_EndDate,t1.Created_Name
 from tbl_nm_inventory_daily_location_header t1
 inner join tbl_nm_inventory_daily_location_body t2 on t1.ID=t2.Daily_Part_Check_Header
 left join tbl_storagemaster t4 on t2.Location_ID=t4.ID
 where t1.Document_No='$doctype' order by index2,Location;"; */

 $sql = "SELECT t1.Document_No,t1.Document_Date,t1.Status_Header,
 t1.Check_Type,t3.Location,t1.Created_Name,t1.Station
 from tbl_nm_cyclecount_header t1
 left join tbl_nm_cyclecount_body t2 on t1.ID=t2.Cyclecount_ID
 left join tbl_storagemaster t3 on t2.P_LO_ID=t3.ID
 where t1.Document_No='$doctype';";
 
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
    $header->rowStyle('min-height:10');
    $rowObj = $data[$i];
    $header->easyCell(utf8Th(++$numRow), 'valign:M;align:C;');
    $header->easyCell(utf8Th($rowObj->Location), 'valign:M;align:C;');
    $header->easyCell(utf8Th(''), 'valign:M;align:C;');
    $header->easyCell(utf8Th(''), 'valign:M;align:C;');
    $header->easyCell(utf8Th(''), 'valign:M;align:C;');
    $header->printRow();
    $pdf->Code128($pdf->GetX()+53,$pdf->GetY()-8,$rowObj->Location,55,5);
 }
 $header->endTable(0);


 if($pdf->GetY()>258.3610326087)
  $pdf->AddPage();

/*  $header=new easyTable($pdf,3,'align:L;width:100%;border:0;font-family:Trirong;font-size:8; font-style:B;valign:T;');
 $header->rowStyle('min-height:10');
 $header->easyCell(utf8Th(""), 'valign:M;align:C;');
 $header->easyCell(utf8Th("Check By"), 'valign:M;align:C;');
 $header->easyCell(utf8Th(""), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th(""), 'valign:M;align:C;');
 $header->easyCell(utf8Th("................................................................................."), 'valign:M;align:C;');
 $header->easyCell(utf8Th(""), 'valign:M;align:C;');
 $header->printRow();
 $header->easyCell(utf8Th(""), 'valign:M;align:C;');
 $header->easyCell(utf8Th("................................................................................."), 'valign:M;align:C;');
 $header->easyCell(utf8Th(""), 'valign:M;align:C;');
 $header->printRow();

 $header->endTable(0); */
 

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