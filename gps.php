<?php
$obj = $_POST['obj'];
$dateT = date('Y-m-d H:i:s');
$dataAr = array();
include('php/connection.php');
$c = 0;
$data = array();
for($i=0,$len=count($obj);$i<$len;$i++)
{
    
    $plateNumber = trim($obj[$i]['truckLicense']);
    if(strlen($plateNumber) == 0) continue;
    if(strpos($plateNumber,'_'))
    {
        $truckLicense = substr($plateNumber,strpos($plateNumber,'_')+1,strlen($plateNumber));
    }
    else
    {
        $truckLicense = $plateNumber;
    }
    $gps_speed = $obj[$i]['speedKmh'];
    $gps_angle = $obj[$i]['angle'];
    $gps_lat = $obj[$i]['lat'];
    $gps_lng = $obj[$i]['lng'];
    $timeStamp = $obj[$i]['timeStamp'];
    $geo = "GeomFromText('point($gps_lat $gps_lng)')";
    $data[] = "('$truckLicense','',now(),'SYSTEM',$gps_speed,$gps_angle,'','',$geo,'$timeStamp')";
}
if(count($data) > 0)
{
    $sql = 'INSERT INTO tbl_truck(truckLicense,truckType,createDatetime,createBy,
    gps_speed,gps_angle,gps_customerName,gps_address,geo,gps_updateDatetime)values';
    $sql .= join(',',$data).'ON DUPLICATE KEY UPDATE 
    gps_speed = if(X(geo)=X(values(geo)) and Y(geo)=Y(values(geo)),gps_speed,values(gps_speed)),
    gps_angle = if(X(geo)=X(values(geo)) and Y(geo)=Y(values(geo)),gps_angle,values(gps_angle)),
    gps_updateDatetime = if(X(geo)=X(values(geo)) and Y(geo)=Y(values(geo)),gps_updateDatetime,values(gps_updateDatetime)),
    geo = if(X(geo)=X(values(geo)) and Y(geo)=Y(values(geo)),geo,values(geo))';
    if(!$mysqli->query($sql)) echo $mysqli->error;
    if($mysqli->affected_rows > 0)
    {    	
    	$c += $mysqli->affected_rows;
    }
}

echo  $c.' rows data='.count($data);
$mysqli->close();
?>