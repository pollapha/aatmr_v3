<?php

mysqli_report(MYSQLI_REPORT_STRICT);
$mysqli = mysqli_init();
if (!$mysqli) {echo json_encode(array('ch'=>2,'data'=>'mysqli_init failed'));exit();}
try
{
  $mysqli->real_connect('ttvdbs.titan-vns.com', 'apichat', '!Q2w3e4r', 'aatmr_v2_test',3306);
  // $mysqli->real_connect('ttvdbs.titan-vns.com', 'apichat', '!Q2w3e4r', 'aatmr_v2_test',3306);
  $mysqli->set_charset("utf8");
}
catch( mysqli_sql_exception $e )
{
    echo json_encode(array('ch'=>2,'data'=>'ไม่สามารถติดต่อฐานข้อมูลได้'));
    exit();
}

?>