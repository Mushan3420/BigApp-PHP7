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
    <script src="<%plugin_path%>/js/uz/pushmsg.js" charset="utf-8"></script>
	<%js_script%>
</head>
<body id="uz">
  <table class="tb tb2">
    <tr><th class="partition">消息推送提示</th></tr>
    <tr><td style='color:red;'>请确保您打包生成的APP中已开启推送功能</td></tr>
  </table>
<div id="main-body">
  <table class="tb tb2">
    <tr><th colspan='3' class="partition">消息推送</th></tr>
    <tr style='display:none;'>
      <td width='80'><b>推送对象：</b></td>
      <td width='300'><input id='msg-uids'/></td>
      <td class='tips2'></td>
    </tr>
    <tr>
      <td width='80'><b>消息标题：</b></td>
      <td width='300'><input type='text' id='msg-title' class='form-control'/></td>
      <td class='tips2'>不超过30个字符</td>
    </tr>
    <tr>
      <td valign='top'><b>消息内容：</b></td>
      <td><textarea id='msg-txt' class='form-control' style='height:70px;'></textarea></td>
      <td class='tips2' valign='top'>不超过100个字符</td>
    </tr>
    <tr>
      <td colspan="3">
        <input type="submit" id='subbtn' class='mwt-btn mwt-btn-default mwt-btn-xs' style='padding:2px 8px;' value="提交"/>
      </td>
    </tr>
  </table>
  <table class="tb tb2">
    <tr><th class="partition">已推送消息</th></tr>
  </table>
  <table class='table' style='margin:5px 0 5px 0;'>
    <thead><tr>
      <td width='150'>消息标题</td>
      <td>消息内容</td>
      <td width='150'>发送时间</td>
    </tr></thead>
    <tbody id='release-versions-tbody'>
    </tbody>
  </table>
  <div id="pagebar-div"></div-->
</div>
</body>
</html>
