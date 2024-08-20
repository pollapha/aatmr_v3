<?php
include('php/connectionS.php');
include('vendor/autoload.php');
use \Curl\Curl;
$curl = new Curl();
$mysqli->autocommit(FALSE);
$sql = "WITH getData as
(
	SELECT t1.ID monitorID,t2.StopTypeEnumVal,t3.truckLicense,t2.Load_ID,t3.NumberOfStops,t2.StopSequenceNumber,t2.Supplier_Code,
	t2.Gate_In_Datetime,t2.Gate_Out_Datetime,
	substring_index(t2.Supplier_Code,'-',1) aat_ftm,
	length(substring_index(t2.Supplier_Code,'-',-1)) check_aat_ftm,
	t2.ActualIN_Datetime,t2.ActualOut_Datetime,t2.PlanIN_Datetime,t2.PlanOut_Datetime,
	CASE
		WHEN ActualIN_Datetime is null and ( LAG(ActualOut_Datetime,1) OVER (PARTITION BY Load_ID ORDER BY Load_ID,StopSequenceNumber ) is not null or  t2.StopSequenceNumber=1) THEN 'Enter time In'
		WHEN ActualIN_Datetime  is not null and ActualOut_Datetime is null THEN 'Enter time Out'
		WHEN ActualIN_Datetime  is not null and ActualOut_Datetime is not null THEN 'Completed'
		ELSE 'Waiting'
	END as `Status`,
	if( t4.truck_carrier is not null and t4.gps_updateDatetime is not null,if(timediff(now(),t4.gps_updateDatetime)>'05:00','DISCONNECT','CONNECTED'),'DISCONNECT') gps_connection,
	date_add(t2.PlanIN_Datetime, INTERVAL -3 hour) PlanInFindStart,
	date_add(t2.PlanIN_Datetime, INTERVAL 3 hour) PlanInFindEnd,
	date_add(t2.PlanOut_Datetime, INTERVAL -3 hour) PlanOutFindStart,
	date_add(t2.PlanOut_Datetime, INTERVAL 3 hour) PlanOutFindEnd,
	LAG(ActualOut_Datetime,1) OVER (PARTITION BY Load_ID ORDER BY Load_ID,StopSequenceNumber ) LastActualTimeOut,LastPick.LastPickDatetime

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
	order by t1.Load_ID,t1.StopSequenceNumber
) , addCol as
(
	select *,TIMESTAMPDIFF(MINUTE,PlanIN_Datetime,now()) PlanInDif,TIMESTAMPDIFF(MINUTE,PlanOut_Datetime,now()) PlanOutDif,
    	CASE
		WHEN  StopTypeEnumVal = 'ST_PICKONLY' and `Status` ='Enter time In' 
			THEN random_date( 
            ifnull( LastActualTimeOut, PlanIN_Datetime) ,
            ifnull( LastActualTimeOut, PlanIN_Datetime) + INTERVAL 10 MINUTE
            )
	END as `randomPickTimeIn`,
    	CASE
		WHEN  StopTypeEnumVal = 'ST_PICKONLY' and `Status` ='Enter time Out' 
			THEN random_date(
				ActualIN_Datetime + INTERVAL 15 MINUTE , 
				ActualIN_Datetime + INTERVAL 20 MINUTE
			)
	END as `randomPickTimeOut`,
    
	CASE
		WHEN  StopTypeEnumVal = 'ST_DROPONLY' and `Status` ='Enter time In' 
			THEN random_date(
            if(PlanIN_Datetime>LastActualTimeOut,PlanIN_Datetime,LastActualTimeOut)  + INTERVAL 2 MINUTE, 
			if(PlanIN_Datetime>LastActualTimeOut,PlanIN_Datetime,LastActualTimeOut)  + INTERVAL 7 MINUTE
            )
	END as `randomDropTimeIn`,
    	CASE
		WHEN  StopTypeEnumVal = 'ST_DROPONLY' and `Status` ='Enter time Out' 
			THEN random_date( 
            ActualIN_Datetime + INTERVAL 5 MINUTE , 
            ActualIN_Datetime + INTERVAL 10 MINUTE
			)
	END as `randomDropTimeOut`,
    PlanIN_Datetime - INTERVAL 30 MINUTE planTimeInLimitStart,
    PlanIN_Datetime + INTERVAL 30 MINUTE planTimeInLimitEnd,
    PlanOut_Datetime - INTERVAL 30 MINUTE planTimeOutLimitStart,
    PlanOut_Datetime + INTERVAL 30 MINUTE planTimeOutLimitEnd
    from getData
)
select * from addCol ;";


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
	
	$planTimeInLimitStart = $row1['planTimeInLimitStart'];
	$planTimeInLimitEnd = $row1['planTimeInLimitEnd'];

	$planTimeOutLimitStart = $row1['planTimeOutLimitStart'];
	$planTimeOutLimitEnd = $row1['planTimeOutLimitEnd'];

	$PlanInDif = $row1['PlanInDif'];
	$PlanOutDif = $row1['PlanOutDif'];

	$randomPickTimeIn = $row1['randomPickTimeIn'];
	$randomPickTimeOut = $row1['randomPickTimeOut'];
	$randomDropTimeIn = $row1['randomDropTimeIn'];
	$randomDropTimeOut = $row1['randomDropTimeOut'];
	$StopTypeEnumVal = $row1['StopTypeEnumVal'];

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
		where t1.Start_Datetime between date_add(now(), INTERVAL -6 day) and date_add(now(), INTERVAL 1 day) and t2.PlanIN_Datetime<'$PlanIN_Datetime'
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
				where t4.gps_updateDatetime between '$planTimeInLimitStart' and '$planTimeInLimitEnd'
				and t4.gps_updateDatetime > '$reCheckAfterLoadID_row1[LastDropLoadID]'
				and t4.truckLicense='$truckLicense' and t5.code='$Supplier_Code' and ST_Contains(t5.geo,(t4.geo))=1 limit 1";
			}
			else
			{
				$sql = "SELECT t4.gps_updateDatetime,t4.truckLicense,t5.code
				from tbl_truck_log t4 ,tbl_supplier t5
				where t4.gps_updateDatetime between '$planTimeInLimitStart' and '$planTimeInLimitEnd'
				and t4.gps_updateDatetime >'$LastActualTimeOut'
				and t4.truckLicense='$truckLicense' and t5.code='$Supplier_Code' and ST_Contains(t5.geo,(t4.geo))=1 limit 1";
			}

			if( $check_aat_ftm == 2 && $Gate_In_Datetime === NULL && $LastPickDatetime !== NULL)
			{
				$sqlGateIn = "SELECT t4.gps_updateDatetime,t4.truckLicense,t5.code
				from tbl_truck_log t4 ,tbl_supplier t5
				where t4.gps_updateDatetime between '$planTimeInLimitStart' and '$planTimeInLimitEnd'
				and t4.gps_updateDatetime >'$LastPickDatetime'
				and t4.truckLicense='$truckLicense' and t5.code='$aat_ftm' and ST_Contains(t5.geo,(t4.geo))=1 limit 1";

				$reGateIN = sqlError($mysqli,__LINE__,$sqlGateIn,1,1);
				if($reGateIN->num_rows > 0)
				{
					$gps_updateDatetime = $reGateIN->fetch_array(MYSQLI_ASSOC)['gps_updateDatetime'];
					sqlError($mysqli,__LINE__,
					"UPDATE tbl_204body_api set Gate_In_Datetime='$gps_updateDatetime' where Load_ID='$Load_ID'",1);
					if($mysqli->affected_rows > 0)
					{
						echo "Gate IN=$Load_ID - $StopSequenceNumber \n";
					}
				}	
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
		else if($check_aat_ftm == 2 && $PlanInDif>=0)
		{
			$randomtime = $StopTypeEnumVal === 'ST_PICKONLY' ? $randomPickTimeIn:$randomDropTimeIn;
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

			$sql = "UPDATE tbl_204body_api
			set ActualIN_Datetime='$randomtime',`Status`='ARRIVE',
			Update_ActualIN_API=Update_ActualIN_API+1,
			Update_ActualIN_Datetime=now(),Update_ActualIN_Datetime_By='1'
			where Load_ID='$Load_ID' and StopSequenceNumber='$StopSequenceNumber' and `Status`='IN TRANSIT' limit 1";
			sqlError($mysqli,__LINE__,$sql,1);
			if($mysqli->affected_rows > 0)
			{
				echo "IN Random=$Load_ID - $StopSequenceNumber \n";
			}

		}
	}
	else if($row1['Status'] === 'Enter time Out')
	{
		$sql = "SELECT t4.gps_updateDatetime,t4.truckLicense,t5.code
		from tbl_truck_log t4 ,tbl_supplier t5
		where t4.gps_updateDatetime between '$planTimeOutLimitStart' and '$planTimeOutLimitEnd'
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
		else if($check_aat_ftm == 2 && $PlanOutDif>=0) 
		{
			$randomtime = $StopTypeEnumVal === 'ST_DROPONLY' ? $randomDropTimeOut:$randomPickTimeOut;
			$sql = "UPDATE tbl_204body_api
			set ActualOut_Datetime='$randomtime',`Status`='COMPLETED',
			Update_ActualOut_API=Update_ActualOut_API+1,
			Update_ActualOut_Datetime=now(),Update_ActualOut_Datetime_By='1'
			where Load_ID='$Load_ID' and StopSequenceNumber='$StopSequenceNumber' and `Status`='ARRIVE' limit 1";
			sqlError($mysqli,__LINE__,$sql,1,1);
			if($mysqli->affected_rows > 0)
			{
				echo "OUT Random=$Load_ID - $StopSequenceNumber \n";
				if($NumberOfStops == $StopSequenceNumber)
				{
					$sql = "DELETE from tbl_truckmonitor where Load_ID='$Load_ID'";
					sqlError($mysqli,__LINE__,$sql,1);
					
					$sql = "UPDATE tbl_204header_api set `Status`='COMPLETED' where Load_ID='$Load_ID' limit 1;";
					sqlError($mysqli,__LINE__,$sql,1);
					
				}
			}
		}

		if( $check_aat_ftm == 2 && $Gate_In_Datetime !== NULL && $Gate_Out_Datetime === NULL)
		{
			$sqlGateIn = "SELECT t4.gps_updateDatetime,t4.truckLicense,t5.code
			from tbl_truck_log t4 ,tbl_supplier t5
			where t4.gps_updateDatetime between '$planTimeOutLimitStart' and '$planTimeOutLimitEnd'
			and t4.gps_updateDatetime >'$Gate_In_Datetime'
			and t4.truckLicense='$truckLicense' and t5.code='$aat_ftm' and ST_Contains(t5.geo,(t4.geo))=0 limit 1";

			$reGateIN = sqlError($mysqli,__LINE__,$sqlGateIn);
			if($reGateIN->num_rows > 0)
			{
				$gps_updateDatetime = $reGateIN->fetch_array(MYSQLI_ASSOC)['gps_updateDatetime'];
				sqlError($mysqli,__LINE__,
				"UPDATE tbl_204body_api set Gate_Out_Datetime='$gps_updateDatetime' where Load_ID='$Load_ID'",1);
				if($mysqli->affected_rows > 0)
				{
					echo "Gate OUT=$Load_ID - $StopSequenceNumber \n";
				}
			}	
		}
	}

}

$mysqli->close();
?>