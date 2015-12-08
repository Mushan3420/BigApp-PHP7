<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title id='pagetitle'></title>
  <script src="<%plugin_path%>/js/jquery.js"></script>
  <script src="<%plugin_path%>/js/qr.js"></script>
  <%js_script%>
  <script>
var jq = jQuery.noConflict();
var UZ;
(function ($) {
    UZ = function () {
        this.init = function() {
            var qrcode = new QRCode(document.getElementById("qrCode"), {
                width  : 98,
				height : 98,
                colorDark: "#000000",
                colorLight: "#f3f3f3",
                correctLevel: QRCode.CorrectLevel.H
            });
		    qrcode.makeCode(v.downurl);
        };
    };
    $(function () {
        var app = new UZ();
        app.init();
    })
})(jq);
  </script>
</head>
<body id="uz">
  <div id='qrCode' style='padding:6px;'></div>扫描下载客户端
</body>
</html>
