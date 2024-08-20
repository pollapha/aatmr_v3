<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'ProjectMaster'}) )
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}
else if($_SESSION['xxxRole']->{'ProjectMaster'}[0] == 0)
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
	else if($type == 2)
	{
		$sql = "SELECT t1.projectName value,t1.ID id
		from tbl_project_master t1 ;";
		$re = sqlError($mysqli,__LINE__,$sql,1);	
		$row = $re->fetch_all(MYSQLI_ASSOC);

		$json = array_column($row,'value');

		closeDBT($mysqli,1,$json);
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>10 && $type<=20)//insert
{
	if($_SESSION['xxxRole']->{'ProjectMaster'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 11)
	{
		$dataParams = array(
			'obj',			
			'obj=>projectName:s:0:3',
		);

		$chkPOST = checkParamsAndDelare($_POST,$dataParams,$mysqli);
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		$mysqli->autocommit(FALSE);
		try
		{
			$dataAr = array(array
			(
				'projectName'=>$projectName,
				'createDatetime'=>'sql=now()',
				'createBy'=>$cBy
			));

			insert($mysqli,'tbl_project_master',$dataAr,'ไม่สามารถบันทึกข้อมูลได้');
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
	if($_SESSION['xxxRole']->{'ProjectMaster'}[2] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 21)
	{
		$dataParams = array(
			'obj',			
			'obj=>projectName:s:0:3',
			'obj=>ID:s:0:1',			
		);

		$chkPOST = checkParamsAndDelare($_POST,$dataParams,$mysqli);
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		$mysqli->autocommit(FALSE);
		try
		{
			$dataAr = array(array
			(
				'projectName'=>$projectName,
			));

			// checkBeforeUpdate($mysqli,$truckLicense,$UOM_Name_En);
			update($mysqli,'tbl_project_master',$ID,$dataAr,$cBy,'ไม่สามารถบันทึกข้อมูลได้');

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
	if($_SESSION['xxxRole']->{'ProjectMaster'}[3] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 31)
	{

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>40 && $type<=50)//save
{
	if($_SESSION['xxxRole']->{'ProjectMaster'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 41)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else closeDBT($mysqli,2,'TYPE ERROR');

function select($mysqli)
{
	$sql = "SELECT t1.projectName,t1.createDatetime,t1.updateDatetime
	,concat(t2.user_fName,' ',t2.user_lname) createBy
	,concat(t3.user_fName,' ',t3.user_lname)  updateBy,
	t1.ID
	from tbl_project_master t1 
	left join tbl_user t2 on t1.createBy=t2.user_id
	left join tbl_user t3 on t1.updateBy=t3.user_id
	order by t1.ID desc;";
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
