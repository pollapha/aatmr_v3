var header_extraTruckTime = function()
{
	var menuName="extraTruckTime_",fd = "h1/"+menuName+"data.php";

    function init()
    {
        loadData();
        
    };

    function ele(name)
    {
        return $$($n(name));
    };

    function $n(name)
    {
        return menuName+name;
    };
    
    function setTable(tableName, data) {
        if (!ele(tableName)) return;
        ele(tableName).clearAll();
        ele(tableName).parse(data);
        ele(tableName).filterByAll();
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

    function loadData() 
    {
        const formData = ele("form1").getValues();
        formData.start_datetime = convertDateTimeFormat(formData.start_date, formData.start_time);
        formData.stop_datetime = convertDateTimeFormat(formData.stop_date, formData.stop_time);
        console.log(formData);
        ajax(fd, formData, 2, function (json)
        {
            setTable('table_t1', json.data);
        }, null, function (json)
        { });
    }

    function convertDateTimeFormat(dateString, timeString) {
        dateString = dayjs(dateString).format('YYYY-MM-DD');
        const completeDateTimeString = `${dateString} ${timeString}`;
        const formattedDateTime = dayjs(completeDateTimeString, 'YYYY-MM-DD HH:mm').format('YYYY-MM-DD HH:mm:ss');
        return formattedDateTime;
    }

    function convertDateFormat(dateString) {
        dateString = dayjs(dateString).format('YYYY-MM-DD');
        return dateString;
    }

    function clear(){
        ele('form1').clear();
        ele("form1").setValues({
            start_date: new Date(),
            stop_date: new Date()
        });
    }

    function findData() {

    
        /* ele("table_t1").filter(function (item) {
            return (
                item.truck_license.toString().toLowerCase().indexOf(formData.truck_license.toLowerCase()) !== -1 &&
                item.start_date.toString().toLowerCase().indexOf(formData.start_date.toLowerCase()) !== -1 &&
                item.start_time.toString().toLowerCase().indexOf(formData.start_time.toLowerCase()) !== -1 &&
                item.stop_date.toString().toLowerCase().indexOf(formData.stop_date.toLowerCase()) !== -1 &&
                item.stop_time.toString().toLowerCase().indexOf(formData.stop_time.toLowerCase()) !== -1
            );
        }); */
    }
    

	return {
        view: "scrollview",
        scroll: "native-y",
        id:"header_extraTruckTime",
        body: 
        {
        	id:"extraTruckTime_id",
        	type:"clean",
    		rows:
    		[
    		    { view: "form", id: $n("form1"), scroll: false,
                    elements: [
                      { rows: [
                        {
                            cols: [
                                { view: "text", label: "ID",name: "id", labelPosition : "top" ,disabled: true},
                                { view: "text", label: "Truck License",name: "truck_license",  labelPosition : "top" ,}
                            ]
                        },
                        {
                            cols: [
                                { 
                                    view: "datepicker", label: "Start Date",name: "start_date", labelPosition: "top" ,
                                    format: "%d/%m/%Y", value: new Date()
                                },
                                { view: "text", label: "Start Time (ตัวอย่าง 08:00)",name: "start_time",  labelPosition: "top" },
                                { 
                                    view: "datepicker", "label": "Stop Date",name: "stop_date",  labelPosition: "top" 
                                    ,format: "%d/%m/%Y", value: new Date()
                                },
                                { view: "text", label: "Stop Time (ตัวอย่าง 23:00)",name: "stop_time",   labelPosition: "top" }
                            ],
                        },
                        {
                            cols: [
                                { view: "button",label: "Find", 
                                click: function () {
                                    loadData(); // เรียกใช้ฟังก์ชัน findData() เมื่อกดปุ่ม "Find"
                                }},
                                { view: "button",label: "Save", 
                                    click: function () {
                                        var formValues = ele("form1").getValues();
                                        formValues.start_datetime = convertDateTimeFormat(formValues.start_date, formValues.start_time);
                                        formValues.stop_datetime = convertDateTimeFormat(formValues.stop_date, formValues.stop_time);
                                        var return_num = 0;
                                        if(formValues.id.length > 0){
                                            return_num = 22;
                                        }
                                        else{
                                            return_num = 12;
                                        };

                                        msBox('เพิ่มข้อมูลใหม่', function () {
                                            ajax(fd, formValues, return_num, function (json) {
                                                loadData();
                                                clear();
                                            }, null, function (json) { });
                                        }, null);
                                    }
                                },
                                { view: "button",label: "Clear", 
                                    click: function () {
                                        clear();
                                    }
                                }
                            ],
                        },
                    ]
                        },
                    ]
                  },
                  { view: "datatable", id: $n("table_t1"),
                    columns: [
                        { id: "edit", header: "&nbsp;", width: 50,
                            template: function (row) {
                                return "<span style='cursor:pointer' class='webix_icon fa-pencil'></span>";
                            }
                        },
                        { id: "id", header: "ID", width: 80 },
                        { id: "truck_license", header: "Truck License", width: 140 },
                        { id: "start_date", header: "Start Date", width: 100 },
                        { id: "start_time", header: "Start Time", width: 100 },
                        { id: "stop_date", header: "Stop Date", width: 100 },
                        { id: "stop_time", header: "Stop Time", width: 100 },
                        { id: "create_by", header: "Creater", width: 100 },
                        { id: "create_date", header: "Create Date", width: 180 },
                        { id: "update_by", header: "Updater", width: 100 },
                        { id: "update_date", header: "Update Date", width: 180 },
                        ],
                        onClick:
                        { "fa-pencil": function (e, t) {
                            const item = this.getItem(t);
                            ele('form1').parse(item);
                            }
                        },
                        scroll: false,
                        autoheight: true,
                        autowidth: false,
                        footer: false,
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