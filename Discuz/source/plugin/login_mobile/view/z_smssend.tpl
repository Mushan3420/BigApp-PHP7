<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title></title>
  <link rel="stylesheet" href="<%plugin_path%>/view/css/mwt.css"/>
  <script src="<%plugin_path%>/view/js/jquery.js"></script>
  <script src="<%plugin_path%>/view/js/require.js"></script>
<!--
  <script src="<%plugin_path%>/view/js/mwt.js" charset="utf-8"></script>
  <script src="<%plugin_path%>/view/js/plugin/smslist.js" charset="utf-8"></script>
-->
  <style>
    .selli {cursor:pointer;padding:2px 5px;}
    [data-state='1'] {color:black;}
    [data-state='0'] {color:green;}
    .selli:hover {background-color:#eee;}
  </style>
  <%js_script%>
  <script>
  var src="<%plugin_path%>/view/src/";
  require.config({
      baseUrl: src,
      packages: [
          {name:'jquery', location:'<%plugin_path%>/view/js', main:'jquery'},
          {name:'mwt', location:'<%plugin_path%>/view/js', main:'mwt'}
      ]
  });

  define("main",function(require){
	  require("mwt");
      return { init:function(){
	      require("user/selgrid").init();
      }};
  });

  jQuery.noConflict();
  jQuery(document).ready(function($) {
      require(["main"],function(main){
          main.init();
      });
  });
  </script>
</head>
<body id="BS">

  <table class="tb tb2">
    <tr><th colspan="3" class="partition">短信群发</th></tr>
  </table>
  <table>
    <tr>
      <td width='100' valign='top'>短信接收对象：</td>
      <td width='200' valign='top'><span style='color:red;margin-bottom:2px;'>（提示：双击删除）</span>
        <a href='javascript:require("user/selgrid").reset();' style='float:right;'>清空</a>
        <ul id='userul' style='border:solid 1px #ccc;height:330px;overflow:auto;margin-top:3px;'>
        </ul>
      </td>
      <td width='80' align='center'>
        <button id='addbtn' class='mwt-btn mwt-btn-success mwt-btn-sm' >《添加</button><br><br>
      </td>
      <td valign='top'>
        选择用户：
        <div id='usergrid-div' style='margin-top:3px;'></div>
      </td>
    </tr>
    <tr><td colspan='4' style='height:10px;'></td></tr>
    <tr>
      <td valign='top'>短信内容：</td>
      <td>
        <textarea id='msgtxt' class='form-control' style='height:70px;'></textarea>
      </td>
      <td class='tips2' valign='top' colspan='2' style='padding-left:10px;'>
        <ul><li>短信内容不超过50个字符</li>
            <li style='color:red;'>请确保短信内容符合您在短信平台上设置的短信模板</li>
            <li>您可以使用$uid,$username,$realname这些变量</li>
        </ul>
      </td>
    </tr>
    <tr>
      <td colspan='4'><button id='subbtn' class='mwt-btn mwt-btn-default mwt-btn-sm'>发送</button>&nbsp;&nbsp;<span id='sendmsg' style='color:red'></span>
    </td>
    </tr>
  </table>
  <ul id='cosoleul'></ul>
</body>
</html>
