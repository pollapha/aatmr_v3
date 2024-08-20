<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'closeInv'}) )
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}
else if($_SESSION['xxxRole']->{'closeInv'}[0] == 0)
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
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>10 && $type<=20)//insert
{
	if($_SESSION['xxxRole']->{'closeInv'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
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
	if($_SESSION['xxxRole']->{'closeInv'}[2] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 21)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>doc','obj=>supReciverName','obj=>supReciverDate'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		$doc = checkTXT($mysqli,$_POST['obj']['doc']);
		$supReciverName = checkTXT($mysqli,$_POST['obj']['supReciverName']);
		$supReciverDate = checkTXT($mysqli,$_POST['obj']['supReciverDate']);

		
		if(strlen($doc) == 0) closeDBT($mysqli,2,'INV ไม่สามารถเป็นค่าว่างได้');
		if(strlen($supReciverDate) == 0) closeDBT($mysqli,2,'วันที่รับ ไม่สามารถเป็นค่าว่างได้');
		if(strlen($supReciverName) == 0) closeDBT($mysqli,2,'ผู้รับเอกสาร ไม่สามารถเป็นค่าว่างได้');


		$sql = "SELECT ID from tbl_invoiceheader where doc='$doc' limit 1;";
		if(!$re1 = $mysqli->query($sql)) closeDBT($mysqli,2,'ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
		if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูล '.$doc);
		$row1 = $re1->fetch_array(MYSQLI_ASSOC);
		$headerID = $row1['ID'];

		$sql = "SELECT ID from tbl_invoicebody where headerID=$headerID and status='IN TRANSIT' limit 1;";
		if(!$re1 = $mysqli->query($sql)) closeDBT($mysqli,2,'ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
		if($re1->num_rows == 0) closeDBT($mysqli,2,'สถาน่ะต้องเป็น IN TRANSIT เท่านั้น ');

		$mysqli->autocommit(FALSE);

		try
		{	
			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			$sql ="UPDATE tbl_invoicebody set status='CLOSED',
			updateByID=$cBy,updateByName='$fName',updateDateTime=now()
			where headerID=$headerID and status = 'IN TRANSIT'";
			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่สามารถบันทึกข้อมูลได้');

			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			$sql ="UPDATE tbl_invoiceheader set inDate=now(),supReciverDate='$supReciverDate',supReciverName='$supReciverName'
			where ID=$headerID;";
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
	if($_SESSION['xxxRole']->{'closeInv'}[3] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 31)
	{

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>40 && $type<=50)//save
{
	if($_SESSION['xxxRole']->{'closeInv'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 41)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else closeDBT($mysqli,2,'TYPE ERROR');

$mysqli->close();
exit();
?>
