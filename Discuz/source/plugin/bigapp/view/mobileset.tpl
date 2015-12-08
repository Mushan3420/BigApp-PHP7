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
    <script src="<%plugin_path%>/js/uz/mobileset.js" charset="utf-8"></script>
	<%js_script%>
</head>
<body id="uz">
  <table class="tb tb2">
    <tr><th colspan="15" class="partition">推广页设置</th></tr>
  </table>

  <table class="tb tb2">
    <tr>
      <td width='80'>IOS URL：</td>
      <td width='300'><input id='fm_ios_url' class='txt' style="width:100%"/></td>
      <td class='tips2'>（必填）IOS链接地址，如：http://bigapp.mob.com/</td>
    </tr>
<!--
    <tr>
      <td>二维码描述：</td>
      <td>
        <input id='fm_download_title' class='txt' style="width:100%" value="扫一扫，马上下载"/>
      </td>
      <td class='tips2'>二维码描述</td>
    </tr>
-->
    <tr style='display:none;'>
      <td valign='top'>推广页标题：</td>
      <td><input id='fm_title' class='txt' style="width:100%" value="客户端下载"/></td>
      <td class='tips2'>（必填）推广页标题</td>
    </tr>
    <tr>
      <td valign='top'>应用介绍：</td>
      <td><textarea id='fm_appdesc' class='txt' style="width:100%;height:40px;"></textarea></td>
      <td class='tips2'>（必填）应用介绍，100字以内</td>
    </tr>
    <tr>
      <td>应用截图: </td>
	  <td class="vtop rowform">
		<div class="yzd-input-file" id="fileBox1" data-id="mobile_app_image_s">
			<button class="btn-file">选择图片</button>
			<input type="file" onchange="uploadFile('<%imgUrl%>', 'fileBox1', 'mobile_app_image', '文件上传失败', 'uploading...', 'OK')" class="input-file"/>
			<input type="text" id='mobile_app_image' name="mobile_app_image" class="hidden" hidden style='width:100%'/>
			<div class="file-result">未选择任何文件</div>
		</div>
	  </td>
	  <td class="vtop tips2" s="1">（选填）应用截图尺寸249x433，文件大小不超过1Mb</td>
    </tr>
    <tr>
      <td colspan="3">
        <input type="button" id='subbtn' class='mwt-btn mwt-btn-default mwt-btn-xs' style='padding:2px 8px;' value="提交"/>
        <input type="button" id='openbtn' class='mwt-btn mwt-btn-default mwt-btn-xs' style='padding:2px 8px;' value="打开推广页"/>
      </td>
    </tr>
  </table>

  <table class="tb tb2">
    <tr><th colspan="15" class="partition">手机版地址配置</th></tr>
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
        <input type="submit" id='subbtn2' class='mwt-btn mwt-btn-default mwt-btn-xs' style='padding:2px 8px;' value="提交"/>
      </td>
    </tr>
  </table>

<style>
    .yzd-input-file{ position: relative; width: 250px; overflow: hidden; border:1px #999 solid; padding: 2px; background: #f9f9f9;}
    .yzd-input-file:hover{ border-color: #0099cc;}
    .yzd-input-file .btn-file{ float: left; width: 62px; height: 20px; line-height: 0; color: #000; padding: 0; margin: 0 5px 0 0; cursor: pointer;}
    .yzd-input-file .input-file{ position: absolute; left: 0; top: 0; width: 100%; height: 100%; padding: 0; border:none; opacity: 0; filter:alpha(opacity = 0);}
    .yzd-input-file .file-result{ overflow: hidden; font-size: 12px; line-height: 20px; color: #333333; }
    .yzd-input-file .img-show{ margin-top: 5px; border:1px #ccc solid; padding: 2px;}
    .yzd-input-file .img-show img{ width: 250px;}
    .yzd-input-file.done .file-result{ display: block; }
</style>


</body>
</html>
