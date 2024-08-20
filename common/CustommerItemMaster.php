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
    else if($_SESSION['xxxRole']->{'OrderPlanning'}[0] == 0)
    {
        echo "{ch:9,data:'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้'}";
        exit();
    }
    include('../php/connection.php');
    $val = checkTXT($mysqli,$_GET['filter']['value']);
    $Customer_Code = checkTXT($mysqli,$_GET['Customer_Code']);

    
	if(strlen(trim($val)) == 0 || strlen(trim($Customer_Code)) == 0)
	{
		echo "[]";
		exit();
	}
	
	$sql = "SELECT concat(t1.Cus_Code,'(',t2.Item_Code,')',' | ',t2.Item_Name)value
	from tbl_customer_items t1
    left join tbl_items_master t2 on t1.Item_ID=t2.Item_ID
    left join tbl_customer_master t3 on t1.Customer_ID=t3.Customer_ID
    where (t1.Cus_Code like '%$val%' or t2.Item_Code like '%$val%') and t3.Customer_Code='$Customer_Code' limit 5";
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
		echo "[2]";
    }
    
    $mysqli->close();
exit();
?>