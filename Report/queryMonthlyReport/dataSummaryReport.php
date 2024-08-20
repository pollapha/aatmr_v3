<?php

function managementfees($mysqli, $array)
{
    $start_date = mysqli_real_escape_string($mysqli, $array['start_date']);
    $stop_date = mysqli_real_escape_string($mysqli, $array['stop_date']);
    $project_name = mysqli_real_escape_string($mysqli, $array['project_name']);


    $sql = "SELECT 
        ROW_NUMBER() OVER (ORDER BY t1.ID ASC) AS row_num,
	    description, unit, service_rate as unitRate, 1 as qty, service_rate as amount, service_rate as total,
        projectName
	FROM 
        tbl_management_fees t1
            inner join
        tbl_project_master t2 ON t1.ProjectID = t2.ID
    WHERE
        '$start_date' between start_date AND if(isnull(stop_date), curdate(), stop_date)
            AND projectName = '$project_name'
    ORDER BY t1.ID ASC;";
    return $sql;
}
//Customer
//aat


function sql_data_summary_service($mysqli, $array)
{

    $start_date = mysqli_real_escape_string($mysqli, $array['start_date']);
    $stop_date = mysqli_real_escape_string($mysqli, $array['stop_date']);
    $project_name = mysqli_real_escape_string($mysqli, $array['project_name']);
    $billing_for = mysqli_real_escape_string($mysqli, $array['billing_for']);
    $carrier = mysqli_real_escape_string($mysqli, $array['carrier']);
    $type = mysqli_real_escape_string($mysqli, $array['type']);

    $start = firstDay($start_date);

    $sqlwhere = '';
    $where = '';
    $where_special = '';
    $group_by = 'description_show';
    $tripType = 'tripType';
    $where_project = '';
    $where_carrier = "AND t1.truck_carrier = '$carrier'";
    if ($billing_for == 'Customer') {
        $where_special = "AND t1.Work_Type != 'Special'";
        if ($project_name == 'AAT MR') {
            $sqlwhere = "AND (t3.projectName = 'AAT MR' OR t3.projectName = 'Bailment' OR t3.projectName = 'PxP MR' OR t3.projectName = 'Service')";
            $group_by = 'description';
        } else if ($project_name == 'FTM MR') {
            $sqlwhere = "AND (t3.projectName = 'FTM MR')";
        } else if ($project_name == 'SKD-FTM') {
            $sqlwhere = "AND (t3.projectName = 'SKD')";
            $project_name = 'FTM MR';
        }
    } else if ($billing_for == 'Partner') {
        $group_by = 'description';
        $tripType = 'tripType_partner';
        if ($carrier == 'ALL Carrier') {
            $where_carrier = "";
        }
        if ($project_name == 'AAT MR') {
            $sqlwhere = "AND (t3.projectName = 'AAT MR' OR t3.projectName = 'Bailment' OR t3.projectName = 'PxP MR' OR t3.projectName = 'Service')
            $where_carrier";
        }
        // else if ($project_name == 'FTM MR') {
        //     $where_project = "WHERE project != 'AAT'";
        //     $sqlwhere = "AND (t3.projectName = 'FTM MR' OR t3.projectName = 'SKD' OR t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8')
        //     $where_carrier";
        // } 
        else if ($project_name == 'FTM MR') {
            $where_project = "WHERE project != 'AAT'";
            $sqlwhere = "AND (t3.projectName = 'FTM MR')
            $where_carrier";
        } else if ($project_name == 'SKD-FTM') {
            $where_project = "WHERE project != 'AAT'";
            $sqlwhere = "AND (t3.projectName = 'SKD')
            $where_carrier";
        } else if ($project_name == 'EDC-FTM') {
            $where_project = "WHERE project != 'AAT'";
            $sqlwhere = "AND (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8')
            $where_carrier";
        }
        $project_name = '';
    }

    $sql = "WITH FindRoute AS (
        SELECT 
            t2.projectID, t3.projectName, t1.Route, t1.Load_ID, 
            t1.truckLicense, t1.truck_carrier, t1.truckType, t1.Work_Type, t1.distanceBand, t1.$tripType AS tripType,
            CASE
				WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) = 'F' THEN 'FTM'
				WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) != 'F' THEN 'AAT'
				ELSE ''
			END AS project
        FROM 
            tbl_204header_api t1
                LEFT JOIN tbl_route_master_header t2 ON t1.Route = t2.routeName
                LEFT JOIN tbl_project_master t3 ON t2.projectID = t3.ID
        WHERE 
            t1.operation_date BETWEEN '$start_date' AND '$stop_date'
                AND t1.Status = 'COMPLETED'
                AND t1.distanceBand IS NOT NULL
                AND t1.truck_carrier != ''
                $sqlwhere
        GROUP BY t1.Load_ID
    )
    , RouteCounts AS (
        SELECT
            ProjectName,
            Route,
            Work_Type, 
            COUNT(*) AS RouteCount, 
            distanceBand, truckType, tripType
        FROM FindRoute
        $where_project
        GROUP BY Route, truckType, distanceBand, Work_Type, tripType
    )
    , CombinationCounts AS (
        SELECT
            projectName,
            truckType,
            distanceBand,
            Work_Type, 
            tripType,
            SUM(RouteCount) AS TotalRouteCount
        FROM RouteCounts
        GROUP BY truckType, distanceBand, tripType, Work_Type
    )
    , FinalQuery AS (
    SELECT
            c.*,
            tq.unitRate
        FROM CombinationCounts c
        LEFT JOIN tbl_trip_quotation tq ON c.Work_Type = tq.Work_Type 
            AND c.truckType = tq.truckType
            AND c.tripType = tq.tripType
            AND CONCAT(SUBSTRING_INDEX(c.distanceBand, '-', 1),' - ',
            SUBSTRING_INDEX(SUBSTRING_INDEX(c.distanceBand, '-', 2), '-', -1)) = tq.distanceBand
        WHERE EXTRACT(YEAR_MONTH FROM tq.start_date) = EXTRACT(YEAR_MONTH FROM '$start')
            AND tq.billing_for = '$billing_for'
            AND tq.project = '$project_name'
        GROUP BY truckType, distanceBand, tripType, Work_Type )
    , ConvertTripType AS (
    SELECT
        t2.ProjectName,
        t1.Work_Type,
        t1.tripType,
        t1.truckType,
        t1.distanceBand,
        t1.unitRate,
        CAST(COALESCE(t2.TotalRouteCount, 0) as UNSIGNED) AS qty
    FROM 
        tbl_trip_quotation t1
            LEFT JOIN FinalQuery t2 ON t1.truckType = t2.truckType
                AND t1.Work_Type = t2.Work_Type AND t1.tripType = t2.tripType
                AND t1.distanceBand = CONCAT(SUBSTRING_INDEX(t2.distanceBand, '-', 1),' - ',
                SUBSTRING_INDEX(SUBSTRING_INDEX(t2.distanceBand, '-', 2), '-', -1))
        WHERE 
            EXTRACT(YEAR_MONTH FROM t1.start_date) = EXTRACT(YEAR_MONTH FROM '$start')
                AND t1.billing_for = '$billing_for' 
                AND t1.project = '$project_name'
                $where_special
                )
    , CreateDescription AS (
    SELECT 
        CASE
            WHEN t1.Work_Type = 'Empty Package' THEN 'Empty Package Return Service'
            WHEN t1.Work_Type = 'Normal' AND t1.tripType = '1WAY' THEN 'Normal One - way Service'
            WHEN t1.Work_Type = 'Normal' AND t1.tripType = 'ROUND' THEN 'Normal Round Trip Service'
            WHEN (t1.Work_Type = 'Blowout' OR t1.Work_Type = 'Additional') AND t1.tripType = '1WAY' THEN 'Extra One - way Service'
            WHEN (t1.Work_Type = 'Blowout' OR t1.Work_Type = 'Additional') AND t1.tripType = 'ROUND' THEN 'Extra Round Trip Service'
            WHEN t1.Work_Type = 'Special' AND t1.tripType = '1WAY' THEN 'Special One - way Service'
            WHEN t1.Work_Type = 'Special' AND t1.tripType = 'ROUND' THEN 'Special Round Trip Service'
            ELSE CONCAT(t1.Work_Type, ' - ', t1.tripType, ' Service')
        END AS description_show,
        if(t1.Work_Type = 'Empty Package','Empty Package - Return Service',
        CONCAT(t1.Work_Type, ' - ', t1.tripType, ' Service')) AS description,
        Work_Type, tripType, truckType, distanceBand, unitRate, qty 
    FROM ConvertTripType t1 )
    , final_Q AS (
    SELECT 
        substring_index(description,' ',1) AS work_type, description_show, description, truckType, distanceBand, 'THB/Trip' as unit, unitRate, SUM(qty) AS qty 
    FROM CreateDescription
    GROUP BY $group_by, truckType, distanceBand)
    SELECT *, CAST(unitRate*qty AS unsigned) AS amount, CAST(unitRate*qty AS unsigned) AS total
    FROM final_Q $where;";
    // exit($sql);
    return $sql;
}


