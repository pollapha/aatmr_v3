<?php
header("Access-Control-Allow-Origin: *");
include('../php/connection.php');
include('../common/commonFunc.php');

$sql = "SELECT X(geo)lat,Y(geo)lng,truckLicense,gps_speed,gps_angle,gps_customerName,gps_address,gps_updateDatetime
,gps_status from tbl_truck where ST_X(geo)>0;";
		$re1 = sqlError($mysqli,__LINE__,$sql);
        // if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูลในระบบ');
        closeDBT($mysqli,1,jsonRow($re1,true,0));
?>