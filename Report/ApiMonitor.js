var header_ApiMonitor = function()
{
	var menuName="ApiMonitor_",fd = "Report/"+menuName+"data.php";

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

    function exportExcel(btn)
    {
        var dataT1 = ele("dataT1"),obj={},data = [];
        if(dataT1.count()==0) 
        {
            webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:'ไม่พบข้อมูลในตาราง',callback:function(){}});
        }
        
        for(var i=-1,len=dataT1.config.columns.length;++i<len;)
        {
            obj[dataT1.config.columns[i].id] = dataT1.config.columns[i].header[0].text;
        }

        delete obj.data22;
        var objKey = Object.keys(obj);
        var f = [];
        for (var i = -1, len = objKey.length; ++i < len;) {
            f.push(objKey[i]);
        }
        var col = [];
        for(var i=-1,len=f.length;++i<len;)
        {
            col[col.length] = obj[f[i]];
        }
        data[data.length] = col;
        if(dataT1.count()>0)
        {
            btn.disable();
            dataT1.eachRow( function (row)
            {
                var r = dataT1.getItem(row),rr=[];
                for(var i=-1,len=f.length;++i<len;)
                {
                  rr[rr.length] = r[f[i]];
                }
                data[data.length] = rr;
            });
            
            var worker = new Worker('js/workerToExcel.js?v=2');
            worker.addEventListener('message', function(e) 
            {
                saveAs(e.data, 'DRIVER_MASTER_'+new Date().getTime()+".xlsx");
                btn.enable();
                webix.message({expire:7000, text:"Export สำเร็จ" });
            }, false);  
            worker.postMessage({'cmd': 'start', 'msg':data});  
        }   
        else {webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:"ไม่พบข้อมูลในตาราง",callback:function(){}});}
    };

	return {
        view: "scrollview",
        scroll: "native-y",
        id:"header_ApiMonitor",
        body: 
        {
        	id:"ApiMonitor_id",
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
                                        onItemClick: function () 
                                        {
                                            var btn = this;
                                            loadData(btn);
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
                    columns:
                        [
                            {
                                id: "data22", header: "&nbsp;", adjust: true,
                                template: function (row) {
                                    if (row.change == 1) {
                                        return "<span style='cursor:pointer' class='webix_icon fa-check'></span>";
                                    }
                                    else {
                                        return "<span style='cursor:pointer' class='webix_icon'></span>";
                                    }

                                }
                            },
                            { id: "NO", header: ["NO."], editor: "", width: 60 },
                            { id: "Api_Name", header: ["Api Name"], editor: "text", width: 150 },

                            { id: "Last_Total_Q", header: "Last Q", editor: "", width: 200 },
                            { id: "Last_Update", header: "Last Update", editor: "", width: 200 },

                            { id: "Prev_Total_Q", header: ["Prev Q"], editor: "text", width: 150 },
                            { id: "Prev_Update", header: "Prev Update", editor: "", width: 150 },
                            { id: "Last_Restart", header: "Last Restart", editor: "", width: 150 },

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