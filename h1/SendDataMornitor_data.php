<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'SendDataMornitor'}) )
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}
else if($_SESSION['xxxRole']->{'SendDataMornitor'}[0] == 0)
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
		$sql ="SELECT t2.Load_ID,t2.StopSequenceNumber,t1.truckLicense TrailerNumber,
        date_format(t2.ActualIN_Datetime,'%Y-%m-%dT%H:%i:%s')DateIN,
        date_format(t2.ActualOut_Datetime,'%Y-%m-%dT%H:%i:%s')DateOUT,t2.Load_ID SystemLoadID,t2.Supplier_Code
        from tbl_204body_api t2
        left join tbl_204header_api t1 on t2.Load_ID=t1.Load_ID
        where t2.Update_ActualIN_API>t2.Update_ActualIN_API_Count and length(t1.truckLicense)>0
union all	
		SELECT t2.Load_ID,t2.StopSequenceNumber,t1.truckLicense TrailerNumber,
        date_format(t2.ActualIN_Datetime,'%Y-%m-%dT%H:%i:%s')DateIN,
        date_format(t2.ActualOut_Datetime,'%Y-%m-%dT%H:%i:%s')DateOUT,t2.Load_ID SystemLoadID,t2.Supplier_Code
        from tbl_204body_api t2
        left join tbl_204header_api t1 on t2.Load_ID=t1.Load_ID
        where t2.Update_ActualOut_API>t2.Update_ActualOut_API_Count 
        and t2.Update_ActualIN_API>0 and t2.Update_ActualIN_API=t2.Update_ActualIN_API_Count
        and length(t1.truckLicense)>0
        order by Load_ID,StopSequenceNumber;";


		$re1 = sqlError($mysqli,__LINE__,$sql);
		closeDBT($mysqli,1,jsonRow($re1,true,0));
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>10 && $type<=20)//insert
{
	if($_SESSION['xxxRole']->{'SendDataMornitor'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
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
	if($_SESSION['xxxRole']->{'SendDataMornitor'}[2] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 21)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>30 && $type<=40)//delete
{
	if($_SESSION['xxxRole']->{'SendDataMornitor'}[3] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 31)
	{

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>40 && $type<=50)//save
{
	if($_SESSION['xxxRole']->{'SendDataMornitor'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 41)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else closeDBT($mysqli,2,'TYPE ERROR');

$mysqli->close();
exit();
?>
