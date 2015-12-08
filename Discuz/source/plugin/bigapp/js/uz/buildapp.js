/*
 * 配置APP页面JS
 */
var jq = jQuery.noConflict();
var UZ;
(function ($) {
    function enable_dom(sel){$(sel).removeAttr("disabled");}
    function disable_dom(sel){$(sel).attr("disabled","disabled");}

    UZ = function () {
        this.init = function() {
/*
            var thiso = this;
            $.ajax({
                type: 'get',
                async: false,
                url: v.new_versions,
                dataType: "json", 
                success: function (res) { 
                    if (typeof(res.data) != "undefined") {
                        var vs = res.data.new_versions;
                        var code = "";
                        for (var i=0; i<vs.length; ++i) {
                            code += "<option value='"+vs[i]+"'>"+vs[i]+"</option>";
                        }
                        $("#app_version").html(code);
                    }
                }, 
                error: function (data) {
                    alert("new_versions: 数据读取失败");
                }
            });
*/
        };
    };

    $(function () {
        var app = new UZ();
        app.init();
    })

})(jq);

