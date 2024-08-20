<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'truckMonitor'}) )
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}
else if($_SESSION['xxxRole']->{'truckMonitor'}[0] == 0)
{
	echo "{ch:9,data:'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้'}";
	exit();
}

if(!isset($_REQUEST['type'])) {echo json_encode(array('ch'=>2,'data'=>'ข้อมูลไม่ถูกต้อง'));exit();}
$cBy = $_SESSION['xxxID'];
$fName = $_SESSION['xxxFName'];
$type  = intval($_REQUEST['type']);


include('../php/connection.php');
include('../common/commonFunc.php');
require_once('../Classes/PHPExcel.php');
if($type<=10)//data
{
	if($type == 1)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>project','obj=>problem'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
		$project = checkTXT($mysqli,$_POST['obj']['project']);
		$problem = checkTXT($mysqli,$_POST['obj']['problem']);

		if($project !== 'ALL')
		{
			$project = "where t7.projectName='$project'";
		}
		else 
		{
			$project = '';
		}


		$problem = (explode(' (',$problem))[0];

		$having = "";
		if($problem == 'ALL')
		{
			$having = "";
		}
		else if($problem == 'NORMAL')
		{
			$having = "having checkTimeStampIN <>'กรุณากรอกเวลา' and checkTimeStampOUT <>'กรุณากรอกเวลา'";
		}
		else if($problem == 'PROBLEM')
		{
			$having = "having checkTimeStampIN ='กรุณากรอกเวลา' or checkTimeStampOUT ='กรุณากรอกเวลา'";
		}


		$sql ="SELECT t1.docCount,t1.pus,t1.tripTTV,t1.truckLicense,ifnull(t4.truck_carrier,'ทะเบียนรถไม่อยู่ในระบบ')truck_carrier,
		if( t4.truck_carrier is not null and t4.gps_updateDatetime is not null,if(timediff(now(),t4.gps_updateDatetime)>'03:00','DISCONNECT','CONNECTED'),'DISCONNECT') gps_connection,t4.gps_updateDatetime,
		if(ST_AsText(t5.geo)='POINT(0 0)','ยังไม่ตีกรอบ','OK')checkGeo,t1.truckType,t1.driverName,t1.phone,t1.remark,
				t1.planTimeOut_Origin,t1.planTimeIn_Origin,t1.acTimeOut_Origin,t1.acTimeIn_Origin,
				t1.AlertTypeCode,t1.Load_ID,t1.Start_Datetime,t1.End_Datetime,t1.Route,t1.Load_Description,t1.CarrierCode,t1.NumberOfStops,t1.Status,
				t2.PlanIN_Datetime,t2.PlanOut_Datetime,t2.ActualIN_Datetime,t2.ActualOut_Datetime,
				t2.Supplier_Code,t2.Supplier_Name,t2.Supplier_Address,t2.Short_JD,t2.Country,
				t2.Identifi,t2.Supplier_Geographic,t2.Supplier_Zip,t2.CurrentLoadOperationalStatusEnumVal,
				t2.ServiceCode,t2.EquipmentTypeCode,t2.TractorEquipmentTypeCode,t2.StopSequenceNumber,t2.StopStatusEnumVal,t2.StopTypeEnumVal,
				t3.gpsStatus,t3.ediStep,t3.ID monitorID,
				date_format(if(date_format(t1.Start_Datetime,'%H:%i:%s') between '00:00:01' and '05:40:00',t1.Start_Datetime - INTERVAL 1 DAY,t1.Start_Datetime),'%Y-%m-%d') operration_date,
				date_format(t1.Start_Datetime,'%Y-%m-%d') dateStart,
				date_format(t2.PlanIN_Datetime,'%Y-%m-%d') docDate,
				date_format(t2.PlanIN_Datetime,'%H:%i') docTime,
				date_format(t2.ActualIN_Datetime,'%H:%i') acDocTime,
				date_format(t2.PlanOut_Datetime,'%H:%i') lateTime,
				date_format(t2.ActualOut_Datetime,'%H:%i') acOutDocTime	,
				if(t2.PlanIN_Datetime<now()=1 and t2.ActualIN_Datetime is null,'กรุณากรอกเวลา','OK') checkTimeStampIN,
                if(t2.PlanOut_Datetime<now()=1 and t2.ActualOut_Datetime is null,'กรุณากรอกเวลา','OK') checkTimeStampOUT
				from tbl_truckmonitor t3
				left join tbl_204header_api t1 on t3.Load_ID=t1.Load_ID
				left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID and t3.StopSequenceNumber=t2.StopSequenceNumber
				left join tbl_truck t4 on t1.truckLicense=t4.truckLicense
				left join tbl_supplier t5 on t2.Supplier_Code=t5.code
				left join tbl_route_master_header t6 on t1.Route=t6.routeName
                left join tbl_project_master t7 on t6.projectID=t7.ID
				$project
				$having
				order by t1.Start_Datetime,t1.Route,t2.Load_ID,t2.StopSequenceNumber";

		$re1 = sqlError($mysqli,__LINE__,$sql);
		// if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูลในระบบ');
		closeDBT($mysqli,1,jsonRow($re1,true,0));
	}
	else if($type == 2)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>project'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
		$project = checkTXT($mysqli,$_POST['obj']['project']);

		if($project == 'AAT MR')
		{
			$like = "where t3.routeOriginal RLIKE 'NA|NB|NP|MB|BA|SV'";
		}
		else
		{
			$like = '';
		}

		$sql ="SELECT t1.docCount,t1.pus,t1.tripTTV,t1.truckLicense,ifnull(t4.truck_carrier,'ทะเบียนรถไม่อยู่ในระบบ')truck_carrier,
		if(ST_AsText(t5.geo)='POINT(0 0)','ยังไม่ตีกรอบ','OK')checkGeo,t1.truckType,t1.driverName,t1.phone,
				t1.planTimeOut_Origin,t1.planTimeIn_Origin,t1.acTimeOut_Origin,t1.acTimeIn_Origin,
				t1.AlertTypeCode,t1.Load_ID,t1.Start_Datetime,t1.End_Datetime,t1.Route,t1.Load_Description,t1.CarrierCode,t1.NumberOfStops,t1.Status,
				t2.PlanIN_Datetime,t2.PlanOut_Datetime,t2.ActualIN_Datetime,t2.ActualOut_Datetime,
				t2.Supplier_Code,t2.Supplier_Name,t2.Supplier_Address,t2.Short_JD,t2.Country,
				t2.Identifi,t2.Supplier_Geographic,t2.Supplier_Zip,t2.CurrentLoadOperationalStatusEnumVal,
				t2.ServiceCode,t2.EquipmentTypeCode,t2.TractorEquipmentTypeCode,t2.StopSequenceNumber,t2.StopStatusEnumVal,t2.StopTypeEnumVal,
				t3.gpsStatus,t3.ediStep,t3.ID monitorID,
				date_format(if(date_format(t1.Start_Datetime,'%H:%i:%s') between '00:00:01' and '05:40:00',t1.Start_Datetime - INTERVAL 1 DAY,t1.Start_Datetime),'%Y-%m-%d') operration_date,
				date_format(t1.Start_Datetime,'%Y-%m-%d') dateStart,
				date_format(t2.PlanIN_Datetime,'%Y-%m-%d') docDate,
				date_format(t2.PlanIN_Datetime,'%H:%i') docTime,
				date_format(t2.ActualIN_Datetime,'%H:%i') acDocTime,
				date_format(t2.PlanOut_Datetime,'%H:%i') lateTime,
				date_format(t2.ActualOut_Datetime,'%H:%i') acOutDocTime	
				from tbl_truckmonitor t3
				left join tbl_204header_api t1 on t3.Load_ID=t1.Load_ID
				left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID and t3.StopSequenceNumber=t2.StopSequenceNumber
				left join tbl_truck t4 on t1.truckLicense=t4.truckLicense
				left join tbl_supplier t5 on t2.Supplier_Code=t5.code
				order by t2.Load_ID,t2.StopSequenceNumber;";


		$re1 = sqlError($mysqli,__LINE__,$sql);
		$data = jsonRow($re1,true,0);

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
	else if($type == 3)
	{
		$re1 = getDataTruck($mysqli,__LINE__);
		closeDBT($mysqli,1,jsonRow($re1,true,0,''));
	}
	else if($type == 4)
	{
		$re1 = getDataCheckTimeFTM($mysqli,__LINE__);
		closeDBT($mysqli,1,jsonRow($re1,true,0,''));
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>10 && $type<=20)//insert
{
	if($_SESSION['xxxRole']->{'truckMonitor'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
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
	if($_SESSION['xxxRole']->{'truckMonitor'}[2] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 21)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>Load_ID','obj=>StopSequenceNumber','obj=>monitorID',
		'obj=>acDocTime','obj=>acOutDocTime','obj=>ActualIN_Date','obj=>ActualOut_Date'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
		
		
		$Load_ID = checkINT($mysqli,$_POST['obj']['Load_ID']);
		$StopSequenceNumber = checkINT($mysqli,$_POST['obj']['StopSequenceNumber']);
		$monitorID = checkINT($mysqli,$_POST['obj']['monitorID']);

		$acDocTime = checkTXT($mysqli,$_POST['obj']['acDocTime']);
		$acOutDocTime = checkTXT($mysqli,$_POST['obj']['acOutDocTime']);

		$acDocTime = strlen($acDocTime) == 0 ? '00:00':$acDocTime;
		$acOutDocTime = strlen($acOutDocTime) == 0 ? '00:00':$acOutDocTime;

		$ActualIN_Date = checkTXT($mysqli,$_POST['obj']['ActualIN_Date']);
		$ActualOut_Date = checkTXT($mysqli,$_POST['obj']['ActualOut_Date']);

		
		$sql = "SELECT t2.Status,t2.StopSequenceNumber,t1.truckLicense,
		if( t4.truck_carrier is not null and t4.gps_updateDatetime is not null,if(timediff(now(),t4.gps_updateDatetime)>'03:00','','OK'),'') gps_connection
		from tbl_204header_api t1
		left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID
		left join tbl_truckmonitor t3 on t2.Load_ID=t3.Load_ID
		left join tbl_truck t4 on t1.truckLicense=t4.truckLicense
		where t1.Load_ID=$Load_ID and t2.StopSequenceNumber=$StopSequenceNumber and t3.ID=$monitorID limit 1;";
		$re1 = sqlError($mysqli,__LINE__,$sql);
		if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูลในระบบ');
		$row = $re1->fetch_array(MYSQLI_ASSOC);
		$oldStatus = $row['Status'];
		$truckLicense = $row['truckLicense'];
		$gps_connection = $row['gps_connection'];
		
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
			$sql = "SELECT t2.StopSequenceNumber,t2.Status,
			if( t4.truck_carrier is not null and t4.gps_updateDatetime is not null,if(timediff(now(),t4.gps_updateDatetime)>'03:00','','OK'),'') gps_connection
			from tbl_truckmonitor t1
			left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID
			left join tbl_204header_api t3 on t2.Load_ID=t3.Load_ID
			left join tbl_truck t4 on t3.truckLicense=t4.truckLicense
			where t1.ID=$monitorID and t2.Status in('IN TRANSIT','ARRIVE')
			order by t3.Load_ID,t2.StopSequenceNumber*1 limit 1;";
			$re1 = sqlError($mysqli,__LINE__,$sql);
			if($re1->num_rows == 0) closeDBT($mysqli,2,'กรุณากรอกข้อมูลลำดับที่ 1 ก่อน');
			$row1 = $re1->fetch_array(MYSQLI_ASSOC);

			if($StopSequenceNumber > $row1['StopSequenceNumber']*1)
			{
				closeDBT($mysqli,2,'ไม่สามารถกรอกข้อมูลข้ามได้ 1');
			}

			if($row['Status'] == 'PENDING')
			{
				closeDBT($mysqli,2,'ไม่สามารถกรอกข้อมูลข้ามได้ 2');
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
			/* 
			`ActualIN_Datetime` datetime DEFAULT NULL,
			`ActualOut_Datetime` datetime DEFAULT NULL, 
			*/

			$sql = "UPDATE tbl_204body_api
			set ActualIN_Datetime='$ActualIN_Date $acDocTime',
			ActualOut_Datetime=if('$acOutDocTime' = '00:00',null,'$ActualOut_Date $acOutDocTime'),
			Update_ActualIN_API=Update_ActualIN_API+1,GPS_Status='$gps_connection',
			Update_ActualIN_Datetime=now(),Update_ActualIN_Datetime_By='$cBy'
			where Load_ID='$Load_ID' and StopSequenceNumber='$StopSequenceNumber' limit 1";
			  
/* 			$sql = "UPDATE tbl_204body_api
			set ActualIN_Datetime=date_format(PlanIN_Datetime,'%Y-%m-%d $acDocTime'),
			ActualOut_Datetime=if('$acOutDocTime' = '00:00',null,date_format(PlanOut_Datetime,'%Y-%m-%d $acOutDocTime')),
			Update_ActualIN_API=Update_ActualIN_API+1,
			Update_ActualIN_Datetime=now(),Update_ActualIN_Datetime_By='$cBy'
			where Load_ID='$Load_ID' and StopSequenceNumber='$StopSequenceNumber' limit 1"; */

			sqlError($mysqli,__LINE__,$sql,1);
			if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่พบการเปลียนแปลง');

			$sql = "SELECT ID from tbl_204body_api 
			where Load_ID='$Load_ID' and StopSequenceNumber='$StopSequenceNumber' and ActualOut_Datetime is not null limit 1";
			$re1 = sqlError($mysqli,__LINE__,$sql,1);
			$upStatus = $re1->num_rows == 0 ? 'ARRIVE':'COMPLETED';
			$body204 = $re1->fetch_array(MYSQLI_ASSOC);

			$sql = "WITH getLoadID as 
			(
				select t1.ID,t1.Load_ID,t1.PlanIN_Datetime,t1.PlanOut_Datetime,t1.ActualIN_Datetime,t1.ActualOut_Datetime,t1.StopSequenceNumber,
				if( time_to_sec(timediff(t1.ActualIN_Datetime,t1.ActualOut_Datetime)) > 0,1,0) CheckTimeIN_OUT,
				LAG(t1.ActualOut_Datetime,1) OVER (
						PARTITION BY t1.Load_ID
						ORDER BY t1.Load_ID,t1.StopSequenceNumber ) Prev_ActualOut_Datetime
				from tbl_204body_api t1
				where t1.Load_ID='$Load_ID'
				order by t1.Load_ID,t1.StopSequenceNumber
			), calPrevTime as 
			(
				select *,
					if( time_to_sec(timediff(Prev_ActualOut_Datetime,ActualIN_Datetime)) > 0,1,0) CheckPrevTime_OUT,
					if( time_to_sec(timediff(Prev_ActualOut_Datetime,ActualIN_Datetime)) < -21600,1,0) CheckPrevTime_OUT6HR
				from getLoadID
			)
			select 1
			from calPrevTime where CheckTimeIN_OUT>0 or CheckPrevTime_OUT>0 or CheckPrevTime_OUT6HR>0;";
			if( sqlError($mysqli,__LINE__,$sql,1)->num_rows >0 )
			{
				throw new Exception('ERROR LINE '.__LINE__.'<br>วันที่หรือเวลาไม่สอดคล้องกัน');
			}
			
			$sql = "UPDATE tbl_204body_api
			set Status='$upStatus',Update_ActualOut_Datetime= if(ActualOut_Datetime is null,null,now()),
			Update_ActualOut_API=if(ActualOut_Datetime is null,Update_ActualOut_API,Update_ActualOut_API+1),GPS_Status='$gps_connection',
			Update_ActualOut_Datetime_By= if(ActualOut_Datetime is null,null,'$cBy')
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
	else if($type == 22)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>Load_ID','obj=>truckLicense','obj=>truckType','obj=>driverName','obj=>phone','obj=>remark'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		$Load_ID = checkTXT($mysqli,$_POST['obj']['Load_ID']);
		
        $truckLicense = checkTXT($mysqli,$_POST['obj']['truckLicense']);
        $truckType = checkTXT($mysqli,$_POST['obj']['truckType']);
        $driverName = checkTXT($mysqli,$_POST['obj']['driverName']);
        $phone = checkTXT($mysqli,$_POST['obj']['phone']);
		$remark = checkTXT($mysqli,$_POST['obj']['remark']);

		// if(preg_replace('/[^ก-ฮ]/u','',$truckType))
		// {
		// 	closeDBT($mysqli,2,'ทะเบียนรถมีภาษาไทย');
		// }
		
		if(strlen($Load_ID) == 0) closeDBT($mysqli,2,'Load_ID ไม่ถูกต้อง');


		$sql = "SELECT t1.ID,t1.Load_ID
		from tbl_204header_api t1
		left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID
		where t1.Load_ID='$Load_ID' limit 1;";
		$re1 = sqlError($mysqli,__LINE__,$sql);
		if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูลในระบบ');

		$mysqli->autocommit(FALSE);
		try
		{

			$sql = "UPDATE tbl_204header_api set truckLicense='$truckLicense',truckType='$truckType',driverName='$driverName',phone='$phone',remark='$remark'
			where Load_ID='$Load_ID' limit 1;";
						
			sqlError($mysqli,__LINE__,$sql,1);
			if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่พบการเปลียนแปลง');

			$sql = "UPDATE tbl_204header_api set Update_Datetime=now(),update_By=$cBy where Load_ID='$Load_ID' limit 1;";

			sqlError($mysqli,__LINE__,$sql,1);
			if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่พบการเปลียนแปลง');

            $mysqli->commit();
            closeDBT($mysqli,1,'บันทึกสำเร็จ');
        }
		catch( Exception $e )
		{
			$mysqli->rollback();
			closeDBT($mysqli,2,$e->getMessage());
        }
	}
	else if($type == 23)
	{
		array('obj','obj=>Load_ID','obj=>StopSequenceNumber','obj=>monitorID',
		'obj=>acDocTime','obj=>acOutDocTime','obj=>ActualIN_Date','obj=>ActualOut_Date');
		$dataParams = array(
			'obj',			
			'obj=>Load_ID:s:1:1',
			'obj=>StopSequenceNumber:i:1:1',
			'obj=>monitorID:i:1:1',

			'obj=>ActualIN_Date:s:1:2',
			'obj=>ActualOut_Date:s:1:2',
			
			'obj=>acDocTime:s:1:2',
			'obj=>acOutDocTime:s:1:2',
		);

		$chkPOST = checkParamsAndDelare($_POST,$dataParams,$mysqli);
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		var_dump($ActualIN_Date);
		var_dump($ActualOut_Date);
		var_dump($acDocTime);
		var_dump($acOutDocTime);
		$mysqli->autocommit(FALSE);
		try
		{
			// $mysqli->commit();
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
	if($_SESSION['xxxRole']->{'truckMonitor'}[3] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 31)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>Load_ID','obj=>monitorID',
		'obj=>acDocTime','obj=>acOutDocTime'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		$monitorID = checkINT($mysqli,$_POST['obj']['monitorID']);

		$mysqli->autocommit(FALSE);
		try
		{
			$sql = "SELECT Load_ID from tbl_truckmonitor where id=$monitorID";
			$re1 = sqlError($mysqli,__LINE__,$sql,1);
			if($re1->num_rows == 0)
			{
				throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่สามารถลบข้อมูลได้');
			}

			$Load_ID = $re1->fetch_array(MYSQLI_ASSOC)['Load_ID'];
			$sql = "DELETE from tbl_truckmonitor where Load_ID in('$Load_ID')";
			sqlError($mysqli,__LINE__,$sql,1,1);
			if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่สามารถลบข้อมูล');

			$sql = "INSERT into tbl_event_log(`Load_ID`,`event`,`event_date`,`Create_Datetime`,`Create_By`) values
			('$Load_ID','กดปุ่มถังขยะลบข้อมูลออกจากหน้า truck monitor',now(),now(),$cBy);";

			sqlError($mysqli,__LINE__,$sql,1,1);
			if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่สามารถลบข้อมูล');
			
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
else if($type>40 && $type<=50)//save
{
	if($_SESSION['xxxRole']->{'truckMonitor'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 41)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else closeDBT($mysqli,2,'TYPE ERROR');



function send214($mysqli,$body204,$time=0)
{
	// ID,isa,st
	/* if($step == 1)
	{
		$limit = "limit 1";
	}
	else if($step == 2)
	{
		$limit = "and t2.type not in('UNLOAD','CU')";
	}
	else if($step == 3)
	{
		$limit = '';
	}
	else
	{
		$limit = '';
	} */


	$sql = "SELECT t1.b2,t1.docDate dateFirst,t1.docCount,t2.StopSequenceNumber,t2.route,t2.bm,t2.type,t2.supplierCode,t2.supplierName,t2.supplierAddress,
	t2.shortJD,t2.supplierGeographic,t2.supplierZip,t2.docDate,t2.docTime,t2.ediStatus,
	t2.updateDate,t2.updateTime,t2.updateBy,
	date_format(ADDTIME(concat(t2.acDocDate,' ',t2.acDocTime),'00:06'),'%H%i%s')agTime,
	date_format(ADDTIME(concat(t2.acDocDate,' ',t2.acDocTime),'00:06'),'%H%i%s')aaTime,
	date_format(ADDTIME(concat(t2.acDocDate,' ',t2.acDocTime),'00:01'),'%H%i%s')abTime,
	t1.supplierCode supplierCode_head,t1.supplierName supplierName_head,t1.supplierAddress supplierAddress_head,
	t1.shortJD shortJD_head,t1.supplierGeographic supplierGeographic_head,t1.supplierZip supplierZip_head,
	t1.trucklicense,t2.acDocDate,t2.acDocTime,t2.acOutDocDate,t2.acOutDocTime,t2.country,t2.identifi,
	t1.country country_head,t1.identifi identifi_head
	from tbl_204header_api t1
	left join tbl_204body_api t2 on t1.isa=t2.isa and t1.st=t2.st
	where t2.isa='$body204[isa]' and t2.st='$body204[st]';";

	if(!$re1 = $mysqli->query($sql)){echo json_encode(array('ch'=>2,'data'=>'Error Code 1'));closeDB($mysqli);}
	if($re1->num_rows ==0) {echo json_encode(array('ch'=>2,'data'=>'NOT MATCH'));closeDB($mysqli);}
	$Ymd = date('Ymd');
	$ymd = date('ymd');
	$Hi = date('Hi');
	$runingNumber = $mysqli->query("select func_GenRuningNumber('edi_header1',0)edi_header1,func_GenRuningNumber('edi_header2',0)edi_header2")->fetch_array(MYSQLI_ASSOC);
	$edi_header1 = $runingNumber['edi_header1'];
	$edi_header2 = $runingNumber['edi_header2'];
	$templet  = "ISA*00*          *00*          *ZZ*TTVTP          *ZZ*FMXFORD        *{ymd}*{Hi}*U*00401*{edi_header1}*0*T*;\n";
	$templet .= "GS*QM*TTVTP*FMXFORD*{Ymd}*{Hi}*{edi_header1*1}*X*004010\n";
	$templet .= "{body1}";
	$templet .= "GE*{ge}*{edi_header1*1}\n";
	$templet .= "IEA*1*{edi_header1}\n";
	$templet = str_replace("{edi_header1}",$edi_header1,$templet);
	$templet = str_replace("{edi_header1*1}",$edi_header1*1,$templet);
	$templet = str_replace("{edi_header2}",$edi_header2,$templet);
	$templet = str_replace("{Ymd}",$Ymd,$templet);
	$templet = str_replace("{ymd}",$ymd,$templet);
	$templet = str_replace("{Hi}",$Hi,$templet);

	$bodyAr = array();      
	$rowCount = 0;
	while($row1 = $re1->fetch_array(MYSQLI_ASSOC))
	{
	    $bodyAr[] =  gen_x1_body($Ymd,$ymd,$Hi,$runingNumber,$edi_header1,$edi_header2,$row1,++$rowCount);
	}

	// $body1 =  gen_x1_body($Ymd,$ymd,$Hi,$runingNumber,$edi_header1,$edi_header2,$row1,++$rowCount);
	$templet = str_replace("{ge}",$rowCount,$templet);
	$templet = str_replace("{body1}",join('',$bodyAr),$templet);
	$templet = explode("\n",$templet);
	$templet = join("~\n",$templet);
	$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,24);
	// $fileName = 'FC_OUT_214_FMXFORDT_'.$randomString.'_'.date('YmdHis').substr(explode(' ',microtime())[0],5,3).'.edi';
	$fileName = date('YmdHis').substr(explode(' ',microtime())[0],5,3).'T'.$time.'_EDI_214_FMXFORDT.edi'; 
	$directory = 'C:/aatmr/edi_v2/out214seq/';
	if (!file_exists($directory)) {
	    mkdir($directory, 0777, true);
	}
	$fullFileName = $directory.$fileName;
	$objFopen = fopen($fullFileName, 'w');
	fwrite($objFopen,$templet);
	fclose($objFopen);

	if($time == '00')
	{
		$directory = 'C:/aatmr/edi_v2/backup214/';
		if (!file_exists($directory)) {
		    mkdir($directory, 0777, true);
		}
		$fullFileName = $directory.$fileName;
		$objFopen = fopen($fullFileName, 'w');
		fwrite($objFopen,$templet);
		fclose($objFopen);
	}
}

function gen_x1_body($Ymd,$ymd,$Hi,$runingNumber,$edi_header1,$edi_header2,$row1,$st_run)
{
    $st_run =substr('0000',0,4-strlen($st_run)).$st_run;
    $body = "ST*214*{edi_header2}{st_run}\n";
    $body .= "B10*{bolPSKL}*{bol}*TTVT\n";
    $body .= "L11*{bolPSKL}*IL*9999\n";
    $body .= "N1*BT*{supplierName}*{country}*{supplierCode}\n";
    $body .= "N3*{supplierAddress}\n";
    $body .= "N4*{supplierGeographic}*{shortJD}*{supplierZip}*{identifi}\n";
    $body .= "LX*{lx}\n";
    $body .= "AT7*AG*NS***{Ymd_1}*{agTime}*LT\n";

    if($row1['type'] == 'UNLOAD' || $row1['type'] == 'CU' || $row1['type'] == 'PU')
        $body .= "AT7***AB*NS*{Ymd_2}*{abTime}*LT\n";
    else
        $body .= "AT7***AA*NS*{Ymd_2}*{aaTime}*LT\n";    

    $body .= "AT7*X1*NS***{Ymd_1}*{Hi_1}*LT\n";
    $body .= "AT7*D1*NS***{Ymd_2}*{Hi_2}*LT\n";
    $body .= "MS1*{supplierGeographic}*{country}*TH\n";
    $body .= "MS2*TTVT*{truckLicense}**1\n";
    $body .= "L11*{StopSequenceNumber}*QN\n";

    $body .= "SE*{numLine}*{edi_header2}{st_run}\n";

    // $body = str_replace("{bolPSKL}",substr($row1['b2'],3,12),$body);
    $body = str_replace("{bolPSKL}",$row1['b2'],$body);
    $body = str_replace("{bol}",$row1['b2'],$body);
    
    $body = str_replace("{lx}",$st_run*1,$body);
    $body = str_replace("{agTime}",$row1['agTime'],$body);
    $body = str_replace("{aaTime}",$row1['aaTime'],$body);
    $body = str_replace("{abTime}",$row1['abTime'],$body);
    $body = str_replace("{identifi}",$row1['identifi'],$body);
    $body = str_replace("{Ymd_1}",date('Ymd',strtotime($row1['acDocDate'])),$body);
    $body = str_replace("{Hi_1}",date('His',strtotime($row1['acDocTime'])),$body);
    $body = str_replace("{Ymd_2}",date('Ymd',strtotime($row1['acOutDocDate'])),$body);
    $body = str_replace("{Hi_2}",date('His',strtotime($row1['acOutDocTime'])),$body);

    $body = str_replace("{supplierGeographic}",$row1['supplierGeographic'],$body);
    $body = str_replace("{truckLicense}",$row1['trucklicense'],$body);
    $body = str_replace("{StopSequenceNumber}",$row1['StopSequenceNumber'],$body);
    $body = str_replace("{shortJD}",$row1['shortJD'],$body);
    $body = str_replace("{supplierName}",$row1['supplierName'],$body);
    $body = str_replace("{supplierCode}",$row1['supplierCode'],$body);
    $body = str_replace("{supplierZip}",$row1['supplierZip'],$body);
    $body = str_replace("{country}",$row1['country'],$body);
    $body = str_replace("{supplierAddress}",$row1['supplierAddress'],$body);
    // $body = str_replace("{routeNo}",$row1['route'],$body);

    $body = str_replace("{edi_header2}",$edi_header2,$body);
    $body = str_replace("{st_run}",$st_run,$body);
    $se = count(explode("\n",$body))-1;
    $body = str_replace("{numLine}",$se,$body);
    return $body;
}

function getDataTruck($mysqli,$lineCode)
{
	$sql = "SELECT truckLicense,truckType,ST_AsGeoJSON(Geo)Geo,gps_speed,gps_angle,truck_carrier,
	concat(substring_index(truckLicense,'-',-1),truck_carrier) ID
	from tbl_truck;";
	return sqlError($mysqli,$lineCode,$sql);
}

function getDataCheckTimeFTM($mysqli,$lineCode)
{
	$sql = "SELECT count(*) countRows, 'IN' detail 
	from tbl_truckmonitor t3
	left join tbl_204header_api t1 on t3.Load_ID=t1.Load_ID
	left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID and t3.StopSequenceNumber=t2.StopSequenceNumber
	where (t2.ActualIN_Datetime is null) and t1.Route rlike '^9A'
	group by t2.ActualIN_Datetime
	union all
	SELECT count(*) countRows, 'OUT' detail 
	from tbl_truckmonitor t3
	left join tbl_204header_api t1 on t3.Load_ID=t1.Load_ID
	left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID and t3.StopSequenceNumber=t2.StopSequenceNumber
	where (t2.ActualOut_Datetime is null) and t1.Route rlike '^9A'
	group by t2.ActualOut_Datetime;";
	return sqlError($mysqli,$lineCode,$sql);
}

$mysqli->close();
exit();
?>
