var header_AATMap = function()
{
	var menuName="AATMap_",fd = "h1/"+menuName+"data.php";
    var objTruck = {},loadTruckGeoTime = null,supplier_ar = []
    var popupShowClick1 = null;
    var line_truckToSup = null
    var supliier_master = [];
    function init()
    {
        
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
            placeholder: document.getElementById($n('map')),
            zoomRange: {max: 19 },
        });        

        map.Event.bind('overlayClick', function(overlay) 
        {
            if(overlay instanceof longdo.Polyline)
            {
               
            }
        });

        map.Event.bind('toolbarChange', () =>
        {                    
            if(map.Ui.Toolbar.activeTool() === 'M')
            {
                removeSupplierAll();
            }
            else if(map.Ui.Toolbar.activeTool() === null)
            {
                if(map.Ui.Toolbar.measureList().length>0)
                {
                    var polygonObj = map.Ui.Toolbar.measureList()[0].location();
                    var polygonAr = [];

                    for(var i=0,len=polygonObj.length;i<len;i++)
                    {
                        polygonAr.push(`${polygonObj[i].lat} ${polygonObj[i].lon}`);
                    }

                    map.placeholder().querySelector(`.ldmap_toolbar img.ldmap_button[title="Clear measurement"]`).click();

                    if(polygonAr.length<5)
                    {
                        loadDataGeo(null);
                    }
                    else
                    {
                        ele('updateMap').show();
                        ele('updateMap_polygon').setValue('POLYGON(('+polygonAr.join(',')+'))');
                    }
                    
                }
                else
                {
                    loadDataGeo(null);
                }
            }     
        }
        );

        map.Layers.externalOptions({ googleQuery: 'key=AIzaSyC9ItnaPb2x897MTFxXygqJdT6QPVTW6Hc' });
        map.Layers.setBase(longdo.Layers.GOOGLE_HYBRID);
        loadDataGeo(null);
        menuMap();        
        
    };

    function removeSupplierAll()
    {
        for(var i=0,len=supplier_ar.length;i<len;i++)
        {
            map.Overlays.remove(supplier_ar[i]);
        }
        supplier_ar = [];        
    }

    function menuMap()
    {
        $('.ldmap_topright').append(`
            <div id="${$n('listA')}"></div>
        `);

        webix.ui({
            container:$n('listA'),
            height:0,width:400,
            rows:
            [
                {
                    view:"segmented", id:'tabbar', value:$n('Hide'), multiview:true, optionWidth:90,  align:"center", padding:0, options: [
                         { value: 'Setting', id:$n('setting')},
                         { value: 'Supplier', id:$n('supplierView')},
                         { value: 'Hide', id:$n('Hide')}
                     ],on:
                     {
                        onChange:function(newv, oldv)
                        {
                            if(newv!=$n('Hide'))
                            {
                                ele('viewdata').show();
                                /* this.config.height = 100;
                                this.refresh(); */
                            }
                            else if(newv==$n('Hide'))
                            {
                                ele('viewdata').hide();
                            }
    
                            if(newv==$n('setting'))
                            {
                                ele('supplierView').hide();
                                ele('setting').show();
                            }
                            else if(newv==$n('supplierView'))
                            {
                                console.log('tese');
                                ele('setting').hide();
                                ele('supplierView').show();
                            }
                        }
                     }
                 },
                 {
                     id:$n('viewdata'),
                     hidden:true,
                     rows:
                     [
                        {
                            view:"form",paddingY:20,
                            navigation:true,height:500,
                            id:$n("setting"),
                            elements:
                            [
                                {
                                    cols:
                                    [
                                        vw1('button','hideSupplier','Hide Supplier',{
                                            on:
                                            {
                                                onItemClick:function(id, e)
                                                {
                                                    removeSupplierAll(); 
                                                }
                                            }
                                        }),
                                        vw1('button','showSupplier','Show Supplier',{
                                            on:
                                            {
                                                onItemClick:function(id, e)
                                                {
                                                    loadDataGeo(this);
                                                }
                                            }
                                        }),
                                    ]
                                }
                                
                            ]
                        },
                        {
                            view:"datatable",id:$n("supplierView"),navigation:true,
                            resizeColumn:true,select:'row',height:500,
                            threeState:true,
                            datatype:"json",
                            columns:
                            [
                                { id:"supplier_code",header:["Supplier Code",{content:"textFilter"}],width:100},
                                { id:"supplier_name",header:["Supplier Name",{content:"textFilter"}],width:250},
                            ],on:
                            {
                                onSelectChange:function()
                                {                                    
                                    var obj = this.getItem(this.getSelectedId(false));
                                    if(obj)
                                    {
                                        var geoTruck =  JSON.parse(obj.supplier_geoCenter);
                                        if(_.has(geoTruck,'type'))
                                        {
                                            if(geoTruck.type == 'Point')
                                            {
                                                map.location({ lat: geoTruck.coordinates[0], lon: geoTruck.coordinates[1] });
                                                
                                            }
                                        }
                                    }
                                    
                                }
                            }
                        },
                     ]
                 }
                 
            ]
            
        });
    };

    function loadDataGeo(btn)
    {        
        if(supplier_ar.length>0)
        {
            removeSupplierAll();
        }

        ajax(fd, {}, 2, function (json) 
        {
            setTable('supplierView',json.data);
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
                            weight: longdo.OverlayWeight.Top,
                            visibleRange:{ min: 15, max: 20 },
                        });          
                        supplier_ar.push(polygon);           
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
                              visibleRange:{ min: 15, max: 20 },
                            }
                          )
                          supplier_ar.push(wardLabel);
                          map.Overlays.add(wardLabel);

                          var wardImage = new longdo.Marker(
                            { lon: polyCenterSupplier.coordinates[1], lat: polyCenterSupplier.coordinates[0] }, {
                              icon: {
                                url: 'images/factory.png',
                                offset: { x: 15, y: 40 }
                              },
                              weight: longdo.OverlayWeight.Top,
                              visibleRange:{ min: 6, max: 20 },
                            }
                          )
                          supplier_ar.push(wardImage);
                          map.Overlays.add(wardImage);
                    }
                }
            }
            
        }, btn);
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

    function setTable(tableName,data)
    {
        ele(tableName).clearAll();
        ele(tableName).parse(data);
        ele(tableName).filterByAll();
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
                                                else
                                                {
                                                    loadDataGeo(null);
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
                                        loadDataGeo(null);
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
        id:"header_AATMap",
        body: 
        {
        	id:"mapMaster_id",
        	type:"clean",
    		rows:
    		[
    		    {
                    view:"htmlform",
                    template: `
                    <div id='${$n('map')}' class='map' style="height: 100%;">
                    </div>`,
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