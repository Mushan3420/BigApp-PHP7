/*
 * 推荐帖子设置JS
 */

var jq = jQuery.noConflict();
var UZ;
(function ($) {

    UZ = function () {

        this.init = function() {
            var thiso = this;
           
            $("#subbtn").click(function(){
				thiso.submit();
            });

        };

        this.submit = function() {
			var partal=[];
			$(".myportal").each(function(){ 
					partal.push({ 
									"id":$(this).find(".myportal_id").val(),
									"sort":$(this).find(".myportal_sort").val(),
									"title":$(this).find(".myportal_title").val(),
									"enable":$(this).find(".myportal_enable").attr("checked")=='checked'?1:0,
								});
			});
            var params = {
				"portal":partal
            };
            console.log(params);
			var RegUrl = new RegExp(); 
            RegUrl.compile("^[0-9]+$");
			var number = text = sort = true;
			$(".myportal").each(function(index,element){
                if(!RegUrl.test($(this).find(".myportal_sort").val())) { 
					number = false;
					return;
				}
				if($(this).find(".myportal_title").val().length > 10 && $(this).find(".myportal_enable").attr("checked")=='checked'){
					text = false;
					return;
				}
				if(index > 0 && $(this).find(".myportal_sort").val() < 2 && $(this).find(".myportal_enable").attr("checked")=='checked' ){
					sort = false;
					return;
				}
			});
			if(!number){
				alert("排序值必须为数字");
				return;
			}
			if(!text){
				alert("显示名称长度过长");
				return;
			}
			if(!sort){
				alert("排序值必须大于等于2");
				return;
			}
            var thiso = this;
            $.ajax({
                type: "post",
                async: false,
                url: v.setPortalSetting,
                data: params,
                dataType: "json",
                success: function (res) {
                    if (res.error_code==0) {
						console.log(res);
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
