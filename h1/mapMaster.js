var header_mapMaster = function()
{
	var menuName="mapMaster_",fd = "h1/"+menuName+"data.php";
    var objTruck = {},loadTruckGeoTime = null,truckHistory = []
    var popupShowClick1 = null;
    var line_truckToSup = null
    function init()
    {
        console.log('test');
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

        map.Event.bind('overlayClick', function(overlay) 
        {
            if(overlay instanceof longdo.Polyline)
            {
                /* popupShowClick1.detail(`Start ${overlay.time1} <br> End ${overlay.time2}`); */
                /* popupShowClick1.move(overlay.location()[1]); */

                popupShowClick1 = new longdo.Popup(overlay.location()[0],
                {
                  title: 'Popup',
                  detail: `Start ${overlay.time1} <br> End ${overlay.time2}`,
                  size: { width: 200, height: 100 },
                  closable: true
                });

                var list = map.Overlays.list(),num_rows=0;
                
                for(var i=0,len=list.length;i<len;i++)
                {
                    if(list[i] == popupShowClick1)
                    {
                        num_rows++;
                        break;
                    }
                }
                if(num_rows==0)
                {
                    map.Overlays.add(popupShowClick1);
                }                
            }

            /* if(overlay == popupShowClick1)
            {
                console.log(map.Overlays.list().length);
                
            } */
            
        });
        /* 
        
        setTimeout(function(){console.log(map.Overlays.list().length,'overlayChange');},1000*10)

        map.Event.bind('overlayChange', function(overlay)         
        {
            
            if(overlay == popupShowClick1)
            {
                console.log(map.Overlays.list().length,'overlayChange');
                console.log(overlay);
            }
            
        }); */

        map.Layers.externalOptions({ googleQuery: 'key=AIzaSyC9ItnaPb2x897MTFxXygqJdT6QPVTW6Hc' });
        map.Layers.setBase(longdo.Layers.GOOGLE_HYBRID);
        loadDataGeo(null);
        loadTruckGeo(null);
        loadTruckGeoTime = setInterval(() => 
        {
            loadTruckGeo(null);
        }, 1000*5);
        menuMap();        
        
    };

    function removeTruckHistoryAll()
    {
        for(var i=0,len=truckHistory.length;i<len;i++)
        {
            map.Overlays.remove(truckHistory[i]);
        }
        truckHistory = [];        
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
                         { value: 'Truck', id:$n('truckView')},
                         { value: 'Truck History', id:$n('truckHistory')},
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
    
                            if(newv==$n('truckView'))
                            {
                                ele('supplierView').hide();
                                ele('truckHistory').hide();
                                ele('truckView').show();
                            }
                            else if(newv==$n('supplierView'))
                            {
                                ele('truckView').hide();
                                ele('truckHistory').hide();
                                ele('supplierView').show();
                            }
                            else if(newv==$n('truckHistory'))
                            {
                                ele('truckView').hide();
                                ele('supplierView').hide();
                                ele('truckHistory').show();
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
                            view:"datatable",id:$n("truckView"),navigation:true,
                            resizeColumn:true,select:'row',
                            threeState:true,height:500,
                            datatype:"json",
                            columns:
                            [
                                { id:"truckLicense",header:["Truck No",{content:"textFilter"}],width:100},
                                { id:"gps_speed",header:["Speed",{content:"textFilter"}],width:100},
                                { id:"gps_updateDatetime",header:["GPS Update",{content:"textFilter"}],width:200},
                            ],on:
                            {
                                onSelectChange:function()
                                {                                    
                                    var obj = this.getItem(this.getSelectedId(false));
                                    if(obj)
                                    {
                                        var geoTruck =  JSON.parse(obj.Geo);
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
                        {
                            view:"form",paddingY:20,
                            navigation:true,height:500,
                            id:$n("truckHistory"),
                            elements:
                            [
                                {
                                    cols:
                                    [
                                        vw1('datepicker','dateStart','Start Date (วันเริ่มต้น)',{format:webix.Date.dateToStr("%Y-%m-%d"),value:new Date(),stringResult:1,type:"date"}),
                                        vw1('text','timeStart','Start Time (เวลาเริ่มต้น)',{}),
                                    ]
                                },
                                {
                                    cols:
                                    [
                                        vw1('datepicker','dateEnd','End Date (วันสิ้นสุด)',{format:webix.Date.dateToStr("%Y-%m-%d"),value:new Date(),stringResult:1,type:"date"}),
                                        vw1('text','timeEnd','End Time (เวลาสิ้นสุด)',{}),                                       
                                    ]
                                },
                                {
                                    cols:
                                    [
                                        vw1('text','truckNo','truck License (ทะเบียนรถ)',{}),
                                        vw1('text','Supplier_Code','Supplier Code',{}),
                                    ],
                                },                                                                
                                vw1('button','find1','Find (ค้นหา)',{
                                    on:
                                    {
                                        onItemClick:function(id, e)
                                        {
                                            var btn = this;
                                            ajax(fd, ele('truckHistory').getValues(), 6, function (json) 
                                            {

                                                setTable('historyView',json.data);
                                                /* removeTruckHistoryAll();
                                                
                                                var line_Ar = [];
                                                var time_Ar = [];
                                                var firstPoint = 0;
                                                for(var i=0,len=json.data.length;i<len;i++)
                                                {  
                                                    line_Ar.push(JSON.parse(json.data[i].pt));
                                                    time_Ar.push(json.data[i].gps_updateDatetime);
                                                    
                                                    if( (i+1) % 2  == 0)
                                                    {
                                                        var pt1 = line_Ar[0];
                                                        var pt2 = line_Ar[1];

                                                        var time1 = time_Ar[0];
                                                        var time2 = time_Ar[1];
                                                        
                                                        var obj1 = {};
                                                        var obj2 = {};

                                                        if(pt1.hasOwnProperty('type'))
                                                        {
                                                            if(pt1.type == 'Point')
                                                            {
                                                                obj1 ={
                                                                    lon: pt1.coordinates[1], lat: pt1.coordinates[0]
                                                                };
                                                            }
                                                        }

                                                        if(pt2.hasOwnProperty('type'))
                                                        {
                                                            if(pt2.type == 'Point')
                                                            {
                                                                obj2 ={
                                                                    lon: pt2.coordinates[1], lat: pt2.coordinates[0]
                                                                };
                                                            }
                                                        }
                                                        line_Ar = [];
                                                        time_Ar = [];

                                                        var line = new longdo.Polyline([obj1, obj2], 
                                                        { linePattern: function(context, i, x1, y1, x2, y2)
                                                            {
                                                                var size = 10;
                                                                var angle = Math.PI / 6;
                                                                var direction = Math.atan2(y2 - y1, x2 - x1);
                                                                context.moveTo(x1, y1);
                                                                context.lineTo(x2, y2);
                                                                context.lineTo(x2 - size * Math.cos(direction - angle), y2 - size * Math.sin(direction - angle));
                                                                context.moveTo(x2, y2);
                                                                context.lineTo(x2 - size * Math.cos(direction + angle), y2 - size * Math.sin(direction + angle));
                                                            },
                                                            lineColor: 'rgba(241, 196, 15,1)',
                                                            lineWidth: 3,
                                                        });
                                                        if(firstPoint == 0)
                                                        {
                                                            firstPoint = 1;
                                                            map.location(obj1);
                                                        }

                                                        line.time1 = time1;
                                                        line.time2 = time2;
                                                        truckHistory.push(line);
                                                        map.Overlays.add(line);

                                                    }
                                                } */
                                            }, btn);
                                        }
                                    }
                                }),
                                /* vw1('button','find2','Remove Point (ลบจุด)',{
                                    on:
                                    {
                                        onItemClick:function(id, e)
                                        {
                                            removeTruckHistoryAll();
                                        }
                                    }
                                }), */
                                {
                                    view:"datatable",id:$n("historyView"),navigation:true,
                                    resizeColumn:true,select:'row',
                                    threeState:true,
                                    datatype:"json",
                                    columns:
                                    [
                                        { id:"gps_updateDatetime",header:["gps updateDatetime",{content:"textFilter"}],width:200},
                                        { id:"Contain",header:["อยู่ในกรอบหรือไม่",{content:"textFilter"}],width:100},
                                    ],on:
                                    {
                                        onSelectChange:function()
                                        {                                    
                                            var obj = this.getItem(this.getSelectedId(false));
                                            if(obj)
                                            {
                                                var geoTruck =  JSON.parse(obj.pt);
                                                var sup_geo =  JSON.parse(obj.sup_geo);
                                                if(_.has(geoTruck,'type'))
                                                {
                                                    if(geoTruck.type == 'Point')
                                                    {
                                                        map.location({ lat: geoTruck.coordinates[0], lon: geoTruck.coordinates[1] });
                                                    }
                                                }

                                                if(_.has(sup_geo,'type'))
                                                {
                                                    if(sup_geo.type == 'Point')
                                                    {
                                                        if(line_truckToSup)
                                                        {
                                                            map.Overlays.remove(line_truckToSup);
                                                        }
                                                        var lineOption = 
                                                        {                            
                                                            lineWidth: 2,
                                                            lineColor: 'rgba(240, 227, 94, 100)',
                                                            linePattern: pattern2
                                                        }
                                                        line_truckToSup = new longdo.Polyline([{ lon: geoTruck.coordinates[1], lat: geoTruck.coordinates[0] }, 
                                                            { lon: sup_geo.coordinates[1], lat: sup_geo.coordinates[0] }], lineOption);
                                                        map.Overlays.add(line_truckToSup);  
                                                          
                                                        
                                                        function pattern2(context, i, x1, y1, x2, y2) 
                                                        {
                                                            var size = 30;
                                                            var angle = Math.PI / 6;
                                                            var direction = Math.atan2(y2 - y1, x2 - x1);
                                                            context.moveTo(x1, y1);
                                                            context.lineTo(x2, y2);
                                                            context.lineTo(x2 - size * Math.cos(direction - angle), y2 - size * Math.sin(direction - angle));
                                                            context.moveTo(x2, y2);
                                                            context.lineTo(x2 - size * Math.cos(direction + angle), y2 - size * Math.sin(direction + angle));
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                },

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
        ajax("MasterData/SupplierMaster_data.php", {}, 2, function (json) 
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
                          
                          map.Overlays.add(wardLabel);
                          var wardLabel = new longdo.Marker(
                            { lon: polyCenterSupplier.coordinates[1], lat: polyCenterSupplier.coordinates[0] }, {
                              icon: {
                                url: 'images/factory.png',
                                offset: { x: 15, y: 40 }
                              },
                              weight: longdo.OverlayWeight.Top,
                              visibleRange:{ min: 6, max: 20 },
                            }
                          )
                          map.Overlays.add(wardLabel);
                    }
                    

                }
            }
            
        }, btn);
    };

    function loadTruckGeo(btn)
    {
        ajax(fd, {}, 3, function (json) 
        {
            setTable('truckView',json.data);
            for(var i=0,len=json.data.length;i<len;i++)
            {            
                var Geo =  JSON.parse(json.data[i].Geo), truckLicense ='';
                if(Geo == null) 
                {
                    continue;
                }

                if(Geo.hasOwnProperty('type'))
                {
                    if(Geo.type == 'Point')
                    {
                        truckLicense = json.data[i].truckLicense;
                        var point = { lon: Geo.coordinates[1], lat: Geo.coordinates[0] };
                        if(!objTruck.hasOwnProperty(`${json.data[i].ID}`))
                        {
                            
                            var wardLabel = new longdo.Marker(
                                point, {
                                icon: {
                                    html: `
                                    <div class='webix_view' style='background-color:lightblue;opacity: 1;width:90px;' >
                                        <center><h10>${truckLicense}</h10></center>                                        
                                    </div>
                                    `,
                                    offset: { x: 45, y: 27 }
                                },
                                weight: longdo.OverlayWeight.Top,
                                visibleRange:{ min: 15, max: 20 },
                                }
                            )
                            objTruck[`${json.data[i].ID}`] = wardLabel;
                            map.Overlays.add(wardLabel);

                            var wardLabel = new longdo.Marker(
                                point, {
                                icon: {
                                    url: 'images/car-top-view.png',
                                    offset: { x: 16, y: 16 }
                                },
                                rotate:parseInt(json.data[i].gps_angle),
                                weight: longdo.OverlayWeight.Top,
                                visibleRange:{ min: 6, max: 20 },
                                }
                            )
                            objTruck[`${json.data[i].ID}_image`] = wardLabel;
                            map.Overlays.add(wardLabel);

                        }
                        else
                        {
                            var oldPoint = objTruck[`${json.data[i].ID}`].location();
                            if(oldPoint.lon != point.lon && oldPoint.lat != point.lat)
                            {
                                objTruck[`${json.data[i].ID}`].move(point);
                                objTruck[`${json.data[i].ID}_image`].move(point);
                            }

                        }
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

	return {
        view: "scrollview",
        scroll: "native-y",
        id:"header_mapMaster",
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