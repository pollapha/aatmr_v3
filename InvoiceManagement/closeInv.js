var header_closeInv = function()
{
	var menuName="closeInv_",fd = "InvoiceManagement/"+menuName+"data.php";

    function init()
    {
        focus('supReciverName');
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
            ele('supReciverName').setValue('');
            ele('supReciverDate').setValue('');
            focus('supReciverName');
            // webix.alert({title:"<b>ข้อความจากระบบ</b>",ok:'ตกลง',text:json.data,callback:function(){}});
            webix.message({ type:"default",expire:7000, text:json.data});
        },null,function()
        {
            ele('doc').focus();
            ele('doc').setValue('');
        });
    };

	return {
        view: "scrollview",
        scroll: "native-y",
        id:"header_closeInv",
        body: 
        {
        	id:"closeInv_id",
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
                                vw1("text","supReciverName"," ผู้รับเอกสาร",{}),
                                vw1("datepicker","supReciverDate","วันที่รับ",{stringResult:1,format:"%Y-%m-%d",
                                on:{
                                    'onChange': function(newv, oldv){ 
                                        focus('doc');
                                    }
                                }
                            }),
                                vw1("text","doc","เลขที่เอกสาร INV",{}),
                            ]
                        },
                    ]
                }
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