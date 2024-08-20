<?php

$html = '<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="shortcut icon" href="../images/favicon.ico"> 
        <link rel="stylesheet" href="../codebase/all.min.css" type="text/css" media="screen" charset="utf-8">
        <script src="../codebase/all.min.js"></script>
        <script src="../main.js"></script>
    </head>
    <style>
    .highlight-yellow
    {
        background-color:#F39C12;
        color:white;
    }
    .highlight-blue
    {
        background-color:#3498db;
        color:white;
    }
    .highlight-red
    {
        background-color:#F64747;
        color:white;
    }
    .highlight-gray
    {
        background-color:#6C7A89;
        color:white;
    }
    .highlight-bluelight
    {
        background-color:#D2E3EF;
        color:white;
    }
    </style>
    <body>
    </body>
    <script>
    {XXX}
    var data = {DATA};
    webix.ready(function(){

        webix.ui(header_dailyReport().body);
        
        $$("dailyReport_showReportHide").hide();
        $$("dailyReport_showReportShow").show();
        $$("dailyReport_dateShow").setValue("{DATE}");
        $$("dailyReport_workTypeShow").setValue("{WORK_TYPE}");
        $$("dailyReport_workType").setValue("{WORK_TYPE_SELECT}");
        $$("dailyReport_dataT1").parse(data.data);
        $$("dailyReport_pie").parse(data.pie);
        $$("dailyReport_stackedBar").parse(data.stackedBar);
        $$("dailyReport_sumStatus").parse(data.sumStatus);
        
        
    });
    
    
    </script>
    </html>';
    include('../vendor/autoload.php');
    use \Curl\Curl;

    $date1 = $_REQUEST['date1'];
    $workType = $_REQUEST['workType'];
    $curl = new Curl();
    // $param = array('obj'=>array('date1' => '2018-08-24'));
    $param = array('obj'=>array('date1' => $date1,'workType'=>$workType));
    $curl->post('http://localhost/mazdamr/report/dailyReport_common.php',$param);

    

    if (!$curl->error) 
    {
        $html = preg_replace('/{DATA}/',$curl->response,$html);
    } else 
    {
        echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";    
    }
    
    $html =  preg_replace('/{DATE}/',$date1,$html);
    $html =  preg_replace('/{WORK_TYPE}/',$workType,$html);
    $html =  preg_replace('/{WORK_TYPE_SELECT}/',getWorkType($workType),$html);
    
    echo preg_replace('/{XXX}/',requireToVar('../report/dailyReport.js'),$html);
    function requireToVar($file){
        ob_start();
        require($file);
        return ob_get_clean();
    }    

function getWorkType($workType)
{   
	if($workType == 'normal' || $workType == 'shuttle_truck')
	{
		return $workType;
    } 
    
	if($workType == 'Normal Part')
	{
		return $workType = 'normal';
	}
	else if($workType == 'Shuttle Truck')
	{
		return $workType = 'shuttle_truck';
    }
	return '';
}	
?>
