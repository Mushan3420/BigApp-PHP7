var jq = jQuery.noConflict();
var UZ;
(function ($) {

    UZ = function () {
        this.init = function() {
            set_html("pagetitle", _bigapp_obj.pagetitle);
            set_html("appdesc", _bigapp_obj.appdesc);
            set_html("appname", _bigapp_obj.appname);
           // set_html("iosurl", _bigapp_obj.iosurl);
            //set_html("down_url", _bigapp_obj.downurl);
            var qrcode = new QRCode(document.getElementById("qrCode"), {
                width  : 120,
				height : 120,
                colorDark: "#000000",
                colorLight: "#f3f3f3",
                correctLevel: QRCode.CorrectLevel.H
            });
			//print_r(_bigapp_obj.downurl);return;
		    qrcode.makeCode(_bigapp_obj.downurl);
        };
    };

    $(function () {
        var app = new UZ();
        app.init();
    })

})(jq);
