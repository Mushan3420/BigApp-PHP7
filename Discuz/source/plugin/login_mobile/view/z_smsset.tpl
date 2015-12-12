<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title></title>
  <link rel="stylesheet" href="<%plugin_path%>/view/css/mwt.css"/>
  <script src="<%plugin_path%>/view/js/jquery.js"></script>
  <script src="<%plugin_path%>/view/js/mwt.js" charset="utf-8"></script>
  <script src="<%plugin_path%>/view/js/plugin/smsset.js" charset="utf-8"></script>
  <%js_script%>
  <script>
    function check()
    {
        try {
		    var params = {
				"username": get_text_value("fm-username"),
				"password": get_text_value("fm-password"),
                "template1": get_text_value("fm-template1"),
                "template2": get_text_value("fm-template2"),
			};
            set_value("fm-username", params.username);
            set_value("fm-password", params.password);
            set_value("fm-template1", params.template1);
            set_value("fm-template2", params.template2);
            return true;
        } catch (e) {
            console.log("Exception: "+e.message);
			return false;
        }
    }
  </script>
</head>
<body id="BS">
  <table class="tb tb2">
    <tr><th colspan="15" class="partition">使用提示</th></tr>
    <tr><td class="tipsblock" s="1">
      <ul style='margin-bottom:5px;'>
        <li>说明：短信平台设置是为了发送短信验证码使用。</li>
      </ul>
      <ul id="infoul" style='margin:5px 0;'>
        <li>当前我们已经集成了多个短信平台的客户端，您可以自行选择其一作为您的短信发送平台。</li>
      </ul>
      <ul>
        <li>如果您已经申请了其他短信平台的账号，请联系我们（QQ: 492108207）为您集成短信客户端。</li>
      </ul>
    </td></tr>
  </table>

  <form method="post" action="admin.php?action=plugins&operation=config&identifier=login_mobile&pmod=z_smsset" onsubmit="return check();">
  <table class="tb tb2">
    <tr><th colspan="15" class="partition">短信平台设置</th></tr>
    <tr>
      <td width='80'>短信平台：</td>
      <td width='300'>
          <select id='fm-smsid' name='smsid' class='form-control'></select>
      </td>
      <td class='tips2' id='smsid-desc'>选择您申请的短信平台</td>
    </tr>
    <tr>
      <td>APP_ID：</td>
      <td>
          <input type='text' id='fm-username' name='username' class='form-control'/>
      </td>
      <td class='tips2'>输入您在短信平台申请的APP_ID（账号）</td>
    </tr>
    <tr>
      <td>APP_KEY：</td>
      <td>
          <input type='text' id='fm-password' name='password' class='form-control'/>
      </td>
      <td class='tips2'>输入您在短信平台申请的APP_KEY（密码）</td>
    </tr>
  </table>

  <table class="tb tb2">
    <tr><th colspan="15" class="partition">短信模板设置</th></tr>
    <tr>
      <td width='80'>测试短信：</td>
      <td width='300'>
          <input type='text' id='fm-template1' name='template1' class='form-control' value='这是一条测试短信，请忽略。'/>
      </td>
      <td class='tips2'>发送测试短信时，将使用此短信模板。</td>
    </tr>
    <tr>
      <td>验证码短信：</td>
      <td>
          <input type='text' id='fm-template2' name='template2' class='form-control' value='您的验证码是：【变量】。'/>
      </td>
      <td class='tips2' id='smsid-desc'>发送验证码时，将使用此短信模板，其中【变量】将替换成随机生成的验证码。</td>
    </tr>
    <tr>
      <td colspan="3">
        <input type="submit" id='subbtn' class='mwt-btn mwt-btn-default mwt-btn-xs' style='padding:2px 8px;' value="提交"/>
      </td>
    </tr>
  </table>
  </form>

  

  <table class="tb tb2">
    <tr><th colspan="15" class="partition">测试短信</th></tr>
    <tr>
      <td width='70'>手机号：</td>
      <td width='200'>
          <input type='text' id='fm-phone' class='form-control'/>
      </td>
      <td class='tips2'>输入接收测试短信的手机号</td>
    </tr>
    <tr>
      <td colspan="3">
        <input type="button" id='testbtn' class='mwt-btn mwt-btn-default mwt-btn-xs' style='padding:2px 8px;' value="发送测试短信"/>
        &nbsp;
        <div id='resmsg' style='display:inline;'></div>
      </td>
    </tr>
  </table>
  
</body>
</html>
