<?php
$port = 80;
$fd = 'files/';

$doctype = $_REQUEST['doctype'];
$printerName = $_REQUEST['printerName'];
$copy = $_REQUEST['copy'];
$printType = $_REQUEST['printType'];
$warter = $_REQUEST['warter'];

// $doctype = '396290';
include('../php/connection.php');
$sql =
"SELECT t2.ID,t4.projectName
from tbl_204header_api t1
left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID
left join tbl_route_master_header t3 on t1.Route=t3.routeName
left join tbl_project_master t4 on t3.projectID=t4.ID
where t1.Load_ID='$doctype' and t2.StopTypeEnumVal='ST_PICKONLY' order by t2.StopSequenceNumber;";


require('concatpdf/fpdf_merge.php');
$merge = new FPDF_Merge();
if($result = $mysqli->query($sql)) 
{ 
    $len = $result->num_rows;
    if($len > 0)
    {  
        
        $pdfBuff = file_get_contents('http://localhost:'.$port.'/aatmr_v3/print/truckControl.php?doctype='.$doctype.
        '&copy=1&printType=F&printerName=1401&warter=NO');
        $merge->add($fd.$pdfBuff);
        unlink($fd.$pdfBuff);
        $projectManual = array('Manual  SCM','E-Smart');
        

          
        $projectName = '';
        while($row = $result->fetch_object())
        {
          $data = $row->ID;
          $projectName = $row->projectName;
          if(in_array($projectName, $projectManual)) 
          {
              
          } 
          else 
          {
            
            $pdfBuff = file_get_contents('http://localhost:'.$port.'/aatmr_v3/print/pickupByidAll.php?doctype='.$data.'&copy=1&printType=F&printerName=1401&warter=NO');
            $merge->add($fd.$pdfBuff);
            unlink($fd.$pdfBuff);
          }
            
        }

        $pdfBuff = file_get_contents('http://localhost:'.$port.'/aatmr_v3/print/packageControl.php?doctype='.$doctype.'&copy=1&printType=F&printerName=1401&warter=NO');
        $merge->add($fd.$pdfBuff);
        unlink($fd.$pdfBuff);

        /* if(in_array($projectName, $projectManual)) 
        {
            
        } 
        else 
        {
          $pdfBuff = file_get_contents('http://localhost:'.$port.'/aatmr_v3/print/packageControl.php?doctype='.$doctype.'&copy=1&printType=F&printerName=1401&warter=NO');
          $merge->add($fd.$pdfBuff);
          unlink($fd.$pdfBuff);
        } */
                                
        /* $pdfBuff = file_get_contents('http://localhost:'.$port.'/aatmr_v3/print/csControl.php?doctype='.$doctype.
        '&copy=1&printType=F&printerName=1401&warter=NO');
        $merge->add($fd.$pdfBuff);
        unlink($fd.$pdfBuff); */
        
        // $merge->output();
        /* $printType ='F';
        $printerName='14';
        $copy = 1; */
        if(strlen($printerName) >0)
        {
          $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,5);
          $fileName = $randomString.strtotime(date('Y-m-d H:i:s')).'-'.$printerName.'-'.$copy.'.pdf'; 
          
          // echo '{"ch":1,"data":"เอกสารออกที่เครื่องปริ้นเตอร์ชื่อ '.$printerName.' จำนวน '.$copy.'ชุด"}';
          if($printType == 'F')
          {
            $merge->Output("files/".$fileName,$printType);
            echo $fileName;
          }
          else
          {
            $merge->Output();
          }
          
        }else echo '{ch:2,data:"ไม่สามารถปริ้นได้เนื่องจากคุณไม่ได้เลือกปริ้นเตอร์"}';
    }
    else echo 'ไม่พบข้อมูล '.$sql;
}
else echo 'Error';
$mysqli->close();





?>