function sql_trip_summary_report($mysqli, $array)
{

    $start_date = mysqli_real_escape_string($mysqli, $array['start_date']);
    $stop_date = mysqli_real_escape_string($mysqli, $array['stop_date']);
    $project_name = mysqli_real_escape_string($mysqli, $array['project_name']);
    $billing_for = mysqli_real_escape_string($mysqli, $array['billing_for']);
    $carrier = mysqli_real_escape_string($mysqli, $array['carrier']);
    $type = mysqli_real_escape_string($mysqli, $array['type']);

    $start = firstDay($start_date);

    $sqlwhere = '';
    $tripType = 'tripType';
    $where_special = "";
    $where_carrier = "AND t1.truck_carrier = '$carrier'";
    $where_project = "";
    if ($billing_for == 'Customer') {
        $where_special = "WHERE Work_Type != 'Special'";
        if ($type == 6 || $type == 54) {
            $where = "";
        } else {
            $where = "AND t1.invoice_no = ''";
        }

        if ($project_name == 'AAT MR') {
            $sqlwhere = "AND (t3.projectName = 'AAT MR' OR t3.projectName = 'Bailment' OR t3.projectName = 'PxP MR' OR t3.projectName = 'Service') $where";
        } else if ($project_name == 'FTM MR') {
            $sqlwhere = "AND (t3.projectName = 'FTM MR') $where";
        } else if ($project_name == 'SKD-FTM') {
            $sqlwhere = "AND (t3.projectName = 'SKD')";
            $project_name = 'FTM MR';
        }
    } else if ($billing_for == 'Partner') {
        $tripType = 'tripType_partner';
        if ($type == 6 || $type == 54) {
            $where = "";
        } else {
            $where = "AND t1.pr_no = ''";
        }

        if ($carrier == 'ALL Carrier') {
            $where_carrier = "";
        }

        if ($project_name == 'AAT MR') {
            $sqlwhere = "AND (t3.projectName = 'AAT MR' OR t3.projectName = 'Bailment' OR t3.projectName = 'PxP MR' OR t3.projectName = 'Service')
            $where_carrier $where";
        }
        // elseif ($project_name == 'FTM MR') {
        //     $where_project = "WHERE project != 'AAT'";
        //     $sqlwhere = "AND (t3.projectName = 'FTM MR' OR t3.projectName = 'SKD' OR t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8')AND t1.truck_carrier = '$carrier' $where";
        // }
        elseif ($project_name == 'FTM MR') {
            $where_project = "WHERE project != 'AAT'";
            $sqlwhere = "AND (t3.projectName = 'FTM MR')AND t1.truck_carrier = '$carrier' $where";
        } elseif ($project_name == 'SKD-FTM') {
            $where_project = "WHERE project != 'AAT'";
            $sqlwhere = "AND (t3.projectName = 'SKD')AND t1.truck_carrier = '$carrier' $where";
        } elseif ($project_name == 'EDC-FTM') {
            $where_project = "WHERE project != 'AAT'";
            $sqlwhere = "AND (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8')AND t1.truck_carrier = '$carrier' $where";
        }
        $project_name = '';
    }

    $sql = "WITH FindRoute AS (
        SELECT 
                t2.projectID, t3.projectName, 
                t1.operation_date, DATE(t1.Start_Datetime) AS Start_Datetime, DATE(t1.End_Datetime) AS End_Datetime,
                t1.Load_ID, t1.Route, CONCAT(t1.Route,t1.Load_ID) as Internal_Tracking,
                t1.Work_Type, t1.truckType, t1.distanceBand, t1.truck_carrier,
                t4.unitRate,
                t1.pr_no,
                t1.invoice_no,
                t1.$tripType AS tripType,
                CASE
                    WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) = 'F' THEN 'FTM'
                    WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) != 'F' THEN 'AAT'
                    ELSE ''
                END AS project
            FROM 
                tbl_204header_api t1
                    LEFT JOIN tbl_route_master_header t2 ON t1.Route = t2.routeName
                    LEFT JOIN tbl_project_master t3 ON t2.projectID = t3.ID
                    INNER JOIN tbl_trip_quotation t4 ON t1.truckType = t4.truckType AND t1.distanceBand = t4.distanceBand 
                    AND t1.Work_Type = t4.Work_Type AND t1.$tripType = t4.tripType
                    AND t4.start_date = '$start'
                    AND t4.billing_for = '$billing_for'
                    AND t4.project = '$project_name'
            WHERE 
                t1.operation_date BETWEEN '$start_date' AND '$stop_date'
                    AND t1.Status = 'COMPLETED'
                    AND t1.distanceBand IS NOT NULL
                    AND t1.truck_carrier != ''
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
        FindRoute
    $where_special $where_project";
    // exit($sql);
    return $sql;
}


