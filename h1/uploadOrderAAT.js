var header_uploadOrderAAT = function()
{
	var menuName="uploadOrderAAT_",fd = "h1/"+menuName+"data.php";

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
    };

    function val(name)
    {
        return ele(name).getValue();
    };

    webix.ui(
        {
            view:"window", move:true,modal:true,id:$n("win_upload"),
            top:100,position:"center",head:
            {
                view:"toolbar", margin:-4,
                cols:
                [
                    {view:"label", label: "Upload File" },
                    {},
                    { view:"icon", icon:"times-circle",on:
                        {
                            onItemClick:function(id, e)
                            {
                                ele('win_upload').hide();ele('upload').files.clearAll();
                            }
                        }
                    }
                ],
            },
            body:
            {
                view:"form", scroll:false,id:$n("win_form1"),width:450,
                elements:
                [
                    {
                        cols:
                        [
                            {
                                rows:
                                [
                                    { 
                                        view: "uploader", value: 'Select File(กดเพื่อเลือกไฟล์)', 
                                        multiple:false, autosend:false,
                                        name:"uploader",id:$n("upload"),
                                        link:$n("list"),upload:fd+"?type=51",
                                        on:{
                                            onBeforeFileAdd:function(item){
                                                var type = item.type.toLowerCase();
                                                if (type == "xlsx" || type == "xls"){
                                                    
                                                }
                                                else
                                                {
                                                    webix.message("ลองรับเฉพาะ xlsx,xls เท่านั้น");
                                                    return false;
                                                }
                                            },
                                            onFileUpload:function(item){
                                            },
                                            onFileUploadError:function(item){
                                                 webix.alert("Error during file upload");
                                            },
                                            onUploadComplete:function(data)
                                            {
                                                ele("upload").files.clearAll();
                                                // webix.alert("Error during file upload");
                                                ele('win_upload').hide();
                                            }
                                        },
                                    },
                                    {
                                        view:"list",id:$n("list"), type:"uploader",
                                        autoheight:true, borderless:true,on:
                                        {
                                            onAfterRender:function()
                                            {
                                                if(this.count() >0) ele('save').show();
                                                else ele('save').hide();
                                            }
                                        }
                                    },
                                    { view:"button",label:"Upload (อัพโหลด)",id:$n("save"),type:'form',hidden:true, click: function() 
                                        {
                                            var btn = ele('save');
                                            btn.disable();
                                            ele("upload").send(function(response)
                                            {
                                                if(response)
                                                {
                                                    btn.enable();
                                                    webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:response.mms,callback:function(){}});
                                                }
                                            });
                                        }
                                    }
                                ]
                            },
                            
                        ]
                    }
                ]
            }
        });

        webix.ui(
        {
            view:"window", move:true,modal:true,id:$n("win_uploadFTM"),
            top:100,position:"center",head:
            {
                view:"toolbar", margin:-4,
                cols:
                [
                    {view:"label", label: "Upload File FTM" },
                    {},
                    { view:"icon", icon:"times-circle",on:
                        {
                            onItemClick:function(id, e)
                            {
                                ele('win_uploadFTM').hide();ele('uploadFTM').files.clearAll();
                            }
                        }
                    }
                ],
            },
            body:
            {
                view:"form", scroll:false,id:$n("win_form1FTM"),width:450,
                elements:
                [
                    {
                        cols:
                        [
                            {
                                rows:
                                [
                                    { 
                                        view: "uploader", value: 'Select File(กดเพื่อเลือกไฟล์)', 
                                        multiple:false, autosend:false,
                                        name:"uploader",id:$n("uploadFTM"),
                                        link:$n("listFTM"),upload:fd+"?type=52",
                                        on:{
                                            onBeforeFileAdd:function(item){
                                                var type = item.type.toLowerCase();
                                                if (type == "xlsx" || type == "xls"){
                                                    
                                                }
                                                else
                                                {
                                                    webix.message("ลองรับเฉพาะ xlsx,xls เท่านั้น");
                                                    return false;
                                                }
                                            },
                                            onFileUpload:function(item){
                                            },
                                            onFileUploadError:function(item){
                                                    webix.alert("Error during file upload");
                                            },
                                            onUploadComplete:function(data)
                                            {
                                                ele("uploadFTM").files.clearAll();
                                                // webix.alert("Error during file upload");
                                                ele('win_uploadFTM').hide();
                                            }
                                        },
                                    },
                                    {
                                        view:"list",id:$n("listFTM"), type:"uploader",
                                        autoheight:true, borderless:true,on:
                                        {
                                            onAfterRender:function()
                                            {
                                                if(this.count() >0) ele('saveFTM').show();
                                                else ele('saveFTM').hide();
                                            }
                                        }
                                    },
                                    { view:"button",label:"Upload (อัพโหลด)",id:$n("saveFTM"),type:'form',hidden:true, click: function() 
                                        {
                                            var btn = ele('saveFTM');
                                            btn.disable();
                                            ele("uploadFTM").send(function(response)
                                            {
                                                if(response)
                                                {
                                                    btn.enable();
                                                    webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:response.mms,callback:function(){}});
                                                }
                                            });
                                        }
                                    },
                                ]
                            },
                            
                        ]
                    }
                ]
            }
        });

        webix.ui(
            {
                view:"window", move:true,modal:true,id:$n("win_uploadNewFormat1"),
                top:100,position:"center",head:
                {
                    view:"toolbar", margin:-4,
                    cols:
                    [
                        {view:"label", label: "Upload File" },
                        {},
                        { view:"icon", icon:"times-circle",on:
                            {
                                onItemClick:function(id, e)
                                {
                                    ele('win_uploadNewFormat1').hide();ele('uploadNewFormat').files.clearAll();
                                }
                            }
                        }
                    ],
                },
                body:
                {
                    view:"form", scroll:false,id:$n("win_form1NewFormat"),width:450,
                    elements:
                    [
                        {
                            cols:
                            [
                                {
                                    rows:
                                    [
                                        { 
                                            view: "uploader", value: 'Select File(กดเพื่อเลือกไฟล์)', 
                                            multiple:false, autosend:false,
                                            name:"uploader",id:$n("uploadNewFormat"),
                                            link:$n("listNewFormat"),upload:fd+"?type=53",
                                            on:{
                                                onBeforeFileAdd:function(item){
                                                    var type = item.type.toLowerCase();
                                                    if (type == "xlsx" || type == "xls"){
                                                        
                                                    }
                                                    else
                                                    {
                                                        webix.message("ลองรับเฉพาะ xlsx,xls เท่านั้น");
                                                        return false;
                                                    }
                                                },
                                                onFileUpload:function(item){
                                                },
                                                onFileUploadError:function(item){
                                                        webix.alert("Error during file upload");
                                                },
                                                onUploadComplete:function(data)
                                                {
                                                    ele("uploadNewFormat").files.clearAll();
                                                    // webix.alert("Error during file upload");
                                                    ele('win_uploadNewFormat1').hide();
                                                }
                                            },
                                        },
                                        {
                                            view:"list",id:$n("listNewFormat"), type:"uploader",
                                            autoheight:true, borderless:true,on:
                                            {
                                                onAfterRender:function()
                                                {
                                                    if(this.count() >0) ele('saveNewFormat').show();
                                                    else ele('saveNewFormat').hide();
                                                }
                                            }
                                        },
                                        { view:"button",label:"Upload (อัพโหลด)",id:$n("saveNewFormat"),type:'form',hidden:true, click: function() 
                                            {
                                                var btn = ele('saveNewFormat');
                                                btn.disable();                                                
                                                ele("uploadNewFormat").send(function(response)
                                                {
                                                    console.log(response);
                                                    if(response)
                                                    {
                                                        btn.enable();
                                                        webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:response.mms,callback:function(){}});
                                                    }
                                                });
                                            }
                                        },
                                    ]
                                },
                                
                            ]
                        }
                    ]
                }
            });

	return {
        view: "scrollview",
        scroll: "native-y",
        id:"header_uploadOrderAAT",
        body: 
        {
        	id:"uploadOrderAAT_id",
        	type:"clean",
    		rows:
    		[
    		    {
                    cols:
                    [
                        {},
                        {view:"button",value:"Upload (อัพโหลด)",id:$n("pop_upload"),on:
                            {
                                onItemClick:function(id, e)
                                {
                                    ele('win_upload').show();
                                }
                            }
                        },
                        {view:"button",value:"Upload FTM (อัพโหลดเอฟทีเอ็ม)",id:$n("pop_uploadFTM"),on:
                            {
                                onItemClick:function(id, e)
                                {
                                    ele('win_uploadFTM').show();
                                }
                            }
                        },          
                        {view:"button",value:"Upload New Format (อัพโหลด)",id:$n("pop_uploadNewFormat"),on:
                            {
                                onItemClick:function(id, e)
                                {
                                    ele('win_uploadNewFormat1').show();
                                }
                            }
                        },         
                        {}
                    ]
                },
                {
                    cols:
                    [
                        vw1('text','LOAD_ID','LOAD ID',{}),
                        {
                            rows:
                            [
                                {},
                                {view:"button",value:"Find (ค้นหา)",on:
                                    {
                                        onItemClick:function(id, e)
                                        {
                                            var btn  = this,dataT1=ele('dataT1'),obj={LOAD_ID:val('LOAD_ID')};
                                            ajax(fd,obj,1,function(json)
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
                        {
                            rows:
                            [
                                {},
                                {view:"button",value:"Delete (ลบ)",type:'danger',on:
                                    {
                                        onItemClick:function(id, e)
                                        {
                                            var rowAr = ele('dataT1').getSelectedItem(true),btn  = this;
                                            var idAr = [];
                                            if(rowAr.length >0)
                                            {
                                                for(var i=0,len=rowAr.length;i<len;i++)
                                                {
                                                    idAr.push(rowAr[i].ID);
                                                }
                                                
                                                var dataT1=ele('dataT1'),obj={ID:idAr.join(','),LOAD_ID:val('LOAD_ID')};
                                                ajax(fd,obj,31,function(json)
                                                {                                                       
                                                    setTable('dataT1',json.data);
                                                },btn,function(json)
                                                {
                                                    
                                                });
                                            }
                                            else 
                                            {
                                                webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:'ยังไม่ได้เลือกรายการ',callback:function(){}});
                                            }
                                            
                                        }
                                    }
                                },
                            ]
                        }
                    ]
                },
                {
                    view:"datatable",id:$n("dataT1"),multiselect:true,
                    autoheight:false,datatype:"json",datathrottle:1000,
                    select:"row",footer:false,hover:"myhover",navigation:true,resizeColumn:true,
                    fixedRowHeight:false, scrollAlignY:true,
                    columns:
                    [
                        { id:"NO",header:"No",css:"rank",width:50},
                        { id:"CD_PLANT",header:["CD_PLANT",{content:"textFilter"}],width:150},
                        { id:"CD_SUPPLIER_SHP_FR",header:["CD_SUPPLIER_SHP_FR",{content:"textFilter"}],width:150},
                        { id:"NO_PART_PREFIX",header:["NO_PART_PREFIX",{content:"textFilter"}],width:150},
                        { id:"NO_PART_BASE",header:["NO_PART_BASE",{content:"textFilter"}],width:150},
                        { id:"NO_PART_SUFFIX",header:["NO_PART_SUFFIX",{content:"textFilter"}],width:150},
                        { id:"DT_PGM_START",header:["DT_PGM_START",{content:"textFilter"}],width:150},
                        { id:"NO_PGM",header:["NO_PGM",{content:"textFilter"}],width:150},
                        { id:"LOAD_ID",header:["LOAD ID",{content:"textFilter"}],width:150},
                        { id:"CD_PICKUP_RTE_NEW",header:["CD_PICKUP_RTE_NEW",{content:"textFilter"}],width:150},
                        { id:"DT_SHIP",header:["DT_SHIP",{content:"textFilter"}],width:150},
                        { id:"TM_SHIP",header:["TM_SHIP",{content:"textFilter"}],width:150},
                        { id:"CD_DELIVRY_RTE_NEW",header:["CD_DELIVRY_RTE_NEW",{content:"textFilter"}],width:150},
                        { id:"DT_DELIVERY",header:["DT_DELIVERY",{content:"textFilter"}],width:150},
                        { id:"TM_DELIVERY",header:["TM_DELIVERY",{content:"textFilter"}],width:150},
                        { id:"CD_DELIVERY_DOCK",header:["CD_DELIVERY_DOCK",{content:"textFilter"}],width:150},
                        { id:"QT_SHP_DEL",header:["QT_SHP_DEL",{content:"textFilter"}],width:150},
                        { id:"QT_CUM_SHP_DEL",header:["QT_CUM_SHP_DEL",{content:"textFilter"}],width:150},
                        { id:"QT_PKG",header:["QT_PKG",{content:"textFilter"}],width:150},
                        { id:"WT_PART",header:["WT_PART",{content:"textFilter"}],width:150},
                        { id:"CD_COUNTRY",header:["CD_COUNTRY",{content:"textFilter"}],width:150},
                        { id:"NA_COMP",header:["NA_COMP",{content:"textFilter"}],width:150},
                        { id:"CD_PLANT_DOCK_LOC",header:["CD_PLANT_DOCK_LOC",{content:"textFilter"}],width:150},
                        { id:"CD_RELEASE_ANAL",header:["CD_RELEASE_ANAL",{content:"textFilter"}],width:150},
                    ],
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