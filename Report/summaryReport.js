var header_summaryReport = function () {
    var menuName = "summaryReport_", fd = "Report/" + menuName + "data.php";

    function init() {
        ele('billing_for').setValue('Customer');
        ele('project_name').setValue('');
        setStarDate();
        setLastDate();
        setIssueDate();
        // ele('start_date').setValue('2024-01-01');
        // ele('stop_date').setValue('2024-01-31');
        // ele('issue_date').setValue('2024-01-31');
        // ele('start_date').setValue('2023-11-01');
        // ele('stop_date').setValue('2023-11-30');
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
        // ele('start_date').setValue('2024-01-01');
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
        //ele('stop_date').setValue('2024-01-31');
    };

    function setIssueDate() {
        const date = new Date();
        const LastDay = getLastDayOfMonth(
            date.getFullYear(),
            date.getMonth(),
        );
        ele('issue_date').setValue(LastDay);
        // ele('issue_date').setValue('2024-01-31');
    };

    function loadManagementFree(id) {
        var obj = ele("form1").getValues()
        ajax(fd, obj, 7, function (json) {
            //console.log(json.data);
            var management_fees_total = 0;
            var Management_Fees = json.data;

            setTable('Management_Fees', Management_Fees);
            for (var j = 0; j < Management_Fees.length; j++) {
                management_fees_total += Management_Fees[j].total;
            }

            var grid = ele('Summary_Service_Charge');
            grid.updateItem(id, { cost: management_fees_total });
            setTable('dataT1', json.data);
        });
    };

    function loadData_summaryReport(btn) {
        var obj = ele("form1").getValues();

        ajax(fd, obj, 1, function (json) {
            ele('find_summary_service').enable();
            var data = json.data;

            var billing_for = ele('billing_for').getValue();
            var project_name = ele('project_name').getValue();

            if (billing_for == 'Customer') {
                if (project_name == 'AAT MR') {

                    var array_nomal = [];
                    var array_blowout = [];
                    var array_addnew = [];
                    var array_extra = [];
                    var array_empty = [];

                    var normal_trip_total = 0;
                    var blowout_trip_total = 0;
                    var addnew_trip_total = 0;
                    var empty_trip_total = 0;
                    var extra_trip_total = 0;
                    var management_fees_total = 0;
                    var total = 0;

                    for (var i = 0; i < data.length; i++) {
                        var work_type = data[i].work_type;
                        if (work_type == 'Normal') {
                            array_nomal.push(data[i]);
                            normal_trip_total += data[i].total;
                        } else if (work_type == 'Blowout') {
                            array_blowout.push(data[i]);
                            blowout_trip_total += data[i].total;
                        } else if (work_type == 'Additional') {
                            array_addnew.push(data[i]);
                            addnew_trip_total += data[i].total;
                        } else if (work_type == 'Empty') {
                            array_empty.push(data[i]);
                            empty_trip_total += data[i].total;
                        }
                        total += data[i].total;
                    }

                    if (total == 0) {
                        window.playsound(2);
                        webix.alert({
                            title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูล Project ' + project_name, callback: function () {
                                ele('rowSummaryReport').hide();
                            }
                        });
                    } else {
                        setTable("normal_trip", array_nomal);
                        setTable("blowout_trip", array_blowout);
                        setTable("addnew_trip", array_addnew);
                        setTable("empty_trip", array_empty);

                        ele('normal_trip').show();
                        ele('blowout_trip').show();
                        ele('addnew_trip').show();
                        ele('empty_trip').show();
                        ele('Management_Fees').show();
                        ele('Summary_Service_Charge').show();

                        ele('normal_trip_label').show();
                        ele('blowout_trip_label').show();
                        ele('addnew_trip_label').show();
                        ele('empty_trip_label').show();
                        ele('Management_Fees_label').show();

                        ele('normal_bailment_trip_label').hide();
                        ele('normal_nonbailment_trip_label').hide();
                        ele('blowout_bailment_trip_label').hide();
                        ele('blowout_nonbailment_trip_label').hide();
                        ele('extra_trip_label').hide();
                        ele('extra_trip').hide();

                        ele('blowout_nonbailment_trip').hide();
                        ele('blowout_nonbailment_trip').hide();
                        ele('normal_bailment_trip').hide();
                        ele('normal_nonbailment_trip').hide();

                        ele('special_trip').hide();
                        ele('special_trip_label').hide();

                        loadManagementFree(5);

                        var Summary_Service_Charge = [
                            { "id": 1, "description": "Normal Trip Delivery", "cost": 0 },
                            { "id": 2, "description": "Extra Trip Delivery (Blow Outs)", "cost": 0 },
                            { "id": 3, "description": "Extra Trip Delivery (Additional)", "cost": 0 },
                            { "id": 4, "description": "Empty Package Return", "cost": 0 },
                            { "id": 5, "description": "Management Fees", "cost": 0 },
                        ];

                        setTable('Summary_Service_Charge', Summary_Service_Charge);

                        var grid = ele('Summary_Service_Charge');
                        grid.updateItem(1, { cost: normal_trip_total });
                        grid.updateItem(2, { cost: blowout_trip_total });
                        grid.updateItem(3, { cost: addnew_trip_total });
                        grid.updateItem(4, { cost: empty_trip_total });
                        //grid.updateItem(5, { cost: management_fees_total });
                    }


                }
                else if (project_name == 'FTM MR' || project_name == 'SKD-FTM') {

                    var array_nomal = [];
                    var array_blowout = [];
                    var array_addnew = [];
                    var array_extra = [];
                    var array_empty = [];

                    var normal_trip_total = 0;
                    var blowout_trip_total = 0;
                    var addnew_trip_total = 0;
                    var empty_trip_total = 0;
                    var extra_trip_total = 0;
                    var management_fees_total = 0;
                    var total = 0;

                    for (var i = 0; i < data.length; i++) {
                        var description_show = data[i].description_show;
                        var split = description_show.split(' ');
                        var work_type = split[0];
                        if (work_type == 'Normal') {
                            array_nomal.push(data[i]);
                        } else if (work_type == 'Extra') {
                            array_extra.push(data[i]);
                        } else if (work_type == 'Empty') {
                            array_empty.push(data[i]);
                        }


                        var trip_type = split[1];
                        if (trip_type == 'One') {
                            normal_trip_total += data[i].total;
                        } else if (trip_type == 'Round') {
                            extra_trip_total += data[i].total;
                        } else {
                            empty_trip_total += data[i].total;
                        }

                        total += data[i].total;
                    }

                    if (total == 0) {
                        window.playsound(2);
                        webix.alert({
                            title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูล Project ' + project_name, callback: function () {
                                ele('rowSummaryReport').hide();
                            }
                        });
                    } else {
                        setTable("normal_trip", array_nomal);
                        setTable("extra_trip", array_extra);
                        setTable("empty_trip", array_empty);

                        ele('normal_trip').show();
                        ele('extra_trip').show();
                        ele('empty_trip').show();
                        ele('Summary_Service_Charge').show();
                        ele('normal_trip_label').show();
                        ele('extra_trip_label').show();
                        ele('empty_trip_label').show();

                        ele('blowout_trip').hide();
                        ele('addnew_trip').hide();
                        ele('Management_Fees').hide();
                        ele('blowout_trip_label').hide();
                        ele('addnew_trip_label').hide();
                        ele('Management_Fees_label').hide();

                        ele('normal_bailment_trip_label').hide();
                        ele('normal_nonbailment_trip_label').hide();
                        ele('blowout_bailment_trip_label').hide();
                        ele('blowout_nonbailment_trip_label').hide();

                        ele('blowout_nonbailment_trip').hide();
                        ele('blowout_nonbailment_trip').hide();
                        ele('normal_bailment_trip').hide();
                        ele('normal_nonbailment_trip').hide();
                        //ele('extra_trip_label').hide();

                        ele('special_trip').hide();
                        ele('special_trip_label').hide();

                        var Summary_Service_Charge = [
                            { "id": 1, "description": "One - Way Trip Delivery", "cost": 0 },
                            { "id": 2, "description": "Round Trip Delivery", "cost": 0 },
                            { "id": 3, "description": "Empty Package Return", "cost": 0 },
                        ];

                        setTable('Summary_Service_Charge', Summary_Service_Charge);

                        var grid = ele('Summary_Service_Charge');
                        grid.updateItem(1, { cost: normal_trip_total });
                        grid.updateItem(2, { cost: extra_trip_total });
                        grid.updateItem(3, { cost: empty_trip_total });
                    }


                } else if (project_name == 'EDC-FTM') {

                    //console.log(data);

                    var array_nomal_bailment = [];
                    var array_nomal_nonbailment = [];
                    var array_blowout_bailment = [];
                    var array_blowout_nonbailment = [];
                    var array_empty = [];
                    var array_empty_count = [];

                    var normal_bailment_total = 0;
                    var normal_nonbailment_total = 0;
                    var blowout_bailment_total = 0;
                    var blowout_nonbailment_total = 0;
                    var empty_trip_total = 0;
                    var management_fees_total = 0;
                    var total = 0;

                    for (var i = 0; i < data.length; i++) {
                        var work_type = data[i].work_type;
                        var bailment = data[i].bailment;
                        if (bailment == 'Bailment' && (work_type == 'Normal' || work_type == 'Additional')) {
                            array_nomal_bailment.push(data[i]);
                            normal_bailment_total += data[i].total;
                        } else if (bailment == 'Non Bailment' && (work_type == 'Normal' || work_type == 'Additional')) {
                            array_nomal_nonbailment.push(data[i]);
                            normal_nonbailment_total += data[i].total;
                        } else if (bailment == 'Bailment' && work_type == 'Blowout') {
                            array_blowout_bailment.push(data[i]);
                            blowout_bailment_total += data[i].total;
                        } else if (bailment == 'Non Bailment' && work_type == 'Blowout') {
                            array_blowout_nonbailment.push(data[i]);
                            blowout_nonbailment_total += data[i].total;
                        } else if (work_type == 'Empty') {
                            array_empty.push(data[i]);
                            empty_trip_total += data[i].total;
                        }
                        total += data[i].total;
                    }

                    console.log(total);

                    if (total == 0) {
                        window.playsound(2);
                        webix.alert({
                            title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูล Project ' + project_name, callback: function () {
                                ele('rowSummaryReport').hide();
                            }
                        });
                    } else {

                        if (normal_bailment_total > 0) {
                            setTable("normal_bailment_trip", array_nomal_bailment);
                            ele('normal_bailment_trip').show();
                            ele('normal_bailment_trip_label').show();
                        }
                        else {
                            ele('normal_bailment_trip').hide();
                            ele('normal_bailment_trip_label').hide();
                        }


                        if (normal_nonbailment_total > 0) {
                            setTable("normal_nonbailment_trip", array_nomal_nonbailment);
                            ele('normal_nonbailment_trip').show();
                            ele('normal_nonbailment_trip_label').show();
                        }
                        else {
                            ele('normal_nonbailment_trip').hide();
                            ele('normal_nonbailment_trip_label').hide();
                        }

                        if (blowout_bailment_total > 0) {
                            setTable("blowout_bailment_trip", array_blowout_bailment);
                            ele('blowout_bailment_trip').show();
                            ele('blowout_bailment_trip_label').show();
                        }
                        else {
                            ele('blowout_bailment_trip').hide();
                            ele('blowout_bailment_trip_label').hide();
                        }

                        if (blowout_nonbailment_total > 0) {
                            setTable("blowout_nonbailment_trip", array_blowout_nonbailment);
                            ele('blowout_nonbailment_trip').show();
                            ele('blowout_nonbailment_trip_label').show();
                        }
                        else {
                            ele('blowout_nonbailment_trip').hide();
                            ele('blowout_nonbailment_trip_label').hide();
                        }
                        if (empty_trip_total > 0) {
                            setTable("empty_trip", array_empty);
                            ele('empty_trip').show();
                            ele('empty_trip').show();
                        }
                        else {
                            ele('empty_trip').hide();
                            ele('empty_trip_label').hide();
                        }


                        //console.log(empty_trip_total);

                        if (empty_trip_total > 0) {
                            setTable("empty_trip", array_empty);
                            ele('empty_trip').show();
                            ele('empty_trip_label').show();
                            var Summary_Service_Charge = [
                                { "id": 1, "description": "EDC Bailment Normal Trip Delivery (Normal and Additional)", "cost": 0 },
                                { "id": 2, "description": "EDC Non Bailment Normal Trip Delivery (Normal and Additional)", "cost": 0 },
                                { "id": 3, "description": "EDC Bailment Extra Trip Delivery (Blow Out)", "cost": 0 },
                                { "id": 4, "description": "EDC Non Bailment Extra Trip Delivery (Blow Out)", "cost": 0 },
                                { "id": 5, "description": "Empty Package Return", "cost": 0 },
                            ];

                            setTable('Summary_Service_Charge', Summary_Service_Charge);

                            var grid = ele('Summary_Service_Charge');
                            grid.updateItem(1, { cost: normal_bailment_total });
                            grid.updateItem(2, { cost: normal_nonbailment_total });
                            grid.updateItem(3, { cost: blowout_bailment_total });
                            grid.updateItem(4, { cost: blowout_nonbailment_total });
                            grid.updateItem(5, { cost: empty_trip_total });
                        }
                        else {
                            ele('empty_trip').hide();
                            ele('empty_trip_label').hide();
                            var Summary_Service_Charge = [
                                { "id": 1, "description": "EDC Bailment Normal Trip Delivery (Normal and Additional)", "cost": 0 },
                                { "id": 2, "description": "EDC Non Bailment Normal Trip Delivery (Normal and Additional)", "cost": 0 },
                                { "id": 3, "description": "EDC Bailment Extra Trip Delivery (Blow Out)", "cost": 0 },
                                { "id": 4, "description": "EDC Non Bailment Extra Trip Delivery (Blow Out)", "cost": 0 },
                            ];

                            setTable('Summary_Service_Charge', Summary_Service_Charge);

                            var grid = ele('Summary_Service_Charge');
                            grid.updateItem(1, { cost: normal_bailment_total });
                            grid.updateItem(2, { cost: normal_nonbailment_total });
                            grid.updateItem(3, { cost: blowout_bailment_total });
                            grid.updateItem(4, { cost: blowout_nonbailment_total });
                        }

                        ele('Summary_Service_Charge').show();

                        ele('Management_Fees').hide();
                        ele('Management_Fees_label').hide();

                        ele('normal_trip').hide();
                        ele('blowout_trip').hide();
                        ele('addnew_trip').hide();
                        ele('extra_trip').hide();
                        ele('normal_trip_label').hide();
                        ele('blowout_trip_label').hide();
                        ele('addnew_trip_label').hide();
                        ele('extra_trip_label').hide();

                        ele('special_trip').hide();
                        ele('special_trip_label').hide();
                    }

                } else if (project_name == 'AAT EDC') {

                    var array_nomal_bailment = [];
                    var array_nomal_nonbailment = [];
                    var array_blowout_bailment = [];
                    var array_blowout_nonbailment = [];
                    var array_empty = [];

                    var normal_bailment_total = 0;
                    var normal_nonbailment_total = 0;
                    var blowout_bailment_total = 0;
                    var blowout_nonbailment_total = 0;
                    var empty_trip_total = 0;
                    var management_fees_total = 0;
                    var total = 0;

                    for (var i = 0; i < data.length; i++) {
                        var work_type = data[i].work_type;
                        var bailment = data[i].bailment;
                        if (bailment == 'Bailment' && (work_type == 'Normal' || work_type == 'Additional')) {
                            array_nomal_bailment.push(data[i]);
                            normal_bailment_total += data[i].total;
                        } else if (bailment == 'Non Bailment' && (work_type == 'Normal' || work_type == 'Additional')) {
                            array_nomal_nonbailment.push(data[i]);
                            normal_nonbailment_total += data[i].total;
                        } else if (bailment == 'Bailment' && work_type == 'Blowout') {
                            array_blowout_bailment.push(data[i]);
                            blowout_bailment_total += data[i].total;
                        } else if (bailment == 'Non Bailment' && work_type == 'Blowout') {
                            array_blowout_nonbailment.push(data[i]);
                            blowout_nonbailment_total += data[i].total;
                        } else if (work_type == 'Empty') {
                            array_empty.push(data[i]);
                            empty_trip_total += data[i].total;
                        }
                        total += data[i].total;
                    }

                    if (total == 0) {
                        window.playsound(2);
                        webix.alert({
                            title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูล Project ' + project_name, callback: function () {
                                ele('rowSummaryReport').hide();
                            }
                        });
                    } else {

                        ele('normal_trip').hide();
                        ele('blowout_trip').hide();
                        ele('addnew_trip').hide();
                        ele('extra_trip').hide();
                        ele('normal_trip_label').hide();
                        ele('blowout_trip_label').hide();
                        ele('addnew_trip_label').hide();
                        ele('extra_trip_label').hide();

                        ele('special_trip').hide();
                        ele('special_trip_label').hide();

                        ele('Management_Fees').show();
                        ele('Management_Fees_label').show();
                        ele('Summary_Service_Charge').show();


                        arr = [];

                        if (normal_bailment_total > 0) {
                            setTable("normal_bailment_trip", array_nomal_bailment);
                            ele('normal_bailment_trip').show();
                            ele('normal_bailment_trip_label').show();
                            arr.push(normal_bailment_total);
                        }
                        else {
                            ele('normal_bailment_trip').hide();
                            ele('normal_bailment_trip_label').hide();
                        }


                        if (normal_nonbailment_total > 0) {
                            setTable("normal_nonbailment_trip", array_nomal_nonbailment);
                            ele('normal_nonbailment_trip').show();
                            ele('normal_nonbailment_trip_label').show();
                            arr.push(normal_nonbailment_total);
                        }
                        else {
                            ele('normal_nonbailment_trip').hide();
                            ele('normal_nonbailment_trip_label').hide();
                        }

                        if (blowout_bailment_total > 0) {
                            setTable("blowout_bailment_trip", array_blowout_bailment);
                            ele('blowout_bailment_trip').show();
                            ele('blowout_bailment_trip_label').show();
                            arr.push(blowout_bailment_total);
                        }
                        else {
                            ele('blowout_bailment_trip').hide();
                            ele('blowout_bailment_trip_label').hide();
                        }

                        if (blowout_nonbailment_total > 0) {
                            setTable("blowout_nonbailment_trip", array_blowout_nonbailment);
                            ele('blowout_nonbailment_trip').show();
                            ele('blowout_nonbailment_trip_label').show();
                            arr.push(blowout_nonbailment_total);
                        }
                        else {
                            ele('blowout_nonbailment_trip').hide();
                            ele('blowout_nonbailment_trip_label').hide();
                        }

                        if (empty_trip_total > 0) {
                            setTable("empty_trip", array_empty);
                            ele('empty_trip').show();
                            ele('empty_trip').show();
                            arr.push(empty_trip_total);
                        }
                        else {
                            ele('empty_trip').hide();
                            ele('empty_trip_label').hide();
                        }

                        var Summary_Service_Charge = [
                            { "id": 1, "description": "EDC Bailment Normal Trip Delivery (Normal and Additional)", "cost": 0 },
                            { "id": 2, "description": "EDC Non Bailment Normal Trip Delivery (Normal and Additional)", "cost": 0 },
                            { "id": 3, "description": "EDC Bailment Extra Trip Delivery (Blow Out)", "cost": 0 },
                            { "id": 4, "description": "EDC Non Bailment Extra Trip Delivery (Blow Out)", "cost": 0 },
                            { "id": 5, "description": "EDC Bailment Empty Package Return Service", "cost": 0 },
                            { "id": 6, "description": "Management Fees", "cost": 0 },
                        ];

                        if (normal_bailment_total == 0) {
                            Summary_Service_Charge.splice(0, 1);
                        }
                        if (normal_nonbailment_total == 0) {
                            Summary_Service_Charge.splice(1, 1);
                        }
                        if (blowout_bailment_total == 0) {
                            Summary_Service_Charge.splice(2, 1);
                        }
                        if (blowout_nonbailment_total == 0) {
                            Summary_Service_Charge.splice(3, 1);
                        }
                        if (empty_trip_total == 0) {
                            Summary_Service_Charge.splice(4, 1);
                        }

                        loadManagementFree(6);

                        setTable('Summary_Service_Charge', Summary_Service_Charge);

                        var grid = ele('Summary_Service_Charge');


                        i = 0;
                        while (i < Summary_Service_Charge.length - 1) {
                            grid.updateItem(Summary_Service_Charge[i].id, { cost: arr[i] });
                            i++;
                        }

                    }
                }
            } else if (billing_for == 'Partner') {
                if (project_name == 'AAT EDC') {

                    var array_nomal_bailment = [];
                    var array_nomal_nonbailment = [];
                    var array_blowout_bailment = [];
                    var array_blowout_nonbailment = [];
                    var array_empty = [];

                    var normal_bailment_total = 0;
                    var normal_nonbailment_total = 0;
                    var blowout_bailment_total = 0;
                    var blowout_nonbailment_total = 0;
                    var empty_trip_total = 0;
                    var management_fees_total = 0;
                    var total = 0;

                    for (var i = 0; i < data.length; i++) {
                        var work_type = data[i].work_type;
                        var bailment = data[i].bailment;
                        if (bailment == 'Bailment' && (work_type == 'Normal' || work_type == 'Additional')) {
                            array_nomal_bailment.push(data[i]);
                            normal_bailment_total += data[i].total;
                        } else if (bailment == 'Non Bailment' && (work_type == 'Normal' || work_type == 'Additional')) {
                            array_nomal_nonbailment.push(data[i]);
                            normal_nonbailment_total += data[i].total;
                        } else if (bailment == 'Bailment' && work_type == 'Blowout') {
                            array_blowout_bailment.push(data[i]);
                            blowout_bailment_total += data[i].total;
                        } else if (bailment == 'Non Bailment' && work_type == 'Blowout') {
                            array_blowout_nonbailment.push(data[i]);
                            blowout_nonbailment_total += data[i].total;
                        } else if (work_type == 'Empty') {
                            array_empty.push(data[i]);
                            empty_trip_total += data[i].total;
                        }
                        total += data[i].total;
                    }

                    if (total == 0) {
                        window.playsound(2);
                        webix.alert({
                            title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูล Project ' + project_name, callback: function () {
                                ele('rowSummaryReport').hide();
                            }
                        });
                    } else {

                        ele('normal_trip').hide();
                        ele('blowout_trip').hide();
                        ele('addnew_trip').hide();
                        ele('extra_trip').hide();
                        ele('normal_trip_label').hide();
                        ele('blowout_trip_label').hide();
                        ele('addnew_trip_label').hide();
                        ele('extra_trip_label').hide();

                        ele('special_trip').hide();
                        ele('special_trip_label').hide();

                        ele('Management_Fees').show();
                        ele('Management_Fees_label').show();
                        ele('Summary_Service_Charge').show();


                        arr = [];

                        if (normal_bailment_total > 0) {
                            setTable("normal_bailment_trip", array_nomal_bailment);
                            ele('normal_bailment_trip').show();
                            ele('normal_bailment_trip_label').show();
                            arr.push(normal_bailment_total);
                        }
                        else {
                            ele('normal_bailment_trip').hide();
                            ele('normal_bailment_trip_label').hide();
                        }


                        if (normal_nonbailment_total > 0) {
                            setTable("normal_nonbailment_trip", array_nomal_nonbailment);
                            ele('normal_nonbailment_trip').show();
                            ele('normal_nonbailment_trip_label').show();
                            arr.push(normal_nonbailment_total);
                        }
                        else {
                            ele('normal_nonbailment_trip').hide();
                            ele('normal_nonbailment_trip_label').hide();
                        }

                        if (blowout_bailment_total > 0) {
                            setTable("blowout_bailment_trip", array_blowout_bailment);
                            ele('blowout_bailment_trip').show();
                            ele('blowout_bailment_trip_label').show();
                            arr.push(blowout_bailment_total);
                        }
                        else {
                            ele('blowout_bailment_trip').hide();
                            ele('blowout_bailment_trip_label').hide();
                        }

                        if (blowout_nonbailment_total > 0) {
                            setTable("blowout_nonbailment_trip", array_blowout_nonbailment);
                            ele('blowout_nonbailment_trip').show();
                            ele('blowout_nonbailment_trip_label').show();
                            arr.push(blowout_nonbailment_total);
                        }
                        else {
                            ele('blowout_nonbailment_trip').hide();
                            ele('blowout_nonbailment_trip_label').hide();
                        }

                        if (empty_trip_total > 0) {
                            setTable("empty_trip", array_empty);
                            ele('empty_trip').show();
                            ele('empty_trip').show();
                            arr.push(empty_trip_total);
                        }
                        else {
                            ele('empty_trip').hide();
                            ele('empty_trip_label').hide();
                        }

                        var Summary_Service_Charge = [
                            { "id": 1, "description": "EDC Bailment Normal Trip Delivery (Normal and Additional)", "cost": 0 },
                            { "id": 2, "description": "EDC Non Bailment Normal Trip Delivery (Normal and Additional)", "cost": 0 },
                            { "id": 3, "description": "EDC Bailment Extra Trip Delivery (Blow Out)", "cost": 0 },
                            { "id": 4, "description": "EDC Non Bailment Extra Trip Delivery (Blow Out)", "cost": 0 },
                            { "id": 5, "description": "EDC Bailment Empty Package Return Service", "cost": 0 },
                            { "id": 6, "description": "Management Fees", "cost": 0 },
                        ];

                        if (normal_bailment_total == 0) {
                            Summary_Service_Charge.splice(0, 1);
                        }
                        if (normal_nonbailment_total == 0) {
                            Summary_Service_Charge.splice(1, 1);
                        }
                        if (blowout_bailment_total == 0) {
                            Summary_Service_Charge.splice(2, 1);
                        }
                        if (blowout_nonbailment_total == 0) {
                            Summary_Service_Charge.splice(3, 1);
                        }
                        if (empty_trip_total == 0) {
                            Summary_Service_Charge.splice(4, 1);
                        }

                        loadManagementFree(6);

                        setTable('Summary_Service_Charge', Summary_Service_Charge);

                        var grid = ele('Summary_Service_Charge');


                        i = 0;
                        while (i < Summary_Service_Charge.length - 1) {
                            grid.updateItem(Summary_Service_Charge[i].id, { cost: arr[i] });
                            i++;
                        }

                    }
                } else {

                    var array_nomal = [];
                    var array_blowout = [];
                    var array_addnew = [];
                    var array_extra = [];
                    var array_empty = [];
                    var array_special = [];

                    var normal_trip_total = 0;
                    var blowout_trip_total = 0;
                    var addnew_trip_total = 0;
                    var empty_trip_total = 0;
                    var extra_trip_total = 0;
                    var special_trip_total = 0;
                    var management_fees_total = 0;
                    var total = 0;

                    for (var i = 0; i < data.length; i++) {
                        var description_show = data[i].description;
                        var split = description_show.split(' ');
                        var work_type = split[0];
                        if (work_type == 'Normal') {
                            array_nomal.push(data[i]);
                            normal_trip_total += data[i].total;
                        }

                        else if (work_type == 'Blowout') {
                            array_blowout.push(data[i]);
                            blowout_trip_total += data[i].total;
                        } else if (work_type == 'Additional') {
                            array_addnew.push(data[i]);
                            addnew_trip_total += data[i].total;
                        }

                        else if (work_type == 'Empty') {
                            array_empty.push(data[i]);
                            empty_trip_total += data[i].total;
                        } else if (work_type == 'Special' && data[i].total > 0) {
                            array_special.push(data[i]);
                            special_trip_total += data[i].total;
                        }
                        total += data[i].total;
                    }

                    if (total == 0) {
                        window.playsound(2);
                        webix.alert({
                            title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูล Project ' + project_name, callback: function () {
                                ele('rowSummaryReport').hide();
                            }
                        });
                    } else {

                        //console.log(array_blowout);
                        //console.log(array_special.length);

                        setTable("normal_trip", array_nomal);
                        setTable("empty_trip", array_empty);
                        setTable("blowout_trip", array_blowout);
                        setTable("addnew_trip", array_addnew);

                        if (array_special.length > 0) {
                            setTable("special_trip", array_special);
                            ele('special_trip').show();
                            ele('special_trip_label').show();


                            var Summary_Service_Charge = [
                                { "id": 1, "description": "Normal Trip Delivery", "cost": 0 },
                                { "id": 2, "description": "Extra Trip Delivery (Blow Outs)", "cost": 0 },
                                { "id": 3, "description": "Extra Trip Delivery (Additional)", "cost": 0 },
                                { "id": 4, "description": "Empty Package Return", "cost": 0 },
                                { "id": 5, "description": "Special", "cost": 0 },
                            ];

                            setTable('Summary_Service_Charge', Summary_Service_Charge);

                            var grid = ele('Summary_Service_Charge');
                            grid.updateItem(1, { cost: normal_trip_total });
                            grid.updateItem(2, { cost: blowout_trip_total });
                            grid.updateItem(3, { cost: addnew_trip_total });
                            grid.updateItem(4, { cost: empty_trip_total });
                            grid.updateItem(5, { cost: special_trip_total });
                        } else {
                            ele('special_trip').hide();
                            ele('special_trip_label').hide();
                            ele('special_trip').clearAll();


                            var Summary_Service_Charge = [
                                { "id": 1, "description": "Normal Trip Delivery", "cost": 0 },
                                { "id": 2, "description": "Extra Trip Delivery (Blow Outs)", "cost": 0 },
                                { "id": 3, "description": "Extra Trip Delivery (Additional)", "cost": 0 },
                                { "id": 4, "description": "Empty Package Return", "cost": 0 },
                            ];

                            setTable('Summary_Service_Charge', Summary_Service_Charge);

                            var grid = ele('Summary_Service_Charge');
                            grid.updateItem(1, { cost: normal_trip_total });
                            grid.updateItem(2, { cost: blowout_trip_total });
                            grid.updateItem(3, { cost: addnew_trip_total });
                            grid.updateItem(4, { cost: empty_trip_total });
                        }

                        ele('normal_trip').show();
                        ele('addnew_trip').show();
                        ele('blowout_trip').show();
                        ele('empty_trip').show();
                        ele('Summary_Service_Charge').show();

                        ele('normal_trip_label').show();
                        ele('empty_trip_label').show();
                        ele('blowout_trip_label').show();
                        ele('addnew_trip_label').show();


                        ele('extra_trip').hide();
                        ele('Management_Fees').hide();
                        ele('Management_Fees_label').hide();
                        ele('extra_trip_label').hide();


                        ele('normal_bailment_trip_label').hide();
                        ele('normal_nonbailment_trip_label').hide();
                        ele('blowout_bailment_trip_label').hide();
                        ele('blowout_nonbailment_trip_label').hide();

                        ele('blowout_nonbailment_trip').hide();
                        ele('blowout_nonbailment_trip').hide();
                        ele('normal_bailment_trip').hide();
                        ele('normal_nonbailment_trip').hide();
                    }
                }
            }
        }

            , null, function (json) {
                window.playsound(2);
                ele('find_summary_service').enable();
                ele('rowSummaryReport').hide();
            });
    };


    function loadDataPR(type) {
        var obj1 = ele("form1").getValues();
        var obj2 = ele("form2").getValues();
        var obj = { ...obj1, ...obj2 };

        $.post(fd, { obj: obj, type: type })
            .done(function (data) {
                var json = JSON.parse(data);
                data = eval('(' + data + ')');
                if (json.ch == 1) {
                    setTable('data_send_pr', json.data);
                    ele('btn_create_pr').enable();
                }
                else {
                    webix.alert({
                        title: "<b>ข้อความจากระบบ</b>", type: "alert-error", ok: 'ตกลง', text: json.data, callback: function () {
                            ele('btn_create_pr').disable();
                            ele('data_send_pr').clearAll();
                            window.playsound(2);
                        }
                    });
                }
            })
    };

    function loadDataPRNo() {
        var obj1 = ele("form1").getValues();
        var obj2 = ele("form2").getValues();
        var obj = { ...obj1, ...obj2 };

        $.post(fd, { obj: obj, type: 4 })
            .done(function (data) {
                var json = JSON.parse(data);
                var data1 = json.data;
                if (json.ch == 1) {
                    setTable('data_send_pr', data1.body);
                    ele('remarks').setValue(data1.header[0].remarks);
                    ele('issue_date').setValue(data1.header[0].issue_date);
                    ele('btn_cancel_pr').enable();
                    ele('btn_update_pr').enable();
                    ele('btn_create_pr').disable();
                    ele('project_name').disable();
                    ele('carrier').disable();
                    ele('find_item_pr').disable();
                }
                else {
                    webix.alert({
                        title: "<b>ข้อความจากระบบ</b>", type: "alert-error", ok: 'ตกลง', text: json.data, callback: function () {
                            window.playsound(2);
                            ele('data_send_pr').clearAll();
                            ele('pr_no').setValue('');
                            ele('remarks').setValue('');
                            ele('btn_cancel_pr').disable();
                            ele('btn_update_pr').disable();
                            ele('btn_create_pr').disable();
                            setIssueDate();
                            setStarDate();
                            setLastDate();
                            ele('project_name').enable();
                            ele('carrier').enable();
                            ele('find_item_pr').enable();
                        }
                    });
                }
            });
    };

    function loadDataTripSummaryReport(type) {
        var obj1 = ele("form1").getValues();
        var obj2 = ele("form2").getValues();
        var obj = { ...obj1, ...obj2 };
        ajax(fd, obj, type, function (json) {
            setTable('data_trip_summary_report', json.data);
        }, null,
            function (json) {
                ele('data_trip_summary_report').clearAll();
                window.playsound(2);
            },);
    };

    function reload_options(start_date, stop_date, project) {
        var list = ele("carrier").getPopup().getList();
        list.clearAll();
        list.load("common/truckCarrier.php?type=1&&start_date=" + start_date + "&stop_date=" + stop_date + "&project=" + project);
    };

    function reload_options2(start_date, stop_date, project) {
        var list = ele("carrier").getPopup().getList();
        list.clearAll();
        list.load("common/truckCarrier.php?type=2&&start_date=" + start_date + "&stop_date=" + stop_date + "&project=" + project);
    };


    return {
        view: "scrollview",
        scroll: "native-y",
        id: "header_summaryReport",
        body:
        {
            id: "summaryReport_id",
            type: "line",
            rows:
                [
                    {
                        view: "form", id: $n("form1"),
                        rows: [
                            {
                                cols: [
                                    { width: 20 },
                                    vw1("datepicker", 'start_date', "Start Date", { value: dayjs().format("YYYY-MM-DD"), stringResult: true, ...datatableDateFormat, }),
                                    vw1("datepicker", 'stop_date', "Stop Date", { value: dayjs().format("YYYY-MM-DD"), stringResult: true, ...datatableDateFormat, }),
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
                                                    ele('data_send_pr').hideColumn("project");
                                                    ele('carrier').setValue('');
                                                    ele('carrier').hide();
                                                    ele('invoice_no').enable();
                                                    ele('pr_no').disable();
                                                    ele('btn_create_pr').disable();
                                                    ele('issue_date').disable();
                                                    ele('remarks').disable();
                                                    ele('find_item_pr').disable();
                                                    ele('pr_no').setValue('');
                                                    ele("remarks").setValue('');
                                                    secondCombo.define("options", ["AAT MR", "FTM MR", "SKD-FTM", "EDC-FTM", "AAT EDC"
                                                    ]);
                                                }
                                                else if (newVal === "Partner") {
                                                    ele('data_send_pr').showColumn("project");
                                                    ele('carrier').show();
                                                    ele('project_name').enable();
                                                    ele('carrier').enable();
                                                    ele("project_name").setValue('');
                                                    ele('invoice_no').disable();
                                                    ele('pr_no').enable();
                                                    ele('issue_date').enable();
                                                    ele('remarks').enable();
                                                    ele('find_item_pr').enable();
                                                    ele('invoice_no').setValue('');
                                                    secondCombo.define("options", ["AAT MR", "FTM MR", "SKD-FTM", "EDC-FTM", "AAT EDC"
                                                    ]);
                                                }
                                                secondCombo.refresh();
                                            },
                                        },
                                    },
                                    {
                                        view: "combo", label: "Project", labelPosition: "top", name: "project_name", id: $n("project_name"), required: true, options: ["ALL"],
                                        on: {
                                            onChange: function (newVal) {

                                                if (newVal != "") {
                                                    ele("export_hourly_template").enable();
                                                }
                                                else {
                                                    ele("export_hourly_template").disable();
                                                }

                                                var secondCombo = ele("carrier");

                                                var remarks = ele("remarks");
                                                if (ele("billing_for").getValue() == 'Partner' && newVal != '') {
                                                    remarks.setValue(newVal + ', ');
                                                }

                                                ele("carrier").setValue('');
                                                var start_date = ele('start_date').getValue();
                                                var stop_date = ele('stop_date').getValue();
                                                var project = ele('project_name').getValue();
                                                reload_options(start_date, stop_date, project);
                                                //secondCombo.define("options", ["AAT MR", "FTM MR", "AAT EDC"]);

                                                // if (newVal === "ALL AAT-FTM MR") {
                                                //     ele('find_summary_service').disable();
                                                //     ele('export_summary_excel').disable();
                                                // } else {
                                                //     ele('find_summary_service').enable();
                                                //     ele('export_summary_excel').enable();
                                                // }
                                                secondCombo.refresh();
                                            },
                                        },
                                    },
                                    vw1("combo", 'carrier', "Carrier", {
                                        required: true, hidden: 1, options: ["ALL"],
                                        on: {
                                            onChange: function (newVal) {
                                                var remarks = ele("remarks");
                                                var project_name = ele("project_name").getValue();
                                                //console.log(newVal);
                                                remarks.setValue(project_name + ', ' + newVal);
                                                ele('pr_no').setValue('');
                                                //remarks.refresh();

                                            },
                                        },
                                    }),
                                    { width: 20 },
                                ]
                            },
                            {
                                cols: [
                                    {},
                                    {
                                        view: "button", id: $n('export_jda'), label: "Export JDA (Excel)", width: 120, hidden: 0,
                                        on:
                                        {
                                            onItemClick: async (id, e) => {
                                                var obj = ele("form1").getValues();
                                                webix.extend($$("summaryReport_id"), webix.ProgressBar);
                                                ele("export_jda").disable();
                                                $$("summaryReport_id").showProgress({
                                                    type: "top",
                                                    delay: 100000,
                                                    hide: true
                                                });

                                                setTimeout(function () {
                                                    $.post(fd, { obj: obj, type: 56 })
                                                        .done(function (data) {
                                                            var json = JSON.parse(data);
                                                            data = eval('(' + data + ')');
                                                            if (json.ch == 1) {
                                                                webix.alert({
                                                                    title: "<b>ข้อความจากระบบ</b>", type: "alert-complete", ok: 'ตกลง', text: 'Export Complete', callback: function () {
                                                                        ele("export_jda").enable();
                                                                        $$("summaryReport_id").hideProgress();
                                                                        window.location.href = 'Report/' + json.data;
                                                                    }
                                                                });
                                                            }
                                                            else {
                                                                webix.alert({
                                                                    title: "<b>ข้อความจากระบบ</b>", type: "alert-error", ok: 'ตกลง', text: json.data, callback: function () {
                                                                        ele("export_jda").enable();
                                                                        $$("summaryReport_id").hideProgress();
                                                                        window.playsound(2);
                                                                    }
                                                                });
                                                            }
                                                        })
                                                }, 0);

                                            }
                                        }
                                    },
                                    {
                                        view: "button", id: $n('export_hourly_template'), label: "Template Upload", width: 120, disabled: 1,
                                        on:
                                        {
                                            onItemClick: async (id, e) => {
                                                var project = ele('project_name').getValue();

                                                if (project == 'AAT EDC' || project == 'EDC-FTM') {
                                                    window.location.href = 'Report/template_upload/template_upload_hourly_report_edc.xlsx';
                                                } else {
                                                    window.location.href = 'Report/template_upload/template_upload_hourly_report.xlsx';
                                                }

                                            }
                                        }
                                    },

                                    vw1("uploader", 'upload_hourly_btn', "Upload Hourly Report", {
                                        width: 150, hidden: false, multiple: false, autosend: false, on:
                                        {
                                            onBeforeFileAdd: function (file) {
                                                var type = file.type.toLowerCase();
                                                if (type == "csv" || type == "xlsx" || type == "xls") {

                                                }
                                                else {
                                                    webix.alert({ title: "<b>ข้อความจากระบบ</b>", text: "รองรับ CSV ,XLS ,XLSX เท่านั้น", type: 'alert-error' });
                                                    return false;
                                                }
                                                ele("upload_hourly_btn").disable();
                                            },
                                            onAfterFileAdd: function (item) {
                                                var formData = new FormData();
                                                var project_name = ele('project_name').getValue();
                                                this.files.data.each(function (obj, i) {
                                                    formData.append("upload", obj.file);
                                                    formData.append("project_name", project_name);
                                                });


                                                webix.extend($$("summaryReport_id"), webix.ProgressBar);
                                                ele("upload_hourly_btn").disable();
                                                $$("summaryReport_id").showProgress({
                                                    type: "top",
                                                    delay: 300000,
                                                    hide: true
                                                });
                                                setTimeout(function () {
                                                    $.ajax({
                                                        type: 'POST',
                                                        cache: false,
                                                        contentType: false,
                                                        processData: false,
                                                        url: fd + '?type=51',
                                                        data: formData,
                                                        success: function (data) {
                                                            var json = JSON.parse(data);
                                                            webix.alert({
                                                                title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: json.mms, callback: function () {
                                                                    ele("upload_hourly_btn").enable();
                                                                    $$("summaryReport_id").hideProgress();
                                                                }
                                                            });
                                                        }
                                                    });

                                                }, 0);
                                            },
                                        },
                                    }),
                                    {
                                        view: "button", id: $n('find_summary_service'), label: "Find (Summary Service Charge)", width: 180,
                                        on:
                                        {
                                            onItemClick: async (id, e) => {
                                                ele('rowSummaryReport').show();
                                                ele('rowDataPR').hide();
                                                ele('rowTripSummayReport').hide();
                                                loadData_summaryReport();
                                            }
                                        }
                                    },
                                    {
                                        view: "button", id: $n('export_summary_excel'), label: "Export Summary Report (Excel)", width: 180,
                                        on:
                                        {
                                            onItemClick: async (id, e) => {
                                                var obj1 = ele("form1").getValues();
                                                var obj2 = ele("form2").getValues();
                                                var obj = { ...obj1, ...obj2 };
                                                webix.extend($$("summaryReport_id"), webix.ProgressBar);
                                                ele("export_summary_excel").disable();
                                                $$("summaryReport_id").showProgress({
                                                    type: "top",
                                                    delay: 100000,
                                                    hide: true
                                                });

                                                setTimeout(function () {
                                                    $.post(fd, { obj: obj, type: 54 })
                                                        .done(function (data) {
                                                            var json = JSON.parse(data);
                                                            data = eval('(' + data + ')');
                                                            if (json.ch == 1) {
                                                                webix.alert({
                                                                    title: "<b>ข้อความจากระบบ</b>", type: "alert-complete", ok: 'ตกลง', text: 'Export Complete', callback: function () {
                                                                        ele("export_summary_excel").enable();
                                                                        $$("summaryReport_id").hideProgress();
                                                                        window.location.href = 'Report/' + json.data;
                                                                    }
                                                                });
                                                            }
                                                            else {
                                                                webix.alert({
                                                                    title: "<b>ข้อความจากระบบ</b>", type: "alert-error", ok: 'ตกลง', text: json.data, callback: function () {
                                                                        ele("export_summary_excel").enable();
                                                                        $$("summaryReport_id").hideProgress();
                                                                        window.playsound(2);
                                                                    }
                                                                });
                                                            }
                                                        })
                                                }, 0);

                                            }
                                        }
                                    },
                                    // {
                                    //     view: "button", id: $n('find_trip_summary_report'), label: "Find (Trip Summary Report)", width: 50,
                                    //     on:
                                    //     {
                                    //         onItemClick: async (id, e) => {
                                    //             ele('rowSummaryReport').hide();
                                    //             ele('rowDataPR').hide();
                                    //             ele('rowTripSummayReport').show();
                                    //             var type = 6;
                                    //             loadDataTripSummaryReport(type);
                                    //             // ele('data_trip_summary_report').hideColumn("pr_no");
                                    //             // ele('data_trip_summary_report').hideColumn("invoice_no");
                                    //         }
                                    //     }
                                    // },
                                    {
                                        view: "button", id: $n('find_item_pr'), label: "Find Items Transport (PR)", width: 180,
                                        on:
                                        {
                                            onItemClick: function (id, e) {

                                                var carrier = ele('carrier').getValue();
                                                if (carrier == 'ALL Carrier') {
                                                    ele('btn_create_pr').disable();
                                                }
                                                else {
                                                    ele('btn_create_pr').enable();
                                                }
                                                ele('btnclear_datapr').enable();

                                                ele('rowSummaryReport').hide();
                                                ele('rowDataPR').show();
                                                ele('rowTripSummayReport').hide();
                                                var type = 2;
                                                loadDataPR(type);
                                            }
                                        }
                                    },
                                    {
                                        view: "button", id: $n('btn_clear_all'), label: "Clear Data", width: 80,
                                        on:
                                        {
                                            onItemClick: async (id, e) => {
                                                setStarDate();
                                                setLastDate();
                                                setIssueDate();
                                                ele('billing_for').setValue('Customer');
                                                ele('project_name').setValue('');
                                                ele('invoice_no').setValue('');
                                                ele('pr_no').setValue('');
                                                ele('remarks').setValue('');
                                                // ele('carrier').hide();

                                                ele('carrier').hide();
                                                ele('rowSummaryReport').hide();
                                                ele('rowDataPR').hide();
                                                ele('rowTripSummayReport').hide();

                                                ele('pr_no').disable();
                                                ele('issue_date').disable();
                                                ele('remarks').disable();
                                                ele('carrier').disable();
                                                ele('find_item_pr').disable();

                                                ele('project_name').enable();

                                                ele('data_send_pr').clearAll();
                                                ele('data_trip_summary_report').clearAll();
                                            }
                                        }
                                    },
                                    {}
                                ]
                            }
                        ]
                    },
                    {
                        view: "fieldset",
                        label: "PR Control",
                        body: {
                            rows: [
                                {
                                    view: "form", id: $n("form2"), hidden: false,
                                    rules: {
                                        "invoice_no": webix.rules.isNotEmpty,
                                        "pr_no": webix.rules.isNotEmpty
                                    },

                                    on:
                                    {
                                        "onSubmit": function (view, e) {

                                            if (view.config.name == 'pr_no') {
                                                ele('rowSummaryReport').hide();
                                                ele('rowDataPR').show();
                                                ele('rowTripSummayReport').hide();
                                                ele('project_name').setValue('');
                                                ele('carrier').setValue('');
                                                loadDataPRNo();
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
                                                vw1('text', 'invoice_no', 'Invoice No.', { labelPosition: "top", required: false, width: 170, disabled: false, hidden: 1, }),
                                                vw1('text', 'pr_no', 'PR No.', {
                                                    labelPosition: "top", required: false, width: 170, disabled: false,
                                                }),
                                                vw1("datepicker", 'issue_date', "Issue Date", { value: dayjs().format("YYYY-MM-DD"), stringResult: true, ...datatableDateFormat, width: 170 }),
                                                vw1('text', 'remarks', 'Remarks', { labelPosition: "top", required: false, }),
                                            ]
                                        },
                                        {
                                            cols: [
                                                {},
                                                {
                                                    view: "button", id: $n('btn_create_pr'), label: "Create PR", width: 100,
                                                    on:
                                                    {
                                                        onItemClick: function (id, e) {
                                                            var pr = ele("pr_no").getValue();
                                                            var formValues = ele("form1").getValues();
                                                            var obj2 = ele('form2').getValues();
                                                            var obj = { ...formValues, ...obj2 };
                                                            ele('btn_create_pr').disable();
                                                            var data_send_pr = ele("data_send_pr");
                                                            if (data_send_pr.count() != 0) {
                                                                webix.confirm(
                                                                    {
                                                                        title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการส่งข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                                        callback: function (res) {
                                                                            if (res) {
                                                                                $.post(fd, { obj: obj, type: 11 })
                                                                                    .done(function (data) {
                                                                                        var json = JSON.parse(data);
                                                                                        var data1 = json.data;
                                                                                        ele("data_send_pr").clearAll();
                                                                                        ele("remarks").setValue('');
                                                                                        if (json.ch == 1) {
                                                                                            if (data1.code == 200) {
                                                                                                webix.alert({
                                                                                                    title: "<b>ข้อความจากระบบ</b>", type: "alert-complete", ok: 'ตกลง', text: data1.message, callback: function () {
                                                                                                        window.open(data1.route, '_blank');
                                                                                                    }
                                                                                                });
                                                                                            } else if (data1.code == 400) {
                                                                                                webix.alert({ title: "<b>ข้อความจากระบบ</b>", type: "alert-error", ok: 'ตกลง', text: data1.message, callback: function () { } });
                                                                                            }
                                                                                        }
                                                                                        else {
                                                                                            webix.alert({
                                                                                                title: "<b>ข้อความจากระบบ</b>", type: "alert-error", ok: 'ตกลง', text: json.data, callback: function () {
                                                                                                    window.playsound(2);
                                                                                                }
                                                                                            });
                                                                                        }
                                                                                    })

                                                                            }
                                                                        }
                                                                    });
                                                            }
                                                            else {
                                                                webix.alert({
                                                                    title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูลในตาราง', callback: function () {
                                                                        window.playsound(2);
                                                                        ele('btn_create_pr').enable();
                                                                    }
                                                                });
                                                            }
                                                        }
                                                    }
                                                }, {
                                                    view: "button", id: $n('btn_update_pr'), label: "Update PR", width: 100, disabled: true,
                                                    on:
                                                    {
                                                        onItemClick: function (id, e) {
                                                            var pr = ele("pr_no").getValue();
                                                            //console.log(pr);
                                                            var formValues = ele("form1").getValues();
                                                            var obj2 = ele('form2').getValues();
                                                            var obj = { ...formValues, ...obj2 };
                                                            ele('btn_cancel_pr').disable();
                                                            ele('btn_update_pr').disable();
                                                            var data_send_pr = ele("data_send_pr");
                                                            if (data_send_pr.count() != 0) {
                                                                webix.confirm(
                                                                    {
                                                                        title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการส่งข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                                        callback: function (res) {
                                                                            if (res) {
                                                                                $.post(fd, { obj: obj, type: 12 })
                                                                                    .done(function (data) {
                                                                                        var json = JSON.parse(data);
                                                                                        var data1 = json.data;
                                                                                        ele("data_send_pr").clearAll();
                                                                                        ele("remarks").setValue('');
                                                                                        if (json.ch == 1) {
                                                                                            if (data1.code == 200) {
                                                                                                webix.alert({
                                                                                                    title: "<b>ข้อความจากระบบ</b>", type: "alert-complete", ok: 'ตกลง', text: data1.message, callback: function () {
                                                                                                        ele("pr_no").setValue('');
                                                                                                        setIssueDate();
                                                                                                        window.open(data1.route, '_blank');
                                                                                                    }
                                                                                                });
                                                                                            } else if (data1.code == 400) {
                                                                                                webix.alert({
                                                                                                    title: "<b>ข้อความจากระบบ</b>", type: "alert-error", ok: 'ตกลง', text: data1.message, callback: function () {
                                                                                                        ele("pr_no").setValue('');
                                                                                                        setIssueDate();
                                                                                                    }
                                                                                                });
                                                                                            }
                                                                                        }
                                                                                        else {
                                                                                            webix.alert({
                                                                                                title: "<b>ข้อความจากระบบ</b>", type: "alert-error", ok: 'ตกลง', text: json.data, callback: function () {
                                                                                                    ele("pr_no").setValue('');
                                                                                                    setIssueDate();
                                                                                                }
                                                                                            });
                                                                                        }
                                                                                    })
                                                                            }
                                                                        }
                                                                    });
                                                            }
                                                            else {
                                                                webix.alert({
                                                                    title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูลในตาราง', callback: function () {
                                                                        window.playsound(2);
                                                                        ele('btn_cancel_pr').enable();
                                                                        ele('btn_update_pr').enable();
                                                                    }
                                                                });
                                                            }
                                                        }
                                                    }
                                                },
                                                {
                                                    view: "button", id: $n('btn_cancel_pr'), label: "Cancel PR", width: 100, disabled: true,
                                                    on:
                                                    {
                                                        onItemClick: function (id, e) {
                                                            var pr = ele("pr_no").getValue();
                                                            //console.log(pr);
                                                            var formValues = ele("form1").getValues();
                                                            var obj2 = ele('form2').getValues();
                                                            var obj = { ...formValues, ...obj2 };
                                                            ele('btn_cancel_pr').disable();
                                                            ele('btn_update_pr').disable();
                                                            var data_send_pr = ele("data_send_pr");
                                                            if (data_send_pr.count() != 0) {
                                                                webix.confirm(
                                                                    {
                                                                        title: "กรุณายืนยัน", ok: "ใช่", cancel: "ไม่", text: "คุณต้องการส่งข้อมูล<br><font color='#27ae60'><b>ใช่</b></font> หรือ <font color='#3498db'><b>ไม่</b></font>",
                                                                        callback: function (res) {
                                                                            if (res) {
                                                                                $.post(fd, { obj: obj, type: 13 })
                                                                                    .done(function (data) {
                                                                                        var json = JSON.parse(data);
                                                                                        var data1 = json.data;
                                                                                        ele("data_send_pr").clearAll();
                                                                                        ele("remarks").setValue('');
                                                                                        if (json.ch == 1) {
                                                                                            if (data1.code == 200) {
                                                                                                webix.alert({
                                                                                                    title: "<b>ข้อความจากระบบ</b>", type: "alert-complete", ok: 'ตกลง', text: data1.message, callback: function () {
                                                                                                        ele("pr_no").setValue('');
                                                                                                        setIssueDate();
                                                                                                    }
                                                                                                });
                                                                                            } else if (data1.code == 400) {
                                                                                                webix.alert({
                                                                                                    title: "<b>ข้อความจากระบบ</b>", type: "alert-error", ok: 'ตกลง', text: data1.message, callback: function () {
                                                                                                        ele("pr_no").setValue('');
                                                                                                        setIssueDate();
                                                                                                    }
                                                                                                });
                                                                                            }
                                                                                        }
                                                                                        else {
                                                                                            webix.alert({
                                                                                                title: "<b>ข้อความจากระบบ</b>", type: "alert-error", ok: 'ตกลง', text: json.data, callback: function () {
                                                                                                    ele("pr_no").setValue('');
                                                                                                    setIssueDate();
                                                                                                }
                                                                                            });
                                                                                        }
                                                                                    })
                                                                            }
                                                                        }
                                                                    });
                                                            }
                                                            else {
                                                                webix.alert({
                                                                    title: "<b>ข้อความจากระบบ</b>", ok: 'ตกลง', text: 'ไม่พบข้อมูลในตาราง', callback: function () {
                                                                        window.playsound(2);

                                                                        ele('btn_cancel_pr').enable();
                                                                        ele('btn_update_pr').enable();
                                                                    }
                                                                });
                                                            }
                                                        }
                                                    }
                                                },
                                                {
                                                    view: "button", id: $n('btnclear_datapr'), label: "Clear", width: 80, disabled: true,
                                                    on:
                                                    {
                                                        onItemClick: async (id, e) => {
                                                            setStarDate();
                                                            setLastDate();
                                                            setIssueDate();
                                                            ele('billing_for').setValue('Partner');
                                                            ele('project_name').setValue('');
                                                            ele('carrier').setValue('');

                                                            ele('project_name').enable();
                                                            ele('carrier').enable();
                                                            ele('find_item_pr').enable();

                                                            ele('pr_no').setValue('');
                                                            setIssueDate();
                                                            ele('remarks').setValue('');

                                                            ele('data_send_pr').clearAll();

                                                            ele('btn_create_pr').disable();
                                                            ele('btn_update_pr').disable();
                                                            ele('btn_cancel_pr').disable();
                                                            ele('btnclear_datapr').disable();
                                                        }
                                                    }
                                                },
                                                {}
                                            ]
                                        }

                                    ]
                                },
                            ]
                        }
                    },
                    {
                        id: $n("rowSummaryReport"),
                        hidden: true,
                        cols: [
                            {},
                            {
                                rows: [
                                    { view: "label", id: $n('normal_trip_label'), label: "Normal Trip Delivery", height: 20 },
                                    {
                                        view: "datatable", id: $n('normal_trip'),
                                        datatype: "json", headerRowHeight: 20, rowLineHeight: 15, rowHeight: 15,
                                        footer: true, autoheight: true, hover: false, editable: true,
                                        css: { "font-size": "12px" }, resizeColumn: true, scroll: false, hidden: true,
                                        header: true, autowidth: true,
                                        columns:
                                            [
                                                //{ id: "NO", header: ["NO."],  width: 60 },
                                                { id: "description_show", header: [{ text: "Description", css: { "text-align": "center" } }], width: 200, },
                                                { id: "truckType", header: [{ text: "Truck Type", css: { "text-align": "center" } }], width: 100, css: { "text-align": "center" } },
                                                {
                                                    id: "distanceBand", header: [{ text: "Distance Band", css: { "text-align": "center" } }], width: 120, css: { "text-align": "center" },
                                                },
                                                { id: "unit", header: [{ text: "Unit", css: { "text-align": "center" } }], width: 80, css: { "text-align": "center" } },
                                                {
                                                    id: "unitRate", header: [{ text: "Service Rate", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ text: "Total :", css: { "text-align": "left" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "qty", header: [{ text: "Qty", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "amount", header: [{ text: "Amount", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" },
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                //"background-color":"#bbfac6"
                                                {
                                                    id: "total", header: [{ text: "Total", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right", } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },

                                            ],
                                    },


                                    //
                                    { view: "label", id: $n('blowout_trip_label'), label: "Extra Trip Delivery (Blow Outs)", height: 20 },
                                    {
                                        view: "datatable", id: $n('blowout_trip'), datatype: "json", headerRowHeight: 20, rowLineHeight: 15, rowHeight: 15,
                                        footer: true, autoheight: true, hover: false, editable: true,
                                        css: { "font-size": "12px" }, resizeColumn: true, scroll: false, hidden: true,
                                        header: false,
                                        columns:
                                            [
                                                //{ id: "NO", header: ["NO."],  width: 60 },
                                                { id: "description_show", header: [{ text: "Description", css: { "text-align": "center" } }], width: 200, },
                                                { id: "truckType", header: [{ text: "Truck Type", css: { "text-align": "center" } }], width: 100, css: { "text-align": "center" } },
                                                { id: "distanceBand", header: [{ text: "Distance Band", css: { "text-align": "center" } }], width: 120, css: { "text-align": "center" } },
                                                { id: "unit", header: [{ text: "Unit", css: { "text-align": "center" } }], width: 80, css: { "text-align": "center" } },
                                                {
                                                    id: "unitRate", header: [{ text: "Service Rate", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ text: "Total :", css: { "text-align": "left" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "qty", header: [{ text: "Qty", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "amount", header: [{ text: "Amount", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" },
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "total", header: [{ text: "Total", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },

                                            ],
                                    },



                                    //
                                    { view: "label", id: $n('addnew_trip_label'), label: "Extra Trip Delivery (Additional)", height: 20 },
                                    {
                                        view: "datatable", id: $n('addnew_trip'), datatype: "json", headerRowHeight: 20, rowLineHeight: 15, rowHeight: 15,
                                        footer: true, autoheight: true, hover: false, editable: true,
                                        css: { "font-size": "12px" }, resizeColumn: true, scroll: false, hidden: true,
                                        header: false,
                                        columns:
                                            [
                                                //{ id: "NO", header: ["NO."],  width: 60 },
                                                { id: "description_show", header: [{ text: "Description", css: { "text-align": "center" } }], width: 200, },
                                                { id: "truckType", header: [{ text: "Truck Type", css: { "text-align": "center" } }], width: 100, css: { "text-align": "center" } },
                                                { id: "distanceBand", header: [{ text: "Distance Band", css: { "text-align": "center" } }], width: 120, css: { "text-align": "center" } },
                                                { id: "unit", header: [{ text: "Unit", css: { "text-align": "center" } }], width: 80, css: { "text-align": "center" } },
                                                {
                                                    id: "unitRate", header: [{ text: "Service Rate", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ text: "Total :", css: { "text-align": "left" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "qty", header: [{ text: "Qty", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "amount", header: [{ text: "Amount", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" },
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "total", header: [{ text: "Total", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },

                                            ],
                                    },

                                    { view: "label", id: $n('extra_trip_label'), label: "Extra Trip Delivery (Blow Outs and Additional)", height: 20 },
                                    {
                                        view: "datatable", id: $n('extra_trip'), datatype: "json", headerRowHeight: 20, rowLineHeight: 15, rowHeight: 15,
                                        footer: true, autoheight: true, hover: false, editable: true,
                                        css: { "font-size": "12px" }, resizeColumn: true, scroll: false, hidden: true,
                                        header: false,
                                        columns:
                                            [
                                                //{ id: "NO", header: ["NO."],  width: 60 },
                                                { id: "description_show", header: [{ text: "Description", css: { "text-align": "center" } }], width: 200, },
                                                { id: "truckType", header: [{ text: "Truck Type", css: { "text-align": "center" } }], width: 100, css: { "text-align": "center" } },
                                                { id: "distanceBand", header: [{ text: "Distance Band", css: { "text-align": "center" } }], width: 120, css: { "text-align": "center" } },
                                                { id: "unit", header: [{ text: "Unit", css: { "text-align": "center" } }], width: 80, css: { "text-align": "center" } },
                                                {
                                                    id: "unitRate", header: [{ text: "Service Rate", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ text: "Total :", css: { "text-align": "left" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "qty", header: [{ text: "Qty", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "amount", header: [{ text: "Amount", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" },
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "total", header: [{ text: "Total", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },

                                            ],
                                    },

                                    { view: "label", id: $n('normal_bailment_trip_label'), label: "EDC Bailment Normal Trip Delivery (Normal Trip and Additional)", height: 20 },
                                    {
                                        view: "datatable", id: $n('normal_bailment_trip'), datatype: "json", headerRowHeight: 20, rowLineHeight: 15, rowHeight: 15,
                                        footer: true, autoheight: true, hover: false, editable: true,
                                        css: { "font-size": "12px" }, resizeColumn: true, scroll: false, hidden: true,
                                        header: true, autowidth: true,
                                        columns:
                                            [
                                                //{ id: "NO", header: ["NO."],  width: 60 },
                                                { id: "description_show", header: [{ text: "Description", css: { "text-align": "center" } }], width: 200, },
                                                { id: "truckType", header: [{ text: "Truck Type", css: { "text-align": "center" } }], width: 100, css: { "text-align": "center" } },
                                                {
                                                    id: "distanceBand", header: [{ text: "Distance Band", css: { "text-align": "center" } }], width: 120, css: { "text-align": "center" },
                                                },
                                                { id: "unit", header: [{ text: "Unit", css: { "text-align": "center" } }], width: 80, css: { "text-align": "center" } },
                                                {
                                                    id: "unitRate", header: [{ text: "Service Rate", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ text: "Total :", css: { "text-align": "left" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "qty", header: [{ text: "Qty", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "amount", header: [{ text: "Amount", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" },
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                //"background-color":"#bbfac6"
                                                {
                                                    id: "total", header: [{ text: "Total", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right", } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },

                                            ],
                                    },


                                    { view: "label", id: $n('normal_nonbailment_trip_label'), label: "EDC Non Bailment Normal Trip Delivery (Normal Trip and Additional)", height: 20 },
                                    {
                                        view: "datatable", id: $n('normal_nonbailment_trip'), datatype: "json", headerRowHeight: 20, rowLineHeight: 15, rowHeight: 15,
                                        footer: true, autoheight: true, hover: false, editable: true,
                                        css: { "font-size": "12px" }, resizeColumn: true, scroll: false, hidden: true,
                                        header: true, autowidth: true,
                                        columns:
                                            [
                                                //{ id: "NO", header: ["NO."],  width: 60 },
                                                { id: "description_show", header: [{ text: "Description", css: { "text-align": "center" } }], width: 200, },
                                                { id: "truckType", header: [{ text: "Truck Type", css: { "text-align": "center" } }], width: 100, css: { "text-align": "center" } },
                                                {
                                                    id: "distanceBand", header: [{ text: "Distance Band", css: { "text-align": "center" } }], width: 120, css: { "text-align": "center" },
                                                },
                                                { id: "unit", header: [{ text: "Unit", css: { "text-align": "center" } }], width: 80, css: { "text-align": "center" } },
                                                {
                                                    id: "unitRate", header: [{ text: "Service Rate", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ text: "Total :", css: { "text-align": "left" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "qty", header: [{ text: "Qty", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "amount", header: [{ text: "Amount", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" },
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                //"background-color":"#bbfac6"
                                                {
                                                    id: "total", header: [{ text: "Total", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right", } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },

                                            ],
                                    },

                                    { view: "label", id: $n('blowout_bailment_trip_label'), label: "EDC Bailment Extra Trip Delivery (Blow Out)", height: 20 },
                                    {
                                        view: "datatable", id: $n('blowout_bailment_trip'), datatype: "json", headerRowHeight: 20, rowLineHeight: 15, rowHeight: 15,
                                        footer: true, autoheight: true, hover: false, editable: true,
                                        css: { "font-size": "12px" }, resizeColumn: true, scroll: false, hidden: true,
                                        header: true, autowidth: true,
                                        columns:
                                            [
                                                //{ id: "NO", header: ["NO."],  width: 60 },
                                                { id: "description_show", header: [{ text: "Description", css: { "text-align": "center" } }], width: 200, },
                                                { id: "truckType", header: [{ text: "Truck Type", css: { "text-align": "center" } }], width: 100, css: { "text-align": "center" } },
                                                {
                                                    id: "distanceBand", header: [{ text: "Distance Band", css: { "text-align": "center" } }], width: 120, css: { "text-align": "center" },
                                                },
                                                { id: "unit", header: [{ text: "Unit", css: { "text-align": "center" } }], width: 80, css: { "text-align": "center" } },
                                                {
                                                    id: "unitRate", header: [{ text: "Service Rate", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ text: "Total :", css: { "text-align": "left" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "qty", header: [{ text: "Qty", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "amount", header: [{ text: "Amount", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" },
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                //"background-color":"#bbfac6"
                                                {
                                                    id: "total", header: [{ text: "Total", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right", } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },

                                            ],
                                    },


                                    { view: "label", id: $n('blowout_nonbailment_trip_label'), label: "EDC Non Bailment Extra Trip Delivery (Blow Out)", height: 20 },
                                    {
                                        view: "datatable", id: $n('blowout_nonbailment_trip'), datatype: "json", headerRowHeight: 20, rowLineHeight: 15, rowHeight: 15,
                                        footer: true, autoheight: true, hover: false, editable: true,
                                        css: { "font-size": "12px" }, resizeColumn: true, scroll: false, hidden: true,
                                        header: true, autowidth: true,
                                        columns:
                                            [
                                                //{ id: "NO", header: ["NO."],  width: 60 },
                                                { id: "description_show", header: [{ text: "Description", css: { "text-align": "center" } }], width: 200, },
                                                { id: "truckType", header: [{ text: "Truck Type", css: { "text-align": "center" } }], width: 100, css: { "text-align": "center" } },
                                                {
                                                    id: "distanceBand", header: [{ text: "Distance Band", css: { "text-align": "center" } }], width: 120, css: { "text-align": "center" },
                                                },
                                                { id: "unit", header: [{ text: "Unit", css: { "text-align": "center" } }], width: 80, css: { "text-align": "center" } },
                                                {
                                                    id: "unitRate", header: [{ text: "Service Rate", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ text: "Total :", css: { "text-align": "left" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "qty", header: [{ text: "Qty", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "amount", header: [{ text: "Amount", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" },
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                //"background-color":"#bbfac6"
                                                {
                                                    id: "total", header: [{ text: "Total", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right", } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },

                                            ],
                                    },

                                    //
                                    { view: "label", id: $n('empty_trip_label'), label: "Empty Package Return", height: 20 },
                                    {
                                        view: "datatable", id: $n('empty_trip'), datatype: "json", headerRowHeight: 20, rowLineHeight: 15, rowHeight: 15,
                                        footer: true, autoheight: true, hover: false, editable: true,
                                        css: { "font-size": "12px" }, resizeColumn: true, scroll: false, hidden: true,
                                        header: true, autowidth: true,
                                        columns:
                                            [
                                                //{ id: "NO", header: ["NO."],  width: 60 },
                                                { id: "description_show", header: [{ text: "Description", css: { "text-align": "center" } }], width: 200, },
                                                { id: "truckType", header: [{ text: "Truck Type", css: { "text-align": "center" } }], width: 100, css: { "text-align": "center" } },
                                                { id: "distanceBand", header: [{ text: "Distance Band", css: { "text-align": "center" } }], width: 120, css: { "text-align": "center" } },
                                                { id: "unit", header: [{ text: "Unit", css: { "text-align": "center" } }], width: 80, css: { "text-align": "center" } },
                                                {
                                                    id: "unitRate", header: [{ text: "Service Rate", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ text: "Total :", css: { "text-align": "left" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "qty", header: [{ text: "Qty", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "amount", header: [{ text: "Amount", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" },
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "total", header: [{ text: "Total", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },

                                            ],
                                    },

                                    { view: "label", id: $n('special_trip_label'), label: "Special", height: 20 },
                                    {
                                        view: "datatable", id: $n('special_trip'), datatype: "json", headerRowHeight: 20, rowLineHeight: 15, rowHeight: 15,
                                        footer: true, autoheight: true, hover: false, editable: true,
                                        css: { "font-size": "12px" }, resizeColumn: true, scroll: false, hidden: true,
                                        header: true, autowidth: true,
                                        columns:
                                            [
                                                //{ id: "NO", header: ["NO."],  width: 60 },
                                                { id: "description_show", header: [{ text: "Description", css: { "text-align": "center" } }], width: 200, },
                                                { id: "truckType", header: [{ text: "Truck Type", css: { "text-align": "center" } }], width: 100, css: { "text-align": "center" } },
                                                { id: "distanceBand", header: [{ text: "Distance Band", css: { "text-align": "center" } }], width: 120, css: { "text-align": "center" } },
                                                { id: "unit", header: [{ text: "Unit", css: { "text-align": "center" } }], width: 80, css: { "text-align": "center" } },
                                                {
                                                    id: "unitRate", header: [{ text: "Service Rate", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ text: "Total :", css: { "text-align": "left" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "qty", header: [{ text: "Qty", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "amount", header: [{ text: "Amount", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" },
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "total", header: [{ text: "Total", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },

                                            ],
                                    },

                                    //
                                    { view: "label", id: $n('Management_Fees_label'), label: "Management Fees", height: 20 },
                                    {
                                        view: "datatable", id: $n('Management_Fees'), datatype: "json", headerRowHeight: 20, rowLineHeight: 15, rowHeight: 15,
                                        footer: true, autoheight: true, hover: false, editable: true,
                                        css: { "font-size": "12px" }, resizeColumn: true, scroll: false, hidden: true,
                                        header: true,
                                        columns:
                                            [
                                                //{ id: "NO", header: ["NO."],  width: 60 },
                                                { id: "description", header: [{ text: "Description", css: { "text-align": "center" } }], width: 420, },
                                                //{ id: "truckType", header: [{ text: "", css: { "text-align": "center" } }], width: 100, css: { "text-align": "center" } },
                                                //{ id: "distanceBand", header: [{ text: "", css: { "text-align": "center" } }], width: 120, css: { "text-align": "center" } },
                                                { id: "unit", header: [{ text: "Unit", css: { "text-align": "center" } }], width: 80, css: { "text-align": "center" } },
                                                {
                                                    id: "unitRate", header: [{ text: "Service Rate", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ text: "Total :", css: { "text-align": "left" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                { id: "qty", header: [{ text: "Qty", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, },
                                                {
                                                    id: "amount", header: [{ text: "Amount", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" },
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "total", header: [{ text: "Total", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },

                                            ],
                                        //data: Management_Fees
                                    },
                                    { height: 20 },
                                    {
                                        view: "datatable", id: $n('Summary_Service_Charge'), datatype: "json", headerRowHeight: 20, rowLineHeight: 15, rowHeight: 15,
                                        footer: true, autoheight: true, hover: false, editable: true,
                                        css: { "font-size": "12px" }, resizeColumn: true, scroll: false, hidden: true, autowidth: true,
                                        header: true,
                                        columns:
                                            [
                                                { id: "description", header: [{ text: "Summary Service Charge", css: { "text-align": "center" } }], width: 420, footer: [{ text: "Total Cost:", css: { "text-align": "left" } }], },
                                                {
                                                    id: "cost", header: [{ text: "Cost", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },

                                            ],
                                        //data: Summary_Service_Charge
                                    },
                                    { height: 20 }
                                ]
                            },
                            {}
                        ]
                    },
                    {
                        id: $n("rowDataPR"),
                        hidden: true,
                        rows: [
                            {
                                cols: [
                                    {},
                                    {
                                        view: "datatable", id: $n('data_send_pr'), datatype: "json", headerRowHeight: 20, rowLineHeight: 20, rowHeight: 20,
                                        footer: true, autoheight: true, hover: false, editable: true,
                                        css: { "font-size": "12px" }, resizeColumn: true, scroll: false, hidden: false,
                                        header: true,
                                        autowidth: true,
                                        columns:
                                            [
                                                { id: "NO", header: [{ text: "No.", css: { "text-align": "center" } }], css: { "text-align": "center" }, width: 60 },
                                                { id: "project", header: [{ text: "Projects", css: { "text-align": "center" } }], css: { "text-align": "center" }, width: 100, },
                                                { id: "trip_detail", header: [{ text: "Description", css: { "text-align": "center" } }], css: { "text-align": "center" }, width: 350, },
                                                {
                                                    id: "quantity", header: [{ text: "Quantity", css: { "text-align": "center" } }], width: 80, css: { "text-align": "right" }, format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                { id: "unit", header: [{ text: "UOM", css: { "text-align": "center" } }], width: 80, css: { "text-align": "center" } },
                                                {
                                                    id: "unit_price", header: [{ text: "Unit Price", css: { "text-align": "center" } }], width: 100, css: { "text-align": "right" }, footer: [{ text: "Total :", css: { "text-align": "right" } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                                {
                                                    id: "amount", header: [{ text: "Sum of Amount", css: { "text-align": "center" } }], width: 100, css: { "text-align": "right" }, footer: [{ content: "summColumn", css: { "text-align": "right", } }],
                                                    format: webix.Number.numToStr({
                                                        groupDelimiter: ",",
                                                        groupSize: 3,
                                                    })
                                                },
                                            ],
                                    },
                                    {}
                                ],
                            },
                            { height: 20 }
                        ]
                    },
                    {
                        id: $n("rowTripSummayReport"),
                        hidden: true,
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
                                                { id: "truck_carrier", header: [{ text: "Truck Carrier", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 90, },
                                                { id: "operation_date", header: [{ text: "Operation Date", css: { "text-align": "center" } }, { content: "dateFilter" }], css: { "text-align": "center" }, width: 90, },
                                                { id: "Start_Datetime", header: [{ text: "Pickup Date", css: { "text-align": "center" } }, { content: "dateFilter" }], css: { "text-align": "center" }, width: 80, },
                                                { id: "End_Datetime", header: [{ text: "Delivery Date", css: { "text-align": "center" } }, { content: "dateFilter" }], css: { "text-align": "center" }, width: 90, },
                                                { id: "Load_ID", header: [{ text: "JDA Tracking no.", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 180, },
                                                { id: "Route", header: [{ text: "JDA Route no.", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 100, },
                                                //{ id: "Internal_Tracking", header: [{ text: "Internal Tracking No.", css: { "text-align": "center" } }, { content: "textFilter" }], css: { "text-align": "center" }, width: 100, },
                                                { id: "Work_Type", header: [{ text: "Status", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 80, },
                                                { id: "truckType", header: [{ text: "Truck Type", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 80, },
                                                { id: "tripType", header: [{ text: "Status Route", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 100, },
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
                                                { id: "pr_no", header: [{ text: "PR No.", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 100, },
                                                { id: "invoice_no", header: [{ text: "Invoice No.", css: { "text-align": "center" } }, { content: "selectFilter" }], css: { "text-align": "center" }, width: 100, hidden: 0 },
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
                                            size: 10,
                                            group: 5
                                        }
                                    ]
                            }
                        ]

                    },
                    {
                        height: 10
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