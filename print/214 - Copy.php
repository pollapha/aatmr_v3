<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');


include('../php/connection.php');
$txt_x1= '';
$txt_x3= '';
$txt_af= '';
$txt_d1= '';
$numberRun = 0;
$sql = <<<EEE
select b2.id outID,h.b2,b1.route,b1.acDocDate,b1.acDocTime,b1.acOutDocDate,b1.acOutDocTime,b1.supplierGeographic,h.trucklicense,b1.s5_seq,b1.l11_2i,
group_concat(od.orderid order by od.id separator ',')orderID,b1.shortJD
from tbl_204header h 
left join tbl_204body b1 on h.isa=b1.isa
left join tbl_204body b2 on b1.isa=b2.isa
left join tbl_204order od on b1.isa=od.isa
where b1.docDate='2016-12-16'  and b1.type='LOAD' and b2.type='UNLOAD' and b1.route='NA2090'
and b1.supplierCode=od.supplierCode
and b1.ediStatus='ACTIVE' and b1.ediStatus=b2.ediStatus
group by b1.s5_seq
EEE;
$fileAr = array();
if(!$re1 = $mysqli->query($sql)) {echo json_encode(array('ch'=>2,'data'=>'NO1'));closeDB($mysqli);} 
if($re1->num_rows==0) {echo json_encode(array('ch'=>2,'data'=>'NO2'));closeDB($mysqli);} 
while ($row1 = $re1->fetch_array(MYSQLI_ASSOC))
{
    $outID = $row1['outID'];

    $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,22);
    $fileName = 'FC_OUT_214_PSKL_X3_'.$randomString.'_'.date('YmdHis').substr(explode(' ',microtime())[0],5,3).'.edi'; 
    $fileAr[] = array('fileName'=>$fileName,'data'=>gen_x3($mysqli,$row1));

    $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,22);
    $fileName = 'FC_OUT_214_PSKL_AF_'.$randomString.'_'.date('YmdHis').substr(explode(' ',microtime())[0],5,3).'.edi'; 
    $fileAr[] = array('fileName'=>$fileName,'data'=>gen_af($mysqli,$row1));
}

$sql = <<<EEE
select h.b2,b1.route,b1.acDocDate,b1.acDocTime,b1.acOutDocDate,b1.acOutDocTime,b1.supplierGeographic,h.trucklicense,b1.s5_seq,b1.l11_2i,
group_concat(od.orderid order by od.id separator ',')orderID,b1.shortJD
from tbl_204header h 
left join tbl_204body b1 on h.isa=b1.isa
left join tbl_204order od on b1.isa=od.isa
where b1.id=$outID
and b1.supplierCode=od.supplierCode
and b1.ediStatus='ACTIVE'
group by b1.s5_seq
EEE;
if(!$re1 = $mysqli->query($sql)) {echo json_encode(array('ch'=>2,'data'=>'NO3'));closeDB($mysqli);} 
if($re1->num_rows==0) {echo json_encode(array('ch'=>2,'data'=>'NO4'));closeDB($mysqli);}
while ($row1 = $re1->fetch_array(MYSQLI_ASSOC))
{
    $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,24);
    $fileName = 'FC_OUT_214_PSKL_X1_'.$randomString.'_'.date('YmdHis').substr(explode(' ',microtime())[0],5,3).'.edi'; 
    $fileAr[] = array('fileName'=>$fileName,'data'=>gen_x1($mysqli,$row1));
    
    $randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,24);
    $fileName = 'FC_OUT_214_PSKL_D1_'.$randomString.'_'.date('YmdHis').substr(explode(' ',microtime())[0],5,3).'.edi'; 
    $fileAr[] = array('fileName'=>$fileName,'data'=>gen_d1($mysqli,$row1));
}

for($i=0,$len=count($fileAr);$i<$len;$i++)
{
    $directory = '../214/';
    $fullFileName = $directory.$fileAr[$i]['fileName'];
    $objFopen = fopen($fullFileName, 'w');
    fwrite($objFopen, $fileAr[$i]['data']);
    fclose($objFopen);
}

