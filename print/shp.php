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

/*$doctype = 'SHP1607010001';
$copy =1;
$printerName = '14G01_1';
$printType = 'I';*/

/*if($printerName == 'NO_PRINT')
{
  echo '{ch:2,data:"ไม่ได้เลือกปริ้นเตอร์"}';
  exit();
}*/

include('../php/connection.php');
require_once('tcpdf/tcpdf.php');
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetFont('freeserif', '');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(TRUE, 0);
$pdf->SetMargins(10, 5, 10,5);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
    require_once(dirname(__FILE__).'/lang/eng.php');
    $pdf->setLanguageArray($l);
}
$pdf->SetCreator(PDF_CREATOR);

$chk = $mysqli->query("SELECT `ship_no` from tbl_shipping_header where `ship_no`='$doctype' and status in('PENDING','CLOSED') and tstatus='YES' limit 1");
if ($chk->num_rows == 0) 
{
	echo '{"ch":0,"data":"ไม่พบ Ship No '.$doctype.' ในระบบ"}';
	$mysqli->close();
	exit();
}else
{
	$outType = intval($chk->fetch_object()->outType);
}

$num = 'page 1/1';

$thead = '<table border="1" cellspacing="0" cellpadding="2">
	<tr style="font-size:12px;background-color:#C8C8C8;" >
		<td align="center" rowspan="2" width="30"><b>No<span style="font-size: 22px;">&nbsp;</span></b></td>
		<td align="center" rowspan="2" width="110"><b>Plant Delivery<span style="font-size: 22px;">&nbsp;</span></b></td>
		<td align="center" rowspan="2" width="90"><b>Period<span style="font-size: 22px;">&nbsp;</span></b></td>
		<td align="center" rowspan="2" width="193"><b>PART DELIVERY SUMMARY<span style="font-size: 22px;">&nbsp;</span></b></td>
		<td align="center" rowspan="2" width="55"><b>Total Qty<span style="font-size: 18px;">&nbsp;</span></b></td>
		<td align="center" rowspan="2" width="55"><b>Total Box<span style="font-size: 18px;">&nbsp;</span></b></td>
		<td align="center" width="70"><b>Loading</b></td>
		<td align="center" width="70"><b>Unloading</b></td>
	</tr>
	<tr style="font-size:12px;background-color:#C8C8C8;" >
		<td align="center" width="70"><b>ขึ้นงาน</b></td>
		<td align="center" width="70"><b>ลงงาน</b></td>
	</tr>';

	$foot = '
<table border="0">
	<tr>
		<td align="center" style="font-size:12px;">TTV In Plant Receiving :</td>
		<td align="center" style="font-size:12px;"></td>
		<td align="center" style="font-size:12px;">Data Entry :</td>
	</tr>
	<tr>
		<td align="center">_______________________</td>
		<td align="center"></td>
		<td align="center">_______________________</td>
	</tr>
	<tr>
		<td align="center">(______/_______/_______)</td>
		<td align="center"></td>
		<td align="center">(______/_______/_______)</td>
	</tr>
</table>';
$tableData = $thead;

