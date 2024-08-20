<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');


$printerName = $_REQUEST['printerName'];
$copy = $_REQUEST['copy'];
$doctype = $_REQUEST['doctype'];
$printType = $_REQUEST['printType'];
$warter = $_REQUEST['warter'];

/*WS16051400001
WS16051800002*/
/*$doctype = 'WS16051800002';
$copy = 1;
$printType = 'I';
$printerName = '1401';
$warter = 'NO';*/
if($printerName == 'NO_PRINT' && $printType == 'F')
{
    echo '{ch:2,data:"ไม่สามารถปริ้นได้เนื่องจากคุณไม่ได้เลือกปริ้นเตอร์"}';
    exit();
}
include('../php/connection.php');
if(!$re0 = $mysqli->query("SELECT t2.vendorCode from tbl_worksheet_body t2 
where t2.doc='$doctype'
group by t2.vendorCode,t2.branch,t2.plant")){echo '{ch:2,data:"Error Code 1"}';closeDB($mysqli);}
if($re0->num_rows == 0) {echo '{ch:2,data:"ไม่พบ '.$doctype.' ในระบบ1"}';closeDB($mysqli);} 
if($re0->num_rows == 1) {echo '{ch:2,data:"vendor ต้องมีมากกว่า 1 เจ้า"}';closeDB($mysqli);}    
$row1 =  $re0->num_rows*2;
if(!$re1 = $mysqli->query("SELECT t2.vendorCode from tbl_worksheet_body t2 left join tbl_worksheet_body_detail t3 on t2.id = t3.ref_id
where t2.doc='$doctype' and t2.id=t3.ref_id
group by t2.vendorCode,t2.branch,t2.plant,t3.trantype")){echo '{ch:2,data:"Error Code 2"}';closeDB($mysqli);}
if($re1->num_rows == 0) {echo '{ch:2,data:"ไม่พบ '.$doctype.' ในระบบ2"}';closeDB($mysqli);}
if($re1->num_rows != $row1) {echo '{ch:2,data:"คุณยังป้อนข้อมูลไมใ่ครบ"}';closeDB($mysqli);}

require('code128.php');
$pdf=new PDF_Code128();
$pdf->SetAutoPageBreak(true,10);
$pdf->AliasNbPages();
$c = 0;
$sum = 0;
$rowLen = 0;
$pdf->AddFont('THSarabun','','THSarabun.php');
$pdf->AddFont('THSarabun','B','THSarabun Bold.php');
$pdf->count1 = -1;
$pdf->count2 = 0;
$pdf->ptnAr = array();
if($result = $mysqli->query("SELECT t1.doc,t2.plant,t1.trip,t1.route,t1.period,t1.phone,t1.tLicense,t1.driverName,t1.cBy,t1.dDate,t2.book,t2.subbook1,t2.subbook2,t2.subbook3,t2.vendorCode,t2.branch,t2.vendorNameTh,group_concat(concat(TIME_FORMAT(t3.planIn, '%H:%i'),'_',TIME_FORMAT(t3.planOut, '%H:%i')) order by t3.trantype,t3.seq separator '|')plantime,group_concat(concat(t3.trantype,'_',t3.seal1,'_',t3.seal2,'_',t3.seal3,'_',t3.plant) order by t3.trantype,t3.seq separator '|')seal,t2.vendorType,t1.remark from tbl_worksheet_header t1 left join tbl_worksheet_body t2 on t1.doc = t2.doc left join tbl_worksheet_body_detail t3 on t2.id = t3.ref_id
where t1.doc='$doctype' and t1.doc=t2.doc and t2.id=t3.ref_id
group by t2.vendorCode,t2.branch,t2.plant order by t2.seqrun")) 
{ 
    $len = $result->num_rows;
    $pdf->rowLen = $len;
    if($len > 0)
    {  
        $totalPage = 0;
        $row = $result->fetch_object();
        $doc = $row->doc;
        $pdf->doc = $doc;
        $pdf->totalPage = $totalPage;
        $pdf->row = $row;
        $pdf->warter = $warter;
        $row->c = ++$c;
        $pdf->numrow = 0;

        // createHeader($pdf);
        createTableBody($pdf,$row);
        
        if($row->vendorType == 'VENDOR') $pdf->ptnAr[] = $row;
        for($i=1;$i<$len;$i++)
        {
            $row = $result->fetch_object();
            $row->c = ++$c;
            $sum += $row->qty;
            if($row->vendorType == 'VENDOR') $pdf->ptnAr[] = $row;
            createTableBody($pdf,$row);
        }
        createTableFooter($pdf,$sum);
        // createPageFooter($pdf,$cBy);

        if(strlen($printerName) >0)
        {
          $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,5);
          $fileName = $randomString.strtotime(date('Y-m-d H:i:s')).'-'.$printerName.'-'.$copy.'-l.pdf'; 
          $pdf->Output("C:\\report\\".$fileName,$printType);
          echo '{"ch":1,"data":"เอกสารออกที่เครื่องปริ้นเตอร์ชื่อ '.$printerName.' จำนวน '.$copy.'ชุด"}';
        }else echo '{ch:2,data:"ไม่สามารถปริ้นได้เนื่องจากคุณไม่ได้เลือกปริ้นเตอร์"}';
    }else echo '{"ch":0,"data":"ไม่พบ  <b>'.$doctype.'</b> ในระบบ"}';
}

$mysqli->close();
function createHeader($pdf,$row)
{
    $pdf->SetFont('THSarabun', 'B', 9);
    // $pdf->setXY(10,10);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Image('images/abt-logo.gif',10,10,20,12);
    // $pdf->SetXY(30,14);
    $pdf->SetX(30);
    $pdf->drawTextBox("ALBATROSS LOGISTICS CO., LTD.",90, 5,'L', 'T',0);
    $pdf->SetX(30);
    $pdf->SetFont('Arial', '', 8);
    $pdf->drawTextBox("336/7 MOO 7 BOWIN, SRIRACHA CHONBURI 20230\nPhone +66 38 058 021, +66 38 058 081-2\nFax : +66 38 058 007",90, 10, 'L', 'T',0);

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetXY(138,10);
    $pdf->drawTextBox(wordwrap($row->doc,1,'   ', true),287-50, 11, 'C', 'B',0);
    $pdf->Code128(287-60,10,$row->doc,60,7.5);
    $pdf->Ln(2);
    $pdf->Line(10,$pdf->GetY(),287,$pdf->GetY());
    $pdf->SetFont('Arial', 'B', 13);
    $pdf->drawTextBox('WORK SHEET ABT MMTH MILK RUN ZONE A',287, 7, 'C', 'M',0);
    $pdf->Line(10,$pdf->GetY(),287,$pdf->GetY());
    $pdf->Ln(2);

    $pdf->SetFont('THSarabun', 'B', 12);
    $pdf->SetFillColor(239, 240, 241);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620','วันที่ (Date) :'),'LRT',0,'R',true);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620',DateThai($row->dDate)),'LRT',0,'C',false);
    $pdf->Cell(20,5,iconv( 'UTF-8','TIS-620','Period :'),'LRT',0,'R',true);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620',$row->period),'RT',0,'C',false);
    $pdf->Cell(60,5,iconv( 'UTF-8','TIS-620','บันทึกเลขไมล์การทำงาน'),'LRT',0,'C',true);
    $pdf->Cell(100,5,iconv( 'UTF-8','TIS-620','เบอร์โทรศัพท์ CS MILK RUN MMTH ZONE A'),'LRT',0,'C',true);
    $pdf->Cell(-0,5,iconv( 'UTF-8','TIS-620','Trip'),'LRT',1,'C',true);

    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620','(Route) :'),'LRT',0,'R',true);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620',$row->route),'LT',0,'C',false);
    $pdf->Cell(20,5,iconv( 'UTF-8','TIS-620','ทะเบียนรถ :'),'LTR',0,'R',true);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620',$row->tLicense),'RT',0,'C',false);
    $pdf->Cell(30,5,iconv( 'UTF-8','TIS-620','เลขไมล์เริ่มต้นการทำงาน'),'LRT',0,'C',true);
    $pdf->Cell(30,5,iconv( 'UTF-8','TIS-620',' '),'LRT',0,'C',false);
    $pdf->Cell(33.3,5,iconv( 'UTF-8','TIS-620','08-1761-2803'),'LRT',0,'C',false);
    $pdf->Cell(33.3,5,iconv( 'UTF-8','TIS-620','06-2590-2520'),'LRT',0,'C',false);
    $pdf->Cell(33.3,5,iconv( 'UTF-8','TIS-620','09-2253-3790'),'LRT',0,'C',false);
    $pdf->Cell(-0,5,iconv( 'UTF-8','TIS-620',$row->trip),'LRT',1,'C',false);


    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620','พนักงานขับรถ :'),'LRTB',0,'R',true);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620',$row->driverName),'LTB',0,'C',false);
    $pdf->Cell(20,5,iconv( 'UTF-8','TIS-620','เบอร์โทรศัพท์ :'),'LTBR',0,'R',true);
    $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620',$row->phone),'RTB',0,'C',false);
    $pdf->Cell(30,5,iconv( 'UTF-8','TIS-620','เลขไมล์สิ้นสุดการทำงาน'),'LRTB',0,'C',true);
    $pdf->Cell(30,5,iconv( 'UTF-8','TIS-620',' '),'LRTB',0,'C',false);
    $pdf->Cell(33.3,5,iconv( 'UTF-8','TIS-620','06-2590-2846'),'LRTB',0,'C',false);
    $pdf->Cell(33.3,5,iconv( 'UTF-8','TIS-620','08-1761-2807'),'LRTB',0,'C',false);
    // $pdf->Cell(33.3,5,iconv( 'UTF-8','TIS-620',''),'LRTB',0,'C',false);
    $pdf->Cell(-0,5,iconv( 'UTF-8','TIS-620','Remark : '.$row->remark),'LRTB',1,'L',false);

    $pdf->SetFont('THSarabun', 'B', 15);
    $pdf->Cell(-0,5,iconv( 'UTF-8','TIS-620','แผนการทำงาน'),0,1,'C',false);

    $pdf->Ln(2);
}

