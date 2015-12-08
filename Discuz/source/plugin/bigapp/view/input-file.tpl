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
<tr><td style="line-height:30px;font-weight:bold"><% icon_image_title %>: </td></tr>
<tr class="noborder" onmouseover="setfaq(this, 'faqbda4')">
	<td class="vtop rowform">
		<div class="yzd-input-file" id="fileBox1" data-id="icon_image_s">
			<button class="btn-file"><% btn_name %></button>
			<input type="file" onchange="uploadFile('<% upload_url_icon %>', 'fileBox1', '<% upid_icon %>', '<% error_str %>', 'uploading...', 'OK')" class="input-file"/>
			<input type="text" name="<% upid_icon %>" class="hidden" hidden/>
			<div class="file-result">未选择任何文件</div>
		</div>
	</td>
	<td class="vtop tips2" s="1"><% file_tip_icon %></td>
</tr>
<tr><td style="line-height:30px;font-weight:bold"><% startup_image_title %>: </td></tr>
<tr class="noborder" onmouseover="setfaq(this, 'faqbda4')">
	<td class="vtop rowform">
		<div class="yzd-input-file" id="fileBox2" data-id="startup_image_s">
			<button class="btn-file"><% btn_name %></button>
			<input type="file" onchange="uploadFile('<% upload_url_startup %>', 'fileBox2', '<% upid_startup %>', '<% error_str %>', 'uploading...', 'OK')" class="input-file"/>
			<input type="text" name="<% upid_startup %>" class="hidden" hidden/>
			<div class="file-result">未选择任何文件</div>
		</div>
	</td>
	<td class="vtop tips2" s="1"><% file_tip_startup %></td>
</tr>
