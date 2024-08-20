<?php 
include('connection.php');
include('config.php');

$apiConfig = json_decode($apiConfig);
$str = $apiConfig->username . ":" . $apiConfig->password;
$auth = "Basic " .base64_encode($str);

$bodyChangeINAr = array();
$bodyChangeOUTAr = array();

bodyChangeIN($mysqli,$apiConfig->url,$auth);
bodyChangeOUT($mysqli,$apiConfig->url,$auth);

echo '{"status":true}';

function bodyChangeIN($mysqli,$url,$auth)
{
    $mysqli->autocommit(FALSE);
    try {
        $sql = "SELECT t2.Load_ID,t2.StopSequenceNumber,t1.truckLicense TrailerNumber,
        date_format(t2.ActualIN_Datetime,'%Y-%m-%dT%H:%i:%s')DateIN,
        date_format(t2.ActualOut_Datetime,'%Y-%m-%dT%H:%i:%s')DateOUT,t2.Load_ID SystemLoadID,t2.Supplier_Code
        from tbl_204body_api t2
        left join tbl_204header_api t1 on t2.Load_ID=t1.Load_ID
        where t2.Update_ActualIN_API>t2.Update_ActualIN_API_Count and length(t1.truckLicense)>0
        order by t2.Load_ID,t2.StopSequenceNumber;";  
        if($re1 = $mysqli->query($sql))
		{
            echo 'bodyChangeIN OK<br>';
            if($re1->num_rows > 0)
            {
                while($obj = $re1->fetch_object())
                {
                    if($obj->StopSequenceNumber == 1)
                    {
                        $bodyChangeINAr[] = $obj;
                    }
                    else
                    {
                        $ss = $obj->StopSequenceNumber-1;
                        $sql2 = "SELECT t2.Load_ID,t2.StopSequenceNumber,t1.truckLicense TrailerNumber,
                                date_format(t2.ActualIN_Datetime,'%Y-%m-%dT%H:%i:%s')DateIN,
                                date_format(t2.ActualOut_Datetime,'%Y-%m-%dT%H:%i:%s')DateOUT,t2.Load_ID SystemLoadID,t2.Supplier_Code
                                from tbl_204body_api t2
                                left join tbl_204header_api t1 on t2.Load_ID=t1.Load_ID
                                where t2.Load_ID='$obj->Load_ID' and t2.StopSequenceNumber='$ss'
                                and t2.Update_ActualOut_API>0 and t2.Update_ActualOut_API=t2.Update_ActualOut_API_Count;";
                        if($re2 = $mysqli->query($sql2))
                        {
                            if($re2->num_rows > 0)
                            {
                                while($obj2 = $re2->fetch_object())
                                {
                                    $bodyChangeINAr[] = $obj2;
                                }
                            }
                        }   
                    }
                }
            }
            echo count($bodyChangeINAr).'<br>';
		}
		else
		{
            echo "not math Body IN Change".date("Y m d H:i:s");;
        }
        $mysqli->commit();
      }
      catch(Exception $e) {
        $mysqli->rollback();
        echo 'Message: ' .$e->getMessage();
      }
    if(isset($bodyChangeINAr))
    {
        while(sizeof($bodyChangeINAr))
        {            
            $data = array_shift($bodyChangeINAr);
            callapi($url,$auth,$data,'bodyChangeIN',$mysqli);
        }
        return true;
    }
    else
    {
        //nodata for update
        return false;
    }
}

