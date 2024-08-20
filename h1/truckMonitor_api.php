<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');

if(!isset($_REQUEST['type'])) {echo json_encode(array('ch'=>2,'data'=>'ข้อมูลไม่ถูกต้อง'));exit();}
$type  = intval($_REQUEST['type']);


include('../php/connection.php');
include('../common/commonFunc.php');
require_once('../Classes/PHPExcel.php');
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

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>10 && $type<=20)//insert
{
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
	if($type == 21)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>Load_ID','obj=>StopSequenceNumber','obj=>monitorID',
		'obj=>acDocTime','obj=>acOutDocTime'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
		
		$Load_ID = checkINT($mysqli,$_POST['obj']['Load_ID']);
		$StopSequenceNumber = checkINT($mysqli,$_POST['obj']['StopSequenceNumber']);
		$monitorID = checkINT($mysqli,$_POST['obj']['monitorID']);

		$acDocTime = checkTXT($mysqli,$_POST['obj']['acDocTime']);
		$acOutDocTime = checkTXT($mysqli,$_POST['obj']['acOutDocTime']);

		$acDocTime = strlen($acDocTime) == 0 ? '00:00':$acDocTime;
		$acOutDocTime = strlen($acOutDocTime) == 0 ? '00:00':$acOutDocTime;

		
		$sql = "SELECT t2.Status,t2.StopSequenceNumber,t1.truckLicense
		from tbl_204header_api t1
		left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID
		left join tbl_truckmonitor t3 on t2.Load_ID=t3.Load_ID
		where t1.Load_ID=$Load_ID and t2.StopSequenceNumber=$StopSequenceNumber and t3.ID=$monitorID limit 1;";
		$re1 = sqlError($mysqli,__LINE__,$sql);
		if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูลในระบบ');
		$row = $re1->fetch_array(MYSQLI_ASSOC);
		$oldStatus = $row['Status'];
		$truckLicense = $row['truckLicense'];
		
		if(strlen($truckLicense)<4)
		{
			closeDBT($mysqli,2,'กรุณากรอกทะเบียนก่อนถึงสามารถคีย์เวลาได้');
		}

		if($row['StopSequenceNumber']*1 == 1)
		{
			if($row['Status'] == 'PENDING')
			{
				if($acDocTime == '00:00')
				{
					closeDBT($mysqli,2,'กรุณาป้อนเวลาเข้าจริง');
				}

			}
			else if($row['Status'] == 'ARRIVE')
			{
				if($acDocTime == '00:00')
				{
					closeDBT($mysqli,2,'กรุณาป้อนเวลาเข้าจริง');
				}
				if($acOutDocTime == '00:00')
				{
					closeDBT($mysqli,2,'กรุณาป้อนเวลาออกจริง');
				}
			}
			// docDate,docTime,lateDate,lateTime status='',
		}
		else
		{
			$sql = "SELECT t2.StopSequenceNumber,t2.Status
			from tbl_truckmonitor t1
			left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID
			left join tbl_204header_api t3 on t2.Load_ID=t3.Load_ID
			where t1.ID=$monitorID and t2.Status in('IN TRANSIT','ARRIVE')
			order by t3.Load_ID,t2.StopSequenceNumber*1 limit 1;";
			$re1 = sqlError($mysqli,__LINE__,$sql);
			if($re1->num_rows == 0) closeDBT($mysqli,2,'กรุณากรอกข้อมูลลำดับที่ 1 ก่อน');
			$row1 = $re1->fetch_array(MYSQLI_ASSOC);

			if($StopSequenceNumber != $row1['StopSequenceNumber']*1)
			{
				closeDBT($mysqli,2,'ไม่สามารถกรอกข้อมูลข้ามได้');
			}

			if($row['Status'] == 'PENDING')
			{
				closeDBT($mysqli,2,'ไม่สามารถกรอกข้อมูลข้ามได้');
			}
			else if($row['Status'] == 'IN TRANSIT')
			{
				if($acDocTime == '00:00')
				{
					closeDBT($mysqli,2,'กรุณาป้อนเวลาเข้าจริง');
				}
			}
			else if($row['Status'] == 'ARRIVE')
			{
				if($acDocTime == '00:00')
				{
					closeDBT($mysqli,2,'กรุณาป้อนเวลาเข้าจริง');
				}
				if($acOutDocTime == '00:00')
				{
					closeDBT($mysqli,2,'กรุณาป้อนเวลาออกจริง');
				}
			}
		}

		$mysqli->autocommit(FALSE);
		try
		{			
			/* `ActualIN_Datetime` datetime DEFAULT NULL,
			`ActualOut_Datetime` datetime DEFAULT NULL, 
			`PlanIN_Datetime` datetime NOT NULL,
			  `PlanOut_Datetime` datetime NOT NULL,*/
			  
			$sql = "UPDATE tbl_204body_api
			set ActualIN_Datetime=date_format(PlanIN_Datetime,'%Y-%m-%d $acDocTime'),
			ActualOut_Datetime=if('$acOutDocTime' = '00:00',null,date_format(PlanOut_Datetime,'%Y-%m-%d $acOutDocTime')),
			Update_ActualIN_API=Update_ActualIN_API+1,
			Update_ActualIN_Datetime=now(),Update_ActualIN_Datetime_By='1'
			where Load_ID='$Load_ID' and StopSequenceNumber='$StopSequenceNumber' limit 1";
			sqlError($mysqli,__LINE__,$sql,1);
			if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่พบการเปลียนแปลง');

			$sql = "SELECT ID from tbl_204body_api 
			where Load_ID='$Load_ID' and StopSequenceNumber='$StopSequenceNumber' and ActualOut_Datetime is not null limit 1";
			$re1 = sqlError($mysqli,__LINE__,$sql,1);
			$upStatus = $re1->num_rows == 0 ? 'ARRIVE':'COMPLETED';
			$body204 = $re1->fetch_array(MYSQLI_ASSOC);
			
			$sql = "UPDATE tbl_204body_api
			set Status='$upStatus',Update_ActualOut_Datetime= if(ActualOut_Datetime is null,null,now()),
			Update_ActualOut_API=if(ActualOut_Datetime is null,Update_ActualOut_API,Update_ActualOut_API+1),
			Update_ActualOut_Datetime_By= if(ActualOut_Datetime is null,null,'1')
			where Load_ID='$Load_ID' and StopSequenceNumber='$StopSequenceNumber' limit 1";
			sqlError($mysqli,__LINE__,$sql,1);
			if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่พบการเปลียนแปลง');

			// if($StopSequenceNumber == 1 and $oldStatus=='PENDING')
			if($StopSequenceNumber == 1  and $oldStatus=='PENDING')
			{
				$sql="UPDATE tbl_truckmonitor t1
				left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID and t1.StopSequenceNumber=t2.StopSequenceNumber
				left join tbl_204header_api t3 on t2.Load_ID=t3.Load_ID
				SET t2.Status='IN TRANSIT'
				where t2.Load_ID='$Load_ID' and t1.ID<>$monitorID";
				sqlError($mysqli,__LINE__,$sql,1);
				if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่พบการเปลียนแปลง');

				$sql = "UPDATE tbl_204header_api set Status='IN TRANSIT' where Load_ID='$Load_ID' limit 1;";
				sqlError($mysqli,__LINE__,$sql,1);
				// if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่สามารถบันทึกข้อมูลได้');
				
			}

			if($upStatus=='COMPLETED')
			{
				$sql = "SELECT StopSequenceNumber from tbl_204body_api 
				where Load_ID='$Load_ID' order by StopSequenceNumber desc limit 1";
				$re1 = sqlError($mysqli,__LINE__,$sql,1);
				if($re1->num_rows==0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่สามารถบันทึกข้อมูลได้');
				if($re1->fetch_array(MYSQLI_ASSOC)['StopSequenceNumber'] == $StopSequenceNumber)
				{
					$sql = "DELETE from tbl_truckmonitor where Load_ID='$Load_ID'";
					sqlError($mysqli,__LINE__,$sql,1);
					if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่พบการเปลียนแปลง');
					
					$sql = "UPDATE tbl_204header_api set Status='COMPLETED' where Load_ID='$Load_ID' limit 1;";
					sqlError($mysqli,__LINE__,$sql,1);
					if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่สามารถบันทึกข้อมูลได้');
				}

			}
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
	if($type == 31)
	{		

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>40 && $type<=50)//save
{
	if($type == 41)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else closeDBT($mysqli,2,'TYPE ERROR');


function getDataTruck($mysqli,$lineCode)
{
	$sql = "SELECT truckLicense,truckType,ST_AsGeoJSON(Geo)Geo,gps_speed,gps_angle,truck_carrier,
	concat(substring_index(truckLicense,'-',-1),truck_carrier) ID
	from tbl_truck;";
	return sqlError($mysqli,$lineCode,$sql);
}

$mysqli->close();
exit();
?>
