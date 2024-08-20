<?php
    include('../php/connection.php');
    if(!isset($_REQUEST['printerName']) || !isset($_REQUEST['copy']) || !isset($_REQUEST['doctype'])
     || !isset($_REQUEST['printType']) || !isset($_REQUEST['warter']))
        closeDBT($mysqli,2,'ข้อมูลไม่ถูกต้อง 1');
    $printerName = checkTXT($mysqli,$_REQUEST['printerName']);
    $copy = checkINT($mysqli,$_REQUEST['copy']);
    $doctype = checkTXT($mysqli,$_REQUEST['doctype']);
    $printType = checkTXT($mysqli,$_REQUEST['printType']);
    $warter = checkTXT($mysqli,$_REQUEST['warter']);

    if(strlen($printerName) == 0 || strlen($doctype) == 0 || strlen($printType) == 0 || strlen($warter) == 0 || $copy == 0) 
        closeDBT($mysqli,2,'ข้อมูลไม่ถูกต้อง 2');

    if($printerName == 'NO_PRINT' && $printType == 'F')
    {
        echo '{"ch":2,"data":"ไม่สามารถปริ้นได้เนื่องจากคุณไม่ได้เลือกปริ้นเตอร์"}';
        exit();
    }

    /* $doctype = 'PUS18083000013';
    $copy = 1;
    $printType = 'I';
    $printerName = '1401';
    $warter = 'NO'; */

    $port = 80;
    $fd = 'vendor/';

    $sql = "SELECT s1.pusChild,t1.pusDate,t1.truckLicense,t1.truckType,t1.driverName,t1.phone,s1.ID,
    s1.statusTypeSort,timeFormat(s1.planTimeIn)planTimeIn,timeFormat(s1.planTimeOut)planTimeOut,t1.workType
    from tbl_transaction_header t1 
    left join tbl_transaction_body s1 on t1.ID=s1.transaction_headerID
    where t1.pus='$doctype' and s1.statusType IN ('LOAD') and s1.status not in('CANCEL BY TTV')
    order by s1.statusTypeSort,planTimeIn;";
    $re = sqlError($mysqli,__LINE__,$sql);
    if($re->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูล');

    require('fpdf_merge.php');
    $merge = new FPDF_Merge();

    while($row = $re->fetch_array(MYSQLI_ASSOC))
    {
        $pdfBuff = trim(file_get_contents('http://localhost:'.$port.'/mazdamr/print/pus.php?doctype='.$row['ID'].
        '&copy=1&printType=F&printerName=1401&warter=NO&workType='.urlencode($row['workType'])));
        $merge->add($pdfBuff);
        unlink($pdfBuff);
        
    }

    // $doctype = 'PUS18083000013';
    $pdfBuff = trim(file_get_contents('http://localhost:'.$port.'/mazdamr/print/truckControl.php?doctype='.$doctype.'&copy=1&printType=F&printerName=1401&warter=NO'));
    $merge->add($pdfBuff);
    unlink($pdfBuff);
    $merge->output();
    
?>