<?php
include('php/connection.php');
include('vendor/autoload.php');
use \Curl\Curl;
$curl = new Curl();

// t3.Start_Datetime between date_add(now(), INTERVAL -3 day) and date_add(now(), INTERVAL 1 day)
$sql = "SELECT t1.ID monitorID,t3.truckLicense,t2.Load_ID,t3.NumberOfStops,
group_concat(if(t2.ActualIN_Datetime is null or t2.ActualOut_Datetime is null,t2.StopSequenceNumber,0) order by t2.StopSequenceNumber) checkSeq,
group_concat(if(t2.ActualIN_Datetime is null,0,1)  order by t2.StopSequenceNumber) stampIN_check,
group_concat(if(t2.ActualOut_Datetime is null,0,1)  order by t2.StopSequenceNumber) stampOUT_check,
group_concat(t2.Supplier_Code  order by t2.StopSequenceNumber) Supplier_Code_All,
group_concat(t2.PlanIN_Datetime  order by t2.StopSequenceNumber) PlanIN_Datetime_All,
group_concat(t2.PlanOut_Datetime  order by t2.StopSequenceNumber) PlanOut_Datetime_All,
group_concat(ifnull(t2.ActualIN_Datetime,0)  order by t2.StopSequenceNumber) ActualIN_Datetime_All,
group_concat(ifnull(t2.ActualOut_Datetime,0)  order by t2.StopSequenceNumber) ActualOut_Datetime_All,
group_concat(ifnull(date_add(t2.PlanIN_Datetime, INTERVAL -3 hour),0)  order by t2.StopSequenceNumber) PlanIN_Datetime_GPS_Start_All,
group_concat(ifnull(date_add(t2.PlanIN_Datetime, INTERVAL 3 hour),0)  order by t2.StopSequenceNumber) PlanIN_Datetime_GPS_End_All,
group_concat(ifnull(date_add(t2.PlanOut_Datetime, INTERVAL -3 hour),0)  order by t2.StopSequenceNumber) PlanOut_Datetime_GPS_Start_All,
group_concat(ifnull(date_add(t2.PlanOut_Datetime, INTERVAL 3 hour),0)  order by t2.StopSequenceNumber) PlanOut_Datetime_GPS_End_All
from tbl_truckmonitor t1
inner join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID and t1.StopSequenceNumber=t2.StopSequenceNumber
inner join tbl_204header_api t3 on t1.Load_ID=t3.Load_ID
left join tbl_truck t4 on t3.truckLicense=t4.truckLicense
left join tbl_supplier t5 on t2.Supplier_Code=t5.code
where t3.Start_Datetime between date_add(now(), INTERVAL -3 day) and date_add(now(), INTERVAL 1 day) and substring_index(t2.Supplier_Code,'-',1) not in('GRBNA','GBL9A')
group by t1.Load_ID
order by t1.Load_ID,t1.StopSequenceNumber;";


