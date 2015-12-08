<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <link rel="stylesheet" href="<%plugin_path%>/css/jquery-ui.min.css"/>
    <link rel="stylesheet" href="<%plugin_path%>/css/jquery-ui.theme.min.css"/>
    <link rel="stylesheet" href="<%plugin_path%>/css/uz-style.css"/>
    <script src="<%plugin_path%>/js/jquery.js"></script>
    <script src="<%plugin_path%>/js/jquery-ui.js"></script>
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
            <h2>截至到今日0点为止的安装总数、倒数7天的平均日活</h2>
        </div>
        <div class="box-content">
            <div class="box-group getData"id="days_total">
                <div class="box-block total_install">
                    <h3>累计安装</h3>
                    <p></p>
                    <!--<div class="tips">1213</div>-->
                </div>
                <div class="box-block dau">
                    <h3>7日平均日活</h3>
                    <p></p>
                    <!--<div class="tips">-1.8%</div>-->
                </div>
            </div>
        </div>
    </div>
    <div class="data-box">
        <div class="box-title">
            <h2>昨天的全部指标</h2>
        </div>
        <div class="box-content">
            <div class="box-group getData"id="yesterday_total">
                <div class="box-block total_install">
                    <h3>累计安装</h3>
                    <p></p>
                    <!--<div class="tips">1213</div>-->
                </div>
                <div class="box-block dau">
                    <h3>昨天活跃的用户</h3>
                    <p></p>
                    <!--<div class="tips">1213</div>-->
                </div>
                <div class="box-block startup_times">
                    <h3>启动次数</h3>
                    <p></p>
                    <!--<div class="tips">-1.8%</div>-->
                </div>
                <div class="box-block duration">
                    <h3>单次平均使用时间(秒)</h3>
                    <p></p>
                    <!--<div class="tips"></div>-->
                </div>
                <div class="box-block duration_day">
                    <h3>日平均使用时间(秒)</h3>
                    <p></p>
                    <!--<div class="tips"></div>-->
                </div>
            </div>
        </div>
    </div>
    <div class="data-box getData" id="days_trend">
        <div class="box-title">
            <h2>整体趋势</h2>
            <div class="option-bar">
                <label class="barDate">
                    <span>选择时间：</span>
                    <input type="text" class="date-value" readonly/>
                    <div class="date-pop">
                        <div class="btn-bar">
                            <button class="date-submit" data-value="60">过去60天</button>
                            <button class="date-submit" data-value="30">过去30天</button>
                            <button class="date-submit" data-value="7">过去7天</button>
                            <button class="date-submit fr">确定</button>
                        </div>
                        <div class="date-area" id="from"></div>
                        <div class="date-area" id="to"></div>
                    </div>
                </label>
                <!--<label><span>起始：</span><input type="text" id="from" name="from"></label>-->

                <!--<label><span>结束：</span><input type="text" id="to" name="to"></label>-->

            </div>
        </div>
        <div class="box-content chart-area">
            <div class="chart-bar">
                <ul class="chart-tab">
                    <li data-value="total_install">总安装数</li>
                    <li data-value="dau">日活</li>
                    <li data-value="startup_times">启动次数</li>
                    <li data-value="duration">单次平均使用时间</li>
                    <li data-value="duration_day">日平均使用时间</li>
                </ul>
            </div>
            <div class="chart-content">

            </div>
        </div>
    </div>
</div>
</body>
</html>
