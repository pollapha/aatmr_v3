<?php
ini_set('max_execution_time', 0);
ini_set("memory_limit", "4000M");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
include('../vendor/autoload.php');
session_start();
if (!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'summaryReport'})) {
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
} else if ($_SESSION['xxxRole']->{'summaryReport'}[0] == 0) {
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

include('../vendor/autoload.php');
include('../php/connection.php');
include('queryMonthlyReport/dataSummaryReport.php');
if ($type <= 10) {
	if ($_SESSION['xxxRole']->{'summaryReport'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 1) {

		$dataParams = array(
			'obj',
			'obj=>start_date:s:0:2',
			'obj=>stop_date:s:0:2',
			'obj=>project_name:s:0:2',
			'obj=>billing_for:s:0:2',
			'obj=>carrier:s:0:0',
		);

		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {
			$array = array(
				'start_date' => $start_date,
				'stop_date' => $stop_date,
				'project_name' => $project_name,
				'billing_for' => $billing_for,
				'carrier' => $carrier,
				'creater' => $cBy,
				'type' => $type
			);


			if ($billing_for == 'Customer' && $project_name == 'EDC-FTM') {
				$sql = sql_data_summary_service_edc_ftm($mysqli, $array);
				$re = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re->num_rows === 0) {
					throw new Exception('ไม่พบข้อมูล ' . __LINE__);
				}
			} else if ($project_name == 'AAT EDC') {
				$sql = sql_data_summary_service_aat_edc($mysqli, $array);
				$re = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re->num_rows === 0) {
					throw new Exception('ไม่พบข้อมูล ' . __LINE__);
				}
			} else {
				$sql = sql_data_summary_service($mysqli, $array);
				$re = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re->num_rows === 0) {
					throw new Exception('ไม่พบข้อมูล ' . __LINE__);
				}
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
			'obj=>project_name:s:0:0',
			'obj=>billing_for:s:0:2',
			'obj=>carrier:s:0:0',
			'obj=>pr_no:s:0:0',
			'obj=>invoice_no:s:0:0',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$array = array(
				'start_date' => $start_date,
				'stop_date' => $stop_date,
				'project_name' => $project_name,
				'billing_for' => $billing_for,
				'carrier' => $carrier,
				'creater' => $cBy,
				'pr_no' => $pr_no,
				'invoice_no' => $invoice_no,
				'type' => $type
			);


			$sql = sql_data_pr($mysqli, $array);
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
			'obj=>start_date:s:0:0',
			'obj=>stop_date:s:0:0',
			'obj=>project_name:s:0:0',
			'obj=>billing_for:s:0:0',
			'obj=>carrier:s:0:0',
			'obj=>pr_no:s:0:0',
			'obj=>invoice_no:s:0:0',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$array = array(
				'start_date' => $start_date,
				'stop_date' => $stop_date,
				'project_name' => $project_name,
				'billing_for' => $billing_for,
				'carrier' => $carrier,
				'creater' => $cBy,
				'pr_no' => $pr_no,
				'invoice_no' => $invoice_no,
				'type' => $type
			);

			$sql = sql_trip_summary_report_by_no($mysqli, $array);
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
	} else if ($type == 4) {

		$dataParams = array(
			'obj',
			'obj=>start_date:s:0:2',
			'obj=>stop_date:s:0:2',
			'obj=>project_name:s:0:0',
			'obj=>billing_for:s:0:2',
			'obj=>carrier:s:0:0',
			'obj=>pr_no:s:0:0',
			'obj=>invoice_no:s:0:0',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {
			$array = array(
				'billing_for' => $billing_for,
				'pr_no' => $pr_no,
			);

			$sql = sql_data_pr_find_pr_no($mysqli, $array);
			// $re1 = sqlError($mysqli, __LINE__, $sql, 1);
			// if ($re1->num_rows === 0) {
			// 	throw new Exception('ไม่พบข้อมูล ' . __LINE__);
			// }

			$mysqli->commit();

			closeDBT($mysqli, 1, $sql);
			//closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else if ($type == 6) {

		$dataParams = array(
			'obj',
			'obj=>start_date:s:0:2',
			'obj=>stop_date:s:0:2',
			'obj=>project_name:s:0:2',
			'obj=>billing_for:s:0:2',
			'obj=>carrier:s:0:0',
			'obj=>pr_no:s:0:0',
			'obj=>invoice_no:s:0:0',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$array = array(
				'start_date' => $start_date,
				'stop_date' => $stop_date,
				'project_name' => $project_name,
				'billing_for' => $billing_for,
				'carrier' => $carrier,
				'creater' => $cBy,
				'pr_no' => $pr_no,
				'invoice_no' => $invoice_no,
				'type' => $type
			);

			if ($project_name == 'AAT EDC_AAT' || $project_name == 'AAT EDC_FTM' || $project_name == 'ALL AAT EDC') {
				$sql = sql_trip_summary_report_edc_ftm($mysqli, $array);
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows === 0) {
					throw new Exception('ไม่พบข้อมูล ' . __LINE__);
				}
			} else {
				$sql = sql_trip_summary_report($mysqli, $array);
				$re1 = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re1->num_rows === 0) {
					throw new Exception('ไม่พบข้อมูล ' . __LINE__);
				}
			}



			$mysqli->commit();

			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else if ($type == 7) {

		$dataParams = array(
			'obj',
			'obj=>start_date:s:0:2',
			'obj=>stop_date:s:0:2',
			'obj=>project_name:s:0:2',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			$array = array(
				'start_date' => $start_date,
				'stop_date' => $stop_date,
				'project_name' => $project_name,
			);

			$sql = managementfees($mysqli, $array);
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows === 0) {
				throw new Exception('ไม่พบข้อมูล Management fees' . __LINE__);
			}

			$mysqli->commit();

			//closeDBT($mysqli, 1, $sql);
			closeDBT($mysqli, 1, jsonRow($re1, true, 0));
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 10 && $type <= 20) {
	if ($_SESSION['xxxRole']->{'summaryReport'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 11) {
		$dataParams = array(
			'obj',
			'obj=>issue_date:s:0:1',
			'obj=>remarks:s:0:0',
			'obj=>start_date:s:0:2',
			'obj=>stop_date:s:0:2',
			'obj=>project_name:s:0:2',
			'obj=>billing_for:s:0:2',
			'obj=>carrier:s:0:0',
			'obj=>pr_no:s:0:0',
			'obj=>invoice_no:s:0:0',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			//Create

			$sql = "SELECT user_name FROM tbl_user WHERE user_id = $cBy";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows === 0) {
				throw new Exception('ไม่พบข้อมูล ' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$user_name = $row['user_name'];
			}

			$issue_date = date_create($issue_date);
			$issue_date = date_format($issue_date, "Y-m-d");

			$explode = explode(', ', $remarks);
			$project_name = $explode[0];
			$carrier = $explode[1];

			$array = array(
				'start_date' => $start_date,
				'stop_date' => $stop_date,
				'project_name' => $project_name,
				'billing_for' => $billing_for,
				'carrier' => $carrier,
				'creater' => $cBy,
				'pr_no' => $pr_no,
				'invoice_no' => $invoice_no,
				'type' => $type
			);

			$total = 0;
			$datas = array();

			$project = '';
			if ($project_name == 'AAT MR') {
				$project = 'AAT-MR';
			} elseif ($project_name == 'FTM MR') {
				$project = 'FTM-MR';
			}
			$remarks = $project . ', ' . $carrier;


			$sql = sql_data_pr($mysqli, $array);
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows === 0) {
				throw new Exception('ไม่พบข้อมูล ' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$amount = $row['amount'];
				$total += $amount;
				array_push($datas, $row);
			}

			$data = array(
				//"TTV02734" - Wiwat
				//"TTV02368" - Piyaporn
				'user_code' => $user_name,
				'issue_date' => $issue_date,
				'summary' => $total,
				'remarks' => $remarks,
				'partner' => $carrier
			);

			$url = 'https://lib.albatrosslogistic.com/api_apps/pur/requisition';
			$ch = curl_init($url);
			$payload = json_encode(array("protocol" => "Publish_v1", "header" => $data, 'datas' => $datas));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			curl_setopt($ch, CURLOPT_CAINFO, "cacert.pem");
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$result = curl_exec($ch);

			if ($result === false) {
				echo curl_errno($ch) . "<br>";
				echo curl_error($ch) . "<br>";
			} else {
				curl_close($ch);
				$result1 = json_decode($result, true);
				$code = $result1['code'];

				if ($code == 200) {
					$pr_no = $result1['pr_no'];
					$query = sql_trip_summary_report($mysqli, $array);
					$sql = "UPDATE tbl_204header_api t1,
							($query) AS t2
							SET
								t1.pr_no = '$pr_no',  
								Update_Datetime = now(), 
								update_By = $cBy
							WHERE 
								t1.Load_ID = t2.Load_ID
									AND t1.pr_no = '';";
					//exit($sql);
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้ ' . __LINE__);
					}

					$sql = "INSERT INTO tbl_pr_number ( pr_no, issue_date, summary, remarks, start_date, stop_date, 
					billing_for, project_name, carrier, createDatetime, createBy, updateDatetime, updateBy )
					values( '$pr_no', '$issue_date', '$total', '$remarks', '$start_date', '$stop_date', 
					'$billing_for', '$project_name', '$carrier', now(), $cBy, now(), $cBy );";
					sqlError($mysqli, __LINE__, $sql, 1);
					// if ($mysqli->affected_rows == 0) {
					// 	throw new Exception('ไม่สามารถบันทึกข้อมูลได้ ' . __LINE__);
					// }
				}
			}


			$mysqli->commit();
			closeDBT($mysqli, 1, $result1);
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else if ($type == 12) {
		$dataParams = array(
			'obj',
			'obj=>issue_date:s:0:1',
			'obj=>remarks:s:0:0',
			'obj=>start_date:s:0:2',
			'obj=>stop_date:s:0:2',
			'obj=>billing_for:s:0:2',
			'obj=>carrier:s:0:0',
			'obj=>pr_no:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			//Update

			$sql = "SELECT user_name FROM tbl_user WHERE user_id = $cBy";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows === 0) {
				throw new Exception('ไม่พบข้อมูล ' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$user_name = $row['user_name'];
			}

			$issue_date = date_create($issue_date);
			$issue_date = date_format($issue_date, "Y-m-d");


			$sql = "SELECT pr_no, issue_date, summary, remarks, start_date, stop_date, billing_for, project_name, carrier FROM tbl_pr_number WHERE pr_no = '$pr_no';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			$header = jsonRow($re1, true, 0);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล ' . __LINE__);
			}

			$data = array(
				//"TTV02734" - Wiwat
				//"TTV02368" - Piyaporn
				//'user_code' => $user_name,
				'user_code' => 'TTV02734',
				'issue_date' => $issue_date,
				'remarks' => $remarks,
				'pr_no' => $pr_no
			);

			$url = 'https://lib.albatrosslogistic.com/api_apps/pur/requisition';
			$ch = curl_init($url);
			$payload = json_encode(array("protocol" => "Update_v2", "header" => $data));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			curl_setopt($ch, CURLOPT_CAINFO, "cacert.pem");
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$result = curl_exec($ch);

			if ($result === false) {
				echo curl_errno($ch) . "<br>";
				echo curl_error($ch) . "<br>";
			} else {
				curl_close($ch);
				$result1 = json_decode($result, true);
				$code = $result1['code'];

				if ($code == 200) {

					$sql = "UPDATE tbl_204header_api
							SET
								pr_no = '',  
								Update_Datetime = now(), 
								update_By = $cBy
							WHERE 
								pr_no = '$pr_no';";
					//exit($sql);
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้ ' . __LINE__);
					}

					$sql = "UPDATE tbl_pr_number
							SET
								issue_date = '$issue_date',  
								remarks = '$remarks',
								Update_Datetime = now(), 
								update_By = $cBy
							WHERE 
								pr_no = '$pr_no';";
					//exit($sql);
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้ ' . __LINE__);
					}
				}
			}

			$mysqli->commit();
			closeDBT($mysqli, 1, $result1);
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else if ($type == 13) {
		$dataParams = array(
			'obj',
			'obj=>issue_date:s:0:1',
			'obj=>remarks:s:0:0',
			'obj=>start_date:s:0:2',
			'obj=>stop_date:s:0:2',
			'obj=>billing_for:s:0:2',
			'obj=>carrier:s:0:0',
			'obj=>pr_no:s:0:1',
		);
		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {

			//Update

			$sql = "SELECT user_name FROM tbl_user WHERE user_id = $cBy";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re1->num_rows === 0) {
				throw new Exception('ไม่พบข้อมูล ' . __LINE__);
			}
			while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
				$user_name = $row['user_name'];
			}

			$issue_date = date_create($issue_date);
			$issue_date = date_format($issue_date, "Y-m-d");


			$sql = "SELECT pr_no, issue_date, summary, remarks, start_date, stop_date, billing_for, project_name, carrier FROM tbl_pr_number WHERE pr_no = '$pr_no';";
			$re1 = sqlError($mysqli, __LINE__, $sql, 1);
			$header = jsonRow($re1, true, 0);
			if ($re1->num_rows == 0) {
				throw new Exception('ไม่พบข้อมูล ' . __LINE__);
			}

			$data = array(
				//"TTV02734" - Wiwat
				//"TTV02368" - Piyaporn
				//'user_code' => $user_name,
				'user_code' => 'TTV02734',
				'issue_date' => $issue_date,
				'remarks' => $remarks,
				'pr_no' => $pr_no
			);

			$url = 'https://lib.albatrosslogistic.com/api_apps/pur/requisition';
			$ch = curl_init($url);
			$payload = json_encode(array("protocol" => "Cancel_v1", "header" => $data));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			curl_setopt($ch, CURLOPT_CAINFO, "cacert.pem");
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$result = curl_exec($ch);

			if ($result === false) {
				echo curl_errno($ch) . "<br>";
				echo curl_error($ch) . "<br>";
			} else {
				curl_close($ch);
				$result1 = json_decode($result, true);
				$code = $result1['code'];

				if ($code == 200) {

					$sql = "UPDATE tbl_204header_api
							SET
								pr_no = '',
								Update_Datetime = now(), 
								update_By = $cBy
							WHERE 
								pr_no = '$pr_no';";
					//exit($sql);
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้ ' . __LINE__);
					}

					$sql = "UPDATE tbl_pr_number
							SET
								pr_status = 'CANCEL',
								updateDatetime = now(), 
								updateBy = $cBy
							WHERE 
								pr_no = '$pr_no';";
					//exit($sql);
					sqlError($mysqli, __LINE__, $sql, 1);
					if ($mysqli->affected_rows == 0) {
						throw new Exception('ไม่สามารถบันทึกข้อมูลได้ ' . __LINE__);
					}
				}
			}

			$mysqli->commit();
			closeDBT($mysqli, 1, $result1);
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 20 && $type <= 30) {
} else if ($type > 30 && $type <= 40) {
} else if ($type > 40 && $type <= 50) {
	if ($_SESSION['xxxRole']->{'summaryReport'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 41) {
	} else closeDBT($mysqli, 2, 'TYPE ERROR');
} else if ($type > 50 && $type <= 60) {
	if ($_SESSION['xxxRole']->{'summaryReport'}[1] == 0) closeDBT($mysqli, 9, 'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if ($type == 51) {
		if (!isset($_FILES["upload"])) {
			echo json_encode(array('status' => 'server', 'mms' => 'ไม่พบไฟล์ UPLOAD'));
			closeDB($mysqli);
		}

		if (!isset($_POST['project_name'])) {
			echo json_encode(array('status' => 'server', 'mms' => 'ไม่พบ Project'));
			closeDB($mysqli);
		}

		$project_name = $_POST['project_name'];

		$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);
		$fileName = $randomString . '_' . $_FILES["upload"]["name"];
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
					$newArray = array();

					$total = 0;
					$total_add = 0;
					if (count($data) > 0) {

						foreach ($data as $row) {
							if ($count > 0) {
								if ($row[0] != NULL) {

									if ($row[0] == 'Completed') {

										$bailment = '';
										$Status = strtoupper($row[0]);
										$truck_carrier = $row[2];
										$Work_Type = $row[4];
										$Load_ID = $row[5];
										$tripType = $row[7];
										$tripType_partner = $row[8];
										$operation_date = convertDate($row[9]);
										$Start_Datetime = convertDate($row[10]);
										$End_Datetime = convertDate($row[11]);
										$Route = $row[13];
										$distanceBand = $row[14];
										$number = $row[15];
										$project = $row[16];
										$truckType = $row[19];

										if ($tripType_partner == '') {
											$tripType_partner = $tripType;
										}
										//$truckLicense = $row[20];
										//$driverName = $row[21];
										//$phone = $row[22];
										$AlertTypeCode = "LOAD_TENDER_NEW";
										//
									} else if ($row[0] == 'Bailment' || $row[0] == 'Non Bailment') {
										//throw new Exception('เลือก Project ในการ Upload ไม่ถูกต้อง<br>' . $row[6]);
										$bailment = $row[0];
										$Status = strtoupper($row[1]);
										$truck_carrier = $row[3];
										$Work_Type = $row[5];
										$Load_ID = $row[6];
										$tripType = $row[8];
										$tripType_partner = $row[9];
										$operation_date = convertDate($row[10]);
										$Start_Datetime = convertDate($row[11]);
										$End_Datetime = convertDate($row[12]);
										$Route = $row[14];
										$distanceBand = $row[15];
										$project = $row[17];
										$truckType = $row[20];
										// $truckLicense = $row[21];
										// $driverName = $row[22];
										// $phone = $row[23];
										$AlertTypeCode = "LOAD_TENDER_NEW";
									}


									if (substr("$Load_ID", 0, 2) == "PO") {

										//$fName = $_FILES["upload"]["name"];
										//$explode = explode('.xlsx', $fName);
										//$fName = $explode[0];

										$CurrentLoadOperationalStatusEnumVal = 'S_COMPLETED';
										//special
										//สร้างเลข load_id
										$date = date_create($operation_date);
										$text = date_format($date, "ymd");

										$number = str_pad($number, 3, '0', STR_PAD_LEFT);
										//$Load_ID = $Load_ID . ' L' . $text  . $number . ' ' . $fName;
										$Load_ID = $Load_ID . ' L' . $Route . $text  . $number;

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
													bailment = '$bailment',
													Status = '$Status',
													truck_carrier = '$truck_carrier',
													Work_Type = '$Work_Type',
													Load_ID = '$Load_ID',
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
													fileupload_name = '$fileName',
													AlertTypeCode = '$AlertTypeCode',
													CurrentLoadOperationalStatusEnumVal = '$CurrentLoadOperationalStatusEnumVal'
												WHERE
													Load_ID = '$Load_ID';";
											//exit($sql);
											sqlError($mysqli, __LINE__, $sql, 1);
											if ($mysqli->affected_rows == 0) {
												throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
												//continue;
											}
											$total += $mysqli->affected_rows;
										} else {
											$sql = "INSERT INTO tbl_204header_api(
													bailment, Status, truck_carrier, Work_Type, Load_ID, tripType, tripType_partner, operation_date, Start_Datetime, End_Datetime,
													Route, distanceBand, 
													Create_Datetime, update_By, fileupload_name, AlertTypeCode, CurrentLoadOperationalStatusEnumVal )
												VALUES(
													'$bailment', '$Status', '$truck_carrier', '$Work_Type', '$Load_ID', '$tripType', '$tripType_partner', '$operation_date', '$Start_Datetime', '$End_Datetime',
													'$Route', '$distanceBand', '$truckType', 
													NOW(), $cBy, '$fileName', '$AlertTypeCode', '$CurrentLoadOperationalStatusEnumVal'
												);";
											//exit($sql);
											sqlError($mysqli, __LINE__, $sql, 1);
											if ($mysqli->affected_rows == 0) {
												throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
											}
											$total += $mysqli->affected_rows;
										}
									} else {
										$CurrentLoadOperationalStatusEnumVal = 'S_COMPLETED';

										if ($project == 'AAT-MR') {
											$project = 'AAT MR';
										} elseif ($project == 'FTM-MR') {
											$project = 'FTM MR';
										} elseif ($project == 'AAT-EDC') {
											$project = 'AAT EDC';
										}


										$sql = "SELECT 
											projectName,
											ID AS projectID
										FROM
											tbl_project_master
										WHERE 
											projectName = '$project';";
										$re1 = sqlError($mysqli, __LINE__, $sql, 1);
										if ($re1->num_rows == 0) {
											throw new Exception('ไม่พบ Project ' . $project);
										}
										while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
											$projectID = $row['projectID'];
										}

										$sql = "SELECT 
												Load_ID,
												Route AS jda_route
											FROM
												tbl_204header_api
											WHERE 
												Load_ID = '$Load_ID';";
										$re1 = sqlError($mysqli, __LINE__, $sql, 1);

										// var_dump($re1);
										// exit();
										if ($re1->num_rows > 0) {

											$jda_route = $re1->fetch_array(MYSQLI_ASSOC)['jda_route'];

											$sql = "SELECT 
													Load_ID,
													CurrentLoadOperationalStatusEnumVal
												FROM
													tbl_204header_api
												WHERE 
													Load_ID = '$Load_ID'
													AND (CurrentLoadOperationalStatusEnumVal = 'S_TENDER_REJECTED' OR CurrentLoadOperationalStatusEnumVal = 'S_CANCELLED');";
											$re1 = sqlError($mysqli, __LINE__, $sql, 1);
											if ($re1->num_rows > 0) {
												while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
													$CurrentLoadOperationalStatusEnumVal = $row['CurrentLoadOperationalStatusEnumVal'];
												}
												throw new Exception('Operation Status : ' . $CurrentLoadOperationalStatusEnumVal . "<br>Load ID. : " . $Load_ID);
												//throw new Exception("Load ID. : " . $Load_ID . "<br>JDA Status : " . $CurrentLoadOperationalStatusEnumVal);
												//array_push($mistakeArray, $Load_ID);
											}



											if ($jda_route == $Route) {
												$sql = "SELECT 
													routeName
												FROM
													tbl_route_master_header
												WHERE 
													routeName = '$Route';";
												$re1 = sqlError($mysqli, __LINE__, $sql, 1);
												if ($re1->num_rows == 0) {

													$sql = "INSERT INTO tbl_route_master_header
													( routeName,projectID, distanceBand,createDatetime,createBy )
													VALUES( '$Route', '$projectID', '$distanceBand',NOW(), $cBy);";
													//exit($sql);
													sqlError($mysqli, __LINE__, $sql, 1);
													if ($mysqli->affected_rows == 0) {
														throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
													}
												}
											} else {

												$Route = $jda_route;
												//throw new Exception('Load ID : ' . $Load_ID . "<br> Route ไม่ตรงกับใน JDA<br>Route in JDA : " . $jda_route . "<br>Route in Hourly Report : " . $Route);
											}



											$sql = "SELECT 
												routeName,
												distanceBand AS route_distanceBand
											FROM
												tbl_route_master_header
											WHERE 
												routeName = '$Route';";
											$re1 = sqlError($mysqli, __LINE__, $sql, 1);
											if ($re1->num_rows > 0) {
												$route_distanceBand = $re1->fetch_array(MYSQLI_ASSOC)['route_distanceBand'];
											}


											if (strpos(strtolower($tripType), 'way') !== false) {
												$tripType = '1WAY';
											} elseif (strpos(strtolower($tripType), 'round') !== false) {
												$tripType = 'ROUND';
											} elseif ($tripType == '') {
												$tripType = '';
											} else {
												throw new Exception('Status Route ไม่ถูกต้อง<br>Load ID : ' . $Load_ID);
											}


											if (strpos(strtolower($tripType_partner), 'way') !== false) {
												$tripType_partner = '1WAY';
											} elseif (strpos(strtolower($tripType_partner), 'round') !== false) {
												$tripType_partner = 'ROUND';
											} elseif ($tripType_partner == '') {
												$tripType_partner = '';
											} else {
												throw new Exception('Status Route ไม่ถูกต้อง<br>Load ID : ' . $Load_ID);
											}


											$sql = "SELECT 
												routeName,
												projectID
											FROM
												tbl_route_master_header
											WHERE 
												routeName = '$Route'
													AND projectID = '$projectID';";
											$re1 = sqlError($mysqli, __LINE__, $sql, 1);
											if ($re1->num_rows == 0) {
												throw new Exception('Project ไม่ถูกต้อง<br>Load ID : ' . $Load_ID);
											}

											$distanceBand = replaceStr($distanceBand);
											$route_distanceBand = replaceStr($route_distanceBand);

											//exit();

											if ($route_distanceBand != $distanceBand) {
												throw new Exception('Load ID : ' . $Load_ID . '<br>Route : ' . $jda_route . "<br> Distance ไม่ตรงกับใน Master<br>Distance in Master: " . $route_distanceBand .
													"<br>Distance in Hourly Report : " . $distanceBand);
											}


											if ($tripType == '' && $tripType_partner != '') {

												$sql = "UPDATE tbl_204header_api
												SET
													bailment = '$bailment',
													Status = '$Status',
													truck_carrier = '$truck_carrier',
													Work_Type = '$Work_Type',
													Load_ID = '$Load_ID',
													tripType_partner = '$tripType_partner',
													operation_date = '$operation_date',
													Start_Datetime = '$Start_Datetime',
													End_Datetime = '$End_Datetime',
													Route = '$Route',
													distanceBand = '$distanceBand',
													truckType = '$truckType',
													Update_Datetime = NOW(),
													update_By = $cBy,
													fileupload_name = '$fileName',
													AlertTypeCode = '$AlertTypeCode',
													CurrentLoadOperationalStatusEnumVal = '$CurrentLoadOperationalStatusEnumVal'
												WHERE
													Load_ID = '$Load_ID';";
												sqlError($mysqli, __LINE__, $sql, 1);
												if ($mysqli->affected_rows == 0) {
													throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
												}
												$total += $mysqli->affected_rows;
											} else if ($tripType_partner == '' && $tripType != '') {

												$sql = "UPDATE tbl_204header_api
												SET
													bailment = '$bailment',
													Status = '$Status',
													truck_carrier = '$truck_carrier',
													Work_Type = '$Work_Type',
													Load_ID = '$Load_ID',
													tripType = '$tripType',
													operation_date = '$operation_date',
													Start_Datetime = '$Start_Datetime',
													End_Datetime = '$End_Datetime',
													Route = '$Route',
													distanceBand = '$distanceBand',
													truckType = '$truckType',
													Update_Datetime = NOW(),
													update_By = $cBy,
													fileupload_name = '$fileName',
													AlertTypeCode = '$AlertTypeCode',
													CurrentLoadOperationalStatusEnumVal = '$CurrentLoadOperationalStatusEnumVal'
												WHERE
													Load_ID = '$Load_ID';";
												sqlError($mysqli, __LINE__, $sql, 1);
												if ($mysqli->affected_rows == 0) {
													throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
												}
												$total += $mysqli->affected_rows;
											} else {
												$sql = "UPDATE tbl_204header_api
												SET
													bailment = '$bailment',
													Status = '$Status',
													truck_carrier = '$truck_carrier',
													Work_Type = '$Work_Type',
													Load_ID = '$Load_ID',
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
													fileupload_name = '$fileName',
													AlertTypeCode = '$AlertTypeCode',
													CurrentLoadOperationalStatusEnumVal = '$CurrentLoadOperationalStatusEnumVal'
												WHERE
													Load_ID = '$Load_ID';";
												sqlError($mysqli, __LINE__, $sql, 1);
												if ($mysqli->affected_rows == 0) {
													throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
												}
												$total += $mysqli->affected_rows;
											}
										} else {

											if (strpos(strtolower($tripType), 'way') !== false) {
												$tripType = '1WAY';
											} elseif (strpos(strtolower($tripType), 'round') !== false) {
												$tripType = 'ROUND';
											} elseif ($tripType == '') {
												$tripType = '';
											} else {
												throw new Exception('Status Route ไม่ถูกต้อง<br>Load ID : ' . $Load_ID);
											}


											if (strpos(strtolower($tripType_partner), 'way') !== false) {
												$tripType_partner = '1WAY';
											} elseif (strpos(strtolower($tripType_partner), 'round') !== false) {
												$tripType_partner = 'ROUND';
											} elseif ($tripType_partner == '') {
												$tripType_partner = '';
											} else {
												throw new Exception('Status Route ไม่ถูกต้อง<br>Load ID : ' . $Load_ID);
											}


											$CurrentLoadOperationalStatusEnumVal = 'S_COMPLETED';

											// $sql = "INSERT INTO tbl_204header_api (
											// 	bailment, Status, truck_carrier, Work_Type, 
											// 	Load_ID, tripType, tripType_partner, 
											// 	operation_date, Start_Datetime, End_Datetime,
											// 	Route, distanceBand, truckType,
											// 	Create_Datetime, update_By, 
											// 	fileupload_name, 
											// 	AlertTypeCode, CurrentLoadOperationalStatusEnumVal )
											// VALUES(
											// 	'$bailment', '$Status', '$truck_carrier', '$Work_Type', 
											// 	'$Load_ID', '$tripType', '$tripType_partner', 
											// 	'$operation_date', '$Start_Datetime', '$End_Datetime',
											// 	'$Route', '$distanceBand', '$truckType', 
											// 	NOW(), $cBy, 
											// 	'$fileName', 
											// 	'$AlertTypeCode', '$CurrentLoadOperationalStatusEnumVal'
											// );";
											// //exit($sql);
											// sqlError($mysqli, __LINE__, $sql, 1);
											// if ($mysqli->affected_rows == 0) {
											// 	throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
											// }

											if ($tripType == '' && $tripType_partner != '') {
												$sql = "SELECT 
												Load_ID
												FROM
													tbl_hourly_report_pending
												WHERE 
													Load_ID = '$Load_ID';";
												$re1 = sqlError($mysqli, __LINE__, $sql, 1);
												if ($re1->num_rows > 0) {

													$sql = "UPDATE tbl_hourly_report_pending
													SET
														bailment = '$bailment',
														Status = '$Status',
														truck_carrier = '$truck_carrier',
														Work_Type = '$Work_Type',
														Load_ID = '$Load_ID',
														tripType_partner = '$tripType_partner',
														operation_date = '$operation_date',
														Start_Datetime = '$Start_Datetime',
														End_Datetime = '$End_Datetime',
														Route = '$Route',
														distanceBand = '$distanceBand',
														truckType = '$truckType',
														Update_Datetime = NOW(),
														update_By = $cBy,
														fileupload_name = '$fileName',
														AlertTypeCode = '$AlertTypeCode',
														CurrentLoadOperationalStatusEnumVal = '$CurrentLoadOperationalStatusEnumVal'
													WHERE
														Load_ID = '$Load_ID';";
													sqlError($mysqli, __LINE__, $sql, 1);
													if ($mysqli->affected_rows == 0) {
														throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
													}
													$total_add += $mysqli->affected_rows;
												} else {
													$sql = "INSERT INTO tbl_hourly_report_pending (
													bailment, Status, truck_carrier, Work_Type, 
													Load_ID, tripType_partner, 
													operation_date, Start_Datetime, End_Datetime,
													Route, distanceBand, truckType,
													Create_Datetime, update_By, 
													fileupload_name, 
													AlertTypeCode, CurrentLoadOperationalStatusEnumVal )
												VALUES(
													'$bailment', '$Status', '$truck_carrier', '$Work_Type', 
													'$Load_ID', '$tripType_partner', 
													'$operation_date', '$Start_Datetime', '$End_Datetime',
													'$Route', '$distanceBand', '$truckType', 
													NOW(), $cBy, 
													'$fileName', 
													'$AlertTypeCode', '$CurrentLoadOperationalStatusEnumVal'
												);";
													//exit($sql);
													sqlError($mysqli, __LINE__, $sql, 1);
													if ($mysqli->affected_rows == 0) {
														throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
													}
													$total_add += $mysqli->affected_rows;
												}
											} else if ($tripType_partner == '' && $tripType != '') {
												$sql = "SELECT 
												Load_ID
												FROM
													tbl_hourly_report_pending
												WHERE 
													Load_ID = '$Load_ID';";
												$re1 = sqlError($mysqli, __LINE__, $sql, 1);
												if ($re1->num_rows > 0) {

													$sql = "UPDATE tbl_hourly_report_pending
													SET
														bailment = '$bailment',
														Status = '$Status',
														truck_carrier = '$truck_carrier',
														Work_Type = '$Work_Type',
														Load_ID = '$Load_ID',
														tripType = '$tripType',
														operation_date = '$operation_date',
														Start_Datetime = '$Start_Datetime',
														End_Datetime = '$End_Datetime',
														Route = '$Route',
														distanceBand = '$distanceBand',
														truckType = '$truckType',
														Update_Datetime = NOW(),
														update_By = $cBy,
														fileupload_name = '$fileName',
														AlertTypeCode = '$AlertTypeCode',
														CurrentLoadOperationalStatusEnumVal = '$CurrentLoadOperationalStatusEnumVal'
													WHERE
														Load_ID = '$Load_ID';";
													sqlError($mysqli, __LINE__, $sql, 1);
													if ($mysqli->affected_rows == 0) {
														throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
													}
													$total_add += $mysqli->affected_rows;
												} else {
													$sql = "INSERT INTO tbl_hourly_report_pending (
														bailment, Status, truck_carrier, Work_Type, 
														Load_ID, tripType,
														operation_date, Start_Datetime, End_Datetime,
														Route, distanceBand, truckType,
														Create_Datetime, update_By, 
														fileupload_name, 
														AlertTypeCode, CurrentLoadOperationalStatusEnumVal )
													VALUES(
														'$bailment', '$Status', '$truck_carrier', '$Work_Type', 
														'$Load_ID', '$tripType',
														'$operation_date', '$Start_Datetime', '$End_Datetime',
														'$Route', '$distanceBand', '$truckType', 
														NOW(), $cBy, 
														'$fileName', 
														'$AlertTypeCode', '$CurrentLoadOperationalStatusEnumVal'
												);";
													//exit($sql);
													sqlError($mysqli, __LINE__, $sql, 1);
													if ($mysqli->affected_rows == 0) {
														throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
													}
													$total_add += $mysqli->affected_rows;
												}
											} else {
												$sql = "SELECT 
													Load_ID
												FROM
													tbl_hourly_report_pending
												WHERE 
													Load_ID = '$Load_ID';";
												$re1 = sqlError($mysqli, __LINE__, $sql, 1);
												if ($re1->num_rows > 0) {

													$sql = "UPDATE tbl_hourly_report_pending
													SET
														bailment = '$bailment',
														Status = '$Status',
														truck_carrier = '$truck_carrier',
														Work_Type = '$Work_Type',
														Load_ID = '$Load_ID',
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
														fileupload_name = '$fileName',
														AlertTypeCode = '$AlertTypeCode',
														CurrentLoadOperationalStatusEnumVal = '$CurrentLoadOperationalStatusEnumVal'
													WHERE
														Load_ID = '$Load_ID';";
													sqlError($mysqli, __LINE__, $sql, 1);
													if ($mysqli->affected_rows == 0) {
														throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
													}
													$total_add += $mysqli->affected_rows;
												} else {
													$sql = "INSERT INTO tbl_hourly_report_pending (
														bailment, Status, truck_carrier, Work_Type, 
														Load_ID, tripType, tripType_partner, 
														operation_date, Start_Datetime, End_Datetime,
														Route, distanceBand, truckType,
														Create_Datetime, update_By, 
														fileupload_name, 
														AlertTypeCode, CurrentLoadOperationalStatusEnumVal )
													VALUES(
														'$bailment', '$Status', '$truck_carrier', '$Work_Type', 
														'$Load_ID', '$tripType', '$tripType_partner', 
														'$operation_date', '$Start_Datetime', '$End_Datetime',
														'$Route', '$distanceBand', '$truckType', 
														NOW(), $cBy, 
														'$fileName', 
														'$AlertTypeCode', '$CurrentLoadOperationalStatusEnumVal'
													);";
													//exit($sql);
													sqlError($mysqli, __LINE__, $sql, 1);
													if ($mysqli->affected_rows == 0) {
														throw new Exception('ไม่สามารถแก้ไขข้อมูลได้ ' . __LINE__);
													}
													$total_add += $mysqli->affected_rows;
												}
											}
										}
									}
								}
							} else {
								$count = 1;
							}
						}

						$mysqli->commit();

						if ($total == 0) {
							if ($total_add > 0) {
								echo '{"status":"server","mms":"Upload สำเร็จ : ' . $total . ' รายการ<br>Upload ไม่สำเร็จ : ' . $total_add . ' รายการ<br>กรุณาตรวจสอบในเมนู 2.8' . '","data":[]}';
								//echo '{"status":"server","mms":"Update สำเร็จ : ' . $total . '<br>Upload ไม่ผ่าน : ' . $total_add . 'กรุณาตรวจสอบในเมนู 2.8,"data":[]}';
							} else {
								throw new Exception('ไม่มีรายการอัพเดท');
							}
						} else {
							if ($total_add > 0) {
								echo '{"status":"server","mms":"Upload สำเร็จ : ' . $total . ' รายการ<br>Upload ไม่สำเร็จ : ' . $total_add . ' รายการ<br>กรุณาตรวจสอบในเมนู 2.8' . '","data":[]}';
								//echo '{"status":"server","mms":"Update สำเร็จ : ' . $total . '<br>Upload ไม่ผ่าน : ' . $total_add . 'กรุณาตรวจสอบในเมนู 2.8,"data":[]}';
							} else {
								echo '{"status":"server","mms":"Update สำเร็จ ' . $total . '","data":[]}';
							}
						}
						closeDB($mysqli);
					} else {
						echo '{"status":"server","mms":"ไม่พบข้อมูลในไฟล์ ' . count($data) . '","data":[]}';
						closeDB($mysqli);
					}
					//}
					closeDBT($mysqli, 1, jsonRow($re1, true, 0));
				}
			} catch (Exception $e) {
				$mysqli->rollback();
				echo '{"status":"server","mms":"' . $e->getMessage() . '","sname":[]}';
				closeDB($mysqli);
			}
		} else echo json_encode(array('status' => 'server', 'mms' => 'ข้อมูลในไฟล์ไม่ถูกต้อง', 'sname' => array()));
	} else if ($type == 52) {
		if (!isset($_FILES["upload"])) {
			echo json_encode(array('status' => 'server', 'mms' => 'ไม่พบไฟล์ UPLOAD'));
			closeDB($mysqli);
		}

		if (!isset($_POST['project_name'])) {
			echo json_encode(array('status' => 'server', 'mms' => 'ไม่พบ Project'));
			closeDB($mysqli);
		}

		$project_name = $_POST['project_name'];

		$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 5);
		$fileName = $randomString . '_' . $_FILES["upload"]["name"];
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
					$newArray = array();

					//var_dump($data);
					$batchSize = 1000;
					$insertData[] = $data;
					$updateData = [];

					// In the loop where you process rows:
					// if (condition_for_insert) {
					// 	$insertData[] = [/* data for insert */];
					// } else {
					// 	$updateData[] = [/* data for update */];
					// }

					if (count($insertData) >= $batchSize) {
						batchInsert($mysqli, 'tbl_204header_api', $insertData);
						$insertData = [];
					}

					/* if (count($updateData) >= $batchSize) {
						batchUpdate($mysqli, 'table_name', $updateData);
						$updateData = [];
					} */

					// After the loop, insert/update any remaining data
					/* if (!empty($insertData)) {
						batchInsert($mysqli, 'tbl_204header_api', $insertData);
					} */
					/* if (!empty($updateData)) {
						batchUpdate($mysqli, 'table_name', $updateData);
					} */


					exit();
				}
			} catch (Exception $e) {
				$mysqli->rollback();
				echo '{"status":"server","mms":"' . $e->getMessage() . '","sname":[]}';
				closeDB($mysqli);
			}
		} else echo json_encode(array('status' => 'server', 'mms' => 'ข้อมูลในไฟล์ไม่ถูกต้อง', 'sname' => array()));
	} else if ($type == 54) {
		$dataParams = array(
			'obj',
			'obj=>start_date:s:0:2',
			'obj=>stop_date:s:0:2',
			'obj=>project_name:s:0:2',
			'obj=>billing_for:s:0:2',
			'obj=>carrier:s:0:0',
			'obj=>pr_no:s:0:0',
			'obj=>invoice_no:s:0:0',
		);

		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {
			$array = array(
				'start_date' => $start_date,
				'stop_date' => $stop_date,
				'project_name' => $project_name,
				'billing_for' => $billing_for,
				'carrier' => $carrier,
				'creater' => $cBy,
				'pr_no' => $cBy,
				'invoice_no' => $cBy,
				'type' => $type
			);


			if ($billing_for == 'Customer' && $project_name == 'EDC-FTM') {

				$sql = sql_data_summary_service_edc_ftm($mysqli, $array);
				$re = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re->num_rows === 0) {
					throw new Exception('ไม่พบข้อมูล ' . __LINE__);
				}
				$dataArray = array();
				while ($row = $re->fetch_assoc()) {
					$dataArray[] = $row;
				}

				$sql = sql_trip_summary_report_edc_ftm($mysqli, $array);
				$re = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re->num_rows === 0) {
					throw new Exception('ไม่พบข้อมูล ' . __LINE__);
				}
				$dataArraySummaryTrip = array();
				while ($row = $re->fetch_assoc()) {
					$dataArraySummaryTrip[] = $row;
				}

				include('queryMonthlyReport/aat_edc/excel_summaryServiceEDC_FTM.php');
				include('queryMonthlyReport/aat_edc/excel_TripSummaryReportEDC_FTM.php');
			} else if ($project_name == 'AAT EDC') {
				if ($billing_for == 'Customer') {
					$arr = array();

					$sql = managementfees($mysqli, $array);
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re1->num_rows === 0) {
						throw new Exception('ไม่พบข้อมูล ' . __LINE__);
					}
					while ($row = $re1->fetch_array(MYSQLI_NUM)) {
						array_push($arr, $row);
					}

					$sql = sql_data_summary_service_aat_edc($mysqli, $array);
					$re = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re->num_rows === 0) {
						throw new Exception('ไม่พบข้อมูล ' . __LINE__);
					}
					$dataArray = array();
					while ($row = $re->fetch_assoc()) {
						$dataArray[] = $row;
					}

					$sql = sql_trip_summary_report_aat_edc($mysqli, $array);
					$re = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re->num_rows === 0) {
						throw new Exception('ไม่พบข้อมูล ' . __LINE__);
					}
					$dataArraySummaryTrip = array();
					while ($row = $re->fetch_assoc()) {
						$dataArraySummaryTrip[] = $row;
					}

					include('queryMonthlyReport/aat_edc/excel_summaryServiceAAT_EDC.php');
					include('queryMonthlyReport/aat_edc/excel_TripSummaryReportAAT_EDC.php');
				} elseif ($billing_for == 'Partner') {

					$arr = array();

					$sql = managementfees($mysqli, $array);
					$re1 = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re1->num_rows === 0) {
						throw new Exception('ไม่พบข้อมูล ' . __LINE__);
					}
					while ($row = $re1->fetch_array(MYSQLI_NUM)) {
						array_push($arr, $row);
					}

					$sql = sql_data_summary_service_aat_edc($mysqli, $array);
					$re = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re->num_rows === 0) {
						throw new Exception('ไม่พบข้อมูล ' . __LINE__);
					}
					$dataArray = array();
					while ($row = $re->fetch_assoc()) {
						$dataArray[] = $row;
					}

					$sql = sql_trip_summary_report_aat_edc($mysqli, $array);
					$re = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re->num_rows === 0) {
						throw new Exception('ไม่พบข้อมูล ' . __LINE__);
					}
					$dataArraySummaryTrip = array();
					while ($row = $re->fetch_assoc()) {
						$dataArraySummaryTrip[] = $row;
					}

					include('queryMonthlyReport/aat_edc/excel_summaryServiceAAT_EDC.php');
					include('queryMonthlyReport/aat_edc/excel_TripSummaryReportAAT_EDC.php');
				}
			} else {
				//echo('in3');
				$sql = sql_data_summary_service($mysqli, $array);
				$re = sqlError($mysqli, __LINE__, $sql, 1);
				if ($re->num_rows === 0) {
					throw new Exception('ไม่พบข้อมูล ' . __LINE__);
				}
				$dataArray = array();
				while ($row = $re->fetch_assoc()) {
					$dataArray[] = $row;
				}

				if ($billing_for == 'Customer') {
					if ($project_name == 'AAT MR') {
						$arr = array();

						$sql = managementfees($mysqli, $array);
						$re1 = sqlError($mysqli, __LINE__, $sql, 1);
						if ($re1->num_rows === 0) {
							throw new Exception('ไม่พบข้อมูล ' . __LINE__);
						}
						while ($row = $re1->fetch_array(MYSQLI_NUM)) {
							array_push($arr, $row);
						}

						include('queryMonthlyReport/aat_ftm_mr/excel_summaryServiceAAT.php');
					} else if ($project_name == 'FTM MR' || $project_name == 'SKD-FTM') {
						include('queryMonthlyReport/aat_ftm_mr/excel_summaryServiceFTM.php');
					}

					$sql = sql_trip_summary_report($mysqli, $array);
					$re = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re->num_rows === 0) {
						throw new Exception('ไม่พบข้อมูล ' . __LINE__);
					}
					$dataArraySummaryTrip = array();
					while ($row = $re->fetch_assoc()) {
						$dataArraySummaryTrip[] = $row;
					}
					include('queryMonthlyReport/aat_ftm_mr/excel_TripSummaryReport.php');
				} else if ($billing_for == 'Partner') {

					include('queryMonthlyReport/aat_ftm_mr/excel_summaryServiceCarrier.php');

					$sql = sql_trip_summary_report($mysqli, $array);
					$re = sqlError($mysqli, __LINE__, $sql, 1);
					if ($re->num_rows === 0) {
						throw new Exception('ไม่พบข้อมูล ' . __LINE__);
					}

					$dataArraySummaryTrip = array();
					while ($row = $re->fetch_assoc()) {
						$dataArraySummaryTrip[] = $row;
					}
					include('queryMonthlyReport/aat_ftm_mr/excel_TripSummaryReportCarrier.php');
				}
			}

			$mysqli->commit();
			closeDBT($mysqli, 1, $filename);
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else if ($type == 55) {
		$dataParams = array(
			'obj',
			'obj=>project_name:s:0:2',
		);

		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {
			// $array = array(
			// 	'project_name' => $project_name,
			// );

			// $sql = sql_trip_jda($mysqli, $array);
			// $re = sqlError($mysqli, __LINE__, $sql, 1);
			// if ($re->num_rows === 0) {
			// 	throw new Exception('ไม่พบข้อมูล ' . __LINE__);
			// }

			$dataArray = array(
				'project' => 'Non Bailment',
				'status' => 'Completed',
				'no' => 1,
				'carrier' => 'BTS',
				'order_status_check_862' => 'Check Load ID',
				'order_status' => 'Normal',
				'load_ID' => '4487059',
				'status_route_master' => 'One-way',
				'status_route_customer' => 'Round Trip',
				'status_route_partner' => 'One-way',
				'operation_date' => '02-01-2024',
				'pickup_date' => '02-01-2024',
				'delivery_date' => '02-01-2024',
				'delivery_shift' => 'Day',
				'route_number' => 'NA1F28',
				'distance_band' => '0 - 5',
				'trip_no' => 1,
				'route_refer' => 'AAT-MR',
				'release_order' => '862 Released',
				'truck_type_master' => '6W',
				'truck_type_TTV_Use' => '6W',
				'truck_license' => '70-7909',
				'driver_name' => 'ชื่อพขร.',
				'tel' => 'เบอร์โทรพขร.',
			);

			if ($project_name != 'EDC-FTM') {
				unset($dataArray['project']);
				include('queryMonthlyReport/template_hourly_report.php');
			} else {
				// var_dump($dataArray);
				// exit();
				include('queryMonthlyReport/template_hourly_report_edc.php');
			}



			$mysqli->commit();
			closeDBT($mysqli, 1, $filename);
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	} else if ($type == 56) {
		$dataParams = array(
			'obj',
			'obj=>start_date:s:0:2',
			'obj=>stop_date:s:0:2',
			'obj=>project_name:s:0:2',
			'obj=>billing_for:s:0:2',
			'obj=>carrier:s:0:0',
		);

		$chkPOST = checkParamsAndDelare($_POST, $dataParams, $mysqli);
		if (count($chkPOST) > 0) closeDBT($mysqli, 2, join('<br>', $chkPOST));

		$mysqli->autocommit(FALSE);
		try {
			$array = array(
				'start_date' => $start_date,
				'stop_date' => $stop_date,
				'project_name' => $project_name,
				'billing_for' => $billing_for,
				'carrier' => $carrier,
				'type' => $type
			);

			$sql = sql_trip_jda($mysqli, $array);
			$re = sqlError($mysqli, __LINE__, $sql, 1);
			if ($re->num_rows === 0) {
				throw new Exception('ไม่พบข้อมูล ' . __LINE__);
			}
			$dataArraySummaryTrip = array();
			while ($row = $re->fetch_assoc()) {
				$dataArraySummaryTrip[] = $row;
			}
			include('queryMonthlyReport/aat_ftm_mr/excel_TripJDA.php');

			$mysqli->commit();
			closeDBT($mysqli, 1, $filename);
		} catch (Exception $e) {
			$mysqli->rollback();
			closeDBT($mysqli, 2, $e->getMessage());
		}
	}
} else closeDBT($mysqli, 2, 'TYPE ERROR');

$mysqli->close();

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

function convertDate($value)
{
	$date = date_create($value);
	$result = date_format($date, "Y-m-d");

	return $result;
}

function replaceStr($value)
{
	$string = str_replace(' ', '', $value);
	$explode = explode("-", $string);
	$value = $explode[0] . " - " . $explode[1];

	return $value;
}


// Implement batchInsert and batchUpdate functions
function batchInsert($mysqli, $table, $data)
{
	$columns = implode(', ', array_keys($data[0]));
	$values = [];
	foreach ($data as $row) {
		$values[] = '(' . implode(', ', array_map([$mysqli, 'real_escape_string'], $row)) . ')';
	}
	$valuesStr = implode(', ', $values);
	$sql = "INSERT INTO $table ($columns) VALUES $valuesStr";
	sqlError($mysqli, __LINE__, $sql, 1);
}

function batchUpdate($mysqli, $table, $data)
{
	// Implement batch update logic
}

$mysqli->close();
exit();
