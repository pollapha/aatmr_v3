<?php
if(!ob_start("ob_gzhandler")) ob_start();
header('Expires: Sun, 01 Jan 2014 00:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
include('../start.php');
session_start();
include('./../vendor/autoload.php');
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;
if(!isset($_SESSION['xxxID']) || !isset($_SESSION['xxxRole']) || !isset($_SESSION['xxxID']) || !isset($_SESSION['xxxFName'])  || !isset($_SESSION['xxxRole']->{'uploadOrderAAT'}) )
{
	echo "{ch:10,data:'เวลาการเชื่อมต่อหมด<br>คุณจำเป็นต้อง login ใหม่'}";
	exit();
}
else if($_SESSION['xxxRole']->{'uploadOrderAAT'}[0] == 0)
{
	echo "{ch:9,data:'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้'}";
	exit();
}

if(!isset($_REQUEST['type'])) {echo json_encode(array('ch'=>2,'data'=>'ข้อมูลไม่ถูกต้อง'));exit();}
$cBy = $_SESSION['xxxID'];
$fName = $_SESSION['xxxFName'];
$type  = intval($_REQUEST['type']);


include('../php/connection.php');
if($type<=10)//data
{
	if($type == 1)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>LOAD_ID'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
		$LOAD_ID = checkTXT($mysqli,$_POST['obj']['LOAD_ID']);
		if(strlen($LOAD_ID) == 0) closeDBT($mysqli,2,'LOAD ID ไม่สามารถเป็นค่าว่างได้');
		$sql = "SELECT ID,CD_PLANT,CD_SUPPLIER_SHP_FR,NO_PART_PREFIX,NO_PART_BASE,NO_PART_SUFFIX,DT_PGM_START,
		NO_PGM,LOAD_ID,CD_PICKUP_RTE_NEW,DT_SHIP,TM_SHIP,CD_DELIVRY_RTE_NEW,DT_DELIVERY,
		TM_DELIVERY,CD_DELIVERY_DOCK,QT_SHP_DEL,QT_CUM_SHP_DEL,
		QT_PKG,WT_PART,CD_COUNTRY,NA_COMP,CD_PLANT_DOCK_LOC,CD_RELEASE_ANAL
		from tbl_862order 
		where LOAD_ID='$LOAD_ID' order by LOAD_ID,CD_PICKUP_RTE_NEW,CD_SUPPLIER_SHP_FR";

		$re1 = sqlError($mysqli,__LINE__,$sql);
		if($re1->num_rows == 0) closeDBT($mysqli,2,'ไม่พบข้อมูลในระบบ');
		closeDBT($mysqli,1,jsonRow($re1,true,0));
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>10 && $type<=20)//insert
{
	if($_SESSION['xxxRole']->{'uploadOrderAAT'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 11)
	{

	}
	else if($type == 12)
	{

	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>20 && $type<=30)//update
{
	if($_SESSION['xxxRole']->{'uploadOrderAAT'}[2] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 21)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>30 && $type<=40)//delete
{
	if($_SESSION['xxxRole']->{'uploadOrderAAT'}[3] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 31)
	{
		$chkPOST = checkParams($_POST,array('obj','obj=>LOAD_ID','obj=>ID'));
		if(count($chkPOST) > 0) closeDBT($mysqli,2,join('<br>',$chkPOST));
		$LOAD_ID = checkTXT($mysqli,$_POST['obj']['LOAD_ID']);
		$ID = checkTXT($mysqli,$_POST['obj']['ID']);
		if(strlen($LOAD_ID) == 0) closeDBT($mysqli,2,'LOAD ID ไม่สามารถเป็นค่าว่างได้');
		if(strlen($ID) == 0) closeDBT($mysqli,2,'ID ไม่สามารถเป็นค่าว่างได้');


		$sql = "DELETE from tbl_862order where ID in($ID) and LOAD_ID='$LOAD_ID'";
		sqlError($mysqli,__LINE__,$sql);
		if($mysqli->affected_rows == 0) closeDBT($mysqli,2,'ไม่สามารถลบข้อมูลได้');

		$sql = "SELECT ID,CD_PLANT,CD_SUPPLIER_SHP_FR,NO_PART_PREFIX,NO_PART_BASE,NO_PART_SUFFIX,DT_PGM_START,
		NO_PGM,LOAD_ID,CD_PICKUP_RTE_NEW,DT_SHIP,TM_SHIP,CD_DELIVRY_RTE_NEW,DT_DELIVERY,
		TM_DELIVERY,CD_DELIVERY_DOCK,QT_SHP_DEL,QT_CUM_SHP_DEL,
		QT_PKG,WT_PART,CD_COUNTRY,NA_COMP,CD_PLANT_DOCK_LOC,CD_RELEASE_ANAL
		from tbl_862order 
		where LOAD_ID='$LOAD_ID' order by LOAD_ID,CD_PICKUP_RTE_NEW,CD_SUPPLIER_SHP_FR";

		$re1 = sqlError($mysqli,__LINE__,$sql);
		closeDBT($mysqli,1,jsonRow($re1,true,0));
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>40 && $type<=50)//save
{
	if($_SESSION['xxxRole']->{'uploadOrderAAT'}[1] == 0) closeDBT($mysqli,9,'คุณไม่ได้รับอุญาติให้ทำกิจกรรมนี้');
	if($type == 41)
	{
		
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else if($type>50 && $type<=60)//upload
{
    if($type==51)
    {
		if(!isset($_FILES["upload"])) {echo json_encode(array('status'=>'server','mms'=>'ไม่พบไฟล์ UPLOAD'));closeDB($mysqli);}
        $fileName = $_FILES["upload"]["name"];
        $tempName = $_FILES["upload"]["tmp_name"];

        $sql = "SELECT FileName from tbl_862order where FileName='$fileName' limit 1";
		$re1 = sqlError($mysqli,__LINE__,$sql,1,1);
		if($re1->num_rows>0) {echo json_encode(array('status'=>'server','mms'=>'ไฟล์นี้ถูก Upload แล้ว'));closeDB($mysqli);}

        if(move_uploaded_file($tempName,"../order_file/".$fileName))
        {
            $file_ext = pathinfo("../order_file/".$fileName, PATHINFO_EXTENSION);
			if($file_ext == 'xls')
            {
                $uriAr = explode('\\',__DIR__);
                $dir = join('\\',array_slice($uriAr, 0, count($uriAr)-1)).'\order_file\\';
                $saveFileName= explode('.',$fileName)[0].'.xlsx';
                $openFile =$dir.$fileName;
                $saveFile =$dir.$saveFileName;
                $output = shell_exec("node C:\\node\\excelConvert\\index.js \"$openFile\" \"$saveFile\"");
                $strFileName = "../order_file/".$saveFileName;
                
            }
            else
            {
                $strFileName = "../order_file/".$fileName;
			}
			
			$reader = ReaderFactory::create(Type::XLSX);
            $reader->setShouldPreserveEmptyRows(true);
			$reader->open($strFileName);
            $total = 0;
            $pages = 0;
            
			foreach ($reader->getSheetIterator() as $sheet) 
            {
				if($sheet->getIndex() == 0)
                {
					$countRow = 0;
					$sqlArray = array();
					foreach ($sheet->getRowIterator() as $row)
                    {
                        ++$countRow;
                        if($countRow === 1)
                        {
							$checkData = checkCol($row);
							$col = $checkData[1];
                            if(strlen($checkData[0])>0)
                            {
                                echo '{"status":"server","mms":"'.$checkData[0].'","sname":[]}';
                                closeDB($mysqli);
                                break;
                            }
						}
						else if($countRow > 1)
						{
							arraytrim($row,$mysqli);
							$ch = array();
							if (strlen($row[$col["LOAD ID"]]) == 0) $ch[] = 'LOAD ID ไม่ควรเป็นค่าว่าง';
							
							if(count($ch) > 0)
                            {                                    
                                echo '{"status":"server","mms":"'.join('<br>',$ch).'<br>บรรทัดที่'.($countRow).'","sname":[]}';
                                closeDB($mysqli);
                                break;
							}
							$partNo = $row[$col["NO_PART_PREFIX"]].$row[$col["NO_PART_BASE"]].$row[$col["NO_PART_SUFFIX"]];
							$dataAr = array(
								array('type'=>'string','CD_PLANT'=>$row[$col["CD_PLANT"]])
								,array('type'=>'string','CD_SUPPLIER_SHP_FR'=>$row[$col["CD_SUPPLIER_SHP_FR"]])
								,array('type'=>'string','NO_PART_PREFIX'=>$row[$col["NO_PART_PREFIX"]])
								,array('type'=>'string','NO_PART_BASE'=>$row[$col["NO_PART_BASE"]])
								,array('type'=>'string','NO_PART_SUFFIX'=>$row[$col["NO_PART_SUFFIX"]])
								,array('type'=>'DateTime','DT_PGM_START'=>$row[$col["DT_PGM_START"]])
								,array('type'=>'string','NO_PGM'=>$row[$col["NO_PGM"]])
								,array('type'=>'string','LOAD ID'=>$row[$col["LOAD ID"]])
								,array('type'=>'string','CD_PICKUP_RTE_NEW'=>$row[$col["CD_PICKUP_RTE_NEW"]])
								,array('type'=>'DateTime','DT_SHIP'=>$row[$col["DT_SHIP"]])
								,array('type'=>'string','TM_SHIP'=>str_replace('.',':',$row[$col["TM_SHIP"]]))
								,array('type'=>'string','CD_DELIVRY_RTE_NEW'=>$row[$col["CD_DELIVRY_RTE_NEW"]])
								,array('type'=>'DateTime','DT_DELIVERY'=>$row[$col["DT_DELIVERY"]])
								,array('type'=>'string','TM_DELIVERY'=>str_replace('.',':',$row[$col["TM_DELIVERY"]]))
								,array('type'=>'string','CD_DELIVERY_DOCK'=>$row[$col["CD_DELIVERY_DOCK"]])
								,array('type'=>'string','QT_SHP_DEL'=>$row[$col["QT_SHP_DEL"]])
								,array('type'=>'string','QT_CUM_SHP_DEL'=>$row[$col["QT_CUM_SHP_DEL"]])
								,array('type'=>'string','QT_PKG'=>$row[$col["QT_PKG"]])
								,array('type'=>'string','WT_PART'=>$row[$col["WT_PART"]])
								,array('type'=>'string','CD_COUNTRY'=>$row[$col["CD_COUNTRY"]])
								,array('type'=>'string','NA_COMP'=>$row[$col["NA_COMP"]])
								,array('type'=>'string','CD_PLANT_DOCK_LOC'=>$row[$col["CD_PLANT_DOCK_LOC"]])
								,array('type'=>'string','CD_RELEASE_ANAL'=>$row[$col["CD_RELEASE_ANAL"]])
								,array('type'=>'string','Carriers'=>$row[$col["Carriers"]])
								,array('type'=>'string','Part_No'=>$partNo)
                                ,array('type'=>'string','FileName'=>$fileName)
                                ,array('type'=>'string','Project'=>'AAT')
                                ,array('type'=>'string','truckLicense'=>$row[$col["Truck NO."]])
                                ,array('type'=>'string','truckType'=>$row[$col["Truck Type"]])
                                ,array('type'=>'string','driverName'=>$row[$col["Driver Name"]])
                                ,array('type'=>'string','phone'=>$row[$col["Phone"]])
							);
							$sqlArray[] = $dataAr;
						}
					}
					if(count($sqlArray)>0)
					{
                        $sql = prepareInsertOrder($sqlArray);
                        /* echo $sql;
                        exit(); */
						sqlError($mysqli,__LINE__,$sql,1,1);
						$total += $mysqli->affected_rows;
						echo '{"status":"server","mms":"เพิ่มสำเร็จ '.($total).' รายการ","sname":[]}';
                        closeDB($mysqli);
					}	
				}
			}
		}
    }
    else if($type==52)
    {
		if(!isset($_FILES["upload"])) {echo json_encode(array('status'=>'server','mms'=>'ไม่พบไฟล์ UPLOAD'));closeDB($mysqli);}
        $fileName = $_FILES["upload"]["name"];
        $tempName = $_FILES["upload"]["tmp_name"];

        $sql = "SELECT FileName from tbl_862order where FileName='$fileName' limit 1";
		$re1 = sqlError($mysqli,__LINE__,$sql,1,1);
		if($re1->num_rows>0) {echo json_encode(array('status'=>'server','mms'=>'ไฟล์นี้ถูก Upload แล้ว'));closeDB($mysqli);}

        if(move_uploaded_file($tempName,"../order_file/".$fileName))
        {
            $file_ext = pathinfo("../order_file/".$fileName, PATHINFO_EXTENSION);
			if($file_ext == 'xls')
            {
                $uriAr = explode('\\',__DIR__);
                $dir = join('\\',array_slice($uriAr, 0, count($uriAr)-1)).'\order_file\\';
                $saveFileName= explode('.',$fileName)[0].'.xlsx';
                $openFile =$dir.$fileName;
                $saveFile =$dir.$saveFileName;
                $output = shell_exec("node C:\\node\\excelConvert\\index.js \"$openFile\" \"$saveFile\"");
                $strFileName = "../order_file/".$saveFileName;
                
            }
            else
            {
                $strFileName = "../order_file/".$fileName;
			}
			
			$reader = ReaderFactory::create(Type::XLSX);
            $reader->setShouldPreserveEmptyRows(true);
			$reader->open($strFileName);
            $total = 0;
            $pages = 0;
            
			foreach ($reader->getSheetIterator() as $sheet) 
            {
				if($sheet->getIndex() == 0)
                {
					$countRow = 0;
					$sqlArray = array();
					foreach ($sheet->getRowIterator() as $row)
                    {
                        ++$countRow;
                        if($countRow === 1)
                        {
                            $checkData = checkColFTM($row);
							$col = $checkData[1];
                            if(strlen($checkData[0])>0)
                            {
                                echo '{"status":"server","mms":"'.$checkData[0].'","sname":[]}';
                                closeDB($mysqli);
                                break;
                            }
						}
						else if($countRow > 1)
						{
							arraytrim($row,$mysqli);
							$ch = array();
							if (strlen($row[$col["LOAD ID"]]) == 0) $ch[] = 'LOAD ID ไม่ควรเป็นค่าว่าง';
							
							if(count($ch) > 0)
                            {                                    
                                echo '{"status":"server","mms":"'.join('<br>',$ch).'<br>บรรทัดที่'.($countRow).'","sname":[]}';
                                closeDB($mysqli);
                                break;
							}
							
							$dataAr = array(
								array('type'=>'string','CD_PLANT'=>$row[$col["CD_PLANT"]])
								,array('type'=>'string','CD_SUPPLIER_SHP_FR'=>$row[$col["CD_SUPPLIER_SHP_FR"]])
								,array('type'=>'string','NO_PART_PREFIX'=>'')
								,array('type'=>'string','NO_PART_BASE'=>'')
								,array('type'=>'string','NO_PART_SUFFIX'=>'')
								,array('type'=>'DateTime','DT_PGM_START'=>'')
								,array('type'=>'string','NO_PGM'=>$row[$col["NO_PGM"]])
								,array('type'=>'string','LOAD ID'=>$row[$col["LOAD ID"]])
								,array('type'=>'string','CD_PICKUP_RTE_NEW'=>$row[$col["CD_PICKUP_RTE_NEW"]])
								,array('type'=>'DateTime','DT_SHIP'=>$row[$col["DT_SHIP"]])
								,array('type'=>'string','TM_SHIP'=>str_replace('.',':',$row[$col["TM_SHIP"]]))
								,array('type'=>'string','CD_DELIVRY_RTE_NEW'=>$row[$col["CD_DELIVRY_RTE_NEW"]])
								,array('type'=>'DateTime','DT_DELIVERY'=>$row[$col["DT_DELIVERY"]])
								,array('type'=>'string','TM_DELIVERY'=>str_replace('.',':',$row[$col["TM_DELIVERY"]]))
								,array('type'=>'string','CD_DELIVERY_DOCK'=>$row[$col["Dock_Load"]])
								,array('type'=>'string','QT_SHP_DEL'=>$row[$col["QT_SHP_DEL"]])
								,array('type'=>'string','QT_CUM_SHP_DEL'=>$row[$col["QT_CUM_SHP_DEL"]])
								,array('type'=>'string','QT_PKG'=>$row[$col["QT_PKG_SU"]])
								,array('type'=>'string','WT_PART'=>$row[$col["Weight of part(KG)"]])
								,array('type'=>'string','CD_COUNTRY'=>$row[$col["CD_COUNTRY"]])
								,array('type'=>'string','NA_COMP'=>$row[$col["NA_COMP"]])
								,array('type'=>'string','CD_PLANT_DOCK_LOC'=>$row[$col["Dock_Load"]])
								,array('type'=>'string','CD_RELEASE_ANAL'=>'')
								,array('type'=>'string','Carriers'=>'')
								,array('type'=>'string','Part_No'=>$row[$col["Partno"]])
                                ,array('type'=>'string','FileName'=>$fileName)
                                ,array('type'=>'string','Project'=>'FTM')
                                ,array('type'=>'string','truckLicense'=>$row[$col["Truck NO."]])
                                ,array('type'=>'string','truckType'=>$row[$col["Truck Type"]])
                                ,array('type'=>'string','driverName'=>$row[$col["Driver Name"]])
                                ,array('type'=>'string','phone'=>$row[$col["Phone"]])
							);
							$sqlArray[] = $dataAr;
						}
                    }
                    // var_dump($sqlArray);
                    // exit();
					if(count($sqlArray)>0)
					{
						$sql = prepareInsertOrder($sqlArray);
						sqlError($mysqli,__LINE__,$sql,1,1);
						$total += $mysqli->affected_rows;
						echo '{"status":"server","mms":"เพิ่มสำเร็จ '.($total).' รายการ","sname":[]}';
                        closeDB($mysqli);
					}	
				}
			}
		}
	}
	else closeDBT($mysqli,2,'TYPE ERROR');
}
else closeDBT($mysqli,2,'TYPE ERROR');

function prepareInsertOrder($data)
{
    $values = array();
    for($i=0,$len=count($data);$i<$len;$i++)
    {
        $values[] = '('.prepareValueInsert($data[$i]).')';
    }
    
    return 'INSERT INTO tbl_862order('.prepareNameInsert($data[0]).')VALUES'.join(',',$values);
}

function prepareNameInsert($data)
{
    $dataReturn = array();
    foreach ($data as $valueAr)
    {
        foreach ($valueAr as $key => $value)
        {
            if($key != 'type')
            {
                $dataReturn[] = $key;
            }
        }
	}
    return str_replace('LOAD ID','LOAD_ID',join(',',$dataReturn));
}
function prepareValueInsert($data)
{
    $dataReturn = array();
    foreach ($data as $valueAr)
    {
        $typeV;
        $keyV;
        $valueV;
        foreach ($valueAr as $key => $value)
        {
            if($key == 'type')
            {
                $typeV = $value;
            }
            else
            {
                $keyV = $key;
                $valueV = $value;
            }
        }
        if($typeV == 'string')
        {
            $dataReturn[$keyV] = "'$valueV'";
        }
        else if($typeV == 'DateTime')
        {
            $dataReturn[$keyV] = (!is_a($valueV, 'DateTime')) ? 'null' : "'".$valueV->format('Y-m-d')."'";
            // $dataReturn[$keyV] = convertDate($valueV);
            
        }
        else
        {
            $dataReturn[$keyV] = $valueV;
        }
    }
    return join(',',$dataReturn);
}

function getCol()
{
    return array(
        'CD_PLANT'
		,'CD_SUPPLIER_SHP_FR'
		,'NO_PART_PREFIX'
		,'NO_PART_BASE'
		,'NO_PART_SUFFIX'
		,'DT_PGM_START'
		,'NO_PGM'
		,'LOAD ID'
		,'CD_PICKUP_RTE_NEW'
		,'DT_SHIP'
		,'TM_SHIP'
		,'CD_DELIVRY_RTE_NEW'
		,'DT_DELIVERY'
		,'TM_DELIVERY'
		,'CD_DELIVERY_DOCK'
		,'QT_SHP_DEL'
		,'QT_CUM_SHP_DEL'
		,'QT_PKG'
		,'WT_PART'
		,'CD_COUNTRY'
		,'NA_COMP'
		,'CD_PLANT_DOCK_LOC'
		,'CD_RELEASE_ANAL'
        ,'Carriers'
        ,'Truck NO.'
        ,'Truck Type'
        ,'Driver Name'
        ,'Phone'
    );
}

function getColFTM()
{
    return array(
        'CD_COUNTRY',
        'CD_PLANT',
        'CD_SUPPLIER_SHP_FR',
        'NA_COMP',
        'Partno',
        'Dock_Load',
        'Ship_FreQuency',
        'NO_PGM',
        'DT_SHIP',
        'TM_SHIP',
        'PU_Time',
        'QT_SHP_DEL',
        'QT_CUM_SHP_DEL',
        'CD_REL_ANL',
        'Part_Status',
        'First Digit',
        'CD_PART_TYPE',
        'CD_PICKUP_RTE_NEW',
        'CD_DELIVRY_RTE_NEW',
        'DT_DELIVERY',
        'TM_DELIVERY',
        'QT_PKG_SU',
        'Weight of part(KG)',
        'Q\'ty pallets or rack',
        'LOAD ID',
        'Truck NO.',
        'Truck Type',
        'Driver Name',
        'Phone'
    );
}

function arraytrim(&$data,$mysqli)
{
    for($i=0,$len=count($data)-1;$i<$len;$i++)
    {
        if(isset($data[$i]))
        {
            if(!is_a($data[$i], 'DateTime'))
            {
                $data[$i] = checkTXT($mysqli,$data[$i],0);
            }
            
        }
    }
}

function checkCol($data)
{
    $needCol = getCol();
    $result = array();
    $colIndex = array();
    for($i=0,$len=count($data);$i<$len;$i++)
    {
        $data[$i] = trim($data[$i]);
    }

    for($i=0,$len=count($needCol);$i<$len;$i++)
    {
        $index = array_search($needCol[$i], $data);
        if($index === false)
        {
            $result[] = 'ไม่พบคอลั่ม '.$needCol[$i];
        }
        else
        {
            $colIndex[$needCol[$i]] = $index;
        }
    }
    return array(join('<br>',$result),$colIndex);
}

function checkColFTM($data)
{
    $needCol = getColFTM();
    $result = array();
    $colIndex = array();
    for($i=0,$len=count($data);$i<$len;$i++)
    {
        $data[$i] = trim($data[$i]);
    }

    for($i=0,$len=count($needCol);$i<$len;$i++)
    {
        $index = array_search($needCol[$i], $data);
        if($index === false)
        {
            $result[] = 'ไม่พบคอลั่ม '.$needCol[$i];
        }
        else
        {
            $colIndex[$needCol[$i]] = $index;
        }
    }
    return array(join('<br>',$result),$colIndex);
}

function convertDate($valueV)
{
    if(is_a($valueV, 'DateTime'))
    {
        $v = "'".$valueV->format('Y-m-d')."'";
    }
    else
    {
        $valueV1 = explode('-',$valueV);
        $valueV2 = explode('/',$valueV);
        if(count($valueV1) == 3)
        {
            $v = switchDate($valueV1);
        
        }else if(count($valueV2) == 3)
        {
            $v = switchDate($valueV2);
        }
        else
        {
            $v = 'null';
        }
    }
    
    return $v;
}

function switchDate($d)
{
    if(strlen($d[0]) == 4)
    {
        return "'"."$d[0]-$d[1]-$d[2]"."'";
    }
    else
    {
        return "'"."$d[2]-$d[1]-$d[0]"."'";
    }
}
$mysqli->close();
exit();
?>
