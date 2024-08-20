<?php
include('php/connectionS.php');
include('vendor/autoload.php');
use \Curl\Curl;
$curl = new Curl();

$sql = "SELECT t1.ID monitorID,t2.StopTypeEnumVal,t3.truckLicense,t2.Load_ID,t3.NumberOfStops,t2.StopSequenceNumber,t2.Supplier_Code,
substring_index(t2.Supplier_Code,'-',1) aat_ftm,
length(substring_index(t2.Supplier_Code,'-',-1)) check_aat_ftm,
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
LAG(ActualOut_Datetime,1) OVER (PARTITION BY Load_ID ORDER BY Load_ID,StopSequenceNumber ) LastActualTimeOut,LastPick.LastPickDatetime,
t2.Gate_In_Datetime,t2.Gate_Out_Datetime

from tbl_truckmonitor t1
inner join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID and t1.StopSequenceNumber=t2.StopSequenceNumber
inner join tbl_204header_api t3 on t1.Load_ID=t3.Load_ID
left join tbl_truck t4 on t3.truckLicense=t4.truckLicense
left join tbl_supplier t5 on t2.Supplier_Code=t5.code,
LATERAL 
(
	SELECT tt3.ActualOut_Datetime LastPickDatetime
	from tbl_204header_api tt2
	inner join tbl_204body_api tt3 on tt2.Load_ID=tt3.Load_ID and tt2.Load_ID=t3.Load_ID
	where tt3.StopTypeEnumVal='ST_PICKONLY'
	order by tt3.StopSequenceNumber desc limit 1
) as LastPick
where t3.Start_Datetime between date_add(now(), INTERVAL -3 day) and date_add(now(), INTERVAL 1 day) and
if( t4.truck_carrier is not null and t4.gps_updateDatetime is not null,if(timediff(now(),t4.gps_updateDatetime)>'01:00','DISCONNECT','CONNECTED'),'DISCONNECT') ='CONNECTED'
and t2.StopTypeEnumVal='ST_DROPONLY'
having LastPickDatetime is not null
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

	$aat_ftm = $row1['aat_ftm'];
	$check_aat_ftm = $row1['check_aat_ftm'];
	$Gate_In_Datetime = $row1['Gate_In_Datetime'];
	$Gate_Out_Datetime = $row1['Gate_Out_Datetime'];
	$LastPickDatetime = $row1['LastPickDatetime'];

	if( $check_aat_ftm == 2 && $Gate_In_Datetime === NULL)
	{
		$sqlGateIn = "SELECT t4.gps_updateDatetime,t4.truckLicense,t5.code
		from tbl_truck_log t4 ,tbl_supplier t5
		where t4.gps_updateDatetime between '$PlanInFindStart' and '$PlanInFindEnd'
		and t4.gps_updateDatetime >'$LastPickDatetime'
		and t4.truckLicense='$truckLicense' and t5.code='$aat_ftm' and ST_Contains(t5.geo,(t4.geo))=1 limit 1";

		$reGateIN = sqlError($mysqli,__LINE__,$sqlGateIn);
		if($reGateIN->num_rows > 0)
		{
			$gps_updateDatetime = $reGateIN->fetch_array(MYSQLI_ASSOC)['gps_updateDatetime'];
			sqlError($mysqli,__LINE__,
			"UPDATE tbl_204body_api set Gate_In_Datetime='$gps_updateDatetime' where Load_ID='$Load_ID' and Gate_In_Datetime is null",1);
			if($mysqli->affected_rows > 0)
			{
				echo "Gate IN=$Load_ID - $StopSequenceNumber \n";
			}
		}	
	}
	else if( $check_aat_ftm == 2 && $Gate_In_Datetime !== NULL && $Gate_Out_Datetime === NULL)
	{
		$sqlGateIn = "SELECT t4.gps_updateDatetime,t4.truckLicense,t5.code
		from tbl_truck_log t4 ,tbl_supplier t5
		where t4.gps_updateDatetime between '$PlanInFindStart' and '$PlanInFindEnd'
		and t4.gps_updateDatetime >'$Gate_In_Datetime'
		and t4.truckLicense='$truckLicense' and t5.code='$aat_ftm' and ST_Contains(t5.geo,(t4.geo))=0 limit 1";

		$reGateIN = sqlError($mysqli,__LINE__,$sqlGateIn);
		if($reGateIN->num_rows > 0)
		{
			$gps_updateDatetime = $reGateIN->fetch_array(MYSQLI_ASSOC)['gps_updateDatetime'];
			sqlError($mysqli,__LINE__,
			"UPDATE tbl_204body_api set Gate_Out_Datetime='$gps_updateDatetime' where Load_ID='$Load_ID' and Gate_In_Datetime is not null and Gate_Out_Datetime is null",1);
			if($mysqli->affected_rows > 0)
			{
				echo "Gate OUT=$Load_ID - $StopSequenceNumber \n";
			}
		}	
	}

}

$mysqli->close();
?>