$re1 = sqlError($mysqli,__LINE__,$sql);
if($re1->num_rows == 0) closeDBT($mysqli,2,'Data = 0');
while($row1 = $re1->fetch_array(MYSQLI_ASSOC))
{
	$monitorID = $row1['monitorID'];
	$truckLicense = $row1['truckLicense'];
	$Load_ID = $row1['Load_ID'];
	$NumberOfStops = $row1['NumberOfStops'];

	$checkSeq = explode(',',$row1['checkSeq']);
	$stampIN_check = explode(',',$row1['stampIN_check']);
	$stampOUT_check = explode(',',$row1['stampOUT_check']);
	$Supplier_Code_All = explode(',',$row1['Supplier_Code_All']);
	$PlanIN_Datetime_All = explode(',',$row1['PlanIN_Datetime_All']);
	$PlanOut_Datetime_All = explode(',',$row1['PlanOut_Datetime_All']);
	$ActualIN_Datetime_All = explode(',',$row1['ActualIN_Datetime_All']);
	$ActualOut_Datetime_All = explode(',',$row1['ActualOut_Datetime_All']);
	$PlanIN_Datetime_GPS_Start_All = explode(',',$row1['PlanIN_Datetime_GPS_Start_All']);
	$PlanIN_Datetime_GPS_End_All = explode(',',$row1['PlanIN_Datetime_GPS_End_All']);
	$PlanOut_Datetime_GPS_Start_All = explode(',',$row1['PlanOut_Datetime_GPS_Start_All']);
	$PlanOut_Datetime_GPS_End_All = explode(',',$row1['PlanOut_Datetime_GPS_End_All']);

	$index  = -1;
	for($i=0,$len=count($checkSeq);$i<$len;$i++)
	{
		if($checkSeq[$i] != 0)
		{
			$index = $i;
			break;
		}
	}

	if($index != -1)
	{
		$Supplier_Code = $Supplier_Code_All[$index];
		$PlanIN_Datetime = $PlanIN_Datetime_All[$index];
		$PlanOut_Datetime = $PlanOut_Datetime_All[$index];
		$ActualIN_Datetime = $ActualIN_Datetime_All[$index];
		$ActualOut_Datetime = $ActualOut_Datetime_All[$index];
		$PlanIN_Datetime_GPS_Start = $PlanIN_Datetime_GPS_Start_All[$index];
		$PlanIN_Datetime_GPS_End = $PlanIN_Datetime_GPS_End_All[$index];
		$PlanOut_Datetime_GPS_Start = $PlanOut_Datetime_GPS_Start_All[$index];
		$PlanOut_Datetime_GPS_End = $PlanOut_Datetime_GPS_End_All[$index];

		$StopSequenceNumber = $checkSeq[$index];
		$checkGPS_IN = 0;
		$checkGPS_OUT = 0;

		if($checkSeq[$index] == 1)
		{
			if($stampIN_check[$index] == 0)
			{
				// check in
				$checkGPS_IN = 1;
			}
			else if($stampIN_check[$index] == 1 && $stampOUT_check[$index] == 0)
			{
				// check out
				$checkGPS_OUT = 1;				
			}
		}
		else if($checkSeq[$index] > 1)
		{
			// check seq before
			if($checkSeq[($index-1)] == 0)
			{
				if($stampIN_check[$index] == 0)
				{
					// check in
					$checkGPS_IN = 1;
				}
				else if($stampIN_check[$index] == 1 && $stampOUT_check[$index] == 0)
				{
					// check out
					$checkGPS_OUT = 1;
				}
			}
		}

/* 		echo $checkGPS_OUT;
		exit(); */
		if($checkGPS_IN == 1)
		{			
			// $useSql เช็คว่ามี load id วิ่งไปที่เดียวกันก่อน load id นี้ หรือไม่
			
			$useSql = 0;
			$sql = "SELECT t2.PlanIN_Datetime,t2.PlanOut_Datetime,t1.Status,t1.Load_ID,
			ifnull(t2.ActualIN_Datetime,0) ActualIN_Datetime,ifnull(t2.ActualOut_Datetime,0) ActualOut_Datetime
			from tbl_204header_api t1
			left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID
			where t2.PlanIN_Datetime<'$PlanIN_Datetime'
			and t2.Supplier_Code='$Supplier_Code' and t1.truckLicense='$truckLicense' order by t2.PlanIN_Datetime desc limit 1;";

			$reCheckAfterLoadID = sqlError($mysqli,__LINE__,$sql);
			if($reCheckAfterLoadID->num_rows == 1)
			{
				$reCheckAfterLoadID_row1 = $reCheckAfterLoadID->fetch_array(MYSQLI_ASSOC);
				if($reCheckAfterLoadID_row1['ActualOut_Datetime'] == 0 || ($reCheckAfterLoadID_row1['Status'] != 'COMPLETED'))
				{					
					$useSql = 0;
					continue;
				}
				else
				{
					$sql = "SELECT t2.PlanIN_Datetime,t2.PlanOut_Datetime,t1.Status,t1.Load_ID,t2.ActualOut_Datetime					
					from tbl_204header_api t1
					left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID
					where t2.Load_ID='$reCheckAfterLoadID_row1[Load_ID]'
					order by t2.StopSequenceNumber desc limit 1;";
					$useGpsNextTime = sqlError($mysqli,__LINE__,$sql);
					if($useGpsNextTime->num_rows == 1)
					{
						$useGpsNextTime_row = $useGpsNextTime->fetch_array(MYSQLI_ASSOC);
					}
					
					$useSql = 1;
				}
			}
			else
			{
				$useSql = 0;
			}

			$Status = 'ARRIVE';
			
			if($StopSequenceNumber > 1)
			{
				$prevSeq = $StopSequenceNumber-1;
				$sql = "SELECT ActualOut_Datetime,ActualIN_Datetime
				from tbl_204body_api where Load_ID='$Load_ID' and StopSequenceNumber='$prevSeq'";
				$rePrev = sqlError($mysqli,__LINE__,$sql);

				if($rePrev->num_rows >0)
				{
					$prev_row = $rePrev->fetch_array(MYSQLI_ASSOC);
					$prev_ActualOut_Datetime = "and t4.gps_updateDatetime >'$prev_row[ActualOut_Datetime]'";
					$prev_ActualIN_Datetime = "and t4.gps_updateDatetime >'$prev_row[ActualIN_Datetime]'";
				}
			}
			else
			{
				$prev_ActualOut_Datetime = '';
				$prev_ActualIN_Datetime = '';
			}

			$lastPick_ActualOut_Datetime = '';

			// find drop
			if(!(strpos($Supplier_Code,'-') === false))
			{
				$prev_ActualOut_Datetime = '';
				$prev_ActualIN_Datetime = '';

				$sql = "SELECT ActualOut_Datetime from tbl_204body_api 
				where Load_ID='$Load_ID' and StopTypeEnumVal='ST_PICKONLY' 
				order by ActualOut_Datetime desc limit 1;";

				// echo $sql;
				$lastPick = sqlError($mysqli,__LINE__,$sql);

				if($lastPick->num_rows >0)
				{
					$lastPick_row = $lastPick->fetch_array(MYSQLI_ASSOC);
					$lastPick_ActualOut_Datetime = "and t4.gps_updateDatetime >'$lastPick_row[ActualOut_Datetime]'";
				}
			}

			if($useSql == 0)
			{
				$sql = "SELECT t4.gps_updateDatetime,t4.truckLicense,t5.code
				from tbl_truck_log t4 ,tbl_supplier t5
				where t4.gps_updateDatetime between '$PlanIN_Datetime_GPS_Start' and '$PlanIN_Datetime_GPS_End'
				$lastPick_ActualOut_Datetime
				and t4.truckLicense='$truckLicense' and t5.code='$Supplier_Code' and ST_Contains(t5.geo,(t4.geo))=1 limit 1;";
			}
			else
			{
				// ดึงเวลาต่อจากการเข้าครั้งก่อน
				$sql = "SELECT t4.gps_updateDatetime,t4.truckLicense,t5.code
				from tbl_truck_log t4 ,tbl_supplier t5
				where t4.gps_updateDatetime between '$PlanIN_Datetime_GPS_Start' and '$PlanIN_Datetime_GPS_End'
				and t4.gps_updateDatetime > '$useGpsNextTime_row[ActualOut_Datetime]'
				$lastPick_ActualOut_Datetime
				and t4.truckLicense='$truckLicense' and t5.code='$Supplier_Code' and ST_Contains(t5.geo,(t4.geo))=1 limit 1";
			}
			
			$re2 = sqlError($mysqli,__LINE__,$sql);

			if($re2->num_rows > 0)
			{
				
				if($StopSequenceNumber == 1)
				{
					$sql="UPDATE tbl_204body_api
					SET `Status`='IN TRANSIT'
					where Load_ID='$Load_ID' and `Status`='PENDING'";
					sqlError($mysqli,__LINE__,$sql,1);
					
					$sql = "UPDATE tbl_204header_api 
					set `Status`='IN TRANSIT' where Load_ID='$Load_ID' and `Status` in('PENDING','WAITING DUETIME') limit 1;";
					sqlError($mysqli,__LINE__,$sql,1);
				}				

				$gps_updateDatetime = $re2->fetch_array(MYSQLI_ASSOC)['gps_updateDatetime'];
				$sql = "UPDATE tbl_204body_api
				set ActualIN_Datetime='$gps_updateDatetime',`Status`='$Status',
				Update_ActualIN_API=Update_ActualIN_API+1,
				Update_ActualIN_Datetime=now(),Update_ActualIN_Datetime_By='1'
				where Load_ID='$Load_ID' and StopSequenceNumber='$StopSequenceNumber' and `Status`='IN TRANSIT' limit 1";
				sqlError($mysqli,__LINE__,$sql,1);
				if($mysqli->affected_rows > 0)
				{
					echo "IN=$Load_ID - $StopSequenceNumber \n";
				}
			}
			
		}

		if($checkGPS_OUT == 1)
		{
			$Status = 'COMPLETED';
			$sql = "SELECT t4.gps_updateDatetime,t4.truckLicense,t5.code
			from tbl_truck_log t4 ,tbl_supplier t5
			where gps_updateDatetime between '$PlanOut_Datetime_GPS_Start' and '$PlanOut_Datetime_GPS_End' and 
			gps_updateDatetime > '$ActualIN_Datetime'
			and truckLicense='$truckLicense' and t5.code='$Supplier_Code' and ST_Contains(t5.geo,(t4.geo))=0 
			limit 1;";
			$re2 = sqlError($mysqli,__LINE__,$sql);
			
			if($re2->num_rows > 0)
			{
				$gps_updateDatetime = $re2->fetch_array(MYSQLI_ASSOC)['gps_updateDatetime'];
				
				$sql = "UPDATE tbl_204body_api
				set ActualOut_Datetime='$gps_updateDatetime',`Status`='$Status',
				Update_ActualOut_API=Update_ActualOut_API+1,
				Update_ActualOut_Datetime=now(),Update_ActualOut_Datetime_By='1'
				where Load_ID='$Load_ID' and StopSequenceNumber='$StopSequenceNumber' and `Status`='ARRIVE' limit 1";
				sqlError($mysqli,__LINE__,$sql,1);
				if($mysqli->affected_rows > 0)
				{
					echo "OUT=$Load_ID - $StopSequenceNumber \n";
					if($NumberOfStops == $checkSeq[$index])
					{
						$sql = "DELETE from tbl_truckmonitor where Load_ID='$Load_ID'";
						sqlError($mysqli,__LINE__,$sql,1);
						
						$sql = "UPDATE tbl_204header_api set `Status`='COMPLETED' where Load_ID='$Load_ID' limit 1;";
						sqlError($mysqli,__LINE__,$sql,1);
						
					}
				}
			}
			
		}

	}

}

$mysqli->close();
?>