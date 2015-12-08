/*
 * 发布管理页面JS
 */

var jq = jQuery.noConflict();
var UZ;
(function ($) {

    UZ = function () {

        this.init = function() {
            var thiso = this;
            $("#openbtn").click(function(){
                window.open(_bigapp_obj.mobileurl);
            });
            $("#subbtn").click(function(){
				thiso.submit();
            });
			
            set_value("fm_ios_url",_bigapp_obj.iosurl);
            set_value("fm_appdesc",_bigapp_obj.appdesc);
        };

        this.submit = function() {
            var params = {
                "ios_url": get_text_value("fm_ios_url"),
                "title": get_text_value("fm_title"),
                "appdesc": get_text_value("fm_appdesc"),
                "mobile_app_image": get_value("mobile_app_image")
            };
            //print_r(params);
            //return;

            var RegUrl = new RegExp(); 
            RegUrl.compile("^[A-Za-z]+://[A-Za-z0-9-_]+\\.[A-Za-z0-9-_%&\?\/.=]+$");
            if (!RegUrl.test(params.ios_url)) { 
                alert("ios_url不符合url格式规范");
                return;
            }
            RegUrl.compile("['\"]");
            if (RegUrl.test(params.ios_url)) {
                alert("ios_url不可以带特殊字符 ' 和 \"");
                return;
            }
            if (RegUrl.test(params.appdesc)) {
                alert("应用介绍不可以带特殊字符 ' 和 \"");
                return;
            }
            if (RegUrl.test(params.title)) {
                alert("推广页标题不可以带特殊字符 ' 和 \"");
                return;
            }
            var max_len = 100;
            if (params.appdesc.length>max_len) {
                alert("应用介绍不得超过"+max_len+"个字!");
                return;
            }
            max_len = 28;
            if (params.title.length>max_len) {
                alert("页面标题字段不得超过"+max_len+"个字!");
                return;
            }

            var thiso = this;
            $.ajax({
                type: "post",
                async: false,
                url: _bigapp_obj.ajax_url,
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
					print_r(data); return;
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