function sql_data_pr($mysqli, $array)
{
    $start_date = mysqli_real_escape_string($mysqli, $array['start_date']);
    $stop_date = mysqli_real_escape_string($mysqli, $array['stop_date']);
    $project_name = mysqli_real_escape_string($mysqli, $array['project_name']);
    $billing_for = mysqli_real_escape_string($mysqli, $array['billing_for']);
    $carrier = mysqli_real_escape_string($mysqli, $array['carrier']);
    $type = mysqli_real_escape_string($mysqli, $array['type']);

    $pr_no = mysqli_real_escape_string($mysqli, $array['pr_no']);
    $invoice_no = mysqli_real_escape_string($mysqli, $array['invoice_no']);

    $start = firstDay($start_date);

    $sqlwhere = '';
    $where_project = "";
    $where_carrier = "AND t1.truck_carrier = '$carrier'";
    if ($billing_for == 'Customer') {
    } else if ($billing_for == 'Partner') {
        if ($carrier == 'ALL Carrier') {
            $where_carrier = "";
        }
        if ($project_name == 'AAT MR') {
            $where_project = "WHERE project != 'FTM'";
            $sqlwhere = "AND (t3.projectName = 'AAT MR' OR t3.projectName = 'Bailment' OR t3.projectName = 'PxP MR' OR t3.projectName = 'Service')
            $where_carrier AND t1.pr_no = ''";
        }
        // elseif ($project_name == 'FTM MR') {
        //     $where_project = "WHERE project != 'AAT'";
        //     $sqlwhere = "AND (t3.projectName = 'FTM MR' OR t3.projectName = 'SKD' OR t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') $where_carrier AND t1.pr_no = ''";
        // }
        elseif ($project_name == 'FTM MR') {
            $where_project = "WHERE project != 'AAT'";
            $sqlwhere = "AND (t3.projectName = 'FTM MR') $where_carrier AND t1.pr_no = ''";
        } elseif ($project_name == 'SKD-FTM') {
            $where_project = "WHERE project != 'AAT'";
            $sqlwhere = "AND (t3.projectName = 'SKD') $where_carrier AND t1.pr_no = ''";
        } elseif ($project_name == 'EDC-FTM') {
            $where_project = "WHERE project != 'AAT'";
            $sqlwhere = "AND (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') $where_carrier AND t1.pr_no = ''";
        } elseif ($project_name == 'AAT EDC') {
            $where_project = "WHERE project != 'FTM'";
            $sqlwhere = "AND (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') $where_carrier AND t1.pr_no = ''";
        }
        $project_name = '';
    }

    $sql = "WITH FindRoute AS (
        SELECT 
            t2.projectID,
            CASE
            WHEN t3.projectName = 'AAT MR' OR t3.projectName = 'Bailment' OR t3.projectName = 'PxP MR' OR t3.projectName = 'Service' THEN 'AAT-MR'
            WHEN t3.projectName = 'FTM MR' OR t3.projectName = 'SKD' THEN 'FTM-MR'
            WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) = 'F' THEN 'FTM-MR'
            WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) != 'F' THEN 'AAT-EDC'
            ELSE t3.projectName
            END AS projectName,
            t1.Route, t1.Load_ID, t1.truckType, 
            t1.Work_Type, t1.truckLicense, t1.truck_carrier,
            t1.tripType_partner as tripType,
            t1.distanceBand,
            CASE
                WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) = 'F' THEN 'FTM'
                WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) != 'F' THEN 'AAT'
                ELSE ''
            END AS project
        FROM 
            tbl_204header_api t1
                LEFT JOIN tbl_route_master_header t2 ON t1.Route = t2.routeName
                LEFT JOIN tbl_project_master t3 ON t2.projectID = t3.ID
        WHERE
            t1.operation_date BETWEEN '$start_date' AND '$stop_date'
                AND t1.Status = 'COMPLETED'
                AND t1.distanceBand IS NOT NULL
                AND t1.truck_carrier != ''
                $sqlwhere
        GROUP BY t1.Load_ID )
        ,CombinationCounts AS (
                SELECT
                    ProjectName,
                    truck_carrier,
                    Work_Type, 
                    COUNT(*) AS RouteCount, 
                    distanceBand, truckType, tripType
                FROM FindRoute
                $where_project
                GROUP BY ProjectName, truck_carrier, truckType, distanceBand, Work_Type, tripType )
        ,FinalQuery AS (
                SELECT
                    c.*,
                    tq.unitRate
                FROM CombinationCounts c
                    LEFT JOIN tbl_trip_quotation tq ON c.Work_Type = tq.Work_Type
					AND c.truckType = tq.truckType
					AND c.tripType = tq.tripType
					AND CONCAT(SUBSTRING_INDEX(c.distanceBand, '-', 1),' - ',
					SUBSTRING_INDEX(SUBSTRING_INDEX(c.distanceBand, '-', 2), '-', -1)) = tq.distanceBand
                WHERE EXTRACT(YEAR_MONTH FROM tq.start_date) = EXTRACT(YEAR_MONTH FROM '$start')
                    AND tq.billing_for = '$billing_for'
                    AND tq.project = '$project_name'
                GROUP BY ProjectName, truck_carrier, truckType, distanceBand, Work_Type, tripType)
        , a AS (
            SELECT t2.ProjectName, t1.Work_Type, t1.tripType,
                CASE
                WHEN t1.Work_Type = 'Empty Package' THEN CONCAT('Empty Package Return Service', ' ',t1.truckType, ' ',t1.distanceBand)
                WHEN t1.Work_Type = 'Normal' AND t1.tripType = '1WAY' THEN CONCAT(t1.Work_Type, ' ', 'One - way Service ',t1.truckType, ' ',t1.distanceBand)
                WHEN t1.Work_Type = 'Normal' AND t1.tripType = 'ROUND' THEN CONCAT(t1.Work_Type, ' ', 'Round Trip Service ',t1.truckType, ' ',t1.distanceBand)
                WHEN (t1.Work_Type = 'Blowout' OR t1.Work_Type = 'Additional') AND t1.tripType = '1WAY' THEN CONCAT('Extra', ' ', 'One - way Service ',t1.truckType, ' ',t1.distanceBand)
                WHEN (t1.Work_Type = 'Blowout' OR t1.Work_Type = 'Additional') AND t1.tripType = 'ROUND' THEN CONCAT('Extra', ' ', 'Round Trip Service ',t1.truckType, ' ',t1.distanceBand)
                WHEN t1.Work_Type = 'Special' AND t1.tripType = '1WAY' THEN CONCAT(t1.Work_Type, ' ', 'One - way Service ',t1.truckType, ' ',t1.distanceBand)
                WHEN t1.Work_Type = 'Special' AND t1.tripType = 'ROUND' THEN CONCAT(t1.Work_Type, ' ', 'Round Trip Service ',t1.truckType, ' ',t1.distanceBand)
                ELSE CONCAT(t1.Work_Type, ' ', t1.tripType,' Service ',t1.truckType, ' ',t1.distanceBand)
                END AS description,
                    t1.truckType,t1.distanceBand,t1.unitRate,
                    CAST(COALESCE(t2.RouteCount, 0) as UNSIGNED) AS qty
                    FROM tbl_trip_quotation t1
                    LEFT JOIN FinalQuery t2 ON t1.truckType = t2.truckType 
                AND t1.Work_Type = t2.Work_Type AND t1.tripType = t2.tripType
                AND t1.distanceBand = CONCAT(SUBSTRING_INDEX(t2.distanceBand, '-', 1),' - ',
                SUBSTRING_INDEX(SUBSTRING_INDEX(t2.distanceBand, '-', 2), '-', -1))
                    WHERE EXTRACT(YEAR_MONTH FROM t1.start_date) = EXTRACT(YEAR_MONTH FROM '$start')  
                    AND t1.billing_for = '$billing_for' AND t1.project = '$project_name'
                    order by ProjectName, t1.ID)
            , b AS (
            SELECT Work_Type, tripType, ProjectName AS project,
                description AS trip_detail, 
                SUM(qty) AS quantity, 
                'Trip' AS unit, 
                unitRate AS unit_price
            FROM a WHERE qty > 0 GROUP BY ProjectName, description, Work_Type )
            SELECT project, trip_detail, quantity, unit, unit_price, (unit_price*quantity) AS amount 
            FROM b order by project, Work_Type, tripType
            -- trip_detail
            ;";
    // exit($sql);
    return $sql;
}

