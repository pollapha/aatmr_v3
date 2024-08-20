<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'DriverMaster'}) )
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}
else if($_SESSION['xxxRole']->{'DriverMaster'}[0] == 0)
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
		$re = select($mysqli);
		// var_dump(jsonRow($re,true,0));
		closeDBT($mysqli,1,jsonRow($re,true,0));
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>10 && $type<=20)//insert
{
	if($_SESSION['xxxRole']->{'DriverMaster'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 11)
	{
		$dataParams = array(
			'obj',			
			'obj=>fNameTH:s:0:2',
			'obj=>lNameTH:s:0:2',
			'obj=>phone:s:0:2'
		);

		$chkPOST = checkParamsAndDelare($_POST,$dataParams,$mysqli);
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		$mysqli->autocommit(FALSE);
		try
		{
			$dataAr = array(array
			(
				'fNameTH'=>$fNameTH,
				'lNameTH'=>$lNameTH,
				'phone'=>$phone,
				'createDatetime'=>'sql=now()',
				'createBy'=>$cBy
			));
			// var_dump($dataAr);exit();
			// checkBeforeInsert($mysqli,$UOM_Name_En);
			insert($mysqli,'tbl_driver',$dataAr,'ไม่สามารถบันทึกข้อมูลได้');
			$re = select($mysqli);
			$mysqli->commit();
			closeDBT($mysqli,1,jsonRow($re,true,0));
		}
		catch( Exception $e )
		{
			$mysqli->rollback();
			closeDBT($mysqli,2,$e->getMessage());
		}
	}
	else if($type == 12)
	{

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>20 && $type<=30)//update
{
	if($_SESSION['xxxRole']->{'DriverMaster'}[2] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 21)
	{
		$dataParams = array(
			'obj',			
			'obj=>Drive_ID:s:0:1',
			'obj=>fNameTH:s:0:2',
			'obj=>lNameTH:s:0:2',
			'obj=>phone:s:0:2'
		);

		$chkPOST = checkParamsAndDelare($_POST,$dataParams,$mysqli);
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		$mysqli->autocommit(FALSE);
		try
		{
			$dataAr = array(array
			(
				'fNameTH'=>$fNameTH,
				'lNameTH'=>$lNameTH,
				'phone'=>$phone
			));

			// checkBeforeUpdate($mysqli,$truckLicense,$UOM_Name_En);
			update($mysqli,'tbl_driver',$Drive_ID,$dataAr,$cBy,'ไม่สามารถบันทึกข้อมูลได้');

			$re = select($mysqli);
			$mysqli->commit();
			closeDBT($mysqli,1,jsonRow($re,true,0));
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
	if($_SESSION['xxxRole']->{'DriverMaster'}[3] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 31)
	{
		$dataParams = array(
			'obj',			
			'obj=>upload_text:s:0:1'
		);
		$chkPOST = checkParamsAndDelare($_POST,$dataParams,$mysqli);
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		$dataAR_line = explode('\n', $upload_text);
		// var_dump($dataAR_line);
		for ($i=0; $i < sizeof($dataAR_line); $i++) { 
			$dataAR_col = preg_split('/\s+/', $dataAR_line[$i]);
			$dataAr = array(array
			(
				'fNameTH'=>$dataAR_col[0],
				'lNameTH'=>$dataAR_col[1],
				'phone'=>$dataAR_col[2],
				'createDatetime'=>'sql=now()',
				'createBy'=>$cBy
			));
			insert($mysqli,'tbl_driver',$dataAr,'ไม่สามารถบันทึกข้อมูลได้');
		}
		$re = select($mysqli);
		$mysqli->commit();
		closeDBT($mysqli,1,jsonRow($re,true,0));
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>40 && $type<=50)//save
{
	if($_SESSION['xxxRole']->{'DriverMaster'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 41)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else closeDBT($mysqli,2,'TYPE ERROR');

function select($mysqli)
{
	$sql = "SELECT
				ID as Drive_ID,
				fNameTH ,
				lNameTH ,
				phone ,
				status,
				createDatetime ,
				createBy ,
				updateDatetime ,
				updateBy
			FROM
				tbl_driver td;";
	return sqlError($mysqli,__LINE__,$sql,1);	
}

function insert($mysqli,$tableName,$data,$error)
{
	$sql = "INSERT into $tableName".prepareInsert($data);
	sqlError($mysqli,__LINE__,$sql,1);
	if($mysqli->affected_rows == 0)
	{
		throw new Exception($error);
	}
}

function update($mysqli,$tableName,$ID,$data,$cBy,$error)
{
	$sql = "UPDATE $tableName SET ".prepareUpdate($data)." where ID = '$ID' limit 1";

	sqlError($mysqli,__LINE__,$sql,1);
	if($mysqli->affected_rows == 0) 
	{
		throw new Exception('ไม่พบการเปลียนแปลงข้อมูล');
	}

	$dataAr = array(array
	(
		'updateDatetime'=>'sql=now()',
		'updateBy'=>$cBy,
	));
	$sql = "UPDATE $tableName SET ".prepareUpdate($dataAr)." where ID = '$ID' limit 1";
	sqlError($mysqli,__LINE__,$sql,1,1);
	if($mysqli->affected_rows == 0) 
	{
		throw new Exception($error);
	}
}

$mysqli->close();
exit();
?>
