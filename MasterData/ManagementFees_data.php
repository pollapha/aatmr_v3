<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'ManagementFees'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'ManagementFees'}[0] == 0) {
	echo "{ch:9,data:'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้'}";
	exit();
}

if (!isset($_REQUEST['type'])) {
	echo json_encode(array('ch' => 2, 'data' => 'ข้อมูลไม่ถูกต้อง'));
	exit();
}
$cBy = $_SESSION['xxxID'];
$fName = $_SESSION['xxxFName'];
$type  = intval($_REQUEST['type']);

include('../php/connection.php');
include('../vendor/autoload.php');
if ($type <= 10) //data
{
	if ($type == 1) {
		$re = select($mysqli);
		// var_dump(jsonRow($re,true,0));
		closeDBT($mysqli, 1, jsonRow($re, true, 0));
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) //insert
{
	if ($_SESSION['xxxRole']->{'ManagementFees'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {
	} else if ($type == 12) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'ManagementFees'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {
		$dataParams = array(
			'obj',
			'obj=>ID:s:0:1',
			'obj=>start_date:s:0:0',
			'obj=>stop_date:s:0:0',
			'obj=>service_rate:s:0:0',
		);

		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				ID
			FROM 
			tbl_management_fees 
			WHERE 
				ID = '$ID'";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล'  . __LINE__);
			}

			$service_rate = str_replace(array(','), '', $service_rate);

			// echo $service_rate;
			// exit();

			$sql = "UPDATE tbl_management_fees 
			SET 
				start_date = if('$start_date' = '',null,'$start_date'),
				stop_date = if('$stop_date' = '',null,'$stop_date'),
				service_rate = $service_rate,
				updateDatetime = NOW(),
				updateBy = $cBy
			WHERE
			ID = '$ID';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถแก้ไขข้อมูลได้');
			}
			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'ManagementFees'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'ManagementFees'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 50 && $type <= 60) //save
{
	if ($_SESSION['xxxRole']->{'ManagementFees'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 51) {
		if (!isset($_FILES["upload"])) {
			echo json_encode(array('status' => 'server', 'mms' => 'ไม่พบไฟล์ UPLOAD'));
			closeDB($mysqli);
		}
		$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);
		$fileName = $randomString . '_' . $_FILES["upload"]["name"];
		$tempName = $_FILES["upload"]["tmp_name"];
		if (move_uploaded_file($tempName, "../uploadFile/" . $fileName)) {
			$file_info = pathinfo("../uploadFile/" . $fileName);
			$myfile = fopen("../uploadFile/" . $file_info['basename'], "r") or die("Unable to open file!");
			$data_file = fread($myfile, filesize("../uploadFile/" . $file_info['basename']));
			$file_ext = pathinfo($fileName, PATHINFO_EXTENSION);
			$allowed_ext = ['xls', 'csv', 'xlsx'];
			fclose($myfile);

			$mysqli->autocommit(FALSE);
			try {
				if (in_array($file_ext, $allowed_ext)) {
					$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('../uploadFile/' . $fileName);
					$data = $spreadsheet->getActiveSheet()->toArray();
					$count = 0;
					$total = 0;

					$projectName = $data[0][1];
					unset($data[0]);

					$projectName = str_replace('-', ' ', $projectName);

					// var_dump($data);
					// exit();

					foreach ($data as $row) {
						if ($count > 0) {
							if ($row[0] != NULL) {

								$start_date = $row[0];
								$description = $row[1];
								$service_rate = $row[3];

								$service_rate = str_replace(array(','), '', $service_rate);
								// echo ($start_date);

								$sql = "SELECT ID as projectID FROM tbl_project_master WHERE projectName = '$projectName';";
								$re1 = sqlError($mysqli, __LINE__, $sql, 1);
								if ($re1->num_rows == 0) {
									throw new Exception($projectName . ' : Project ไม่ถูกต้อง');
								}
								while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
									$projectID = $row['projectID'];
								}


								$sql = "INSERT IGNORE INTO tbl_management_fees
								( start_date, description, service_rate, projectID,
									createDatetime, createBy )
								VALUES( if('$start_date' = '',null,'$start_date'), 
								'$description', '$service_rate', $projectID,
									NOW(), $cBy )
								ON DUPLICATE KEY UPDATE 
								start_date = if('$start_date' = '',null,'$start_date'),
								description = '$description',
								service_rate = '$service_rate',
								projectID = $projectID,
								updateDatetime = NOW(),
								updateBy = $cBy;";
								//exit($sql);
								sqlError($mysqli, __LINE__, $sql, 1);
								if ($mysqli->affected_rows == 0) {
									throw new Exception('ไม่สามารถแก้ไขข้อมูลได้');
								}
								$total += $mysqli->affected_rows;
							}
						} else {
							$count = 1;
						}
					}

					//exit();
					$mysqli->commit();

					if ($total == 0) throw new Exception('ไม่มีรายการอัพเดท' . $mysqli->error);
					echo '{"status":"server","mms":"Update สำเร็จ ' . $total . '","data":[]}';
					closeDB($mysqli);
				}
				closeDBT($mysqli, 1, jsonRow($re1, true, 0));
			} catch (Exception $e) {
				$mysqli->rollback();
				echo '{"status":"server","mms":"' . $e->getMessage() . '","sname":[]}';
				closeDB($mysqli);
			}
		} else echo json_encode(array('status' => 'server', 'mms' => 'ข้อมูลในไฟล์ไม่ถูกต้อง', 'sname' => array()));
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else closeDBT($mysqli, 2, 'TYPE ERROR');

function select($mysqli)
{
	//ID, description, unit, service_rate, start_date, stop_date, createDatetime, createBy, updateDatetime, updateBy
	$sql = "SELECT 
	t1.ID, description, unit, FORMAT(service_rate,0) as service_rate, start_date, stop_date, projectName,
	t1.createDatetime, t1.updateDatetime,
	concat(t3.user_fName,' ',t3.user_lname) createBy,
	concat(t4.user_fName,' ',t4.user_lname)  updateBy
	from tbl_management_fees t1
	left join tbl_project_master t2 ON t1.projectID = t2.ID
	left join tbl_user t3 on t1.createBy=t3.user_id
	left join tbl_user t4 on t1.updateBy=t4.user_id
	order by t1.start_date desc, ID ASC";
	//exit($sql);
	return sqlError($mysqli, __LINE__, $sql, 1);
}

function update($mysqli, $tableName, $ID, $data, $cBy, $error)
{
	$sql = "UPDATE $tableName SET " . prepareUpdate($data) . " where ID = '$ID' limit 1";

	sqlError($mysqli, __LINE__, $sql, 1);
	if ($mysqli->affected_rows == 0) {
		throw new Exception('ไม่พบการเปลียนแปลงข้อมูล');
	}

	$dataAr = array(array(
		'updateDatetime' => 'sql=now()',
		'updateBy' => $cBy,
	));
	$sql = "UPDATE $tableName SET " . prepareUpdate($dataAr) . " where ID = '$ID' limit 1";
	sqlError($mysqli, __LINE__, $sql, 1, 1);
	if ($mysqli->affected_rows == 0) {
		throw new Exception($error);
	}
}

function prepareNameInsert($data)
{
	$dataReturn = array();
	foreach ($data as $key => $value) {
		$dataReturn[] = $key;
	}
	return '(' . join(',', $dataReturn) . ')';
}

function prepareValueInsert($data)
{
	$dataReturn = array();
	foreach ($data as $valueAr) {
		$typeV;
		$keyV;
		$valueV;
		$dataAr = array();
		foreach ($valueAr as $key => $value) {
			$keyV = $key;
			$valueV = $value;
			$dataAr[] = $valueV;
		}
		$dataReturn[] = '(' . join(',', $dataAr) . ')';
	}
	return join(',', $dataReturn);
}

function stringConvert($data)
{
	if (strlen($data) > 0) {
		return "'$data'";
	} else {
		return "''";
	}
}
$mysqli->close();
exit();