function sql_data_pr_find_pr_no($mysqli, $array)
{

    $billing_for = mysqli_real_escape_string($mysqli, $array['billing_for']);
    $pr_no = mysqli_real_escape_string($mysqli, $array['pr_no']);

    $sql = "SELECT pr_no, issue_date, summary, remarks, start_date, stop_date, billing_for, project_name, carrier FROM tbl_pr_number WHERE pr_no = '$pr_no';";
    $re1 = sqlError($mysqli, __LINE__, $sql, 1);
    $header = jsonRow($re1, true, 0);
    if ($re1->num_rows == 0) {
        throw new Exception('ไม่พบข้อมูล ' . __LINE__);
    }

    $body = [];

    if (count($header) > 0) {


        $sql = "WITH FindRoute AS (
        SELECT
			DATE_ADD(t1.operation_date, INTERVAL -DAY(t1.operation_date)+1 DAY) as operation_date,
            t2.projectID,
            CASE
            WHEN t3.projectName = 'AAT MR' OR t3.projectName = 'Bailment' OR t3.projectName = 'PxP MR' OR t3.projectName = 'Service' THEN 'AAT-MR'
            WHEN t3.projectName = 'FTM MR' OR t3.projectName = 'SKD' THEN 'FTM-MR'
            WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) = 'F' THEN 'FTM-MR'
            WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) != 'F' THEN 'AAT-EDC'
            ELSE t3.projectName
            END AS projectName,
            t1.Route, t1.Load_ID, t1.truckType, 
            t1.Work_Type, t1.truckLicense, t1.truck_carrier,
            t1.tripType_partner as tripType,
            t1.distanceBand,
            CASE
                WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) = 'F' THEN 'FTM'
                WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) != 'F' THEN 'AAT'
                ELSE ''
            END AS project
        FROM 
            tbl_204header_api t1
                LEFT JOIN tbl_route_master_header t2 ON t1.Route = t2.routeName
                LEFT JOIN tbl_project_master t3 ON t2.projectID = t3.ID
        WHERE
            t1.pr_no = '$pr_no'
        GROUP BY t1.Load_ID)
        ,CombinationCounts AS (
                SELECT
					operation_date,
                    ProjectName,
                    truck_carrier,
                    Work_Type, 
                    COUNT(*) AS RouteCount, 
                    distanceBand, truckType, tripType
                FROM FindRoute
                
                GROUP BY ProjectName, truck_carrier, truckType, distanceBand, Work_Type, tripType )
        ,FinalQuery AS (
                SELECT
                    c.*,
                    tq.unitRate
                FROM CombinationCounts c
                    LEFT JOIN tbl_trip_quotation tq ON c.Work_Type = tq.Work_Type 
                    AND c.truckType = tq.truckType 
                    AND c.distanceBand = tq.distanceBand 
                    AND c.tripType = tq.tripType
                WHERE EXTRACT(YEAR_MONTH FROM tq.start_date) = EXTRACT(YEAR_MONTH FROM c.operation_date)
                    AND tq.billing_for = '$billing_for'
                    AND tq.project = ''
                GROUP BY ProjectName, truck_carrier, truckType, distanceBand, Work_Type, tripType)
        , a AS (
            SELECT t2.ProjectName, t1.Work_Type, t1.tripType,
                CASE
                WHEN t1.Work_Type = 'Empty Package' THEN CONCAT('Empty Package Return Service', ' ',t1.truckType, ' ',t1.distanceBand)
                WHEN t1.Work_Type = 'Normal' AND t1.tripType = '1WAY' THEN CONCAT(t1.Work_Type, ' ', 'One - way Service ',t1.truckType, ' ',t1.distanceBand)
                WHEN t1.Work_Type = 'Normal' AND t1.tripType = 'ROUND' THEN CONCAT(t1.Work_Type, ' ', 'Round Trip Service ',t1.truckType, ' ',t1.distanceBand)
                WHEN (t1.Work_Type = 'Blowout' OR t1.Work_Type = 'Additional') AND t1.tripType = '1WAY' THEN CONCAT('Extra', ' ', 'One - way Service ',t1.truckType, ' ',t1.distanceBand)
                WHEN (t1.Work_Type = 'Blowout' OR t1.Work_Type = 'Additional') AND t1.tripType = 'ROUND' THEN CONCAT('Extra', ' ', 'Round Trip Service ',t1.truckType, ' ',t1.distanceBand)
                WHEN t1.Work_Type = 'Special' AND t1.tripType = '1WAY' THEN CONCAT(t1.Work_Type, ' ', 'One - way Service ',t1.truckType, ' ',t1.distanceBand)
                WHEN t1.Work_Type = 'Special' AND t1.tripType = 'ROUND' THEN CONCAT(t1.Work_Type, ' ', 'Round Trip Service ',t1.truckType, ' ',t1.distanceBand)
                ELSE CONCAT(t1.Work_Type, ' ', t1.tripType,' Service ',t1.truckType, ' ',t1.distanceBand)
                END AS description,
                    t1.truckType,t1.distanceBand,t1.unitRate,
                    CAST(COALESCE(t2.RouteCount, 0) as UNSIGNED) AS qty
                    FROM tbl_trip_quotation t1
                    LEFT JOIN FinalQuery t2 ON t1.truckType = t2.truckType AND t1.distanceBand = t2.distanceBand 
                    AND t1.Work_Type = t2.Work_Type AND t1.tripType = t2.tripType
                    WHERE EXTRACT(YEAR_MONTH FROM t1.start_date) = EXTRACT(YEAR_MONTH FROM t2.operation_date)  
                    AND t1.billing_for = '$billing_for' AND t1.project = ''
                    order by ProjectName, t1.ID )
            , b AS (
            SELECT Work_Type, ProjectName AS project,
                description AS trip_detail, 
                SUM(qty) AS quantity, 
                'Trip' AS unit, 
                unitRate AS unit_price
            FROM a WHERE qty > 0 GROUP BY ProjectName, description, Work_Type )
            SELECT project, trip_detail, quantity, unit, unit_price, (unit_price*quantity) AS amount 
            FROM b ORDER BY project
            -- trip_detail
            ;";
        //exit($sql);

        $re1 = sqlError($mysqli, __LINE__, $sql, 1);
        $body = jsonRow($re1, true, 0);
    }

    $returnData = ['header' => $header, 'body' => $body];
    return $returnData;
}


function sql_trip_summary_report_by_no($mysqli, $array)
{
    $billing_for = mysqli_real_escape_string($mysqli, $array['billing_for']);
    $pr_no = mysqli_real_escape_string($mysqli, $array['pr_no']);
    $invoice_no = mysqli_real_escape_string($mysqli, $array['invoice_no']);

    $sqlwhere = '';
    $where = '';
    $tripType = 'tripType';
    if ($billing_for == 'Customer') {
        $sql = "SELECT 
                project
            FROM
                tbl_invoice_header
            WHERE
                invoice_no = '$invoice_no';";
        $re1 = sqlError($mysqli, __LINE__, $sql, 1);
        if ($re1->num_rows == 0) {
            throw new Exception('ไม่พบข้อมูล' . __LINE__);
        }
        while ($row = $re1->fetch_array(MYSQLI_ASSOC)) {
            $project = $row['project'];
        }
        $sqlwhere = "AND t1.invoice_no = '$invoice_no'";
        $where = "AND t2.project = '$project'";
        $sqlwhere = "t1.invoice_no = '$invoice_no'";
    } else if ($billing_for == 'Partner') {
        $sqlwhere = "t1.pr_no = '$pr_no'";
        $where = "AND t2.project = ''";
        $tripType = 'tripType_partner';
    }

    $sql = "WITH FindRoute AS (
            SELECT 
                t2.projectID, t3.projectName, 
                t1.operation_date, DATE(t1.Start_Datetime) AS Start_Datetime, DATE(t1.End_Datetime) AS End_Datetime,
                t1.Load_ID, t1.Route, CONCAT(t1.Route,t1.Load_ID) as Internal_Tracking,
                t1.Work_Type, t1.truckType, t1.distanceBand, t1.truck_carrier,
                t1.pr_no,
                t1.invoice_no,
                t1.$tripType AS tripType,
                DATE_SUB(DATE(t1.Start_Datetime), INTERVAL DAYOFMONTH(DATE(t1.Start_Datetime))-1 DAY) AS start_date
            FROM 
                tbl_204header_api t1
                    LEFT JOIN tbl_route_master_header t2 ON t1.Route = t2.routeName
                    LEFT JOIN tbl_project_master t3 ON t2.projectID = t3.ID
            WHERE 
                $sqlwhere
                    AND t1.Status = 'COMPLETED'
                    AND t1.distanceBand IS NOT NULL
                    -- AND t1.truck_carrier != ''
            GROUP BY t1.Load_ID)
            SELECT t1.pr_no, t1.invoice_no, t1.truck_carrier,
                t1.projectID, t1.projectName, t1.operation_date, t1.Start_Datetime, t1.End_Datetime, 
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
                    AND t1.start_date = t2.start_date
                    AND t2.billing_for = '$billing_for'
                    $where;";
    // exit($sql);
    return $sql;
}

