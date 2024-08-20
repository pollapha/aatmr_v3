<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'hourlyreport'}) )
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}
else if($_SESSION['xxxRole']->{'hourlyreport'}[0] == 0)
{
	echo "{ch:9,data:'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้'}";
	exit();
}

if(!isset($_REQUEST['type'])) {echo json_encode(array('ch'=>2,'data'=>'ข้อมูลไม่ถูกต้อง'));exit();}
$cBy = $_SESSION['xxxID'];
$fName = $_SESSION['xxxFName'];
$type  = intval($_REQUEST['type']);


include('../php/connection.php');
include('../common/commonFunc.php');
require_once('../Classes/PHPExcel.php');
if($type<=10)//data
{
	if($type == 1)
	{
		$paramAr = getParams($mysqli,$_POST);
		$where = $paramAr['where'];
		$date1 = $paramAr['date1'];
		$date2 = $paramAr['date2'];				
		$data = hourly($mysqli,$where,$date1,$date2);
		closeDBT($mysqli,1,$data);
		// hourlyExport($data);
	}
	else if($type == 2)
	{
		$paramAr = getParams($mysqli,$_POST);
		$where = $paramAr['where'];
		$date1 = $paramAr['date1'];
		$date2 = $paramAr['date2'];				
		$data = hourly($mysqli,$where,$date1,$date2);
		hourlyAATMRExport($data);
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>10 && $type<=20)//insert
{
	if($_SESSION['xxxRole']->{'hourlyreport'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 11)
	{

	}
	else if($type == 12)
	{

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>20 && $type<=30)//update
{
	if($_SESSION['xxxRole']->{'hourlyreport'}[2] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 21)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>30 && $type<=40)//delete
{
	if($_SESSION['xxxRole']->{'hourlyreport'}[3] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 31)
	{

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>40 && $type<=50)//save
{
	if($_SESSION['xxxRole']->{'hourlyreport'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 41)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else closeDBT($mysqli,2,'TYPE ERROR');

function getParams($mysqli,$_POST_)
{
	$chkPOST = checkParams($_POST_,array('obj','obj=>date1','obj=>date2','obj=>project'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

	$date1 = checkTXT($mysqli,$_POST_['obj']['date1']);
	$date2 = checkTXT($mysqli,$_POST_['obj']['date2']);
	$project = checkTXT($mysqli,$_POST_['obj']['project'],0);
	if(strlen($date1) == 0 || strlen($date2) == 0) closeDBT($mysqli,2,'วันไม่ถูกต้อง');
	
	$date1 = date('Y-m-d',strtotime($date1));
	$date2 = date('Y-m-d',strtotime($date2));
	$date_Start = date('Y-m-d',strtotime($date1. ' - 1 days'));
	$date_End = date('Y-m-d',strtotime($date2. ' + 1 days'));
	$where = array();
	$where[] = "t2.Start_Datetime between '$date_Start 00:00:00' and '$date_End 23:59:59'";
	if($project != 'ALL')
	{
		$reProject = checkProject($mysqli,__LINE__,$project);
		if($reProject->num_rows==0)
		{
			closeDBT($mysqli,2,'ไม่พบข้อมูล Project '.$project);
		}
		$projectID = $reProject->fetch_array(MYSQLI_ASSOC)['ID'];
		$where[] = "t4.projectID=$projectID";
	}
	return array('where'=>$where,'date1'=>$date1,'date2'=>$date2);
}

function hourly($mysqli,$where,$date1,$date2)
{
	$sql = 
        "SELECT *,
		substring_index(group_concat(PlanIN_Datetime order by PlanIN_Datetime), ',', 1 ) as PlanIN_Datetime_,
		substring_index(group_concat(PlanOut_Datetime order by PlanOut_Datetime desc), ',', 1 ) as PlanOut_Datetime_,
		substring_index(group_concat(ActualIN_Datetime order by ActualIN_Datetime), ',', 1 ) as ActualIN_Datetime_,
		substring_index(group_concat(ActualOut_Datetime order by ActualOut_Datetime desc), ',', 1 ) as ActualOut_Datetime_,
		if(Supplier_Code_Split='GRBNA',substring_index(Supplier_Name,',',1),Supplier_Name)Supplier_Name_
		from
		(select t2.Load_ID,t2.Route,t2.Start_Datetime Operration_Date_Check,
		if(timeTh(t2.Start_Datetime)>'00:00:01' and timeTh(t2.Start_Datetime)<'05:40',dateTh(t2.Start_Datetime - INTERVAL 1 DAY),dateTh(t2.Start_Datetime)) Operration_Date,
		dateTh(t2.Start_Datetime)Start_Datetime,dateTh(t2.End_Datetime)End_Datetime,
		t2.truckLicense,t2.truckType,t3.StopSequenceNumber,
		t2.driverName,t2.phone,t5.projectName,
		t3.Supplier_Code,substring_index(t3.Supplier_Code,'-',1) Supplier_Code_Split ,t3.Supplier_Name,
		timeTh(t3.PlanIN_Datetime) PlanIN_Datetime,
		timeTh(t3.PlanOut_Datetime) PlanOut_Datetime,
		timeTh(t3.ActualIN_Datetime) ActualIN_Datetime,
		timeTh(t3.ActualOut_Datetime) ActualOut_Datetime
		from tbl_transaction t1
		inner join tbl_204header_api t2 on t1.Load_ID=t2.Load_ID
		left join tbl_204body_api t3 on t2.Load_ID=t3.Load_ID
		left join tbl_route_master_header t4 on t2.Route=t4.routeName
		left join tbl_project_master t5 on t4.projectID=t5.ID
		where ".join(' and ',$where)."
		having if(timeTh(Operration_Date_Check)>'00:00:01' and timeTh(Operration_Date_Check)<'05:40',date(Operration_Date_Check - INTERVAL 1 DAY),date(Operration_Date_Check)) between '$date1' and '$date2'
		)s1
		group by Load_ID,Supplier_Code_Split
		order by Operration_Date,Load_ID,StopSequenceNumber;";
		//exit($sql);
		
		$re1 = sqlError($mysqli,__LINE__,$sql);
		if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูลในระบบ');
		$loadID = '';
		$data = array();
		$sup = 0;
		$count = 0;
		while($row = $re1->fetch_array(MYSQLI_ASSOC))
		{
			if($row['Load_ID'] != $loadID)
			{
				$sup = 1;
				$loadID = $row['Load_ID'];
				$row['sup'.$sup.'PlanIN_Datetime'] = $row['PlanIN_Datetime'];
				$row['sup'.$sup.'PlanOut_Datetime'] = $row['PlanOut_Datetime'];
				$row['sup'.$sup.'ActualIN_Datetime'] = $row['ActualIN_Datetime'];
				$row['sup'.$sup.'ActualOut_Datetime'] = $row['ActualOut_Datetime'];
				$row['sup'.$sup.'Supplier_Code'] = $row['Supplier_Code_Split'];
				$row['sup'.$sup.'Supplier_Name'] = $row['Supplier_Name_'];
				$row['NO'] = ++$count;
				$data[] = $row;
			}
			else
			{				
				$sup++;
				$index = count($data)-1;				
				$data[$index]['sup'.$sup.'PlanIN_Datetime'] = $row['PlanIN_Datetime'];
				$data[$index]['sup'.$sup.'PlanOut_Datetime'] = $row['PlanOut_Datetime'];
				$data[$index]['sup'.$sup.'ActualIN_Datetime'] = $row['ActualIN_Datetime'];
				$data[$index]['sup'.$sup.'ActualOut_Datetime'] = $row['ActualOut_Datetime'];
				$data[$index]['sup'.$sup.'Supplier_Code'] = $row['Supplier_Code_Split'];
				$data[$index]['sup'.$sup.'Supplier_Name'] = $row['Supplier_Name_'];
				
			}
		}
		return $data;
}

function hourlyAATMRExport($data)
{
$header = array(
'NO',
'Load ID',
'Operration Date',
'Pick Up Date',
'Delivery Date',
'Route',
'Load_ID',
'truck License',
'truck Type',
'driver Name',
'phone',
'sup1Supplier_Name',
'sup1PlanIN_Datetime',
'sup1PlanOut_Datetime',
'sup1ActualIN_Datetime',
'sup1ActualOut_Datetime',

'sup2Supplier_Name',
'sup2PlanIN_Datetime',
'sup2PlanOut_Datetime',
'sup2ActualIN_Datetime',
'sup2ActualOut_Datetime',

'sup3Supplier_Name',
'sup3PlanIN_Datetime',
'sup3PlanOut_Datetime',
'sup3ActualIN_Datetime',
'sup3ActualOut_Datetime',

'sup4Supplier_Name',
'sup4PlanIN_Datetime',
'sup4PlanOut_Datetime',
'sup4ActualIN_Datetime',
'sup4ActualOut_Datetime',

'sup5Supplier_Name',
'sup5PlanIN_Datetime',
'sup5PlanOut_Datetime',
'sup5ActualIN_Datetime',
'sup5ActualOut_Datetime',

'sup6Supplier_Name',
'sup6PlanIN_Datetime',
'sup6PlanOut_Datetime',
'sup6ActualIN_Datetime',
'sup6ActualOut_Datetime',

'sup7Supplier_Name',
'sup7PlanIN_Datetime',
'sup7PlanOut_Datetime',
'sup7ActualIN_Datetime',
'sup7ActualOut_Datetime',

'Delivery',
'DeliveryPlanIN_Datetime',
'DeliveryPlanOut_Datetime',
'DeliveryActualIN_Datetime',
'DeliveryActualOut_Datetime',
);
$d = array();
$d[] = $header;
for($i=0,$len=count($data);$i<$len;$i++)
{
	$ar = array();
	$ar[] = $data[$i]['NO'];
	$ar[] = $data[$i]['Load_ID'];
	$ar[] = $data[$i]['Operration_Date'];
	$ar[] = $data[$i]['Start_Datetime'];
	$ar[] = $data[$i]['End_Datetime'];
	$ar[] = $data[$i]['Route'];
	$ar[] = $data[$i]['Load_ID'];
	$ar[] = $data[$i]['truckLicense'];
	$ar[] = $data[$i]['truckType'];
	$ar[] = $data[$i]['driverName'];
	$ar[] = $data[$i]['phone'];
	$last = 0;
	for($j=1,$len2=8;$j<$len2;$j++)
	{
		if(!array_key_exists('sup'.($j+1).'Supplier_Name', $data[$i]) && $last == 0)
		{
			$last = ($j);
		}
		if($last==0)
		{
			$ar[] = array_key_exists('sup'.$j.'Supplier_Name', $data[$i]) ? $data[$i]['sup'.$j.'Supplier_Name']:'';
			$ar[] = array_key_exists('sup'.$j.'PlanIN_Datetime', $data[$i]) ? $data[$i]['sup'.$j.'PlanIN_Datetime']:'';
			$ar[] = array_key_exists('sup'.$j.'PlanOut_Datetime', $data[$i]) ? $data[$i]['sup'.$j.'PlanOut_Datetime']:'';
			$ar[] = array_key_exists('sup'.$j.'ActualIN_Datetime', $data[$i]) ? $data[$i]['sup'.$j.'ActualIN_Datetime']:'';
			$ar[] = array_key_exists('sup'.$j.'ActualOut_Datetime', $data[$i]) ? $data[$i]['sup'.$j.'ActualOut_Datetime']:'';
		}
		else
		{
			$ar[] = '';
			$ar[] = '';
			$ar[] = '';
			$ar[] = '';
			$ar[] = '';
		}
			
	}
	$ar[] = array_key_exists('sup'.$last.'Supplier_Name', $data[$i]) ? $data[$i]['sup'.$last.'Supplier_Name']:'';
	$ar[] = array_key_exists('sup'.$last.'PlanIN_Datetime', $data[$i]) ? $data[$i]['sup'.$last.'PlanIN_Datetime']:'';
	$ar[] = array_key_exists('sup'.$last.'PlanOut_Datetime', $data[$i]) ? $data[$i]['sup'.$last.'PlanOut_Datetime']:'';
	$ar[] = array_key_exists('sup'.$last.'ActualIN_Datetime', $data[$i]) ? $data[$i]['sup'.$last.'ActualIN_Datetime']:'';
	$ar[] = array_key_exists('sup'.$last.'ActualOut_Datetime', $data[$i]) ? $data[$i]['sup'.$last.'ActualOut_Datetime']:'';
	$d[] = $ar;
}

$data[] = $header;
	$excel = new PHPExcel();
		PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
		$excel->setActiveSheetIndex(0);		
		$objWorksheet = $excel->getActiveSheet();		
		$objWorksheet->fromArray(
			$d
	);
		
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="ดาวน์โหลด.xlsx"');
		header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');
		
		$objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$objWriter->save('php://output');
}
$mysqli->close();
exit();
?>
