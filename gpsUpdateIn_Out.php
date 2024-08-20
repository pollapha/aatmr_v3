<?php
// include('php/connection.php');
include('php/connectionS.php');
include('vendor/autoload.php');
use \Curl\Curl;
$curl = new Curl();

$sql = "SELECT t1.ID monitorID,t2.StopTypeEnumVal,t3.truckLicense,t2.Load_ID,t3.NumberOfStops,t2.StopSequenceNumber,t2.Supplier_Code,
t2.ActualIN_Datetime,t2.ActualOut_Datetime,t2.PlanIN_Datetime,t2.PlanOut_Datetime,
CASE
    WHEN ActualIN_Datetime is null and ( LAG(ActualOut_Datetime,1) OVER (PARTITION BY Load_ID ORDER BY Load_ID,StopSequenceNumber ) is not null or  t2.StopSequenceNumber=1) THEN 'Enter time In'
    WHEN ActualIN_Datetime  is not null and ActualOut_Datetime is null THEN 'Enter time Out'
    WHEN ActualIN_Datetime  is not null and ActualOut_Datetime is not null THEN 'Completed'
    ELSE 'Waiting'
END as `Status`,
if( t4.truck_carrier is not null and t4.gps_updateDatetime is not null,if(timediff(now(),t4.gps_updateDatetime)>'01:00','DISCONNECT','CONNECTED'),'DISCONNECT') gps_connection,
date_add(t2.PlanIN_Datetime, INTERVAL -3 hour) PlanInFindStart,
date_add(t2.PlanIN_Datetime, INTERVAL 3 hour) PlanInFindEnd,
date_add(t2.PlanOut_Datetime, INTERVAL -3 hour) PlanOutFindStart,
date_add(t2.PlanOut_Datetime, INTERVAL 3 hour) PlanOutFindEnd,
LAG(ActualOut_Datetime,1) OVER (PARTITION BY Load_ID ORDER BY Load_ID,StopSequenceNumber ) LastActualTimeOut

from tbl_truckmonitor t1
inner join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID and t1.StopSequenceNumber=t2.StopSequenceNumber
inner join tbl_204header_api t3 on t1.Load_ID=t3.Load_ID
left join tbl_truck t4 on t3.truckLicense=t4.truckLicense
left join tbl_supplier t5 on t2.Supplier_Code=t5.code
where t3.Start_Datetime between date_add(now(), INTERVAL -3 day) and date_add(now(), INTERVAL 1 day) and
if( t4.truck_carrier is not null and t4.gps_updateDatetime is not null,if(timediff(now(),t4.gps_updateDatetime)>'01:00','DISCONNECT','CONNECTED'),'DISCONNECT') ='CONNECTED'
order by t1.Load_ID,t1.StopSequenceNumber limit 100000;";


