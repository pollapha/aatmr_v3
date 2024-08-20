var header_checkPointReport = function()
{
	var menuName="checkPointReport_",fd = "Report/"+menuName+"data.php";

    function init()
    {
        loadData();
    };

    function ele(name)
    {
        return $$($n(name));
    };

    function $n(name)
    {
        return menuName+name;
    };
    
    function focus(name)
    {
        setTimeout(function(){ele(name).focus();},100);
    };
    
    function setView(target,obj)
    {
        var key = Object.keys(obj);
        for(var i=0,len=key.length;i<len;i++)
        {
            target[key[i]] = obj[key[i]];
        }
        return target;
    };

    function vw1(view,name,label,obj)
    {
        var v = {view:view,required:true,label:label,id:$n(name),name:name,labelPosition:"top"};
        return setView(v,obj);
    };

    function vw2(view,id,name,label,obj)
    {
        var v = {view:view,required:true,label:label,id:$n(id),name:name,labelPosition:"top"};
        return setView(v,obj);
    };

    function setTable(tableName, data) {
        if (!ele(tableName)) return;
        ele(tableName).clearAll();
        ele(tableName).parse(data);
        ele(tableName).filterByAll();
    };
    
    function loadData(btn) {
        ajax(fd, {}, 1, function (json) {
            setTable('dataT1', json.data);
        }, btn);
    };

    function exportExcel(btn) {
        var dataT1 = ele("dataT1"), obj = {}, data = [];
        if (dataT1.count() == 0) {
            webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูลในตาราง', callback: function () { } });
        }

        for (var i = -1, len = dataT1.config.columns.length; ++i < len;) {
            obj[dataT1.config.columns[i].id] = dataT1.config.columns[i].header[0].text;
        }
        delete obj.data22;
        var objKey = Object.keys(obj);
        var f = [];
        for (var i = -1, len = objKey.length; ++i < len;) {
            f.push(objKey[i]);
        }
        

        var col = [];
        for (var i = -1, len = f.length; ++i < len;) {
            col[col.length] = obj[f[i]];
        }
        data[data.length] = col;
        if (dataT1.count() > 0) {
            btn.disable();
            dataT1.eachRow(function (row) {
                var r = dataT1.getItem(row), rr = [];
                for (var i = -1, len = f.length; ++i < len;) {
                    rr[rr.length] = r[f[i]];
                }
                data[data.length] = rr;
            });
            // console.log(data);
            var worker = new Worker('js/workerToExcel.js?v=2');
            worker.addEventListener('message', function(e) 
            {
                saveAs(e.data, 'TRUCK_CHECK_POINT_'+new Date().getTime()+".xlsx");
                btn.enable();
                webix.message({expire:7000, text:"Export สำเร็จ" });
            }, false);  
            worker.postMessage({ 'cmd': 'start', 'msg': data });
        }
        else { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: "ไม่พบข้อมูลในตาราง", callback: function () { } }); }
    };

	return {
        view: "scrollview",
        scroll: "native-y",
        id:"header_checkPointReport",
        body: 
        {
        	id:"checkPointReport_id",
        	type:"clean",
    		rows:
    		[
                {
                    view: "form",
                    paddingY: 0,
                    id: $n("form1"),
                    on:
                    {

                    },
                    elements:
                        [
                            {
                                cols:
                                    [
                                        {},
                                        vw1("button", 'btnFind', "Find (ค้นหา)", {
                                            width: 170, on:
                                            {
                                                onItemClick: function () {
                                                    var btn = this;
                                                    loadData(btn);
                                                }
                                            }
                                        }),
                                        vw1("button", 'btnExport', "Export (โหลดเป็นไฟล์เอ๊กเซล)", {
                                            width: 170, on:
                                            {
                                                onItemClick: function () {
                                                    var btn = this;
                                                    exportExcel(this);
                                                }
                                            }
                                        }),
                                        {}
                                    ]
                            }
                        ]
                },
    		    {
                    view: "datatable", id: $n('dataT1'), datatype: "json",
                    headerRowHeight: 20, rowLineHeight: 20, rowHeight: 20,
                    hover: "webix_row_select", editable: true,
                    css: { "font-size": "12px" }, resizeColumn: true,
                    scheme:{
                        $change:function(item)
                        {
                            if(item.Status == 'WAIT')
                            {
                                item.$css = "highlight-yellow";
                            }
                            else
                            {
                                if(item.docCount*1 >0)
                                {
                                    item.$css = "webix_row_select";
                                }
                            }
                        }
                    },
                    columns:
                        [
                            { id: "NO", header: ["NO."], editor: "", width: 60 },
                            { id: "check_date_time", header: ["Check Date Time", { content: "textFilter" }], editor: "", width: 130 },
                            { id: "Status", header: ["Check Status", { content: "textFilter" }], editor: "", width: 100 },
                            { id: "Load_ID", header: ["Load ID", { content: "textFilter" }], editor: "", width: 100 },
                            { id: "Route", header: ["Route", { content: "textFilter" }], editor: "", width: 100 },
                            { id: "Dock", header: ["Dock", { content: "textFilter" }], editor: "", width: 100 },
                            { id: "truckLicense", header: ["ทะเบียรถ", { content: "textFilter" }], editor: "", width: 100},
                            { id: "driverName", header: ["ชื่อพนักงานขับรถ", { content: "textFilter" }], editor: "", width: 100},
                            { id: "phone", header: ["เบอร์โทร", { content: "textFilter" }], editor: "", width: 100},
                            { id: "Truck_L", header: ["ประตู ซ้าย", { content: "textFilter" }], editor: "", width: 100},
                            { id: "Truck_B", header: ["ประตู หลัง", { content: "textFilter" }], editor: "", width: 100},
                            { id: "Truck_R", header: ["ประตู ขวา", { content: "textFilter" }], editor: "", width: 100},
                            { id: "Rack_Check_1", header: ["แร็คหรือพาเลทไม่ชำรุดเสียหาย", { content: "textFilter" }], editor: "", width: 200},
                            { id: "Rack_Check_2", header: ["ทำการล็อค Stopper ทุกตัวหรือพาเลทสินค้ารัดแน่นหนา", { content: "textFilter" }], editor: "", width: 200},
                            { id: "Rack_Check_3", header: ["รัดสินค้าด้านท้ายแร็คหรือด้านท้ายพาเลท", { content: "textFilter" }], editor: "", width: 200},
                            { id: "Rack_Check_4", header: ["กล่องสินค้าหรือแร็ค ไม่บุบ ไม่ยุบตัว", { content: "textFilter" }], editor: "", width: 200}
                        ],
                    onClick:
                    {
                        
                    },
                    on:
                    {
                        
                    }
                }
            ],on:
            {
                onHide:function()
                {
                    
                },
                onShow:function()
                {

                },
                onAddView:function()
                {
                	init();
                }
            }
        }
    };
};