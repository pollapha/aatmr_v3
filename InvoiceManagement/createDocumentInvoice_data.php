<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'createDocumentInvoice'}) )
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}
else if($_SESSION['xxxRole']->{'createDocumentInvoice'}[0] == 0)
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
		if(!$re1 = getHeaderByid($mysqli,$cBy)){echo json_encode(array('ch'=>2,'data'=>'Error Code 1'));closeDB($mysqli);}
		$header = jsonRow($re1,true,0);
		$body = array();
		if(count($header) > 0)
		{
			if(!$re2 = dataBodyByHeaderID($mysqli,$header[0]['ID'])) throw new Exception('Error Code 5');
			$body = jsonRow($re2,true,0);
		}
		echo json_encode(array('ch'=>1,'header'=>$header,'body'=>$body));
		closeDB($mysqli);
	}
	else if($type == 3)
	{
		if(!isset($_GET['code'])) {echo '[]';closeDB($mysqli);}
		$code = checkTXT($mysqli,$_GET['code']);
		if($code == '') {echo '[]';closeDB($mysqli);}
		else toArrayStringOne($mysqli->query("SELECT concat(code,' | ',name) from tbl_supplier where code like '%$code%' limit 5;"),1);
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>10 && $type<=20)//insert
{
	if($_SESSION['xxxRole']->{'createDocumentInvoice'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 11)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>doc','obj=>invoiceDate','obj=>issuedDate','obj=>code','obj=>remark'
		));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
		$doc = checkTXT($mysqli,$_POST['obj']['doc']);
		$invoiceDate = checkTXT($mysqli,$_POST['obj']['invoiceDate']);
		$issuedDate = checkTXT($mysqli,$_POST['obj']['issuedDate']);
		$code = checkTXT($mysqli,$_POST['obj']['code']);
		$remark = checkTXT($mysqli,$_POST['obj']['remark']);

		if(strlen($invoiceDate) == 0) closeDBT($mysqli,2,'Invoice Date ไม่สามารถเป็นค่าว่างได้');
		if(strlen($issuedDate) == 0) closeDBT($mysqli,2,'Issued Date ไม่สามารถเป็นค่าว่างได้');
		if(strlen($code) == 0) closeDBT($code,2,'Code ไม่สามารถเป็นค่าว่างได้');

		$code =  explode(' | ',$code);
		if(count($code) == 0) {closeDBT($mysqli,2,'ไม่พบผู้ผลิตใน Master Data');}
		if(!$re1 = checkSup($mysqli,$code[0])) {closeDBT($mysqli,2,'ERROR LINE '.__LINE__.'<br>'.$mysqli->error);}
		if($re1->num_rows == 0 ) {closeDBT($mysqli,2,'ไม่พบผู้ผลิตใน Master Data');}
		$supID = $re1->fetch_array(MYSQLI_ASSOC)['ID'];

		if($doc != '')
		{
			$sql = "UPDATE tbl_invoiceheader_temp set invoiceDate='$invoiceDate',issuedDate='$issuedDate',supID='$supID',
			remark='$remark' where doc='$doc' limit 1;";
			updateHeader($mysqli,$sql,$doc);
		}
		
		$mysqli->autocommit(FALSE);
		try
		{
			$sql = "SELECT func_GenRuningNumber('autogen',0) auto";
			if(!$re1 = $mysqli->query($sql)) closeDBT($mysqli,2,'ERROR LINE '.__LINE__);
			$doc = $re1->fetch_array(MYSQLI_ASSOC)['auto'];

			$sql = "INSERT INTO tbl_invoiceheader_temp(doc,supID,remark,invoiceDate,issuedDate,
			createDate,createDateTime,createByName,createByID)
			values('$doc','$supID','$remark','$invoiceDate','$issuedDate',curdate(),now(),'$fName','$cBy')";
			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			if($mysqli->errno == 1062) throw new Exception('ไม่สามารถสร้างเอกสารพร้อมกันได้');

			if($mysqli->affected_rows == 0)  throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			if(!$re1 = $mysqli->query('SELECT LAST_INSERT_ID() lastID')) throw new Exception('ERROR LINE ');
			$mysqli->commit();
			response($mysqli,$re1->fetch_array(MYSQLI_ASSOC)['lastID']);
		}
		catch( Exception $e )
		{
			$mysqli->rollback();
			closeDBT($mysqli,2,$e->getMessage());
		}

	}
	else if($type == 12)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>doc','obj=>invoiceDate','obj=>issuedDate','obj=>code','obj=>remark'
		,'obj=>invoiceNo'
		));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
		$doc = checkTXT($mysqli,$_POST['obj']['doc']);
		$invoiceDate = checkTXT($mysqli,$_POST['obj']['invoiceDate']);
		$issuedDate = checkTXT($mysqli,$_POST['obj']['issuedDate']);
		$code = checkTXT($mysqli,$_POST['obj']['code']);
		$remark = checkTXT($mysqli,$_POST['obj']['remark']);
		$invoiceNo = checkTXT($mysqli,$_POST['obj']['invoiceNo']);

		if(strlen($invoiceDate) == 0) closeDBT($mysqli,2,'Invoice Date ไม่สามารถเป็นค่าว่างได้');
		if(strlen($issuedDate) == 0) closeDBT($mysqli,2,'Issued Date ไม่สามารถเป็นค่าว่างได้');
		if(strlen($code) == 0) closeDBT($code,2,'Code ไม่สามารถเป็นค่าว่างได้');
		if(strlen($invoiceNo) == 0) closeDBT($code,2,'invoiceNo ไม่สามารถเป็นค่าว่างได้');

		$sql = "SELECT ID from tbl_invoiceheader_temp where doc='$doc' limit 1";

		if(!$re1 = $mysqli->query($sql)) {closeDBT($mysqli,2,'ERROR LINE '.__LINE__.'<br>'.$mysqli->error);}
		if($re1->num_rows == 0 ) {closeDBT($mysqli,2,'Doc ในระบบ');}
		$headerID = $re1->fetch_array(MYSQLI_ASSOC)['ID'];

		$mysqli->autocommit(FALSE);
		try
		{
			$sql = "INSERT INTO tbl_invoicebody_temp(invoiceNo,headerID,updateDateTime,updateByName,updateByID)
			values('$invoiceNo','$headerID',now(),'$fName','$cBy')";
			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			if($mysqli->errno == 1062) throw new Exception('invoice ซ้ำ');

			if($mysqli->affected_rows == 0)  throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			$mysqli->commit();
			response($mysqli,$headerID);
		}
		catch( Exception $e )
		{
			$mysqli->rollback();
			closeDBT($mysqli,2,$e->getMessage());
		}
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>20 && $type<=30)//update
{
	if($_SESSION['xxxRole']->{'createDocumentInvoice'}[2] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 21)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>doc','obj=>invoiceDate','obj=>issuedDate','obj=>code','obj=>remark'
		,'obj=>invoiceNo'
		));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
		$doc = checkTXT($mysqli,$_POST['obj']['doc']);
		$invoiceDate = checkTXT($mysqli,$_POST['obj']['invoiceDate']);
		$issuedDate = checkTXT($mysqli,$_POST['obj']['issuedDate']);
		$code = checkTXT($mysqli,$_POST['obj']['code']);
		$remark = checkTXT($mysqli,$_POST['obj']['remark']);

		if(strlen($invoiceDate) == 0) closeDBT($mysqli,2,'Invoice Date ไม่สามารถเป็นค่าว่างได้');
		if(strlen($issuedDate) == 0) closeDBT($mysqli,2,'Issued Date ไม่สามารถเป็นค่าว่างได้');
		if(strlen($code) == 0) closeDBT($code,2,'Code ไม่สามารถเป็นค่าว่างได้');

		$sql = "SELECT ID from tbl_invoiceheader_temp where doc='$doc' limit 1";
		
		if(!$re1 = $mysqli->query($sql)) {closeDBT($mysqli,2,'ERROR LINE '.__LINE__.'<br>'.$mysqli->error);}
		if($re1->num_rows == 0 ) {closeDBT($mysqli,2,'Doc ในระบบ');}
		$headerID = $re1->fetch_array(MYSQLI_ASSOC)['ID'];

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>30 && $type<=40)//delete
{
	if($_SESSION['xxxRole']->{'createDocumentInvoice'}[3] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 31)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>ID','obj=>headerID'
		));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
		$ID = checkTXT($mysqli,$_POST['obj']['ID']);
		$headerID = checkTXT($mysqli,$_POST['obj']['headerID']);
		$mysqli->autocommit(FALSE);
		try
		{
			$sql = "DELETE from  tbl_invoicebody_temp where ID=$ID and headerID=$headerID limit 1";
			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			if($mysqli->affected_rows == 0)  throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			$mysqli->commit();
			response($mysqli,$headerID);
		}
		catch( Exception $e )
		{
			$mysqli->rollback();
			closeDBT($mysqli,2,$e->getMessage());
		}
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>40 && $type<=50)//save
{
	if($_SESSION['xxxRole']->{'createDocumentInvoice'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 41)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>doc','obj=>invoiceDate','obj=>issuedDate','obj=>code','obj=>remark'
		,'obj=>invoiceNo'
		));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
		$doc = checkTXT($mysqli,$_POST['obj']['doc']);

		$sql = "SELECT ID from tbl_invoiceheader_temp where doc='$doc' limit 1";
		if(!$re1 = $mysqli->query($sql)) {closeDBT($mysqli,2,'ERROR LINE '.__LINE__.'<br>'.$mysqli->error);}
		if($re1->num_rows == 0 ) {closeDBT($mysqli,2,'Doc ในระบบ');}
		$headerID = $re1->fetch_array(MYSQLI_ASSOC)['ID'];

		$sql = "SELECT ID from tbl_invoicebody_temp where headerID=$headerID limit 1";
		if(!$re1 = $mysqli->query($sql)) {closeDBT($mysqli,2,'ERROR LINE '.__LINE__.'<br>'.$mysqli->error);}
		if($re1->num_rows == 0 ) {closeDBT($mysqli,2,'กรุณาคีย์เลขที่อินวอย');}

		$mysqli->autocommit(FALSE);
		try
		{
			$sql = "INSERT INTO tbl_invoiceheader(doc,supID,truckLicense,truckType,driverName,phone,remark,
			invoiceDate,issuedDate,createDate,createDateTime,createByName,createByID)
			SELECT func_GenRuningNumber('inv',0),supID,truckLicense,truckType,driverName,phone,remark,
			invoiceDate,issuedDate,curdate(),now(),createByName,createByID from tbl_invoiceheader_temp where ID=$headerID limit 1;";
			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			if($mysqli->affected_rows == 0)  throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			if(!$re1 = $mysqli->query('SELECT LAST_INSERT_ID() lastID')) throw new Exception('ERROR LINE ');
			$lastID = $re1->fetch_array(MYSQLI_ASSOC)['lastID'];

			$sql = "INSERT INTO tbl_invoicebody(invoiceNo,headerID,updateDateTime,updateByName,updateByID)
			SELECT invoiceNo,$lastID,updateDateTime,updateByName,updateByID from tbl_invoicebody_temp
			where headerID=$headerID";
			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			if($mysqli->affected_rows == 0)  throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);

			$sql = "DELETE from tbl_invoiceheader_temp where ID=$headerID limit 1;";
			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			if($mysqli->affected_rows == 0)  throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);

			$sql = "DELETE from tbl_invoicebody_temp where headerID=$headerID";
			if(!$mysqli->query($sql)) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
			if($mysqli->affected_rows == 0)  throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);

			$mysqli->commit();
			closeDBT($mysqli,1,'OK');
		}
		catch( Exception $e )
		{
			$mysqli->rollback();
			closeDBT($mysqli,2,$e->getMessage());
		}

		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else closeDBT($mysqli,2,'TYPE ERROR');

