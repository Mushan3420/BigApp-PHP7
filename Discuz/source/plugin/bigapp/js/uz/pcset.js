var jq = jQuery.noConflict();
var UZ;
(function ($) {

    UZ = function () {

        function set_moburl_switch(value) {
            set_radio_value("moburl_switch",value);
            if (value==2) {
                $("#moburl_txt").show();
            } else {
                $("#moburl_txt").hide();
            }
        };

        this.init = function() {
            var thiso = this;
            set_moburl_switch(v.moburl_switch);
            set_value("moburl_txt", v.moburl);
            
            $("[name='moburl_switch']").change(function(){
                var v = $("[name='moburl_switch']:checked").val();
                //alert(v);
                set_moburl_switch(v);
            });
        };
    };

    $(function () {
        var app = new UZ();
        app.init();
    })

})(jq);
