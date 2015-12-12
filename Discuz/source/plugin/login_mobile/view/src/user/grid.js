define(function(require){
    var ajax=require("ajax");
    var dialog=require("./dialog");
    var store,grid,o={};

    o.init = function() {
	    o.enter();
        dialog.init();
    };

    o.enter = function(){
        var thiso = this;
        //alert("category grid");
        var stateOptions = [{value:-1,text:"不限"},{value:1,text:"已绑定"},{value:0,text:"未绑定"}];
        store = new MWT.Store({
            "url": v.ajaxapi+"action=query",
        });

        grid = new MWT.Grid({
            render: "grid-div",
            store: store,
            pagebar: true,
            pageSize: 20, 
            multiSelect:true, 
            bordered: true,
            cm: new MWT.Grid.ColumnModel([
                {dataIndex:'uid', head:'用户ID',width:70,sort:true,render:function(v,record){
                    return v;
                }},
                {dataIndex:'username', head:'用户名',width:150,sort:true,render:function(v,record){
                    return v;
                }},
                {dataIndex:'phone', head:'手机号',sort:true,align:"center",render:function(v,record){
                    if(v==null) return "<span style='color:#999;'>未绑定手机号</span>";
                    return v;
                }},
                {dataIndex:'regdate', head:'注册日期',width:150,align:'center',sort:true,render:function(v,record){
                    return v;
                }},
                {dataIndex:'phone', head:'操作',width:120,align:'center',render:function(v,record){
                    var uid = record.uid;
                    var bind_btn = "<a href='javascript:require(\"user/dialog\").open("+uid+");'>绑定手机</a>";
                    var unbind_btn = "<a href='javascript:require(\"user/grid\").unbind("+v+");' style='color:red;'>解除手机绑定</a>";
                    var btns = [unbind_btn];
                    if(v==null) btns = [bind_btn];
                    return btns.join("&nbsp;&nbsp;");
                }}
            ]),
            tbar: [
                {type:"select",label:"手机绑定状态",id:"state-sel",width:90,options:stateOptions,handler:thiso.query,value:'-1'},
                {type:"search",label:"查询",id:"so-key",width:400,placeholder:"输入手机号或用户名",handler:thiso.query},
                '->'//,
                //{"label":"批量解绑",class:'mwt-btn-danger',handler:function(){thiso.unbind();}}
            ]
        });
        grid.create();
        thiso.query();
    };

    o.query = function() {
        store.baseParams = {
            "key": get_value("so-key"),
            "status": get_select_value("state-sel")
        };
        grid.load();
    };

    o.getItem = function(id) {
        return grid.getRecord("pid",id);
    };

    o.add = function() {
        dialog.open(0);
    };

    o.get_selected_ids = function() {
        var idarr = [];
        var records=grid.getSelectedRecords();
        if (records.length==0) {
            alert("未勾选记录");
            throw new Error("未勾选记录");
        }
        for (var i=records.length-1;i>=0;--i) {
			idarr.push(records[i].phone);
        };
        return idarr.join(",");
    };

    o.unbind = function(ids) {
        if (!ids) { ids=o.get_selected_ids(); }
        if(!window.confirm("确定要解除手机绑定吗？")) { return; }
        ajax.post("action=unbind",{ids:ids},function(){
            o.query();
        });
    };

    return o;
});
