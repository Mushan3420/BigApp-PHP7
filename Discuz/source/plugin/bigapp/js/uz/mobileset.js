/*
 * 发布管理页面JS
 */

var jq = jQuery.noConflict();
var UZ;
(function ($) {

    UZ = function () {

	    function set_moburl_switch(value) {
            set_radio_value("moburl_switch",value);
            if (value==2) {
                $("#moburl_txt").show();
            } else {
                $("#moburl_txt").hide();
            }
        };


        this.init = function() {
            var thiso = this;
            $("#openbtn").click(function(){
                window.open(v.mobileurl);
            });
            $("#subbtn").click(function(){
				thiso.submit();
            });
            set_value("fm_ios_url",v.iosurl);
            set_value("fm_appdesc",v.appdesc);

            ///////////////////////////////////

            set_moburl_switch(v.moburl_switch);
            set_value("moburl_txt", v.moburl);
            
            $("[name='moburl_switch']").change(function(){
                var v = $("[name='moburl_switch']:checked").val();
                //alert(v);
                set_moburl_switch(v);
            });

            $("#subbtn2").click(function(){
				thiso.submit2();
            });
        };

        this.submit = function() {
            var params = {
                "ios_url": get_text_value("fm_ios_url"),
                //"download_title": get_text_value("fm_download_title"),
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

        this.submit2 = function() {
            var params = {
				"moburl_switch": get_radio_value("moburl_switch"),
				"moburl": ""
            };
			if (params.moburl_switch==2) {
				params.moburl = get_text_value("moburl_txt");
			}
            if (params.moburl_switch==1) {
                if (v.iosurl=="" || $("#fm_ios_url").val()=="") {
                    alert("请先设置推广页，并提交");
                    return;
                }
            }
            //print_r(params);
            $.ajax({
                type: "post",
                async: false,
                url: v.ajaxurl2,
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