function DateThai($strDate)
{
    $strYear = date("Y",strtotime($strDate))+543;
    $strMonth= date("n",strtotime($strDate));
    $strDay= date("j",strtotime($strDate));
    $strHour= date("H",strtotime($strDate));
    $strMinute= date("i",strtotime($strDate));
    $strSeconds= date("s",strtotime($strDate));
    $strMonthCut = Array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
    $strMonthThai=$strMonthCut[$strMonth];
    // , $strHour:$strMinute"
    return "$strDay $strMonthThai $strYear";
}

function createTableHeader($pdf)
{

    $pdf->SetFont('THSarabun', 'B', 9);
    $l1 = 5;
    $l2 = 8;
    $l3 = 15;
    $pdf->SetFillColor(239, 240, 241);
    //1
    $pdf->Cell(8,$l1,' ','LTR',0,'C',1);
    $pdf->Cell(25,$l1,' ','LTR',0,'C',1);
    $pdf->Cell(18,$l1,iconv( 'UTF-8','TIS-620','เวลาตามแผน'),1,0,'C',1);
    $pdf->Cell(22,$l1,iconv( 'UTF-8','TIS-620','เวลาตามจริง'),1,0,'C',1);
    $pdf->Cell(120,$l1,iconv( 'UTF-8','TIS-620','นับจำนวนภาชนะ'),1,0,'C',1);
    $pdf->Cell(54,$l1,iconv( 'UTF-8','TIS-620','บันทึกหมายเลขซีล'),1,0,'C',1);
    $pdf->Cell(15,$l1,iconv( 'UTF-8','TIS-620','ลงชื่อผู้เปิดซิล'),1,0,'C',1);
    $pdf->Cell(15,$l1,iconv( 'UTF-8','TIS-620','ลงชื่อผู้ล็อคซิล'),1,1,'C',1);

    //2
    $pdf->SetFont('THSarabun', 'B', 12);
    $pdf->Cell(8,$l2,'No','LR',0,'C',1);
    $pdf->Cell(25,$l2,iconv( 'UTF-8','TIS-620','บริษัท'),'LR',0,'C',1);
    $pdf->Cell(9,$l2,iconv( 'UTF-8','TIS-620','เข้า'),'LTR',0,'C',1);
    $pdf->Cell(9,$l2,iconv( 'UTF-8','TIS-620','ออก'),'LTR',0,'C',1);
    $pdf->Cell(11,$l2,iconv( 'UTF-8','TIS-620','เข้า'),'LTR',0,'C',1);
    $pdf->Cell(11,$l2,iconv( 'UTF-8','TIS-620','ออก'),'LTR',0,'C',1);

    $pdf->Cell(20,$l2,iconv( 'UTF-8','TIS-620','กล่องพลาสติก'),'LTR',0,'C',1);
    $pdf->Cell(20,$l2,iconv( 'UTF-8','TIS-620','กล่องลูกฟูก'),'LTR',0,'C',1);
    $pdf->Cell(20,$l2,iconv( 'UTF-8','TIS-620','แร็ค'),'LTR',0,'C',1);
    $pdf->Cell(20,$l2,iconv( 'UTF-8','TIS-620','พาเลทไม้'),'LTR',0,'C',1);
    $pdf->Cell(20,$l2,iconv( 'UTF-8','TIS-620','พาเลทพลาสติก'),'LTR',0,'C',1);
    $pdf->SetFont('THSarabun', 'B', 8);
    $pdf->Cell(20,$l2,iconv( 'UTF-8','TIS-620','กล่องดัมมี่-แผ่นรองยาง'),'LTR',0,'C',1);
    $pdf->SetFont('THSarabun', 'B', 12);

    $pdf->Cell(18,$l2,iconv( 'UTF-8','TIS-620','ซีลที่ 1'),'LTR',0,'C',1);
    $pdf->Cell(18,$l2,iconv( 'UTF-8','TIS-620','ซีลที่ 2'),'LTR',0,'C',1);
    $pdf->Cell(18,$l2,iconv( 'UTF-8','TIS-620','ซีลที่ 3'),'LTR',0,'C',1);

    $pdf->Cell(15,$l2,'','LTR',0,'C',1);
    $pdf->Cell(15,$l2,'','LTR',1,'C',1);

    //3
    $pdf->Cell(8,$l3,'','LR',0,'C',1);
    $pdf->Cell(25,$l3,'Vendor Code','LR',0,'C',1);
    $pdf->Cell(9,$l3,'','LR',0,'C',1);
    $pdf->Cell(9,$l3,'','LR',0,'C',1);
    $pdf->Cell(11,$l3,'','LR',0,'C',1);
    $pdf->Cell(11,$l3,'','LR',0,'C',1);

    $pdf->Image('images/box3.jpg',$pdf->getX()+1,$pdf->getY()+1,18,13);
    $pdf->Cell(20,$l3,'','LTR',0,'C',false);
    
    $pdf->Image('images/box2.jpg',$pdf->getX()+1,$pdf->getY()+1,18,13);
    $pdf->Cell(20,$l3,'','LTR',0,'C',false);

    $pdf->Image('images/box5.jpg',$pdf->getX()+1,$pdf->getY()+1,18,13);
    $pdf->Cell(20,$l3,'','LTR',0,'C',false);

    $pdf->Image('images/box4.jpg',$pdf->getX()+1,$pdf->getY()+1,18,13);
    $pdf->Cell(20,$l3,'','LTR',0,'C',false);

    $pdf->Image('images/box1.jpg',$pdf->getX()+1,$pdf->getY()+1,18,13);
    $pdf->Cell(20,$l3,'','LTR',0,'C',false);

    $pdf->Image('images/box6.jpg',$pdf->getX()+1,$pdf->getY()+1,18,13);
    $pdf->Cell(20,$l3,'','LTR',0,'C',false);

    $pdf->Cell(18,$l3,'','LR',0,'C',1);
    $pdf->Cell(18,$l3,'','LR',0,'C',1);
    $pdf->Cell(18,$l3,'','LR',0,'C',1);

    $pdf->Cell(15,$l3,'','LR',0,'C',1);
    $pdf->Cell(15,$l3,'','LR',0,'C',1);

    $pdf->Ln();
}

