<?php
header("Access-Control-Allow-Origin: *");
include('../php/connection.php');
include('../common/commonFunc.php');

$sql = "SELECT ID,name supplier_name,code supplier_code,
ST_AsGeoJSON(geo)geo_polygon,ST_AsGeoJSON(ST_Centroid(geo)) center_polygon
from tbl_supplier;";
		$re1 = sqlError($mysqli,__LINE__,$sql);
        // if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูลในระบบ');
        closeDBT($mysqli,1,jsonRow($re1,true,0));
?>