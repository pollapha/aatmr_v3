var header_createDocumentInvoice = function()
{
	var menuName="createDocumentInvoice_",fd = "InvoiceManagement/"+menuName+"data.php";

    function init()
    {
        ele('remark').setValue('NONBAILMENT');
        ajax(fd,null,1,function(json)
        {
            if(json.header.length>0)
            {
                setInputInit(json.header[0]);
                setTable(json.body);
                focus('invoiceNo');
            }
            else
            {
            }
            btnEnable();
            inputEnable();
        });
    };

    function setInputInit(data)
    {
        ele('doc').setValue(data.doc);
        ele('invoiceDate').setValue(data.invoiceDate);
        ele('issuedDate').setValue(data.issuedDate);
        ele('code').setValue(data.code);
        ele('remark').setValue(data.remark);
    };

    function setTable(data)
    {
        var table = ele('dataT1');
        table.clearAll();
        if(data) table.parse(data);
    }

    function btnEnable()
    {
        if(ele('dataT1').count() == 0)
            _disable_([$n('btnSave')]);
        else 
            _enable_([$n('btnSave')]);
    };

    function inputEnable()
    {
        if(ele('doc').getValue() == '')
        {
            _disable_([$n('invoiceNo'),$n('btnEdit'),$n('btnCancel'),$n('dataT1')]);
            _enable_([$n('invoiceDate'),$n('issuedDate'),$n('code'),$n('remark'),$n('btnOK')]);
        }
        else 
        {
            _enable_([$n('invoiceNo'),$n('btnEdit'),$n('btnCancel'),$n('dataT1')]);
            _disable_([$n('invoiceDate'),$n('issuedDate'),$n('code'),$n('remark'),$n('btnOK')]);
        }
    };

    function inputEdit()
    {
        _disable_([$n('invoiceNo'),$n('btnEdit'),$n('btnCancel'),$n('dataT1')]);
        _enable_([$n('invoiceDate'),$n('issuedDate'),$n('code'),$n('remark'),$n('btnOK')]);
    };

    function clearInputWorking()
    {
        ele('invoiceNo').setValue('');
    };

    function clearInput()
    {
        ele('doc').setValue('');
        ele('invoiceDate').setValue(new Date());
        ele('issuedDate').setValue(new Date());
        ele('code').setValue('');
        // ele('remark').setValue('NONBAILMENT');
        ele('invoiceNo').setValue('');
    };

    function addHeader(btn = null)
    {
        if(ele('code').validate())
        {
            var obj = ele('form1').getValues();
            obj.invoiceDate = obj.invoiceDate.split(' ')[0];
            obj.issuedDate = obj.issuedDate.split(' ')[0];
            ajax(fd,obj,11,function(json)
            {
                setInputInit(json.header[0]);
                setTable(json.body);
                btnEnable();
                inputEnable();
                focus('invoiceNo');
            },btn,function()
            {
            });
        }
        else 
        {
            webix.message({ type: "error", expire: 7000, text: 'กรุณาป้อนข้อมูล' });
        }
    };

    function addBody(btn = null)
    {
        if(ele('invoiceNo').getValue().trim().length>0)
        {
            var obj = ele('form1').getValues();
            obj.invoiceDate = obj.invoiceDate.split(' ')[0];
            obj.issuedDate = obj.issuedDate.split(' ')[0];
            ajax(fd,obj,12,function(json)
            {
                setInputInit(json.header[0]);
                setTable(json.body);
                btnEnable();
                inputEnable();
                clearInputWorking();
                focus('invoiceNo');
            },btn,function()
            {
                ele('invoiceNo').setValue('');
                focus('invoiceNo');
            });
        }
        else 
        {
            webix.message({ type: "error", expire: 7000, text: 'กรุณาป้อนข้อมูล' });
        }
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

    

	return {
        view: "scrollview",
        scroll: "native-y",
        id:"header_createDocumentInvoice",
        body: 
        {
        	id:"createDocumentInvoice_id",
        	type:"clean",
    		rows:
    		[
    		    {
                    view:"form",
                    paddingY:0,
                    id:$n('form1'),
                    on:
                    {
                        "onSubmit":function(view,e)
                        {
                            if (view.config.name == 'remark')
                            {
                                view.blur();
                                addHeader();
                            }
                            else if (view.config.name == 'invoiceNo')
                            {
                                view.blur();
                                addBody();
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
                    elements:
                    [
                        {
                            cols:
                            [
                                {view:"text",label:'Document No (งานค้าง)',id:$n('doc'),name:"doc",value:"",labelPosition:"top",hidden:false},
                                {
                                    view:"datepicker",required:true,align:"left",label:'Invoice Date',labelPosition:"top",value:new Date(),
                                    id:$n('invoiceDate'),name:"invoiceDate",stringResult:1,format:"%Y-%m-%d"
                                },
                                {
                                    view:"datepicker",required:true,align:"left",label:'Issued Date',labelPosition:"top",value:new Date(),
                                    id:$n('issuedDate'),name:"issuedDate",stringResult:1,format:"%Y-%m-%d"
                                },
                                
                                {view:"text",label:'Supplier (รหัสผู้ผลิต)',id:$n('code'),name:"code",labelPosition:"top",value:"",required:true,suggest:
                                    {
                                        body: 
                                        {
                                            dataFeed: function(text){inputFeed(this,fd+"?type=3&code="+text);},
                                        }
                                    },on:
                                    {
                                        onKeyPress:function(code, e){inputEnter(this,code,e)}
                                    }
                                },
                                {view:"combo",label:'Remark (หมายเหตุ)',id:$n('remark'),name:"remark",labelPosition:"top",value:"",options:["E-SMART","PXP","862"]},
                                {
                                    rows:
                                    [
                                        {},
                                        vw1("button",'btnOK',"OK (ตกลง)",{width:130,on:
                                            {
                                                onItemClick:function()
                                                {
                                                    addHeader(this);
                                                }
                                            }
                                        }),
                                    ]
                                },
                            ]
                        },
                        {
                            cols:
                            [
                                {view:"text",label:'Invoice No (หมายเลขอินวอย)',id:$n('invoiceNo'),name:"invoiceNo",labelPosition:"top",value:""},
                                {
                                    rows:
                                    [
                                        {},
                                        {
                                            cols:
                                            [
                                                vw1("button",'btnEdit',"Edit (แก่ใข)",{width:130,on:
                                                    {
                                                        onItemClick:function()
                                                        {
                                                            inputEdit();
                                                        }
                                                    }
                                                }),
                                                vw1("button",'btnCancel',"Cancel (ยกเลิก)",{width:130,type:'danger',on:
                                                    {
        
                                                    }
                                                }),
                                                vw1("button",'btnSave',"Save (บันทึก)",{width:130,type:'form',on:
                                                    {
                                                        onItemClick:function()
                                                        {
                                                            var btn = this,obj=ele('form1').getValues();
                                                            msBox('บันทึกข้อมูล',function()
                                                            {
                                                                ajax(fd,obj,41,function(json)
                                                                {
                                                                    clearInput();
                                                                    setTable(null);
                                                                    btnEnable();
                                                                    inputEnable();
                                                                    webix.message({ type: "", expire: 7000, text: 'บันทึกสำเร็จ' });
                                                                },btn,function()
                                                                {

                                                                });
                                                            });
                                                        }
                                                    }
                                                }),
                                            ]
                                        }
                                    ]
                                },
                            ]
                        },
                    ]
                },
                {
                    view: "datatable", id:$n('dataT1'),headerRowHeight:25,
                    autoheight: true, datatype: "json",rowLineHeight:25, rowHeight:25,
                    footer: true, hover: "myhover", navigation: true, resizeColumn: true,
                    fixedRowHeight: false,
                    scheme:
                    {

                    },
                    columns:
                    [
                        { id:"fa-trash-o",header: "&nbsp;",width: 35,
                            template: "<span style='color:#777777;cursor:pointer;' class='webix_icon fa-trash-o'></span>"
                        },
                        { id:"NO",header:"No",css:"rank",width:50},
                        { id:"invoiceNo",header:"Invoice No.",width:150},
                    ],
                    onClick:
                    {
                        "fa-trash-o":function(e,t)
                        {
                            var row = this.getItem(t),obj={};
                            obj.ID = row.ID;
                            obj.headerID = row.headerID;
                            msBox('ลบข้อมูล',function()
                            {
                                ajax(fd,obj,31,function(json)
                                {
                                    setInputInit(json.header[0]);
                                    setTable(json.body);
                                    btnEnable();
                                    inputEnable();
                                    clearInputWorking();
                                    focus('invoiceNo');
                                },null,function()
                                {
                                    ele('invoiceNo').setValue('');
                                    focus('invoiceNo');
                                });
                            });
                        }
                    }
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
                    _disable_([$n('doc'),$n('invoiceDate'),$n('issuedDate'),$n('code'),$n('remark'),$n('btnOK'),
                    $n('invoiceNo'),$n('btnEdit'),$n('btnCancel'),$n('btnSave'),$n('dataT1')]);
                    init();
                }
            }
        }
    };
};