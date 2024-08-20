<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if(empty($_SESSION['xxxID']))
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}

include('../php/connection.php');
$cBy = $_SESSION['xxxID'];
$role = $_SESSION['xxxPermission'];
$type  = intval($_REQUEST['type']);
// $filter  = $_REQUEST['filter'];
$type = $mysqli->real_escape_string($type);
// $filter = $mysqli->real_escape_string($filter["value"]);

if($type == 0)
{
	$obj = $mysqli->real_escape_string(trim(strtoupper($obj)));
	getData($mysqli,$obj);
}
else if($type == 1)
{
    $role  = $_REQUEST['role'];
    if($role == "ADMIN")
    {
        $sql = "SELECT Member_Name FROM tbl_member_master WHERE Member_Name  IN('SYSTEM','MY COMPANY')  GROUP BY Member_Name";
    }
    else
    {
        $sql = "SELECT Member_Name FROM tbl_member_master WHERE Member_Name <> 'SYSTEM'  GROUP BY Member_Name";
    }
	
	toArrayStringOne($mysqli->query($sql),1);

}
else if($type == 2)
{
    $member  = $_REQUEST['role'];
    $filter  = $_REQUEST['filter'];
    $filter = $mysqli->real_escape_string($filter["value"]);
    if($member == "SYSTEM" || $member == "MY COMPANY")
    {
        $sql = "SELECT Company_Code FROM tbl_companys WHERE Company_Code LIKE '%$filter%'  GROUP BY Company_Code";
    }
    else if($member == "CUSTOMER")
    {
        $sql = "SELECT Customer_Code FROM tbl_customer_master  WHERE Customer_Code LIKE '%$filter%' GROUP BY Customer_Code";
    }
    else
    {
        $sql = "SELECT Vendor_Code FROM tbl_vendor_master WHERE Vendor_Code LIKE '%$filter%'  GROUP BY Vendor_Code";
    }
	toArrayStringOne($mysqli->query($sql),1);

}

function getData($mysqli,$part)
{
	echo '{ch:1,data:';
	toArrayStringAddNumberRow($mysqli->query("SELECT partNo,qty,revision,fifo,replace(fifo,'-', '')*1 sortFifo,id FROM tbl_inventory where partNo='$part' and area='STORAGE' and ref=0 and _use=1 order by sortFifo"),1);
	echo '}';
}

$mysqli->close();
exit();

?>