function bodyChangeOUT($mysqli,$url,$auth)
{
    $mysqli->autocommit(FALSE);
    try {
        $sql = "SELECT t2.Load_ID,t2.StopSequenceNumber,t1.truckLicense TrailerNumber,
        date_format(t2.ActualIN_Datetime,'%Y-%m-%dT%H:%i:%s')DateIN,
        date_format(t2.ActualOut_Datetime,'%Y-%m-%dT%H:%i:%s')DateOUT,t2.Load_ID SystemLoadID,t2.Supplier_Code
        from tbl_204body_api t2
        left join tbl_204header_api t1 on t2.Load_ID=t1.Load_ID
        where t2.Update_ActualOut_API>t2.Update_ActualOut_API_Count 
        and t2.Update_ActualIN_API>0 and t2.Update_ActualIN_API=t2.Update_ActualIN_API_Count
        and length(t1.truckLicense)>0
        order by t2.Load_ID,t2.StopSequenceNumber;";  
        if($re1 = $mysqli->query($sql))
		{
            echo 'bodyChangeOUT OK<br>';
            if($re1->num_rows > 0)
            {
                while($obj = $re1->fetch_object())
                {
                    $bodyChangeOUTAr[] = $obj;
                }
            }
		}
		else
		{
            echo "not math Body OUT Change".date("Y m d H:i:s");;
        }
        $mysqli->commit();
      }
      catch(Exception $e) {
        $mysqli->rollback();
        echo 'Message: ' .$e->getMessage();
      }
    if(isset($bodyChangeOUTAr))
    {
        while(sizeof($bodyChangeOUTAr))
        {            
            $data = array_shift($bodyChangeOUTAr);
            callapi($url,$auth,$data,'bodyChangeOUT',$mysqli);
        }
        return true;
    }
    else{
        //nodata for update
        return false;
    }
}

function callapi($url,$auth,$data,$type,$mysqli){
    
    $headers = [
        'Authorization: '.$auth,
        'Content-Type: text/xml',
        'SOAPAction: ""',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.87 Safari/537.36',
        'Cookie:""'
    ];
    if($type == 'bodyChangeIN')
    {
        $input_xml = processLoadStatusUpdateIN($data);
    }
    else
    {
        $input_xml = processLoadStatusUpdateOUT($data);
    }
    try {
        $ch = curl_init();
        if ($ch === false) {
            throw new Exception('failed to initialize');
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $input_xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        if ($result === false) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $header_size);
        $body = strval(substr($result, $header_size));
        $xml = simplexml_load_string(($body));
        $body_obj = $xml->children("soapenv", true)->children("ns1", true);
        
        if($xml->children("soapenv", true)->children("ns1", true)->processLoadStatusUpdateResponse)
        {
            
            $res = $xml->children("soapenv", true)->children("ns1", true)->processLoadStatusUpdateResponse;
            $apiHeader = $res->ApiHeader;
            $responseHeader = $res->ResponseHeader;
            if($responseHeader->CompletedSuccessfully == true)
            {
                echo "test$type<br>";
                if($type == 'bodyChangeIN')
                {
                    $TYPE_IO = 'IN';
                }
                else
                {
                    $TYPE_IO = 'OUT';
                }
                // echo $TYPE_IO.' Load_ID '.$data->Load_ID.'-'.$data->StopSequenceNumber.' Update Time Successfully<br>';
                if($type =='bodyChangeIN')
                {
                     bodyChangeIN_Update($mysqli,$data);
                     return true;
                }
                else if($type =='bodyChangeOUT')
                {
                     bodyChangeOUT_Update($mysqli,$data);
                     return true;
                }   
            }
        }
        else
        {
            return false;
            // echo 'Load_ID '.$data->Load_ID.'-'.$data->StopSequenceNumber.' Update Time False<br>';
        }
        curl_close($ch);
    } catch (Exception $e) {
        trigger_error(sprintf(
            'Curl failed with error #%d: %s',
            $e->getCode(), $e->getMessage()),
            E_USER_ERROR);
    }
}

function processLoadStatusUpdateIN($data)
{
    $res = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:cis="http://www.i2.com/cis">
    <soap:Header/>
    <soap:Body>
       <cis:processLoadStatusUpdate>      
          <cis:ApiHeader>
             <cis:OperationName>processLoadStatusUpdate</cis:OperationName>
          </cis:ApiHeader>
          <cis:LoadStatusUpdateData>      
             <cis:SystemLoadID>$SystemLoadID</cis:SystemLoadID>
             <cis:TrailerNumber>$TrailerNumber</cis:TrailerNumber>          
             <cis:StopArrivalDepartureData>
                <cis:ShippingLocationCode>$Supplier_Code</cis:ShippingLocationCode>
                <cis:StopTypeEnumVal>STR_NULL</cis:StopTypeEnumVal>
                <cis:ArrivalDateTime>$DateIN</cis:ArrivalDateTime>
                <cis:ArrivalEventCode>DRVRCHKIN_</cis:ArrivalEventCode>
                <cis:UpdateStatusFlag>true</cis:UpdateStatusFlag>
             </cis:StopArrivalDepartureData>
          </cis:LoadStatusUpdateData>        
          <cis:EventSourceEnumVal>EVENTSRC_API</cis:EventSourceEnumVal>
       </cis:processLoadStatusUpdate>
    </soap:Body>
 </soap:Envelope>';
    $vars = array(
        '$SystemLoadID' => $data->SystemLoadID,
        '$TrailerNumber' => $data->TrailerNumber,
        '$Supplier_Code' => $data->Supplier_Code,
        '$DateIN' => $data->DateIN,
      );
return strtr($res, $vars);
}