function createTableBody($pdf,$row)
{
    $getX = $pdf->GetY();

    if($getX < 15 || $getX > 270)
    {
        $pdf->AddPage('L');
        createHeader($pdf,$row);
        createTableHeader($pdf);
    }
    $seal = explode('|', $row->seal);
    $sealIN = explode('_', $seal[0]);
    $sealOUT = explode('_', $seal[1]);
    $plantimeAr = explode('|', $row->plantime);
    $plantime = explode('_', $plantimeAr[0]);
    $IN_OUT = '';
    if($row->vendorType == 'DESTINATION')
    {
        if($row->vendorCode != 'MMTH') $plan = '';
        else $plan = ' โรง '.$row->plant;
        if($plan == '') $plan = ' '.$row->vendorNameTh;

        $IN_OUT = $sealIN[0]== 'IN'? 'เข้า  ':'ออก ';

        $vendorB = $row->branch == '' ? '':'-'.$row->branch;
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(8,5,++$pdf->numrow,1,0,'C',false);
        $pdf->SetFont('THSarabun', 'B', 9);
        $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620',$IN_OUT.$row->vendorCode.$plan),1,0,'C',false);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(9,5,$plantime[0],1,0,'C',false);
        $pdf->Cell(9,5,'',1,0,'C',false);
        $pdf->Cell(11,5,'',1,0,'C',false);
        $pdf->Cell(11,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(10,5,'',1,0,'C',false);
        $pdf->Cell(10,5,'',1,0,'C',false);
        $pdf->Cell(18,5,$sealIN[1],1,0,'C',false);
        $pdf->Cell(18,5,$sealIN[2],1,0,'C',false);
        $pdf->Cell(18,5,$sealIN[3],1,0,'C',false);

        $pdf->SetFillColor(145,153,161);
        $pdf->Cell(15,5,'',1,0,'C',(++$pdf->count1) % 2 == 0);
        $pdf->Cell(15,5,'',1,1,'C',(++$pdf->count2) % 2 == 0);

        $plantime = explode('_', $plantimeAr[1]);
        if($row->vendorCode != 'MMTH') $plan = '';
        else $plan = ' โรง '.$row->plant;
        if($plan == '') $plan = ' '.$row->vendorNameTh;

        $IN_OUT = $sealOUT[0]== 'IN'? 'เข้า  ':'ออก ';

        $vendorB = $row->branch == '' ? '':'-'.$row->branch;
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(8,5,++$pdf->numrow,1,0,'C',false);
        $pdf->SetFont('THSarabun', 'B', 9);
        $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620',$IN_OUT.$row->vendorCode.$plan),1,0,'C',false);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(9,5,'',1,0,'C',false);
        $pdf->Cell(9,5,$plantime[1],1,0,'C',false);
        $pdf->Cell(11,5,'',1,0,'C',false);
        $pdf->Cell(11,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(10,5,'',1,0,'C',false);
        $pdf->Cell(10,5,'',1,0,'C',false);
        $pdf->Cell(18,5,$sealOUT[1],1,0,'C',false);
        $pdf->Cell(18,5,$sealOUT[2],1,0,'C',false);
        $pdf->Cell(18,5,$sealOUT[3],1,0,'C',false);

        $pdf->SetFillColor(145,153,161);
        $pdf->Cell(15,5,'',1,0,'C',(++$pdf->count1) % 2 == 0);
        $pdf->Cell(15,5,'',1,0,'C',(++$pdf->count2) % 2 == 0);

        $pdf->Ln();

    }
    else
    {
        $plan = ' งานโรง '.$row->plant;
        $vendorB = $row->branch == '' ? '':'-'.$row->branch;
        $vendorB ='';
        $plantime = explode('_', $plantimeAr[0]);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(8,10,++$pdf->numrow,'TLR',0,'C',false);
        $pdf->SetFont('THSarabun', 'B', 9);
        $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620',$IN_OUT.$row->vendorCode.$vendorB.$plan),'LTR',0,'C',false);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(9,5,$plantime[0],1,0,'C',false);
        $pdf->Cell(9,5,$plantime[1],1,0,'C',false);
        $pdf->Cell(11,5,'',1,0,'C',false);
        $pdf->Cell(11,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(10,5,'',1,0,'C',false);
        $pdf->Cell(10,5,'',1,0,'C',false);
        $pdf->Cell(18,5,$sealIN[1],1,0,'C',false);
        $pdf->Cell(18,5,$sealIN[2],1,0,'C',false);
        $pdf->Cell(18,5,$sealIN[3],1,0,'C',false);

        $pdf->SetFillColor(145,153,161);
        $pdf->Cell(15,5,'',1,0,'C',(++$pdf->count1) % 2 == 0);
        $pdf->Cell(15,5,'',1,1,'C',(++$pdf->count2) % 2 == 0);
        // $pdf->Ln();


        $pdf->Cell(8,5,' ','BLR',0,'C',false);
        $pdf->SetFont('THSarabun', 'B', 9);
        $pdf->Cell(25,5,iconv( 'UTF-8','TIS-620',$row->vendorNameTh),'BLR',0,'C',false);
        
        $pdf->Cell(29,5,iconv( 'UTF-8','TIS-620','ลงชื่อผู้ส่งสินค้า'),'BLR',0,'C',false);
        // $pdf->Cell(11,5,'',1,0,'C',false);
        $pdf->SetFont('Arial', '', 7);
        
        $pdf->Image('images/iconarrowR.png',$pdf->getX()+3,$pdf->getY(),5,5);
        $pdf->Cell(11,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(20,5,'',1,0,'C',false);
        $pdf->Cell(10,5,'',1,0,'C',false);
        $pdf->Cell(10,5,'',1,0,'C',false);
        $pdf->Cell(18,5,$sealOUT[1],1,0,'C',false);
        $pdf->Cell(18,5,$sealOUT[2],1,0,'C',false);
        $pdf->Cell(18,5,$sealOUT[3],1,0,'C',false);
        $pdf->Cell(15,5,'',1,0,'C',(++$pdf->count1) % 2 == 0);
        $pdf->Cell(15,5,'',1,0,'C',(++$pdf->count2) % 2 == 0);

        $pdf->Ln();

    }
}

function createTableFooter($pdf,$sum)
{
    $pdf->Ln(2);
    $pdf->SetFillColor(239, 240, 241);
    $y = $pdf->getY();
    $pdf->SetFont('THSarabun', 'B', 11);
    $pdf->Cell(100,5,iconv( 'UTF-8','TIS-620','บันทึกควบคุมภาชนะ'),1,1,'C',true);
    $pdf->Cell(20,5,iconv( 'UTF-8','TIS-620','Supplier'),'LRB',0,'C',true);
    $pdf->Cell(40,5,iconv( 'UTF-8','TIS-620','Packageing Transfer Note'),'LRB',0,'C',true);
    $pdf->Cell(13.3,5,iconv( 'UTF-8','TIS-620','ใบที่ 1'),'LRB',0,'C',true);
    $pdf->Cell(13.3,5,iconv( 'UTF-8','TIS-620','ใบที่ 2'),'LRB',0,'C',true);
    $pdf->Cell(13.3,5,iconv( 'UTF-8','TIS-620','ใบที่ 3'),'LRB',1,'C',true);

    $len = count($pdf->ptnAr);
    
    for($i=0;$i<$len;$i++)
    {
        $row = $pdf->ptnAr[$i];
        $book = $row->book;
        $subbook1 = $row->subbook1;
        $subbook2 = $row->subbook2;
        $subbook3 = $row->subbook3;
        $vendorCode = $row->vendorCode;
        $vendorB = $row->branch == '' ? '':'-'.$row->branch;
        $pdf->Cell(20,5,iconv( 'UTF-8','TIS-620',$vendorCode.$vendorB),'LRB',0,'C',false);
        $pdf->Cell(10,5,iconv( 'UTF-8','TIS-620','เล่มที่'),'LRB',0,'C',true);
        $pdf->Cell(20,5,iconv( 'UTF-8','TIS-620',$book),'LRB',0,'C',false);
        $pdf->Cell(10,5,iconv( 'UTF-8','TIS-620','เลขที่'),'LRB',0,'C',true);
        $pdf->Cell(13.3,5,iconv( 'UTF-8','TIS-620',$subbook1),'LRB',0,'C',false);
        $pdf->Cell(13.3,5,iconv( 'UTF-8','TIS-620',$subbook2),'LRB',0,'C',false);
        $pdf->Cell(13.3,5,iconv( 'UTF-8','TIS-620',$subbook3),'LRB',1,'C',false);
    }
    // $pdf->setX($x+120);
    $pdf->setXY($x+110,$y);
    $pdf->Cell(80,5,iconv( 'UTF-8','TIS-620',''),'LRTB',1,'C',true);
    $pdf->setX($x+110);
    $pdf->Image('images/4w.jpg',$pdf->getX()+3,$pdf->getY()+2,35,15);
    $pdf->Cell(40,20,iconv( 'UTF-8','TIS-620',''),'LRTB',0,'C',false);
    $pdf->Image('images/6w.jpg',$pdf->getX()+3,$pdf->getY()+2,35,15);
    $pdf->Cell(40,20,iconv( 'UTF-8','TIS-620',''),'LRTB',1,'C',false);
    $pdf->setX($x+110);
    $pdf->Cell(40,5,iconv( 'UTF-8','TIS-620','4W (สี่ล้อ)'),'LRTB',0,'C',true);
    $pdf->Cell(40,5,iconv( 'UTF-8','TIS-620','6W (หกล้อ)'),'LRTB',1,'C',true);
    $pdf->setX($x+110);
    $pdf->Cell(80,5,iconv( 'UTF-8','TIS-620','จุดล็อคซีล'),'LRTB',0,'C',true);

    $pdf->setXY($x+190,$y);
    $pdf->Cell(50,5,iconv( 'UTF-8','TIS-620','คำแนะนำ / ปัญหาที่พบ'),1,1,'C',true);
    $pdf->setX($x+190);
    $pdf->Cell(50,5,iconv( 'UTF-8','TIS-620',' '),1,1,'C',false);
    $pdf->setX($x+190);
    $pdf->Cell(50,5,iconv( 'UTF-8','TIS-620',' '),1,1,'C',false);
    $pdf->setX($x+190);
    $pdf->Cell(50,5,iconv( 'UTF-8','TIS-620',' '),1,1,'C',false);
    $pdf->setX($x+190);
    $pdf->Cell(50,5,iconv( 'UTF-8','TIS-620',' '),1,1,'C',false);
    $pdf->setX($x+190);
    $pdf->Cell(50,5,iconv( 'UTF-8','TIS-620',''),1,1,'L',false);
    $pdf->setX($x+190);
    $pdf->Cell(50,5,iconv( 'UTF-8','TIS-620','ลงชื่อผู้แจ้ง :'),1,1,'L',true);
    $pdf->setXY($x+240,$y);
    $pdf->Cell(47,5,iconv( 'UTF-8','TIS-620','ลงชื่อ ABT'),1,1,'C',true);
    $pdf->setX($x+240);
    $pdf->Cell(47/2,5,iconv( 'UTF-8','TIS-620',''),'LRT',0,'C',false);
    $pdf->Cell(47/2,10,iconv( 'UTF-8','TIS-620','Perpare By CS'),'LRT',1,'L',true);

    $pdf->setX($x+240);
    $pdf->Cell(47/2,5,iconv( 'UTF-8','TIS-620',''),'LR',0,'C',false);
    $pdf->Cell(47/2,0,iconv( 'UTF-8','TIS-620',''),'LR',1,'C',true);
    $pdf->setX($x+240);
    $pdf->Cell(47/2,5,iconv( 'UTF-8','TIS-620',''),'LRT',0,'C',false);
    $pdf->Cell(47/2,10,iconv( 'UTF-8','TIS-620','Driver'),'LRT',1,'L',true);

    $pdf->setX($x+240);
    $pdf->Cell(47/2,5,iconv( 'UTF-8','TIS-620',''),'LR',0,'C',false);
    $pdf->Cell(47/2,0,iconv( 'UTF-8','TIS-620',''),'LR',1,'C',false);
    $pdf->setX($x+240);
    $pdf->Cell(47/2,5,iconv( 'UTF-8','TIS-620',''),'LRT',0,'C',false);
    $pdf->Cell(47/2,5,iconv( 'UTF-8','TIS-620','Check By Data'),'LRT',1,'L',true);

    $pdf->setX($x+240);
    $pdf->Cell(47/2,5,iconv( 'UTF-8','TIS-620',''),'LRB',0,'C',false);
    $pdf->Cell(47/2,5,iconv( 'UTF-8','TIS-620','(Retrun To ABT)'),'LRB',1,'L',true);
}

function createPageFooter($pdf,$cBy)
{
    $w = 190/3;
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell($w,5,'- Data Entry -',0,0,'C',false);
    $pdf->Cell($w,5,'- Delivered By -',0,0,'C',false);
    $pdf->Cell($w,5,'- Received By -',0,0,'C',false);
    $pdf->Ln(7);
    $pdf->drawTextBox('',62, 10, 'C', 'T',0);
    $pdf->Line(15,$pdf->GetY(),$w+5,$pdf->GetY());
    $pdf->Line($w+15,$pdf->GetY(),$w*2+5,$pdf->GetY());
    $pdf->Line($w*2+15,$pdf->GetY(),$w*3+5,$pdf->GetY());
    // $pdf->Ln();
    $pdf->Cell($w,5,'(                 /                    /                  )',0,0,'C',false);
    $pdf->Cell($w,5,'(                 /                    /                  )',0,0,'C',false);
    $pdf->Cell($w,5,'(                 /                    /                  )',0,0,'C',false);
    $pdf->Ln();
    $pdf->Line(15,$pdf->GetY(),$w+5,$pdf->GetY());
    $pdf->Line($w+15,$pdf->GetY(),$w*2+5,$pdf->GetY());
    $pdf->Line($w*2+15,$pdf->GetY(),$w*3+5,$pdf->GetY());
    // $pdf->Line(15,$pdf->GetY(),$w+5,$pdf->GetY());
    // $pdf->Cell($w,5,'________________',0,0,'C',false);

}

function Rotate($pdf,$angle,$x=-1,$y=-1) { 

    if($x==-1) 
        $x=$pdf->x; 
    if($y==-1) 
        $y=$pdf->y; 
    if($pdf->angle!=0) 
        $pdf->_out('Q'); 
    $pdf->angle=$angle; 
    if($angle!=0) 

    { 
        $angle*=M_PI/180; 
        $c=cos($angle); 
        $s=sin($angle); 
        $cx=$x*$pdf->k; 
        $cy=($pdf->h-$y)*$pdf->k; 

        $pdf->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
     } 
  } 

function closeDB($mysqli)
{
    $mysqli->close();exit();
}

?>