$result = $mysqli->query("SELECT count(t3.doc)numBox,t3.period,t3.doc_pds,substring(t5.dock,1,1)dock,t4.partNo,t4.partName,t1.ship_no,t1.ship_from,t1.ship_to,DATE_FORMAT(t1.dDate, '%d-%b-%Y') ship_date,t1.working_ship,t1.trip_no,t1.truck_license,t1.driver_Name,t1.phone,t1.routeCode,TIME_FORMAT(t1.print_time,'%H:%i') print_time,TIME_FORMAT(t1.plan_time,'%H:%i') plan_time,
sum(t3.qty*-1) qty,t2.gtn,t1.cBy,TIME_FORMAT(t1.ttv_out,'%H:%i') ttv_out  from tbl_shipping_header t1 left join tbl_shipping_body t2 on t1.ID = t2.refID
left join tbl_inventory_transac t3 on t2.gtn=t3.doc left join tbl_partmaster t4 on t3.partID=t4.id left join tbl_order t5 on t3.dnid=t5.id
where t1.ship_no='$doctype' and t1.status in('PENDING','CLOSED') and t1.tstatus='YES' and t2.refID is not null and t3.tstatus='YES'
group by t3.doc");
if($result) 
{ 
	if($result->num_rows > 0)
	{	
		$field = 0;
    	$c = 0;
    	$n = 1;
    	$d = 17;
    	$p = $d*$n;
    	$totalBox=0;
    	$allPage = ceil($result->num_rows/$d);
    	$dataObj;

    	
    	  while($obj = $result->fetch_object())
    	  { 
    	    $c++;

    	      if($field == 0)
    	      {
    	        $fieldName = array_keys(get_object_vars($obj));
    	        $numField = count($fieldName);
    	        $field = 1;
    	        $dataObj =$obj;
				$barcodeDocType= TCPDF_STATIC::serializeTCPDFtagParameters(array($dataObj->{'ship_no'}, 'C128', '', '', 0, 13, 0.4, array('position'=>'R', 'border'=>false, 'padding'=>1, 'fgcolor'=>array(0,0,0), 'bgcolor'=>array(255,255,255), 'text'=>true, 'font'=>'helvetica', 'fontsize'=>8, 'stretchtext'=>4,'cellfitalign'=>'R','stretch'=>true), 'N'));
    	        $pdf->AddPage();
				$html = createHead('page 1/'.$allPage,$barcodeDocType,$dataObj);
    	      }

    	      if($c > $p)
    	      {
    	      	$n++;
    	      	$p = $d*$n;
    	      	$tableData .='</table>';
    	      	$html .= $tableData;
    	      	$pdf->writeHTML($html, true, false, true, false, '');
    	      	$pdf->AddPage();
    	      	$html = createHead('page '.$n.'/'.$allPage,$barcodeDocType,$dataObj);
    	      	$tableData = $thead;
    	      	
    	      }

    	      $totalBox += $obj->{'numBox'};

    	      $tableData .= '<tr style="font-size:12px" >';
			  $tableData .= '<td align="center" width="30">'.$c.'</td>';
			  $tableData .= '<td align="center" width="110">'.$obj->{'dock'}.'</td>';
			  $tableData .= '<td align="center" width="90">'.$obj->{'period'}.'</td>';
			  $tableData .= '<td align="center" width="193">'.$obj->{'doc_pds'}.'</td>';
			  // $tableData .= '<td align="center" width="100">'.$obj->{'vendorName'}.'</td>';
			  $tableData .= '<td align="center" width="55">'.$obj->{'qty'}.'</td>';
			  $tableData .= '<td align="center" width="55">'.$obj->{'numBox'}.'</td>';
			  $tableData .= '<td align="center" width="70"></td>';
			  $tableData .= '<td align="center" width="70"></td></tr>';
    	      
    	  } 
    	  $tableData .='</table>';
    	  $html .= $tableData;

    	  $html .= '
    	  <table border="1"  cellspacing="0" cellpadding="2" style="font-size:12px;">
    	  	<tr>
    	  		<td rowspan="6" width="360">หมายเหตุ</td>
    	  		<td align="center" colspan="2" width="313" style="font-size:12px;background-color:#C8C8C8;"><b>บันทึกการเติมน้ำมันเชื่อเพลิง</b></td>
    	  	</tr>
    	  	<tr>
    	  		<td>วัน-เวลา ขณะเติม</td>
    	  		<td></td>
    	  	</tr>
    	  	<tr>
    	  		<td>ชนิดเชื่อเพลิง</td>
    	  		<td>[ &nbsp;] แก๊ส NGV [ &nbsp;] ดีเซล</td>
    	  	</tr>
    	  	<tr>
    	  		<td>เลขไมล์ขณะเติม</td>
    	  		<td></td>
    	  	</tr>
    	  	<tr>
    	  		<td>ปริมาณ (ลิตร,กิโลเมตร)</td>
    	  		<td></td>
    	  	</tr>
    	  	<tr>
    	  		<td>จำนวนเงินที่เติม (บาท)</td>
    	  		<td></td>
    	  	</tr>
		  </table>
		<p style="font-size: 2px;">&nbsp;</p>
<table border="1"  cellspacing="0" cellpadding="2">
	<tr style="font-size:12px;background-color:#C8C8C8;" >
		<td align="center" colspan="3"><b>TTV DEPOT</b></td>
		<td align="center" colspan="3"><b>MMTH</b></td>
	</tr>
	<tr style="font-size:12px;background-color:#C8C8C8;" >
		<td align="center"><b>Planning Time</b></td>
		<td align="center"><b>เวลาออก</b></td>
		<td align="center"><b>ลงชื่อผู้ปล่อยรถ</b></td>
		<td align="center"><b>เวลาเข้า</b></td>
		<td align="center"><b>เวลาออก</b></td>
		<td align="center"><b>ผู้รับสินค้า</b></td>
	</tr>
	<tr>
		<td align="center" rowspan="2" style="font-size:25px"><span style="font-size: 30px;">&nbsp;</span>'.$dataObj->{'plan_time'}.'</td>
		<td align="center" rowspan="2" style="font-size:25px"><span style="font-size: 30px;">&nbsp;</span>'.$dataObj->{'ttv_out'}.'</td>
		<td align="center" rowspan="2"></td>
		<td align="center" rowspan="2"></td>
		<td align="center" rowspan="2"></td>
		<td align="center" rowspan="2"></td>
	</tr> 
	<tr>
		<td align="center"></td>
		<td align="center"></td>
		<td align="center"></td>
		<td align="center"></td>
		<td align="center"></td>
		<td align="center"></td>
	</tr>
</table>
		<p style="font-size: 2px;">&nbsp;</p>
		<table border="1"  cellspacing="0" cellpadding="2">
			<tr style="font-size:12px;background-color:#C8C8C8;">
				<td align="center" colspan="2"><b>TTV DEPOT</b></td>
				<td align="center" colspan="2"><b>เลขไมล์</b></td>
			</tr>
			<tr style="font-size:12px;background-color:#C8C8C8;" >
				<td align="center"><b>เวลาเข้า</b></td>
				<td align="center"><b>ลงชื่อผู้รับ EMP</b></td>
				<td align="center"><b>เลขไมล์เริ่ม</b></td>
				<td align="center"><b>เลขไมล์สิ้นสุด</b></td>
			</tr>
			<tr>
				<td align="center" rowspan="2" style="font-size:25px"></td>
				<td align="center" rowspan="2" style="font-size:25px"></td>
				<td align="center" rowspan="2" style="font-size:25px"></td>
				<td align="center" rowspan="2" style="font-size:25px"></td>
			</tr> 
		</table>

		<p style="font-size: 2px;">&nbsp;</p>
		<table border="1"  cellspacing="0" cellpadding="2">
			<tr style="font-size:12px" >
				<td align="center" colspan="6" style="background-color:#C8C8C8;"><b>Delivery Packaging To MMTH. ( บรรจุภัณฑ์ที่จัดส่งเข้ามิตซู )</b></td>
			</tr>
			<tr style="font-size:12px;background-color:#C8C8C8;">
				<td align="center" rowspan="2"><b><span style="font-size: 22px;">&nbsp;</span>Vendor</b></td>
				<td align="center"><b>PTB</b></td>
				<td align="center"><b>CPB</b></td>
				<td align="center"><b>STR</b></td>
				<td align="center"><b>PPL</b></td>
				<td align="center"><b>WPL</b></td>
			</tr>
			<tr style="font-size:12px;background-color:#C8C8C8;">
				<td align="center" ><b>กล่องพลาสติก</b></td>
				<td align="center"><b>กล่องลูกฟูก</b></td>
				<td align="center"><b>แร็คเหล็ก</b></td>
				<td align="center"><b>พาเลทพลาสติก</b></td>
				<td align="center"><b>พาเลทไม้</b></td>
			</tr>
			<tr style="font-size:12px">
				<td align="center" >TGRT</td>
				<td align="center">'.$totalBox.'</td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
			</tr>
			<tr style="font-size:12px">
				<td align="center" ></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
			</tr>
			<tr style="font-size:12px">
				<td align="center" ></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
			</tr>
			
			<tr style="font-size:12px" >
				<td align="center" colspan="6" style="background-color:#C8C8C8;"><b>Empty Packaging From MMTH. ( บรรจุภัณฑ์เปล่าที่รับจากเข้ามิตซู )</b></td>
			</tr>
			<tr style="font-size:12px;background-color:#C8C8C8;">
				<td align="center" rowspan="2"><b><span style="font-size: 22px;">&nbsp;</span>Vendor</b></td>
				<td align="center"><b>PTB</b></td>
				<td align="center"><b>CPB</b></td>
				<td align="center"><b>STR</b></td>
				<td align="center"><b>PPL</b></td>
				<td align="center"><b>WPL</b></td>
			</tr>
			<tr style="font-size:12px;background-color:#C8C8C8;">
				<td align="center" ><b>กล่องพลาสติก</b></td>
				<td align="center"><b>กล่องลูกฟูก</b></td>
				<td align="center"><b>แร็คเหล็ก</b></td>
				<td align="center"><b>พาเลทพลาสติก</b></td>
				<td align="center"><b>พาเลทไม้</b></td>
			</tr>
			<tr style="font-size:12px">
				<td align="center" ></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
			</tr>
			<tr style="font-size:12px">
				<td align="center" ></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
			</tr>
			<tr style="font-size:12px">
				<td align="center" ></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
				<td align="center"></td>
			</tr>
		</table>
		';

    	  $html .= $foot;
    	  $pdf->writeHTML($html, true, false, true, false, '');

    	  $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,5);
		  $fileName = $randomString.strtotime(date('Y-m-d H:i:s')).'-'.$printerName.'-'.$copy.'.pdf'; 
		  $pdf->Output("C:\\report\\".$fileName,$printType);
		  echo '{"ch":1,"data":"เอกสารออกที่เครื่องปริ้นเตอร์ชื่อ '.$printerName.' จำนวน '.$copy.'ชุด"}';
	}
	else
	{

	}
	$result->close();
}
else
{

}

