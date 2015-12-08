<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <link rel="stylesheet" href="<%plugin_path%>/css/mwt.css"/>
    <link rel="stylesheet" href="<%plugin_path%>/css/uz-style.css"/>
    <script src="<%plugin_path%>/js/jquery.js"></script>
    <script src="<%plugin_path%>/js/qr.js"></script>
    <script src="<%plugin_path%>/js/lib/mwt.js" charset="utf-8"></script>
    <script src="<%plugin_path%>/js/uz/release.js" charset="utf-8"></script>
	<%js_script%>
</head>
<body id="uz">
  <table class="tb tb2">
    <tr><th colspan="15" class="partition">发布管理</th></tr>
  </table>
<table>
<tr>
<td>
  <table class="tb tb2">
    <tr>
      <td width='60'>当前版本：</td>
      <td width='300'><input id='curversion-txt' readonly class='txt' style="border:none;"/></td>
      <td class='tips2'>当前已发布的最新版本。</td>
    </tr>
    <tr>
      <td>发布版本：</td>
      <td>
        <input id='newversion-txt' readonly class='txt' style="border:none;"/>
        <input id='taskid' type='hidden'/>
        <input id='downlink' type='hidden'/>
      </td>
      <td class='tips2'>当前可以发布的新版本号。</td>
    </tr>
    <tr>
      <td valign='top'>更新内容：</td>
      <td><textarea id='releasemsg-txt' style='width:300px;height:80px;'></textarea></td>
      <td class='tips2'>新版本的更新内容，该信息会提示给用户。</td>
    </tr>
    <tr>
      <td>更新方式：</td>
      <td>
        <label><input type='radio' class='radio' name='upgrademode' value='0' checked>建议更新</label>
        <label><input type='radio' class='radio' name='upgrademode' value='1'>强制更新</label>
      </td>
      <td class="vtop tips2">
        选择强制更新时，用户必须升级到最新版本才能继续使用APP。
      </td>
    </tr>
    <tr>
      <td colspan="1">
        <input type="button" id='subbtn' class='mwt-btn mwt-btn-default mwt-btn-xs' style='padding:2px 8px;' value="提交"/>
      </td>
      <td colspan='2' style="color:red;font-weight:bold" id="btnmsg"></td>
    </tr>
  </table>
</td>
<td style='text-align:center;vertical-align:top;padding-left:50px;padding-top:10px;'>
  <div id="qrcode"></div>
  <div id="qrcode-comment" style='padding-top:10px;'></div>
</td>
</tr>
</table>
  <table class="tb tb2">
    <tr><th colspan="15" class="partition">已发布版本</th></tr>
  </table>
  <table class='table' style='margin:5px 0 5px 0;'>
    <thead><tr>
      <td width='80'>平台</td>
      <td width='110'>发布版本</td>
      <td width='150'>发布时间</td>
      <td>更新内容</td>
    </tr></thead>
    <tbody id='release-versions-tbody'>
    </tbody>
  </table>
  <div id="pagebar-div"></div>
</body>
</html>
