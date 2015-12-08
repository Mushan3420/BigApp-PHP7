<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <link rel="stylesheet" href="<%plugin_path%>/css/mwt.css"/>
    <link rel="stylesheet" href="<%plugin_path%>/css/uz-style.css"/>
    <script src="<%plugin_path%>/js/jquery.js"></script>
    <script src="<%plugin_path%>/js/lib/mwt.js" charset="utf-8"></script>
    <script src="<%plugin_path%>/js/uz/certify.js" charset="utf-8"></script>
	<%js_script%>
</head>
<body id="uz">
  <table class="tb tb2">
    <tr><th colspan="15" class="partition">站点引导地址 <span style='color:red;'>（请在BigApp应用中心创建应用时，填写此地址）<span></th></tr>
  </table>

  <div class='' style='border-radius:4px;margin:10px 5px;'>
    <b style='font-size:14px;'><%apiurl%></b>
    <div id='apierrmsg' style='color:red;font-size:13px;margin-top:10px;'></div>
  </div>


  <table class="tb tb2">
    <tr><th colspan="15" class="partition">站长认证</th></tr>
  </table>

  <div id='info-div' class='mwt-alert mwt-alert-danger' 
       style='border-radius:4px;margin:10px 0;'>
  </div>

  <table class="tb tb2">
    <tr>
      <td width='150' rowspan='2' valign='top' style='font-size:22px;font-family:Arial;'><b>APP_KEY：</b></td>
      <td width='300'>
        <input id='fm_ak' class='txt' style="width:100%"/>
      </td>
      <td>
        <input type="button" id='getkeybtn' class='mwt-btn mwt-btn-default'
               style='padding: 3px 10px;border-radius:4px;margin-left:10px;' value="获取Key"/>
      </td>
    </tr>
    <tr>
      <td class='tips2'>您可以前往BigApp应用中心获取您的AppKey，并进行认证。<br>有关如何获取您的AppKey，<a href='<%myapp%>' target='_blank'>请点此查阅</a></td>
      <td></td>
    </tr>
    <tr>
      <td valign='top' style='font-size:22px;font-family:Arial;'><b>APP_SECRET：</b></td>
      <td width='300'>
        <input id='fm_sk' class='txt' style="width:100%"/>
      </td>
      <td></td>
    </tr>
    <tr>
      <td colspan="3">
        <input type="button" id='subbtn' class='mwt-btn mwt-btn-default'
               style='padding:3px 20px;border-radius:4px;margin-top:10px;' value="提交"/>
      </td>
    </tr>
  </table>

<style>
  .chkbox {margin:5px;}
</style>


</body>
</html>
