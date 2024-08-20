var header_SupplierMaster = function()
{
	var menuName="SupplierMaster_",fd = "MasterData/"+menuName+"data.php";
    var map = null;
    
    function init()
    {
        loadData(null);
        
        if(typeof longdo == 'undefined')
        {
            const script = document.createElement('script');
            script.src = 'https://api.longdo.com/map/?key=c14ebfa703dfb4d69fbcd2947d9a5777';
            document.body.appendChild(script);
            script.onload = () => 
            {
                initMap();
            };
        }
        else
        {
            initMap();
        }
        
    };

    function initMap()
    {
        map = new longdo.Map(
        {
            placeholder: document.getElementById($n('map'))
        });

        map.Layers.externalOptions({ googleQuery: 'key=AIzaSyC9ItnaPb2x897MTFxXygqJdT6QPVTW6Hc' });
        map.Layers.setBase(longdo.Layers.GOOGLE_HYBRID);
        loadDataGeo(null);
        map.Event.bind('toolbarChange', () =>
            {                    
                if(map.Ui.Toolbar.measureList().length>0)
                {
                    var polygonObj = map.Ui.Toolbar.measureList()[0].location();
                    var polygonAr = [];
                    map.Overlays.remove(map.Ui.Toolbar.measureList()[0]);
                    map.Ui.Toolbar.measureList().pop();
                    for(var i=0,len=polygonObj.length;i<len;i++)
                    {
                        polygonAr.push(`${polygonObj[i].lat} ${polygonObj[i].lon}`);
                        
                    }
                    ele('updateMap').show();
                    ele('updateMap_polygon').setValue('POLYGON(('+polygonAr.join(',')+'))');
                }                    
            }
        );
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

    function loadData(btn) 
    {
        ajax(fd, {}, 1, function (json) {
            setTable('dataT1', json.data);
        }, btn);
    };

    function loadDataGeo(btn)
    {
        ajax(fd, {}, 2, function (json) 
        {
            map.Overlays.clear();
            for(var i=0,len=json.data.length;i<len;i++)
            {                    
                
                var geoSupplier =  JSON.parse(json.data[i].supplier_geo);

                if(geoSupplier.hasOwnProperty('type'))
                {
                    if(geoSupplier.type == 'Polygon')
                    {
                        var arPoly = [];
                        for(var i2=0,len2=geoSupplier.coordinates[0].length;i2<len2;i2++)
                        {
                            arPoly.push({ lon: geoSupplier.coordinates[0][i2][1], lat: geoSupplier.coordinates[0][i2][0] });
                        }
                        var polygon = new longdo.Polygon(arPoly, {
                            detail: '-',                                
                            lineWidth: 2,
                            lineColor: 'rgba(0, 0, 0, 1)',
                            fillColor: 'rgba(255, 0, 0, 0.2)',
                            editable: false,
                            weight: longdo.OverlayWeight.Top
                        });                            
                        map.Overlays.add(polygon);
                        
                    }
                }

                var polyCenterSupplier =  JSON.parse(json.data[i].supplier_geoCenter), Supplier ='';
                if(polyCenterSupplier.hasOwnProperty('type'))
                {
                    if(polyCenterSupplier.type == 'Point')
                    {
                        Supplier = json.data[i].supplier_code;

                        var wardLabel = new longdo.Marker(
                            { lon: polyCenterSupplier.coordinates[1], lat: polyCenterSupplier.coordinates[0] }, {
                              icon: {
                                html: `
                                <div class='webix_view' style='background-color:lightblue;opacity: 1;' >
                                    <center><h10>${Supplier}</h10></center>                                        
                                </div>
                                `
                              },
                              weight: longdo.OverlayWeight.Top,
                              visibleRange:{ min: 10, max: 20}
                            }
                          )
                          map.Overlays.add(wardLabel);
                          var wardLabel = new longdo.Marker(
                            { lon: polyCenterSupplier.coordinates[1], lat: polyCenterSupplier.coordinates[0] }, {
                              icon: {
                                url: 'images/factory.png',
                                offset: { x: 15, y: 40 }
                              },
                              weight: longdo.OverlayWeight.Top,
                              visibleRange:{ min: 9, max: 20 },
                            }
                          )
                          map.Overlays.add(wardLabel);
                          
                    }
                }
            }
            
        }, btn);
    };

    webix.ui(
        {
            view:"window", move:true,modal:true,id:$n("updateMap"),
            head:{
            view:"toolbar",
            cols:
            [
                {view:"label", label: "เพิ่มพิกัด",align:"center"},
            ],
            },top:50,position:"center",
            body:
            {
                view:"form", scroll:false,id:$n("updateMap_form1"),width:400,
                elements:
                [
                    {

                        cols:
                        [
                            {view:"text",labelPosition:"top",required:true,labelWidth:120,disabled:false,id:$n("updateMap_polygon"), name:"polygon", label:"Polygon", value:""},
                        ]
                    },
                    {
                        cols:
                        [
                            {view:"combo",labelPosition:"top",required:true,labelWidth:120,id:$n("updateMap_supplier"), name:"supplier_code", label:"Supplier Code", value:"",
                                suggest:fd+"?type=3",
                                on:
                                {
                                    onChange: function(value,obj)
                                    {
                                    },
                                }
                            },
                        ]
                    },
                    {
                        cols:
                        [   
                            {},
                            {
                                view:"button", value:"OK (ตกลง)",type:"form", width:150 ,click:function()
                                {
                                    if(ele('updateMap_form1').validate())
                                    {
                                        webix.confirm(
                                        {
                                            title: "กรุณายืนยัน",ok:"ใช่", cancel:"ไม่",text:"คุณต้องการบันทึกข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                            callback:function(res)
                                            {
                                                if(res)
                                                {
                                                    
                                                    $.post(fd, {obj:ele('updateMap_form1').getValues(),type:22})
                                                    .done(function( ddd ) 
                                                    {
                                                      var data=JSON.parse(ddd);
                                                      if(data.ch == 1)
                                                      {
                                                        loadDataGeo(null);
                                                        ele("updateMap_Clear").callEvent("onItemClick", []);
                                                      } 
                                                      else if(data.ch == 2){webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){}});}
                                                      else if(data.ch == 9){webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){}});}
                                                      else if(data.ch == 10){webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:data.data,callback:function(){window.open("login.php","_self");}});}
                                                    });
                                                }
                                            }
                                        });
                                    } 
                                    else
                                    {
                                       webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:'กรุณาป้อนข้อมูล<font color="#ce5545"><b>ในช่องสีแดง</b></font>ให้ครบ',callback:function(){}});
                                    }
                                }
                            },
                            {
                                view:"button", value:"Cancel (ยกเลิก)",type:"danger",id:$n("updateMap_Clear"), width:150,on:
                                {
                                    onItemClick:function(id)
                                    {
                                        ele('updateMap').hide();
                                        ele('updateMap_form1').setValues('');
                                    }
                                }
                            }
                        ]
                    }
                ]
            }
        });

    

	return {
        view: "scrollview",
        scroll: "native-y",
        id:"header_SupplierMaster",
        body: 
        {
        	id:"SupplierMaster_id",
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
                                                onItemClick: function () {
                                                    var btn = this;
                                                    loadData(btn);
                                                }
                                            }
                                        }),
                                        /* vw1("button", 'btnAddNew', "Add New (เพิ่ม)", {
                                            width: 170, on:
                                            {
                                                onItemClick: function () {
                                                    var btn = this;
                                                    ele('win_Addnew').show();
                                                }
                                            }
                                        }), */
                                        vw1("button", 'btnExport', "Export (โหลดเป็นไฟล์เอ๊กเซล)", {
                                            width: 170, on:
                                            {
                                                onItemClick: function () {
                                                    var btn = this;
                                                    $.fileDownload(fd, {
                                                        httpMethod: "POST",
                                                        data:{obj:{},type:4},
                                                        successCallback: function (url) {
                                                        },
                                                        prepareCallback: function (url) {
                                                        },
                                                        failCallback: function (responseHtml, url) {
                        
                                                        }
                                                    });
                                                }
                                            }
                                        }),
                                        vw1("button", 'btnUpload', "Show/Hide Map", {
                                            width: 170, on:
                                            {
                                                onItemClick: function () {
                                                    var btn = this;
                                                    ele('win_Upload').show();
                                                }
                                            }
                                        }),
                                        {},
                                    ]
                            }
                        ]
                },
                {
                    view:"htmlform",
                    template: `
                    <div id='${$n('map')}' class='map' style="height: 100%;">
                    </div>`,
                },
                { view:"resizer" },
                {
                    view: "datatable", id: $n('dataT1'), datatype: "json",
                    headerRowHeight: 20, rowLineHeight: 20, rowHeight: 20,
                    hover: "webix_row_select", editable: false,
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
                            { id: "supplier_code", header: ["Supplier Code", { content: "textFilter" }], editor: "text", width: 100 },
                            { id: "supplier_name", header: ["Supplier Name", { content: "textFilter" }], editor: "text", width: 350 },
                            { id: "checkGeo", header: ["Check Geo", { content: "textFilter" }], editor: "text", width: 100 },
                            { id: "createBy", header: "Creation User", editor: "", width: 200 },
                            { id: "createDatetime", header: "Creation DateTime", editor: "", width: 150 },
                            { id: "updateBy", header: "Updated User", editor: "", width: 200 },
                            { id: "updateDatetime", header: "Last Updated DateTime", editor: "", width: 150 },
                        ],
                    onClick:
                    {
                        "fa-check": function (e, t) {
                            var row = this.getItem(t), dataTable = this;
                            ajax(fd, row, 21, function (json) {
                                row.change = 0;
                                dataTable.updateItem(t.row, row);
                            }, null);
                        },
                    },
                    on:
                    {
                        "onEditorChange": function (id, value) {
                            var row = this.getItem(id), dataTable = this;
                            row.change = 1;
                            dataTable.updateItem(id.row, row);
                        }
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