//edc-ftm
function sql_data_summary_service_edc_ftm($mysqli, $array)
{

    $start_date = mysqli_real_escape_string($mysqli, $array['start_date']);
    $stop_date = mysqli_real_escape_string($mysqli, $array['stop_date']);
    $project_name = mysqli_real_escape_string($mysqli, $array['project_name']);
    $billing_for = mysqli_real_escape_string($mysqli, $array['billing_for']);
    $carrier = mysqli_real_escape_string($mysqli, $array['carrier']);

    $start = firstDay($start_date);

    $tripType = 'tripType';
    $where_special = "";
    $where_project = "WHERE project != 'AAT'";
    $sqlwhere = "AND (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8')";
    $project_name = 'AAT MR';

    if ($billing_for == 'Customer') {
        $where_special = "WHERE Work_Type != 'Special'";
        $where_carrier = "";
    } else {
        $project_name = '';
        $tripType = 'tripType_partner';
        if ($carrier == 'ALL Carrier') {
            $where_carrier = "";
        } else {
            $where_carrier = "AND t1.truck_carrier = '$carrier'";
        }
    }

    $sql = "WITH FindRoute AS (
        SELECT 
            t2.projectID, t1.Route, t1.Load_ID, t1.bailment,
            t1.truckLicense, t1.truck_carrier, t1.truckType, t1.Work_Type, t1.distanceBand, t1.$tripType as tripType,
            CASE
                WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) = 'F' THEN 'FTM'
                WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) != 'F' THEN 'AAT'
                ELSE ''
            END AS project,
            t3.projectName
        FROM 
            tbl_204header_api t1
                LEFT JOIN tbl_route_master_header t2 ON t1.Route = t2.routeName
                LEFT JOIN tbl_project_master t3 ON t2.projectID = t3.ID
        WHERE 
            t1.operation_date BETWEEN DATE('$start_date') AND DATE('$stop_date')
                AND t1.Status = 'COMPLETED'
                AND t1.truck_carrier != ''
                $sqlwhere
                $where_carrier
        GROUP BY t1.Load_ID
    )
	, RouteCounts AS (
        SELECT
            ProjectName,
            project,
            Route,
            bailment,
            Work_Type, 
            COUNT(*) AS RouteCount, 
            distanceBand, truckType, tripType
        FROM FindRoute
        $where_project
        GROUP BY Route, bailment, truckType, distanceBand, Work_Type, tripType )
   , CombinationCounts AS (
        SELECT
            projectName,
			bailment,
            truckType,
            distanceBand,
            Work_Type, 
            tripType,
            SUM(RouteCount) AS TotalRouteCount
        FROM RouteCounts
        GROUP BY  bailment, truckType, distanceBand, tripType, Work_Type )
    , FinalQuery AS (
        SELECT tq.bailment, tq.Work_Type, tq.tripType, tq.truckType, tq.distanceBand, tq.unitRate, CAST(COALESCE(c.TotalRouteCount, 0) as UNSIGNED) AS qty
	FROM 
    ( SELECT ID, 'Bailment' AS bailment, Work_Type, tripType, truckType, distanceBand, unitRate, 1 AS d
      FROM tbl_trip_quotation
      WHERE EXTRACT(YEAR_MONTH FROM start_date) = EXTRACT(YEAR_MONTH FROM '$start')
        AND billing_for = '$billing_for'
        AND project = '$project_name'
      UNION 
      SELECT ID, if(Work_Type = 'Empty Package', 'Bailment', 'Non Bailment') AS bailment, Work_Type, tripType, truckType, distanceBand, unitRate, 2
      FROM tbl_trip_quotation
      WHERE EXTRACT(YEAR_MONTH FROM start_date) = EXTRACT(YEAR_MONTH FROM '$start')
        AND billing_for = '$billing_for'
        AND project = '$project_name'
    ) AS tq LEFT JOIN CombinationCounts c ON c.Work_Type = tq.Work_Type 
        AND c.truckType = tq.truckType 
        AND c.distanceBand = tq.distanceBand 
        AND c.tripType = tq.tripType
        AND c.bailment = tq.bailment
    WHERE tq.truckType != '10W'
    GROUP BY tq.bailment, tq.truckType, tq.distanceBand, tq.tripType, tq.Work_Type)
    , CreateDescription AS (
    SELECT
        t1.bailment,
        CASE
            WHEN t1.Work_Type = 'Empty Package' THEN 'Empty Package Return Service'
            WHEN (t1.Work_Type = 'Normal' OR t1.Work_Type = 'Additional') AND t1.tripType = '1WAY' THEN 'Normal One - way Service'
            WHEN (t1.Work_Type = 'Normal' OR t1.Work_Type = 'Additional') AND t1.tripType = 'ROUND' THEN 'Normal Round Trip Service'
            WHEN t1.Work_Type = 'Blowout' AND t1.tripType = '1WAY' THEN 'Extra One - way Service'
            WHEN t1.Work_Type = 'Blowout' AND t1.tripType = 'ROUND' THEN 'Extra Round Trip Service'
            ELSE CONCAT(t1.Work_Type, ' - ', t1.tripType, ' Service')
        END AS description_show,
        if(t1.Work_Type = 'Empty Package','Empty Package - Return Service',
        CONCAT(t1.Work_Type, ' - ', t1.tripType, ' Service')) AS description,
        Work_Type, tripType, truckType, distanceBand, unitRate, qty
    FROM FinalQuery t1 $where_special)
    , final_Q AS (
    SELECT 
        bailment, substring_index(description,' ',1) AS work_type, description_show, description,
        truckType, distanceBand, 'THB/Trip' AS unit, unitRate,
        SUM(qty) AS qty 
        FROM CreateDescription
    GROUP BY description_show, bailment, truckType, distanceBand )
    SELECT *, CAST(unitRate*qty AS unsigned) AS amount, CAST(unitRate*qty AS unsigned) AS total
    FROM final_Q;";
    //exit($sql);
    return $sql;
}



