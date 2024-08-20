<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'TripQuotation'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'TripQuotation'}[0] == 0) {
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
	if ($_SESSION['xxxRole']->{'TripQuotation'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {
	} else if ($type == 12) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'TripQuotation'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {
		$dataParams = array(
			'obj',
			'obj=>unitRate:i:0:3',
			'obj=>ID:s:0:1',
		);

		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				ID
			FROM 
				tbl_trip_quotation 
			WHERE 
				ID = '$ID'";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล'  . __LINE__);
			}

			$sql = "UPDATE tbl_trip_quotation 
			SET 
			unitRate = $unitRate,
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
	if ($_SESSION['xxxRole']->{'TripQuotation'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'TripQuotation'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {
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

					$start_date = $data[0][3];
					unset($data[0]);
					$project = $data[1][3];
					unset($data[1]);
					$billing_for = $data[2][3];
					unset($data[2]);
					$special = $data[3][3];
					unset($data[3]);

					$project = str_replace('-', ' ', $project);

					if ($billing_for == 'Partner') {
						$project = '';
					}

					$arr_worktype = array();
					if ($special == 'Yes') {
						$arr_worktype = array('Special');
					} else {
						$arr_worktype = array('Normal', 'Blowout', 'Additional', 'Empty Package');
					}

					$date = date_create($start_date);
					$start_date = date_format($date, "Y-m-d");
					// exit($start_date);

					foreach ($data as $row) {
						//var_dump($row);
						if ($count > 0) {

							if ($row[0] != NULL) {

								$tripType = $row[0];
								if (strpos(strtolower($tripType), 'way') !== false) {
									$tripType = '1WAY';
								} elseif (strpos(strtolower($tripType), 'round') !== false) {
									$tripType = 'ROUND';
								}

								$truckType = $row[1];
								$distanceBand = $row[2];
								$unitRate = str_replace(array(','), '', $row[3]);

								$i = 0;
								while ($i < count($arr_worktype)) {
									$Work_Type = $arr_worktype[$i];

									if ($Work_Type == 'Empty Package' && $tripType == 'ROUND') {
									} else {
										$sql = "INSERT IGNORE INTO tbl_trip_quotation (
											start_date, Work_Type, tripType, truckType, 
											distanceBand, unitRate, billing_for, project,
											createDatetime, createBy )
										VALUES(
											'$start_date', '$Work_Type', '$tripType', '$truckType', 
											'$distanceBand', '$unitRate', '$billing_for', '$project',
											NOW(), $cBy
										)
										ON DUPLICATE KEY UPDATE 
										start_date = '$start_date',
										Work_Type = '$Work_Type',
										tripType = '$tripType',
										truckType = '$truckType',
										distanceBand = '$distanceBand',
										unitRate = '$unitRate',
										billing_for = '$billing_for',
										project = '$project',
										updateDatetime = NOW(),
										updateBy = $cBy;";
										sqlError($mysqli, __LINE__, $sql, 1);
										if ($mysqli->affected_rows == 0) {
											//echo ($start_date . $Work_Type . $tripType . $truckType . $distanceBand . $unitRate . $billing_for . $project);
											throw new Exception('ไม่สามารถเพิ่มข้อมูลได้');
										}
									}
									$total += $mysqli->affected_rows;
									$i++;
								}
								$mysqli->commit();
							} else {
								echo '{"status":"server","mms":"ไม่พบข้อมูลในไฟล์","data":[]}';
								closeDB($mysqli);
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
					exit();
				}
				closeDBT($mysqli, 1, jsonRow($re1, true, 0));
			} catch (Exception $e) {
				$mysqli->rollback();
				echo '{"status":"server","mms":"' . $e->getMessage() . '","sname":[]}';
				closeDB($mysqli);
			}
		} else echo json_encode(array('status' => 'server', 'mms' => 'ข้อมูลในไฟล์ไม่ถูกต้อง', 'sname' => array()));
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 50 && $type <= 60) //save
{
	if ($_SESSION['xxxRole']->{'TripQuotation'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 51) {
		$dataParams = array(
			'obj',
			'obj=>start_date:s:0:1',
			'obj=>billing_for:s:0:1',
			'obj=>project_name:s:0:0',
		);

		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT tripType,truckType,distanceBand,unitRate FROM aatmr_v2_test.tbl_trip_quotation 
			WHERE start_date = ADDDATE(LAST_DAY(SUBDATE(DATE_SUB('$start_date', INTERVAL 1 MONTH), INTERVAL 1 MONTH)), 1)
			AND billing_for = '$billing_for' AND project = '$project_name' AND Work_Type = 'Normal';";

			//exit($sql);
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows === 0) {
				throw new Exception('ไม่พบข้อมูล ' . __LINE__);
			}
			$dataArray = array();
			while ($row = $re1->fetch_assoc()) {
				$dataArray[] = $row;
			}

			include('excel/excel_template_tripquotation.php');

			$mysqli->commit();
			closeDBT($mysqli, 1, $filename);
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else closeDBT($mysqli, 2, 'TYPE ERROR');

function select($mysqli)
{
	$sql = "SELECT 
	ID, Work_Type, tripType, truckType, distanceBand, unitRate, billing_for, start_date,
	project, bailment, t1.createDatetime, t1.createBy, t1.updateDatetime, t1.updateBy
	,concat(t3.user_fName,' ',t3.user_lname) createBy
	,concat(t4.user_fName,' ',t4.user_lname)  updateBy
	from tbl_trip_quotation t1
	left join tbl_user t3 on t1.createBy=t3.user_id
	left join tbl_user t4 on t1.updateBy=t4.user_id
	order by t1.start_date desc";
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
