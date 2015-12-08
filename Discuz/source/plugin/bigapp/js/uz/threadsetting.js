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
			
			//thiso.getCustomData();
        };
		
		this.getCustomData = function(){
            $.ajax({
                type: "get",
                async: false,
                url: v.getThreadSetting,
                dataType: "json",
                success: function (res) {
					
					if (res.error_code==0) {
						//console.log(res);
						//initial page setting
						//console.log(res.data.enable_hot);
						if(res.data.enable_new == 'true') {
							$("#enable_new").attr("checked", true);
						} else {
							$("#enable_new").attr("checked", false);
						}
						
						$("#sort_new").attr("value", res.data.sort_new);
						$("#title_new").attr("value", res.data.title_new);
						
						if(res.data.enable_hot == 'true') {
							$("#enable_hot").attr("checked", true);
						} else {
							$("#enable_hot").attr("checked", false);
						}
						
						$("#sort_hot").attr("value", res.data.sort_hot);
						$("#title_hot").attr("value", res.data.title_hot);
						
						if(res.data.enable_fav == 'true') {
							$("#enable_fav").attr("checked", true);
						} else {
							$("#enable_fav").attr("checked", false);
						}
						
						$("#sort_fav").attr("value", res.data.sort_fav);
						$("#title_fav").attr("value", res.data.title_fav);
					} else {
						if (res.error_code==100803) {
							alert("您没有权限管理此页面");
						} if (res.error_code==100807) {
							alert("拉取设置失败");
						}
					}

                },
                error: function (data) {
                    alert("拉取设置失败");
                }
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
                "enable_new": $("#enable_new").attr("checked")=='checked',
                "sort_new": $("#sort_new").val(),
				"title_new": $("#title_new").val(),
				"enable_hot": $("#enable_hot").attr("checked")=='checked',
                "sort_hot": $("#sort_hot").val(),
				"title_hot": $("#title_hot").val(),
				"enable_fav": $("#enable_fav").attr("checked")=='checked',
                "sort_fav": $("#sort_fav").val(),
				"title_fav": $("#title_fav").val(),
				"portal":partal
            };
            console.log(params);
			
            var RegUrl = new RegExp(); 
            RegUrl.compile("^[0-9]+$");
            if (params.enable_new == true) { 
				if(!RegUrl.test(params.sort_new)) { 
					alert("输入错误,请输入数字");
					return;
				} else if(eval(params.sort_new) > 10 || eval(params.sort_new) <= 0){
					alert("请输入数字（1-10）");
					return;
				}
			}
            if (params.enable_hot == true) {
				if(!RegUrl.test(params.sort_hot)) { 
					alert("输入错误,请输入数字");
					return;
				} else if(eval(params.sort_hot) > 10 || eval(params.sort_hot) <= 0){
					alert("请输入数字（1-10）");
					return;
				}
			}
			
            if (params.enable_fav == true && !RegUrl.test(params.sort_fav)) { 
                if(!RegUrl.test(params.sort_fav)) { 
					alert("输入错误,请输入数字");
					return;
				} else if(eval(params.sort_fav) > 10 || eval(params.sort_fav) <= 0){
					alert("请输入数字（1-10）");
					return;
				}
            }
			
			if(params.enable_new == true && params.enable_hot == true) {
				if(eval(params.sort_new) == eval(params.sort_hot)) {
					alert("排序值需设定不同值");
					return;
				}
			}
			
			if(params.enable_new == true && params.enable_fav == true) {
				if(eval(params.sort_new) == eval(params.sort_fav)) {
					alert("排序值需设定不同值");
					return;
				}
			}
			
			if(params.enable_hot == true && params.enable_fav == true) {
				if(eval(params.sort_hot) == eval(params.sort_fav)) {
					alert("排序值需设定不同值");
					return;
				}
			}
			
			var number = text = true;
			$(".myportal").each(function(){
                if(!RegUrl.test($(this).find(".myportal_sort").val())) { 
					number = false;
					return;
				}
				if($(this).find(".myportal_title").val().length > 10){
					text = false;
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

            var thiso = this;
            $.ajax({
                type: "post",
                async: false,
                url: v.setThreadSetting,
                data: params,
                dataType: "json",
                success: function (res) {
                    if (res.error_code==0) {
						console.log(res);
					    alert("设置已更改");
						//initial page setting
						console.log(res.data.enable_new);
						if(res.data.enable_new == 'true') {
							$("#enable_new").attr("checked", true);
						} else {
							$("#enable_new").attr("checked", false);
						}
						
						$("#sort_new").attr("value", res.data.sort_new);
						
						if(res.data.enable_hot == 'true') {
							$("#enable_hot").attr("checked", true);
						} else {
							$("#enable_hot").attr("checked", false);
						}
						
						$("#sort_hot").attr("value", res.data.sort_hot);
						
						if(res.data.enable_fav == 'true') {
							$("#enable_fav").attr("checked", true);
						} else {
							$("#enable_fav").attr("checked", false);
						}
						
						$("#sort_fav").attr("value", res.data.sort_fav);
						
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
