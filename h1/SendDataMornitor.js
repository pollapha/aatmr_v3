var header_SendDataMornitor = function()
{
	var menuName="SendDataMornitor_",fd = "h1/"+menuName+"data.php";

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

	return {
        view: "scrollview",
        scroll: "native-y",
        id:"header_SendDataMornitor",
        body: 
        {
        	id:"SendDataMornitor_id",
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
                                {}
                            ]
                        },
                    ]
                },
                {
                    view:"datatable",id:"SendDataMornitor_dataT1",navigation:true,
                    resizeColumn:true,autoheight:false,
                    hover:"myhover",threeState:true,
                    datatype:"json",
                    scheme:
                    {
                      $change:function(item)
                      {
                      
                      }
                    },
                    columns:
                    [
                        { id:"data22",header:"&nbsp;",width:40,
                           template: "<span style='cursor:pointer' class='webix_icon fa-clock-o'></span>"
                        },
                        { id:"data23",header:"&nbsp;",width:40,
                           template: "<span style='cursor:pointer' class='webix_icon fa-trash-o'></span>"
                        },
                        { id:"NO",header:"NO.",css:"rank",width:40},
                        { id:"DateIN",header:["ถึงจริง",{content:"textFilter"}],width:70},
                        { id:"DateOUT",header:["ออกจริง",{content:"textFilter"}],width:70},                    
                        { id:"Load_ID",header:["Load ID",{content:"textFilter"}],width:120},
                        { id:"StopSequenceNumber",header:["ลำดับ",{content:"textFilter"}],width:55},
                        { id:"TrailerNumber",header:["ทะเขียนรถ",{content:"textFilter"}],width:120},
                        { id:"Route",header:["รหัสเส้นทาง",{content:"textFilter"}],width:120},
                        { id:"Supplier_Code",header:["รหัสซัพพลายเออร์",{content:"textFilter"}],width:120},
                        { id:"driverName",header:["คนขับรถ",{content:"textFilter"}],width:120},
                        { id:"phone",header:["เบอร์โทร",{content:"textFilter"}],width:120},
                    ],
                    onClick:
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