function sql_trip_summary_report_edc_ftm($mysqli, $array)
{

    $start_date = mysqli_real_escape_string($mysqli, $array['start_date']);
    $stop_date = mysqli_real_escape_string($mysqli, $array['stop_date']);
    $project_name = mysqli_real_escape_string($mysqli, $array['project_name']);
    $billing_for = mysqli_real_escape_string($mysqli, $array['billing_for']);
    $carrier = mysqli_real_escape_string($mysqli, $array['carrier']);
    $type = mysqli_real_escape_string($mysqli, $array['type']);

    $start = firstDay($start_date);


    $tripType = 'tripType';
    $where_special = "";
    $where_project = "WHERE project != 'AAT'";
    $sqlwhere = "AND (t7.projectName = 'AAT EDC' OR t7.projectName = 'EDC8')";
    $project_name = 'AAT MR';
    if ($billing_for == 'Customer') {
        $where_special = "AND Work_Type != 'Special'";
        $where_carrier = "";
    } else {
        $project_name = '';
        $tripType = 'tripType_partner';
        if ($carrier == 'ALL Carrier') {
            $where_carrier = "";
        } else {
            $where_carrier = "AND t1.truck_carrier = '$carrier'";
        }
    }


    $sql = "WITH FindRoute AS (SELECT 
        DATE_FORMAT(t1.Start_Datetime,'%d-%m-%y') AS Start_Datetime, DATE_FORMAT(t1.End_Datetime,'%d-%m-%y') AS End_Datetime,
        'FTM' as common,
        t1.Load_ID, t1.Route, CONCAT('PUS',t1.Route,t1.Load_ID) as Internal_Tracking,
        t2.Supplier_Code as Pick_GSDB_Code,
        t4.name as Pick_GSDB_Name,
        t3.Supplier_Code as Des_GSDB_Code,
        t5.name as Des_GSDB_Name,
        t1.bailment,
        t1.Work_Type, t1.truckType, t1.$tripType AS tripType, t1.distanceBand, t8.unitRate,
        t1.truckLicense,
        t1.driverName,
        t1.truck_carrier,
        t1.phone,
        if(SUBSTRING(t1.Route, 3, 1) = 'F', 'FTM', 'AAT') AS project,
        t7.projectName
    FROM
        tbl_204header_api t1
            LEFT JOIN tbl_204body_api t2 ON t1.Load_ID = t2.Load_ID AND (t2.StopStatusEnumVal = 'SS_PICKEDUP' OR t2.StopStatusEnumVal = 'SS_PICKUP_PNDG') 
            LEFT JOIN tbl_204body_api t3 ON t1.Load_ID = t3.Load_ID AND (t3.StopStatusEnumVal = 'SS_DELIVERED' OR t3.StopStatusEnumVal = 'SS_DROP_PNDG')
            LEFT JOIN tbl_supplier t4 ON t2.Supplier_Code = t4.code
            LEFT JOIN tbl_supplier t5 ON t3.Supplier_Code = t5.code
            INNER JOIN tbl_route_master_header t6 ON t1.Route = t6.routeName
            INNER JOIN tbl_project_master t7 ON t6.projectID = t7.ID
            INNER JOIN tbl_trip_quotation t8 ON t1.truckType = t8.truckType AND t1.distanceBand = t8.distanceBand 
            AND t1.Work_Type = t8.Work_Type AND t1.tripType = t8.tripType
            AND t8.start_date = '$start'
            AND t8.billing_for = '$billing_for'
            AND t8.project = '$project_name'
    WHERE 
        t1.operation_date BETWEEN DATE('$start_date') AND DATE('$stop_date')
            AND t1.CurrentLoadOperationalStatusEnumVal = 'S_COMPLETED'
            AND t1.distanceBand IS NOT NULL
            $sqlwhere
            $where_carrier
    GROUP BY t1.Load_ID )
    SELECT Start_Datetime, End_Datetime, common, Load_ID, Route, Internal_Tracking, 
    Pick_GSDB_Code, Pick_GSDB_Name, Des_GSDB_Code, Des_GSDB_Name, 
    bailment, Work_Type, truckType, if(tripType = '1WAY', 'One way', 'Round Trip') as tripType, distanceBand, unitRate, 
    truckLicense, driverName, truck_carrier, phone, 1 AS Qty_Trip, (unitRate*1) AS total
    FROM 
    FindRoute
    $where_project $where_special";
    //exit($sql);
    return $sql;
}



//aat edc
function sql_data_summary_service_aat_edc($mysqli, $array)
{
    $start_date = mysqli_real_escape_string($mysqli, $array['start_date']);
    $stop_date = mysqli_real_escape_string($mysqli, $array['stop_date']);
    $project_name = mysqli_real_escape_string($mysqli, $array['project_name']);
    $billing_for = mysqli_real_escape_string($mysqli, $array['billing_for']);
    $carrier = mysqli_real_escape_string($mysqli, $array['carrier']);

    $start = firstDay($start_date);

    $tripType = 'tripType';
    $where_special = "";
    $where_project = "WHERE project != 'FTM'";
    $sqlwhere = "AND (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8')";
    if ($billing_for == 'Customer') {
        $where_special = "WHERE Work_Type != 'Special'";
        $where_carrier = "";
    } else {
        $project_name = '';
        $tripType = 'tripType_partner';
        if ($carrier == 'ALL Carrier') {
            $where_carrier = "";
        } else {
            $where_carrier = "AND t1.truck_carrier = '$carrier'";
        }
    }

    $sql = "WITH FindRoute AS (
        SELECT 
            t2.projectID, t1.Route, t1.Load_ID, t1.bailment, t1.truckLicense, t1.truck_carrier, t1.truckType, t1.Work_Type, t1.distanceBand, t1.$tripType as tripType,
            CASE
                WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) = 'F' THEN 'FTM'
                WHEN (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8') AND SUBSTRING(t1.Route, 3, 1) != 'F' THEN 'AAT'
                ELSE ''
            END AS project,
            t3.projectName
        FROM 
            tbl_204header_api t1
                LEFT JOIN tbl_route_master_header t2 ON t1.Route = t2.routeName
                LEFT JOIN tbl_project_master t3 ON t2.projectID = t3.ID
        WHERE 
            t1.operation_date BETWEEN DATE('$start_date') AND DATE('$stop_date') AND t1.Status = 'COMPLETED'
                -- AND t1.distanceBand IS NOT NULL
                $sqlwhere
                $where_carrier
        GROUP BY t1.Load_ID
    )
	, RouteCounts AS ( SELECT ProjectName, project, Route, bailment, Work_Type, COUNT(*) AS RouteCount, distanceBand, truckType, tripType FROM FindRoute $where_project GROUP BY Route, bailment, truckType, distanceBand, Work_Type, tripType )
   , CombinationCounts AS (
        SELECT  projectName, bailment,truckType,distanceBand,Work_Type, tripType,SUM(RouteCount) AS TotalRouteCount FROM RouteCounts
        GROUP BY  bailment, truckType, distanceBand, tripType, Work_Type )
    , FinalQuery AS ( SELECT ROW_NUMBER() OVER (Partition by bailment, work_type, distanceBand order by bailment, work_type, tripType, ID ) row_num, tq.bailment, tq.Work_Type, tq.tripType, tq.truckType, tq.distanceBand, tq.unitRate, 
    CAST(COALESCE(c.TotalRouteCount, 0) as UNSIGNED) AS qty
	FROM 
    ( SELECT ID, 'Bailment' AS bailment, Work_Type, tripType, truckType, distanceBand, unitRate, 1 AS d
      FROM tbl_trip_quotation
      WHERE EXTRACT(YEAR_MONTH FROM start_date) = EXTRACT(YEAR_MONTH FROM '$start')
        AND billing_for = '$billing_for'
        AND project = '$project_name'
		AND truckType != '10W'
      UNION 
      SELECT ID, if(Work_Type = 'Empty Package', 'Bailment', 'Non Bailment') AS bailment, Work_Type, tripType, truckType, distanceBand, unitRate, 2
      FROM tbl_trip_quotation
      WHERE EXTRACT(YEAR_MONTH FROM start_date) = EXTRACT(YEAR_MONTH FROM '$start')
        AND billing_for = '$billing_for'
        AND project = '$project_name'
		AND truckType != '10W'
    ) AS tq LEFT JOIN CombinationCounts c ON c.Work_Type = tq.Work_Type 
        AND c.truckType = tq.truckType 
        AND c.distanceBand = tq.distanceBand 
        AND c.tripType = tq.tripType
        AND c.bailment = tq.bailment
    GROUP BY tq.bailment, tq.truckType, tq.distanceBand, tq.tripType, tq.Work_Type
    order by bailment, work_type, tripType, ID )
    , CreateDescription AS (
    SELECT
        row_num, t1.bailment,
        CASE
            WHEN t1.Work_Type = 'Empty Package' THEN 'Empty Package Return Service'
            WHEN (t1.Work_Type = 'Normal' OR t1.Work_Type = 'Additional') AND t1.tripType = '1WAY' THEN 'Normal One - way Service'
            WHEN (t1.Work_Type = 'Normal' OR t1.Work_Type = 'Additional') AND t1.tripType = 'ROUND' THEN 'Normal Round Trip Service'
            WHEN t1.Work_Type = 'Blowout' AND t1.tripType = '1WAY' THEN 'Extra One - way Service'
            WHEN t1.Work_Type = 'Blowout' AND t1.tripType = 'ROUND' THEN 'Extra Round Trip Service'
            ELSE CONCAT(t1.Work_Type, ' - ', t1.tripType, ' Service')
        END AS description_show,
        if(t1.Work_Type = 'Empty Package','Empty Package - Return Service', CONCAT(t1.Work_Type, ' - ', t1.tripType, ' Service')) AS description, Work_Type, tripType, truckType, distanceBand, unitRate, qty
    FROM FinalQuery t1 $where_special)
    , final_Q AS (
    SELECT row_num, bailment, substring_index(description,' ',1) AS work_type, description_show, description, truckType, distanceBand, 'THB/Trip' AS unit, unitRate, SUM(qty) AS qty  FROM CreateDescription
    GROUP BY description_show, bailment, truckType, distanceBand )
    SELECT *, CAST(unitRate*qty AS unsigned) AS amount, CAST(unitRate*qty AS unsigned) AS total FROM final_Q;";
    // exit($sql);
    return $sql;
}

