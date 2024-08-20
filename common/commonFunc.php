<?php

function checkProject($mysqli,$lineCode,$data,$rollback=1)
{
    $sql = "SELECT * from tbl_project_master t1 where upper(t1.projectName)=upper('$data') limit 1";
    return sqlError($mysqli,$lineCode,$sql);
}

    /* function checkVendorCode($mysqli,$lineCode,$data,$rollback=1)
    {
        $sql = "SELECT * from tbl_vendor_master t1 where t1.Vendor_Code='$data' limit 1";
        return sqlError($mysqli,$lineCode,$sql);
    }

    function extracCodeVendor($Vendor)
    {
        $VendorAr = explode(' | ',$Vendor);
		if(count($VendorAr) !=2) closeDBT($mysqli,2,'Vendor รูปแบบการกรอกข้อมูลไม่ถูกต้อง');
		return $VendorAr;
    }

    function checkItemCode($mysqli,$lineCode,$data,$rollback=1)
    {
        $sql = "SELECT * from tbl_items_master t1 where t1.Item_Code='$data' limit 1";
        return sqlError($mysqli,$lineCode,$sql);
    }

    function extracCodeItem($Item)
    {
        $ItemAr = explode(' | ',$Item);
		if(count($ItemAr) !=2) closeDBT($mysqli,2,'Item รูปแบบการกรอกข้อมูลไม่ถูกต้อง');
		return $ItemAr;
    }

    function checkWareHouseCode($mysqli,$lineCode,$data,$rollback=1)
    {
        $sql = "SELECT * from tbl_warehouse_master t1 where t1.Warehouse_Code='$data' limit 1";
        return sqlError($mysqli,$lineCode,$sql);
    }

    function extracCodeCustomer($Item)
    {
        $ItemAr = explode(' | ',$Item);
        if(count($ItemAr) !=2) closeDBT($mysqli,2,'Item รูปแบบการกรอกข้อมูลไม่ถูกต้อง');
        $ItemAr = explode('(',$ItemAr[0]);
        if(count($ItemAr) !=2) closeDBT($mysqli,2,'Item รูปแบบการกรอกข้อมูลไม่ถูกต้อง');
		return $ItemAr;
    }

    function checkCustomerCode($mysqli,$lineCode,$data,$rollback=1)
    {
        $sql = "SELECT * from tbl_customer_items t1 where t1.Cus_Code='$data' limit 1";
        return sqlError($mysqli,$lineCode,$sql);
    }

    function getCustomerCodeAndItemCode($mysqli,$lineCode,$data,$rollback=1)
    {
        $sql = "SELECT concat(t1.Cus_Code,'(',t2.Item_Code,')',' | ',t2.Item_Name) value
	    from tbl_customer_items t1
        left join tbl_items_master t2 on t1.Item_ID=t2.Item_ID
        left join tbl_customer_master t3 on t1.Customer_ID=t3.Customer_ID
        where (t1.Cus_Code ='$data[Cus_Code]') limit 1";
        return sqlError($mysqli,$lineCode,$sql);

    }

    function getVendorCodeAndName($mysqli,$lineCode,$data,$rollback=1)
    {
        $sql = "SELECT concat(t1.Vendor_Code,' | ',t1.Vendor_Name) value
        from tbl_vendor_master t1 where t1.Vendor_Code='$data[Vendor_Code]' limit 1";
        return sqlError($mysqli,$lineCode,$sql);
    } */

    function systemToHumanDate($date)
    {
        $dateAr = explode('-',$date);
        return $dateAr[2].'/'.$dateAr[1].'/'.substr($dateAr[0],2,2);
    }

    function systemDateConvert($date)
    {
        $dateAr = explode('/',$date);
        $y = strlen($dateAr[2]) == 2 ? '20'.$dateAr[2]:$dateAr[2];
        $m = strlen($dateAr[1]) == 1 ? '0'.$dateAr[1]:$dateAr[1];
        $d = strlen($dateAr[0]) == 1 ? '0'.$dateAr[0]:$dateAr[0];
        return $y.'-'.$m.'-'.$d;
    }

?>