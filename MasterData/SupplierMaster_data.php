<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'SupplierMaster'}) )
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}
else if($_SESSION['xxxRole']->{'SupplierMaster'}[0] == 0)
{
	echo "{ch:9,data:'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้'}";
	exit();
}

if(!isset($_REQUEST['type'])) {echo json_encode(array('ch'=>2,'data'=>'ข้อมูลไม่ถูกต้อง'));exit();}
$cBy = $_SESSION['xxxID'];
$fName = $_SESSION['xxxFName'];
$type  = intval($_REQUEST['type']);


include('../php/connection.php');
require_once('../Classes/PHPExcel.php');
if($type<=10)//data
{
	if($type == 1)
	{
		$re = select($mysqli);
		closeDBT($mysqli,1,jsonRow($re,true,0));
	}
	else if($type == 2)
	{
		$re = getDataGeoSupplier($mysqli);
		closeDBT($mysqli,1,jsonRow($re,true,0));
	}
	else if($type == 3)
	{
		if(!isset($_GET['filter'])) {echo '[]';closeDB($mysqli);}
		$value = !isset($_GET['filter']['value']) ? '' : $mysqli->real_escape_string(trim(strtoupper($_GET['filter']['value'])));
		if($value == '') echo '[]';
		else toArrayStringOne($mysqli->query("SELECT code from tbl_supplier where code like '%$value%' limit 5"),1);
	}
	else if($type == 4)
	{
		$re = select($mysqli);

		$data = jsonRow($re,true,0);

		$header = [
		'supplier_code',
		'supplier_name',
		'createDatetime',
		'checkGeo',
		'createBy',
		'updateDatetime',
		'updateBy'];
		array_unshift($data,$header);

		$excel = new PHPExcel();
		PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
		$excel->setActiveSheetIndex(0);		
		$objWorksheet = $excel->getActiveSheet();		
		$objWorksheet->fromArray(
			$data
		);	 

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="ดาวน์โหลด.xlsx"');
		header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');

		$objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$objWriter->save('php://output');
		
	}

	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>10 && $type<=20)//insert
{
	if($_SESSION['xxxRole']->{'SupplierMaster'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
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
	if($_SESSION['xxxRole']->{'SupplierMaster'}[2] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 21)
	{
		
	}
	else if($type == 22)
	{
		if(!isset($_POST['obj'])) {echo json_encode(array('ch'=>2,'data'=>'ข้อมูลไม่ถูกต้อง 1'));closeDB($mysqli);}
		$supplier_code = !isset($_POST['obj']['supplier_code']) ? '' : $mysqli->real_escape_string(trim(strtoupper($_POST['obj']['supplier_code'])));
		$data = !isset($_POST['obj']['polygon']) ? 0 : $mysqli->real_escape_string(trim(strtoupper($_POST['obj']['polygon'])));

		if(strlen($supplier_code) == 0 || strlen($data) == 0) {echo json_encode(array('ch'=>2,'data'=>'ข้อมูลไม่ถูกต้อง 2'));closeDB($mysqli);}

		$sql = "
		UPDATE tbl_supplier SET geo=ST_GeomFromText('$data'),updateBy='$fName',updateDatetime=now() where code='$supplier_code' limit 1;
		";
		$mysqli->autocommit(FALSE);

		try
		{
			if(!$mysqli->query($sql)) throw new Exception($mysqli->error); 
			if($mysqli->affected_rows == 0) throw new Exception('ไม่สามารถบันทึกข้อมูลได้โปรดลองอึกครั้ง'); 
			$mysqli->commit();

			closeDBT($mysqli,1,'');
		}
		catch( Exception $e )
		{
  			$mysqli->rollback();
  			echo json_encode(array('ch'=>2,'data'=>$e->getMessage()));
		}
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>30 && $type<=40)//delete
{
	if($_SESSION['xxxRole']->{'SupplierMaster'}[3] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 31)
	{

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>40 && $type<=50)//save
{
	if($_SESSION['xxxRole']->{'SupplierMaster'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 41)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else closeDBT($mysqli,2,'TYPE ERROR');


function select($mysqli)
{
	$sql = "SELECT code supplier_code,name supplier_name,if(st_AsText(geo)='POINT(0 0)','ยังไม่ตีกรอบ','OK')checkGeo,createDatetime,createBy,updateDatetime,updateBy
	from tbl_supplier;";
	return sqlError($mysqli,__LINE__,$sql,1);	
}

function getDataGeoSupplier($mysqli)
{
	$sql = "SELECT t1.code supplier_code,t1.name supplier_name,
	ST_AsGeoJSON(t1.geo)supplier_geo,ST_AsGeoJSON(ST_Centroid(t1.geo))supplier_geoCenter
	from tbl_supplier t1;";
	return sqlError($mysqli,__LINE__,$sql);
}


$mysqli->close();
exit();
?>
