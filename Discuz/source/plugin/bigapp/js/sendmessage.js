function GetDatas(url) {
    jq("#send_label").html("正在发送...");
    try {
        jq.ajaxSetup({
            error: function (x, e) {
                jq("#send_label").html("发送测试信息失败，服务器未正确响应");
                return false;
            }
        });
        jq.ajax({
            type:'GET',
            url: url,
            dataType : 'json',
            contentType : 'text/html; charset=UTF-8',
            success: function (data) {
                if (data == null) {
                    jq("#send_label").html("发送测试信息失败，服务器未正确响应");
                    return false;
                }
                if (!data.hasOwnProperty('error_code')) {
                    jq("#send_label").html("发送测试信息失败，服务器未正确响应");
                    return false;
                }
                if (data.hasOwnProperty('show_tips')) {
                    showTips = data.show_tips;
                } else {
                    if (0 == data.error_code) {
                        showTips = '操作成功';
                    } else {
                        showTips = '操作失败，请稍后重试';
                    }
                }
                jq("#send_label").html(showTips);
            }
        });
    }
    catch (ex) {
        jq("#send_label").html("发送测试信息失败，服务器未正确响应");
    }
}