function createHead($page,$barcodeDocType,$obj)
{ 
	$project = $obj->{'project'};
	if($project != 'QX') 
	{
		$project = 'SU';
	}
	$headData = str_format('<table border="0">
	<tr>
		<td align="right" style="font-size:11px">{1} </td>
	</tr>
</table>',$page);

				$headData .= '
<table border="0">
	<tr>
		<td width="155"><img src="images/ttv-logo.gif" width="150"  height="53"/></td>
		<td align="left" width="300" style="font-size:11px"><b>TITAN-VNS AUTO LOGISTICS CO.,LTD.</b><br/>
		49/66 MOO 5 TUNGSUNKLA SRIRACHA CHONBURI 20230<br/>
		Phone +66(0) 3840 1505-6,3804 1787-8<br/>
		Fax : +66(0) 3849 4300
		</td>
		<td align="right" width="218"><tcpdf method="write1DBarcode" params="'.$barcodeDocType.'"/></td>
	</tr>
</table>
<hr/>
<table border="0">
	<tr>
		<td align="center"><b style="font-size:15px; margin-left:300px;">SHIPPING MANIFEST SHEET (TGRT)</b></td>
	</tr>
</table>
<hr />
<br />
<table border="0" style="margin-top:10px;" cellspacing="" cellpadding="2" style="font-size:11px">
	<tr>
		<td align="right" width="180"><b>Ship From : </b></td>
		<td align="left" width="150">'.$obj->{'ship_from'}.'</td>
		<td align="right" width="150"><b>Trip No :</b></td>
		<td align="left" width="120">'.$obj->{'trip_no'}.'</td>
	</tr>
	<tr>
		<td align="right"><b>Ship To : </b></td>
		<td align="left" width="150">'.$obj->{'ship_to'}.'</td>
		<td align="right" width="150"><b>Truck License :</b></td>
		<td align="left" width="120">'.$obj->{'truck_license'}.'</td>
		<td align="center"><b>Route No</b></td>
	</tr>
	<tr>
		<td align="right"><b>Ship Date : </b></td>
		<td align="left">'.$obj->{'ship_date'}.'</td>
		<td align="right" width="150"><b>Driver Name :</b></td>
		<td align="left" width="120">'.$obj->{'driver_Name'}.'</td>
		<td align="center">'.$obj->{'routeCode'}.'</td>
	</tr>
	<tr>
		<td align="right"><b>Working Shift : </b></td>
		<td align="left">'.$obj->{'working_ship'}.'</td>
		<td align="right" width="150"><b>Phone :</b></td>
		<td align="left" width="120">'.$obj->{'phone'}.'</td>
		
	</tr>
</table>';
	return $headData;
}
$mysqli->close();
exit();


?>
