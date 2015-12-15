define(function(require){
    require("jquery");
    require("mwt");
    require("site/main");
    var ajax=require("ajax");
    var refer,o={};
    var leftseconds = 60;

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

    function initpage() {
        var urlsfx = getUrlSubfix();
        var code = "";
        var headdiv = "<table class='tablay'><tr>"+
                "<td class='title' onclick='window.history.go(-1);'>重置密码</td>"+
                "<td style='text-align:right'><a href='login.html"+urlsfx+"'>返回登录</a></td>"+
              "</tr></table>";

        var fields = [
            {type:'html',html:headdiv},
            {type:'html',html:"<div id='errmsg' style='padding-bottom:5px;color:red;font-size:15px;'></div>"},
            {type:'text',icon:"am-icon-phone",id:'fm-phone',placeholder:"输入注册手机号"},
            {type:'password',icon:"am-icon-lock",id:'fm-passwd',placeholder:"新密码"},
            {type:'password',icon:"am-icon-lock",id:'fm-passwd2',placeholder:"重复新密码"},
            {type:'seccode',icon:"am-icon-sun-o",id:"fm-seccode",placeholder:"验证码"},
            {type:'pcode',icon:"am-icon-sun-o",id:"fm-pcode",placeholder:"短信验证码"},
            {type:'button',id:"subbtn",text:"提交"}
        ];
        code += show_fieldset(fields);
		$("#frame-body").html(code);
        // bundle event
        $("#imgcode").click(function(){
            var d = new Date();
            var url = ajaxapi+"?version=4&module=seccode&tm="+d.getTime();
            $(this).attr("src", url);
        });
        $("#subbtn").click(function(res){
            $("#errmsg").html("");
            var params = {
                phone: get_phone(),
                password: get_text_value("fm-passwd"),
                password2: get_text_value("fm-passwd2"),
                seccode: get_text_value("fm-seccode"),
                pcode: get_text_value("fm-pcode")
            };
            if (params.password2!=params.password) {
                $("#errmsg").html("两次输入的密码不一致");
                $("#fm-passwd2").val("");
                $("#fm-passwd2").focus();
                return;
            }
            //print_r(params);
            ajax.post("resetpass",params,function(res){
                if(res.retcode!=0) $("#errmsg").html(res.retmsg);
                else {
                    alert(res.retmsg);
                    window.location = "login.html"+getUrlSubfix();
                }
            });
        });
        $("#pcode-btn").click(o.send_pcode);
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
        ajax.post("smscode",params,function(res){
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

    o.init=function(){
        refer = getRefer();
        ajax.loadcache("profile",function(res){
            if (res.uid>0) {
                o.jump(res);
            } else {
			    initpage();
            }
        });
    };

    o.jump = function(res){
		if (refer) {
			window.location = refer;
		} else {
			welcome(res);
        }
    };

    return o;
});
