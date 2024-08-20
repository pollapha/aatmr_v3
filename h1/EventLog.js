var header_EventLog = function()
{
	var menuName="EventLog_",fd = "h1/"+menuName+"data.php";

    function init()
    {
        
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

    function exportExcel(btn)
    {
        var dataT1 = ele("table_t1"),obj={},data = [];
        if(dataT1.count()==0) 
        {
            webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:'ไม่พบข้อมูลในตาราง',callback:function(){}});
        }
        
        for(var i=-1,len=dataT1.config.columns.length;++i<len;)
        {
            obj[dataT1.config.columns[i].id] = dataT1.config.columns[i].header[0].text;
        }

        var f = 
        [
            "NO",
            "Load_ID",
            "event",
            "event_date",
            "created_by",
        ];
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
                saveAs(e.data, 'TTV'+new Date().getTime()+".xlsx");
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
        id:"header_EventLog",
        body: 
        {
        	id:"EventLog_id",
        	type:"clean",
    		rows:
    		[
    		    { 
                    view: "form", id: $n("form1"), scroll: false,
                    elements: 
                    [
                        {
                            cols:
                            [
                                { view: "text", label: "Load ID",name: "Load_ID",  labelPosition : "top" ,},
                                { 
                                    view: "datepicker", label: "Start Date",name: "start_date", labelPosition: "top" ,stringResult:true,
                                    format: "%d/%m/%Y", value: new Date()
                                },
                                { 
                                    view: "datepicker", label: "Stop Date",name: "stop_date", labelPosition: "top" ,stringResult:true,
                                    format: "%d/%m/%Y", value: new Date()
                                },
                                {
                                    rows:
                                    [
                                        {},
                                        { 
                                            view: "button",label: "Find", 
                                            click: function () 
                                            {
                                                const formData = ele("form1").getValues();
                                                formData.start_date = dayjs(formData.start_date).format('YYYY-MM-DD')
                                                formData.stop_date = dayjs(formData.stop_date).format('YYYY-MM-DD')
                                                ajax(fd, formData, 1, function (json)
                                                {
                                                    setTable('table_t1', json.data);
                                                }, null, function (json)
                                                { });            
                                            }
                                        },
                                    ]
                                },

                                {
                                    rows:
                                    [
                                        {},
                                        { 
                                            view: "button",label: "Export", 
                                            click: function () 
                                            {
                                                exportExcel(this); 
                                            }
                                        },
                                    ]
                                }

                                
                            ]
                        }
                    ]
                },
                { view: "datatable", id: $n("table_t1"),
                    columns: [

                        { id: "NO", header: "ID", width: 80 },
                        { id: "Load_ID", header: "Load ID", width: 100 },
                        { id: "event", header: "Event", width: 400 },
                        { id: "event_date", header: "Event Date", width: 200 },
                        { id: "created_by", header: "Created_by", width: 200 },
                        ],
                        onClick:
                        { "fa-pencil": function (e, t) {
                            const item = this.getItem(t);
                            ele('form1').parse(item);
                            }
                        },
                        scroll: false,
                        autoheight: true,
                        autowidth: false,
                        footer: false,
                  },   

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