function gen_x3($mysqli,$row1)
{
    $Ymd = date('Ymd');
    $ymd = date('ymd');
    $Hi = date('Hi');
    $runingNumber = $mysqli->query("select func_GenRuningNumber('edi_header1',0)edi_header1,func_GenRuningNumber('edi_header2',0)edi_header2")->fetch_array(MYSQLI_ASSOC);
    $edi_header1 = $runingNumber['edi_header1'];
    $edi_header2 = $runingNumber['edi_header2'];
    $templet  = "ISA*00*          *00*          *02*TTVT           *02*PSKL           *{ymd}*{Hi}*U*00400*{edi_header1}*0*T*;\n";
    $templet .= "GS*QM*TTVT*PSKL*{Ymd}*{Hi}*{edi_header1*1}*X*004010\n";
    $templet .= "ST*214*{edi_header2}0001\n";
    $templet .= "{body}";
    $templet .= "SE*{numLine}*{edi_header2}0001\n";
    $templet .= "GE*1*{edi_header1*1}\n";
    $templet .= "IEA*1*{edi_header1}\n";
    $templet = str_replace("{edi_header1}",$edi_header1,$templet);
    $templet = str_replace("{edi_header1*1}",$edi_header1*1,$templet);
    $templet = str_replace("{edi_header2}",$edi_header2,$templet);
    $templet = str_replace("{Ymd}",$Ymd,$templet);
    $templet = str_replace("{ymd}",$ymd,$templet);
    $templet = str_replace("{Hi}",$Hi,$templet);

    $body  = "B10*{bol}*{bol}*TTVT\n";
    $body .= "L11*{routeNo}*RN\n";
    $body .= "LX*1\n";
    $body .= "AT7*X3*NS***{Ymd}*{Hi}\n";
    $body .= "MS1*{supplierGeographic}*{shortJD}\n";
    $body .= "MS2*TTVT*{truckLicense}*TL\n";
    $body .= "L11*{s5_seq}*QN\n";
    $body .= "L11*{l11_2i}*21\n";

    $body = str_replace("{bol}",$row1['b2'],$body);
    $body = str_replace("{Ymd}",date('Ymd',strtotime($row1['acDocDate'])),$body);
    $body = str_replace("{Hi}",date('Hi',strtotime($row1['acDocTime'])),$body);
    $body = str_replace("{supplierGeographic}",$row1['supplierGeographic'],$body);
    $body = str_replace("{truckLicense}",$row1['trucklicense'],$body);
    $body = str_replace("{s5_seq}",$row1['s5_seq'],$body);
    $body = str_replace("{l11_2i}",$row1['l11_2i'],$body);
    $body = str_replace("{shortJD}",$row1['shortJD'],$body);
    $body = str_replace("{routeNo}",$row1['route'],$body);
    $se = count(explode("\n",$body))-1;
    $templet = str_replace("{numLine}",$se+2,$templet);
    $templet = str_replace("{body}",$body,$templet);
    $templet = explode("\n",$templet);
    $templet = join("~",$templet);
    return $templet;
}

