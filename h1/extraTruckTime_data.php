<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'extraTruckTime'}) )
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}
else if($_SESSION['xxxRole']->{'extraTruckTime'}[0] == 0)
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
		$dataParams = array(
			'obj',
			'obj=>truck_license:s:0',
			'obj=>start_datetime:s:0:1',
			'obj=>stop_datetime:s:0:1',
		);
		
		$chkPOST = checkParamsAndDelare($_POST,$dataParams,$mysqli);
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		if(strlen($truck_license) >5)
		{
			$truck_license = " and truckLicense='$truck_license'";
		}
		else
		{
			$truck_licens='';
		}

		$mysqli->autocommit(FALSE);
		try
		{
			$sql = "SELECT id,trucklicense AS truck_license,
						DATE(Start_Datetime) AS start_date,
						TIME(Start_Datetime) AS start_time,
						DATE(Stop_Datetime) AS stop_date,
						TIME(Stop_Datetime) AS stop_time,
						create_datetime AS create_date,create_by,
						update_datetime AS update_date,update_by
					FROM tbl_truck_extra_time where date_format(Start_Datetime,'%Y-%m-%d') >=date_format('$start_datetime','%Y-%m-%d')  
					and date_format(Start_Datetime,'%Y-%m-%d') <= date_format('$stop_datetime','%Y-%m-%d') $truck_license

					";
					// echo $sql;
			$re1 = sqlError($mysqli,__LINE__,$sql,1);
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
else if($type>10 && $type<=20)//insert
{
	if($_SESSION['xxxRole']->{'extraTruckTime'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 11)
	{

	}
	else if($type == 12)
	{
		$dataParams = array(
			'obj',
			'obj=>truck_license:s:0:1',
			'obj=>start_datetime:s:0:1',
			'obj=>stop_datetime:s:0:1',
		);
		
		$chkPOST = checkParamsAndDelare($_POST,$dataParams,$mysqli);
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
		$mysqli->autocommit(FALSE);
		try
		{
			$sql = "SELECT truckLicense, Start_Datetime, Stop_Datetime
					FROM tbl_truck_extra_time
					WHERE truckLicense = '$truck_license'
					AND ('$start_datetime' <= Stop_Datetime AND '$stop_datetime' >= Start_Datetime);";

			$re1 = sqlError($mysqli,__LINE__,$sql,1);
			if($re1->num_rows > 0) // รับค่า id ล่าสุดของ tbl_transaction
			{
				throw new Exception('พบข้อมูล truckLicense ใน tbl_truck_extra_time ที่เวลาเดียวกัน');
			}

			$sql = "INSERT INTO tbl_truck_extra_time(truckLicense, Start_Datetime, Stop_Datetime, Create_Datetime, Create_By)
					values('$truck_license','$start_datetime','$stop_datetime',NOW(),$cBy)";

			sqlError($mysqli, __LINE__, $sql, 1);
			if($mysqli->affected_rows == 0)
			{
				throw new Exception('ไม่สามารถอัพเดทข้อมูลได้ tbl_truck_extra_time'.$sql);
			}

			$mysqli->commit();
			closeDBT($mysqli,1,'OK');
		}
		catch( Exception $e )
		{
			$mysqli->rollback();
			closeDBT($mysqli,2,$e->getMessage());
		}

	}
}
else if($type>20 && $type<=30)//update
{
	if($_SESSION['xxxRole']->{'extraTruckTime'}[2] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 21)
	{
		
	}
	else if($type == 22)
	{
		$dataParams = array(
			'obj',
			'obj=>id:s:0:1',
			'obj=>truck_license:s:0:1',
			'obj=>start_datetime:s:0:1',
			'obj=>stop_datetime:s:0:1',
		);
		
		$chkPOST = checkParamsAndDelare($_POST,$dataParams,$mysqli);
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
		$mysqli->autocommit(FALSE);
		try
		{

			$sql = "SELECT truckLicense, Start_Datetime, Stop_Datetime
					FROM tbl_truck_extra_time
					WHERE truckLicense = '$truck_license'
					AND id <> $id AND ('$start_datetime' <= Stop_Datetime AND '$stop_datetime' >= Start_Datetime);";

			$re1 = sqlError($mysqli,__LINE__,$sql,1);
			if($re1->num_rows > 0) // รับค่า id ล่าสุดของ tbl_transaction
			{
				throw new Exception('พบข้อมูล truckLicense ใน tbl_truck_extra_time ที่เวลาเดียวกัน');
			}

			$sql = "UPDATE tbl_truck_extra_time
					SET truckLicense = '$truck_license' , Start_Datetime = '$start_datetime' , 
						Stop_Datetime = '$stop_datetime' , Update_Datetime = NOW() , Update_By = $cBy
					WHERE ID = $id";

			sqlError($mysqli, __LINE__, $sql, 1);
			if($mysqli->affected_rows == 0)
			{
				throw new Exception('ไม่สามารถอัพเดทข้อมูลได้ tbl_truck_extra_time'.$sql);
			}

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
else if($type>30 && $type<=40)//delete
{
	if($_SESSION['xxxRole']->{'extraTruckTime'}[3] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 31)
	{

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>40 && $type<=50)//save
{
	if($_SESSION['xxxRole']->{'extraTruckTime'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 41)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else closeDBT($mysqli,2,'TYPE ERROR');

$mysqli->close();
exit();
?>
