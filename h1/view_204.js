var header_view_204 = function()
{
	var menuName="view_204_",fd = "h1/"+menuName+"data.php";

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

    function vw3(view,id,name,label,obj)
    {
        var v = {view:view,required:true,label:label.replace(/_/g, " "),id:$n(id+name),name:name,labelPosition:"top"};
        var view = setView(v,obj);
        if(view.view == 'button') return view;
        if(view.required == true)
        {
            view.label = "<font color='red'>"+view.label+"</font>";
        }
        else 
        {
            view.label = "<font color='green'>"+view.label+"</font>";
        }
        return view;
    };

    function setTable(name,data)
    {
        var eName = ele(name);
        eName.clearAll();
        eName.parse(data,"json");
        eName.filterByAll();
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

        var f = [
            'NO',
            'projectName',
            'Load_ID',
            'AlertTypeCode',
            'CurrentLoadOperationalStatusEnumVal',
            'Route',
            'StopSequenceNumber',
            'Supplier_Code',
            'Supplier_Name',
            'PlanIN_Datetime',
            'PlanOut_Datetime',
            'StopStatusEnumVal',
            'StopTypeEnumVal',
            'docCount',
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

    webix.ui(
        {
            view:"window", move:true,modal:true,id:$n("viewPrint"),
            head:{
            view:"toolbar", margin:-4,
            cols:
            [
                {view:"label", label: "ตัวอย่างเอกสาร",height:30},
                {},
                { view:"icon", icon:"times-circle",click:function(){ele('viewPrint').hide();ele('viewPrint_iframe').load('about:blank');}}
            ],
            },top:50,position:"center",width:window.innerWidth,height:window.innerHeight,
            body:
            {
                view:"scrollview", body:
                {
                    rows:
                    [
                        vw1("iframe",'viewPrint_iframe',"",{}),
                    ]
                }
            }
        });

    webix.ui({
        view:"window",modal:true,
        id:$n("winCreatePUS"),
        position:"center",width:window.innerWidth- 400,height:window.innerHeight,
        move:true,head:"",
        body:
        {
            rows:
            [
                {
                    view:"form",
                    paddingY:0,
                    id:$n('winCreatePUSForm1'),
                    elements:
                    [                        
                        vw1("text","Load_ID","ID",{hidden:true}),
                        vw1("text","t1_ID","ID",{hidden:true}),
                        {
                            cols:
                            [
                                vw1("text","planTimeOut","เวลาออก TTV",{type:'time'}),
                                vw1("text","planTimeIn","เวลากลับ TTV",{type:'time'}),
                                vw1("text","truckLicense","Truck License (ทะเบียนรถ)",{}),
                                vw1("combo","truckType","Truck Type (ชนิดรถ)",{options:['4W','6W','10',''],value:'6W'}),
                            ]
                        },
                        {
                            cols:
                            [
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
                            if (view.config.name == 'acTimeOut' || view.config.name == 'remark')
                            {
                                if(!ele('winCreatePUSBtnOK').isEnabled()) return;
                                view.blur();
                                ele('winCreatePUSBtnOK').callEvent("onItemClick", []);
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
                        vw1("button",'winCreatePUSBtnOK',"OK (ตกลง)",{type:'form',width:130,
                            on:{onItemClick:function()
                                {  
                                    var btn = this;
                        
                                    msBox('บันทึกข้อมูล',function()
                                    {
                                        ajax(fd,ele('winCreatePUSForm1').getValues(),21,function(json)
                                        {
                                            ele('winCreatePUS').hide();
                                            ele('find').callEvent("onItemClick", []);
                                            // setTable('dataT1',json.data);
                                            webix.message({ type:"default",expire:7000, text:'บันทึกสำเร็จ'});
                                        },btn,function(json)
                                        {
                                            
                                        });
                                    });
                                }
                            }
                        }),
                        vw1("button",'winCreatePUSCancel',"Cancel (ยกเลิก)",{type:'danger',width:130,on:{onItemClick:function(){ele('winCreatePUS').hide()}}}),
                        {}
                    ]
                }
            ]
        }
    });

	return {
        view: "scrollview",
        scroll: "native-y",
        id:"header_view_204",
        body: 
        {
        	id:"view_204_id",
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
                                vw1('datepicker','date1','Start Date (วันเริ่มต้น)',{format:webix.Date.dateToStr("%Y-%m-%d"),value:new Date(),stringResult:1,type:"date"}),
                                vw1('datepicker','date2','End Date (วันสิ้นสุด)',{format:webix.Date.dateToStr("%Y-%m-%d"),value:new Date(),stringResult:1,type:"date"}),
                                vw1('text','project','Project (โครงการ)',{
                                    suggest:
                                    {
                                        view: "suggest",
                                        on:
                                        {
                                            "onValueSuggest":function(ev)
                                            {
                                                var table = ele('AddnewTable');
                                                table.blockEvent();
                                                var editor = table.getEditState();
                                                setTimeout(function()
                                                {
                                                    table.unblockEvent();
                                                    nextCellHorizontal(table,editor);
                                                },10);
                                            },
                                        },
                                        body:
                                        {
                                            dataFeed:function(text)
                                            {
                                                this.clearAll();
                                                this.load("common/project.php?filter[value]="+text);
                                            },
                                        }
                                    },value:'ALL'
                                }),
                            ]
                        },
                        {
                            cols:
                            [
                                vw3("textarea","form1",'textUpload',"Text",{required:false,height:100}),
                            ]
                        },
                        {
                            view:"form",
                            paddingY:0,
                            id:$n("form2"),
                            elements:
                            [
                                {
                                    cols:
                                    [                                        
                                        vw1('button','find','Find (ค้นหา)',
                                            {
                                                on:
                                                {
                                                    onItemClick:function(id, e)
                                                    {
                                                        var btn  = this,dataT1=ele('dataT1'),obj={},loadID='';
                                                        btn.disable();

                                                        var text = ele('form1'+'textUpload').getValue();
                                                        
                                                        if(text.length>0)
                                                        {
                                                            var textAr = text.split("\n");
                                                            textAr.pop();
                                                            textAr = _.uniqBy(textAr);
                                                            if(textAr.length>0)
                                                            {
                                                                loadID = textAr.join(',');
                                                            }
                                                        }
                                                        
                                                        obj = ele('form1').getValues();
                                                        obj.loadID = loadID;
                                                        ajax(fd,obj,1,function(json)
                                                        {                                    
                                                            setTable('dataT1',json.data);                                                
                                                        },btn,function(json)
                                                        {

                                                        });
                                                    }
                                                }
                                            }
                                        ),                                        
                                        vw1('button','issued_document','Issued document(ออกเอกสาร)',
                                            {
                                                on:
                                                {
                                                    onItemClick:function(id, e)
                                                    {
                                                        var btn  = this,dataT1=ele('dataT1'),obj={},loadID='';
                                                        btn.disable();

                                                        var text = ele('form1'+'textUpload').getValue();
                                                        if(text.length>0)
                                                        {
                                                            var textAr = text.split("\n");
                                                            for(i=0,len=textAr.length;i<len;i++)
                                                            {
                                                                var row = textAr[i].split("\t");
                                                                if(row.length !== 7)
                                                                {
                                                                    btn.enable();
                                                                    break;
                                                                }
                                                                
                                                                var obj = {
                                                                    Load_ID : row[0],
                                                                    truckLicense : row[1],
                                                                    truckType : '6W',
                                                                    driverName : row[2],
                                                                    phone : row[3],
                                                                    remark:'',
                                                                    planTimeOut:'',
                                                                    planTimeIn:''
                                                                }
                                                                
                                                                setTimeout(function(item)
                                                                {
                                                                    ajax(fd,item,21,function(json)
                                                                    {
                                                                        webix.message({ type:"default",expire:7000, text:'บันทึกสำเร็จ'});
                                                                    },btn,function(json)
                                                                    {

                                                                    });
                                                                },i*30,obj);
                                                            }
                                                        }
                                                        
                                                    }
                                                }
                                            }
                                        ),
                                        vw1('button','ori','Print All',
                                            {
                                                on:
                                                {
                                                    onItemClick:function(id, e)
                                                    {                                                                                                                       
                                                        var text = ele('form1'+'textUpload').getValue();
                                                        if(text.length>0)
                                                        {
                                                            var textAr = text.split("\n");
                                                            textAr.pop();
                                                            textAr = _.uniqBy(textAr);
                                                            if(textAr.length>0)
                                                            {
                                                                ele('viewPrint').show();
                                                                ele('viewPrint_iframe').load("print/allEx.php?doctype="+textAr.join(','));
                                                            }
                                                        }
                                                                                                        
                                                    }
                                                },
                                                width:130
                                            }
                                        ),
                                        vw1('button','export','Export',
                                            {
                                                on:
                                                {
                                                    onItemClick:function(id, e)
                                                    {                                                        
                                                        $.fileDownload(fd, {
                                                            httpMethod: "POST",
                                                            data:{obj:ele('form1').getValues(),type:3},
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
                                        vw1('button','extra_route','Upload Add&Blowout()',
                                            {
                                                on:
                                                {
                                                    onItemClick:function(id, e)
                                                    {
                                                        var btn  = this,dataT1=ele('dataT1'),obj={},loadID='';
                                                        btn.disable();

                                                        var text = ele('form1'+'textUpload').getValue();
                                                        if(text.length>0)
                                                        {
                                                            var textAr = text.split("\n");
                                                            for(i=0,len=textAr.length;i<len;i++)
                                                            {
                                                                var row = textAr[i].split("\t");
                                                                if(row.length !== 2)
                                                                {
                                                                    btn.enable();
                                                                    break;
                                                                }
                                                                
                                                                var obj = {
                                                                    Load_ID : row[0],
                                                                    Work_Type : row[1],
                                                                }

                                                                /* 'NORMAL','ADD','BLOW OUT' */
                                                                setTimeout(function(item)
                                                                {
                                                                    ajax(fd,item,22,function(json)
                                                                    {
                                                                        webix.message({ type:"default",expire:7000, text:'บันทึกสำเร็จ'});
                                                                    },btn,function(json)
                                                                    {

                                                                    });
                                                                },i*30,obj);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        ),
                                        vw1('button','return_reload_ID','Return Canceled Load ID',
                                            {
                                                on:
                                                {
                                                    onItemClick:function(id, e)
                                                    {
                                                        var btn  = this,dataT1=ele('dataT1'),obj={},loadID='';
                                                        btn.disable();

                                                        var text = ele('form1'+'textUpload').getValue();
                                                        
                                                        if(text.length>0)
                                                        {
                                                            var textAr = text.split("\n");
                                                            textAr.pop();
                                                            textAr = _.uniqBy(textAr);
                                                            if(textAr.length>0)
                                                            {
                                                                loadID = textAr.join(',');
                                                            }
                                                        }

                                                        obj = ele('form1').getValues();
                                                        obj.Load_ID = loadID;
                                                        ajax(fd,obj,23,function(json)
                                                        {                                    
                                                            webix.message({ type:"default",expire:7000, text:'บันทึกสำเร็จ'});                            
                                                        },btn,function(json)
                                                        {

                                                        });
                                                    }
                                                }
                                            }
                                        )
                                    ]
                                }
                            ]
                        }
                    ]
                },
                {
                    view:"datatable",id:$n("dataT1"),navigation:true,
                    resizeColumn:true,autoheight:true,
                    hover:"myhover",threeState:true,pager:$n("Master_pagerA"),
                    datatype:"json",
                    scheme:{
                        $change:function(item)
                        {
                            if(item.Status == 'CANCEL')
                            {
                                item.$css = "highlight-gray";
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
                        { id:"data22",header:"&nbsp;",width:40,
                            template: function(row)
                            {   
                                
                                var noShow = ['S_TENDER_ACCEPTED']                                
                                if(row.docCount*1 == 0 && row.StopSequenceNumber*1 == 1)
                                {
                                    if(row.StopSequenceNumber*1 == 1)
                                    {
                                        if(noShow.indexOf(row.CurrentLoadOperationalStatusEnumVal) == -1)
                                        {
                                            return "<span style='cursor:pointer' class='webix_icon'></span>";
                                        }
                                        else
                                        {
                                            return "<span style='cursor:pointer' class='webix_icon fa-truck'></span>";
                                        }
                                    }
                                }
                                return "<span style='cursor:pointer' class='webix_icon'></span>";
                            }
                        },
                        { id:"data23",header:"&nbsp;",width:40,
                            template: function(row)
                            {   
                                if(row.StopSequenceNumber*1 == 1)
                                {
                                    return "<span style='cursor:pointer' class='webix_icon fa-file-text-o'></span>";
                                }
                                return "<span style='cursor:pointer' class='webix_icon'></span>";
                            }
                        },
                        { id:"data23",header:"&nbsp;",width:40,
                            template: function(row)
                            {   
                                var noShow = ['S_TENDER_ACCEPTED']
                                if(row.StopSequenceNumber*1 == 1)
                                {
                                    if(noShow.indexOf(row.Status) == -1)
                                    {
                                        return "<span style='cursor:pointer' class='webix_icon'></span>";
                                    }
                                    else
                                    {
                                        return "<span style='cursor:pointer' class='webix_icon fa-trash-o'></span>";
                                    }
                                }
                                return "<span style='cursor:pointer' class='webix_icon'></span>";
                            }
                        },
                        { id:"NO",header:"NO.",css:"rank",width:50},                        
                        { id:"projectName",header:["Project",{content:"selectFilter"}],width:90},
                        { id:"Load_ID",header:["Load ID",{content:"textFilter"}],width:90},
                        { id:"AlertTypeCode",header:["Type Code",{content:"selectFilter"}],width:150},
                        { id:"CurrentLoadOperationalStatusEnumVal",header:["JDA Status",{content:"selectFilter"}],width:150},
                        { id:"Route",header:["Route No.",{content:"textFilter"}],width:130},
                        { id:"StopSequenceNumber",header:["SEQ",{content:"textFilter"}],width:60},
                        { id:"Supplier_Code",header:["Supplier Code",{content:"textFilter"}],width:120},
                        { id:"Supplier_Name",header:["supplier Name",{content:"textFilter"}],width:280},
                        { id:"PlanIN_Datetime",header:["Plan IN",{content:"textFilter"},],width:140},
                        { id:"PlanOut_Datetime",header:["Plan OUT",{content:"textFilter"},],width:140},                        
                        { id:"StopStatusEnumVal",header:["JDA Stop Status",{content:"selectFilter"}],width:130},
                        { id:"StopTypeEnumVal",header:["Type",{content:"selectFilter"}],width:130},
                        { id:"docCount",header:["Doc Count",{content:"selectFilter"}],width:130},                        
                    ],
                    onClick:
                    {
                        "fa-truck":function(e,t)
                        {
                            var row = this.getItem(t);
                            row.truckType = '6W';
                            ele('winCreatePUS').show();
                            ele('winCreatePUSForm1').setValues(row);

                            $.post(fd, { obj: row, type: 2 })
                            .done(function (data) 
                            {
                                /* if(btn) btn.enable(); */
                                var json = JSON.parse(data);
                                if (json.ch == 1) 
                                {
                                    ele('truckLicense').setValue(json.data[0]['truckLicense']);
                                    ele('driverName').setValue(json.data[0]['driverName']);
                                    ele('phone').setValue(json.data[0]['phone']);
                                }

                            });
                            /* ajax(fd,row,2,function(json)
                            {
                                ele('truckLicense').setValue(json.data[0]['truckLicense']);
                                ele('driverName').setValue(json.data[0]['driverName']);
                                ele('phone').setValue(json.data[0]['phone']);
                            },null,function(json)
                            {
                            }); */
                        },
                        "fa-trash-o":function(e,t)
                        {
                            var row = this.getItem(t);
                            msBox('ยกเลิกเที่ยวรถ',function()
                            {
                                ajax(fd,row,22,function(json)
                                {
                                    ele('find').callEvent("onItemClick", []);
                                    webix.message({ type:"default",expire:7000, text:'บันทึกสำเร็จ'});
                                },null,function(json)
                                {

                                });
                            });
                        },
                        "fa-file-text-o":function(e,t)
                        {
                            var row = this.getItem(t);
                            ele('viewPrint').show();
                            var obj = {};
                            obj.printerName = 'TEST';
                            obj.copy = 1;
                            obj.doctype = row.Load_ID;
                            obj.printType = 'I';
                            obj.warter = 'NO';
                            ele('viewPrint_iframe').load("print/ex.php?printerName="+obj.printerName+"&copy="+obj.copy+"&doctype="+obj.doctype+"&printType="+obj.printType+"&warter="+obj.warter);
                        },
                    },
                },
                {
                    type:"wide",
                    cols:
                    [
                        {
                            view:"pager", id:$n("Master_pagerA"),
                            template:function(data, common){
                            var start = data.page * data.size
                            ,end = start + data.size;
                            if(data.count == 0) start = 0;
                            else start += 1;
                            if(end >= data.count) end = data.count;
                            var html = "<b>showing "+(start)+" - "+end+" total "+data.count+" </b>";
                            return common.first()+common.prev()+" "+html+" "+common.next()+common.last();
                            },
                            size:10,
                            group:5 
                        }
                    ]
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