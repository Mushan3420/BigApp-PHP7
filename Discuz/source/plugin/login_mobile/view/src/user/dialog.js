define(function(require){
    var ajax=require("ajax");
    var dialog,skugrid,o={};
    var uid=0;

    o.init_content = function(domid, cateOptions) {
        var code = "<div style='padding:5px;'>"+
            "<table class='tablay'>"+
            "<tr><td width='90'>绑定手机号：</td><td><input id='fm-phone' type='text' class='form-control'/></td></tr>"+
            "</table></td></tr></table></div>";
        $("#"+domid).html(code);
    };

    o.init = function(cateOptions){
        var thiso = this;
        var domid = "dialog-div";
        thiso.init_content(domid, cateOptions);
        dialog = new MWT.Dialog({
            render : domid,
            title  : '绑定手机号',
            width  : 400,
            height : "auto",
            top    : 50, 
            buttons: [
                {"label":"提交",cls:'mwt-btn-primary',handler:thiso.submit},
                {"label":"取消",type:'close',cls:'mwt-btn-danger'}
            ]   
        }); 
        dialog.create();
    };  

    o.reset = function() {
        uid = 0;
		set_value("fm-phone", "");
    }

    o.open = function(buid){
        dialog.open();
        this.reset();
        uid=buid;
    };

    function is_phone_number(a)
	{
		if(!(/^1[1|2|3|4|5|6|7|8|9][0-9]\d{8}$/.test(a))){
			return false;
		}
		return true;
	}

    o.submit = function() {
        var item = {
            uid: uid,
			phone: get_text_value("fm-phone"),
        };
        if (!is_phone_number(item.phone)) {
            alert("请输入正确的手机号码");
            $("#fm-phone").val("");
            $("#fm-phone").focus();
            return;
        }
        ajax.post("action=bind",item,function(res){
            dialog.close();
            require("./grid").query();
        });
    };

    return o;
});
