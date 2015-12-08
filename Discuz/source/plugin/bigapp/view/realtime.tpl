<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="<% app_charset %>">
    <title></title>
    <link rel="stylesheet" href="<%plugin_path%>/css/uz-style.css"/>
    <script src="<%plugin_path%>/js/jquery.js"></script>
    <script src="<%plugin_path%>/js/echarts-all.js"></script>
    <script src="<%plugin_path%>/js/uz-data.js" charset="utf-8"></script>
	<%js_script%>
</head>
<body id="uz">
<div id="container">
    <div class="chart-bar">
        <ul class="chart-tab" id="platTab">
            <li data-value="0">总体</li>
            <li data-value="1">安卓 App</li>
            <li data-value="2">IOS App</li>
        </ul>
    </div>
    <div class="data-box">
        <div class="box-title">
            <h2>基本指标</h2>

            <div class="option-bar">
            </div>
        </div>
        <div class="box-content chart-area">
            <div class="box-group getData" id="today_total">
                <div class="box-block new_install">
                    <h3>截至目前为止的新安装数</h3>
                    <p></p>
                    <!--<div class="tips">1213</div>-->
                </div>
                <div class="box-block startup_times">
                    <h3>截至目前为止的启动次数</h3>
                    <p></p>

                    <!--<div class="tips">-1.8%</div>-->
                </div>
                <div class="box-block dau">
                    <h3>截至目前为止的日活</h3>
                    <p></p>
                    <!--<div class="tips"></div>-->
                </div>
            </div>
            <div class="chart-content getData" id="today_trend">

            </div>
        </div>
    </div>
</div>
</body>
</html>