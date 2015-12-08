var jq = jQuery.noConflict();
var UZ;
(function ($) {

    UZ = function () {

        this.enable_input = function() {
            var thiso = this;
            $("#fm_ak").removeAttr("readonly");
            $("#fm_sk").removeAttr("readonly");
            $("#getkeybtn").attr("disabled",false);
            $("#subbtn").attr("disabled",false);
			$("#subbtn").click(function(){
				thiso.submit();
            });
            $("#getkeybtn").click(function(){
				thiso.getRemoteKey();
            });
        };
        this.disable_input = function() {
            $("#fm_ak").attr("readonly","readonly");
            $("#fm_sk").attr("readonly","readonly");
            $("#getkeybtn").attr("disabled",true);
            $("#subbtn").attr("disabled",true);
        };

        this.init = function() {
            var thiso = this;
            
            $("#fm_ak").val(v.ak);
            $("#fm_sk").val(v.sk);

            var checkin = v.checkin;

            // 新客户没有设置过aksk
            if(v.ak=="" || v.sk=="") {
                $("#info-div").html("非常感谢使用BigApp插件，请先前往BigApp应用中心创建应用。<br><a href='"+checkin+"' target='_blank'>点此前往</a>");
                this.enable_input();
            } 
            // 老客户引导到BigApp应用中心绑定账户
            else if (v.vertify!=1) {
                //$("#info-div").html("我们注意到您刚刚升级了我们的插件，非常感谢您的关注；\
//我们诚挚邀请您尽快于新版的BigApp应用中心里绑定您的账户，新版的BigApp应用中心将更加强大，美观，易于使用；同时还能享受终身免费的服务。<br>\
//<a href='"+checkin+"' target='_blank'>点此前往</a>");
                $("#info-div").html("您的站点认证出了点问题，可能是由于以下一些原因造成的：<br>\
1、若您是第一次从1.x旧版插件进行的升级，则请 <a href='"+checkin+"' target='_blank'>点此前往</a> 全新的BigApp应用中心绑定您的应用。<br>\
2、您当前的AppKey与AppSecret不匹配，导致认证失败或尚未完成认证流程，您可以 <a href='"+checkin+"' target='_blank'>点此前往</a> BigApp应用中心核对您的AppKey与AppSecret重新进行认证。<br>\
3、您的应用尚未创建或者已经被删除，您可以 <a href='"+checkin+"' target='_blank'>点此前往</a> BigApp应用中心重新创建您的应用。<br>\
");
                this.enable_input();
            }
            // 客户已通过认证 
            else {
                $("#info-div").html("恭喜，您的站点已通过认证！您现在可以 <a href='"+v.pack_and_config_url+"' target='_blank'>点此打包与配置您的应用</a>");
                this.disable_input();
            }
            if (!v.api_file_exists) {
                var code = "请注意：您的引导文件不存在或不可读，请将文件 <b>"+v.api_file_libs+"</b> 拷贝至 <b>"+v.api_file_dir+"</b>，并保证文件可读！";
                $("#apierrmsg").html(code);
            }
        };

        this.getRemoteKey = function() {
            window.open(v.checkin);
            /*
            var thiso = this;
            var url = v.ajaxurl;
            var params = {"method": "getkey"};
            var errmsg = "获取不到Key，请先到BigApp应用中心认证";
            $.ajax({
                type: "get",
                async: false,
                url: v.ajaxurl,
                data: params,
                dataType: "json",
                success: function (res) {
                    if (res.error_code==0) {
                        if (res.data.ak=="" || res.data.sk=="") {
                            alert(errmsg);
                        } else {
                            $("#fm_ak").val(res.data.ak);
                            $("#fm_sk").val(res.data.sk);
                        }
					} else {
                        if (res.error_code==100803) {
                            alert("您没有权限管理此页面");
                        } else {
                            alert(errmsg);
                        }
					}
                },
                error: function (data) {
                    alert(errmsg);
                }
            });*/
        };

        this.submit = function() {
            var params = {
                "ak": get_text_value("fm_ak"),
                "sk": get_text_value("fm_sk")
            };
            //print_r(params);
            var thiso = this;
            $.ajax({
                type: "post",
                async: false,
                url: v.ajaxurl,
                data: params,
                dataType: "json",
                success: function (res) {
                    if (res.error_code==0) {
					    alert("已保存");
                        window.location.reload();
					} else {
                        if (res.error_code==100803) {
                            alert("您没有权限管理此页面");
                        }
                        else if (res.error_code==1) {
                            alert("aksk已保存！但您的站点未创建应用，请按页面提示，前往BigApp应用中心创建应用。");
                        }
                        else if (res.error_code==2) {
                            alert("aksk已保存！但您的应用未通过认证，请按页面提示，前往BigApp应用中心完成认证。");
                        }
                        else {
                            alert(res.error_msg);
                        }
					}
                },
                error: function (data) {
                    alert("保存失败");
                }
            });
        };
    };

    $(function () {
        var app = new UZ();
        app.init();
    })

})(jq);
