var header_invoiceTransaction = function()
{
	var menuName="invoiceTransaction_",fd = "InvoiceManagement/"+menuName+"data.php";

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

    function btnFind(btn)
    {
        var obj = ele('form1').getValues();
        ajax(fd,obj,1,function(json)
        {
            if(json.data.length == 0 ) 
                webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:'ไม่พบข้อมูล',callback:function(){}}); 
            setTable('dataT1',json.data);
        },btn,function(json)
        {
            setTable('dataT1',[]);
        });
    };

    function setTable(name,data)
    {
        ele(name).clearAll();
        ele(name).parse(data);
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

        var f = ['NO','doc','status','invoiceDate','invoiceNo','supplierCode','supplierName','truckLicense','driverName',
        'phone','remark','issuedDate','outDate','supReciverName','supReciverDate','inDate','remark','remarkDetail',
        'createByName','createDateTime','updateByName','updateDateTime'];


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
            
            var worker = new Worker('js/workerToExcel.js?v=1');
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
            id:$n("winUpdate"),
            top:50,position:"center",width:window.innerWidth- 100,height:window.innerHeight-200,
            move:true,head:"แก้ใข",
            body:
            {
                rows:
                [
                    {
                        view:"datatable",id:$n("dataT2"),navigation:true,
                        resizeColumn:true,autoheight:false,
                        threeState:true,rowLineHeight:25,rowHeight:25,
                        datatype:"json",headerRowHeight:25,editable:true,
                        columns:
                        [
                            { id:"status",header:["Status",{content:"textFilter"}],editor:"combo",options:['PENDING','IN TRANSIT','CLOSED','CANCEL'],width:120},
                            { id:"invoiceDate",header:["Invoice Date",{content:"textFilter"}],editor:"date",width:140,
                                format:webix.Date.dateToStr("%Y-%m-%d"),editFormat:webix.Date.dateToStr("%Y-%m-%d"),
                                editParse:webix.Date.dateToStr("%Y-%m-%d")
                            },
                            { id:"issuedDate",header:["Issued Date",{content:"textFilter"}],editor:"date",width:100,
                                format:webix.Date.dateToStr("%Y-%m-%d"),editFormat:webix.Date.dateToStr("%Y-%m-%d"),
                                editParse:webix.Date.dateToStr("%Y-%m-%d")
                            },
                            { id:"invoiceNo",header:["Invoice No.",{content:"textFilter"}],editor:"text",width:100},
                            { id:"supplierCode",header:["Supplier Code",{content:"textFilter"}],editor:"text",width:140,suggest:
                                {
                                    body: 
                                    {
                                        dataFeed: function(text){inputFeed(this,fd+"?type=5&code="+text);},
                                    }
                                }
                            },
                            { id:"supReciverName",header:["ผู้รับเอกสาร",{content:"textFilter"}],editor:"text",width:150},
                            { id:"supReciverDate",header:["วันที่รับ",{content:"textFilter"}],editor:"date",width:100,
                                format:webix.Date.dateToStr("%Y-%m-%d"),editFormat:webix.Date.dateToStr("%Y-%m-%d"),
                                editParse:webix.Date.dateToStr("%Y-%m-%d")
                            },
                            { id:"truckLicense",header:["Truck NO.",{content:"textFilter"}],editor:"text",width:110},
                            { id:"driverName",header:["Driver Name",{content:"textFilter"}],editor:"text",width:150},
                            { id:"phone",header:["Phone",{content:"textFilter"}],editor:"text",width:150},
                            { id:"remark",header:["Project",{content:"textFilter"}],editor:"text",width:150},
                            { id:"remarkDetail",header:["Remark",{content:"textFilter"}],editor:"text",width:150},
                        ],
                    },
                    {
                        cols:
                        [
                            {},
                            vw1("button",'winUpdateBtnOK',"OK (ตกลง)",{type:'form',width:130,on:
                            {onItemClick:function()
                                {
                                    var btn = this;
                                    msBox('บันทึกข้อมูล',function()
                                    {
                                        var datat1 = ele('dataT2'),obj={};
                                        obj = datat1.getItem(datat1.getFirstId());
                                        obj.date1 = ele('date1').getValue();
                                        obj.date2 = ele('date2').getValue();
                                        ajax(fd,obj,21,function(json)
                                        {
                                            ele('winUpdate').hide();
                                            setTable('dataT1',json.data);
                                            webix.message({ type:"default",expire:7000, text:'บันทึกสำเร็จ'});
                                        },btn,function(json)
                                        {
                                            
                                        });
                                    });
                                }
                            }}),
                            vw1("button",'winUpdateCancel',"Cancel (ยกเลิก)",{width:130,on:{onItemClick:function(){ele('winUpdate').hide()}}}),
                            {}
                        ]
                    }
                ]
            }
        });

        webix.ui({
            view:"window",modal:true,
            id:$n("winAdd"),
            top:50,position:"center",width:window.innerWidth- 800,height:window.innerHeight-200,
            move:true,head:"เพิ่ม Invoice",
            body:
            {
                rows:
                [
                    {
                        view:"datatable",id:$n("dataT3"),navigation:true,
                        resizeColumn:true,autoheight:false,
                        threeState:true,rowLineHeight:25,rowHeight:25,
                        datatype:"json",headerRowHeight:25,editable:true,
                        columns:
                        [
                            { id:"invoiceNo",header:["Invoice No.",{content:"textFilter"}],editor:"text",width:100},
                        ],
                    },
                    {
                        cols:
                        [
                            {},
                            vw1("button",'winAddBtnOK',"OK (ตกลง)",{type:'form',width:130,on:
                            {onItemClick:function()
                                {
                                    var btn = this;
                                    msBox('บันทึกข้อมูล',function()
                                    {
                                        var datat1 = ele('dataT3'),obj={};
                                        obj = datat1.getItem(datat1.getFirstId());
                                        obj.date1 = ele('date1').getValue();
                                        obj.date2 = ele('date2').getValue();
                                        ajax(fd,obj,22,function(json)
                                        {
                                            ele('winAdd').hide();
                                            setTable('dataT1',json.data);
                                            webix.message({ type:"default",expire:7000, text:'บันทึกสำเร็จ'});
                                        },btn,function(json)
                                        {
                                            
                                        });
                                    });
                                }
                            }}),
                            vw1("button",'winAddCancel',"Cancel (ยกเลิก)",{width:130,on:{onItemClick:function(){ele('winAdd').hide()}}}),
                            {}
                        ]
                    }
                ]
            }
        });  

	return {
        view: "scrollview",
        scroll: "native-y",
        id:"header_invoiceTransaction",
        body: 
        {
        	id:"invoiceTransaction_id",
        	type:"clean",
    		rows:
    		[
    		    {
                    view:"form",
                    paddingY:0,
                    id:$n('form1'),
                    elements:
                    [
                        {
                            cols:
                            [
                                vw1("datepicker",'date1',"Start Date (วันเริ่มต้น)",
                                {type:"date",format:webix.Date.dateToStr("%Y-%m-%d"),value:new Date(),stringResult:1,
                                    on:
                                    {
                                        onChange:function(newv, oldv)
                                        {
                                            ele('date2').setValue(newv);
                                        }
                                    }
                                }),
                                vw1("datepicker",'date2',"End Date (วันสิ้นสุด)",{type:"date",format:webix.Date.dateToStr("%Y-%m-%d"),value:new Date(),stringResult:1}),
                            ]
                        },
                    ]
                },
                {
                    view:"form",
                    paddingY:0,
                    id:$n('form2'),
                    elements:
                    [
                        {
                            cols:
                            [
                                vw1("button",'btnFind',"find (ค้นหา)",{width:130,on:{onItemClick:function(){btnFind(this);}}}),
                                {},
                                vw1("button",'exportExcel',"export Excel",{width:130,on:{onItemClick:function(){exportExcel(this);}}}),
                            ]
                        },
                    ]
                },
                {
                    view:"datatable",id:$n("dataT1"),navigation:true,
                    resizeColumn:true,autoheight:false,
                    hover:"myhover",threeState:true,rowLineHeight:25,rowHeight:25,
                    datatype:"json",headerRowHeight:25,leftSplit:4,
                    columns:
                    [
                        { id:"data21",header:"&nbsp;",width:40,
                            template: function(row)
                            {   
                                return "<span style='cursor:pointer' class='webix_icon fa-file-pdf-o'></span>";
                            }
                        },
                        { id:"data22",header:"&nbsp;",width:40,
                            template: function(row)
                            {   
                                return "<span style='cursor:pointer' class='webix_icon fa-pencil'></span>";
                            }
                        },
                        { id:"data23",header:"&nbsp;",width:40,
                            template: function(row)
                            {   
                                return "<span style='cursor:pointer' class='webix_icon fa-plus'></span>";
                            }
                        },
                        { id:"NO",header:"NO.",css:"rank",width:50},
                        { id:"doc",header:["DOC NO.",{content:"textFilter"}],width:150},
                        { id:"status",header:["Status",{content:"textFilter"}],width:120},
                        { id:"invoiceDate",header:["Invoice Date",{content:"textFilter"}],width:140},
                        { id:"invoiceNo",header:["Invoice No.",{content:"textFilter"}],width:100},
                        { id:"supplierCode",header:["Supplier Code",{content:"textFilter"}],width:140},
                        { id:"supplierName",header:["Supplier Name",{content:"textFilter"}],width:180},
                        { id:"truckLicense",header:["Truck NO.",{content:"textFilter"}],width:110},
                        { id:"driverName",header:["Driver Name",{content:"textFilter"}],width:150},
                        { id:"phone",header:["Phone",{content:"textFilter"}],width:150},
                        { id:"remark",header:["Remark",{content:"textFilter"}],width:150},
                        { id:"issuedDate",header:["Issued Date",{content:"textFilter"}],width:100},
                        { id:"outDate",header:["เอกสารออกจาก TTV",{content:"textFilter"}],width:220},
                        { id:"supReciverName",header:["ผู้รับเอกสาร",{content:"textFilter"}],width:220},
                        { id:"supReciverDate",header:["วันที่รับ",{content:"textFilter"}],width:220},
                        { id:"inDate",header:["เอกสารกลับมาที่ TTV",{content:"textFilter"}],width:220},
                        { id:"remark",header:["Project",{content:"textFilter"}],width:150},
                        { id:"remarkDetail",header:["Remark",{content:"textFilter"}],width:150},
                        { id:"createByName",header:["Create Name"],width:150},
                        { id:"createDateTime",header:["Create Date"],width:150},
                        { id:"updateByName",header:["Update Name"],width:150},
                        { id:"updateDateTime",header:["Update Date"],width:150},	
                    ],
                    onClick:
                    {
                        "fa-trash-o":function(e,t)
                        {
                            var row = this.getItem(t);
                            msBox('ยกเลิกรายการนี้',function()
                            {
                                var datat1 = ele('dataT4'),obj={};
                                obj.headerID = row.headerID;
                                obj.bodyID = row.bodyID;
                                obj.date1 = ele('date1').getValue();
                                obj.date2 = ele('date2').getValue();
                                ajax(fd,obj,25,function(json)
                                {
                                    setTable1(json.data);
                                    webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:'บันทึกสำเร็จ',callback:function(){}});
                                },null,function(json)
                                {
                                    
                                });
                            });
                        },
                        "fa-file-pdf-o":function(e,t)
                        {
                            var row = this.getItem(t);
                            ele('viewPrint').show();
                            var obj = {};
                                obj.printerName = 'TEST';
                                obj.copy = 1;
                                obj.doctype = row.doc;
                                obj.printType = 'I';
                                obj.warter = 'NO';
                            ele('viewPrint_iframe').load("print/inv.php?printerName="+obj.printerName+"&copy="+obj.copy+"&doctype="+obj.doctype+"&printType="+obj.printType+"&warter="+obj.warter);
                        },
                        
                        "fa-pencil":function(e,t)
                        {
                            var row = this.getItem(t),obj={};
                            obj.status = row.status;
                            obj.invoiceDate = row.invoiceDate;
                            obj.issuedDate = row.issuedDate;
                            obj.invoiceNo = row.invoiceNo;
                            obj.supplierCode = row.supplierCode;
                            obj.truckLicense = row.truckLicense;
                            obj.driverName = row.driverName;
                            obj.phone = row.phone;
                            obj.remark = row.remark;
                            obj.headerID = row.headerID;
                            obj.bodyID = row.bodyID;
                            obj.supReciverName = row.supReciverName;
                            obj.supReciverDate = row.supReciverDate;
                            obj.remarkDetail = row.remarkDetail;
                            setTable('dataT2',obj);
                            ele('winUpdate').show();
                        },
                        "fa-plus":function(e,t)
                        {
                            var row = this.getItem(t),obj={};
                            obj.invoiceNo = '';
                            obj.headerID = row.headerID;
                            setTable('dataT3',obj);
                            ele('winAdd').show();
                        },
                    },
                    on:
                    {
                    
                    },
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