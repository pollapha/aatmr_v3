var header_truckMonitor = function()
{
	var menuName="truckMonitor_",fd = "h1/"+menuName+"data.php";
    
    function init()
    {
        checkTimeFTM();
        setInterval(() => 
        {
            checkTimeFTM();
        }, 10*1000);
        
    };

    function checkTimeFTM()
    {
        ajax(fd,{},4,function(json)
        {            
            if(json.data.length === 0)
            {
                return;   
            }
            if(json.data[0].countRows>0 || json.data[1].countRows>0)
            {
                webix.message({ type:"error",expire:7000, text:
                    `กรุณาคีย์เวลา (FTM)<br>
                    ขาเข้า = ${json.data[0].countRows} รายการ<br>
                    ขาออก = ${json.data[1].countRows} รายการ
                    `
                });
            }
            
        },null,function(json)
        {
            
        });
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

    function randomTime(start, end) 
    {
        var diff =  end.getTime() - start.getTime();
        var new_diff = diff * Math.random();
        var date = new Date(start.getTime() + new_diff);
        return date;
    }

    function setTable(name,data)
    {
        var eName = ele(name);
        eName.clearAll();
        eName.parse(data,"json");
        eName.filterByAll();
    };

    function addMinutes(date, minutes) 
    {
        return new Date(date.getTime() + minutes*60000);
    }

    function addZero(date) 
    {
        if(date.toString().length == 1) {
            date = '0'+date;
       }
       return date;
    }

    

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

        var f = [
            'NO',
            'gpsStatus',
            'dateStart',
            'docDate',
            'docTime',
            'acDocTime',
            'lateTime',
            'acOutDocTime',
            's5_seq',
            'l11_rn',
            'supplierCode',
            'supplierName',
            'truckLicense',
            'driverName',
            'phone',
            'b2',
            'insidePolygon',
            'gps_speed',
            'checkGeo',
            'checkGPS',
            'distance',
            'gps_updateDatetime'
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

    webix.ui({
        view:"window",modal:true,
        id:$n("winUpdate2"),
        position:"center",width:window.innerWidth- 100,height:window.innerHeight,
        move:true,head:"",
        body:
        {
            rows:
            [
                {
                    view:"form",
                    paddingY:0,
                    id:$n('winUpdate2Form1'),
                    elements:
                    [
                        {
                            cols:
                            [
                                vw1("text","truckLicense","Truck License (ทะเบียนรถ)",{}),
                                vw1("combo","truckType","Truck Type (ชนิดรถ)",{options:['4W','6W','10',''],value:'6W'}),
                                vw1("text","driverName","Driver Name (ชื่อคนขับรถ)",{}),
                                vw1("text","phone","Phone (เบอร์โทรศัทพ์)",{}),
                                vw1("text","remark","Remark",{required:false}),
                            ]
                        },
                    ],
                    on:
                    {
                        "onSubmit":function(view,e)
                        {
                            if (view.config.name == 'remark')
                            {
                                if(!ele('winUpdate2BtnOK').isEnabled()) return;
                                view.blur();
                                ele('winUpdate2BtnOK').callEvent("onItemClick", []);
                            }
                            else if(webix.UIManager.getNext(view).config.type == 'line')
                            {
                                webix.UIManager.setFocus(webix.UIManager.getNext(webix.UIManager.getNext(view)));
                            }
                            else
                            {
                                webix.UIManager.setFocus(webix.UIManager.getNext(view));
                            }
                        }
                    },
                },
                {
                    cols:
                    [
                        {},
                        vw1("button",'winUpdate2BtnOK',"OK (ตกลง)",{type:'form',width:130,
                            on:{onItemClick:function()
                                {  
                                    var btn = this;
                        
                                    msBox('บันทึกข้อมูล',function()
                                    {
                                        ajax(fd,ele('winUpdate2Form1').getValues(),22,function(json)
                                        {
                                            ele('winUpdate2').hide();
                                            ele('reload').callEvent("onItemClick", []);
                                            webix.message({ type:"default",expire:7000, text:'บันทึกสำเร็จ'});
                                        },btn,function(json)
                                        {
                                            
                                        });
                                    });
                                }
                            }
                        }),
                        vw1("button",'winUpdate2Cancel',"Cancel (ยกเลิก)",{type:'danger',width:130,on:{onItemClick:function(){ele('winUpdate2').hide()}}}),
                        {}
                    ]
                }
            ]
        }
    });

    webix.ui({
        view:"window",modal:true,
        id:$n("winUpdate"),
        position:"center",width:window.innerWidth- 700,height:window.innerHeight,
        move:true,head:"----",
        body:
        {
            rows:
            [
                {
                    view:"form",
                    paddingY:0,
                    id:$n('winUpdateForm1'),
                    elements:
                    [
                        {
                            cols:
                            [
                                vw1("text","PlanIN_Datetime","แผนถึง",{type:'',disabled:true}),
                                vw1("text","ActualIN_Date","วันที่ถึงจริง (ปี-เดือน-วัน)",{type:''}),
                                vw1("text","acDocTime","เวลาถึงจริง",{type:''}),
                                {
                                    rows:
                                    [
                                        {},
                                        vw1("button",'winUpdateIn',"Update In(ขาเข้า)",{type:'form',width:150,
                                            on:
                                            {

                                            }
                                        })
                                    ]
                                }
                            ]
                        },
                        {
                            cols:
                            [
                                vw1("text","PlanOut_Datetime","แผนออก",{type:'',disabled:true}),
                                vw1("text","ActualOut_Date","วันที่ออกจริง (ปี-เดือน-วัน)",{type:''}),
                                vw1("text","acOutDocTime","เวลาออกจริง",{type:''}),
                                {
                                    rows:
                                    [
                                        {},
                                        vw1("button",'winUpdateOut',"Update Out(ขาออก)",{type:'form',width:150,
                                            on:
                                            {
                                                onItemClick:function()
                                                {  
                                                    var btn = this;
                                        
                                                    msBox('บันทึกข้อมูล',function()
                                                    {
                                                        ajax(fd,ele('winUpdateForm1').getValues(),23,function(json)
                                                        {
                                                            ele('winUpdate').hide();
                                                            ele('reload').callEvent("onItemClick", []);
                                                            webix.message({ type:"default",expire:7000, text:'บันทึกสำเร็จ'});
                                                        },btn,function(json)
                                                        {
                                                            
                                                        });
                                                    });
                                                }
                                            }
                                        })
                                    ]
                                }
                            ]
                        },
                        // vw1("text","remark","Remark",{required:false}),
                    ],
                    on:
                    {
                        "onSubmit":function(view,e)
                        {
                            if (view.config.name == 'acOutDocTime')
                            {
                                if(!ele('winUpdateBtnOK').isEnabled()) return;
                                view.blur();
                                ele('winUpdateBtnOK').callEvent("onItemClick", []);
                            }
                            else if (view.config.name == 'acDocTime')
                            {
                                focus('acOutDocTime');
                            }
                            else if(webix.UIManager.getNext(view).config.type == 'line')
                            {
                                webix.UIManager.setFocus(webix.UIManager.getNext(webix.UIManager.getNext(view)));
                            }
                            else
                            {
                                webix.UIManager.setFocus(webix.UIManager.getNext(view));
                            }
                        }
                    },
                },
                {
                    cols:
                    [
                        {},
                        /* vw1("button",'winUpdateBtnOK',"OK (ตกลง)",{type:'form',width:130,
                            on:{onItemClick:function()
                                {  
                                    var btn = this;
                        
                                    msBox('บันทึกข้อมูล',function()
                                    {
                                        ajax(fd,ele('winUpdateForm1').getValues(),23,function(json)
                                        {
                                            ele('winUpdate').hide();
                                            ele('reload').callEvent("onItemClick", []);
                                            webix.message({ type:"default",expire:7000, text:'บันทึกสำเร็จ'});
                                        },btn,function(json)
                                        {
                                            
                                        });
                                    });
                                }
                            }
                        }), */
                        vw1("button",'winUpdateCancel',"Exit (ออก)",{type:'danger',width:130,on:{onItemClick:function(){ele('winUpdate').hide()}}}),
                        {}
                    ]
                }
            ]
        }
    });

	return {
        view: "scrollview",
        scroll: "native-y",
        id:"header_truckMonitor",
        body: 
        {
        	id:"truckMonitor_id",
        	type:"clean",
    		rows:
    		[
    		    {
                    view:"form",
                    paddingY:0,
                    id:$n("form1"),
                    elements:
                    [
                        {
                            cols:
                            [
                                {},
                                {
                                    rows:
                                    [
                                        {},
                                        {view:"button",value:"Reload (โหลดข้อมูลใหม่)",id:$n("reload"),on:
                                            {
                                                onItemClick:function(id, e)
                                                {
                                                    var btn  = this,dataT1=ele('dataT1'),obj={};
                                                    ajax(fd,ele('form1').getValues(),1,function(json)
                                                    {                                                       
                                                        setTable('dataT1',json.data);
                                                    },btn,function(json)
                                                    {
                                                        setTable('dataT1',[]);
                                                    });
                                                }
                                            }
                                        },
                                    ]
                                },
                                vw1('combo','problem','Show Data (แสดงข้อมูล)',{value:"All (ทั้งหมด)",options:['All (ทั้งหมด)','NORMAL (ปกติ)','PROBLEM (เกิดปัญหา)']}),
                                vw1('combo','project','Project (โครงการ)',{value:"AAT MR",options:['AAT MR','AAT EDC','FTM MR','SKD','ALL']}),
                                {
                                    rows:
                                    [
                                        {},
                                        vw1('button','export','Export',
                                            {
                                                on:
                                                {
                                                    onItemClick:function(id, e)
                                                    {
                                                        $.fileDownload(fd, {
                                                            httpMethod: "POST",
                                                            data:{obj:ele('form1').getValues(),type:2},
                                                            successCallback: function (url) {
                                                            },
                                                            prepareCallback: function (url) {
                                                            },
                                                            failCallback: function (responseHtml, url) {
                            
                                                            }
                                                        });
                                                    }
                                                },
                                                width:130
                                            }
                                        ),
                                    ]
                                },
                                {},
                            ]
                        },
                    ]
                },
                {
                    view:"datatable",id:"truckMonitor_dataT1",navigation:true,
                    resizeColumn:true,autoheight:false,
                    hover:"myhover",threeState:true,
                    datatype:"json",
                    scheme:
                    {
                      $change:function(item)
                      {
                        /* if(item.truck_carrier == 'ทะเบียนรถไม่อยู่ในระบบ' || item.checkGeo == 'ยังไม่ตีกรอบ' || item.checkTimeStampIN == 'กรุณากรอกเวลา' || item.checkTimeStampOUT == 'กรุณากรอกเวลา')
                        {
                            item.$css = "highlight-red";
                        } */

                        var obj = {};

                        if(item.checkTimeStampIN == 'กรุณากรอกเวลา')
                        {
                            obj.acDocTime = 'highlight-red';
                        }

                        if(item.checkTimeStampOUT == 'กรุณากรอกเวลา')
                        {
                            obj.acOutDocTime = 'highlight-red';
                        }

                        if(item.truck_carrier == 'ทะเบียนรถไม่อยู่ในระบบ' || item.checkGeo == 'ยังไม่ตีกรอบ')
                        {
                            obj.truck_carrier = 'highlight-red';
                        }

                        if(item.checkGeo == 'ทะเบียนรถไม่อยู่ในระบบ')
                        {
                            obj.checkGeo = 'highlight-red';
                        }

                        if(item.gps_connection == 'CONNECTED')
                        {
                            obj.gps_connection = 'webix_selected';
                        }
                        else
                        {
                            obj.gps_connection = 'highlight-red';
                        }
                                                
                        item.$cellCss = obj;

                      }
                    },
                    columns:
                    [
                        { id:"data22",header:"&nbsp;",width:40,
                           template: "<span style='cursor:pointer' class='webix_icon fa-clock-o'></span>"
                        },
                        { id:"data22",header:"&nbsp;",width:40,
                            template: "<span style='cursor:pointer' class='webix_icon fa-pencil'></span>"
                        },
                        { id:"data23",header:"&nbsp;",width:40,
                           template: "<span style='cursor:pointer' class='webix_icon fa-trash-o'></span>"
                        },
                        { id:"NO",header:"NO.",css:"rank",width:40},
                        /* { id:"gpsStatus",header:["EDI",{content:"textFilter"}],width:90}, */
                        { id:"operration_date",header:["Operration Date",{content:"textFilter"}],width:130},
                        { id:"dateStart",header:["วันที่เริ่ม",{content:"textFilter"}],width:100},                                                
                        { id:"docDate",header:["วันที่ถึง",{content:"textFilter"}],width:100},
                        { id:"docTime",header:["เวลาถึง",{content:"textFilter"}],width:70},
                        { id:"acDocTime",header:["ถึงจริง",{content:"textFilter"}],width:70},
                        { id:"lateTime",header:["เวลาออก",{content:"textFilter"}],width:80},
                        { id:"acOutDocTime",header:["ออกจริง",{content:"textFilter"}],width:70},
                        { id:"StopSequenceNumber",header:["ลำดับ",{content:"textFilter"}],width:55},
                        { id:"Load_ID",header:["Load ID",{content:"textFilter"}],width:120},
                        { id:"Route",header:["รหัสเส้นทาง",{content:"textFilter"}],width:120},
                        { id:"Supplier_Code",header:["รหัสซัพพลายเออร์",{content:"textFilter"}],width:120},
                        { id:"Supplier_Name",header:["ชื่อซัพพลายเออร์",{content:"textFilter"}],width:150},
                        { id:"truckLicense",header:["ทะเขียนรถ",{content:"textFilter"}],width:120},
                        { id:"truck_carrier",header:["Carrier",{content:"textFilter"}],width:120},
                        { id:"gps_connection",header:["GPS Connection",{content:"textFilter"}],width:120},
                        { id:"gps_updateDatetime",header:["GPS Update",{content:"textFilter"}],width:120},
                        { id:"checkGeo",header:["Check Geo",{content:"textFilter"}],width:120},
                        { id:"driverName",header:["คนขับรถ",{content:"textFilter"}],width:120},
                        { id:"phone",header:["เบอร์โทร",{content:"textFilter"}],width:120},
                    ],
                    onClick:
                    {
                        "fa-clock-o":function(e,t)
                        {
                            var row1 = this.getItem(t),obj={};
                            var row = {...row1};
                            if(dayjs(row['ActualIN_Datetime'] , 'YYYY-MM-DD HH:mm:ss').isValid() )
                            {
                                row.ActualIN_Date = dayjs(row['ActualIN_Datetime']).format('YYYY-MM-DD');
                                row.acDocTime = dayjs(row['ActualIN_Datetime']).format('HH:mm');                    
                            }
                            else
                            {
                                row.ActualIN_Date = dayjs(row['PlanIN_Datetime']).format('YYYY-MM-DD');
                                
                                var new_time = randomTime(
                                    addMinutes(new Date(row.PlanIN_Datetime), 5), 
                                new Date(row.PlanIN_Datetime));
                                
                                row.acDocTime = `${addZero(new_time.getHours())}:${addZero(new_time.getMinutes())}`;
                                // ele('acDocTime').setValue(`${addZero(new_time.getHours())}:${addZero(new_time.getMinutes())}`);
                            }


                            if(dayjs(row['ActualOut_Datetime'] , 'YYYY-MM-DD HH:mm:ss').isValid() )
                            {
                                row.ActualOut_Date = dayjs(row['ActualOut_Datetime']).format('YYYY-MM-DD');
                                row.acOutDocTime = dayjs(row['ActualOut_Datetime']).format('HH:mm');                    

                            }
                            else
                            {
                                row.ActualOut_Date = dayjs(row['PlanOut_Datetime']).format('YYYY-MM-DD');

                                var in_ran = ele('acDocTime').getValue();
                                var out_time = new Date(row.PlanOut_Datetime);
                                var in_ran_time = new Date(row.PlanOut_Datetime);
                                let [hours, minutes] = in_ran.split(':');
                                in_ran_time.setHours(hours); 
                                in_ran_time.setMinutes(minutes); 

                                var in_time = new Date(row.PlanIN_Datetime);
                                
                                var diff = out_time - in_time;
                                var diff_min = (diff/60)/1000;

                                if(diff_min <= 5)
                                {
                                    var new_time  = randomTime(addMinutes(in_ran_time,5),in_ran_time);
                                
                                    // ele('acOutDocTime').setValue(`${addZero(new_time.getHours())}:${addZero(new_time.getMinutes())}`); 
                                    row.acOutDocTime = `${addZero(new_time.getHours())}:${addZero(new_time.getMinutes())}`;
                                    
                                }
                                else
                                {
                                    var new_time  = randomTime(
                                        addMinutes(new Date(row.PlanOut_Datetime), 5), 
                                    new Date(row.PlanOut_Datetime));
                                    // ele('acOutDocTime').setValue(`${addZero(new_time.getHours())}:${addZero(new_time.getMinutes())}`);
                                    row.acOutDocTime = `${addZero(new_time.getHours())}:${addZero(new_time.getMinutes())}`;
                                }
                            
                            }                            
                            
                            ele('winUpdate').show();
                            ele('winUpdateForm1').setValues(row);
                            ele('winUpdate').getHead().setHTML(row.Supplier_Name);
                            focus('acDocTime');

                            if(row.gpsStatus == 'MANUAL')
                            {
                                
                            }
                            else if(row.gpsStatus == 'AUTO') webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:'ระบบกำลังทำงานอัตโนมัติ',callback:function(){}});
                            else if(row.gpsStatus == 'SEND_EDI') webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:'ระบบกำลังส่งไฟล์ EDI',callback:function(){}});
                            else if(row.gpsStatus == 'EDI_ERROR') webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:'ออก PICKUP SHEET ไม่ครบ',callback:function(){}});
                        },
                        "fa-trash-o":function(e,t)
                        {
                            var row = this.getItem(t),obj={};
                            
                            webix.confirm(
                            {
                                title: "กรุณายืนยัน",ok:"ใช่", cancel:"ไม่",text:"คุณต้องการลบข้อมูลที่ทำใน JIDA แล้ว<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                callback:function(res)
                                {
                                    if(res)
                                    {
                                        ajax(fd,row,31,function(json)
                                        {
                                            ele('reload').callEvent("onItemClick", []);
                                            webix.message({ type:"default",expire:7000, text:'บันทึกสำเร็จ'});
                                        },null,function(json)
                                        {
                                            
                                        });
                                    }
                                }
                            });
                        },
                        "fa-pencil":function(e,t)
                        {
                            var row = this.getItem(t);
                            ele('winUpdate2').show();
                            ele('winUpdate2Form1').setValues(row);
                        },
                    },
                },
            ],on:
            {
                onHide:function()
                {
                    
                },
                onShow:function()
                {
                    ele('reload').callEvent("onItemClick", []);
                },
                onAddView:function()
                {
                	ele('reload').callEvent("onItemClick", []);
                    init();
                }
            }
        }
    };
};