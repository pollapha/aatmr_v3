<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'deliveryReport'}) )
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}
else if($_SESSION['xxxRole']->{'deliveryReport'}[0] == 0)
{
	echo "{ch:9,data:'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้'}";
	exit();
}

if(!isset($_REQUEST['type'])) {echo json_encode(array('ch'=>2,'data'=>'ข้อมูลไม่ถูกต้อง'));exit();}
$cBy = $_SESSION['xxxID'];
$fName = $_SESSION['xxxFName'];
$type  = intval($_REQUEST['type']);


include('../php/connection.php');
if($type<=10)//data
{
	if($type == 1)
	{
		$chkPOST = checkParamsAndDelare($_POST,array('obj','obj=>date1:s:1:3'),$mysqli);
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
		$dateAR = explode("-",$date1);
		$dataComplete = getData_check($mysqli,$dateAR[0],$dateAR[1]);
		$dataNOTComplete = getData_uncheck($mysqli,$dateAR[0],$dateAR[1]);
		$d = cal_days_in_month(CAL_GREGORIAN,$dateAR[1],$dateAR[0]);
		$date_array_check = array();
		$date_array_uncheck = array();
		for ($i=0; $i < $d; $i++) { 
			$date_array_check[$i] = 0;
			$date_array_uncheck[$i] = 0;
		}

		foreach ($dataComplete as $value) {
			$date_array_check[($value['eDate']-1)]++;
		}
		foreach ($dataNOTComplete as $value) {
			$date_array_uncheck[($value['eDate']-1)]++;
		}
		$getSumTruckForChart = array(
		'Delivery_Complete'=>$date_array_check,
		'Delivery_Not_Complete'=>$date_array_uncheck);
		$dataReturn = array('ch'=>1);
		$dataReturn['SumTruckForChart'] = $getSumTruckForChart;
		echo json_encode($dataReturn);
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>10 && $type<=20)//insert
{
	if($_SESSION['xxxRole']->{'deliveryReport'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
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
	if($_SESSION['xxxRole']->{'deliveryReport'}[2] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 21)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>30 && $type<=40)//delete
{
	if($_SESSION['xxxRole']->{'deliveryReport'}[3] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 31)
	{

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>40 && $type<=50)//save
{
	if($_SESSION['xxxRole']->{'deliveryReport'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 41)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else closeDBT($mysqli,2,'TYPE ERROR');

function getData_check($mysqli,$Y,$M)
{
	$sql = "SELECT
				DATE_FORMAT(End_Datetime, '%d') AS eDate 
			FROM
				tbl_truck_check ttc
			WHERE
				Status = 'CHECKED' AND
				MONTH(End_Datetime) = '$M'AND 
 	 			YEAR(End_Datetime) = '$Y';";
	$dataAll = array();
	$re1 = sqlError($mysqli,__LINE__,$sql);
	while($row = $re1->fetch_array(MYSQLI_ASSOC))
	{
		$dataAll[] = $row;
	}

	return $dataAll;
}

function getData_uncheck($mysqli,$Y,$M)
{
	$sql = "SELECT
				DATE_FORMAT(End_Datetime, '%d') AS eDate 
			FROM
				tbl_truck_check ttc
			WHERE
				Status <> 'CHECKED' AND
				MONTH(End_Datetime) = '$M'AND 
 	 			YEAR(End_Datetime) = '$Y';";
		
	$dataAll = array();
	$re1 = sqlError($mysqli,__LINE__,$sql);
	while($row = $re1->fetch_array(MYSQLI_ASSOC))
	{
		$dataAll[] = $row;
	}

	return $dataAll;
}

$mysqli->close();
exit();
?>
