var header_transaction = function()
{
	var menuName="transaction_",fd = "h1/"+menuName+"data.php";

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

    function setTable(name,data)
    {
        var eName = ele(name);
        eName.clearAll();
        eName.parse(data,"json");
        eName.filterByAll();
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

        var f = 
        [
            "NO",
            "Load_ID",
            "Work_Type",
            "Route",
            "StopSequenceNumber",
            "Supplier_Code",
            "Supplier_Name",
            "Status",
            "JDA_Status",
            "StopStatusEnumVal",
            "operration_date",
            "docDate",
            "docTime",
            "acDocTime",
            "lateTime",
            "acOutDocTime",
            "tripTTV",
            "truckLicense",
            "truckType",
            "driverName",
            "phone",
            "type",
            "remark",
            "cDateTime",
            "createBy",
            "upDateINTime",
            "SendJDA_ActualIN",
            "upDateINBy",
            "upDateOUTTime",
            "SendJDA_ActualOut",
            "upDateOUTBy",
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
        id:$n("winUpdate"),
        position:"center",width:window.innerWidth- 400,height:window.innerHeight,
        move:true,head:"",
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
                                if(!ele('winUpdateBtnOK').isEnabled()) return;
                                view.blur();
                                ele('winUpdateBtnOK').callEvent("onItemClick", []);
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
                        vw1("button",'winUpdateBtnOK',"OK (ตกลง)",{type:'form',width:130,
                            on:{onItemClick:function()
                                {  
                                    var btn = this;
                        
                                    msBox('บันทึกข้อมูล',function()
                                    {
                                        ajax(fd,ele('winUpdateForm1').getValues(),21,function(json)
                                        {
                                            ele('winUpdate').hide();
                                            ele('find').callEvent("onItemClick", []);
                                            webix.message({ type:"default",expire:7000, text:'บันทึกสำเร็จ'});
                                        },btn,function(json)
                                        {
                                            
                                        });
                                    });
                                }
                            }
                        }),
                        vw1("button",'winUpdateCancel',"Cancel (ยกเลิก)",{type:'danger',width:130,on:{onItemClick:function(){ele('winUpdate').hide()}}}),
                        {}
                    ]
                }
            ]
        }
    });

	return {
        view: "scrollview",
        scroll: "native-y",
        id:"header_transaction",
        body: 
        {
        	id:"transaction_id",
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
                                vw1('text','b2','Bol (รหัสติดตาม)',{}),
                            ]
                        },
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
                                                var btn  = this,dataT1=ele('dataT1'),obj={};
                                                btn.disable();
                                                ajax(fd,ele('form1').getValues(),1,function(json)
                                                {                                    
                                                    setTable('dataT1',json.data);                                                
                                                },btn,function(json)
                                                {

                                                });
                                            }
                                        },width:130
                                    }
                                ),
                                {},{},
                                vw1('button','export','Export',
                                {
                                    on:
                                    {
                                        onItemClick:function(id, e)
                                        {
                                            exportExcel(this);
                                            /* $.fileDownload(fd, {
                                                httpMethod: "POST",
                                                data:{obj:ele('form1').getValues(),type:2},
                                                successCallback: function (url) {
                                                },
                                                prepareCallback: function (url) {
                                                },
                                                failCallback: function (responseHtml, url) {
                
                                                }
                                            }); */
                                        }
                                    },
                                    width:130
                                }
                            ),
                                
                            ]
                        },
                        {
                            view:"datatable",id:$n("dataT1"),navigation:true,
                            resizeColumn:true,autoheight:true,
                            hover:"myhover",threeState:true,pager:$n("Master_pagerA"),
                            datatype:"json",
                            scheme:{
                                $change:function(item){
                               
                                }
                            },
                            columns:
                            [
                                { id:"data22",header:"&nbsp;",width:40,
                                    template: function(row)
                                    {   
                                        if(row.StopSequenceNumber*1 == 1 && row.JDA_Status =='S_TENDER_ACCEPTED')
                                        {
                                            return "<span style='cursor:pointer' class='webix_icon fa-pencil'></span>";
                                        }
                                        return "<span style='cursor:pointer' class='webix_icon'></span>";
                                    }
                                },
                                { id:"data23",header:"&nbsp;",width:40,
                                    template: function(row)
                                    {   
                                        if(row.StopSequenceNumber*1 == 1 && row.status == 'COMPLETED')
                                        {
                                            return "<span style='cursor:pointer' class='webix_icon fa-external-link'></span>";
                                        }
                                        return "<span style='cursor:pointer' class='webix_icon'></span>";
                                    }
                                },                                
                                {id:"NO",header:"No",css:"rank",width:50},
                                {id:"Load_ID",header:["Tracking",{content:"textFilter"}],width:100,editor:"text"},
                                {id:"Work_Type",header:["Work Type",{content:"textFilter"}],width:100,editor:"text"},
                                {id:"Route",header:["Route",{content:"textFilter"}],width:100,editor:"text"},
                                {id:"StopSequenceNumber",header:"SEQ",width:50,editor:"text"},
                                {id:"Supplier_Code",header:["Supplier Code",{content:"textFilter"}],width:140,editor:"text"},
                                {id:"Supplier_Name",header:["Supplier Name",{content:"textFilter"}],width:200,editor:"text"},
                                {id:"Status",header:["Status",{content:"textFilter"}],width:150,editor:"text"},
                                {id:"JDA_Status",header:["JDA Status",{content:"textFilter"}],width:150,editor:"text"},
                                {id:"StopStatusEnumVal",header:["JDA Stop Status",{content:"textFilter"}],width:130},                                                                      
                                {id:"operration_date",header:"Operration Date",width:150,editor:"text"},
                                {id:"docDate",header:"Due Date",width:150,editor:"text"},
                                {id:"docTime",header:"Due Time IN",width:150,editor:"text"},
                                {id:"acDocTime",header:"Actual Time IN",width:150,editor:"text"},
                                {id:"lateTime",header:"Due Time OUT",width:150,editor:"text"},
                                {id:"acOutDocTime",header:"Actual Time OUT",width:150,editor:"text"},
                                {id:"tripTTV",header:"Trip TTV",width:100,editor:"text"},
                                {id:"truckLicense",header:"Truck No",width:120,editor:"text"},
                                {id:"truckType",header:"Truck Type",width:80,editor:"text"},
                                {id:"driverName",header:"Driver Name",width:200,editor:"text"},
                                {id:"phone",header:"Phone",width:120,editor:"text"},
                                {id:"type",header:"Type",width:100,editor:"text"},                                
                                {id:"remark",header:"Remark",width:200,editor:"text"},
                                {id:"cDateTime",header:"Create Date",width:140,editor:"text"},
                                {id:"createBy",header:"Create By",width:200,editor:"text"},
                                {id:"upDateINTime",header:"Update IN Date",width:140,editor:"text"},
                                {id:"SendJDA_ActualIN",header:"JDA Update IN Date",width:140,editor:"text"},
                                {id:"upDateINBy",header:"Update IN By",width:200,editor:"text"},
                                {id:"upDateOUTTime",header:"Update OUT Date",width:140,editor:"text"},
                                {id:"SendJDA_ActualOut",header:"JDA Update OUT Date",width:140,editor:"text"},
                                {id:"upDateOUTBy",header:"Update OUT By",width:200,editor:"text"},                              
                            ],
                            onClick:
                            {
                                "fa-pencil":function(e,t)
                                {
                                    var row = this.getItem(t);
                                    ele('winUpdate').show();
                                    ele('winUpdateForm1').setValues(row);
                                },
                                "fa-external-link":function(e,t)
                                {
                                    var row = this.getItem(t);
                                    ajax('edi214_userClick.php',row,1,function(json)
                                    {                                    
                                        webix.message({ type:"default",expire:7000, text:json.data});
                                    },null,function(json)
                                    {
                                        webix.message({ type:"default",expire:7000, text:json.data});
                                    });
                                    
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