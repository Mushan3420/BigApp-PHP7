<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <link rel="stylesheet" href="<%plugin_path%>/css/mwt.css"/>
	<script src="<%plugin_path%>/js/uz/mobileset.js" charset="utf-8"></script>
    <%js_script%>
</head>
<body id="uz">

<div class="wrap control-section wp-admin wp-core-ui js  nav-menus-php auto-fold admin-bar branch-4-2 version-4-2-2 admin-color-fresh locale-zh-cn customize-support svg menu-max-depth-0" id="wpbody-content">
	<div id="uz">
		<h2>插件名称</h2>
		<ul class="subsubsub">
			<li>插件版本号：V<span class="tpl-version"></span> |</li>
			<li>最后更新日期：<span class="tpl-updatetime"></span> | </li>
            <li><a href="http://bigapp.youzu.com" target="_blank">BigApp应用中心</a> | </li>
			<li><a href="javascript:history.back();">返回</a></li>
		</ul>
	</div>
	<div class="clear"></div>
	<br/>
	<br/>
  <table class="tb tb2">
    <tr><th colspan="15" class="partition">推广页设置</th></tr>
  </table>

  <table class="tb tb2">
    <tr>
      <td width='80'>IOS URL：</td>
      <td width='300'><input id='fm_ios_url' class='txt' style="width:100%"/></td>
      <td class='tips2'>（必填）IOS链接地址，如：http://bigapp.mob.com/</td>
    </tr>

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
  </div>

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