function processLoadStatusUpdateOUT($data)
{
    $res = '<soap:Envelope xmlns:soap="http://www.w3.org/2003/05/soap-envelope" xmlns:cis="http://www.i2.com/cis">
    <soap:Header/>
    <soap:Body>
       <cis:processLoadStatusUpdate>      
          <cis:ApiHeader>
             <cis:OperationName>processLoadStatusUpdate</cis:OperationName>
          </cis:ApiHeader>
          <cis:LoadStatusUpdateData>      
             <cis:SystemLoadID>$SystemLoadID</cis:SystemLoadID>
             <cis:TrailerNumber>$TrailerNumber</cis:TrailerNumber>          
             <cis:StopArrivalDepartureData>
                <cis:ShippingLocationCode>$Supplier_Code</cis:ShippingLocationCode>
                <cis:StopTypeEnumVal>STR_NULL</cis:StopTypeEnumVal>              
                <cis:DepartureDateTime>$DateOUT</cis:DepartureDateTime>
                <cis:DepartureEventCode>DRVRCHKOUT_</cis:DepartureEventCode>
                <cis:UpdateStatusFlag>true</cis:UpdateStatusFlag>
             </cis:StopArrivalDepartureData>
          </cis:LoadStatusUpdateData>        
          <cis:EventSourceEnumVal>EVENTSRC_API</cis:EventSourceEnumVal>
       </cis:processLoadStatusUpdate>
    </soap:Body>
 </soap:Envelope>';
    $vars = array(
        '$SystemLoadID' => $data->SystemLoadID,
        '$TrailerNumber' => $data->TrailerNumber,
        '$Supplier_Code' => $data->Supplier_Code,
        '$DateOUT' => $data->DateOUT,
      );
return strtr($res, $vars);
}


function bodyChangeIN_Update($mysqli,$data){

    $mysqli->autocommit(FALSE);
    try {
        echo "bodyChangeIN_Update<br>";
        $sql = "UPDATE tbl_204body_api set Update_ActualIN_API_Count=Update_ActualIN_API
        where Load_ID='$data->Load_ID' and Supplier_Code='$data->Supplier_Code' and  Update_ActualIN_API>Update_ActualIN_API_Count limit 1;";  
        if(!$mysqli->query($sql)) throw new Exception('error update'); 
        if($mysqli->affected_rows == 0) throw new Exception('update failed2');
        $mysqli->commit();
        $res =  "Load ID ".$data->Load_ID.' Completle<br>';
      }
      catch(Exception $e) {
        // $mysqli->rollback();
        $res =  'Message: ' .$e->getMessage();
      }
      return $res;
}

function bodyChangeOUT_Update($mysqli,$data){

    $mysqli->autocommit(FALSE);
    try {
        $sql = "UPDATE tbl_204body_api set Update_ActualOut_API_Count=Update_ActualOut_API
        where Load_ID='$data->Load_ID' and Supplier_Code='$data->Supplier_Code' and  Update_ActualOut_API>Update_ActualOut_API_Count limit 1;";  
        if(!$mysqli->query($sql)) throw new Exception('error update'); 
        if($mysqli->affected_rows == 0) throw new Exception('update failed2');
        $mysqli->commit();
        $res =  "Load ID ".$data->Load_ID.' Completle<br>';
      }
      catch(Exception $e) {
        // $mysqli->rollback();
        $res =  'Message: ' .$e->getMessage();
      }
      return $res;
}
$mysqli->close();
exit();
?>