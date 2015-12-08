/*
 * UZ Discuz! 前端解决方案
 * Name UZ
 * v             与php的数据通道
 * data_base     数据暂存
 * legend        表格字段数组
 * plat          设备数组
 * init(data)    预设各接口初始化方法（需调用）
 *    data        通过回调带回的数据
 * update(data)  预设各接口更新方法（需调用）
 * getData(formData,callback,state)       接口调用方法
 *    formData    数据设置，其中formData.data为请求的参数
 *    callback    成功回调(暂定错误回调为统一方法)
 *    state       重新请求标记，若值为1强制重复请求，否则如果数据暂存(data_base)对象中有响应的数据则不重复调用。
 */

var jq = jQuery.noConflict();
var UZ;
(function ($) {
    UZ = function () {
        var url_map = {
            today_trend: 'http://192.168.180.23:8080/product/ui/http/index.php?module=api&controller=today&method=getTrend&app_key=11111',
            today_total: 'http://192.168.180.23:8080/product/ui/http/index.php?module=api&controller=today&method=getTotal&app_key=11111',
            days_total: 'http://192.168.180.23:8080/product/ui/http/index.php?module=api&controller=days&method=gettotal&app_key=11111',
            days_trend: 'http://192.168.180.23:8080/product/ui/http/index.php?module=api&controller=today&method=getTrend&app_key=11111',
            yesterday_total: 'http://192.168.180.23:8080/product/ui/http/index.php?module=api&controller=days&method=getyesterdaytotal&app_key=11111'
        };
        if(typeof v != 'undefined'){
            url_map = v;
        }

        var plat = ['total', 'android', 'ios'];
        var legend_map = {
            new_install: '新安装数',
            startup_times: '启动次数',
            dau: '日活'
        };
        var name_map = {
            duration:'单次平均使用时间(秒)',
            duration_day:'日平均使用时间(秒)',
            new_install:'新安装',
            total_install:'总安装数',
            dau:'日活',
            start_times:'启动次数',
            startup_times:'启动次数'
        };
        var platName_map = {
            total: '总和',
            android: '安卓',
            ios: '苹果'
        };

        function formatDate(date){
            return date.getFullYear()+'/'+(date.getMonth()+1)+'/'+date.getDate();
        }
        function calcDate(method,date,num){
            var count = num*1000*60*60*24;
            var newDate = new Date();
            if(method == 'sub'){
                newDate.setTime(date.getTime()-count);
                return newDate;
            }

        }
        var chartOption = {
            title: {
                text: ''
            },
            tooltip : {
                trigger: 'axis'
            },
            legend: {
                data:['新安装','启动次数','日活']
            },
            name_map: name_map,
            platName_map: platName_map,
            toolbox: {
                show : true,
                feature : {
//                    mark : {show: true},
//                    dataView : {show: true, readOnly: false},
//                    magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
//                    restore : {show: true},
                    saveAsImage : {show: true}
                }
            },
            calculable : true,
            xAxis : [
                {
                    type : 'category',
                    boundaryGap : false,
                    data : []
                }
            ],
            yAxis : [
                {
                    type : 'value'
                }
            ],
            series : []
        };
        return {
            v: url_map
            ,data_base: {
                today_trend: undefined,
                today_total: undefined,
                days_total: undefined,
                days_trend: undefined,
                yesterday_total: undefined
            }
            , legend: legend_map
            , plat: plat
            , init:{
                today_trend: '',
                today_total: '',
                days_total: '',
                days_trend: function(){
                    var id = 'days_trend';
                    var obj = $('#' + id);
                    var nowDate = new Date();
                    nowDate.setTime(nowDate.getTime()-1000*60*60*24);
                    var toDate = formatDate(nowDate);
                    var formDate = formatDate(calcDate('sub',nowDate,30));
                    var app = new UZ();
                    //时间组件定义
                    $("#from").datepicker({
                        defaultDate: "+1w",
                        changeMonth: true,
                        numberOfMonths: 1,
                        onSelect : function( selectedDate ) {
                            $( "#to" ).datepicker( "option", "minDate", selectedDate );
                            //格式化并在对象中保存数据
                            obj.data({'from_day':selectedDate.split('/').join('-')});
                        }
                    });

                    $("#to").datepicker({
                        defaultDate: "+1w",
                        changeMonth: true,
                        numberOfMonths: 1,
                        onSelect : function( selectedDate ) {
                            $( "#from" ).datepicker( "option", "maxDate", selectedDate );
                            //格式化并在对象中保存数据
                            obj.data({'end_day':selectedDate.split('/').join('-')});
                        }
                    });
                    //触发事件声明
                    obj.find('.date-value').click(function(){
                        obj.find('.date-pop').show('fast');
                    });
                    //设置（确认）事件声明
                    obj.find('.date-submit').click(function(){
                        var btn = $(this);
                        var formData = {
                            url: id,
                            data: {
                                from_day: '',
                                end_day: '',
                                item: ''
                            },
                            plat : $('#platTab .active').data('value')
                        };
                        if(btn.data('value')){
                            toDate = formatDate(nowDate);
                            formDate = formatDate(calcDate('sub',nowDate,btn.data('value')));
                            obj.data({'from_day':formDate.split('/').join('-'),'end_day':toDate.split('/').join('-')});
                        }
                        $.extend(formData.data,obj.data());
                        app.getData(formData, function (data) {

                            obj.find('.date-pop').hide('fast');
                            if (typeof app.update[this.url] == 'function') {
                                app.update[this.url].apply(this, arguments);
                            }

                        },1);

                    });


                    obj.find('.chart-tab').each(function () {
                        var tab = $(this);
                        tab.find('li').eq(0).addClass('active');
                        tab.find('li').click(function () {
                            var btn = $(this);
                            var formData = {
                                url: id,
                                data: {
                                    from_day: '',
                                    end_day: '',
                                    item: ''
                                },
                                plat : $('#platTab .active').data('value')
                            };
                            btn.siblings().removeClass('active');
                            btn.addClass('active');
                            if(btn.data('value')){
                                obj.data({'item':btn.data('value')});
                            }
                            $.extend(formData.data,obj.data());
                            app.getData(formData, function (data) {
                                if (typeof app.update[this.url] == 'function') {
                                    app.update[this.url].apply(this, arguments);
                                }
                            },1);

                        });

                    });
                    //格式化并在对象中保存数据
                    obj.data({'from_day':formDate.split('/').join('-'),'end_day':toDate.split('/').join('-'),'item':'total_install'});

                },
                yesterday_total: ''
            }
            , update: {
                today_trend: function (data) {
                    var id = 'today_trend';
                    var obj = $('#' + id);
                    var p = plat[this.plat];
                    var d = data[p];
                    var legend = [];
                    var xArr= [];
                    var xDataArr= {};
                    chartOption.xAxis[0].data = [];
                    chartOption.series = [];
                    chartOption.title.text = platName_map[p];
                    for(var i in d){
                        xArr.push(i);
                        for(var x in d[i]){
                            if(!xDataArr[x]){
                                xDataArr[x] = [];
                            }
                            xDataArr[x].push(d[i][x]);
                        }
                    }
                    for(var i in xDataArr){
                        var o = {
                            name: legend_map[i],
                            type: 'line',
                            data: xDataArr[i]
                        };
                        legend.push(legend_map[i]);
                        chartOption.series.push(o)
                    }
                    chartOption.legend.data = legend;
                    chartOption.xAxis[0].data = xArr;
                    var item = echarts.init(obj.get(0));
                    item.clear();
                    item.setOption(chartOption);

                }, today_total: function (data) {
                    var id = 'today_total';
                    var obj = $('#' + id);
                    var p = plat[this.plat];
                    var d = data[p];
//                    console.log(d);
                    for (var i in d) {
                        obj.find('.' + i + ' p').html(d[i]);
                    }

                }, days_total: function (data) {
                    var id = 'days_total';
                    var obj = $('#' + id);
                    var p = plat[this.plat];
                    var d = data[p];
                    for (var i in d) {
                        obj.find('.' + i + ' p').html(d[i]);
                    }

                }, days_trend: function (data) {
                    var id = 'days_trend';
                    var obj = $('#' + id);
                    var p = plat[this.plat];
                    var d = data[p];
                    var legend = [];
                    var xArr= [];
                    var xDataArr= {};
//                    console.log(d);
                    chartOption.xAxis[0].data = [];
                    chartOption.series = [];
                    chartOption.title.text = platName_map[p];
                    chartOption.legend.data = [];
                    chartOption.legend.data[0] = name_map[obj.data('item')];
                    chartOption.xAxis[0].data = d.date;
                    chartOption.series[0] = {
                        name: name_map[obj.data('item')],
                        type: 'line',
                        data: d.value
                    };
                    var item = echarts.init(obj.find('.chart-content').get(0));
                    var fd = this.data.from_day.split('-').join('/');
                    var ed = this.data.end_day.split('-').join('/');
                    $("#from").datepicker('setDate',fd);
                    $("#to").datepicker('setDate',ed);
                    obj.find('.date-value').val(fd+'~'+ed);
//                    console.log(chartOption);
                    item.clear();
                    item.setOption(chartOption);


                }, yesterday_total: function (data) {
//                    console.log(data);
                    var id = 'yesterday_total';
                    var obj = $('#' + id);
                    var p = plat[this.plat];
                    var d = data[p];
                    for (var i in d) {
                        obj.find('.' + i + ' p').html(d[i]);
                    }

                }
            }
            ,getData: function(formData, callback, state) {
                var that = this;
                if(that.data_base[formData.url] == undefined){
                    state = 1;
                }

                if(state){
                    $.ajax({
                        type: "get",
                        async: false,
                        url: that.v[formData.url],
                        data: formData.data,
                        dataType: "json",
//                        dataType: "jsonp",
//                        jsonp: "callback", //传递给请求处理程序或页面的，用以获得jsonp回调函数名的参数名(一般默认为:callback)1
//                        jsonpCallback: formData.url, //自定义的jsonp回调函数名称，默认为jQuery自动生成的随机函数名，也可以写"?"，jQuery会自动为你处理数据
                        success: function (json) {
                            if (typeof callback != 'undefined' && typeof json.data != 'undefined') {
                                callback.apply(formData, [json.data]);
                                that.data_base[formData.url] = json.data;
                            }else{
                                //console.warn(json.data);
                            }
                        },
                        error: function (data) {
                            //console.warn(data);
                            alert(formData.url+': 数据读取失败');
                        }
                    })
                }else{
                    if (typeof callback != 'undefined') {
                        callback.apply(formData, [that.data_base[formData.url]]);
                    }
                }

            }
        }
    };

    $(function () {
        var app = new UZ();
        if($.browser.msie){
            $(".box-group").each(function(){
                var block =$(this).find('.box-block');
                var h = block.length;
                var blockL =$(this).find('.box-block').eq(h-1);
                var baseW = $('#container').width()+10;
                if($.browser.version == '7.0'){
                    baseW = $(this).width();

                }
                var w = parseInt(baseW/h);

                block.css({float:'left',width:w});
                blockL.css({width:baseW-w*(h-1)});

            })
        }
        if($.browser.msie && parseInt($.browser.version)<9){
            alert('数据图表仅支持IE9以上，Chrome，FF等浏览器，国内浏览器可启动极速模式来浏览。');
        }
        $('#platTab').each(function () {
            var obj = $(this);
            obj.find('li').click(function () {
                $(this).siblings().removeClass('active');
                $(this).addClass('active');
                var plat = $(this).data('value');
//                console.log(plat);
                $('.getData').each(function(){
                    var id = $(this).attr('id');
                    var obj = $(this);

                    var formData = {
                        url: id,
                        data: {},
                        plat: plat
                    };


                    if (typeof app.init[id] == 'function') {
                        app.init[id].apply(this, arguments);
                    }
                    if(obj.data()){
                        $.extend(formData.data,obj.data());
                    }
                    app.getData(formData, function (data) {
                        if (typeof app.update[this.url] == 'function') {
                            app.update[this.url].apply(this, arguments);
                        }

                    });

                });

            }).eq(0).trigger('click');

        });
    })

})(jq);
