<div id="uz">
    <link rel="stylesheet" href="<%plugin_path%>/css/uz-style.css"/>
    <script src="<%plugin_path%>/js/jquery.js"></script>
    <script>
        var jq = jQuery.noConflict();
    </script>
    <script src="<%plugin_path%>/js/uz-common.js" charset="utf-8"></script>
    <script src="<%plugin_path%>/js/ajaxfileupload.js" charset="utf-8"></script>
    <script src="<%plugin_path%>/js/uploadfile.js" charset="utf-8"></script>
	<%js_script%>
	<script src="<%plugin_path%>/js/uz/threadsetting.js" charset="utf-8"></script>
	
	
	<body id="uz">
	  <table class="tb tb2">
		<tr><th colspan="15" class="partition">推荐区设置</th></tr>
	  </table>

  <table class="tb tb2">
    <tr>
      <th width='80' style="font-weight:bold;">启用</th>
      <th width='100' style="font-weight:bold;">排序</th>
      <th  style="font-weight:bold;">模块</td>
	  <th  style="font-weight:bold;">显示名称</td>
    </tr>
	<%html%>
    <tr>
      <td colspan="3">
        <input type="button" class='btn' id='subbtn' style='padding:2px 8px;' value="提交"/>
      </td>
    </tr>
  </table>

    </div>
</div>