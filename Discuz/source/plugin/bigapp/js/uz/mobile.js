var jq = jQuery.noConflict();
var UZ;
(function ($) {

    UZ = function () {
        this.init = function() {
            var pageTitle = v.appname+"客户端下载";
            set_html("pagetitle", pageTitle);
            set_html("appdesc", v.appdesc);
            set_html("appname", v.appname);
           // set_html("iosurl", v.iosurl);
            //set_html("down_url", v.downurl);
            var qrcode = new QRCode(document.getElementById("qrCode"), {
                width  : 120,
				height : 120,
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