/* select doc,invoiceDate,issuedDate,supID,truckLicense,truckType,driverName,phone,remark,invoiceNo,headerID,createByID 
from tbl_invoiceheader_temp t1 left join tbl_invoicebody_temp t2 on t1.ID=t2.headerID
where createByID=1; */
function dataHeaderByID($mysqli,$ID)
{
	return $mysqli->query("SELECT t1.ID,doc,invoiceDate,issuedDate,supID,concat(t2.code,' | ',t2.name) code,t1.remark
	from tbl_invoiceheader_temp t1 left join tbl_supplier t2 on t1.supID=t2.ID
	where t1.ID=$ID limit 1;");
}
function dataHeaderByDoc($mysqli,$doc)
{
	return $mysqli->query("SELECT t1.ID,t1.doc,invoiceDate,issuedDate,supID,concat(t2.code,' | ',t2.name) code,t1.remark
	from tbl_invoiceheader_temp t1 left join tbl_supplier t2 on t1.supID=t2.ID
	where t1.doc='$doc' limit 1;");
}
function dataBodyByHeaderID($mysqli,$headerID)
{
	return $mysqli->query("SELECT ID,headerID,invoiceNo from tbl_invoicebody_temp where headerID=$headerID;");
}

function checkSup($mysqli,$code)
{
	return $mysqli->query("SELECT ID from tbl_supplier where code='$code' limit 1;");
}

function getHeaderByid($mysqli,$cBy)
{
	return $mysqli->query("SELECT t1.ID,doc,invoiceDate,issuedDate,supID,concat(t2.code,' | ',t2.name) code,t1.remark
	from tbl_invoiceheader_temp t1 left join tbl_supplier t2 on t1.supID=t2.ID
	where t1.createByID=$cBy limit 1;");
}

function response($mysqli,$ID)
{
	if(!$re1 = dataHeaderByID($mysqli,$ID)){echo json_encode(array('ch'=>2,'data'=>'Error Code 1'));closeDB($mysqli);}
	$header = jsonRow($re1,true,0);
	$body = array();
	if(count($header) > 0)
	{
		if(!$re2 = dataBodyByHeaderID($mysqli,$header[0]['ID'])) throw new Exception('Error Code 5');
		$body = jsonRow($re2,true,0);
	}
	echo json_encode(array('ch'=>1,'header'=>$header,'body'=>$body));
	closeDB($mysqli);
}

function updateHeader($mysqli,$sqlUpdate,$doc)
{
	$sql = "SELECT ID from tbl_invoiceheader_temp where doc='$doc' limit 1";
	
	if(!$re1 = $mysqli->query($sql)) {closeDBT($mysqli,2,'ERROR LINE '.__LINE__.'<br>'.$mysqli->error);}
	if($re1->num_rows == 0 ) {closeDBT($mysqli,2,'Doc ในระบบ');}
	$headerID = $re1->fetch_array(MYSQLI_ASSOC)['ID'];

	$mysqli->autocommit(FALSE);
	try
	{
		if(!$mysqli->query($sqlUpdate)) throw new Exception('ERROR LINE '.__LINE__.'<br>'.$mysqli->error);
		if($mysqli->affected_rows == 0)  throw new Exception('ไม่พบการอัพเดทข้อมูล');

		$mysqli->commit();
		response($mysqli,$headerID);
	}
	catch( Exception $e )
	{
		$mysqli->rollback();
		closeDBT($mysqli,2,$e->getMessage());
	}

}

$mysqli->close();
exit();
?>
