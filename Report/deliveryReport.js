var header_deliveryReport = function()
{
	var menuName="deliveryReport_",fd = "Report/"+menuName+"data.php";

    var SumTruckForChart_ctx,SumTruckForChart,
    chartOptions = {
        responsive: true,
        legend: {
          position: "top"
        },
        title: {
          display: "top",
          text: 'December Report'
        },
        scales: {
            xAxes: [{
                stacked: true,
            }],
            yAxes: [{
                stacked: true
            }]
        },
        layout: {
            padding: {
                left: 0,
                right: 0,
                top: 0,
                bottom: 0
            }
        },
        plugins: {
            datalabels: {
                formatter: function(value, ctx) 
                {
                    if(value == 0) return '';
                    return value;
                },
                font: {
                    weight: "normal"
                },
                color: "#fff"
            }
        }
    };

    function init()
    {
        SumTruckForChart_ctx  = document.getElementById($n('SumTruckForChart')).getContext('2d');
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
        id:"header_deliveryReport",
        body: 
        {
        	id:"deliveryReport_id",
        	type:"clean",
    		rows:
    		[
    		    {
                    cols:
                    [
                        vw1("datepicker",'date1',"Delivery Date (วันที่)",{type:"date",format:webix.Date.dateToStr("%Y-%m"),value:new Date(),stringResult:1}),
                        {
                            rows:
                            [
                                {},
                                vw1("button",'btnFind',"find (ค้นหา)",
                                {
                                    width:130,on:
                                    {
                                        onItemClick:function()
                                        {
                                            var obj={},btn=this;
                                            obj.date1 = webix.Date.dateToStr("%Y-%m-%d")(ele('date1').getValue());
                                            var mm = ele("date1").getValue();
                                            var mmAR = mm.split("-");
                                            ajax(fd,obj,1,function(json)
                                            {
                                                function getDaysInMonth(month, year) {
                                                    var date = new Date(year, month, 1);
                                                    var days = [];
                                                    while (date.getMonth() === month) {
                                                    days.push(new Date(date));
                                                    date.setDate(date.getDate() + 1);
                                                    }
                                                    return days;
                                                }
                                                
                                                var d = getDaysInMonth(mmAR[1]-1,mmAR[0]);
                                                var listdate = [];
                                                d.forEach(element => {
                                                    listdate.push(element.getDate());
                                                });

                                                var barChartData = {
                                                    labels: listdate,
                                                    datasets: [
                                                        {
                                                            label: 'COMPLETE',
                                                            backgroundColor: "#27AE60",
                                                            data:json.SumTruckForChart.Delivery_Complete
                                                        },
                                                        {
                                                            label: 'NOT COMPLETE',
                                                            backgroundColor: "#F64747",
                                                            data:json.SumTruckForChart.Delivery_Not_Complete
                                                        }                                                                                            
                                                    ]
                                                };

                                                if(SumTruckForChart)
                                                {
                                                    SumTruckForChart.destroy();
                                                    SumTruckForChart = null;
                                                }
                                                
                                                SumTruckForChart = new Chart(SumTruckForChart_ctx, {
                                                    type: "bar",
                                                    data: barChartData,
                                                    options: chartOptions
                                                });

                                                
                                                
                                            },btn,function(json)
                                            {
                                            });
                                        }
                                    }
                                }),
                            ]
                        },
                        {}
                    ]
                },
                {
                    cols:
                    [
                        {
                            height:500,width:1000,
                            template:'<div style="width:100%;height:100%"><canvas id="'+$n('SumTruckForChart')+'"></canvas></div>'
                        },{}
                    ]
                },{}
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