<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title></title>
  <link rel="stylesheet" href="<%plugin_path%>/view/css/mwt.css"/>
  <script src="<%plugin_path%>/view/js/jquery.js"></script>
  <script src="<%plugin_path%>/view/js/require.js"></script>
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
	      require("user/grid").init();
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
    <tr><th colspan="15" class="partition">手机用户管理</th></tr>
  </table> 
  <div id='grid-div' style='margin:6px 0;'></div>
  <div id='dialog-div'></div>
</body>
</html>
