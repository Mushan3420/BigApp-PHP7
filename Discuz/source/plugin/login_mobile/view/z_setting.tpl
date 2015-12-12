<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title></title>
  <link rel="stylesheet" href="<%plugin_path%>/view/css/mwt.css"/>
  <script src="<%plugin_path%>/view/js/jquery.js"></script>
  <script src="<%plugin_path%>/view/js/mwt.js" charset="utf-8"></script>
  <%js_script%>
  <script>
    var jq=jQuery.noConflict();
    jq(document).ready(function($) {
        set_radio_value("enable",v.enable);
        set_radio_value("enable_mobile",v.enable_mobile);
    });
  </script>
  <style>
    .ul2 {margin:5px 0;}
    .ul2 li {padding:0 20px;margin:0;}
    .ul2 li:last-child {margin-bottom:10px;}
  </style>
</head>
<body>
  <table class="tb tb2">
    <tr><th colspan="15" class="partition">使用提示</th></tr>
    <tr><td class="tipsblock" s="1">
      <ul style='margin:0;'>
        <li>本插件需要发送验证码短信，请先设置 <a href='admin.php?frames=yes&action=plugins&operation=config&identifier=login_mobile&pmod=z_smsset' target='_blank'>短信平台</a> 并保证短信发送成功。</li>
        <li>开启PC端功能，将使PC端增加以下功能：
          <ul class='ul2'>
            <li>新用户注册时需要通过短信验证码校验。</li>
            <li>老用户可以进行手机绑定。</li>
            <li>绑定手机的用户可以使用手机号码作为用户名登录。</li>
          </ul>
        </li>
        <li>开启移动端功能，将使移动端增加以下功能：
          <ul class='ul2'>
            <li>登录页面跳转到本插件提供的手机登录页面</li>
            <li>注册页面跳转到本插件提供的手机注册页面</li>
          </ul>
        </li>
        <li>如果您在使用过程中遇到任何问题，请随时与我们联系，<b>QQ: 492108207</b></li>
      </ul>
    </td></tr>
  </table>

  <form method="post" action="admin.php?action=plugins&operation=config&identifier=login_mobile&pmod=z_setting">
  <table class="tb tb2">
    <tr><th colspan="15" class="partition">设置</th></tr>
    <tr>
      <td width='80'>开启PC端：</td>
      <td width='200'>
        <label><input type='radio' name='enable' value='1'> 是</label>&nbsp;&nbsp;
        <label><input type='radio' name='enable' value='0'> 否</label>
      </td>
      <td class='tips2' id='smsid-desc'></td>
    </tr>
    <tr>
      <td>开启移动端：</td>
      <td>
        <label><input type='radio' name='enable_mobile' value='1'> 是</label>&nbsp;&nbsp;
        <label><input type='radio' name='enable_mobile' value='0'> 否</label>
      </td>
      <td class='tips2' id='smsid-desc'></td>
    </tr>
    <tr>
      <td colspan="3">
        <input type="submit" id='subbtn' class='mwt-btn mwt-btn-default mwt-btn-xs' style='padding:2px 8px;' value="提交"/>
      </td>
    </tr>
  </table>
  </form>
 
</body>
</html>
