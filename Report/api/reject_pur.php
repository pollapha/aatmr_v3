<?php

header("Content-Type: application/json; charset=UTF-8");

$requestMethod = $_SERVER["REQUEST_METHOD"];

if ($requestMethod === 'POST') {
	$data = file_get_contents("php://input");
	$result = json_decode($data, true);

	if (!empty($result)) {
		if (!isset($result['user_code'])) {
			echo json_encode(['status' => 'error', 'code' => '400', 'message' => 'ไม่มีข้อมูลผู้ใช้งานที่ส่งมา']);
			exit();
		}
		if (!isset($result['pr_no'])) {
			echo json_encode(['status' => 'error', 'code' => '400', 'message' => 'ไม่มีข้อมูลเลขที่เอกสารที่ส่งมา']);
			exit();
		}

		require_once('../../php/connection.php');

		$user_name = $mysqli->real_escape_string($result['user_code']);
		$pr_no = $mysqli->real_escape_string($result['pr_no']);

		$sql = "SELECT user_id FROM tbl_user WHERE user_name = '$user_name';";
		$re = mysqli_query($mysqli, $sql);
		if ($re && $re->num_rows > 0) {
			$user_id = $re->fetch_assoc()['user_id'];
		} else {
			echo json_encode(['status' => 'error', 'code' => '400', 'message' => 'ไม่พบข้อมูลผู้ใช้งาน']);
			exit();
		}

		$sql = "SELECT pr_no FROM tbl_pr_number WHERE pr_no = '$pr_no';";
		$re = mysqli_query($mysqli, $sql);
		if ($re && $re->num_rows > 0) {
			$pr_no = $re->fetch_assoc()['pr_no'];
		} else {
			echo json_encode(['status' => 'error', 'code' => '400', 'message' => 'ไม่พบข้อมูลเลขที่เอกสาร']);
			exit();
		}

		$mysqli->autocommit(FALSE);
		try {
			$sql = "UPDATE tbl_204header_api 
					SET 
						pr_no = '',
						Update_Datetime = NOW(),
						update_By = $user_id
					WHERE pr_no = '$pr_no';";
			$re = mysqli_query($mysqli, $sql);

			if ($mysqli->affected_rows > 0) {
				//$mysqli->commit();
				echo json_encode(['status' => 'ok', 'code' => '200', 'message' => 'ยกเลิกเอกสารสำเร็จ']);
				exit();
			} 
			else {
				throw new Exception("Failed to update document.");
			}
		} catch (Exception $e) {
			$mysqli->rollback();
			echo json_encode(['status' => 'error', 'code' => '400', 'message' => 'เกิดข้อผิดพลาดในการยกเลิกเอกสาร']);
			exit();
		}
	} else {
		echo json_encode(['status' => 'error', 'code' => '400', 'message' => 'ไม่มีข้อมูลที่ส่งมา']);
		exit();
	}
} else {
	echo json_encode(['status' => 'error', 'code' => '400', 'message' => 'เฉพาะเมธอด POST เท่านั้นที่ยอมรับ']);
	exit();
}
