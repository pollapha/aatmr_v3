<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'invoiceTransaction'}) )
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}
else if($_SESSION['xxxRole']->{'invoiceTransaction'}[0] == 0)
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
		$chkPOST = checkParams($_POST,array('obj','obj=>date1','obj=>date2'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		$date1 = checkTXT($mysqli,$_POST['obj']['date1']);
		$date2 = checkTXT($mysqli,$_POST['obj']['date2']);
		if(strlen($date1) == 0) closeDBT($mysqli,2,'Start Date ไม่สามารถเป็นค่าว่างได้');
		if(strlen($date2) == 0) closeDBT($mysqli,2,'End Date ไม่สามารถเป็นค่าว่างได้');
		$date1 = explode(' ',$date1)[0];
		$date2 = explode(' ',$date2)[0];
		if(!$re1 = getData($mysqli,$date1,$date2)) {closeDBT($mysqli,2,'ERROR LINE '.__LINE__.'<br>'.$mysqli->error);}
		closeDBT($mysqli,1,jsonRow($re1,true,0));
	}
	else if($type == 5)
	{
		if(!isset($_GET['code'])) {echo '[]';closeDB($mysqli);}
		$code = checkTXT($mysqli,$_GET['code']);
		if($code == '') {echo '[]';closeDB($mysqli);}
		else toArrayStringOne($mysqli->query("SELECT code from tbl_supplier where code like '%$code%' limit 5;"),1);
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>10 && $type<=20)//insert
{
	if($_SESSION['xxxRole']->{'invoiceTransaction'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
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
	if($_SESSION['xxxRole']->{'invoiceTransaction'}[2] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 21)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>bodyID','obj=>headerID','obj=>status',
		'obj=>invoiceDate','obj=>issuedDate','obj=>invoiceNo','obj=>supplierCode','obj=>truckLicense',
		'obj=>driverName','obj=>phone','obj=>remark','obj=>date1','obj=>date2','obj=>supReciverName','obj=>supReciverDate','obj=>remarkDetail'
		));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
	
		$headerID = checkINT($mysqli,$_POST['obj']['headerID']);
		$bodyID = checkINT($mysqli,$_POST['obj']['bodyID']);
		$status = checkTXT($mysqli,$_POST['obj']['status']);
		$invoiceNo = checkTXT($mysqli,$_POST['obj']['invoiceNo']);
		
		$truckLicense = checkTXT($mysqli,$_POST['obj']['truckLicense']);
		$remark = checkTXT($mysqli,$_POST['obj']['remark']);
		$driverName = checkTXT($mysqli,$_POST['obj']['driverName']);
		$phone = checkTXT($mysqli,$_POST['obj']['phone']);
		$invoiceDate = checkTXT($mysqli,$_POST['obj']['invoiceDate']);
		$issuedDate = checkTXT($mysqli,$_POST['obj']['issuedDate']);
		$supplierCode = checkTXT($mysqli,$_POST['obj']['supplierCode']);
		$supReciverName = checkTXT($mysqli,$_POST['obj']['supReciverName']);
		$supReciverDate = checkTXT($mysqli,$_POST['obj']['supReciverDate']);
		$remarkDetail = checkTXT($mysqli,$_POST['obj']['remarkDetail']);
		
		if(strlen($supReciverDate) == 0)
		{
			$supReciverDate = "null";
		}
		else
		{
			$supReciverDate = "'$supReciverDate'";
		}

		
		$date1 = checkTXT($mysqli,$_POST['obj']['date1']);
		$date2 = checkTXT($mysqli,$_POST['obj']['date2']);
		if(strlen($date1) == 0) closeDBT($mysqli,2,'Start Date ไม่สามารถเป็นค่าว่างได้');
		if(strlen($date2) == 0) closeDBT($mysqli,2,'End Date ไม่สามารถเป็นค่าว่างได้');
		$date1 = explode(' ',$date1)[0];
		$date2 = explode(' ',$date2)[0];

		if(strlen($headerID) == 0) closeDBT($mysqli,2,'headerID = 0');
		if(strlen($bodyID) == 0) closeDBT($mysqli,2,'bodyID = 0');
		if(strlen($status) == 0) closeDBT($mysqli,2,'status ไม่ควรเป็นค่าว่าง');
		if(strlen($supplierCode) == 0) closeDBT($mysqli,2,'supplierCode ไม่ควรเป็นค่าว่าง');
		if(strlen($invoiceNo) == 0) closeDBT($mysqli,2,'invoiceNo ไม่ควรเป็นค่าว่าง');
		
		$sql = "SELECT ID,status from tbl_invoicebody where ID=$bodyID and headerID=$headerID limit 1;";
		if(!$re1 = $mysqli->query($sql)) closeDBT($mysqli,2,'ERROR LINE '.__LINE__);
		if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูล '.$bodyID);

		$sql = "SELECT ID from tbl_supplier where code='$supplierCode' limit 1;";
		if(!$re1 = $mysqli->query($sql)) closeDBT($mysqli,2,'ERROR LINE '.__LINE__);
		if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูล '.$supplierCode);
		$supID = $re1->fetch_array(MYSQLI_ASSOC)['ID'];

		$mysqli->autocommit(FALSE);
		try
		{
			$sql = "UPDATE tbl_invoicebody set invoiceNo='$invoiceNo',status='$status'
			where ID=$bodyID and headerID=$headerID limit 1";
			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.$mysqli->error);
			if($mysqli->affected_rows > 0)
			{
				$sql = "UPDATE tbl_invoicebody set updateDateTime=now(),updateByName='$fName',updateByID=$cBy
				where ID=$bodyID and headerID=$headerID limit 1";
			   	if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.$mysqli->error);
			   	if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			}

			$sql = "UPDATE tbl_invoiceheader set truckLicense='$truckLicense',remark='$remark',driverName='$driverName'
			,phone='$phone',invoiceDate='$invoiceDate',issuedDate='$issuedDate',supID='$supID',
			supReciverDate=$supReciverDate,supReciverName='$supReciverName',remarkDetail='$remarkDetail'
			where ID=$headerID limit 1";
			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.$mysqli->error);
			if($mysqli->affected_rows > 0)
			{
				$sql = "UPDATE tbl_invoiceheader set updateDateTime=now(),updateByName='$fName',updateByID=$cBy
				where ID=$headerID limit 1";
			   	if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.$mysqli->error);
			   	if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			}

			if(!$re1 = getData($mysqli,$date1,$date2)) {closeDBT($mysqli,2,'ERROR LINE '.__LINE__.'<br>'.$mysqli->error);}
			$mysqli->commit();
			closeDBT($mysqli,1,jsonRow($re1,true,0));
		}
		catch( Exception $e )
		{
			$mysqli->rollback();
			closeDBT($mysqli,2,$e->getMessage());
		}


	}
	else if($type == 22)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>invoiceNo','obj=>headerID','obj=>date1','obj=>date2'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
	
		$headerID = checkINT($mysqli,$_POST['obj']['headerID']);
		$invoiceNo = checkTXT($mysqli,$_POST['obj']['invoiceNo']);
		if(strlen($headerID) == 0) closeDBT($mysqli,2,'headerID = 0');
		if(strlen($invoiceNo) == 0) closeDBT($mysqli,2,'invoiceNo ไม่ควรเป็นค่าว่าง');
		$date1 = checkTXT($mysqli,$_POST['obj']['date1']);
		$date2 = checkTXT($mysqli,$_POST['obj']['date2']);
		if(strlen($date1) == 0) closeDBT($mysqli,2,'Start Date ไม่สามารถเป็นค่าว่างได้');
		if(strlen($date2) == 0) closeDBT($mysqli,2,'End Date ไม่สามารถเป็นค่าว่างได้');
		$date1 = explode(' ',$date1)[0];
		$date2 = explode(' ',$date2)[0];
		$mysqli->autocommit(FALSE);
		try
		{
			$sql = "INSERT INTO tbl_invoicebody(invoiceNo,headerID,updateDateTime,updateByName,updateByID)
			values('$invoiceNo','$headerID',now(),'$fName','$cBy')";
			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			if($mysqli->errno == 1062) throw new Exception('invoice ซ้ำ');

			if($mysqli->affected_rows == 0)  throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			if(!$re1 = getData($mysqli,$date1,$date2)) {closeDBT($mysqli,2,'ERROR LINE '.__LINE__.'<br>'.$mysqli->error);}
			$mysqli->commit();
			closeDBT($mysqli,1,jsonRow($re1,true,0));
		}
		catch( Exception $e )
		{
			$mysqli->rollback();
			closeDBT($mysqli,2,$e->getMessage());
		}
	}
	else if($type == 25)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>headerID','obj=>bodyID','obj=>date1','obj=>date2'
		));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		$headerID = checkINT($mysqli,$_POST['obj']['headerID']);
		$bodyID = checkINT($mysqli,$_POST['obj']['bodyID']);

		$date1 = checkTXT($mysqli,$_POST['obj']['date1']);
		$date2 = checkTXT($mysqli,$_POST['obj']['date2']);
		if(strlen($date1) == 0) closeDBT($mysqli,2,'Start Date ไม่สามารถเป็นค่าว่างได้');
		if(strlen($date2) == 0) closeDBT($mysqli,2,'End Date ไม่สามารถเป็นค่าว่างได้');
		$date1 = explode(' ',$date1)[0];
		$date2 = explode(' ',$date2)[0];

		if(strlen($headerID) == 0) closeDBT($mysqli,2,'headerID = 0');
		if(strlen($bodyID) == 0) closeDBT($mysqli,2,'bodyID = 0');

		$sql = "SELECT ID,status from tbl_invoicebody where ID=$bodyID and headerID=$headerID limit 1;";
		if(!$re1 = $mysqli->query($sql)) closeDBT($mysqli,2,'ERROR LINE '.__LINE__);
		if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูล '.$ID);
		$row1 = $re1->fetch_array(MYSQLI_ASSOC);
		if($row1['status'] == 'CANCEL') closeDBT($mysqli,2,'status ต้องยกเลิกอยู่แล้ว');
		$mysqli->autocommit(FALSE);
		try
		{
			$sql = "UPDATE tbl_invoicebody set status='CANCEL' where ID=$bodyID and headerID=$headerID and status not in('CANCEL') limit 1";
			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.$mysqli->error);
			if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);

			$sql = "UPDATE tbl_invoicebody set updateDateTime=now(),updateByName='$fName',updateByID=$cBy
			 where ID=$bodyID and headerID=$headerID limit 1";
			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.$mysqli->error);
			if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			if(!$re1 = getData($mysqli,$date1,$date2)) {closeDBT($mysqli,2,'ERROR LINE '.__LINE__.'<br>'.$mysqli->error);}
			$mysqli->commit();
			closeDBT($mysqli,1,jsonRow($re1,true,0));
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
	if($_SESSION['xxxRole']->{'invoiceTransaction'}[3] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 31)
	{

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>40 && $type<=50)//save
{
	if($_SESSION['xxxRole']->{'invoiceTransaction'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 41)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else closeDBT($mysqli,2,'TYPE ERROR');

function getData($mysqli,$date1,$date2,$status = '')
{
	$sql = "SELECT t1.ID headerID,t2.ID bodyID,t2.invoiceNo,t2.status,t1.invoiceDate,t3.code supplierCode,t3.name supplierName
	,t1.issuedDate,t1.truckLicense,t1.remark,t1.doc,t1.inDate,t1.outDate,t1.supReciverName,t1.supReciverDate,t1.remarkDetail,
t1.driverName,t1.phone,t1.createDateTime,t1.createByName,t2.updateDateTime,t2.updateByName
from tbl_invoiceheader t1 left join tbl_invoicebody t2 on t1.ID=t2.headerID
left join tbl_supplier t3 on t1.supID=t3.ID
where t1.issuedDate between '$date1' and '$date2';";

	return $mysqli->query($sql);
}
$mysqli->close();
exit();
?>
