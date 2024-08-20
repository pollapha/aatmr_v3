var header_TripQuotation = function () {
    var menuName = "TripQuotation_", fd = "MasterData/" + menuName + "data.php";

    function init() {
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
        ajax(fd, {}, 1, function (json) {
            setTable('dataT1', json.data);
        }, btn);
    };

    function exportExcel(btn) {
        var dataT1 = ele("dataT1"), obj = {}, data = [];
        if (dataT1.count() == 0) {
            webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูลในตาราง', callback: function () { } });
        }

        for (var i = -1, len = dataT1.config.columns.length; ++i < len;) {
            obj[dataT1.config.columns[i].id] = dataT1.config.columns[i].header[0].text;
        }

        delete obj.data22;
        delete obj.createBy;
        delete obj.createDatetime;
        delete obj.updateBy;
        delete obj.updateDatetime;

        var objKey = Object.keys(obj);
        var f = [];
        for (var i = -1, len = objKey.length; ++i < len;) {
            f.push(objKey[i]);
        }
        var col = [];
        for (var i = -1, len = f.length; ++i < len;) {
            col[col.length] = obj[f[i]];
        }
        data[data.length] = col;
        if (dataT1.count() > 0) {
            btn.disable();
            dataT1.eachRow(function (row) {
                var r = dataT1.getItem(row), rr = [];
                for (var i = -1, len = f.length; ++i < len;) {
                    rr[rr.length] = r[f[i]];
                }
                data[data.length] = rr;
            });

            var worker = new Worker('js/workerToExcel.js?v=2');
            worker.addEventListener('message', function (e) {
                saveAs(e.data, 'TripQuotation_' + new Date().getTime() + ".xlsx");
                btn.enable();
                webix.message({ expire: 7000, text: "Export สำเร็จ" });
            }, false);
            worker.postMessage({ 'cmd': 'start', 'msg': data });
        }
        else { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: "ไม่พบข้อมูลในตาราง", callback: function () { } }); }
    };

    //add
    webix.ui(
        {
            view: "window", id: $n("win_template"), modal: 1,
            head: "Add (เพิ่มข้อมูล)", top: 50, position: "center",
            close: true, move: true,
            body:
            {
                view: "form", scroll: false, id: $n("win_template_form"), width: 600,
                elements:
                    [
                        {
                            cols:
                                [
                                    {
                                        paddingX: 20,
                                        paddingY: 10,
                                        rows:
                                            [
                                                {
                                                    cols: [
                                                        vw1("datepicker", 'start_date', "First Date of Month", { value: dayjs().format("YYYY-MM-DD"), stringResult: true, ...datatableDateFormat, }),

                                                        {
                                                            view: "combo",
                                                            label: "Billing For", labelPosition: "top", required: true,
                                                            id: $n("billing_for"),
                                                            name: "billing_for",
                                                            options: ["Customer", "Partner"],
                                                            on: {
                                                                onChange: function (newVal) {
                                                                    var secondCombo = ele("project_name");
                                                                    if (newVal === "Customer") {
                                                                        secondCombo.define("options", ["AAT MR", "FTM MR", "AAT EDC"]);
                                                                    }
                                                                    else if (newVal === "Partner") {
                                                                        secondCombo.define("options", []);
                                                                    }
                                                                    secondCombo.refresh();
                                                                },
                                                            },
                                                        },
                                                        {
                                                            view: "combo", label: "Project", labelPosition: "top", name: "project_name", id: $n("project_name"), required: true, options: ["ALL"],
                                                        },

                                                    ],
                                                },
                                            ]
                                    }
                                ]
                        },
                        {
                            cols:
                                [
                                    {},
                                    vw1('button', 'btn_download_template', 'Download Template', {
                                        width: 150,
                                        on: {
                                            onItemClick: function () {
                                                var obj = ele('win_template_form').getValues();
                                                ajax(fd, obj, 51, function (json) {
                                                    webix.alert({
                                                        title: "<b>ข้อความจากระบบ</b>", type: "alert-complete", ok: 'ตกลง', text: 'Export Complete', callback: function () {
                                                            window.location.href = 'MasterData/' + json.data;
                                                            ele('win_template').hide();
                                                            ele('start_date').setValue(new Date());
                                                        }
                                                    });
                                                }, null,
                                                    function (json) {
                                                        /* ele('find').callEvent("onItemClick", []); */
                                                    });
                                            }
                                        }
                                    }),
                                    vw1('button', 'cancel', 'Cancel', {
                                        width: 120,
                                        on: {
                                            onItemClick: function () {
                                                ele('win_template').hide();
                                                ele('start_date').setValue(new Date());
                                            }
                                        }
                                    }),
                                ]
                        }
                    ],
                rules:
                {
                }
            }
        });

    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_TripQuotation",
        body:
        {
            id: "TripQuotation_id",
            type: "clean",
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
                                            vw1("button", 'btnExport', "Export (โหลดเป็นไฟล์เอ๊กเซล)", {
                                                width: 170, on:
                                                {
                                                    onItemClick: function () {
                                                        var btn = this;
                                                        exportExcel(this);
                                                    }
                                                }
                                            }),
                                            {
                                                view: "button", id: $n('export_template'), label: "Template Upload", width: 180, hidden: 0,
                                                on:
                                                {
                                                    onItemClick: async (id, e) => {
                                                        ele('win_template').show();
                                                        //window.location.href = 'MasterData/template_upload/template_upload_trip_quotation.xlsx';

                                                    }
                                                }
                                            },

                                            vw1("uploader", 'Upload_Quotation', "Upload Quotation", {
                                                width: 200, hidden: false, multiple: false, autosend: false, on:
                                                {
                                                    onBeforeFileAdd: function (file) {
                                                        var type = file.type.toLowerCase();
                                                        if (type == "csv" || type == "xlsx" || type == "xls") {

                                                        }
                                                        else {
                                                            webix.alert({ title: "<b>ข้อความจากระบบ</b>", text: "รองรับ CSV ,XLS ,XLSX เท่านั้น", type: 'alert-error' });
                                                            return false;
                                                        }
                                                        //ele("Upload_Quotation").disable();
                                                    },
                                                    onAfterFileAdd: function (item) {
                                                        var formData = new FormData();
                                                        this.files.data.each(function (obj, i) {
                                                            formData.append("upload", obj.file);
                                                        });
                                                        $.ajax({
                                                            type: 'POST',
                                                            cache: false,
                                                            contentType: false,
                                                            processData: false,
                                                            url: fd + '?type=41',
                                                            data: formData,
                                                            success: function (data) {
                                                                //ele("Upload_Quotation").enable();
                                                                loadData();
                                                                var json = JSON.parse(data);
                                                                webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: json.mms, callback: function () { } });
                                                            }
                                                        });
                                                    },
                                                },
                                            }),
                                            {},
                                        ]
                                },
                                {
                                    view: "datatable", id: $n('dataT1'), datatype: "json",
                                    headerRowHeight: 20, rowLineHeight: 20, rowHeight: 20,
                                    hover: "webix_row_select", editable: true,
                                    css: { "font-size": "12px" }, resizeColumn: true,
                                    pager: $n("Master_pagerA"),
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
                                            { id: "start_date", header: ["Start Date", { content: "selectFilter" }], editor: "", width: 100 },
                                            //{ id: "stop_date", header: ["Stop Date", { content: "selectFilter" }], editor: "", width: 100 },
                                            { id: "Work_Type", header: ["Work Type", { content: "selectFilter" }], editor: "", width: 100 },
                                            { id: "tripType", header: ["Trip Type", { content: "selectFilter" }], editor: "", width: 100 },
                                            { id: "truckType", header: ["Truck Type", { content: "selectFilter" }], editor: "", width: 100 },
                                            { id: "distanceBand", header: ["Distance", { content: "selectFilter" }], editor: "", width: 100 },
                                            { id: "unitRate", header: ["Unit Rate", { content: "textFilter" }], editor: "text", width: 100 },
                                            { id: "billing_for", header: ["Billing for", { content: "selectFilter" }], editor: "", width: 100 },
                                            { id: "project", header: ["Project", { content: "selectFilter" }], editor: "", width: 100 },
                                            //{ id: "bailment", header: ["Bailment", { content: "selectFilter" }], editor: "", width: 100 },
                                            { id: "createBy", header: "Creation User", editor: "", width: 120 },
                                            { id: "createDatetime", header: "Creation DateTime", editor: "", width: 150 },
                                            { id: "updateBy", header: "Updated User", editor: "", width: 120 },
                                            { id: "updateDatetime", header: "Last Updated DateTime", editor: "", width: 150 },

                                        ],
                                    onClick:
                                    {
                                        "fa-check": function (e, t) {
                                            var row = this.getItem(t), dataTable = this;
                                            ajax(fd, row, 21, function (json) {
                                                row.change = 0;
                                                dataTable.updateItem(t.row, row);
                                                webix.message({ expire: 7000, text: "บันทึกสำเร็จ" });
                                            }, null);
                                        },
                                    },
                                    on:
                                    {
                                        "onEditorChange": function (id, value) {
                                            var row = this.getItem(id.row), dataTable = this;
                                            row.change = 1;
                                            dataTable.updateItem(id.row, row);
                                        },
                                        /* "onBeforeLoad": function () {
                                            this.getColumnConfig("projectName").collection = json.data;
                                            this.refreshColumns();

                                        },
                                        "onbeforeeditstop": function (value, editor) {
                                            if (editor.column === 'projectName') {
                                                let obj = { row: editor.row, column: editor.column };
                                                this.callEvent("onEditorChange", [obj, value.value]);
                                            }

                                        } */
                                    }
                                },
                                {
                                    type: "wide",
                                    cols:
                                        [
                                            {
                                                view: "pager", id: $n("Master_pagerA"),
                                                template: function (data, common) {
                                                    var start = data.page * data.size
                                                        , end = start + data.size;
                                                    if (data.count == 0) start = 0;
                                                    else start += 1;
                                                    if (end >= data.count) end = data.count;
                                                    var html = "<b>showing " + (start) + " - " + end + " total " + data.count + " </b>";
                                                    return common.first() + common.prev() + " " + html + " " + common.next() + common.last();
                                                },
                                                size: 259,
                                                group: 5
                                            }
                                        ]
                                }
                            ]
                    },

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