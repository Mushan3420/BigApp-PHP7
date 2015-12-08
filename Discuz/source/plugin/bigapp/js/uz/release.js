/*
 * 发布管理页面JS
 */

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
              var os  = im.os==1 ? "Android" : "IOS";
              var msg = im.releasemsg.replace(/\n/g, "<br>");
			  code += "<tr><td>"+os+"</td><td>"+im.version+"</td><td>"+im.releasetime+"</td><td>"+msg+"</td></tr>";
			}
			document.getElementById("release-versions-tbody").innerHTML = code;            
		}

        function disable_input() {
            disable_dom("#newversion-txt");
            disable_dom("#releasemsg-txt");
            disable_dom("[name='upgrademode']");
            disable_dom("#subbtn");
        };
        function enable_input() {
			enable_dom("#newversion-txt");
			enable_dom("#releasemsg-txt");
			enable_dom("[name='upgrademode']");
			enable_dom("#subbtn");
        };

        this.init = function() {
            disable_input();
            $("#btnmsg").html("");
            var thiso = this;
            // gen qrcode
            $.ajax({
                type: "get",
                async: false,
                url: v.mid_page,
                data: {"url":v.latest_package},
                dataType: "json",
                success: function(res) {
                    if (res.error_msg=='auth failed') {
                        alert("您没有权限管理此页面");
                        return;
                    }
                    else if (res.error_msg!='SUCC') {
                        alert(res.error_msg);
                        return;
                    }
                    if (!res.data || !res.data.midurl) return;
                    var midurl = res.data.midurl;
		    var qrcode = new QRCode(document.getElementById("qrcode"), {
                        width  : 150,
                        height : 150
                    });
		    qrcode.makeCode(midurl);
		    var code = "<a style='color:black;text-decoration:underline;' href='"+midurl+"'>扫描下载最新安装包</a>";
		    $("#qrcode-comment").html(code);
                },
                error: function(data) {
					alert("get_midpage: 数据读取失败");
                }
            });
            

            // get latest verison
            $.ajax({
                type: "get",
                async: false,
                url: v.latest_version,
                dataType: "json",
                success: function (res) {
                    if (isset(res.data)) {
                        set_value("curversion-txt", res.data.online_version);
                        if (res.data.prepare_version != "" && res.data.channel_name == 'bigapp' && res.data.compare!=0) {
                            ///////////////////////////////////////////////////////
                            set_value("newversion-txt", res.data.prepare_version);
                            set_value("taskid", res.data.taskid);
                            set_value("downlink", res.data.prepare_pkg);
                            ///////////////////////////////////////////////////////
                            if (res.data.compare < 0) {
							    $("#btnmsg").html("发布版本号小于当前版本号");
                            } else {
                                enable_input();
						        $("#btnmsg").html("");
                                $("#subbtn").click(function(){thiso.submit();});
                            }
                        } else {
						    $("#btnmsg").html("自建渠道最新版本已发布");
                        }
                    }
                },
                error: function (data) {
                    alert("latest_version: 数据读取失败");
                }
            });

            // release version grid
            store = new MWT.Store({
                "url": v.release_versions,
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
                "version": get_value("newversion-txt"),
                "releasemsg": get_text_value("releasemsg-txt"),
                "upgrademode": get_radio_value("upgrademode"),
                "taskid": get_value("taskid"),
                "downlink": get_value("downlink"),
                "os": 1
            };
            var max_releasemsg_len = 256;
            if (params.releasemsg.length>max_releasemsg_len) {
                alert("更新内容不得超过"+max_releasemsg_len+"个字!");
                return;
            }
            var thiso = this;
            disable_input();
            $.ajax({
                type: "get",
                async: false,
                url: v.release,
                data: params,
                dataType: "json",
                success: function (res) {
                    if (isset(res.data) && res.data.retstat==0) {
					    alert("发布成功");
						window.location.reload(); return;
					} else {
                        enable_input();
                        alert("发布失败: "+res.data.retmsg);
					}
                },
                error: function (data) {
                    enable_input();
                    alert("发布失败");
                }
            });
        };
    };

    $(function () {
        var app = new UZ();
        app.init();
    })

})(jq);
