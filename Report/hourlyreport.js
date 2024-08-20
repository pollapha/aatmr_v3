var header_hourlyreport = function()
{
	var menuName="hourlyreport_",fd = "Report/"+menuName+"data.php";

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

    function getColSupplier()
    {
        var col = [];

        col.push({ id:"NO",header:"NO.",css:"rank",width:50});
        col.push({ id:"projectName",header:["Project Name",{content:"selectFilter"}],width:130});
        col.push({ id:"Load_ID",header:["Load ID",{content:"textFilter"}],width:90});                
        col.push({ id:"Route",header:["Route No.",{content:"selectFilter"}],width:130});
        col.push({ id:"Operration_Date",header:["Operration Date",{content:"selectFilter"}],width:100});
        col.push({ id:"Start_Datetime",header:["Pick up Date",{content:"selectFilter"}],width:100});
        col.push({ id:"End_Datetime",header:["Delivery Date",{content:"selectFilter"}],width:100});    
        col.push({ id:"truckLicense",header:["Truck License",{content:"textFilter"},],width:140});
        col.push({ id:"truckType",header:["Truck Type",{content:"textFilter"},],width:140});
        col.push({ id:"driverName",header:["Driver Name",{content:"selectFilter"}],width:130});
        col.push({ id:"phone",header:["Phone",{content:"selectFilter"}],width:130});
        for(var i=0,len=8;i<len;i++)
        {
            col.push({ id:"sup"+(i+1)+"Supplier_Code",header:["Supplier Code "+(i+1),{content:"textFilter"}],width:180});
            col.push({ id:"sup"+(i+1)+"Supplier_Name",header:["Supplier Name "+(i+1),{content:"textFilter"}],width:280});
            col.push({ id:"sup"+(i+1)+"PlanIN_Datetime",header:["Plan IN "+(i+1),],width:130});
            col.push({ id:"sup"+(i+1)+"PlanOut_Datetime",header:["Plan OUT "+(i+1),],width:130});
            col.push({ id:"sup"+(i+1)+"ActualIN_Datetime",header:["Actual IN "+(i+1),],width:130});
            col.push({ id:"sup"+(i+1)+"ActualOut_Datetime",header:["Actual OUT "+(i+1),],width:130});
        }
        return col;

    };

    function exportAAT_MR()
    {

    };

	return {
        view: "scrollview",
        scroll: "native-y",
        id:"header_hourlyreport",
        body: 
        {
        	id:"hourlyreport_id",
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
                                                    setTable('dataT1',[]);
                                                });
                                            }
                                        }
                                    }
                                ),
                                vw1('button','hourAATMR','Hourly Report(AAT-MR)',
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
                                vw1('button','hourFTM','Hourly Report(FTM)',
                                    {
                                        on:
                                        {
                                            onItemClick:function(id, e)
                                            {
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
                                vw1('button','export','Export',
                                    {
                                        on:
                                        {
                                            onItemClick:function(id, e)
                                            {
                                                exportExcel(this);
                                            }
                                        },
                                        width:130
                                    }
                                ),
                            ]
                        }
                    ]
                },
                {
                    view:"datatable",id:$n("dataT1"),navigation:true,
                    resizeColumn:true,autoheight:false,
                    threeState:true,pager:$n("Master_pagerA"),
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
                    columns:getColSupplier(),
                    onClick:
                    {

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