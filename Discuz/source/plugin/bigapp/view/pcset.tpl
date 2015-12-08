<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <link rel="stylesheet" href="<%plugin_path%>/css/mwt.css"/>
    <link rel="stylesheet" href="<%plugin_path%>/css/uz-style.css"/>
    <script src="<%plugin_path%>/js/jquery.js"></script>
    <script src="<%plugin_path%>/js/lib/mwt.js" charset="utf-8"></script>
    <script src="<%plugin_path%>/js/uz/pcset.js" charset="utf-8"></script>
	<%js_script%>
    <script>
        function check()
        {
            try {
				var params = {
					"moburl_switch": get_radio_value("moburl_switch"),
					"moburl": ""
				};
				if (params.moburl_switch==2) {
					params.moburl = get_text_value("moburl_txt");
				}
                return true;
            } catch (e) {
                console.log("Exception: "+e.message);
				return false;
            }
        }
    </script>
</head>
<body id="uz">
  <form method="post" action="admin.php?action=plugins&operation=config&identifier=bigapp&pmod=pcset" onsubmit="return check();">
  <table class="tb tb2">
    <tr><th colspan="15" class="partition">PC端配置</th></tr>
    <tr><td colspan='3' style='font-weight:bold;color:red;'>
      注意：本页面上的配置只影响PC端，对移动应用无效。
    </td></tr>
    <tr>
      <td width='80' valign='top' style='font-weight:bold;'>手机版地址：</td>
      <td width='200' style='line-height:25px;'>
          <label><input type='radio' name='moburl_switch' value='0'/> 不设置</label><br>
          <label><input type='radio' name='moburl_switch' value='1'/> 使用推广页地址</label><br>
          <label><input type='radio' name='moburl_switch' value='2'/> 自定义地址</label><br>
          <input id='moburl_txt' name='moburl_txt' type='text' value='' style='display:none;margin-left:20px;'/>
      </td>
      <td class='tips2' valign='top'>不设置将使用Discuz原生的地址<br>使用推广页地址，请确保已设置过推广页<br>自定义地址请填写合法的url，如 http://www.youzu.com/</td>
    </tr>
    <tr>
      <td colspan="3">
        <input type="submit" id='subbtn' class='mwt-btn mwt-btn-default mwt-btn-xs' style='padding:2px 8px;' value="提交"/>
      </td>
    </tr>
  </table>

</body>
</html>
