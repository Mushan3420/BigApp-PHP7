<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title></title>
  <link rel="stylesheet" href="<%plugin_path%>/view/css/mwt.css"/>
  <script src="<%plugin_path%>/view/js/jquery.js"></script>
  <script src="<%plugin_path%>/view/js/qrcode.js"></script>
  <script src="<%plugin_path%>/view/js/mwt.js" charset="utf-8"></script>
  <%js_script%>
  <script>
  function IsURL(str_url){
      var strRegex = "^(https|http)://";
	  var re=new RegExp(strRegex,"i");
	  return re.test(str_url);
  }

  function genqrcode(domid, url) {
      var qrcode = new QRCode(document.getElementById(domid), {
          width  : 150,
          height : 150
      });
	  qrcode.makeCode(url);
  }

  $(document).ready(function(){ 
    $("#genbtn").click(function(){
        $("#gen-res-url").html("");
        $("#gen-res-qrcode").html("");

        var loginurl = $("#lurl").attr("href");
        var from = get_text_value("jumpurl");
        if (!IsURL(from)) {
            alert("请输入以http开头的url");
            $("#lurl").focus();
            return;
        }
        loginurl += "?refer="+encodeURIComponent(from);
        var code = "<a href='"+loginurl+"' target='_blank' style='font-size:14px;'>"+loginurl+"</a>";
        $("#gen-res-url").html(code);
        genqrcode("gen-res-qrcode",loginurl);
    });
    var loginurl = $("#lurl").attr("href");
    genqrcode("lurl-qr",loginurl);
  });
  </script>
</head>
<body id="BS">
  <table class="tb tb2">
    <tr><th colspan="15" class="partition">使用提示</th></tr>
    <tr><td class="tipsblock" s="1">
      <ul id="lis">
        <li>如果你的移动端页面使用Discuz原生的登录地址，只需 <a href='admin.php?frames=yes&action=plugins&operation=config&identifier=login_mobile&pmod=z_setting' target='_blank'>开启移动端设置</a> 即可自动跳转手机登录和注册页面。</li>
        <li>如果你想使用独立链接来使用手机登录，请关注以下各页面地址，并在你页面需要跳转的地方，使用对应的链接。</li>
        <li>特别地，如果你正在使用<a href='http://addon.discuz.com/?@bigapp.plugin'>BigApp插件</a>，你可以在 <a href='admin.php?frames=yes&action=plugins&operation=config&identifier=bigapp&pmod=account' target='_blank'>登录注册设置</a> 中使用Web登录，并复制下面的登录地址和注册地址。</li>
      </ul>
    </td></tr>
  </table>

  <table class="tb tb2">
    <tr><th colspan="2" class="partition">移动端各页面地址（建议在移动端打开）</th></tr>
    <tr>
      <td width='70'>登录页面：</td>
      <td><a href='<%plugin_path%>/fe/login.html' target='_blank'><%plugin_path%>/fe/login.html</a></td>
<!--
      <td>
        <a id='lurl' href='<%login_url%>' target='_blank' style='font-size:14px;'><%login_url%></a><br>
        <p style='color:red;margin:5px 0;'>（提示：建议在移动端打开此链接，或用手机扫描以下二维码打开）</p>
        <div id='lurl-qr'></div>
      </td>
-->
    </tr>
    <tr>
      <td>注册页面：</td>
      <td><a href='<%plugin_path%>/fe/regist.html' target='_blank'><%plugin_path%>/fe/regist.html</a></td>
    </tr>
    <tr>
      <td>找回密码：</td>
      <td><a href='<%plugin_path%>/fe/findpass.html' target='_blank'><%plugin_path%>/fe/findpass.html</a></td>
    </tr>
    <tr>
      <td>绑定手机：</td>
      <td><a href='<%plugin_path%>/fe/bind.html' target='_blank'><%plugin_path%>/fe/bind.html</a></td>
    </tr>
    <tr><td colspan='2' style='color:red;'>
      （提示：以上各地址，可以通过加参数?refer=url来指定操作成功后的跳转地址）
    </td></tr>
  </table>
  
<!--
  <table class="tb tb2">
    <tr><th colspan="3" class="partition">生成带跳转地址的登录入口</th><td></td></tr>
    <tr>
      <td width="137">登录成功后的跳转地址：</td>
      <td width="250"><input id='jumpurl' type='text' class='form-control'/></td>
      <td><button id='genbtn' class='mwt-btn mwt-btn-default mwt-btn-sm'>生成</button></td>
    </tr>
  </table>
  <div id='gen-res-url' style='margin:10px 0;'></div>
  <div id='gen-res-qrcode'></div>
-->
</body>
</html>