function sql_trip_summary_report_aat_edc($mysqli, $array)
{

    $start_date = mysqli_real_escape_string($mysqli, $array['start_date']);
    $stop_date = mysqli_real_escape_string($mysqli, $array['stop_date']);
    $project_name = mysqli_real_escape_string($mysqli, $array['project_name']);
    $billing_for = mysqli_real_escape_string($mysqli, $array['billing_for']);
    $carrier = mysqli_real_escape_string($mysqli, $array['carrier']);
    $type = mysqli_real_escape_string($mysqli, $array['type']);

    $start = firstDay($start_date);


    $tripType = 'tripType';
    $where_special = "";
    $where_project = "WHERE project != 'FTM'";
    $sqlwhere = "AND (t3.projectName = 'AAT EDC' OR t3.projectName = 'EDC8')";
    if ($billing_for == 'Customer') {
        $where_special = "AND Work_Type != 'Special'";
        $where_carrier = "";
    } else {
        $project_name = '';
        $tripType = 'tripType_partner';
        if ($carrier == 'ALL Carrier') {
            $where_carrier = "";
        } else {
            $where_carrier = "AND t1.truck_carrier = '$carrier'";
        }
    }



    $sql = "WITH FindRoute AS (
        SELECT 
                t2.projectID, t3.projectName, t1.bailment,
                t1.operation_date, DATE(t1.Start_Datetime) AS Start_Datetime, DATE(t1.End_Datetime) AS End_Datetime,
                t1.Load_ID, t1.Route, CONCAT(t1.Route,t1.Load_ID) as Internal_Tracking,
                t1.Work_Type, t1.truckType, t1.distanceBand, t1.truck_carrier,
                t4.unitRate,
                t1.pr_no,
                t1.invoice_no,
                t1.$tripType AS tripType,
                if(SUBSTRING(t1.Route, 3, 1) = 'F', 'FTM', 'AAT') AS project
            FROM
				tbl_204header_api t1
                    LEFT JOIN tbl_route_master_header t2 ON t1.Route = t2.routeName
                    LEFT JOIN tbl_project_master t3 ON t2.projectID = t3.ID
                    INNER JOIN tbl_trip_quotation t4 ON t1.truckType = t4.truckType AND t1.distanceBand = t4.distanceBand 
                    AND t1.Work_Type = t4.Work_Type AND t1.tripType = t4.tripType
                    -- AND t1.bailment = t4.bailment
                    AND t4.start_date = '$start'
                    AND t4.billing_for = '$billing_for'
                    AND t4.project = '$project_name'
            WHERE 
                t1.operation_date BETWEEN DATE('$start_date') AND DATE('$stop_date')
                    AND t1.Status = 'COMPLETED'
                    AND t1.distanceBand IS NOT NULL
                    $sqlwhere
                    $where_carrier
        GROUP BY t1.Load_ID )
    SELECT pr_no, invoice_no, truck_carrier,
        projectID, projectName, bailment, operation_date, Start_Datetime, End_Datetime, 
        Load_ID, Route, Internal_Tracking, Work_Type, truckType,
        if(tripType = '1WAY', 'One-way', 'Roud Trip') as tripType, 
        distanceBand,
        unitRate,
        1 AS Qty_Trip,
        (unitRate*1) AS total
    FROM 
        FindRoute
    $where_project
    $where_special";
    //exit($sql);
    return $sql;
}


