<!doctype html>
<html>
<head>
    <meta charset="<%app_charset%>">
    <title></title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    <link rel="stylesheet" href="<%source_path%>styles/main.ddafc7c6.css">
    <%js_script%>
</head>
<body ng-app="designApp"> <!--[if lte IE 8]>
<![endif]--> <!-- Add your site or application content here -->
<app-header value="ini"></app-header>
<div ng-view=""></div>
<!--<footer id="footer">Copyright &copy; BigApp 2015</footer>-->
<loading></loading>
<global-tips></global-tips>
<!-- BaseInfo
<%source_path%>
charset='utf-8'
<%js_script%>
-->
<script charset='utf-8' src="<%source_path%>scripts/vendor.769e44a5.js"></script>
<script charset='utf-8' src="<%source_path%>scripts/scripts.f67a1da3.js"></script>
<script charset='utf-8' src="<%source_path%>scripts/template.8971778a.js"></script>
</body>
</html>