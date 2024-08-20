<?php
ini_set('memory_limit', '5048M');
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'view_204'}) )
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}
else if($_SESSION['xxxRole']->{'view_204'}[0] == 0)
{
	echo "{ch:9,data:'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้'}";
	exit();
}

if(!isset($_REQUEST['type'])) {echo json_encode(array('ch'=>2,'data'=>'ข้อมูลไม่ถูกต้อง'));exit();}
$cBy = $_SESSION['xxxID'];
$fName = $_SESSION['xxxFName'];
$type  = intval($_REQUEST['type']);


include('../php/connection.php');
include('../common/commonFunc.php');
require_once('../Classes/PHPExcel.php');

if($type<=10)//data
{
	if($type == 1)
	{

		$chkPOST = checkParams($_POST,array('obj','obj=>date1','obj=>date2','obj=>project','obj=>loadID'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		$date1 = checkTXT($mysqli,$_POST['obj']['date1']);
		$date2 = checkTXT($mysqli,$_POST['obj']['date2']);
		$project = checkTXT($mysqli,$_POST['obj']['project'],0);
		$loadID = checkTXT($mysqli,$_POST['obj']['loadID'],0);
		if(strlen($date1) == 0 || strlen($date2) == 0) closeDBT($mysqli,2,'วันไม่ถูกต้อง');
		$date1 = date('Y-m-d',strtotime($date1));
		$date2 = date('Y-m-d',strtotime($date2));

		$where = '';
		if($project != 'ALL')
		{
			$reProject = checkProject($mysqli,__LINE__,$project);
			if($reProject->num_rows==0)
			{
				closeDBT($mysqli,2,'ไม่พบข้อมูล Project '.$project);
			}
		}

		if(strlen($loadID)>0)
		{
			$loadID = explode(',',$loadID);
			$loadID = "'".join("','",$loadID)."'";
			$where = "t1.Load_ID in($loadID)";
		}
		else
		{
			$where = "date_format(t1.Start_Datetime,'%Y-%m-%d') between '$date1' and '$date2'";
		}
		
        $sql = 
        "SELECT t4.projectName,t1.docCount,t1.pus,t1.tripTTV,t1.truckLicense,t1.truckType,t1.driverName,t1.phone,
		t1.planTimeOut_Origin,t1.planTimeIn_Origin,t1.acTimeOut_Origin,t1.acTimeIn_Origin,
		t1.AlertTypeCode,t1.Load_ID,t1.Start_Datetime,t1.End_Datetime,t1.Route,t1.Load_Description,t1.CarrierCode,t1.NumberOfStops,t1.Status,
		t2.PlanIN_Datetime,t2.PlanOut_Datetime,t2.ActualIN_Datetime,t2.ActualOut_Datetime,
		t2.Supplier_Code,t2.Supplier_Name,t2.Supplier_Address,t2.Short_JD,t2.Country,
		t2.Identifi,t2.Supplier_Geographic,t2.Supplier_Zip,t2.CurrentLoadOperationalStatusEnumVal,
		t2.ServiceCode,t2.EquipmentTypeCode,t2.TractorEquipmentTypeCode,t2.StopSequenceNumber,t2.StopStatusEnumVal,t2.StopTypeEnumVal,
		t1.truckLicense,t1.truckType,t1.driverName,t1.phone
		from tbl_204header_api t1
		left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID
		left join tbl_route_master_header t3 on t1.Route=t3.routeName
		left join tbl_project_master t4 on t3.projectID=t4.ID
		where $where
		order by t1.Start_Datetime,t1.Load_ID,t2.StopSequenceNumber;";
				
		$re1 = sqlError($mysqli,__LINE__,$sql);
		if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูลในระบบ');
		closeDBT($mysqli,1,jsonRow($re1,true,0));

	}
	else if($type == 2)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>Load_ID'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
		$Load_ID = checkTXT($mysqli,$_POST['obj']['Load_ID']);
		$sql = 
        "SELECT truckLicense,driverName,phone from tbl_862order where LOAD_ID='$Load_ID' limit 1;";
		$re1 = sqlError($mysqli,__LINE__,$sql);
		if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูลในระบบ');
		closeDBT($mysqli,1,jsonRow($re1,true,0));
	}
	else if($type == 3)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>date1','obj=>date2','obj=>project'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		$date1 = checkTXT($mysqli,$_POST['obj']['date1']);
		$date2 = checkTXT($mysqli,$_POST['obj']['date2']);
		$project = checkTXT($mysqli,$_POST['obj']['project'],0);
		if(strlen($date1) == 0 || strlen($date2) == 0) closeDBT($mysqli,2,'วันไม่ถูกต้อง');
		$date1 = date('Y-m-d',strtotime($date1));
		$date2 = date('Y-m-d',strtotime($date2));

		if($project != 'ALL')
		{
			$reProject = checkProject($mysqli,__LINE__,$project);
			if($reProject->num_rows==0)
			{
				closeDBT($mysqli,2,'ไม่พบข้อมูล Project '.$project);
			}
		}
		
        $sql = 
        "SELECT t4.projectName,t1.docCount,t1.pus,t1.tripTTV,t1.truckLicense,t1.truckType,t1.driverName,t1.phone,
		t1.planTimeOut_Origin,t1.planTimeIn_Origin,t1.acTimeOut_Origin,t1.acTimeIn_Origin,
		t1.AlertTypeCode,t1.Load_ID,t1.Start_Datetime,t1.End_Datetime,t1.Route,t1.Load_Description,t1.CarrierCode,t1.NumberOfStops,t1.Status,
		t2.PlanIN_Datetime,t2.PlanOut_Datetime,t2.ActualIN_Datetime,t2.ActualOut_Datetime,
		t2.Supplier_Code,t2.Supplier_Name,t2.Supplier_Address,t2.Short_JD,t2.Country,
		t2.Identifi,t2.Supplier_Geographic,t2.Supplier_Zip,t2.CurrentLoadOperationalStatusEnumVal,
		t2.ServiceCode,t2.EquipmentTypeCode,t2.TractorEquipmentTypeCode,t2.StopSequenceNumber,t2.StopStatusEnumVal,t2.StopTypeEnumVal,
		t1.truckLicense,t1.truckType,t1.driverName,t1.phone
		from tbl_204header_api t1
		left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID
		left join tbl_route_master_header t3 on t1.Route=t3.routeName
		left join tbl_project_master t4 on t3.projectID=t4.ID
		where date_format(t1.Start_Datetime,'%Y-%m-%d') between '$date1' and '$date2'
		order by t1.Start_Datetime,t1.Load_ID,t2.StopSequenceNumber;";
				
		$re1 = sqlError($mysqli,__LINE__,$sql);
		$data = jsonRow($re1,true,0);

		$d = array();
		$d[] = array('NO','projectName','Load_ID','AlertTypeCode','CurrentLoadOperationalStatusEnumVal','Route','StopSequenceNumber','Supplier_Code','Supplier_Name','PlanIN_Datetime','PlanOut_Datetime','StopStatusEnumVal','StopTypeEnumVal','docCount');
		for($i=0,$len=count($data);$i<$len;$i++)
		{
			$ar = array();
			$ar[] = $data[$i]['NO'];
			$ar[] = $data[$i]['projectName'];
			$ar[] = $data[$i]['Load_ID'];
			$ar[] = $data[$i]['AlertTypeCode'];
			$ar[] = $data[$i]['CurrentLoadOperationalStatusEnumVal'];
			$ar[] = $data[$i]['Route'];
			$ar[] = $data[$i]['StopSequenceNumber'];
			$ar[] = $data[$i]['Supplier_Code'];
			$ar[] = $data[$i]['Supplier_Name'];
			$ar[] = $data[$i]['PlanIN_Datetime'];
			$ar[] = $data[$i]['PlanOut_Datetime'];
			$ar[] = $data[$i]['StopStatusEnumVal'];
			$ar[] = $data[$i]['StopTypeEnumVal'];
			$ar[] = $data[$i]['docCount'];
			$d[] = $ar;
		}

		
		
		

		$excel = new PHPExcel();
		PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
		$excel->setActiveSheetIndex(0);		
		$objWorksheet = $excel->getActiveSheet();		
		$objWorksheet->fromArray(
			$d
		);
		
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="ดาวน์โหลด.xlsx"');
		header('Cache-Control: max-age=0');
		header('Cache-Control: max-age=1');
		
		$objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$objWriter->save('php://output');

		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>10 && $type<=20)//insert
{
	if($_SESSION['xxxRole']->{'view_204'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 11)
	{

	}
	else if($type == 12)
	{

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>20 && $type<=30)//update
{
	if($_SESSION['xxxRole']->{'view_204'}[2] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 21)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>Load_ID','obj=>planTimeOut',
		'obj=>planTimeIn','obj=>truckLicense','obj=>truckType','obj=>driverName','obj=>phone','obj=>remark'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		$Load_ID = checkTXT($mysqli,$_POST['obj']['Load_ID']);
		$planTimeOut = checkTXT($mysqli,$_POST['obj']['planTimeOut']);
        $planTimeIn = checkTXT($mysqli,$_POST['obj']['planTimeIn']);
        $truckLicense = checkTXT($mysqli,$_POST['obj']['truckLicense']);
        $truckType = checkTXT($mysqli,$_POST['obj']['truckType']);
        $driverName = checkTXT($mysqli,$_POST['obj']['driverName']);
        $phone = checkTXT($mysqli,$_POST['obj']['phone']);
		$remark = checkTXT($mysqli,$_POST['obj']['remark']);
		
		if(strlen($Load_ID) == 0) closeDBT($mysqli,2,'Load_ID ไม่ถูกต้อง');

		$planTimeOut = strlen($planTimeOut) == 0 ? '00:00':"'$planTimeOut'";
		$planTimeIn = strlen($planTimeIn) == 0 ? '00:00':"'$planTimeIn'";

		
		$sql = "SELECT t1.ID,t1.Load_ID,t1.docCount
		from tbl_204header_api t1
		left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID
		where t1.Load_ID='$Load_ID' limit 1;";
		$re1 = sqlError($mysqli,__LINE__,$sql);
		if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูลในระบบ');
		$dataLoadID = jsonRow($re1,true,0);

		$mysqli->autocommit(FALSE);
		try
		{
			
			if(intval($dataLoadID[0]['docCount']) == 0)
			{
				$sql = "INSERT ignore INTO tbl_transaction(createDate,createTime,createBy,Load_ID)
				values(curdate(),curtime(),'$fName','$Load_ID');";
				sqlError($mysqli,__LINE__,$sql,1);
				// if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่สามารถบันทึกข้อมูลได้');

				$sql = "UPDATE tbl_204header_api set truckLicense='$truckLicense',truckType='$truckType',driverName='$driverName',phone='$phone',
				planTimeOut_Origin=if('$planTimeOut'='00:00',null,date_format(Start_Datetime,\"%Y-%m-%d '$planTimeOut'\")),
				planTimeIn_Origin=if('$planTimeIn'='00:00',null,date_format(End_Datetime,\"%Y-%m-%d '$planTimeIn'\")),
				docCount=docCount+1,Status='WAITING DUETIME',remark='$remark',pus=func_GenRuningNumber('pus',0)
				where Load_ID='$Load_ID' limit 1;";

				sqlError($mysqli,__LINE__,$sql,1);
				if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่สามารถบันทึกข้อมูลได้');

				$sql = "INSERT INTO tbl_truckmonitor(Load_ID,StopSequenceNumber,gpsStatus)
				SELECT t1.Load_ID,t2.StopSequenceNumber,'MANUAL'
				from tbl_204header_api t1
				left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID
				where t1.Load_ID='$Load_ID'";
				
				sqlError($mysqli,__LINE__,$sql,1);
				if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่สามารถบันทึกข้อมูลได้');
			}
			else
			{
				$sql = "UPDATE tbl_204header_api set truckLicense='$truckLicense',truckType='$truckType',driverName='$driverName',phone='$phone',
				planTimeOut_Origin=if('$planTimeOut'='00:00',null,date_format(Start_Datetime,\"%Y-%m-%d '$planTimeOut'\")),
				planTimeIn_Origin=if('$planTimeIn'='00:00',null,date_format(End_Datetime,\"%Y-%m-%d '$planTimeIn'\")),
				remark='$remark'
				where Load_ID='$Load_ID' limit 1;";

				sqlError($mysqli,__LINE__,$sql,1);
			}

            $mysqli->commit();
            closeDBT($mysqli,1,'บันทึกสำเร็จ');
        }
		catch( Exception $e )
		{
			$mysqli->rollback();
			closeDBT($mysqli,2,$e->getMessage());
        }
	}
	else if($type == 22)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>Load_ID','obj=>Work_Type'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		$Load_ID = checkTXT($mysqli,$_POST['obj']['Load_ID']);
		$Work_Type = checkTXT($mysqli,$_POST['obj']['Work_Type']);

		$mysqli->autocommit(FALSE);
		try
		{
			$sql = "UPDATE tbl_204header_api set Work_Type='$Work_Type' where Load_ID='$Load_ID' limit 1;";

			sqlError($mysqli,__LINE__,$sql,1);
			
			$mysqli->commit();
			closeDBT($mysqli,1,'บันทึกสำเร็จ');
		}
		catch( Exception $e )
		{
			$mysqli->rollback();
			closeDBT($mysqli,2,$e->getMessage());
		}
	}
	else if($type == 23)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>Load_ID'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		$Load_ID = checkTXT($mysqli,$_POST['obj']['Load_ID']);

		$mysqli->autocommit(FALSE);
		try
		{
			$Load_ID_Ar = explode(',',$Load_ID);
			$Load_ID = "'".join("','",$Load_ID_Ar)."'";
			$sql = "INSERT ignore into tbl_truckmonitor(Load_ID,StopSequenceNumber)
			select t2.Load_ID,t2.StopSequenceNumber
			from tbl_204header_api t1
			left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID
			where t1.Load_ID in($Load_ID) and docCount=1 and t1.Status not in('COMPLETED');";

			sqlError($mysqli,__LINE__,$sql,1);
			if($mysqli->affected_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูลที่สามารถเพิ่มได้');
			
			$mysqli->commit();
			closeDBT($mysqli,1,'บันทึกสำเร็จ');
		}
		catch( Exception $e )
		{
			$mysqli->rollback();
			closeDBT($mysqli,2,$e->getMessage());
		}
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>30 && $type<=40)//delete
{
	if($_SESSION['xxxRole']->{'view_204'}[3] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 31)
	{

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>40 && $type<=50)//save
{
	if($_SESSION['xxxRole']->{'view_204'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 41)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else closeDBT($mysqli,2,'TYPE ERROR');

$mysqli->close();
exit();
?>
