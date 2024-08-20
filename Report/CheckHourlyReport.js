var header_CheckHourlyReport = function () {
    var menuName = "CheckHourlyReport_", fd = "Report/" + menuName + "data.php";

    function init() {
        setStarDate();
        setLastDate();
        // ele('start_date').setValue('2024-02-01');
        // ele('stop_date').setValue('2024-02-29');
        loadData();
    };

    function ele(name) {
        return $$($n(name));
    };

    function $n(name) {
        return menuName + name;
    };

    function focus(name) {
        setTimeout(function () { ele(name).focus(); }, 100);
    };

    function setView(target, obj) {
        var key = Object.keys(obj);
        for (var i = 0, len = key.length; i < len; i++) {
            target[key[i]] = obj[key[i]];
        }
        return target;
    };

    function vw1(view, name, label, obj) {
        var v = { view: view, required: true, label: label, id: $n(name), name: name, labelPosition: "top" };
        return setView(v, obj);
    };

    function vw2(view, id, name, label, obj) {
        var v = { view: view, required: true, label: label, id: $n(id), name: name, labelPosition: "top" };
        return setView(v, obj);
    };

    function setTable(tableName, data) {
        if (!ele(tableName)) return;
        ele(tableName).clearAll();
        ele(tableName).parse(data);
        ele(tableName).filterByAll();
    };

    function loadData(btn) {
        var obj = ele('form1').getValues();
        ajax(fd, obj, 1, function (json) {
            setTable('dataT1', json.data);
        }, btn);
    };

    function getFirstDayOfMonth(year, month) {
        return new Date(year, month - 1, 1);
    }

    function setStarDate() {
        const date = new Date();
        const firstDay = getFirstDayOfMonth(
            date.getFullYear(),
            date.getMonth(),
        );
        ele('start_date').setValue(firstDay);
    }

    function getLastDayOfMonth(year, month) {
        return new Date(year, month, 0);
    };

    function setLastDate() {
        const date = new Date();
        const LastDay = getLastDayOfMonth(
            date.getFullYear(),
            date.getMonth(),
        );
        ele('stop_date').setValue(LastDay);
    };


    //เช็ค checkbox ทั้งหมด
    var d = [];

    for (var i = 1; i < 20; i++) {
        d.push({ id: i, value: "ID " + i, size: webix.uid(), checked: 0 });
    }

    webix.ui.datafilter.mCheckbox = webix.extend({
        refresh: function (master, node, config) {
            node.onclick = function () {
                this.getElementsByTagName("input")[0].checked = config.checked = !config.checked;
                var column = master.getColumnConfig(config.columnId);
                var checked = config.checked ? column.checkValue : column.uncheckValue;
                master.data.each(function (obj) {
                    //console.log("helo " + obj)
                    //getItemNode checks visibility, other code is default
                    //console.log(master.getItemNode(obj.id))
                    if (obj && master.getItemNode(obj.id)) {
                        obj[config.columnId] = checked;
                        master.callEvent("onCheck", [obj.id, config.columnId, checked]);
                        this.callEvent("onStoreUpdated", [obj.id, obj, "save"]);
                    }
                });
                master.refresh();
            };
        },
    }, webix.ui.datafilter.masterCheckbox);


    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_CheckHourlyReport",
        body:
        {
            id: "CheckHourlyReport_id",
            type: "clean",
            rows:
                [
                    {
                        view: "form",
                        id: $n("form1"),
                        elements:
                            [
                                {
                                    cols:
                                        [
                                            {
                                                rows: [
                                                    {}, vw1("button", 'btn_confirm', "Confirm All", {
                                                        width: 100, css: 'webix_primary', disabled: false,
                                                        on: {
                                                            onItemClick: function () {
                                                                // ele('btn_confirm').disable();
                                                                // ele('btn_cancel').disable();
                                                                var objArray = [];
                                                                ele("dataT1").eachRow(function (id) {
                                                                    if (this.getItem(id).checked == "on") {
                                                                        objArray.push(this.getItem(id).Load_ID);
                                                                    }
                                                                });

                                                                if (objArray.length == 0) {
                                                                    webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูล', callback: function () { } });
                                                                }
                                                                else {
                                                                    $.post(fd, { obj: objArray, type: 11 })
                                                                        .done(function (data) {
                                                                            var json = JSON.parse(data);
                                                                            var data1 = json.data;
                                                                            loadData();
                                                                            if (json.ch == 1) {
                                                                                webix.alert({
                                                                                    title: "<b>ข้อความจากระบบ</b>", type: "alert-complete", ok: 'ตกลง', text: "สำเร็จ", callback: function () { }
                                                                                });
                                                                            }
                                                                            else {
                                                                                webix.alert({
                                                                                    title: "<b>ข้อความจากระบบ</b>", type: "alert-error", ok: 'ตกลง', text: "เกิดข้อผิดพลาดโปรดลองอีกครั้ง", callback: function () { }
                                                                                });
                                                                            }
                                                                        });
                                                                }
                                                            }
                                                        }
                                                    }),
                                                ]
                                            },

                                            {
                                                rows: [
                                                    {}, vw1("button", 'btn_confirm_customer', "Confirm For Customer", {
                                                        width: 150, css: 'webix_primary', disabled: false,
                                                        on: {
                                                            onItemClick: function () {

                                                                var dtable = ele('dataT1');

                                                                // ele('btn_confirm').disable();
                                                                // ele('btn_cancel').disable();
                                                                var objArray = [];
                                                                ele("dataT1").eachRow(function (id) {
                                                                    if (this.getItem(id).checked == "on") {
                                                                        objArray.push(this.getItem(id).Load_ID);
                                                                    }
                                                                });


                                                                if (objArray.length == 0) {
                                                                    webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูล', callback: function () { } });
                                                                }
                                                                else {
                                                                    $.post(fd, { obj: objArray, type: 12 })
                                                                        .done(function (data) {
                                                                            var json = JSON.parse(data);
                                                                            var data1 = json.data;
                                                                            if (json.ch == 1) {
                                                                                webix.alert({
                                                                                    title: "<b>ข้อความจากระบบ</b>", type: "alert-complete", ok: 'ตกลง', text: "สำเร็จ", callback: function () {
                                                                                        loadData();
                                                                                    }
                                                                                });
                                                                            }
                                                                            else {
                                                                                webix.alert({
                                                                                    title: "<b>ข้อความจากระบบ</b>", type: "alert-error", ok: 'ตกลง', text: "เกิดข้อผิดพลาดโปรดลองอีกครั้ง", callback: function () { }
                                                                                });
                                                                            }
                                                                        });
                                                                }
                                                            }
                                                        }
                                                    }),
                                                ]
                                            },

                                            {
                                                rows: [{}, vw1("button", 'btn_confirm_partner', "Confirm For Partner", {
                                                    width: 150, css: 'webix_primary', disabled: false,
                                                    on: {
                                                        onItemClick: function () {
                                                            // ele('btn_confirm').disable();
                                                            // ele('btn_cancel').disable();
                                                            var objArray = [];
                                                            ele("dataT1").eachRow(function (id) {
                                                                if (this.getItem(id).checked == "on") {
                                                                    objArray.push(this.getItem(id).Load_ID);
                                                                }
                                                            });

                                                            if (objArray.length == 0) {
                                                                webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูล', callback: function () { } });
                                                            }
                                                            else {
                                                                $.post(fd, { obj: objArray, type: 13 })
                                                                    .done(function (data) {
                                                                        var json = JSON.parse(data);
                                                                        var data1 = json.data;
                                                                        if (json.ch == 1) {
                                                                            webix.alert({
                                                                                title: "<b>ข้อความจากระบบ</b>", type: "alert-complete", ok: 'ตกลง', text: "สำเร็จ", callback: function () {
                                                                                    loadData();
                                                                                }
                                                                            });
                                                                        }
                                                                        else {
                                                                            webix.alert({
                                                                                title: "<b>ข้อความจากระบบ</b>", type: "alert-error", ok: 'ตกลง', text: "เกิดข้อผิดพลาดโปรดลองอีกครั้ง", callback: function () { }
                                                                            });
                                                                        }
                                                                    });
                                                            }
                                                        }
                                                    }
                                                }),
                                                ]
                                            },

                                            {
                                                rows: [{}, vw1("button", 'btn_cancel', "Cancel All", {
                                                    width: 100, css: 'webix_primary', disabled: false,
                                                    on: {
                                                        onItemClick: function () {
                                                            // ele('btn_confirm').disable();
                                                            // ele('btn_cancel').disable();
                                                            var objArray = [];
                                                            ele("dataT1").eachRow(function (id) {
                                                                if (this.getItem(id).checked == "on") {
                                                                    objArray.push(this.getItem(id).Load_ID);
                                                                }
                                                            });

                                                            if (objArray.length == 0) {
                                                                webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูล', callback: function () { } });
                                                            }
                                                            else {
                                                                $.post(fd, { obj: objArray, type: 31 })
                                                                    .done(function (data) {
                                                                        var json = JSON.parse(data);
                                                                        var data1 = json.data;
                                                                        loadData();
                                                                        if (json.ch == 1) {
                                                                            webix.alert({
                                                                                title: "<b>ข้อความจากระบบ</b>", type: "alert-complete", ok: 'ตกลง', text: "สำเร็จ", callback: function () { }
                                                                            });
                                                                        }
                                                                        else {
                                                                            webix.alert({
                                                                                title: "<b>ข้อความจากระบบ</b>", type: "alert-error", ok: 'ตกลง', text: "เกิดข้อผิดพลาดโปรดลองอีกครั้ง", callback: function () { }
                                                                            });
                                                                        }
                                                                    });
                                                            }
                                                        }
                                                    }
                                                }),
                                                ]
                                            },
                                            {},

                                            vw1("datepicker", 'start_date', "Start Date", { value: dayjs().format("YYYY-MM-DD"), stringResult: true, ...datatableDateFormat, }),
                                            vw1("datepicker", 'stop_date', "Stop Date", { value: dayjs().format("YYYY-MM-DD"), stringResult: true, ...datatableDateFormat, }),
                                            {
                                                rows: [{},
                                                vw1("button", 'btnFind', "Find (ค้นหา)", {
                                                    width: 120, on:
                                                    {
                                                        onItemClick: function () {
                                                            var btn = this;
                                                            loadData(btn);
                                                        }
                                                    }
                                                }),
                                                ]
                                            },
                                        ]
                                },
                                {
                                    view: "datatable", id: $n('dataT1'), datatype: "json", headerRowHeight: 20, rowLineHeight: 20, rowHeight: 20,
                                    footer: true, autoheight: true, hover: false, editable: true,
                                    css: { "font-size": "8px" }, resizeColumn: true, scroll: true, hidden: false, header: true,
                                    datafetch: 50, // Number of rows to fetch at a time
                                    loadahead: 100, // Number of rows to prefetch
                                    //pager: $n("Master_pagerA"),
                                    columns:
                                        [
                                            {
                                                id: "checked", header: [{ content: "mCheckbox", contentId: "mc1" }],
                                                checkValue: "on", uncheckValue: "off", width: 40,
                                                template: "{common.checkbox()}"
                                            },
                                            { id: "NO", header: [{ text: "No.", css: { "text-align": "center" }, }], css: { "text-align": "center" }, width: 40 },

                                            {
                                                id: "check_partner", header: { text: "Customer", css: { "text-align": "center" } }, width: 60, css: { "text-align": "center" },
                                                template: function (row) {
                                                    if (row.select_for_customer == 'Y') {
                                                        return "<span style='cursor:pointer; color:green;' class='webix_icon fa-check-circle'></span>";
                                                    }
                                                    else {
                                                        return "<span style='cursor:pointer; color:red;' class='webix_icon fa-times-circle'></span>";
                                                    }
                                                }
                                            },
                                            {
                                                id: "check_partner", header: { text: "Partner", css: { "text-align": "center" } }, width: 60, css: { "text-align": "center" },
                                                template: function (row) {
                                                    if (row.select_for_partner == 'Y') {
                                                        return "<span style='cursor:pointer; color:green;' class='webix_icon fa-check-circle-o'></span>";
                                                    }
                                                    else {
                                                        return "<span style='cursor:pointer; color:red;' class='webix_icon fa-times-circle-o'></span>";
                                                    }
                                                }
                                            },
                                            { id: "select_for_customer", header: [{ text: "Customer", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 60, },
                                            { id: "select_for_partner", header: [{ text: "Partner", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 60, },
                                            { id: "projectName", header: [{ text: "Project", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 90, },
                                            { id: "truck_carrier", header: [{ text: "Truck Carrier", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 90, },
                                            { id: "operation_date", header: [{ text: "Operation Date", css: { "text-align": "center" } }, { content: "dateFilter" }], css: { "text-align": "center" }, width: 90, },
                                            { id: "Start_Datetime", header: [{ text: "Pickup Date", css: { "text-align": "center" } }, { content: "dateFilter" }], css: { "text-align": "center" }, width: 80, },
                                            { id: "End_Datetime", header: [{ text: "Delivery Date", css: { "text-align": "center" } }, { content: "dateFilter" }], css: { "text-align": "center" }, width: 90, },
                                            { id: "Load_ID", header: [{ text: "Load ID.", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 120, },
                                            { id: "Route", header: [{ text: "Route", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 100, },
                                            { id: "Work_Type", header: [{ text: "Status", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 80, },
                                            { id: "truckType", header: [{ text: "Truck Type", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 80, },
                                            { id: "tripType", header: [{ text: "Status Route Customer", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 140, },
                                            { id: "tripType_partner", header: [{ text: "Status Route Partner", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 140, },
                                            { id: "distanceBand", header: [{ text: "Distance Band", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 90, },
                                        ],
                                    onClick:
                                    {
                                        "fa-times-circle": function (e, t) {
                                            var row = this.getItem(t), dtable = this;
                                            $.post(fd, { obj: row, type: 21 })
                                                .done(function (data) {
                                                    var json = JSON.parse(data);
                                                    if (json.ch != 1) {
                                                        webix.alert({
                                                            title: "<b>ข้อความจากระบบ</b>", type: "alert-error", ok: 'ตกลง', text: "เกิดข้อผิดพลาดโปรดลองอีกครั้ง", callback: function () { }
                                                        });
                                                    }
                                                    else {
                                                        dtable.updateItem(t.row, { select_for_customer: 'Y' });
                                                    }
                                                });
                                        },
                                        "fa-check-circle": function (e, t) {
                                            var row = this.getItem(t), dtable = this;
                                            $.post(fd, { obj: row, type: 22 })
                                                .done(function (data) {
                                                    var json = JSON.parse(data);
                                                    if (json.ch != 1) {
                                                        webix.alert({
                                                            title: "<b>ข้อความจากระบบ</b>", type: "alert-error", ok: 'ตกลง', text: "เกิดข้อผิดพลาดโปรดลองอีกครั้ง", callback: function () { }
                                                        });
                                                    }
                                                    else {
                                                        dtable.updateItem(t.row, { select_for_customer: 'N' });
                                                    }
                                                });
                                        },

                                        "fa-times-circle-o": function (e, t) {
                                            var row = this.getItem(t), dtable = this;
                                            $.post(fd, { obj: row, type: 23 })
                                                .done(function (data) {
                                                    var json = JSON.parse(data);
                                                    if (json.ch != 1) {
                                                        webix.alert({
                                                            title: "<b>ข้อความจากระบบ</b>", type: "alert-error", ok: 'ตกลง', text: "เกิดข้อผิดพลาดโปรดลองอีกครั้ง", callback: function () { }
                                                        });
                                                    }
                                                    else {
                                                        dtable.updateItem(t.row, { select_for_partner: 'Y' });
                                                    }
                                                });
                                        },
                                        "fa-check-circle-o": function (e, t) {
                                            var row = this.getItem(t), dtable = this;
                                            $.post(fd, { obj: row, type: 24 })
                                                .done(function (data) {
                                                    var json = JSON.parse(data);
                                                    if (json.ch != 1) {
                                                        webix.alert({
                                                            title: "<b>ข้อความจากระบบ</b>", type: "alert-error", ok: 'ตกลง', text: "เกิดข้อผิดพลาดโปรดลองอีกครั้ง", callback: function () { }
                                                        });
                                                    }
                                                    else {
                                                        dtable.updateItem(t.row, { select_for_partner: 'N' });
                                                    }
                                                });
                                        },
                                    },
                                },
                            ]
                    }
                ], on:
            {
                onHide: function () {

                },
                onShow: function () {

                },
                onAddView: function () {
                    init();
                }
            }
        }
    };
};