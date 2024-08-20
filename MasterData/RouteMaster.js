var header_RouteMaster = function () {
    var menuName = "RouteMaster_", fd = "MasterData/" + menuName + "data.php";

    ajax('MasterData/ProjectMaster_data.php', {}, 2, function (json) {
        var obj =
        {
            view: "datatable", id: $n('dataT1'), datatype: "json",
            headerRowHeight: 20, rowLineHeight: 20, rowHeight: 20,
            hover: "webix_row_select", editable: true,
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
                    { id: "routeName", header: ["Route Name", { content: "textFilter" }], editor: "text", width: 150 },
                    { id: "projectName", header: ["Project Name", { content: "selectFilter" }], editor: "combo", width: 150 },
                    { id: "distanceBand", header: ["Distance Band", { content: "textFilter" }], editor: "text", width: 150 },
                    // { id: "bailment", header: ["Bailment", { content: "selectFilter" }], editor: "combo", width: 150 },
                    { id: "createBy", header: "Creation User", editor: "", width: 150 },
                    { id: "createDatetime", header: "Creation DateTime", editor: "", width: 150 },
                    { id: "updateBy", header: "Updated User", editor: "", width: 150 },
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
                "onBeforeLoad": function () {
                    this.getColumnConfig("projectName").collection = json.data;
                    this.refreshColumns();

                },
                "onbeforeeditstop": function (value, editor) {
                    if (editor.column === 'projectName') {
                        let obj = { row: editor.row, column: editor.column };
                        this.callEvent("onEditorChange", [obj, value.value]);
                    }

                }
            }
        };

        ele('form1').addView(obj);
        loadData(null);
    }, null, function (json) {

    });


    function init() {
        // loadData();
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
                saveAs(e.data, 'RouteMaster_' + new Date().getTime() + ".xlsx");
                btn.enable();
                webix.message({ expire: 7000, text: "Export สำเร็จ" });
            }, false);
            worker.postMessage({ 'cmd': 'start', 'msg': data });
        }
        else { webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: "ไม่พบข้อมูลในตาราง", callback: function () { } }); }
    };

    webix.ui(
        {
            view: "window", move: true, modal: true, id: $n("win_Upload"),
            on:
            {
                onShow: function () {

                },
            },
            head: {
                view: "toolbar", margin: -4,
                cols:
                    [
                        { width: 50 },
                        { view: "label", label: "Upload Text", height: 30, align: "center" },
                        {
                            view: "icon", icon: "times", width: 50, click: function () {
                                this.getTopParentView().hide();
                            },
                        }
                    ],
            }, top: 50, position: "center", width: window.innerWidth, height: window.innerHeight,
            body:
            {
                view: "scrollview", body:
                {
                    rows:
                        [
                            {
                                cols:
                                    [
                                        {},
                                        vw1("button", 'btnSaveUpload', "Save (บันทึก)", {
                                            width: 170, on:
                                            {
                                                onItemClick: function () {
                                                    var btn = this;
                                                    var obj = {};
                                                    var data = ele('upload_text').getValue();

                                                    if (data.length > 0) {
                                                        setTimeout(function (param) {
                                                            obj.upload_text = param;
                                                            console.log(obj)
                                                            ajax(fd, obj, 31, function (json) {
                                                                ele('win_Upload').hide();
                                                                webix.alert({ title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: "อัพโหลดสำเร็จ", callback: function () { } });
                                                                setTable('dataT1', json.data);
                                                            }, btn, function (json) {
                                                            });
                                                        }, 100, data);
                                                    }
                                                }
                                            }
                                        }),
                                        {},
                                    ]
                            },
                            {
                                cols:
                                    [
                                        {},
                                        vw1("textarea", 'upload_text', "[ชื่อ] [นามสกุล] [เบอร์ติดต่อ]",
                                            {
                                                width: 500, height: 200, required: true
                                            }),
                                        {}
                                    ]
                            }
                        ]

                }
            }
        });

    webix.ui(
        {
            view: "window", move: true, modal: true, id: $n("win_Addnew"),
            on:
            {
                onShow: function () {
                    this.getBody().scrollTo(0, 0);
                    var dataTable = [];
                    for (var i = 0, len = 10; i < len; i++) {
                        var obj =
                        {
                            NO: i + 1,
                            routeName: '',
                            projectName: '',
                        };
                        dataTable.push(obj);
                    }
                    setTable('dataT2', dataTable);
                    ele('dataT2').validate();
                },
            },
            head: {
                view: "toolbar", margin: -4,
                cols:
                    [
                        { width: 50 },
                        { view: "label", label: "Add New (เพิ่ม)", height: 30, align: "center" },
                        {
                            view: "icon", icon: "times", width: 50, click: function () {
                                this.getTopParentView().hide();
                            },
                        }
                    ],
            }, top: 50, position: "center", width: window.innerWidth, height: window.innerHeight,
            body:
            {
                view: "scrollview", body:
                {
                    rows:
                        [
                            {
                                cols:
                                    [
                                        {},
                                        vw1("button", 'btnSaveAdd', "Save (บันทึก)", {
                                            width: 170, on:
                                            {
                                                onItemClick: function () {
                                                    var btn = this;
                                                    var obj = {};
                                                    var table = ele('dataT2');
                                                    table.editStop();
                                                    var data = table.serialize(), dataSelect = [], obj = {};
                                                    for (i = 0, len = data.length; i < len; i++) {
                                                        if (data[i].routeName.length > 2 && data[i].projectName.length > 2) {
                                                            dataSelect.push(data[i]);
                                                        }
                                                    }


                                                    if (dataSelect.length > 0) {
                                                        for (i = 0, len = dataSelect.length; i < len; i++) {
                                                            setTimeout(function (param) {
                                                                ajax(fd, param, 11, function (json) {
                                                                    setTable('dataT1', json.data);
                                                                }, null);
                                                                ele('win_Addnew').hide();
                                                            }, 100 * i, dataSelect[i]);
                                                        }
                                                    }
                                                }
                                            }
                                        }),
                                        {},
                                    ]
                            },
                            {
                                cols:
                                    [
                                        {},
                                        {
                                            view: "datatable", id: $n('dataT2'), datatype: "json",
                                            headerRowHeight: 20, rowLineHeight: 20, rowHeight: 20,
                                            css: { "font-size": "12px" }, resizeColumn: true, editable: true,
                                            rules:
                                            {
                                                projectName: function (obj) { return obj.length > 1; },
                                            },
                                            on:
                                            {
                                                "onKeyPress": function (code, e) {
                                                    var editor = this.getEditState();
                                                    var table = this;
                                                    if (e.key == "Enter") {
                                                        if (editor) {
                                                            if (editor.config.suggest) {
                                                                var popup = $$(editor.config.suggest);
                                                                var list = popup.getBody();
                                                                if (list.count() > 0) {
                                                                    editor.setValue(list.getItem(list.getFirstId()).value);
                                                                }
                                                            }
                                                            nextCellHorizontal(this, editor);
                                                        }
                                                    }
                                                },
                                            },
                                            columns:
                                                [
                                                    { id: "NO", header: ["NO."], editor: "", width: 60 },
                                                    { id: "routeName", header: ["Route Name"], editor: "text", width: 100 },
                                                    { id: "projectName", header: ["Project Name"], editor: "combo", collection: "MasterData/ProjectMaster_data.php?type=2", width: 150 },
                                                    { id: "distanceBand", header: ["Distance Band"], editor: "text", width: 100 },
                                                ],
                                        },
                                        {}
                                    ]
                            }
                        ]

                }
            }
        });


    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_RouteMaster",
        body:
        {
            id: "RouteMaster_id",
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
                                            vw1("button", 'btnAddNew', "Add New (เพิ่ม)", {
                                                width: 170, on:
                                                {
                                                    onItemClick: function () {
                                                        var btn = this;
                                                        ele('win_Addnew').show();
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
                                            /* vw1("button", 'btnUpload', "Upload Text", {
                                                width: 170, on:
                                                {
                                                    onItemClick: function () {
                                                        var btn = this;
                                                        ele('win_Upload').show();
                                                    }
                                                }
                                            }), */
                                            vw1("uploader", 'Upload_Route', "Upload Update Route", {
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
                                                        ele("Upload_Route").disable();
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
                                                            url: fd + '?type=51',
                                                            data: formData,
                                                            success: function (data) {
                                                                ele("Upload_Route").enable();
                                                                webix.message({
                                                                    type: "success",
                                                                    text: "Update Complete",
                                                                    expire: 5000
                                                                })
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