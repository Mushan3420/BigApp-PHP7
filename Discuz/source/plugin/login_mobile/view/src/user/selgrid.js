define(function(require){
    var ajax=require("ajax");
    var store,grid,o={};

    o.init = function() {
	    o.enter();
    };

    o.enter = function(){
        var thiso = this;
        //alert("category grid");
        store = new MWT.Store({
            "url": v.ajaxapi+"action=query"
        });

        grid = new MWT.Grid({
            render: "usergrid-div",
            store: store,
            pagebar: true,
            pageSize: 10, 
            multiSelect:true, 
            bordered: true,
            cm: new MWT.Grid.ColumnModel([
                {dataIndex:'phone', head:'手机号',sort:true,align:"center",width:100,render:function(v,record){
                    if(v==null) return "<span style='color:#999;'>未绑定手机号</span>";
                    return v;
                }},
                {dataIndex:'uid', head:'用户ID',width:70,sort:true,render:function(v,record){
                    return v;
                }},
                {dataIndex:'username', head:'用户名',width:150,sort:true,render:function(v,record){
                    return v;
                }},
                {dataIndex:'realname', head:'真实姓名',width:150,sort:true,render:function(v,record){
                    return v;
                }},
                {dataIndex:'regdate', head:'注册日期',width:150,align:'center',sort:true,render:function(v,record){
                    return v;
                }}
            ]),
            tbar: [
                {type:"search",label:"查询",id:"so-key",width:400,placeholder:"输入手机号或用户名",handler:thiso.query},
                '->'//,
                //{"label":"批量解绑",class:'mwt-btn-danger',handler:function(){thiso.unbind();}}
            ]
        });
        grid.create();
        thiso.query();
        $("#addbtn").click(function(){
			var records=grid.getSelectedRecords();
			if (records.length==0) {
				alert("未勾选记录");
				throw new Error("未勾选记录");
			}
            //alert(records.length);
            var phonemap = {};
            $("[name='userli']").each(function(index){
                var phone=$(this).html();
                phonemap[phone] = 1;
            });
			for (var i=records.length-1;i>=0;--i) {
				//idarr.push(records[i].phone);
                var im=records[i];
                if (phonemap[im.phone]) continue;
                var htm="<li class='selli' name='userli' data-uid='"+im.uid+"' data-username='"+im.username+"' data-realname='"+im.realname+"' data-state='1'>"+im.phone+"</li>";
                $("#userul").append(htm);
			}   
            $("[name='userli']").dblclick(function(){
                $(this).remove();
            });
            $("#subbtn").html("发送");
        });
        $("#msgtxt").change(function(){
            $("[name='userli']").each(function(index){
				$(this).attr("data-state","1");
            });
            $("#subbtn").html("发送");
			$("#cosoleul").html("");
			$("#sendmsg").html("");
        });
        $("#subbtn").click(function(){
            var msg = get_text_value("msgtxt");
            if (msg>50) {
                alert("短信内容不能超过50个字符");
                return;
            }
            if (!window.confirm("确定要发送吗？"))return;
            $("#sendmsg").html("开始发送");
			$("#cosoleul").html("");
            var succ=0;
            var fail=0;
            var total=0;
            $("[name='userli']").each(function(index){
                var state=$(this).attr("data-state");
                if(parseInt(state)==1) {
                    ++total;
                    var phone=$(this).html();
					var uid=$(this).data("uid");
					var username=$(this).data("username");
					var realname=$(this).data("realname");
					var smmsg=msg;
                    smmsg = smmsg.replace(/\$uid/ig, uid);
                    smmsg = smmsg.replace(/\$username/ig, username);
                    smmsg = smmsg.replace(/\$realname/ig, realname);

					$("#sendmsg").html("正在发送第 "+total+" 条短信...");
					var rs=o.sendmsg(phone, smmsg);
					if (rs.retcode==0) {
                        ++succ;
					    $("#cosoleul").append("<li style='color:green;'>【发送成功】"+phone+" "+smmsg+"</li>");
						$(this).attr("data-state","0");
					} else {
                        ++fail;
					    $("#cosoleul").append("<li style='color:red;'>【发送失败】"+phone+" "+rs+"</li>");
					}
                }
            });
            $("#sendmsg").html("全部发送完毕，共发送了 "+total+" 条短信，成功："+succ+" 条，失败："+fail+" 条。");
            if (fail>0) {$(this).html("失败重发");}
        });
    };

    o.query = function() {
        store.baseParams = {
            "key": get_value("so-key"),
            "status": 1
        };
        grid.load();
    };

    o.reset = function() {
        $("#userul").html("");
        $("#cosoleul").html("");
        $("#sendmsg").html("");
        $("#subbtn").html("发送");
    };

    o.sendmsg = function(phone, msg) {
        var result = {
            retcode: 0,
            retmsg: "succ"
        };
        //return result;
        var params = {
            phone: phone,
            msg: msg
        };
        var url = v.ajaxapi+"action=sendmsg";
        jQuery.ajax({
            url: url,
            type: "post",
            async: false,
            dataType: "json",
            data: params,
            complete: function(res) {
            },
            success: function(res) {
                return res;
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                var errmsg = "Error("+XMLHttpRequest.readyState+") : "+textStatus;
                result["retcode"] = 1;
                result["retmsg"] = errmsg;
            }
        });
		return result;
    };

    return o;
});
