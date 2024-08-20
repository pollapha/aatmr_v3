<?php
header("Access-Control-Allow-Origin: *");
include('../php/connection.php');
include('../common/commonFunc.php');

$sql = "SELECT t2.Load_ID,t2.StopSequenceNumber,t1.truckLicense TrailerNumber,
date_format(t2.ActualIN_Datetime,'%Y-%m-%dT%H:%i:%s')DateIN,
date_format(t2.ActualOut_Datetime,'%Y-%m-%dT%H:%i:%s')DateOUT,t2.Load_ID SystemLoadID,t2.Supplier_Code
from tbl_204body_api t2
left join tbl_204header_api t1 on t2.Load_ID=t1.Load_ID
where t2.Update_ActualIN_API>t2.Update_ActualIN_API_Count and length(t1.truckLicense)>0
order by t2.Load_ID,t2.StopSequenceNumber;";
$re1 = sqlError($mysqli,__LINE__,$sql);
$data = jsonRow($re1,true,0);
$bodyChangeINAr = array();
for($i=0,$len=count($data);$i<$len;$i++)
{
    $rows = $data[$i];
    if(intval($rows['StopSequenceNumber']) == 1)
    {
        $bodyChangeINAr[] = $rows;
    }
    else
    {
        $Load_ID = $rows['Load_ID'];
        $StopSequenceNumber = intval($rows['StopSequenceNumber'])-1;

        $sql = "SELECT t2.Load_ID,t2.StopSequenceNumber,t1.truckLicense TrailerNumber,
        date_format(t2.ActualIN_Datetime,'%Y-%m-%dT%H:%i:%s')DateIN,
        date_format(t2.ActualOut_Datetime,'%Y-%m-%dT%H:%i:%s')DateOUT,t2.Load_ID SystemLoadID,t2.Supplier_Code
        from tbl_204body_api t2
        left join tbl_204header_api t1 on t2.Load_ID=t1.Load_ID
        where t2.Load_ID='$Load_ID' and t2.StopSequenceNumber=$StopSequenceNumber 
        and t2.Update_ActualOut_API>0 and t2.Update_ActualOut_API=t2.Update_ActualOut_API_Count;";  

        $re1 = sqlError($mysqli,__LINE__,$sql);
        
        $checkSequence = jsonRow($re1,true,0);
        if(count($checkSequence)>0)
        {
            $bodyChangeINAr[] = $rows;
        }
    }
}

closeDBT($mysqli,1,$bodyChangeINAr);
   
?>