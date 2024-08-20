<?php
    include('../php/connection.php');
    $re1 = getData($mysqli,__LINE__,array());
    $dataAr = array();
    while($row1 = $re1->fetch_array(MYSQLI_ASSOC))
    {
        $dataAr[] = $row1;
    }
    echo json_encode($dataAr);
    
function getData($mysqli,$lineCode,$data,$rollback=1)
{
    $sql = "SELECT ID id,Customer_Project value from tbl_abt_project;";
	return sqlError($mysqli,$lineCode,$sql);
}
?>