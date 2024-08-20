<?php
    if(!ob_start("ob_gzhandler")) ob_start();
    header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', FALSE);
    header('Pragma: no-cache');
    include('../start.php');
    session_start();
    if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName']) )
    {
        echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
        exit();
    }

    include('../php/connection.php');
    $val = checkTXT($mysqli,$_GET['filter']['value']);		
	if(strlen(trim($val)) == 0)
	{
		echo "[]";
		exit();
	}

	$sql = "SELECT t1.projectName value
	from tbl_project_master t1 where upper(t1.projectName) like upper('%$val%') limit 5;";
	if($re1 = $mysqli->query($sql))
	{
		$row = array();
		while($result = $re1->fetch_array(MYSQLI_ASSOC))
		{
			$row[] = $result['value'];
		}
		echo json_encode($row);
	}
	else
	{
		echo "[]";
    }
    
    $mysqli->close();
exit();
?>