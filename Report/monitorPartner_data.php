<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'monitorPartner'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'monitorPartner'}[0] == 0) {
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


// set_time_limit(500);
// ini_set("memory_limit", "256M");
include('../php/connection.php');
include('queryMonthlyReport/dataSummaryReport.php');
if ($type <= 10) //data
{

	if ($_SESSION['xxxRole']->{'monitorPartner'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 1) {

		$dataParams = array(
			'obj',
			'obj=>start_date:s:0:2',
			'obj=>stop_date:s:0:2',
		);

		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {
			$array = array(
				'start_date' => $start_date,
				'stop_date' => $stop_date,
			);

			$sql = "WITH a AS (
				SELECT distinct 
					t1.truck_carrier,
					CASE
						WHEN t3.projectName = 'AAT MR' OR t3.projectName = 'Bailment' OR t3.projectName = 'PxP MR' OR t3.projectName = 'Service' THEN 'AAT-MR'
						-- WHEN t3.projectName = 'FTM MR' OR t3.projectName = 'SKD' THEN 'FTM-MR'
						-- WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) = 'F' THEN 'FTM-MR'
						WHEN t3.projectName = 'FTM MR' THEN 'FTM-MR'
						WHEN t3.projectName = 'SKD' THEN 'SKD-FTM'
						WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) = 'F' THEN 'EDC-FTM'
						WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) != 'F' THEN 'AAT EDC'
						ELSE t3.projectName
					END AS projectName,
					CASE
						WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) = 'F' THEN 'FTM'
						WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) != 'F' THEN 'AAT'
						ELSE ''
					END AS project,
					if(t1.pr_no = '', 'On Progress', 'Completed') AS status_carrier
				FROM
					tbl_204header_api t1
						INNER JOIN
					tbl_route_master_header t2 ON t1.Route = t2.routeName
						INNER JOIN 
					tbl_project_master t3 ON t2.projectID = t3.ID
				WHERE
					t1.operation_date BETWEEN '$start_date' AND '$stop_date'
						AND t1.truck_carrier != ''
						AND (t3.projectName = 'AAT MR' OR t3.projectName = 'Bailment' OR t3.projectName = 'PxP MR' OR t3.projectName = 'Service' 
						OR t3.projectName = 'FTM MR' OR t3.projectName = 'SKD' OR t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8')
						AND t1.operation_date IS NOT NULL
				ORDER BY t1.truck_carrier, t3.projectName),
			b as (SELECT truck_carrier, projectName, status_carrier, 
			ROW_NUMBER() OVER (PARTITION BY truck_carrier, projectName ORDER BY truck_carrier, projectName, status_carrier DESC) AS row_num 
			FROM a WHERE project != 'AAT')
			SELECT *, ROW_NUMBER() OVER (PARTITION BY projectName ORDER BY projectName, truck_carrier) AS row_no  FROM b WHERE row_num = 1 ;";
			//exit($sql);
			$re = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re->num_rows === 0) {
				throw new Exception('ไม่พบข้อมูล ' . __LINE__);
			}

			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else if ($type == 2) {

		$dataParams = array(
			'obj',
			'obj=>start_date:s:0:2',
			'obj=>stop_date:s:0:2',
			'obj=>pr_no:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$billing_for = 'Partner';
			$start = firstDay($start_date);

			$sqlwhere = "AND t1.pr_no = '$pr_no'";

			$sql = "WITH FindRoute AS (
				SELECT 
						t2.projectID, 
						-- t3.projectName,
						CASE
						WHEN t3.projectName = 'AAT MR' OR t3.projectName = 'Bailment' OR t3.projectName = 'PxP MR' OR t3.projectName = 'Service' THEN 'AAT-MR'
						-- WHEN t3.projectName = 'FTM MR' OR t3.projectName = 'SKD' THEN 'FTM-MR'
						-- WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) = 'F' THEN 'FTM-MR'
						WHEN t3.projectName = 'FTM MR' THEN 'FTM-MR'
						WHEN t3.projectName = 'SKD' THEN 'SKD-FTM'
						WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) = 'F' THEN 'EDC-FTM'
						WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) != 'F' THEN 'AAT EDC'
						ELSE t3.projectName
					END AS projectName,
						t1.operation_date, 
						DATE(t1.Start_Datetime) AS Start_Datetime,
						DATE(t1.End_Datetime) AS End_Datetime,
						DATE_ADD(DATE(t1.Start_Datetime), INTERVAL -DAY(DATE(t1.Start_Datetime))+1 DAY) as start_date,
						LAST_DAY(DATE(t1.End_Datetime)) as stop_date,
						t1.Load_ID, t1.Route, CONCAT(t1.Route,t1.Load_ID) as Internal_Tracking,
						t1.Work_Type, t1.truckType, t1.distanceBand, t1.truck_carrier,
						t1.pr_no,
						t1.invoice_no,
						t1.tripType_partner AS tripType
					FROM 
						tbl_204header_api t1
							LEFT JOIN tbl_route_master_header t2 ON t1.Route = t2.routeName
							LEFT JOIN tbl_project_master t3 ON t2.projectID = t3.ID
					WHERE 
						t1.Status = 'COMPLETED'
							AND t1.distanceBand IS NOT NULL
							AND t1.truck_carrier != ''
							$sqlwhere
				GROUP BY t1.Load_ID )
				SELECT t1.pr_no, t1.invoice_no, t1.truck_carrier,
					t1.projectID, t1.projectName, t1.operation_date,
					t1.Start_Datetime, 
					t1.End_Datetime, 
					t1.Load_ID, t1.Route, t1.Internal_Tracking, t1.Work_Type, t1.truckType,
					t1.tripType, 
					t1.distanceBand,
					t2.unitRate,
					1 AS Qty_Trip,
					(t2.unitRate*1) AS total
				FROM 
					FindRoute t1
						INNER JOIN tbl_trip_quotation t2 ON t1.truckType = t2.truckType AND t1.distanceBand = t2.distanceBand 
						AND t1.Work_Type = t2.Work_Type AND t1.tripType = t2.tripType
						AND t2.start_date = t1.start_date
						AND t2.billing_for = '$billing_for'
						AND t2.project = ''
					WHERE t2.start_date BETWEEN t1.start_date AND t1.stop_date";
			//exit($sql);
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows === 0) {
				throw new Exception('ไม่พบข้อมูล ' . __LINE__);
			}

			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else if ($type == 3) {

		$dataParams = array(
			'obj',
			'obj=>start_date:s:0:2',
			'obj=>stop_date:s:0:2',
			'obj=>truck_carrier:s:0:1',
			'obj=>projectName:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$billing_for = 'Partner';
			$start = firstDay($start_date);


			if ($projectName == 'AAT-MR') {
				$sqlwhere = "AND (t3.projectName = 'AAT MR' OR t3.projectName = 'Bailment' OR t3.projectName = 'PxP MR' OR t3.projectName = 'Service')
            		AND t1.truck_carrier = '$truck_carrier'";
			}
			// elseif ($projectName == 'FTM-MR') {
			// 	$sqlwhere = "AND (t3.projectName = 'FTM MR' OR t3.projectName = 'SKD' OR t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8')
			// 		AND t1.truck_carrier = '$truck_carrier'";
			// }
			elseif ($projectName == 'FTM-MR') {
				$sqlwhere = "AND (t3.projectName = 'FTM MR')
            		AND t1.truck_carrier = '$truck_carrier'";
			} elseif ($projectName == 'SKD-FTM') {
				$sqlwhere = "AND (t3.projectName = 'SKD')
            		AND t1.truck_carrier = '$truck_carrier'";
			} elseif ($projectName == 'EDC-FTM') {
				$sqlwhere = "AND (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8')
            		AND t1.truck_carrier = '$truck_carrier'";
			}

			$sql = "WITH FindRoute AS (
				SELECT 
						t2.projectID, 
						CASE
						WHEN t3.projectName = 'AAT MR' OR t3.projectName = 'Bailment' OR t3.projectName = 'PxP MR' OR t3.projectName = 'Service' THEN 'AAT-MR'
						-- WHEN t3.projectName = 'FTM MR' OR t3.projectName = 'SKD' THEN 'FTM-MR'
						-- WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) = 'F' THEN 'FTM-MR'
						WHEN t3.projectName = 'FTM MR' THEN 'FTM-MR'
						WHEN t3.projectName = 'SKD' THEN 'SKD-FTM'
						WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) = 'F' THEN 'EDC-FTM'
						WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) != 'F' THEN 'AAT EDC'
						ELSE t3.projectName
					END AS projectName,
						t1.operation_date, DATE(t1.Start_Datetime) AS Start_Datetime, DATE(t1.End_Datetime) AS End_Datetime,
						t1.Load_ID, t1.Route, CONCAT(t1.Route,t1.Load_ID) as Internal_Tracking,
						t1.Work_Type, t1.truckType, t1.distanceBand, t1.truck_carrier,
						t4.unitRate,
						t1.pr_no,
						t1.invoice_no,
						t1.tripType_partner AS tripType
					FROM 
						tbl_204header_api t1
							LEFT JOIN tbl_route_master_header t2 ON t1.Route = t2.routeName
							LEFT JOIN tbl_project_master t3 ON t2.projectID = t3.ID
							INNER JOIN tbl_trip_quotation t4 ON t1.truckType = t4.truckType AND t1.distanceBand = t4.distanceBand 
							AND t1.Work_Type = t4.Work_Type AND t1.tripType_partner = t4.tripType
							AND t4.start_date = '$start'
							AND t4.billing_for = '$billing_for'
							AND t4.project = ''
					WHERE 
						t1.operation_date BETWEEN '$start_date' AND '$stop_date'
							AND t1.Status = 'COMPLETED'
							AND t1.distanceBand IS NOT NULL
							-- AND t1.truck_carrier != ''
							$sqlwhere
				GROUP BY t1.Load_ID )
			SELECT pr_no, invoice_no, truck_carrier,
				projectID, projectName, operation_date, Start_Datetime, End_Datetime, 
				Load_ID, Route, Internal_Tracking, Work_Type, truckType,
				tripType, 
				distanceBand,
				unitRate,
				1 AS Qty_Trip,
				(unitRate*1) AS total
			FROM 
				FindRoute";
			//exit($sql);
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows === 0) {
				throw new Exception('ไม่พบข้อมูล ' . __LINE__);
			}

			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) //insert
{
	if ($_SESSION['xxxRole']->{'monitorPartner'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) //update
{
	if ($_SESSION['xxxRole']->{'monitorPartner'}[2] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 21) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 30 && $type <= 40) //delete
{
	if ($_SESSION['xxxRole']->{'monitorPartner'}[3] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 31) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 40 && $type <= 50) //save
{
	if ($_SESSION['xxxRole']->{'monitorPartner'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 50 && $type <= 60) {
} else closeDBT($mysqli, 2, 'TYPE ERROR');

$mysqli->close();
exit();
