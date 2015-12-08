/**
 * 消息推送页面JS
 **/

var jq = jQuery.noConflict();
var UZ;
(function ($) {
    function enable_dom(sel){$(sel).removeAttr("disabled");}
    function disable_dom(sel){$(sel).attr("disabled","disabled");}

    UZ = function () {
        var store;
        var pagebar;
		function show_table()
		{
            var code = "";
            var reg=new RegExp("","g");
			for(var i=0; i<store.size(); ++i) {
			  var im  = store.get(i);
              var msg = im.msg.replace(/\n/g, "<br>");
			  code += "<tr><td>"+im.msgtitle+"</td><td>"+msg+"</td><td>"+im.sendtime+"</td></tr>";
			}
			document.getElementById("release-versions-tbody").innerHTML = code;            
		}

        this.init = function() {
            if (v.groupid!=1) {
                var errmsg="<h1 style='color:red;font-size:16px;margin-top:20px;'>很抱歉，只有管理员才能推送消息！</h1>";
                $("#main-body").html(errmsg);
                return;
            }
            if (v.appid==0) {
                $("#main-body").html("<h1 style='color:red;font-size:16px;margin-top:20px;'>请先通过站长认证！</h1>");
                return;
            }
            var thiso=this;
            $("#subbtn").click(function(){
                thiso.submit();
            });

			store = new MWT.Store({
                "url": v.api+"&action=query",
            });
            pagebar = new MWT.Pagebar({
                "store"  : store,
                "render" : "pagebar-div"
            });
            store.on("load", show_table);
            pagebar.changePage(1);          
        };

        this.submit = function() {
            var params = {
                alias_type: 0,
                alias: "",
                title: get_text_value("msg-title"),
                msg: get_text_value("msg-txt")
            };
            if (params.title.length>30) {
                alert("消息标题不能超过30个字符");
                return;
            }
            if (params.msg.length>100) {
                alert("消息内容不能超过100个字符");
                return;
            }
            //print_r(params);
            var thiso = this;
            disable_dom("#subbtn");
            $.ajax({
                type: "post",
                async: false,
                url: v.api+"&action=submit",
                data: params,
                dataType: "json",
                complete: function(res) {
                    enable_dom("#subbtn");
                },
                success: function (res) {
                    if (res.error_code==0) {
						window.location.reload(); return;
					} else {
                        alert("发送失败："+res.show_tips);
					}
                },
                error: function (data) {
                    alert("发送失败：服务器异常");
                }
            });
        };
    };

    $(function () {
        var app = new UZ();
        app.init();
    })

})(jq);
