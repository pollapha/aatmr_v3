<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'docIssue'}) )
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}
else if($_SESSION['xxxRole']->{'docIssue'}[0] == 0)
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
	else if($type == 2)
	{

	}
	else if($type == 3)
	{
		if(!isset($_GET['code'])) {echo '[]';closeDB($mysqli);}
		$code = checkTXT($mysqli,$_GET['code']);
		if($code == '') {echo '[]';closeDB($mysqli);}
		else toArrayStringOne($mysqli->query("SELECT driverName from tbl_driver_master where driverName like '%$code%' limit 5;"),1);
	}
	else if($type == 4)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>driverName'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
		$driverName = checkTXT($mysqli,$_POST['obj']['driverName']);

		$sql = "SELECT truckNo,phone from tbl_driver_master where driverName ='$driverName' limit 1;";
		if(!$re1 = $mysqli->query($sql)) closeDBT($mysqli,2,'ERROR LINE '.__LINE__);
		closeDBT($mysqli,1,jsonRow($re1,true,0));
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>10 && $type<=20)//insert
{
	if($_SESSION['xxxRole']->{'docIssue'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
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
	if($_SESSION['xxxRole']->{'docIssue'}[2] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 21)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>doc','obj=>truckLicense','obj=>driverName','obj=>phone'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
		$doc = checkTXT($mysqli,$_POST['obj']['doc']);
		$truckLicense = checkTXT($mysqli,$_POST['obj']['truckLicense']);
		$driverName = checkTXT($mysqli,$_POST['obj']['driverName']);
		$phone = checkTXT($mysqli,$_POST['obj']['phone']);


		if(strlen($doc) == 0) closeDBT($mysqli,2,'INV ไม่สามารถเป็นค่าว่างได้');
		$truckLicense = strlen($truckLicense) == 0 ? 'truckLicense':"'$truckLicense'";
		$driverName = strlen($driverName) == 0 ? 'driverName':"'$driverName'";
		$phone = strlen($phone) == 0 ? 'phone':"'$phone'";

		$sql = "SELECT ID from tbl_invoiceheader where doc='$doc' limit 1;";
		if(!$re1 = $mysqli->query($sql)) closeDBT($mysqli,2,'ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
		if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูล '.$doc);
		$row1 = $re1->fetch_array(MYSQLI_ASSOC);
		$headerID = $row1['ID'];

		$sql = "SELECT ID from tbl_invoicebody where headerID=$headerID and status='PENDING' limit 1;";
		if(!$re1 = $mysqli->query($sql)) closeDBT($mysqli,2,'ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
		if($re1->num_rows == 0) closeDBT($mysqli,2,'สถาน่ะต้องเป็น PENDING เท่านั้น ');

		$mysqli->autocommit(FALSE);

		try
		{	
			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			$sql ="UPDATE tbl_invoiceheader set truckLicense=$truckLicense,driverName=$driverName,phone=$phone,
			updateByID=$cBy,updateByName='$fName',updateDateTime=now(),outDate=now()
			where ID=$headerID;";
			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่สามารถบันทึกข้อมูลได้');

			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			$sql ="UPDATE tbl_invoicebody set status='IN TRANSIT',
			updateByID=$cBy,updateByName='$fName',updateDateTime=now()
			where headerID=$headerID and status = 'PENDING'";
			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่สามารถบันทึกข้อมูลได้');
			
			$mysqli->commit();
			closeDBT($mysqli,1,'บันทึกสำเร็จ');
		}
		catch( Exception $e )
		{
			$mysqli->rollback();
			closeDBT($mysqli,2,$e->getMessage());
		}
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>30 && $type<=40)//delete
{
	if($_SESSION['xxxRole']->{'docIssue'}[3] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 31)
	{

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>40 && $type<=50)//save
{
	if($_SESSION['xxxRole']->{'docIssue'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 41)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else closeDBT($mysqli,2,'TYPE ERROR');

$mysqli->close();
exit();
?>
