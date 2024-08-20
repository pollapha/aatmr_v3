var header_docIssue = function()
{
	var menuName="docIssue_",fd = "InvoiceManagement/"+menuName+"data.php";

    function init()
    {
        ele('doc').focus();
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

    function findData()
    {
        ajax(fd,ele('form1').getValues(),21,function(json)
        {
            ele('doc').setValue('');
            ele('truckLicense').setValue('');
            ele('driverName').setValue('');
            ele('phone').setValue('');
            focus('driverName');
            webix.message({ type:"default",expire:7000, text:json.data});
            // webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:json.data,callback:function(){}});
        },null,function()
        {
            ele('doc').focus();
            ele('doc').setValue('');
        });
    };

	return {
        view: "scrollview",
        scroll: "native-y",
        id:"header_docIssue",
        body: 
        {
        	id:"docIssue_id",
        	type:"clean",
    		rows:
    		[
    		    {
                    view:"form",
                    paddingY:0,
                    id:$n('form1'),
                    on:
                    {
                        "onSubmit":function(view,e)
                        {
                            if (view.config.name == 'doc')
                            {
                                view.blur();
                                findData();
                            }
                            else if (view.config.name == 'phone')
                            {
                                focus('doc');
                            }
                            else if(webix.UIManager.getNext(view).config.type == 'line')
                            {
                                webix.UIManager.setFocus(webix.UIManager.getNext(webix.UIManager.getNext(view)));
                            }
                            else
                            {
                                webix.UIManager.setFocus(webix.UIManager.getNext(view));
                            }
                        }
                    },
                    elements:
                    [
                        {
                            cols:
                            [
                                {},
                                vw1("text","doc","เลขที่เอกสาร INV",{}),
                                {}
                            ]
                        },
                        {
                            cols:
                            [
                                vw1("text","truckLicense","ทะเขียนรถ",{required:false}),
                                vw1("text","driverName","คนขับรถ",{required:false,suggest:
                                    {
                                        body: 
                                        {
                                            dataFeed: function(text){inputFeed(this,fd+"?type=3&code="+text);},
                                        }
                                    },on:
                                    {
                                        onKeyPress:function(code, e){inputEnter(this,code,e)},
                                        onChange:function(vNew,vOld)
                                        {
                                            if(vNew.trim().length == 0)
                                            return;
                                            var obj = {driverName:vNew};
                                            ajax(fd,obj,4,function(json)
                                            {
                                                if(json.data.length == 0 ) return;
                                                var data = json.data[0];
                                                ele('truckLicense').setValue(data.truckNo);
                                                ele('phone').setValue(data.phone);
                                                focus('doc');
                                            },null,function()
                                            {

                                            });
                                        }
                                    }
                                }),
                                vw1("text","phone","เบอร์โทร",{required:false}),
                            ]
                        }
                    ]
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