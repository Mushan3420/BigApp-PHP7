define(function(require){
    require("jquery");
    require("mwt");
    require("site/main");
    var ajax=require("ajax");
    var refer,o={};

    function get_phone() {
	    var phone = get_text_value("fm-phone");
	    if (!is_phone(phone)) {
		    $("#errmsg").html("请输入11位手机号");
			$("#fm-phone").val("");
			$("#fm-phone").focus();
			throw new Error("请输入11位手机号");
		}
        return phone;
    };


    function initpage(res) {
        $("#frame-body").css("background-color","#eee");
        var urlsfx = getUrlSubfix();
        var code = "";
        var headdiv = "<table class='tablay'><tr>"+
                "<td class='title' onclick='window.history.go(-1);'>绑定手机</td>"+
                "<td style='text-align:right'></td>"+
              "</tr></table>";

        var fields = [
            {type:'html',html:headdiv},
            {type:'html',html:"<div id='errmsg' style='padding-bottom:5px;color:red;font-size:15px;'></div>"},
            {type:'text',icon:"am-icon-phone",id:'fm-phone',placeholder:"输入手机号"},
            {type:'seccode',icon:"am-icon-sun-o",id:"fm-seccode",placeholder:"验证码"},
            {type:'pcode',icon:"am-icon-sun-o",id:"fm-pcode",placeholder:"短信验证码"},
            {type:'button',id:"login-btn",text:"提交"},
            {type:'html',html:"<br><p align='center' style='color:gray;'>"+res.username+"，<a href='"+getSiteUrl()+"' style='color:gray;'>返回首页</a></p>"}
        ];
        code += show_fieldset(fields);
		$("#frame-body").html(code);
        // bundle event
        $("#imgcode").click(function(){
            var d = new Date();
            var url = ajaxapi+"?version=4&module=seccode&tm="+d.getTime();
            $(this).attr("src", url);
        });
        $("#pcode-btn").click(o.send_pcode);
        $("#login-btn").click(function(res){
            $("#errmsg").html("");
            var params = {
                phone: get_text_value("fm-phone"),
                pcode: get_text_value("fm-pcode")
            };
            //print_r(params);
            ajax.post("bind&action=bind",params,function(res){
                if(res.retcode!=0) $("#errmsg").html(res.retmsg);
                else {
                    window.location.reload();
                }
            });
        });
    };

	// 发送短信验证码
    o.send_pcode = function() {
		$("#errmsg").html("");
        var params = {
            phone: get_phone(),
            seccode: get_text_value("fm-seccode")
        };
        //print_r(params);
        leftseconds = 60;
        o.disable_pcode_btn();
        ajax.post("smscode&regist=1",params,function(res){
            if(res.retcode!=0) {
                $("#errmsg").html(res.retmsg);
                leftseconds = 0;
            }
        });
    };

    // 发送短信验证码成功后，必须隔一段时间才能再次发送
    o.disable_pcode_btn = function() {
        $("#pcode-btn").attr("disabled",true);
        $("#pcode-btn").html(leftseconds+" 秒后重新发送");
        --leftseconds;
        if (leftseconds<=0) {
            $("#pcode-btn").attr("disabled",false);
            $("#pcode-btn").html("发送短信验证码");
            return;
        }
        setTimeout(o.disable_pcode_btn, 1000);
    };

    /////////////////////////////////////////////
    function initunbind(res) {
        $("#frame-body").css("background-color","#fff");
        var headdiv = "<table class='tablay'><tr>"+
                "<td class='title' onclick='window.history.go(-1);'>返回</td>"+
                "<td style='text-align:right'></td>"+
              "</tr></table>";
		var fields = [
		    {type:'html',html:headdiv},
		    {type:'html',html:"<p style='margin:5px;'>您已绑定手机：<b>"+res.phone+"</b></p>"},
            //{type:'seccode',icon:"am-icon-sun-o",id:"fm-seccode",placeholder:"验证码"},
		    {type:'button',id:"unbind-btn",text:"解除手机绑定"},
            {type:'html',html:"<br><p align='center' style='color:gray;'>"+res.username+"，<a href='"+getSiteUrl()+"' style='color:gray;'>返回首页</a></p>"}
		];
		var code = show_fieldset(fields);
		$("#frame-body").html(code);
		$("#unbind-btn").click(function(res){
		if (!window.confirm("确定要解除手机绑定吗？")) return;
			require("ajax").post("bind&action=unbind",{},function(res){
                if (res.retcode==0) {
                    alert("手机绑定已解除");
					window.location.reload();
                } else {
                    alert(res.retmsg);
                }
			});
		});
    }
    /////////////////////////////////////////////

    o.init=function(){
        refer = getRefer();
        ajax.loadcache("profile",function(res){
            if (res.uid>0) {
                if(res.phone=="") {
				    initpage(res);
                } else {
					initunbind(res);
                }
            } else {
			    var url = "login.html"+getUrlSubfix();
                window.location=url;
            }
        });
    };

    return o;
});
