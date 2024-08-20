<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'CheckHourlyReport'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'CheckHourlyReport'}[0] == 0) {
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
if ($type <= 10) //data
{
	if ($type == 1) {

		$dataParams = array(
			'obj',
			'obj=>start_date:s:0:2',
			'obj=>stop_date:s:0:2',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$sql = "SELECT 
			CASE
				WHEN t3.projectName = 'AAT MR' OR t3.projectName = 'Bailment' OR t3.projectName = 'PxP MR' OR t3.projectName = 'Service' THEN 'AAT-MR'
				WHEN t3.projectName = 'FTM MR' THEN 'FTM-MR'
				WHEN t3.projectName = 'SKD' THEN 'SKD-FTM'
				WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) = 'F' THEN 'EDC-FTM'
				WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) != 'F' THEN 'AAT EDC'
				ELSE t3.projectName 
				END AS projectName,
			t1.truckLicense,
			t1.truckType,
			t1.Work_Type,
			t1.tripType,
			t1.tripType_partner,
			t1.distanceBand,
			t1.bailment,
			t1.truck_carrier,
			t1.driverName,
			t1.phone,
			t1.AlertTypeCode,
			t1.Load_ID,
			t1.operation_date,
			DATE(t1.Start_Datetime) AS Start_Datetime,
			DATE(t1.End_Datetime) AS End_Datetime,
			t1.Route,
			t1.CurrentLoadOperationalStatusEnumVal,
			t1.Status,
			t1.select_for_customer,
			t1.select_for_partner
		FROM
			tbl_hourly_report_pending t1
				LEFT JOIN
			tbl_route_master_header t2 ON t1.Route = t2.routeName
				LEFT JOIN
			tbl_project_master t3 ON t2.projectID = t3.ID
		WHERE
			DATE(t1.operation_date) between DATE('$start_date') AND DATE('$stop_date')
		ORDER BY t1.operation_date ASC, t1.Load_ID ASC;";
		//exit($sql);
		$re1 = sqlError($mysqli, __LINE__, $sql, 1);
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) //insert
{
	if ($_SESSION['xxxRole']->{'CheckHourlyReport'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {

		if (!isset($_POST["obj"])) {
			echo json_encode(array('status' => 'server', 'mms' => 'ไม่พบข้อมูล'));
			closeDB($mysqli);
		}

		$obj  = $_POST['obj'];

		$mysqli->autocommit(FALSE);
		try {

			foreach ($obj as $Load_ID) {
				$sql = "SELECT 
					Load_ID
				FROM
					tbl_204header_api
				WHERE
					Load_ID = '$Load_ID';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows > 0) {

					$sql = "SELECT truckLicense, truckType, Work_Type, tripType, tripType_partner, distanceBand, bailment, truck_carrier, driverName, phone, AlertTypeCode, 
					Load_ID, operation_date, Start_Datetime, End_Datetime, Route, CurrentLoadOperationalStatusEnumVal, Status, fileupload_name
					FROM tbl_hourly_report_pending 
					WHERE Load_ID = '$Load_ID';";
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
						$bailment = $row['bailment'];
						$Status = $row['Status'];
						$truck_carrier = $row['truck_carrier'];
						$Work_Type = $row['Work_Type'];
						$tripType = $row['tripType'];
						$tripType_partner = $row['tripType_partner'];
						$operation_date = $row['operation_date'];
						$Start_Datetime = $row['Start_Datetime'];
						$End_Datetime = $row['End_Datetime'];
						$Route = $row['Route'];
						$distanceBand = $row['distanceBand'];
						$truckType = $row['truckType'];
						$AlertTypeCode = $row['AlertTypeCode'];
						$CurrentLoadOperationalStatusEnumVal = $row['CurrentLoadOperationalStatusEnumVal'];
						$fileupload_name = $row['fileupload_name'];
					}

					$sql = "UPDATE tbl_204header_api
					SET
						bailment = '$bailment',
						Status = '$Status',
						truck_carrier = '$truck_carrier',
						Work_Type = '$Work_Type',
						tripType = '$tripType',
						tripType_partner = '$tripType_partner',
						operation_date = '$operation_date',
						Start_Datetime = '$Start_Datetime',
						End_Datetime = '$End_Datetime',
						Route = '$Route',
						distanceBand = '$distanceBand',
						truckType = '$truckType',
						Update_Datetime = NOW(),
						update_By = $cBy,
						fileupload_name = '$fileupload_name',
						AlertTypeCode = '$AlertTypeCode',
						CurrentLoadOperationalStatusEnumVal = '$CurrentLoadOperationalStatusEnumVal'
					WHERE
						Load_ID = '$Load_ID';";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
					}
				} else {

					$sql = "INSERT INTO tbl_204header_api (
						bailment, Status, truck_carrier, Work_Type, 
						Load_ID, tripType, tripType_partner, 
						operation_date, Start_Datetime, End_Datetime,
						Route, distanceBand, truckType,
						Create_Datetime, update_By, 
						AlertTypeCode, CurrentLoadOperationalStatusEnumVal )
					SELECT
						bailment, Status, truck_carrier, Work_Type, 
						Load_ID, tripType, tripType_partner, 
						operation_date, Start_Datetime, End_Datetime,
						Route, distanceBand, truckType, 
						NOW(), $cBy,
						AlertTypeCode, CurrentLoadOperationalStatusEnumVal
					FROM
						tbl_hourly_report_pending
					WHERE
						Load_ID = '$Load_ID';";
					//exit($sql);
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
					}
				}

				$sql = "UPDATE tbl_hourly_report_pending
				SET
					select_for_customer = 'Y',
					select_for_partner = 'Y',
					Update_Datetime = NOW(),
					update_By = $cBy
				WHERE
					Load_ID = '$Load_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			}

			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else if ($type == 12) {

		if (!isset($_POST["obj"])) {
			echo json_encode(array('status' => 'server', 'mms' => 'ไม่พบข้อมูล'));
			closeDB($mysqli);
		}

		$obj  = $_POST['obj'];

		$mysqli->autocommit(FALSE);
		try {

			foreach ($obj as $Load_ID) {
				$sql = "SELECT 
					Load_ID, tripType, tripType_partner
				FROM
					tbl_204header_api
				WHERE
					Load_ID = '$Load_ID';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows > 0) {
					while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
						$tripType = $row['tripType'];
						$tripType_partner = $row['tripType_partner'];
					}

					$sql = "SELECT truckLicense, truckType, Work_Type, tripType, tripType_partner, distanceBand, bailment, truck_carrier, driverName, phone, AlertTypeCode, 
					Load_ID, operation_date, Start_Datetime, End_Datetime, Route, CurrentLoadOperationalStatusEnumVal, Status, fileupload_name
					FROM tbl_hourly_report_pending 
					WHERE Load_ID = '$Load_ID';";
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
						$bailment = $row['bailment'];
						$Status = $row['Status'];
						$truck_carrier = $row['truck_carrier'];
						$Work_Type = $row['Work_Type'];
						$tripType = $row['tripType'];
						if ($tripType_partner != '') {
							$tripType_partner = $row['tripType_partner'];
						} else {
							$tripType_partner = '';
						}
						$operation_date = $row['operation_date'];
						$Start_Datetime = $row['Start_Datetime'];
						$End_Datetime = $row['End_Datetime'];
						$Route = $row['Route'];
						$distanceBand = $row['distanceBand'];
						$truckType = $row['truckType'];
						$AlertTypeCode = $row['AlertTypeCode'];
						$CurrentLoadOperationalStatusEnumVal = $row['CurrentLoadOperationalStatusEnumVal'];
						$fileupload_name = $row['fileupload_name'];
					}

					$sql = "UPDATE tbl_204header_api
					SET
						bailment = '$bailment',
						Status = '$Status',
						truck_carrier = '$truck_carrier',
						Work_Type = '$Work_Type',
						tripType = '$tripType',
						tripType_partner = '$tripType_partner',
						operation_date = '$operation_date',
						Start_Datetime = '$Start_Datetime',
						End_Datetime = '$End_Datetime',
						Route = '$Route',
						distanceBand = '$distanceBand',
						truckType = '$truckType',
						Update_Datetime = NOW(),
						update_By = $cBy,
						fileupload_name = '$fileupload_name',
						AlertTypeCode = '$AlertTypeCode',
						CurrentLoadOperationalStatusEnumVal = '$CurrentLoadOperationalStatusEnumVal'
					WHERE
						Load_ID = '$Load_ID';";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
					}
				} else {
					$tripType_partner = '';
					$sql = "INSERT INTO tbl_204header_api (
						bailment, Status, truck_carrier, Work_Type, 
						Load_ID, tripType, tripType_partner, 
						operation_date, Start_Datetime, End_Datetime,
						Route, distanceBand, truckType,
						Create_Datetime, update_By, 
						AlertTypeCode, CurrentLoadOperationalStatusEnumVal )
					SELECT
						bailment, Status, truck_carrier, Work_Type, 
						Load_ID, tripType, '$tripType_partner', 
						operation_date, Start_Datetime, End_Datetime,
						Route, distanceBand, truckType, 
						NOW(), $cBy,
						AlertTypeCode, CurrentLoadOperationalStatusEnumVal
					FROM
						tbl_hourly_report_pending
					WHERE
						Load_ID = '$Load_ID';";
					//exit($sql);
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
					}
				}

				$sql = "UPDATE tbl_hourly_report_pending
				SET
					select_for_customer = 'Y',
					Update_Datetime = NOW(),
					update_By = $cBy
				WHERE
					Load_ID = '$Load_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			}

			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else if ($type == 13) {

		if (!isset($_POST["obj"])) {
			echo json_encode(array('status' => 'server', 'mms' => 'ไม่พบข้อมูล'));
			closeDB($mysqli);
		}

		$obj  = $_POST['obj'];

		$mysqli->autocommit(FALSE);
		try {

			foreach ($obj as $Load_ID) {
				$sql = "SELECT 
					Load_ID, tripType, tripType_partner
				FROM
					tbl_204header_api
				WHERE
					Load_ID = '$Load_ID';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows > 0) {
					while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
						$tripType = $row['tripType'];
						$tripType_partner = $row['tripType_partner'];
					}

					$sql = "SELECT truckLicense, truckType, Work_Type, tripType, tripType_partner, distanceBand, bailment, truck_carrier, driverName, phone, AlertTypeCode, 
					Load_ID, operation_date, Start_Datetime, End_Datetime, Route, CurrentLoadOperationalStatusEnumVal, Status, fileupload_name
					FROM tbl_hourly_report_pending 
					WHERE Load_ID = '$Load_ID';";
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
						$bailment = $row['bailment'];
						$Status = $row['Status'];
						$truck_carrier = $row['truck_carrier'];
						$Work_Type = $row['Work_Type'];
						if ($tripType != '') {
							$tripType = $row['tripType'];
						} else {
							$tripType = '';
						}
						$tripType_partner = $row['tripType_partner'];
						$operation_date = $row['operation_date'];
						$Start_Datetime = $row['Start_Datetime'];
						$End_Datetime = $row['End_Datetime'];
						$Route = $row['Route'];
						$distanceBand = $row['distanceBand'];
						$truckType = $row['truckType'];
						$AlertTypeCode = $row['AlertTypeCode'];
						$CurrentLoadOperationalStatusEnumVal = $row['CurrentLoadOperationalStatusEnumVal'];
						$fileupload_name = $row['fileupload_name'];
					}

					$sql = "UPDATE tbl_204header_api
					SET
						bailment = '$bailment',
						Status = '$Status',
						truck_carrier = '$truck_carrier',
						Work_Type = '$Work_Type',
						tripType = '$tripType',
						tripType_partner = '$tripType_partner',
						operation_date = '$operation_date',
						Start_Datetime = '$Start_Datetime',
						End_Datetime = '$End_Datetime',
						Route = '$Route',
						distanceBand = '$distanceBand',
						truckType = '$truckType',
						Update_Datetime = NOW(),
						update_By = $cBy,
						fileupload_name = '$fileupload_name',
						AlertTypeCode = '$AlertTypeCode',
						CurrentLoadOperationalStatusEnumVal = '$CurrentLoadOperationalStatusEnumVal'
					WHERE
						Load_ID = '$Load_ID';";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
					}
				} else {
					$tripType = '';
					$sql = "INSERT INTO tbl_204header_api (
						bailment, Status, truck_carrier, Work_Type, 
						Load_ID, tripType, tripType_partner, 
						operation_date, Start_Datetime, End_Datetime,
						Route, distanceBand, truckType,
						Create_Datetime, update_By, 
						AlertTypeCode, CurrentLoadOperationalStatusEnumVal )
					SELECT
						bailment, Status, truck_carrier, Work_Type, 
						Load_ID, '$tripType', tripType_partner, 
						operation_date, Start_Datetime, End_Datetime,
						Route, distanceBand, truckType, 
						NOW(), $cBy,
						AlertTypeCode, CurrentLoadOperationalStatusEnumVal
					FROM
						tbl_hourly_report_pending
					WHERE
						Load_ID = '$Load_ID';";
					//exit($sql);
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
					}
				}

				$sql = "UPDATE tbl_hourly_report_pending
				SET
					select_for_partner = 'Y',
					Update_Datetime = NOW(),
					update_By = $cBy
				WHERE
					Load_ID = '$Load_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			}

			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'CheckHourlyReport'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {

		if (!isset($_POST["obj"])) {
			echo json_encode(array('status' => 'server', 'mms' => 'ไม่พบข้อมูล'));
			closeDB($mysqli);
		}

		$obj  = $_POST['obj'];
		$Load_ID = $obj['Load_ID'];

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				Load_ID, tripType, tripType_partner
			FROM
				tbl_204header_api
			WHERE
				Load_ID = '$Load_ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$tripType = $row['tripType'];
					$tripType_partner = $row['tripType_partner'];
				}

				$sql = "SELECT truckLicense, truckType, Work_Type, tripType, tripType_partner, distanceBand, bailment, truck_carrier, driverName, phone, AlertTypeCode, 
				Load_ID, operation_date, Start_Datetime, End_Datetime, Route, CurrentLoadOperationalStatusEnumVal, Status, fileupload_name
				FROM tbl_hourly_report_pending 
				WHERE Load_ID = '$Load_ID';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$bailment = $row['bailment'];
					$Status = $row['Status'];
					$truck_carrier = $row['truck_carrier'];
					$Work_Type = $row['Work_Type'];
					$tripType = $row['tripType'];
					if ($tripType_partner != '') {
						$tripType_partner = $row['tripType_partner'];
					} else {
						$tripType_partner = '';
					}
					$operation_date = $row['operation_date'];
					$Start_Datetime = $row['Start_Datetime'];
					$End_Datetime = $row['End_Datetime'];
					$Route = $row['Route'];
					$distanceBand = $row['distanceBand'];
					$truckType = $row['truckType'];
					$AlertTypeCode = $row['AlertTypeCode'];
					$CurrentLoadOperationalStatusEnumVal = $row['CurrentLoadOperationalStatusEnumVal'];
					$fileupload_name = $row['fileupload_name'];
				}

				$sql = "UPDATE tbl_204header_api
				SET
					bailment = '$bailment',
					Status = '$Status',
					truck_carrier = '$truck_carrier',
					Work_Type = '$Work_Type',
					tripType = '$tripType',
					tripType_partner = '$tripType_partner',
					operation_date = '$operation_date',
					Start_Datetime = '$Start_Datetime',
					End_Datetime = '$End_Datetime',
					Route = '$Route',
					distanceBand = '$distanceBand',
					truckType = '$truckType',
					Update_Datetime = NOW(),
					update_By = $cBy,
					fileupload_name = '$fileupload_name',
					AlertTypeCode = '$AlertTypeCode',
					CurrentLoadOperationalStatusEnumVal = '$CurrentLoadOperationalStatusEnumVal'
				WHERE
					Load_ID = '$Load_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
				}
			} else {
				$tripType_partner = '';
				$sql = "INSERT INTO tbl_204header_api (
					bailment, Status, truck_carrier, Work_Type, 
					Load_ID, tripType, tripType_partner, 
					operation_date, Start_Datetime, End_Datetime,
					Route, distanceBand, truckType,
					Create_Datetime, update_By, 
					AlertTypeCode, CurrentLoadOperationalStatusEnumVal )
				SELECT
					bailment, Status, truck_carrier, Work_Type, 
					Load_ID, tripType, '$tripType_partner', 
					operation_date, Start_Datetime, End_Datetime,
					Route, distanceBand, truckType, 
					NOW(), $cBy,
					AlertTypeCode, CurrentLoadOperationalStatusEnumVal
				FROM
					tbl_hourly_report_pending
				WHERE
					Load_ID = '$Load_ID';";
				//exit($sql);
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
				}
			}

			$sql = "UPDATE tbl_hourly_report_pending
			SET
				select_for_customer = 'Y',
				Update_Datetime = NOW(),
				update_By = $cBy
			WHERE
				Load_ID = '$Load_ID';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else if ($type == 22) {

		if (!isset($_POST["obj"])) {
			echo json_encode(array('status' => 'server', 'mms' => 'ไม่พบข้อมูล'));
			closeDB($mysqli);
		}

		$obj  = $_POST['obj'];
		$Load_ID = $obj['Load_ID'];

		//exit($Load_ID);

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				Load_ID
			FROM
				tbl_204header_api
			WHERE
				Load_ID = '$Load_ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {

				$sql = "UPDATE tbl_204header_api
				SET
					-- truck_carrier = '',
					tripType = '',
					Update_Datetime = NOW(),
					update_By = $cBy
				WHERE
					Load_ID = '$Load_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
				}

				$sql = "UPDATE tbl_hourly_report_pending
				SET
					select_for_customer = 'N',
					Update_Datetime = NOW(),
					update_By = $cBy
				WHERE
					Load_ID = '$Load_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			}

			$mysqli->commit();
			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else if ($type == 23) {

		if (!isset($_POST["obj"])) {
			echo json_encode(array('status' => 'server', 'mms' => 'ไม่พบข้อมูล'));
			closeDB($mysqli);
		}

		$obj  = $_POST['obj'];
		$Load_ID = $obj['Load_ID'];

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				Load_ID, tripType, tripType_partner
			FROM
				tbl_204header_api
			WHERE
				Load_ID = '$Load_ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$tripType = $row['tripType'];
					$tripType_partner = $row['tripType_partner'];
				}

				$sql = "SELECT truckLicense, truckType, Work_Type, tripType, tripType_partner, distanceBand, bailment, truck_carrier, driverName, phone, AlertTypeCode, 
				Load_ID, operation_date, Start_Datetime, End_Datetime, Route, CurrentLoadOperationalStatusEnumVal, Status, fileupload_name
				FROM tbl_hourly_report_pending 
				WHERE Load_ID = '$Load_ID';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
					$bailment = $row['bailment'];
					$Status = $row['Status'];
					$truck_carrier = $row['truck_carrier'];
					$Work_Type = $row['Work_Type'];
					if ($tripType != '') {
						$tripType = $row['tripType'];
					} else {
						$tripType = '';
					}
					$tripType_partner = $row['tripType_partner'];
					$operation_date = $row['operation_date'];
					$Start_Datetime = $row['Start_Datetime'];
					$End_Datetime = $row['End_Datetime'];
					$Route = $row['Route'];
					$distanceBand = $row['distanceBand'];
					$truckType = $row['truckType'];
					$AlertTypeCode = $row['AlertTypeCode'];
					$CurrentLoadOperationalStatusEnumVal = $row['CurrentLoadOperationalStatusEnumVal'];
					$fileupload_name = $row['fileupload_name'];
				}

				$sql = "UPDATE tbl_204header_api
				SET
					bailment = '$bailment',
					Status = '$Status',
					truck_carrier = '$truck_carrier',
					Work_Type = '$Work_Type',
					tripType = '$tripType',
					tripType_partner = '$tripType_partner',
					operation_date = '$operation_date',
					Start_Datetime = '$Start_Datetime',
					End_Datetime = '$End_Datetime',
					Route = '$Route',
					distanceBand = '$distanceBand',
					truckType = '$truckType',
					Update_Datetime = NOW(),
					update_By = $cBy,
					fileupload_name = '$fileupload_name',
					AlertTypeCode = '$AlertTypeCode',
					CurrentLoadOperationalStatusEnumVal = '$CurrentLoadOperationalStatusEnumVal'
				WHERE
					Load_ID = '$Load_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
				}
			} else {
				$tripType = '';
				$sql = "INSERT INTO tbl_204header_api (
					bailment, Status, truck_carrier, Work_Type, 
					Load_ID, tripType, tripType_partner, 
					operation_date, Start_Datetime, End_Datetime,
					Route, distanceBand, truckType,
					Create_Datetime, update_By, 
					AlertTypeCode, CurrentLoadOperationalStatusEnumVal )
				SELECT
					bailment, Status, truck_carrier, Work_Type, 
					Load_ID, '$tripType', tripType_partner, 
					operation_date, Start_Datetime, End_Datetime,
					Route, distanceBand, truckType, 
					NOW(), $cBy,
					AlertTypeCode, CurrentLoadOperationalStatusEnumVal
				FROM
					tbl_hourly_report_pending
				WHERE
					Load_ID = '$Load_ID';";
				//exit($sql);
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
				}
			}

			$sql = "UPDATE tbl_hourly_report_pending
			SET
				select_for_partner = 'Y',
				Update_Datetime = NOW(),
				update_By = $cBy
			WHERE
				Load_ID = '$Load_ID';";
			sqlError($mysqli, __LINE__, $sql, 1);
			if ($mysqli->affected_rows == 0) {
				throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
			}

			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else if ($type == 24) {

		if (!isset($_POST["obj"])) {
			echo json_encode(array('status' => 'server', 'mms' => 'ไม่พบข้อมูล'));
			closeDB($mysqli);
		}

		$obj  = $_POST['obj'];
		$Load_ID = $obj['Load_ID'];

		//exit($Load_ID);

		$mysqli->autocommit(FALSE);
		try {

			$sql = "SELECT 
				Load_ID
			FROM
				tbl_204header_api
			WHERE
				Load_ID = '$Load_ID';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows > 0) {

				$sql = "UPDATE tbl_204header_api
				SET
					-- truck_carrier = '',
					tripType_partner = '',
					Update_Datetime = NOW(),
					update_By = $cBy
				WHERE
					Load_ID = '$Load_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
				}

				$sql = "UPDATE tbl_hourly_report_pending
				SET
					select_for_partner = 'N',
					Update_Datetime = NOW(),
					update_By = $cBy
				WHERE
					Load_ID = '$Load_ID';";
				sqlError($mysqli, __LINE__, $sql, 1);
				if ($mysqli->affected_rows == 0) {
					throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
				}
			}

			$mysqli->commit();
			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'CheckHourlyReport'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {

		if (!isset($_POST["obj"])) {
			echo json_encode(array('status' => 'server', 'mms' => 'ไม่พบข้อมูล'));
			closeDB($mysqli);
		}

		$obj  = $_POST['obj'];

		$mysqli->autocommit(FALSE);
		try {

			foreach ($obj as $Load_ID) {
				$sql = "SELECT 
					Load_ID
				FROM
					tbl_204header_api
				WHERE
					Load_ID = '$Load_ID';";
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows > 0) {

					$sql = "UPDATE tbl_204header_api
					SET
						-- truck_carrier = '',
						tripType = '',
						tripType_partner = '',
						Update_Datetime = NOW(),
						update_By = $cBy
					WHERE
						Load_ID = '$Load_ID';";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
					}

					$sql = "UPDATE tbl_hourly_report_pending
					SET
						select_for_customer = 'N',
						select_for_partner = 'N',
						Update_Datetime = NOW(),
						update_By = $cBy
					WHERE
						Load_ID = '$Load_ID';";
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้' . __LINE__);
					}
				}
			}

			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
		closeDBT($mysqli, 1, jsonRow($re1, true, 0));
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'CheckHourlyReport'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else closeDBT($mysqli, 2, 'TYPE ERROR');

$mysqli->close();
exit();
