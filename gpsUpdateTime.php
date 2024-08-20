<?php
include('php/connection.php');

$chkPOST = checkParams($_POST,array('obj','obj=>header204_ID','obj=>body204_ID','obj=>monitorID',
		'obj=>acDocTime','obj=>acOutDocTime','obj=>supplierCode','obj=>lateTime'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
		
		$header204_ID = checkINT($mysqli,$_POST['obj']['header204_ID']);
		$body204_ID = checkINT($mysqli,$_POST['obj']['body204_ID']);
		$monitorID = checkINT($mysqli,$_POST['obj']['monitorID']);

		$acDocTime = checkTXT($mysqli,$_POST['obj']['acDocTime']);
        $acOutDocTime = checkTXT($mysqli,$_POST['obj']['acOutDocTime']);
        $supplierCode = checkTXT($mysqli,$_POST['obj']['supplierCode']);
        $lateTime = checkTXT($mysqli,$_POST['obj']['lateTime']);

		$acDocTime = strlen($acDocTime) == 0 ? 'null':"'$acDocTime'";
		$acOutDocTime = strlen($acOutDocTime) == 0 ? 'null':"'$acOutDocTime'";

		$sql = "SELECT t2.status,t2.type,t2.s5_seq,t1.b2
		from tbl_204header t1
		left join tbl_204body t2 on t1.isa=t2.isa and t1.st=t2.st
		left join tbl_truckmonitor t3 on t2.ID=t3.body204ID
		where t1.ID=$header204_ID and t2.ID=$body204_ID and t3.ID=$monitorID and t2.ediStatus='ACTIVE' limit 1;";
		$re1 = sqlError($mysqli,__LINE__,$sql);
		if($re1->num_rows == 0) closeDBT($mysqli,2,__LINE__.'ไม่พบข้อมูลในระบบ');
		$row = $re1->fetch_array(MYSQLI_ASSOC);
		$s5_seq = $row['s5_seq']*1;
		$oldStatus = $row['status'];
		if($row['s5_seq']*1 == 1)
		{
			if($row['status'] == 'PENDING')
			{
				if($acDocTime == 'null')
				{
					closeDBT($mysqli,2,__LINE__.'กรุณาป้อนเวลาเข้าจริง');
				}

			}
			else if($row['status'] == 'ARRIVE')
			{
				if($acDocTime == 'null')
				{
					closeDBT($mysqli,2,__LINE__.'กรุณาป้อนเวลาเข้าจริง');
				}
				if($acOutDocTime == 'null')
				{
					closeDBT($mysqli,2,__LINE__.'กรุณาป้อนเวลาออกจริง');
				}
			}
			// docDate,docTime,lateDate,lateTime status='',
		}
		else
		{
			$sql = "SELECT t2.s5_seq
			from tbl_truckmonitor t1
			left join tbl_204body t2 on t1.body204ID=t2.ID
			left join tbl_204header t3 on t2.isa=t3.isa and t2.st=t3.st
			where t3.ID=$header204_ID and t1.ID
			order by t3.b2,t2.s5_seq*1 limit 1;";
			$re1 = sqlError($mysqli,__LINE__,$sql);
			if($re1->num_rows == 0) closeDBT($mysqli,2,__LINE__.'ไม่พบข้อมูลในระบบ');
			$row1 = $re1->fetch_array(MYSQLI_ASSOC);

			if($s5_seq != $row1['s5_seq']*1)
			{
				closeDBT($mysqli,2,__LINE__.'ไม่สามารถกรอกข้อมูลข้ามได้');
			}

			if($row['status'] == 'PENDING')
			{
				closeDBT($mysqli,2,__LINE__.'ไม่สามารถกรอกข้อมูลข้ามได้');
			}
			else if($row['status'] == 'IN TRANSIT')
			{
				if($acDocTime == 'null')
				{
					closeDBT($mysqli,2,__LINE__.'กรุณาป้อนเวลาเข้าจริง');
				}
			}
			else if($row['status'] == 'ARRIVE')
			{
				if($acDocTime == 'null')
				{
					closeDBT($mysqli,2,__LINE__.'กรุณาป้อนเวลาเข้าจริง');
				}
				if($acOutDocTime == 'null')
				{
					closeDBT($mysqli,2,__LINE__.'กรุณาป้อนเวลาออกจริง');
				}
			}
        }
        $supplierCode_list = array("GRBNA", "GRBPA","GBL9A");
        if (in_array($supplierCode, $supplierCode_list))
        {
            $acOutDocTime = "'$lateTime'";
        }

		$mysqli->autocommit(FALSE);
		try
		{
			$sql = "UPDATE tbl_204body
			set acDocDate=docDate,acDocTime=$acDocTime,
			acOutDocDate=lateDate,acOutDocTime=$acOutDocTime
			where ID=$body204_ID limit 1";
			sqlError($mysqli,__LINE__,$sql,1);
			if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่พบการเปลียนแปลง');

			$sql = "SELECT ID,isa,st,type from tbl_204body 
			where ID=$body204_ID and acOutDocTime is not null limit 1";
			$re1 = sqlError($mysqli,__LINE__,$sql,1);
			$upStatus = $re1->num_rows == 0 ? 'ARRIVE':'COMPLETED';
			$body204 = $re1->fetch_array(MYSQLI_ASSOC);
			
			$sql = "UPDATE tbl_204body
			set status='$upStatus',updateDate=curdate(),updateTime=curtime(),updateBy='$fName'
			where ID=$body204_ID limit 1";
			sqlError($mysqli,__LINE__,$sql,1);
			if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่พบการเปลียนแปลง');

			if($s5_seq == 1 and $oldStatus=='PENDING')
			{
				$sql="UPDATE tbl_truckmonitor t1
				left join tbl_204body t2 on t1.body204ID=t2.ID
				left join tbl_204header t3 on t2.isa=t3.isa and t2.st=t3.st
				SET t2.status='IN TRANSIT'
				where t3.ID=$header204_ID and t1.ID<>$monitorID";
				sqlError($mysqli,__LINE__,$sql,1);
				if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่พบการเปลียนแปลง');
				
			}
			if($upStatus=='COMPLETED')
			{
				$sql = "DELETE from tbl_truckmonitor where ID=$monitorID limit 1";
				sqlError($mysqli,__LINE__,$sql,1);
				if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่พบการเปลียนแปลง');
				if($body204['type'] == 'UNLOAD' || $body204['type'] == 'CU')
				{
					send214($mysqli,$body204,'00');
					send214($mysqli,$body204,'03');
					send214($mysqli,$body204,'06');
					send214($mysqli,$body204,'09');
					send214($mysqli,$body204,'12');
					send214($mysqli,$body204,'15');
				}
				// 
				// send edi 
			}
			$mysqli->commit();
            closeDBT($mysqli,1,'บันทึกสำเร็จ');
		}
		catch( Exception $e )
		{
			$mysqli->rollback();
			closeDBT($mysqli,2,$e->getMessage());
        }

        
function send214($mysqli,$body204,$time=0)
{
	// ID,isa,st
	/* if($step == 1)
	{
		$limit = "limit 1";
	}
	else if($step == 2)
	{
		$limit = "and t2.type not in('UNLOAD','CU')";
	}
	else if($step == 3)
	{
		$limit = '';
	}
	else
	{
		$limit = '';
	} */
	$sql = "SELECT t1.b2,t1.docDate dateFirst,t1.docCount,t2.s5_seq,t2.route,t2.bm,t2.type,t2.supplierCode,t2.supplierName,t2.supplierAddress,
	t2.shortJD,t2.supplierGeographic,t2.supplierZip,t2.docDate,t2.docTime,t2.ediStatus,
	t2.updateDate,t2.updateTime,t2.updateBy,
	date_format(ADDTIME(concat(t2.acDocDate,' ',t2.acDocTime),'00:06'),'%H%i%s')agTime,
	date_format(ADDTIME(concat(t2.acDocDate,' ',t2.acDocTime),'00:06'),'%H%i%s')aaTime,
	date_format(ADDTIME(concat(t2.acDocDate,' ',t2.acDocTime),'00:01'),'%H%i%s')abTime,
	t1.supplierCode supplierCode_head,t1.supplierName supplierName_head,t1.supplierAddress supplierAddress_head,
	t1.shortJD shortJD_head,t1.supplierGeographic supplierGeographic_head,t1.supplierZip supplierZip_head,
	t1.trucklicense,t2.acDocDate,t2.acDocTime,t2.acOutDocDate,t2.acOutDocTime,t2.country,t2.identifi,
	t1.country country_head,t1.identifi identifi_head
	from tbl_204header t1
	left join tbl_204body t2 on t1.isa=t2.isa and t1.st=t2.st
	where t2.isa='$body204[isa]' and t2.st='$body204[st]';";

	if(!$re1 = $mysqli->query($sql)){echo json_encode(array('ch'=>2,'data'=>'Error Code 1'));closeDB($mysqli);}
	if($re1->num_rows ==0) {echo json_encode(array('ch'=>2,'data'=>'NOT MATCH'));closeDB($mysqli);}
	$Ymd = date('Ymd');
	$ymd = date('ymd');
	$Hi = date('Hi');
	$runingNumber = $mysqli->query("select func_GenRuningNumber('edi_header1',0)edi_header1,func_GenRuningNumber('edi_header2',0)edi_header2")->fetch_array(MYSQLI_ASSOC);
	$edi_header1 = $runingNumber['edi_header1'];
	$edi_header2 = $runingNumber['edi_header2'];
	$templet  = "ISA*00*          *00*          *ZZ*TTVTP          *ZZ*FMXFORD        *{ymd}*{Hi}*U*00401*{edi_header1}*0*T*;\n";
	$templet .= "GS*QM*TTVTP*FMXFORD*{Ymd}*{Hi}*{edi_header1*1}*X*004010\n";
	$templet .= "{body1}";
	$templet .= "GE*{ge}*{edi_header1*1}\n";
	$templet .= "IEA*1*{edi_header1}\n";
	$templet = str_replace("{edi_header1}",$edi_header1,$templet);
	$templet = str_replace("{edi_header1*1}",$edi_header1*1,$templet);
	$templet = str_replace("{edi_header2}",$edi_header2,$templet);
	$templet = str_replace("{Ymd}",$Ymd,$templet);
	$templet = str_replace("{ymd}",$ymd,$templet);
	$templet = str_replace("{Hi}",$Hi,$templet);

	$bodyAr = array();      
	$rowCount = 0;
	while($row1 = $re1->fetch_array(MYSQLI_ASSOC))
	{
	    $bodyAr[] =  gen_x1_body($Ymd,$ymd,$Hi,$runingNumber,$edi_header1,$edi_header2,$row1,++$rowCount);
	}

	// $body1 =  gen_x1_body($Ymd,$ymd,$Hi,$runingNumber,$edi_header1,$edi_header2,$row1,++$rowCount);
	$templet = str_replace("{ge}",$rowCount,$templet);
	$templet = str_replace("{body1}",join('',$bodyAr),$templet);
	$templet = explode("\n",$templet);
	$templet = join("~\n",$templet);
	$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,24);
	// $fileName = 'FC_OUT_214_FMXFORDT_'.$randomString.'_'.date('YmdHis').substr(explode(' ',microtime())[0],5,3).'.edi';
	$fileName = date('YmdHis').substr(explode(' ',microtime())[0],5,3).'T'.$time.'_EDI_214_FMXFORDT.edi'; 
	$directory = 'C:/aatmr/edi_v2/out214seq/';
	if (!file_exists($directory)) {
	    mkdir($directory, 0777, true);
	}
	$fullFileName = $directory.$fileName;
	$objFopen = fopen($fullFileName, 'w');
	fwrite($objFopen,$templet);
	fclose($objFopen);

	if($time == '00')
	{
		$directory = 'C:/aatmr/edi_v2/backup214/';
		if (!file_exists($directory)) {
		    mkdir($directory, 0777, true);
		}
		$fullFileName = $directory.$fileName;
		$objFopen = fopen($fullFileName, 'w');
		fwrite($objFopen,$templet);
		fclose($objFopen);
	}
}

function gen_x1_body($Ymd,$ymd,$Hi,$runingNumber,$edi_header1,$edi_header2,$row1,$st_run)
{
    $st_run =substr('0000',0,4-strlen($st_run)).$st_run;
    $body = "ST*214*{edi_header2}{st_run}\n";
    $body .= "B10*{bolPSKL}*{bol}*TTVT\n";
    $body .= "L11*{bolPSKL}*IL*9999\n";
    $body .= "N1*BT*{supplierName}*{country}*{supplierCode}\n";
    $body .= "N3*{supplierAddress}\n";
    $body .= "N4*{supplierGeographic}*{shortJD}*{supplierZip}*{identifi}\n";
    $body .= "LX*{lx}\n";
    $body .= "AT7*AF*NS***{Ymd_1}*{agTime}*LT\n";

    if($row1['type'] == 'UNLOAD' || $row1['type'] == 'CU' || $row1['type'] == 'PU')
        $body .= "AT7***AB*NS*{Ymd_2}*{abTime}*LT\n";
    else
        $body .= "AT7***AA*NS*{Ymd_2}*{aaTime}*LT\n";    

    $body .= "AT7*X1*NS***{Ymd_1}*{Hi_1}*LT\n";
    $body .= "AT7*D1*NS***{Ymd_2}*{Hi_2}*LT\n";
    $body .= "MS1*{supplierGeographic}*{country}*TH\n";
    $body .= "MS2*TTVT*{truckLicense}**1\n";
    $body .= "L11*{s5_seq}*QN\n";

    $body .= "SE*{numLine}*{edi_header2}{st_run}\n";

    // $body = str_replace("{bolPSKL}",substr($row1['b2'],3,12),$body);
    $body = str_replace("{bolPSKL}",$row1['b2'],$body);
    $body = str_replace("{bol}",$row1['b2'],$body);
    
    $body = str_replace("{lx}",$st_run*1,$body);
    $body = str_replace("{agTime}",$row1['agTime'],$body);
    $body = str_replace("{aaTime}",$row1['aaTime'],$body);
    $body = str_replace("{abTime}",$row1['abTime'],$body);
    $body = str_replace("{identifi}",$row1['identifi'],$body);
    $body = str_replace("{Ymd_1}",date('Ymd',strtotime($row1['acDocDate'])),$body);
    $body = str_replace("{Hi_1}",date('His',strtotime($row1['acDocTime'])),$body);
    $body = str_replace("{Ymd_2}",date('Ymd',strtotime($row1['acOutDocDate'])),$body);
    $body = str_replace("{Hi_2}",date('His',strtotime($row1['acOutDocTime'])),$body);

    $body = str_replace("{supplierGeographic}",$row1['supplierGeographic'],$body);
    $body = str_replace("{truckLicense}",$row1['trucklicense'],$body);
    $body = str_replace("{s5_seq}",$row1['s5_seq'],$body);
    $body = str_replace("{shortJD}",$row1['shortJD'],$body);
    $body = str_replace("{supplierName}",$row1['supplierName'],$body);
    $body = str_replace("{supplierCode}",$row1['supplierCode'],$body);
    $body = str_replace("{supplierZip}",$row1['supplierZip'],$body);
    $body = str_replace("{country}",$row1['country'],$body);
    $body = str_replace("{supplierAddress}",$row1['supplierAddress'],$body);
    // $body = str_replace("{routeNo}",$row1['route'],$body);

    $body = str_replace("{edi_header2}",$edi_header2,$body);
    $body = str_replace("{st_run}",$st_run,$body);
    $se = count(explode("\n",$body))-1;
    $body = str_replace("{numLine}",$se,$body);
    return $body;
}

$mysqli->close();
?>