function sql_data_pr_edc($mysqli, $array)
{
    $start_date = mysqli_real_escape_string($mysqli, $array['start_date']);
    $stop_date = mysqli_real_escape_string($mysqli, $array['stop_date']);
    $project_name = mysqli_real_escape_string($mysqli, $array['project_name']);
    $billing_for = mysqli_real_escape_string($mysqli, $array['billing_for']);
    $carrier = mysqli_real_escape_string($mysqli, $array['carrier']);
    $type = mysqli_real_escape_string($mysqli, $array['type']);

    $pr_no = mysqli_real_escape_string($mysqli, $array['pr_no']);
    $invoice_no = mysqli_real_escape_string($mysqli, $array['invoice_no']);

    $start = firstDay($start_date);

    if ($billing_for == 'Customer') {
        $explode = explode("_", $project_name);
        $project_name = $explode[0];
        $project = $explode[1];
        $tripType = 'tripType';
        $sqlwhere = "";
        $where = "WHERE project = '$project'";
    } else {
        $tripType = 'tripType_partner';
        if ($project_name == 'ALL AAT EDC') {
            $project_name = 'AAT EDC';
            $where = '';
            $where_carrier = "AND t1.truck_carrier = '$carrier'";
        } else {
            $explode = explode("_", $project_name);
            $project_name = $explode[0];
            $project = $explode[1];
            $where = "WHERE project = '$project'";
            $where_carrier = "AND t1.truck_carrier = '$carrier'";
        }
    }

    $sql = "WITH FindRoute AS (
        SELECT 
            t2.projectID,
            t3.projectName,
            t1.Route, t1.Load_ID, t1.truckType, t1.bailment,
            t1.Work_Type, t1.truckLicense, t1.truck_carrier,
            t1.$tripType AS tripType,
            if(SUBSTRING(t1.Route, 3, 1) = 'F', 'FTM', 'AAT') AS project,
            t1.distanceBand
        FROM 
            tbl_204header_api t1
                LEFT JOIN tbl_route_master_header t2 ON t1.Route = t2.routeName
                LEFT JOIN tbl_project_master t3 ON t2.projectID = t3.ID
        WHERE 
            t1.operation_date BETWEEN '$start_date' AND '$stop_date'
                AND t1.Status = 'COMPLETED'
                AND t1.distanceBand IS NOT NULL
                AND t1.truck_carrier != ''
                $sqlwhere
                $where_carrier
                AND t3.projectName = '$project_name'
        GROUP BY t1.Load_ID )
        , CombinationCounts AS (
                SELECT
                    ProjectName,
                    bailment,
                    truck_carrier,
                    Work_Type, 
                    COUNT(*) AS RouteCount, 
                    distanceBand, truckType, tripType
                FROM FindRoute
                $where
                GROUP BY ProjectName, bailment, truck_carrier, truckType, distanceBand, Work_Type, tripType )
        ,FinalQuery AS (
                SELECT
                    c.*,
                    tq.unitRate
                FROM CombinationCounts c
                    LEFT JOIN tbl_trip_quotation tq ON c.Work_Type = tq.Work_Type 
                    AND c.truckType = tq.truckType 
                    AND c.distanceBand = tq.distanceBand 
                    AND c.tripType = tq.tripType
					AND c.bailment = tq.bailment
                WHERE EXTRACT(YEAR_MONTH FROM tq.start_date) = EXTRACT(YEAR_MONTH FROM '$start')
                    AND tq.billing_for = '$billing_for'
                    AND tq.project = '$project_name'
                GROUP BY ProjectName, bailment, truck_carrier, truckType, distanceBand, Work_Type, tripType )
        , a AS (
            SELECT t2.ProjectName, t1.Work_Type, t1.tripType, t1.bailment,
                CASE
                WHEN t1.Work_Type = 'Empty Package' THEN CONCAT('Empty Package Return Service', ' ',t1.truckType, ' ',t1.distanceBand)
                WHEN t1.Work_Type = 'Normal' AND t1.tripType = '1WAY' THEN CONCAT(t1.Work_Type, ' ', 'One - way Service ',t1.truckType, ' ',t1.distanceBand)
                WHEN t1.Work_Type = 'Normal' AND t1.tripType = 'ROUND' THEN CONCAT(t1.Work_Type, ' ', 'Round Trip Service ',t1.truckType, ' ',t1.distanceBand)
                WHEN (t1.Work_Type = 'Blowout' OR t1.Work_Type = 'Additional') AND t1.tripType = '1WAY' THEN CONCAT('Extra', ' ', 'One - way Service ',t1.truckType, ' ',t1.distanceBand)
                WHEN (t1.Work_Type = 'Blowout' OR t1.Work_Type = 'Additional') AND t1.tripType = 'ROUND' THEN CONCAT('Extra', ' ', 'Round Trip Service ',t1.truckType, ' ',t1.distanceBand)
                ELSE CONCAT(t1.Work_Type, ' ', t1.tripType,' Service ',t1.truckType, ' ',t1.distanceBand)
                END AS description,
                    t1.truckType,t1.distanceBand,t1.unitRate,
                    CAST(COALESCE(t2.RouteCount, 0) as UNSIGNED) AS qty
                    FROM tbl_trip_quotation t1
                    LEFT JOIN FinalQuery t2 ON t1.truckType = t2.truckType AND t1.distanceBand = t2.distanceBand 
                    AND t1.Work_Type = t2.Work_Type AND t1.tripType = t2.tripType AND t1.bailment = t2.bailment
                    WHERE EXTRACT(YEAR_MONTH FROM t1.start_date) = EXTRACT(YEAR_MONTH FROM '$start')  
                    AND t1.billing_for = '$billing_for' AND t1.project = '$project_name'
                    order by ProjectName )
            , b AS (
            SELECT ProjectName AS project,
                concat(bailment, ' ', description) AS trip_detail, 
                SUM(qty) AS quantity, 
                'Trip' AS unit, 
                unitRate AS unit_price
            FROM a WHERE qty > 0 GROUP BY ProjectName, description, bailment )
            SELECT *, (unit_price*quantity) AS amount FROM b ORDER BY project, trip_detail;";
    // exit($sql);
    return $sql;
}




function sql_trip_jda($mysqli, $array)
{

    $start_date = mysqli_real_escape_string($mysqli, $array['start_date']);
    $stop_date = mysqli_real_escape_string($mysqli, $array['stop_date']);
    $project_name = mysqli_real_escape_string($mysqli, $array['project_name']);
    $billing_for = mysqli_real_escape_string($mysqli, $array['billing_for']);
    $carrier = mysqli_real_escape_string($mysqli, $array['carrier']);
    $type = mysqli_real_escape_string($mysqli, $array['type']);

    $start = firstDay($start_date);

    $sqlwhere = '';
    $tripType = 'tripType';
    $where_special = "";
    if ($billing_for == 'Customer') {
        $where_special = "WHERE Work_Type != 'Special'";
        if ($project_name == 'AAT MR') {
            $sqlwhere = "AND (t3.projectName = 'AAT MR' OR t3.projectName = 'Bailment' OR t3.projectName = 'PxP MR' OR t3.projectName = 'Service')";
        } else if ($project_name == 'FTM MR') {
            $sqlwhere = "AND t3.projectName = 'FTM MR'";
        }
    } else if ($billing_for == 'Partner') {
        $tripType = 'tripType_partner';
        if ($project_name == 'AAT MR') {
            $sqlwhere = "AND (t3.projectName = 'AAT MR' OR t3.projectName = 'Bailment' OR t3.projectName = 'PxP MR' OR t3.projectName = 'Service')
        AND t1.truck_carrier = '$carrier'";
        } elseif ($project_name == 'FTM MR') {
            $sqlwhere = "AND (t3.projectName = 'FTM MR')AND t1.truck_carrier = '$carrier'";
        } elseif ($project_name == 'ALL AAT-FTM MR') {
            $sqlwhere = "AND (t3.projectName = 'AAT MR' OR t3.projectName = 'Bailment' OR t3.projectName = 'PxP MR' OR t3.projectName = 'Service' OR t3.projectName = 'FTM MR') 
        AND t1.truck_carrier = '$carrier'";
        }
        $project_name = '';
    }

    $sql = "SELECT 
        t1.truck_carrier,
        t1.Work_Type,
        t1.Load_ID,
        t1.$tripType AS tripType,
        t1.operration_date AS operation_date,
        DATE(t1.Start_Datetime) AS Start_Datetime,
        DATE(t1.End_Datetime) AS End_Datetime,
        t1.Route,
        t2.distanceBand,
        t3.projectName,
        t1.truckType
    FROM
        tbl_204header_api t1
            LEFT JOIN
        tbl_route_master_header t2 ON t1.Route = t2.routeName
            LEFT JOIN
        tbl_project_master t3 ON t2.projectID = t3.ID
    WHERE
        t1.operration_date BETWEEN '$start_date' AND '$stop_date'
            AND t1.CurrentLoadOperationalStatusEnumVal = 'S_COMPLETED'
            AND t2.distanceBand IS NOT NULL
            $sqlwhere
    GROUP BY t1.Load_ID;";
    // exit($sql);
    return $sql;
}


//

function firstDay($start_date = '')
{
    $start_month = date_create($start_date);
    $month = date_format($start_month, "m");
    $start_year = date_create($start_date);
    $year = date_format($start_year, "Y");

    if (empty($month)) {
        $month = date('m');
    }
    if (empty($year)) {
        $year = date('Y');
    }
    $result = strtotime("{$year}-{$month}-01");
    return date('Y-m-d', $result);
}

function lastday($stop_date)
{
    $stop_month = date_create($stop_date);
    $month = date_format($stop_month, "m");
    $stop_year = date_create($stop_date);
    $year = date_format($stop_year, "Y");

    if (empty($month)) {
        $month = date('m');
    }
    if (empty($year)) {
        $year = date('Y');
    }
    $result = strtotime("{$year}-{$month}-01");
    $result = strtotime('-1 second', strtotime('+1 month', $result));
    return date('Y-m-d', $result);
}
