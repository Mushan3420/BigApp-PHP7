<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <link rel="stylesheet" href="<%plugin_path%>/css/mwt.css"/>
    <link rel="stylesheet" href="<%plugin_path%>/css/uz-style.css"/>
    <script src="<%plugin_path%>/js/jquery.js"></script>
    <script src="<%plugin_path%>/js/ajaxfileupload.js"></script>
    <script src="<%plugin_path%>/js/uploadfile.js"></script>
    <script src="<%plugin_path%>/js/lib/mwt.js" charset="utf-8"></script>
    <script src="<%plugin_path%>/js/uz/appcfg.js" charset="utf-8"></script>
	<%js_script%>
</head>
<body id="uz">
  <table class="tb tb2">
    <tr><th colspan="15" class="partition">APP热配置
        <span style='color:red;'>（提示：APP热配置提交后会立即生效，不用重新打包APP）</span></tr>
  </table>

  <table class="tb tb2">
    <tr>
      <td width='80'><b>第三方登录：</b></td>
      <td width='300'>
        <label><input id='fm_qq_login' class='chkbox' type='checkbox'/>QQ登录<span id='qq_login_label' style='color:red;'></span></label><br>
        <label><input id='fm_wechat_login' class='chkbox' type='checkbox'/>微信登录</label><br>
        <label style='display:none;'><input id='fm_weibo_login' class='chkbox' type='checkbox'/>新浪微博登录</label><br>
      </td>
      <td class='tips2'>
        QQ登录需要启用QQ互联插件，请确保PC端的QQ互联能正常使用。<br>
        微信登录与PC端的微信登录是两套登录体系，不需要PC端开启微信登录。<br>
      </td>
    </tr>
    <tr>
      <td colspan="3">
        <input type="button" id='subbtn' class='mwt-btn mwt-btn-default mwt-btn-xs' style='padding:2px 8px;' value="提交"/>
      </td>
    </tr>
  </table>

<style>
  .chkbox {margin:5px;}
</style>


</body>
</html>