function gen_af($mysqli,$row1)
{
    $Ymd = date('Ymd');
    $ymd = date('ymd');
    $Hi = date('Hi');

    $runingNumber = $mysqli->query("select func_GenRuningNumber('edi_header1',0)edi_header1,func_GenRuningNumber('edi_header2',0)edi_header2")->fetch_array(MYSQLI_ASSOC);
    $edi_header1 = $runingNumber['edi_header1'];
    $edi_header2 = $runingNumber['edi_header2'];
    $templet  = "ISA*00*          *00*          *02*TTVT           *02*PSKL           *{ymd}*{Hi}*U*00400*{edi_header1}*0*T*;\n";
    $templet .= "GS*QM*TTVT*PSKL*{Ymd}*{Hi}*{edi_header1*1}*X*004010\n";
    $templet .= "ST*214*{edi_header2}0001\n";
    $templet .= "{body}";
    $templet .= "SE*{numLine}*{edi_header2}0001\n";
    $templet .= "GE*1*{edi_header1*1}\n";
    $templet .= "IEA*1*{edi_header1}\n";
    $templet = str_replace("{edi_header1}",$edi_header1,$templet);
    $templet = str_replace("{edi_header1*1}",$edi_header1*1,$templet);
    $templet = str_replace("{edi_header2}",$edi_header2,$templet);
    $templet = str_replace("{Ymd}",$Ymd,$templet);
    $templet = str_replace("{ymd}",$ymd,$templet);
    $templet = str_replace("{Hi}",$Hi,$templet);

    $body  = "B10*{bol}*{bol}*TTVT\n";
    $body .= "L11*{routeNo}*RN\n";
    $body .= "LX*1\n";
    $body .= "AT7*AF*NS***{Ymd}*{Hi}\n";
    $body .= "MS1*{supplierGeographic}*{shortJD}\n";
    $body .= "MS2*TTVT*{truckLicense}*TL\n";
    $body .= "L11*{bol}*BM\n";
    $body .= "{orderID}";
    $body .= "L11*{s5_seq}*QN\n";
    $body .= "L11*{l11_2i}*21\n";

    $body = str_replace("{bol}",$row1['b2'],$body);
    $body = str_replace("{Ymd}",date('Ymd',strtotime($row1['acOutDocDate'])),$body);
    $body = str_replace("{Hi}",date('Hi',strtotime($row1['acOutDocTime'])),$body);
    $body = str_replace("{supplierGeographic}",$row1['supplierGeographic'],$body);
    $body = str_replace("{truckLicense}",$row1['trucklicense'],$body);
    $body = str_replace("{s5_seq}",$row1['s5_seq'],$body);
    $body = str_replace("{l11_2i}",$row1['l11_2i'],$body);
    $body = str_replace("{shortJD}",$row1['shortJD'],$body);
    $body = str_replace("{routeNo}",$row1['route'],$body);
    $orderAr = explode(",",$row1['orderID']);
    $order = '';
    for($i=0,$len=count($orderAr);$i<$len;$i++)
    {
        $order .= "L11*".$orderAr[$i]."*IT\n";
    }
    $body = str_replace("{orderID}",$order,$body);
    $se = count(explode("\n",$body))-1;
    $templet = str_replace("{numLine}",$se+2,$templet);
    $templet = str_replace("{body}",$body,$templet);
    $templet = explode("\n",$templet);
    $templet = join("~",$templet);
    return $templet;
}

function gen_x1($mysqli,$row1)
{
    $Ymd = date('Ymd');
    $ymd = date('ymd');
    $Hi = date('Hi');
    $runingNumber = $mysqli->query("select func_GenRuningNumber('edi_header1',0)edi_header1,func_GenRuningNumber('edi_header2',0)edi_header2")->fetch_array(MYSQLI_ASSOC);
    $edi_header1 = $runingNumber['edi_header1'];
    $edi_header2 = $runingNumber['edi_header2'];
    $templet  = "ISA*00*          *00*          *02*TTVT           *02*PSKL           *{ymd}*{Hi}*U*00400*{edi_header1}*0*T*;\n";
    $templet .= "GS*QM*TTVT*PSKL*{Ymd}*{Hi}*{edi_header1*1}*X*004010\n";
    $templet .= "ST*214*{edi_header2}0001\n";
    $templet .= "{body}";
    $templet .= "SE*{numLine}*{edi_header2}0001\n";
    $templet .= "GE*1*{edi_header1*1}\n";
    $templet .= "IEA*1*{edi_header1}\n";
    $templet = str_replace("{edi_header1}",$edi_header1,$templet);
    $templet = str_replace("{edi_header1*1}",$edi_header1*1,$templet);
    $templet = str_replace("{edi_header2}",$edi_header2,$templet);
    $templet = str_replace("{Ymd}",$Ymd,$templet);
    $templet = str_replace("{ymd}",$ymd,$templet);
    $templet = str_replace("{Hi}",$Hi,$templet);

    $body  = "B10*{bol}*{bol}*TTVT\n";
    $body .= "L11*{routeNo}*RN\n";
    $body .= "LX*1\n";
    $body .= "AT7*X1*NS***{Ymd}*{Hi}\n";
    $body .= "MS1*{supplierGeographic}*{shortJD}\n";
    $body .= "MS2*TTVT*{truckLicense}*TL\n";
    $body .= "L11*{s5_seq}*QN\n";
    $body .= "L11*{l11_2i}*21\n";

    $body = str_replace("{bol}",$row1['b2'],$body);
    $body = str_replace("{Ymd}",date('Ymd',strtotime($row1['acDocDate'])),$body);
    $body = str_replace("{Hi}",date('Hi',strtotime($row1['acDocTime'])),$body);
    $body = str_replace("{supplierGeographic}",$row1['supplierGeographic'],$body);
    $body = str_replace("{truckLicense}",$row1['trucklicense'],$body);
    $body = str_replace("{s5_seq}",$row1['s5_seq'],$body);
    $body = str_replace("{l11_2i}",$row1['l11_2i'],$body);
    $body = str_replace("{shortJD}",$row1['shortJD'],$body);
    $body = str_replace("{routeNo}",$row1['route'],$body);
    $se = count(explode("\n",$body))-1;
    $templet = str_replace("{numLine}",$se+2,$templet);
    $templet = str_replace("{body}",$body,$templet);
    $templet = explode("\n",$templet);
    $templet = join("~",$templet);
    return $templet;
}

