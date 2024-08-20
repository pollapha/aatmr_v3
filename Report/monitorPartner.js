var header_monitorPartner = function () {
    var menuName = "monitorPartner_", fd = "Report/" + menuName + "data.php";

    function init() {
        setStarDate();
        setLastDate();
        // ele('start_date').setValue('2024-01-01');
        // ele('stop_date').setValue('2024-01-31');
        //loadDataPartner();
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

    function loadDataPartner() {
        var obj = ele("form1").getValues();
        ajax(fd, obj, 1, function (json) {
            setTable('data_partner', json.data);
        }, null,
            function (json) {
                ele('data_partner').clearAll();
            });
    };

    function loadDataTripSummaryReport(row) {
        var obj1 = ele("form1").getValues();
        var obj = { ...obj1, ...row };
        ajax(fd, obj, 3, function (json) {
            setTable('data_trip_summary_report', json.data);
        }, null,
            function (json) {
                ele('data_trip_summary_report').clearAll();
            },);
    };


    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_monitorPartner",
        body:
        {
            id: "monitorPartner_id",
            type: "clean",
            rows:
                [
                    {
                        view: "form", id: $n("form1"),
                        on:
                        {
                            "onSubmit": function (view, e) {

                                if (view.config.name == 'pr_no') {
                                    var obj = ele("form1").getValues();
                                    ele('data_partner').clearAll();
                                    ajax(fd, obj, 2, function (json) {
                                        setTable('data_trip_summary_report', json.data);
                                    }, null,
                                        function (json) {
                                            ele('data_trip_summary_report').clearAll();
                                        },);
                                }
                                else if (webix.UIManager.getNext(view).config.type == 'line') {
                                    webix.UIManager.setFocus(webix.UIManager.getNext(webix.UIManager.getNext(view)));
                                }
                                else {
                                    webix.UIManager.setFocus(webix.UIManager.getNext(view));
                                }
                            },
                        },
                        rows: [
                            {
                                cols: [
                                    vw1("datepicker", 'start_date', "Start Date", { value: dayjs().format("YYYY-MM-DD"), stringResult: true, ...datatableDateFormat, width: 200, }),
                                    vw1("datepicker", 'stop_date', "Stop Date", { value: dayjs().format("YYYY-MM-DD"), stringResult: true, ...datatableDateFormat, width: 200, }),
                                    {
                                        rows: [
                                            {},
                                            {
                                                view: "button", id: $n('find_partner'), label: "Find", width: 100,
                                                on:
                                                {
                                                    onItemClick: async (id, e) => {
                                                        var obj = ele("form1").getValues();
                                                        ajax(fd, obj, 1, function (json) {
                                                            setTable('data_partner', json.data);
                                                        }, null,
                                                            function (json) {
                                                                ele('data_partner').clearAll();
                                                            },);
                                                    }
                                                }
                                            }
                                        ]
                                    },
                                    {
                                        rows: [
                                            {},
                                            {
                                                view: "button", id: $n('clear_date'), label: "Clear", width: 100,
                                                on:
                                                {
                                                    onItemClick: async (id, e) => {
                                                        setStarDate();
                                                        setLastDate();
                                                        // ele('start_date').setValue('2023-11-01');
                                                        // ele('stop_date').setValue('2023-11-30');
                                                        //ele('pr_no').setValue('');
                                                        ele('data_partner').clearAll();
                                                        ele('data_trip_summary_report').clearAll();
                                                    }
                                                }
                                            },
                                        ]
                                    },
                                    {},
                                    vw1('text', 'pr_no', 'PR No.', { labelPosition: "top", required: false, width: 200, disabled: false }),
                                    // {
                                    //     rows: [
                                    //         {},
                                    //         {
                                    //             view: "button", id: $n('find_pr'), label: "Find", width: 100,
                                    //             on:
                                    //             {
                                    //                 onItemClick: async (id, e) => {
                                    //                     var obj = ele("form1").getValues();
                                    //                     ajax(fd, obj, 2, function (json) {
                                    //                         setTable('data_trip_summary_report', json.data);
                                    //                     }, null,
                                    //                         function (json) {
                                    //                             ele('data_trip_summary_report').clearAll();
                                    //                         },);
                                    //                 }
                                    //             }
                                    //         },
                                    //     ]
                                    // },
                                    {
                                        rows: [
                                            {},
                                            {
                                                view: "button", id: $n('clear_all'), label: "Clear", width: 100,
                                                on:
                                                {
                                                    onItemClick: async (id, e) => {
                                                        // setStarDate();
                                                        // setLastDate();
                                                        // ele('start_date').setValue('2023-11-01');
                                                        // ele('stop_date').setValue('2023-11-30');
                                                        ele('pr_no').setValue('');
                                                        //ele('data_partner').clearAll();
                                                        ele('data_trip_summary_report').clearAll();
                                                    }
                                                }
                                            },
                                        ]
                                    },
                                ]
                            },
                            {
                                cols: [
                                    {
                                        rows: [
                                            {
                                                view: "datatable", id: $n('data_partner'), datatype: "json", headerRowHeight: 20, rowLineHeight: 20, rowHeight: 20,
                                                footer: false, autoheight: true, hover: "myhover", editable: true, navigation: true,
                                                css: { "font-size": "12px" }, resizeColumn: true, scroll: false, hidden: false,
                                                header: true,
                                                autowidth: true,
                                                select: "row",
                                                //blockselect:true,
                                                scheme:
                                                {
                                                    $change: function (item) {
                                                        // if (item.status_carrier == 'On Progress') {
                                                        //     item.$css = { "background": "#ffffb2", "font-weight": "bold" };
                                                        // }
                                                        if (item.status_carrier == 'Completed') {
                                                            item.$css = { "background": "#afeac8", "font-weight": "bold" };
                                                        }
                                                    }
                                                },
                                                columns:
                                                    [
                                                        { id: "NO", header: [{ text: "No.", css: { "text-align": "center" } }], css: { "text-align": "center" }, width: 40 },
                                                        //{ id: "row_no", header: [{ text: "Item", css: { "text-align": "center" } }], css: { "text-align": "center" }, width: 40 },
                                                        { id: "projectName", header: [{ text: "Project", css: { "text-align": "center" } }], css: { "text-align": "center" }, width: 60, },
                                                        { id: "truck_carrier", header: [{ text: "Partner", css: { "text-align": "center" } }], css: { "text-align": "center" }, width: 60, },
                                                        { id: "status_carrier", header: [{ text: "Status", css: { "text-align": "center" } }], css: { "text-align": "center" }, width: 70, },
                                                    ],
                                                on: {
                                                    // onItemClick: function (id) {
                                                    //     var row = this.getItem(id), dataTable = this;
                                                    //     loadDataTripSummaryReport(row);
                                                    // },
                                                    onAfterSelect: function (id) {
                                                        var row = this.getItem(id), dataTable = this;
                                                        loadDataTripSummaryReport(row);
                                                    }
                                                }
                                            },
                                            {}
                                        ]
                                    },
                                    { width: 10 },
                                    {
                                        rows: [
                                            {
                                                cols: [
                                                    {
                                                        view: "datatable", id: $n('data_trip_summary_report'), datatype: "json", headerRowHeight: 20, rowLineHeight: 20, rowHeight: 20,
                                                        footer: true, autoheight: true, hover: false, editable: true,
                                                        css: { "font-size": "8px" }, resizeColumn: true, scroll: true, hidden: false, header: true,
                                                        pager: $n("Master_pagerA"),
                                                        columns:
                                                            [
                                                                { id: "NO", header: [{ text: "No.", css: { "text-align": "center" }, }], css: { "text-align": "center" }, width: 40 },
                                                                { id: "pr_no", header: [{ text: "PR No.", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 100, },
                                                                { id: "invoice_no", header: [{ text: "Invoice No.", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 100, hidden: 1 },
                                                                { id: "projectName", header: [{ text: "Project", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 90, },
                                                                { id: "truck_carrier", header: [{ text: "Partner", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 90, },
                                                                { id: "operation_date", header: [{ text: "Operation Date", css: { "text-align": "center" } }, { content: "dateFilter" }], css: { "text-align": "center" }, width: 90, },
                                                                { id: "Start_Datetime", header: [{ text: "Pickup Date", css: { "text-align": "center" } }, { content: "dateFilter" }], css: { "text-align": "center" }, width: 80, },
                                                                { id: "End_Datetime", header: [{ text: "Delivery Date", css: { "text-align": "center" } }, { content: "dateFilter" }], css: { "text-align": "center" }, width: 90, },
                                                                { id: "Load_ID", header: [{ text: "Tracking no.", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 180, },
                                                                { id: "Route", header: [{ text: "Route no.", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 100, },
                                                                //{ id: "Internal_Tracking", header: [{ text: "Internal Tracking No.", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 100, },
                                                                { id: "Work_Type", header: [{ text: "Status", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 80, },
                                                                { id: "truckType", header: [{ text: "Truck Type", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 80, },
                                                                { id: "tripType", header: [{ text: "One way / Round trip", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 120, },
                                                                { id: "distanceBand", header: [{ text: "Distance Band", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 90, },
                                                                {
                                                                    id: "unitRate", header: [{ text: "Unit Rate", css: { "text-align": "center" } }, { content: "numberFilter" }], width: 70, css: { "text-align": "right" }, footer: [{ text: "Total :", css: { "text-align": "right" } }],
                                                                    format: webix.Number.numToStr({
                                                                        groupDelimiter: ",",
                                                                        groupSize: 3,
                                                                    })
                                                                },
                                                                {
                                                                    id: "total", header: [{ text: "Amount", css: { "text-align": "center" } }, { content: "numberFilter" }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right", } }],
                                                                    format: webix.Number.numToStr({
                                                                        groupDelimiter: ",",
                                                                        groupSize: 3,
                                                                    })
                                                                },
                                                            ],
                                                    },
                                                ]
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
                                                            size: 15,
                                                            group: 5
                                                        }
                                                    ]
                                            }
                                            // {
                                            //     view: "datatable", id: $n('data_trip_summary_report'), datatype: "json", headerRowHeight: 20, rowLineHeight: 20, rowHeight: 20,
                                            //     footer: true, autoheight: true, hover: false, editable: true,
                                            //     css: { "font-size": "8px" }, resizeColumn: true, scroll: false, hidden: false,
                                            //     header: true,
                                            //     autowidth: true,
                                            //     pager: "page_data_trip_summary_report_partner",
                                            //     columns:
                                            //         [
                                            //             { id: "NO", header: [{ text: "No.", css: { "text-align": "center" }, }], css: { "text-align": "center" }, width: 40 },
                                            //             { id: "truck_carrier", header: [{ text: "Partner", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 90, },
                                            //             { id: "operation_date", header: [{ text: "Operation Date", css: { "text-align": "center" } }, { content: "dateFilter" }], css: { "text-align": "center" }, width: 90, },
                                            //             { id: "Start_Datetime", header: [{ text: "Pickup Date", css: { "text-align": "center" } }, { content: "dateFilter" }], css: { "text-align": "center" }, width: 80, },
                                            //             { id: "End_Datetime", header: [{ text: "Delivery Date", css: { "text-align": "center" } }, { content: "dateFilter" }], css: { "text-align": "center" }, width: 90, },
                                            //             { id: "Load_ID", header: [{ text: "Tracking no.", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 100, },
                                            //             { id: "Route", header: [{ text: "Route no.", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 100, },
                                            //             { id: "Internal_Tracking", header: [{ text: "Internal Tracking No.", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 100, },
                                            //             { id: "Work_Type", header: [{ text: "Status", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 80, },
                                            //             { id: "truckType", header: [{ text: "Truck Type", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 80, },
                                            //             { id: "tripType", header: [{ text: "One way / Round trip", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 120, },
                                            //             { id: "distanceBand", header: [{ text: "Distance Band", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 90, },
                                            //             {
                                            //                 id: "unitRate", header: [{ text: "Unit Rate", css: { "text-align": "center" } }, { content: "numberFilter" }], width: 70, css: { "text-align": "right" }, footer: [{ text: "Total :", css: { "text-align": "right" } }],
                                            //                 format: webix.Number.numToStr({
                                            //                     groupDelimiter: ",",
                                            //                     groupSize: 3,
                                            //                 })
                                            //             },
                                            //             {
                                            //                 id: "total", header: [{ text: "Amount", css: { "text-align": "center" } }, { content: "numberFilter" }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right", } }],
                                            //                 format: webix.Number.numToStr({
                                            //                     groupDelimiter: ",",
                                            //                     groupSize: 3,
                                            //                 })
                                            //             },
                                            //             { id: "pr_no", header: [{ text: "PR No.", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 100, },
                                            //             { id: "invoice_no", header: [{ text: "Invoice No.", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 100, hidden: 1 },
                                            //         ],
                                            // },
                                            // {
                                            //     cols: [
                                            //         {},
                                            //         {
                                            //             view: "pager", id: "page_data_trip_summary_report_partner",
                                            //             animate: true,
                                            //             size: 500,
                                            //             group: 10
                                            //         },
                                            //         {}
                                            //     ]
                                            // }
                                        ]
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