<?php
ini_set('memory_limit', '5048M');
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'transaction'}) )
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}
else if($_SESSION['xxxRole']->{'transaction'}[0] == 0)
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
// require_once('../Classes/PHPExcel.php');
require_once('../php/xlsxwriter.class.php');
if($type<=10)//data
{
	if($type == 1)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>date1','obj=>date2','obj=>b2'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		$date1 = checkTXT($mysqli,$_POST['obj']['date1']);
		$date2 = checkTXT($mysqli,$_POST['obj']['date2']);
		$b2 = checkTXT($mysqli,$_POST['obj']['b2']);
		if(strlen($date1) == 0 || strlen($date2) == 0) closeDBT($mysqli,2,'วันไม่ถูกต้อง');
		$date1 = date('Y-m-d',strtotime($date1));
		$date2 = date('Y-m-d',strtotime($date2));

		$where = 'where ';
		if(strlen($b2)>0)
		{
			$where .= "t1.Load_ID='$b2'";
		}
		else
		{
			$where .= "t1.operration_date between '$date1' and '$date2'";
		}

		$sql ="SELECT t1.docCount,t1.pus,t1.tripTTV,t1.truckLicense,t1.truckType,t1.driverName,t1.phone,t1.remark,t1.Work_Type,
		t1.planTimeOut_Origin,t1.planTimeIn_Origin,t1.acTimeOut_Origin,t1.acTimeIn_Origin,t1.CurrentLoadOperationalStatusEnumVal JDA_Status,
		t1.AlertTypeCode,t1.Load_ID,t1.Start_Datetime,t1.End_Datetime,t1.Route,t1.Load_Description,t1.CarrierCode,t1.NumberOfStops,t1.Status,
		t2.PlanIN_Datetime,t2.PlanOut_Datetime,t2.ActualIN_Datetime,t2.ActualOut_Datetime,
		t2.Supplier_Code,t2.Supplier_Name,t2.Supplier_Address,t2.Short_JD,t2.Country,
		t2.Identifi,t2.Supplier_Geographic,t2.Supplier_Zip,t2.CurrentLoadOperationalStatusEnumVal,
		t2.ServiceCode,t2.EquipmentTypeCode,t2.TractorEquipmentTypeCode,t2.StopSequenceNumber,t2.StopStatusEnumVal,t2.StopTypeEnumVal,
		'' type,t3.createBy,concat(t3.createDate,' ',t3.createTime) cDateTime,
        t1.operration_date,
		date_format(t2.ActualOut_Datetime,'%Y-%m-%d') docDate,
		date_format(t1.Start_Datetime,'%Y-%m-%d') dateStart,
		date_format(t2.ActualIN_Datetime,'%Y-%m-%d') acDateIn,
		date_format(t2.ActualOut_Datetime,'%Y-%m-%d') acDateOut,
		date_format(t2.PlanIN_Datetime,'%H:%i') docTime,
		date_format(t2.PlanIN_Datetime,'%H:%i') docTime,
		date_format(t2.ActualIN_Datetime,'%H:%i') acDocTime,
		date_format(t2.PlanOut_Datetime,'%H:%i') lateTime,
		date_format(t2.ActualOut_Datetime,'%H:%i') acOutDocTime	,

		t2.Update_ActualIN_Datetime upDateINTime,
		concat(t20.user_fName,' ',t20.user_lname)upDateINBy, 
		t2.Update_ActualOut_Datetime upDateOUTTime,
		concat(t21.user_fName,' ',t21.user_lname)upDateOUTBy,
		t2.SendJDA_ActualIN_Datetime SendJDA_ActualIN,
		t2.SendJDA_ActualOut_Datetime SendJDA_ActualOut,
		t2.GPS_Status
		from tbl_transaction t3
        left join tbl_204header_api t1 on t3.Load_ID=t1.Load_ID
		left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID
		left join tbl_user t20 on t2.Update_ActualIN_Datetime_By=t20.user_id
		left join tbl_user t21 on t2.Update_ActualOut_Datetime_By=t21.user_id
		$where
        order by t2.Load_ID,t2.StopSequenceNumber;";
		// exit($sql);
		$re1 = sqlError($mysqli,__LINE__,$sql);
		if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูลในระบบ');
		closeDBT($mysqli,1,jsonRow($re1,true,0));
	}
	else if($type == 2)
	{
		
		$chkPOST = checkParams($_POST,array('obj','obj=>date1','obj=>date2','obj=>b2'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));

		$date1 = checkTXT($mysqli,$_POST['obj']['date1']);
		$date2 = checkTXT($mysqli,$_POST['obj']['date2']);
		$b2 = checkTXT($mysqli,$_POST['obj']['b2']);
		if(strlen($date1) == 0 || strlen($date2) == 0) closeDBT($mysqli,2,'วันไม่ถูกต้อง');
		$date1 = date('Y-m-d',strtotime($date1));
		$date2 = date('Y-m-d',strtotime($date2));

		$where = 'where ';
		if(strlen($b2)>0)
		{
			$where .= "t1.Load_ID='$b2'";
		}
		else
		{
			$where .= "t1.operration_date between '$date1' and '$date2'";
		}

		$sql ="SELECT t1.docCount,t1.pus,t1.tripTTV,t1.truckLicense,t1.truckType,t1.driverName,t1.phone,t1.remark,t1.Work_Type,
		t1.planTimeOut_Origin,t1.planTimeIn_Origin,t1.acTimeOut_Origin,t1.acTimeIn_Origin,t1.CurrentLoadOperationalStatusEnumVal JDA_Status,
		t1.AlertTypeCode,t1.Load_ID,t1.Start_Datetime,t1.End_Datetime,t1.Route,t1.Load_Description,t1.CarrierCode,t1.NumberOfStops,t1.Status,
		t2.PlanIN_Datetime,t2.PlanOut_Datetime,t2.ActualIN_Datetime,t2.ActualOut_Datetime,
		t2.Supplier_Code,t2.Supplier_Name,t2.Supplier_Address,t2.Short_JD,t2.Country,
		t2.Identifi,t2.Supplier_Geographic,t2.Supplier_Zip,t2.CurrentLoadOperationalStatusEnumVal,
		t2.ServiceCode,t2.EquipmentTypeCode,t2.TractorEquipmentTypeCode,t2.StopSequenceNumber,t2.StopStatusEnumVal,t2.StopTypeEnumVal,
		'' type,t3.createBy,concat(t3.createDate,' ',t3.createTime) cDateTime,
        t1.operration_date,
		date_format(t1.Start_Datetime,'%Y-%m-%d') dateStart,
		date_format(t2.PlanIN_Datetime,'%Y-%m-%d') docDate,
		date_format(t2.PlanIN_Datetime,'%H:%i') docTime,
		date_format(t2.ActualIN_Datetime,'%H:%i') acDocTime,
		t2.SendJDA_ActualIN_Datetime SendJDA_ActualIN,
		date_format(t2.PlanOut_Datetime,'%H:%i') lateTime,
		date_format(t2.ActualOut_Datetime,'%H:%i') acOutDocTime	,
		t2.SendJDA_ActualOut_Datetime SendJDA_ActualOut,
		t2.Update_ActualIN_Datetime upDateINTime,
		concat(t20.user_fName,' ',t20.user_lname)upDateINBy, 
		t2.Update_ActualOut_Datetime upDateOUTTime,
		concat(t21.user_fName,' ',t21.user_lname)upDateOUTBy,
		t2.GPS_Status
		from tbl_transaction t3
        left join tbl_204header_api t1 on t3.Load_ID=t1.Load_ID
		left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID
		left join tbl_user t20 on t2.Update_ActualIN_Datetime_By=t20.user_id
		left join tbl_user t21 on t2.Update_ActualOut_Datetime_By=t21.user_id
		$where
        order by t2.Load_ID,t2.StopSequenceNumber";

		$re1 = sqlError($mysqli,__LINE__,$sql);
		if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูลในระบบ');
		$data = jsonRow($re1,true,0);
		$dataExport = array();

		$sheet1header = array		
		(		
			'NO'=>'integer',
			'Load ID'=>'integer',
			'Work Type'=>'string',
			'Route'=>'string',
			'Stop SequenceNumber'=>'integer',
			'Supplier Code'=>'string',
			'Supplier Name'=>'string',
			'Status'=>'string',
			'JDA Status'=>'string',
			'Stop Status EnumVal'=>'string',
			'Operration Date'=>'YYYY-MM-DD',
			'Due Date'=>'YYYY-MM-DD',
			'Plan Time IN'=>'HH:MM',
			'Actual Time IN'=>'HH:MM',
			'Plan Time OUT'=>'HH:MM',
			'Actual Time OUT'=>'HH:MM',
			'Update IN Date'=>'YYYY-MM-DD HH:MM:SS',
			'SendJDA IN Date'=>'YYYY-MM-DD HH:MM:SS',
			'Update OUT Date'=>'YYYY-MM-DD HH:MM:SS',
			'SendJDA OUT Date'=>'YYYY-MM-DD HH:MM:SS',
			'Trip TTV'=>'string',
			'Truck License'=>'string',
			'Truck Type'=>'string',
			'Driver Name'=>'string',
			'Phone'=>'string',
			'Type'=>'string',
			'Remark'=>'string',
			'Create Document Date'=>'YYYY-MM-DD HH:MM:SS',
			'Create Document By'=>'string',
			'Update Time IN'=>'YYYY-MM-DD HH:MM:SS',
			'Update Time IN By'=>'string',
			'Update Time OUT'=>'YYYY-MM-DD HH:MM:SS',
			'Update Time OUT By'=>'string'
		);		

		$writer = new XLSXWriter();
		$writer->setAuthor('Some Author');
		$writer->writeSheetHeader('sheet1',$sheet1header,['widths'=>[10,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20,20],'freeze_rows'=>1, 'freeze_columns'=>1,'border'=>'left,right,top,bottom','halign'=>'center','font-style'=>'bold','color'=>'#fff','fill'=>'#3498db','auto_filter'=>true]);

		for($i=0,$len=count($data);$i<$len;$i++)
		{
			$writer->writeSheetRow('sheet1', array(
				$data[$i]['NO'],
				$data[$i]['Load_ID'],
				$data[$i]['Work_Type'],
				$data[$i]['Route'],
				$data[$i]['StopSequenceNumber'],
				$data[$i]['Supplier_Code'],
				$data[$i]['Supplier_Name'],
				$data[$i]['Status'],
				$data[$i]['JDA_Status'],
				$data[$i]['StopStatusEnumVal'],
				$data[$i]['operration_date'],
				$data[$i]['PlanIN_Datetime'],
				$data[$i]['PlanIN_Datetime'],
				$data[$i]['ActualIN_Datetime'],
				$data[$i]['PlanOut_Datetime'],
				$data[$i]['ActualOut_Datetime'],
				$data[$i]['upDateINTime'],
				$data[$i]['SendJDA_ActualIN'],
				$data[$i]['upDateOUTTime'],
				$data[$i]['SendJDA_ActualOut'],
				$data[$i]['tripTTV'],
				$data[$i]['truckLicense'],
				$data[$i]['truckType'],
				$data[$i]['driverName'],
				$data[$i]['phone'],
				$data[$i]['type'],
				$data[$i]['remark'],
				$data[$i]['cDateTime'],
				$data[$i]['createBy'],
				$data[$i]['upDateINTime'],
				$data[$i]['upDateINBy'],
				$data[$i]['upDateOUTTime'],
				$data[$i]['upDateOUTBy'],
			));
		}

		
		
		

		$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,5);
		$filename = $randomString.strtotime(date('Y-m-d H:i:s')).'.xlsx'; 
		header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
		header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header('Content-Transfer-Encoding: binary');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		$writer->writeToStdOut();

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>10 && $type<=20)//insert
{
	if($_SESSION['xxxRole']->{'transaction'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
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
	if($_SESSION['xxxRole']->{'transaction'}[2] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
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

		// if(preg_replace('/[^ก-ฮ]/u','',$truckType))
		// {
		// 	closeDBT($mysqli,2,'ทะเบียนรถมีภาษาไทย');
		// }
		
		if(strlen($Load_ID) == 0) closeDBT($mysqli,2,'Load_ID ไม่ถูกต้อง');


		$planTimeOut = strlen($planTimeOut) == 0 ? '00:00':"$planTimeOut";
		$planTimeIn = strlen($planTimeIn) == 0 ? '00:00':"$planTimeIn";

		$sql = "SELECT t1.ID,t1.Load_ID
		from tbl_204header_api t1
		left join tbl_204body_api t2 on t1.Load_ID=t2.Load_ID
		where t1.Load_ID='$Load_ID' limit 1;";
		$re1 = sqlError($mysqli,__LINE__,$sql);
		if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูลในระบบ');

		$mysqli->autocommit(FALSE);
		try
		{

			$sql = "UPDATE tbl_204header_api set truckLicense='$truckLicense',truckType='$truckType',driverName='$driverName',phone='$phone',
			planTimeOut_Origin=if('$planTimeOut'='00:00',null,date_format(Start_Datetime,\"%Y-%m-%d '$planTimeOut'\")),
			planTimeIn_Origin=if('$planTimeIn'='00:00',null,date_format(End_Datetime,\"%Y-%m-%d '$planTimeIn'\")),
			remark='$remark'
			where Load_ID='$Load_ID' limit 1;";
						
			sqlError($mysqli,__LINE__,$sql,1);
			if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่พบการเปลียนแปลง');

			$sql = "UPDATE tbl_204header_api set Update_Datetime=now(),update_By=$cBy where Load_ID='$Load_ID' limit 1;";

			sqlError($mysqli,__LINE__,$sql,1);
			if($mysqli->affected_rows == 0) throw new Exception('ERROR LINE '.__LINE__.'<br>ไม่พบการเปลียนแปลง');

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
	if($_SESSION['xxxRole']->{'transaction'}[3] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 31)
	{

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>40 && $type<=50)//save
{
	if($_SESSION['xxxRole']->{'transaction'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 41)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else closeDBT($mysqli,2,'TYPE ERROR');

$mysqli->close();
exit();
?>