function gen_d1($mysqli,$row1)
{
    $Ymd = date('Ymd');
    $ymd = date('ymd');
    $Hi = date('Hi');

    $runingNumber = $mysqli->query("select func_GenRuningNumber('edi_header1',0)edi_header1,func_GenRuningNumber('edi_header2',0)edi_header2")->fetch_array(MYSQLI_ASSOC);
    $edi_header1 = $runingNumber['edi_header1'];
    $edi_header2 = $runingNumber['edi_header2'];
    $templet  = "ISA*00*          *00*          *02*TTVT           *02*PSKL           *{ymd}*{Hi}*U*00400*{edi_header1}*0*T*;\n";
    $templet .= "GS*QM*TTVT*PSKL*{Ymd}*{Hi}*{edi_header1*1}*X*004010\n";
    $templet .= "ST*214*{edi_header2}0001\n";
    $templet .= "{body}";
    $templet .= "SE*{numLine}*{edi_header2}0001\n";
    $templet .= "GE*1*{edi_header1*1}\n";
    $templet .= "IEA*1*{edi_header1}\n";
    $templet = str_replace("{edi_header1}",$edi_header1,$templet);
    $templet = str_replace("{edi_header1*1}",$edi_header1*1,$templet);
    $templet = str_replace("{edi_header2}",$edi_header2,$templet);
    $templet = str_replace("{Ymd}",$Ymd,$templet);
    $templet = str_replace("{ymd}",$ymd,$templet);
    $templet = str_replace("{Hi}",$Hi,$templet);

    $body  = "B10*{bol}*{bol}*TTVT\n";
    $body .= "L11*{routeNo}*RN\n";
    $body .= "LX*1\n";
    $body .= "AT7*D1*NS***{Ymd}*{Hi}\n";
    $body .= "MS1*{supplierGeographic}*{shortJD}\n";
    $body .= "MS2*TTVT*{truckLicense}*TL\n";
    $body .= "L11*{bol}*BM\n";
    $body .= "{orderID}";
    $body .= "L11*{s5_seq}*QN\n";
    $body .= "L11*{l11_2i}*21\n";

    $body = str_replace("{bol}",$row1['b2'],$body);
    $body = str_replace("{Ymd}",date('Ymd',strtotime($row1['acOutDocDate'])),$body);
    $body = str_replace("{Hi}",date('Hi',strtotime($row1['acOutDocTime'])),$body);
    $body = str_replace("{supplierGeographic}",$row1['supplierGeographic'],$body);
    $body = str_replace("{truckLicense}",$row1['trucklicense'],$body);
    $body = str_replace("{s5_seq}",$row1['s5_seq'],$body);
    $body = str_replace("{l11_2i}",$row1['l11_2i'],$body);
    $body = str_replace("{shortJD}",$row1['shortJD'],$body);
    $body = str_replace("{routeNo}",$row1['route'],$body);
    $orderAr = explode(",",$row1['orderID']);
    $order = '';
    for($i=0,$len=count($orderAr);$i<$len;$i++)
    {
        $order .= "L11*".$orderAr[$i]."*IT\n";
    }
    $body = str_replace("{orderID}",$order,$body);
    $se = count(explode("\n",$body))-1;
    $templet = str_replace("{numLine}",$se+2,$templet);
    $templet = str_replace("{body}",$body,$templet);
    $templet = explode("\n",$templet);
    $templet = join("~",$templet);
    return $templet;
}
$mysqli->close();



?>