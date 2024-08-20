<?php
if (!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
/*  if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName']) )
    {
        echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
        exit();
    }
 */
include('../php/connection.php');

if ($_REQUEST['type'] == 1) {
	$start_date = $_REQUEST['start_date'];
	$stop_date = $_REQUEST['stop_date'];
	$project = $_REQUEST['project'];

	$sqlwhere = '';
	$where_project = '';
	if ($project == 'AAT MR') {
		$where_project = "WHERE project != 'FTM'";
		$sqlwhere = "(t3.projectName = 'AAT MR' OR t3.projectName = 'Bailment' OR t3.projectName = 'PxP MR' OR t3.projectName = 'Service')";
	} elseif ($project == 'FTM MR') {
		$where_project = "WHERE project != 'AAT'";
		$sqlwhere = "(t3.projectName = 'FTM MR')";
	} elseif ($project == 'SKD-FTM') {
		$where_project = "WHERE project != 'AAT'";
		$sqlwhere = "(t3.projectName = 'SKD')";
	} elseif ($project == 'EDC-FTM') {
		$where_project = "WHERE project != 'AAT'";
		$sqlwhere = "(t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8')";
	} elseif ($project == 'AAT EDC') {
		$where_project = "WHERE project != 'FTM'";
		$sqlwhere = "(t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8')";
	} else {
		$sqlwhere = "(t3.projectName = '')";
	}

	$sql = "WITH a AS (
		SELECT 
				t1.Route,
				t3.projectName,
				t1.truck_carrier,
        CASE
			WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) = 'F' THEN 'FTM'
			WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) != 'F' THEN 'AAT'
			ELSE ''
		END AS project
		FROM 
			tbl_204header_api t1
				INNER JOIN
			tbl_route_master_header t2 on t1.Route = t2.routeName
				INNER JOIN
			tbl_project_master t3 on t2.projectID = t3.ID
		WHERE t1.operation_date between '$start_date' AND '$stop_date' 
			AND t1.truck_carrier != ''
			AND $sqlwhere
		ORDER BY t1.truck_carrier)
	SELECT distinct truck_carrier FROM a 
	$where_project 
	order by truck_carrier;";

	$result = $mysqli->query($sql);
	$arr = array();
	while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
		$truck_carrier = $row['truck_carrier'];
		array_push($arr, $truck_carrier);
	}
	if (count($arr) > 1) {
		array_push($arr, 'ALL Carrier');
	}
	ConvertArrayToString($arr);
	//exit($sql);
	//toArrayStringOne($mysqli->query($sql), 1);


} else if ($_REQUEST['type'] == 2) {
	$start_date = $_REQUEST['start_date'];
	$stop_date = $_REQUEST['stop_date'];
	$project = $_REQUEST['project'];

	if ($project != 'ALL AAT EDC') {
		$explode = explode("_", $project);
		$text1 = $explode[0];
		$sqlwhere = "t3.projectName = '$text1'";

		$project = $explode[1];

		$sql = "WITH a AS (
			SELECT 
					t1.truck_carrier,
					t1.Route,
					if(SUBSTRING(t1.Route, 3, 1) = 'F', 'FTM', 'AAT') AS project
				FROM 
					tbl_204header_api t1
						INNER JOIN
					tbl_route_master_header t2 on t1.Route = t2.routeName
						INNER JOIN
					tbl_project_master t3 on t2.projectID = t3.ID
				WHERE t1.operation_date between '$start_date' AND '$stop_date' 
					AND t1.truck_carrier != ''
					AND $sqlwhere
					)
			SELECT distinct a.truck_carrier from a where project = '$project' ORDER BY a.truck_carrier;";
		// exit($sql);
		//toArrayStringOne($mysqli->query($sql), 1);

		$result = $mysqli->query($sql);
		$arr = array();
		while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
			$truck_carrier = $row['truck_carrier'];
			array_push($arr, $truck_carrier);
		}
		if (count($arr) > 1) {
			array_push($arr, 'ALL Carrier');
		}
		ConvertArrayToString($arr);
	} elseif ($project == 'ALL AAT EDC') {
		$sqlwhere = "t3.projectName = 'AAT EDC'";

		$sql = "SELECT DISTINCT
			t1.truck_carrier
		FROM
			tbl_204header_api t1
				INNER JOIN
			tbl_route_master_header t2 ON t1.Route = t2.routeName
				INNER JOIN
			tbl_project_master t3 ON t2.projectID = t3.ID
		WHERE
			t1.operation_date BETWEEN '$start_date' AND '$stop_date'
				AND t1.truck_carrier != ''
				AND $sqlwhere
		ORDER BY t1.truck_carrier;";
		// exit($sql);
		//toArrayStringOne($mysqli->query($sql), 1);

		$result = $mysqli->query($sql);
		$arr = array();
		while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
			$truck_carrier = $row['truck_carrier'];
			array_push($arr, $truck_carrier);
		}
		if (count($arr) > 1) {
			array_push($arr, 'ALL Carrier');
		}
		ConvertArrayToString($arr);
	}
}

function ConvertArrayToString($arr)
{
	if (count($arr) > 0) {
		echo '[';
		$len = 1;

		echo '"';
		$row = $arr;
		echo join('","', $row);
		echo '"';
		for ($i = 1; $i < $len; $i++) {
			echo ',"';
			$row = $arr;
			echo join('","', $row);
			echo '"';
		}
		echo ']';
	}
}
$mysqli->close();
exit();
