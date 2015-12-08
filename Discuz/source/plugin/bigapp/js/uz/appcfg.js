var jq = jQuery.noConflict();
var UZ;
(function ($) {

    UZ = function () {

        this.init = function() {
            var thiso = this;
            $("#subbtn").click(function(){
				thiso.submit();
            });
            if(v.qq_login==1) $("#fm_qq_login").attr("checked",true);
            if(v.wechat_login==1) $("#fm_wechat_login").attr("checked",true);
            if(v.weibo_login==1) $("#fm_weibo_login").attr("checked",true);
            if(!v.qqconnect){
                $("#fm_qq_login").attr("disabled",true);
                $("#qq_login_label").html("（请先开启QQ互联）");
            }else{
                $("#qq_login_label").html("");
            }
        };

        this.submit = function() {
            var params = {
                "qq_login": $("#fm_qq_login").attr("checked")?1:0,
                "wechat_login": $("#fm_wechat_login").attr("checked")?1:0,
                "weibo_login": $("#fm_weibo_login").attr("checked")?1:0
            };
//            print_r(params);
//            return;
            var thiso = this;
            $.ajax({
                type: "post",
                async: false,
                url: v.ajaxurl,
                data: params,
                dataType: "json",
                success: function (res) {
                    if (res.error_code==0) {
					    alert("设置已更改");
					} else {
                        if (res.error_code==100803) {
                            alert("您没有权限管理此页面");
                        } else {
                            alert(res.error_msg);
                        }
					}
                },
                error: function (data) {
                    alert("设置保存失败");
                }
            });
        };
    };

    $(function () {
        var app = new UZ();
        app.init();
    })

})(jq);