$re1 = sqlError($mysqli,__LINE__,$sql);
if($re1->num_rows == 0) closeDBT($mysqli,2,'Data = 0');
while($row1 = $re1->fetch_array(MYSQLI_ASSOC))
{
	$PlanIN_Datetime = $row1['PlanIN_Datetime'];
	$Supplier_Code = $row1['Supplier_Code'];
	$truckLicense = $row1['truckLicense'];
	$PlanInFindStart = $row1['PlanInFindStart'];
	$PlanInFindEnd = $row1['PlanInFindEnd'];

	$PlanOutFindStart = $row1['PlanOutFindStart'];
	$PlanOutFindEnd = $row1['PlanOutFindEnd'];

	$ActualIN_Datetime = $row1['ActualIN_Datetime'];
	

	$LastActualTimeOut = $row1['LastActualTimeOut'];
	$NumberOfStops = $row1['NumberOfStops'];
	$StopSequenceNumber = $row1['StopSequenceNumber'];
	
	$Load_ID = $row1['Load_ID'];
	
	if($row1['Status'] === 'Enter time In')
	{
		$sql2 = "SELECT t2.PlanIN_Datetime,t2.PlanOut_Datetime,t1.Status,t1.Load_ID,t2.Supplier_Code,
		ifnull(t2.ActualIN_Datetime,0) ActualIN_Datetime,ifnull(t2.ActualOut_Datetime,0) ActualOut_Datetime,LastDropLoadID.ActualOut_Datetime LastDropLoadID
		from tbl_204header_api t1
		left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID,
		LATERAL 
		(
			SELECT tt2.ActualOut_Datetime					
			from tbl_204header_api tt1
			left join tbl_204body_api tt2 on tt1.Load_ID=tt2.Load_ID
			where tt2.Load_ID=t2.Load_ID
			order by tt2.StopSequenceNumber desc limit 1
		) as LastDropLoadID
		where t1.Start_Datetime between date_add(now(), INTERVAL -3 day) and date_add(now(), INTERVAL 1 day) and t2.PlanIN_Datetime<'$PlanIN_Datetime'
		and t2.Supplier_Code='$Supplier_Code' and t1.truckLicense='$truckLicense' order by t2.PlanIN_Datetime desc limit 1;";

		$reCheckAfterLoadID = sqlError($mysqli,__LINE__,$sql2);
		if($reCheckAfterLoadID->num_rows == 1)
		{
			$reCheckAfterLoadID_row1 = $reCheckAfterLoadID->fetch_array(MYSQLI_ASSOC);
			if($reCheckAfterLoadID_row1['ActualOut_Datetime'] == 0 || ($reCheckAfterLoadID_row1['Status'] != 'COMPLETED'))
			{					
				continue;
			}

			if($StopSequenceNumber === 1)
			{
				$sql = "SELECT t4.gps_updateDatetime,t4.truckLicense,t5.code
				from tbl_truck_log t4 ,tbl_supplier t5
				where t4.gps_updateDatetime between '$PlanInFindStart' and '$PlanInFindEnd'
				and t4.gps_updateDatetime > '$reCheckAfterLoadID_row1[LastDropLoadID]'
				and t4.truckLicense='$truckLicense' and t5.code='$Supplier_Code' and ST_Contains(t5.geo,(t4.geo))=1 limit 1";
			}
			else
			{
				$sql = "SELECT t4.gps_updateDatetime,t4.truckLicense,t5.code
				from tbl_truck_log t4 ,tbl_supplier t5
				where t4.gps_updateDatetime between '$PlanInFindStart' and '$PlanInFindEnd'
				and t4.gps_updateDatetime >'$LastActualTimeOut'
				and t4.truckLicense='$truckLicense' and t5.code='$Supplier_Code' and ST_Contains(t5.geo,(t4.geo))=1 limit 1";
			}
		}
		else
		{
			continue;

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
			set ActualIN_Datetime='$gps_updateDatetime',`Status`='ARRIVE',
			Update_ActualIN_API=Update_ActualIN_API+1,
			Update_ActualIN_Datetime=now(),Update_ActualIN_Datetime_By='1'
			where Load_ID='$Load_ID' and StopSequenceNumber='$StopSequenceNumber' and `Status`='IN TRANSIT' limit 1";
			sqlError($mysqli,__LINE__,$sql,1);
			if($mysqli->affected_rows > 0)
			{
				echo "IN=$Load_ID - $StopSequenceNumber \n";
			}
		}
		continue;
	}
	else if($row1['Status'] === 'Enter time Out')
	{
		$sql = "SELECT t4.gps_updateDatetime,t4.truckLicense,t5.code
		from tbl_truck_log t4 ,tbl_supplier t5
		where t4.gps_updateDatetime between '$PlanOutFindStart' and '$PlanOutFindEnd'
		and t4.gps_updateDatetime >'$ActualIN_Datetime'
		and t4.truckLicense='$truckLicense' and t5.code='$Supplier_Code' and ST_Contains(t5.geo,(t4.geo))=0 limit 1";
		$re2 = sqlError($mysqli,__LINE__,$sql);

		if($re2->num_rows > 0)
		{	
			$gps_updateDatetime = $re2->fetch_array(MYSQLI_ASSOC)['gps_updateDatetime'];
			$sql = "UPDATE tbl_204body_api
			set ActualOut_Datetime='$gps_updateDatetime',`Status`='COMPLETED',
			Update_ActualOut_API=Update_ActualOut_API+1,
			Update_ActualOut_Datetime=now(),Update_ActualOut_Datetime_By='1'
			where Load_ID='$Load_ID' and StopSequenceNumber='$StopSequenceNumber' and `Status`='ARRIVE' limit 1";
			sqlError($mysqli,__LINE__,$sql,1);
			if($mysqli->affected_rows > 0)
			{
				echo "OUT=$Load_ID - $StopSequenceNumber \n";
				if($NumberOfStops == $StopSequenceNumber)
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

$mysqli->close();
?>