<?php
if(!ob_start("ob_gzhandler")) ob_start();
// header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
// header('Cache-Control: no-store, no-cache, must-revalidate');
// header('Cache-Control: post-check=0, pre-check=0', FALSE);
// header('Pragma: no-cache');
include('start.php');
session_start();
if(empty($_SESSION['xxxID']))
{
	header("Location:login.php");
}else
{
    include('php/connection.php');
    $cBy = $_SESSION['xxxID'];
    if ($result = $mysqli->query("SELECT t1.user_id,concat(t1.user_fName,' ',t1.user_lname)user_fName,t1.user_image,t1.user_permission,concat('{',group_concat(concat('\"',t3.menu_menuUse,'\"',':[',t2.role_viwe,',',t2.role_insert,',',t2.role_update,',',t2.role_del,']') separator ','),'}')role
    from tbl_user t1 left join tbl_rolemaster t2 on t1.user_permission=t2.role_name 
    left join tbl_menu t3 on t2.menu_id = t3.menu_id
    where t1.user_id=$cBy and t1.user_status = 1 group by t1.user_id;")) 
    { 
        if($result->num_rows > 0)
        {
            $data = $result->fetch_object();
            $_SESSION['xxxRole'] = json_decode($data->role);
        }
    }

    /*if(!apcu_exists($TTV_CACHE_OJBJECT_DATA_PAGE))
    {
        $result = $mysqli->query("SELECT concat('{',group_concat(concat('\"',t2.menu_menuUse,'\":[',t1.data,']',',\"',t2.menu_menuUse,'_1_\":\"',t2.main,'\"')
         order by t1.menu_group separator ','),'}') data from
        (select group_concat(concat('{\"value\":\"',menu_menuId,' ',menu_menuName,'\",\"id\":\"',menu_menuUse,'\",\"icon\":\"',menu_icon,'\",\"css\":\"',menu_css,'\"
          ,\"details\":\"',menu_details,'\"}')
        order by substring_index(menu_menuId,'.',-1)*1 separator ',')data,menu_group 
        from tbl_menu where menu_header=0 group by menu_group order by menu_group,substring_index(menu_menuId,'.',-1)*1) t1
        inner join
        (select menu_group,menu_menuUse,concat(menu_menuId,'. ',menu_menuName) main
        from tbl_menu where menu_header=1 order by substring_index(menu_menuId,'.',-1)*1) t2 on t1.menu_group=t2.menu_group");
        $checkPageDataObject = json_decode($result->fetch_object()->data);
         apcu_add($TTV_CACHE_OJBJECT_DATA_PAGE, $checkPageDataObject);
    }
    else $checkPageDataObject = apcu_fetch($TTV_CACHE_OJBJECT_DATA_PAGE);*/
    $result = $mysqli->query("SELECT concat('{',group_concat(concat('\"',t2.menu_menuUse,'\":[',t1.data,']',',\"',t2.menu_menuUse,'_1_\":\"',t2.main,'\"')
         order by t1.menu_group separator ','),'}') data from
        (select group_concat(concat('{\"value\":\"',menu_menuId,' ',menu_menuName,'\",\"id\":\"',menu_menuUse,'\",\"icon\":\"',menu_icon,'\",\"css\":\"',menu_css,'\"
          ,\"details\":\"',menu_details,'\"}')
        order by substring_index(menu_menuId,'.',-1)*1 separator ',')data,menu_group 
        from tbl_menu where menu_header=0 group by menu_group order by menu_group,substring_index(menu_menuId,'.',-1)*1) t1
        inner join
        (select menu_group,menu_menuUse,concat(menu_menuId,'. ',menu_menuName) main
        from tbl_menu where menu_header=1 order by substring_index(menu_menuId,'.',-1)*1) t2 on t1.menu_group=t2.menu_group");
        $checkPageDataObject = json_decode($result->fetch_object()->data);


}
$pageID = '1';
$role;
/*if(isset($_GET['url']))
{
    $url = explode('/',filter_var(rtrim($_GET['url'],'/'),FILTER_SANITIZE_URL));
    $menuName = ($url[0] == '') ? 'homePage' : $url[0];
}
else $menuName='homePage';*/

/*echo $menuName .' '.$_GET['url'];
$mysqli->close();
exit();

*/
echo preg_replace('/\s{2,}/', '','
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <link rel="shortcut icon" href="images/favicon.ico"> 
        <link rel="stylesheet" href="codebase/all.min.css" type="text/css" media="screen" charset="utf-8">
        <script src="codebase/all.min.js"></script>
        <script src="js/lodash.min.js"></script>      
        <script src="js/jquery.fileDownload.js"></script>  
        <script src="js/cleave.min.js"></script>  
        <script src="js/chart.umd@4.2.1.js"></script>
        <script src="js/chartjs-plugin-datalabels@2.2.0.min.js"></script>
        <link href="css/fullcalendar.min.css" rel="stylesheet" />
        <script src="js/dayjs.min.js"></script>
        <script src="js/fullcalendar.min.js"></script>
        <style>

            .toolbar_title{
                padding:10px 10px;line-height:10px;
                background:#3498db;color:#fff;
                 text-align: center;
            }
            .webix_table_checkbox{
              width:15px;
              height:15px;
              margin-top:1px;
            }

            .webix_cell.disabled{
                background-color:#eee;
            }

            .webix_table_checkbox .checkBoxDataTable{
                width:5px;
                height:5px;
                margin-top:1px;
              }
            
            .highlight-yellow
            {
                background-color:#F39C12;
                color:white;
            }

            .highlight-yellow span:only-child
            {
                color:white;
            }

            .highlight-blue
            {
                background-color:#3498db;
                color:white;
            }

            .highlight-blue span:only-child
            {
                color:white;
            }

            .highlight-red
            {
                background-color:#F64747;
                color:white;
            }

            .highlight-red span:only-child
            {
                color:white;
            }

            .highlight-gray
            {
                background-color:#6C7A89;
                color:white;
            }

            .highlight-gray span:only-child
            {
                color: white;
            }

            .highlight-bluelight
            {
                background-color:#D2E3EF;
                color:white;
            }

            .highlight-bluelight span:only-child
            {
                color:white;
            }

            .webix_cal_body .webix_cal_row 
            {
                clear:none;
            }

            .webix_hcell
            {
                font-size:10px;
            }
            .webix_column
            {
                font-size:10px;
            }

            .webix_inp_top_label
            {
                font-size:12px;
            }

            .webix_inp_label
            {
                font-size:12px;
                clear:none;
            }

            .webix_el_colorpicker input, .webix_el_combo input, .webix_el_datepicker input, .webix_el_search input, .webix_el_text input,
            .webix_el_box, .webix_el_select select, .webix_el_button button
            {
                font-size:12px;
            }

            .webix_list_item
            {
                font-size:12px;
            }

            .webix_view
            {
                font-family:"Bai Jamjuree",Tahoma;font-size:12px;cursor:default;overflow:hidden;border:0 solid #ddd;white-space:normal;-webkit-appearance:none
            }

            
        </style>
    </head>
    <body>
    <script>
    var systemDateFormat = (t) => {
        let format = "YYYY-MM-DD";
        if(!dayjs(t,format, true).isValid())
        {
            return "";
        }
        return dayjs(t).format("YYYY-MM-DD");
    };
    
    var thDateFormat = (t) => {
        let format = "DD-MM-YYYY";
        if(!dayjs(t,format, true).isValid())
        {
            return "";
        }
        return dayjs(t).format("DD-MM-YYYY");
    };

    var systemDateTimeFormat = (t) => {
        let format = "YYYY-MM-DD HH:mm:ss";
        if(!dayjs(t,format, true).isValid())
        {
            return "";
        }
        return dayjs(t).format("YYYY-MM-DD HH:mm:ss");
    };

    var thDateTimeFormat = (t) => {
        let format = "DD-MM-YYYY HH:mm:ss";
        if(!dayjs(t,format, true).isValid())
        {
            return "";
        }
        return dayjs(t).format(format);
    };

    var thMonthFormat = (t) => {
        let format = "YYYY-MM-DD";
        if(!dayjs(t,format, true).isValid())
        {
            return "";
        }
        return dayjs(t).format("MM-YYYY");
    };

    const datatableDateFormat = { format: thDateFormat, editFormat: systemDateFormat, editParse: systemDateFormat };

    const datatableDateTimeFormat = { format: thDateTimeFormat, editFormat: systemDateTimeFormat, editParse: systemDateTimeFormat };

    const datatableMonthFormat = { format: thMonthFormat, editFormat: systemDateTimeFormat, editParse: systemDateTimeFormat };
    webix.ui.datafilter.countTableTH = webix.extend({
        refresh:function(master, node, value)
        {          
            node.firstChild.innerHTML = master.count()+" รายการ";
        }
    }, webix.ui.datafilter.summColumn);
');
include('header.php');
echo  '</script><script src="main.js?project='.$TTV_PROJECT_NAME.'&v=26" charset="utf-8"></script></body></html>';
?>

         