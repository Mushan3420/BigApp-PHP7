var jq = jQuery.noConflict();
var BS;
(function ($) {

BS = function () {

    function sync_smsid_desc()
    {
        var sid = get_select_value("fm-smsid");
        var code = "您可以前往<a href='http://www.duanxin.cm/' target='_blank'>莫名短信官网</a>申请账号";
        var list = v.clients;
		for (var i=0;i<list.length; ++i) {
            if (sid==list[i].value) {
			    code = list[i].desc;
                break;
            }
		}
        $("#smsid-desc").html(code);
    }

    function initsmssel(list)
    {
        var code = "";
        if (!list || list.length==0) {
            code = "<option value='1'>莫名短信</option>";
        } else {
            for (var i=0;i<list.length; ++i) {
                var val = list[i].value;
                var t = list[i].text;
                var selected = (v.smsid==val) ? "selected" : "";
                code += "<option value='"+val+"' "+selected+">"+t+"</option>";
            }
        }
console.log(v);
        $("#fm-smsid").html(code);
        sync_smsid_desc();
        $("#fm-smsid").change(function(){
            sync_smsid_desc();
        });
    }

    this.init = function() {
        var thiso=this;
        $("#fm-username").val(v.username);
        $("#fm-password").val(v.password);
        $("#fm-template1").val(v.template1);
        $("#fm-template2").val(v.template2);
        $("#testbtn").click(thiso.send_test_message);

        if (v.list && v.list.length>0) {
		    var code = "";
		    for (var i=0; i<v.list.length; ++i) {
		        code += "<li>"+v.list[i]+"</li>";
		    }
		    $("#infoul").html(code);
        }

        initsmssel(v.clients);
    };

    this.send_test_message = function() {
        var params = {
            "smsid"   : get_select_value("fm-smsid"),
			"username": get_text_value("fm-username"),
			"password": get_text_value("fm-password"),
            "phone"   : get_text_value("fm-phone"),
            "template1": get_text_value("fm-template1")
		};
        set_value("fm-phone", params.phone);
        set_value("fm-username", params.username);
        set_value("fm-password", params.password);
        set_value("fm-template1", params.template1);
		$("#testbtn").attr("disabled","disabled");
        $.ajax({
            type: "post",
            async: false,
            url: v.testapi,
            data: params,
            dataType: "json",
            complete: function(res) {
                $("#testbtn").removeAttr("disabled");
            },
            success: function (res) {
                if (res.retcode==0) {
                    var s = "<span style='color:darkgreen'>发送成功，稍后您将收到一条测试短息</span>";
					$("#resmsg").html(s);
                } else {
                    var s = "<span style='color:red'>"+res.retmsg+"</span>";
					$("#resmsg").html(s);
                }
            },
            error: function (data) {
			    var s = "<span style='color:red'>服务器内部异常</span>";
			    $("#resmsg").html(s);
            }
        });
    };
};

$(function () {
    var app = new BS();
    app.init();
})

})(jq);
