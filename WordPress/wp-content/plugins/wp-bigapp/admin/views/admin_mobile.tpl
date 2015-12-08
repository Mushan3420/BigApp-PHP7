<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title id='pagetitle'></title>
  <style>
    body, div, dl, dt, dd, ul, ol, li, h1, h2, h3, h4, h5, h6, pre, code, form, fieldset, legend, label, input, textarea, p, blockquote, th, td, hr, button, article, aside, details, figcaption, figure, footer, header, menu, nav, section, i, span, a { margin:0; padding:0; border:none;  }
        article, aside, details, figcaption, figure, footer, header, nav, section, summary { display:block }
        [hidden] { display:none }
        html,body { font-family:"microsoft yahei","Helvetica Neue","Arial"; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; font-size: 16px; position: relative;}
        body {min-width:1000px; min-height:100px;line-height:20px; background:#f3f3f3; color:#fff;}
        a:focus { outline:none; }
        a:active, a:hover { outline:0; text-decoration:none }
        h1,h2,h3,h4,h5{ font-weight: 100; font-size: 14px; color: #fff;}
        i, em { font-style:normal;}
        mark { background:#ff0; color:#000 }
        q { quotes:"\201C" "\201D" "\2018" "\2019" }
        img { border:0; max-width:100%; width:100%; vertical-align: bottom; }
        input, textarea, button, select { font-family:inherit; color:#fff; }
        button, html input[type="button"], input[type="reset"], input[type="submit"] { -webkit-appearance:button; cursor:pointer }
        label{ cursor: pointer; display: inline-block;}
        button[disabled], input[disabled] { cursor:default }
        input[type="checkbox"], input[type="radio"] { box-sizing:border-box; padding:0 }
        input[type="search"] { -webkit-appearance:textfield; }
        input[type="search"]::-webkit-search-cancel-button, input[type="search"]::-webkit-search-decoration { -webkit-appearance:none }
        button::-moz-focus-inner, input::-moz-focus-inner { border:0; padding:0 }
        ::-webkit-input-placeholder { color:#a9a9a9!important;}
        :-moz-placeholder { color:#a9a9a9!important;}
        ::-moz-placeholder {color:#a9a9a9!important;}
        :-ms-input-placeholder {color:#a9a9a9!important;}
        textarea { overflow:auto; vertical-align:top }
        table { border-collapse:collapse; border-spacing:0 }
        ol, ul { list-style:none }
        a { text-decoration:none; color:#3e4041; }
        .clear { clear:both; display:block; overflow:hidden; height:0; line-height:0; font-size:0 }
        .fll{ float: left;}
        .flr{ float: right;}

        #container{ width: 100%; min-height:100px; position: relative; background: url(<%plugin_path%>/img/bg.jpg) center 0 no-repeat; }
        #content{ overflow: hidden; width: 980px; margin: 0 auto; position: relative;}
        .frame-l{ float: left; width: 498px; overflow: hidden;}
        .frame-l .phone-show{ margin-left: 2px; width: 419px; height: 625px; background: url(<%plugin_path%>/img/phone.png); margin-top: 45px; overflow: hidden;}
        .frame-l .phone-show .preview{ width: 249px; height: 433px; margin: 112px 0 0 12px;}
        .frame-r{ float: right; width: 482px; }
        .frame-r .title{ width: 432px; border-bottom:1px #fff solid; padding-bottom: 20px; overflow: hidden; padding-top: 136px;}
        .frame-r .title .logo{ float: left; width: 90px; height: 90px; margin-right: 25px;}
        .frame-r .title .hgroup{ float: left;  }
        .frame-r .title .hgroup h1{ font-size: 36px; line-height: 38px;}
        .frame-r .title .hgroup h2{ font-size: 36px; font-weight: bold; line-height: 38px; margin-top: 10px;}
        .frame-r .description{ width: 440px; font-size: 18px; line-height: 30px; margin-top: 18px;min-height:76px;}

        .link-bar{ margin-top: 140px; overflow: hidden;}
        .link-bar .btn-bar{ float: left; width: 212px; }
        .link-bar .btn{ position: relative; width: 145px; height: 60px; border:1px #3e4041 solid; line-height: 60px; padding-left: 65px; background: 20px center no-repeat; font-size: 18px; display: block; margin-bottom: 18px;}
        .link-bar .btn-ios{ background-image: url(<%plugin_path%>/img/icon-ios.png);}
        .link-bar .btn-android{ background-image: url(<%plugin_path%>/img/icon-android.png);}
        .link-bar .qr-code{ float: left; margin-left: 35px; width: 120px; height: 120px; padding: 10px; border:1px #3e4041 solid;}
        .footer{ width: 100%; height: 96px;}
        .copyright{ position: absolute; width: 100%; height: 96px; line-height: 96px; font-size: 14px; color: #a6a6a6; text-align: center; background: #f3f3f3; }
  </style>

  <script src="<%plugin_path%>/js/script.js" charset="utf-8"></script>
  <script src="<%plugin_path%>/js/uz-common.js" charset="utf-8"></script>
  <script src="<%plugin_path%>/js/uploadfile.js" charset="utf-8"></script>
  <script src="<%plugin_path%>/js/qr.js" charset="utf-8"></script>
  <script src="<%plugin_path%>/js/ajaxfileupload.js" charset="utf-8"></script>
  <script src="<%plugin_path%>/js/mwt.js" charset="utf-8"></script>
  <script src="<%plugin_path%>/js/uz/mobile.js" charset="utf-8"></script>
  
 <%js_script%>
</head>
<body id="uz">
  <div id="container">
    <div id="content">
        <div class="frame-l">
            <div class="phone-show">
                <div class="preview" style="background: url(<%mobile_app_img%>);"></div>
            </div>
        </div>
        <div class="frame-r">
            <div class="title">
                <div class="logo">
                  <img src='<%icon_img%>' style='width:90px;height:90px;border-radius:8px;'/>
                </div>
                <div class="hgroup">
                    <!--h1>微吧应用</h1-->
                    <h2 id='appname'>更便捷的掌上论坛</h2>
                </div>
            </div>
            <div id='appdesc' class="description">
                这是一段简介文字，微吧应用是一个什么样的什么功能的APP,旨在打造一个什么样的平台，给用户什么样的用户体验之类
            </div>
            <div class="link-bar">
                <div class="btn-bar">
                    <a href="<% iosurl %>" class="btn btn-ios" target="_blank">iPhone 下载</a>
                    <a href="<% androidurl %>" class="btn btn-android" target="_blank">Android 下载</a>
                </div>
                <div class="qr-code" id="qrCode" data-url="http://localhost:63342/topic/201508/promotion/index.html">

                </div>
            </div>
        </div>
    </div>
    <div class="footer">&nbsp;
        <div class="copyright">Copyright ? BigApp 2015</div>
    </div>
  </div>  
</body>
</html>
