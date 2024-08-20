<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'mapMaster'}) )
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}
else if($_SESSION['xxxRole']->{'mapMaster'}[0] == 0)
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

	}
	else if($type == 3)
	{
		$re1 = getDataTruck($mysqli,__LINE__);
		closeDBT($mysqli,1,jsonRow($re1,true,0,''));
	}
	else if($type == 5)
	{

	}
	else if($type == 6)
	{
		if(!isset($_POST['obj'])) {echo json_encode(array('ch'=>2,'data'=>'ข้อมูลไม่ถูกต้อง 1'));closeDB($mysqli);}

		$dateStart = !isset($_POST['obj']['dateStart']) ? '' : $mysqli->real_escape_string(trim(strtoupper($_POST['obj']['dateStart'])));
		$timeStart = !isset($_POST['obj']['timeStart']) ? 0 : $mysqli->real_escape_string(trim(strtoupper($_POST['obj']['timeStart'])));

		$dateEnd = !isset($_POST['obj']['dateEnd']) ? 0 : $mysqli->real_escape_string(trim(strtoupper($_POST['obj']['dateEnd'])));
		$timeEnd = !isset($_POST['obj']['timeEnd']) ? 0 : $mysqli->real_escape_string(trim(strtoupper($_POST['obj']['timeEnd'])));

		$truckNo = !isset($_POST['obj']['truckNo']) ? 0 : $mysqli->real_escape_string(trim(strtoupper($_POST['obj']['truckNo'])));
		$Supplier_Code = !isset($_POST['obj']['Supplier_Code']) ? 0 : $mysqli->real_escape_string(trim(strtoupper($_POST['obj']['Supplier_Code'])));

		
		$dateStart = (explode(' ',$dateStart))[0];
		$dateEnd = (explode(' ',$dateEnd))[0];

		if(!(validateDate($dateStart,'Y-m-d') && validateDate($timeStart,'H:i') && validateDate($dateEnd,'Y-m-d') && validateDate($timeEnd,'H:i')))
		{
			closeDBT($mysqli,2,'ป้อนเวลาไม่ถูกต้อง');
		}
		 
		if(strlen($truckNo) == 0)
		{
			closeDBT($mysqli,2,'ทะเบียนรถไม่ถูกต้อง');
		}

		if(strlen($Supplier_Code) == 0)
		{
			closeDBT($mysqli,2,'ซัฟพลายเออร์ไม่ถูกต้อง');
		}

		$sql = "SELECT t4.gps_updateDatetime,t4.truckLicense,t5.code,ST_AsGeoJSON(ST_Centroid(t5.geo)) sup_geo,
		if(ST_Contains(t5.geo,(t4.geo))=1,'YES','NO')Contain,ST_AsGeoJSON(t4.Geo)pt
		from tbl_truck_log t4 ,tbl_supplier t5 
		where gps_updateDatetime between '$dateStart $timeStart' and '$dateEnd $timeEnd'
		and truckLicense='$truckNo' and t5.code='$Supplier_Code';";

		$re = sqlError($mysqli,__LINE__,$sql);

		closeDBT($mysqli,1,jsonRow($re,true,0));
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>10 && $type<=20)//insert
{
	if($_SESSION['xxxRole']->{'mapMaster'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
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
	if($_SESSION['xxxRole']->{'mapMaster'}[2] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 21)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>30 && $type<=40)//delete
{
	if($_SESSION['xxxRole']->{'mapMaster'}[3] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 31)
	{

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>40 && $type<=50)//save
{
	if($_SESSION['xxxRole']->{'mapMaster'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 41)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else closeDBT($mysqli,2,'TYPE ERROR');

function getDataTruck($mysqli,$lineCode)
{
	$sql = "SELECT truckLicense,truckType,ST_AsGeoJSON(Geo)Geo,gps_speed,gps_angle,truck_carrier,
	concat(substring_index(truckLicense,'-',-1),truck_carrier) ID,gps_updateDatetime
	from tbl_truck;";
	return sqlError($mysqli,$lineCode,$sql);
}



$mysqli->close();
exit();
?>
