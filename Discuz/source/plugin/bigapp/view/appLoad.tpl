
<style>
    #step-guide {  }
    #step-guide a{color: #333;}
    #step-guide .step-unit{ display: none; position: relative; overflow: hidden; padding: 10px 0; margin: 10px 5px;}
    #step-guide .step-mask{ position: absolute; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,.1)}
    #step-guide .step-mask .progress{ position: absolute; left: 50%; top: 50%; z-index: 1; margin: -20px 0 0 -140px; width: 280px; height: 30px; overflow: hidden; border-radius: 4px; background: rgba(0,0,0,.8); color: #fff; line-height: 30px; text-align: center;}
    #step-guide .step-mask .progress a{ color: #ffff00;}
    #step-guide .step-mask .progress-bar{
        position: absolute; left: 0; top: 0;
        width: 0; height: 30px; background: #0099CC; color: #fff; line-height: 30px; text-align: center;
        overflow: hidden;
        transition: all .5s;
        -webkit-transition: all .5s;
    }
    #step-guide .step-mask .progress-bar:before{
        content:"";
        position: absolute; left: 0; top: 0; width: 30px; height: 100%;
        background-image:-webkit-gradient(linear, left top, right top,color-stop(0, rgba(255,255,255,0)), color-stop(.5, rgba(255,255,255,.3)), color-stop(1, rgba(255,255,255,0)));
        background-image: -moz-linear-gradient(left, rgba(255,255,255,0) 0%,rgba(255,255,255,.3) 50%,rgba(255,255,255,0) 100%);
        animation: step-progress 2.5s linear 0s infinite;
        -webkit-animation: step-progress 2.5s linear 0s infinite;
    }
    @keyframes step-progress {
        0%{
            left: 0;
            transform: translate3d(-100%,0,0);
        }
        100%{
            left: 150%;
            transform: translate3d(100%,0,0);
        }
    }
    @-webkit-keyframes step-progress {
        0%{
            left: 0;
            -webkit-transform: translate3d(-100%,0,0);
        }
        100%{
            left: 100%;
            -webkit-transform: translate3d(100%,0,0);
        }
    }
    #step-guide .step-mask .progress-txt{ position: relative; }

    #step-guide .step-unit.done .step-mask{ display: none;}

    #step-guide .step-box { width: 200px; float: left; margin-right: 30px; text-align: center; }
    #step-guide .step-box .pic{ display: inline-block; width: 112px; height: 112px; background: #efefef; border-radius: 4px; }
    #step-guide .step-box .qrcode{ display: inline-block; width: 112px; height: 112px; background: #efefef; border-radius: 4px; }
    #step-guide .step-box .pic img { width: 112px; height: 112px; vertical-align: bottom; }

    #step-guide .step-box .step-info { line-height: 30px; text-align: center; margin-top: 5px; }
    /*plat-icon.png*/
    #step-guide .step-box .plat-icon { width: 15px; height: 30px; display: inline-block; vertical-align: middle; background: url(source/plugin/bigapp/static/plat-icon.png) no-repeat; }

    #step-guide .step-box .point { display: inline-block; vertical-align: middle; margin-right: 5px; width: 10px; height: 10px; border-radius: 50%; background: #fef022; }

    #step-guide .step-2 .step-info { line-height: 20px; }

    #step-guide .step-box.android .plat-icon { background-position: left bottom; }

    #step-guide .step-box.android .point { background: #95e545; }
</style>
<table>
    <tr>
        <td>
            <div id="step-guide">
                <div class="step-unit" id="pack-android">
                    <a href="javascript:;" class="step-box step-1 android">
                        <div class="pic">
                        </div>
                        <div class="step-info">
                            <p><i class="plat-icon"></i> <span class="app-name"></span><span>(apk下载)</span></p>

                            <!-- <p><i class="point"></i>1.0准备提交</p> -->
                        </div>
                    </a>
                    <a href="javascript:;" class="step-box step-2">
                        <div class="qrcode">
                        </div>
                        <div class="step-info">
                            扫描二维码下载<br>
                            <span class="app-url"></span>
                        </div>
                    </a>
                    <a href="admin.php?action=plugins&operation=config&identifier=bigapp&pmod=release" class='btn'>发布</a>
                    <!-- <a href="javascript:;" class="step-box step-3">
                        <div class="pic">
                        </div>
                        <div class="step-info">
                            <p>安卓版源码下载</p>
                        </div>
                    </a> -->
                    <div class="step-mask">
                        <div class="progress">
                            <div class="progress-bar" data-percent="0"></div>
                            <div class="progress-txt"><span></span></div>
                        </div>
                    </div>
                </div>
                <div class="step-unit" id="pack-ios">
                    <a href="javascript:;" class="step-box step-1">
                        <div class="pic">
                        </div>
                        <div class="step-info">
                            <p><i class="plat-icon"></i> <span class="app-name"></span><span>(ipa下载)</span></p>

                            <!-- <p><i class="point"></i>1.0准备提交</p> -->
                        </div>
                    </a>
                    <a href="javascript:;" class="step-box step-2">
                        <div class="qrcode">
                        </div>
                        <div class="step-info">
                            扫描二维码下载<br>
                            <span class="app-url"></span>
                        </div>
                    </a>
                    <!-- <a href="javascript:;" class="step-box step-3">
                        <div class="pic">
                        </div>
                        <div class="step-info">
                            <p>ios版源码下载</p>
                        </div>
                    </a> -->
                    <div class="step-mask">
                        <div class="progress">
                            <div class="progress-bar" data-percent="0"></div>
                            <div class="progress-txt"><span></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </td>
    </tr>
</table>
<script charset="<% app_charset %>">
    var jq = jQuery.noConflict();
    (function($){

        var app_icon = '<% app_icon %>';
        var app_name = '<% app_name %>';
        var error_url = '<% error_url %>';
        var schedule_url = '<% schedule_url %>';
        var _schedule = {
            schedule_android:'0',
            schedule_ios:'0'
        };

        //var app_icon = 'http://mobfile.youzu.com/show?pic=Uploads_image%2F4%2Fd%2F6%2F0%2Fd609111e17bff58dd8f33a8184a03aa3.jpg&size=1024_1024';
        //var app_name = '三体论坛qa';
        //var error_url = 'http://i.youzu.com';
        //var schedule_url = 'http://192.168.180.23:8080/stub/taskschedule.php?';

        //设置一秒一查
        var checkSpeed = 5000;
        var postData = {

        };
        var done = 0;
        getPackState();
        var timer = setInterval(function(){
            getPackState();
        },checkSpeed);

        function getPackState(){
            $.ajax({
                type : "get",
                async : false,
                url : schedule_url+"&callback=handler",
                data : _schedule,
                dataType : "jsonp",
//                timeout: 5000,
                jsonp : "callback", //传递给请求处理程序或页面的，用以获得jsonp回调函数名的参数名(一般默认为:callback)
                jsonpCallback : "handler", //自定义的jsonp回调函数名称，默认为jQuery自动生成的随机函数名，也可以写"?"，jQuery会自动为你处理数据
                success : function(json) {
//                console.log(json);
                    if(typeof json.data == 'undefined'){
                        clearInterval(timer);
                    }else{
                        //status  0||1||-1 0有效 -1失败
                        //project 源代码下载地址
                        //app  包下载地址
                        var done = 0;
                        var count = 0;
                        var data = json.data.task_info;
                        for(var plat in data){
                            count++;
                            var item = $("#pack-"+plat);
                            if(item.hasClass('done') || item.hasClass('error')){
                                done++;
                            }else{
                                var info = data[plat];
                                console.log(info);
                                var app_path = info.jump_middle_page_url;
                                if(item.find(".pic img").length == 0 && app_icon){
                                    var str = '<img src="'+app_icon+'" alt="'+app_name+'">';
                                    item.find(".pic").append(str);
                                }
                                var schedule = info.schedule;
                                _schedule['schedule_'+plat] = schedule;
//                            console.log(plat+"||"+schedule+"||"+done+"||"+info.status+"||"+app_path+"||"+item.find('.step-mask .progress-bar').data('percent'));
                                item.find('.app-name').html(app_name);
                                item.find('.app-url').html(info.app);
                                item.find('.step-1').attr('href',info.app);
                                item.find('.step-2').attr('href',info.app);
//                                item.find('.step-3').attr('href',info.project);
                                if(schedule>item.find('.step-mask .progress-bar').data('percent')){
                                    item.find('.step-mask .progress-bar').data('percent',schedule);
                                    item.find('.step-mask .progress-bar').css({width:schedule+"%"});
                                }	
				if(schedule < 100){
				    item.find('.step-mask span').html(info.desc);
				}
                                item.show();


                                if(info.status===0){
                                    if((app_path && !item.find(".qrcode").hasClass('done')) || info.status===0){
                                        item.find(".qrcode").each(function(){
                                            $(this).html('');
                                            var url = encodeURI(app_path);
                                            var qrcodeObj = $(this).get(0);
                                            var qrcode = new QRCode(qrcodeObj, {
                                                width : 112,
                                                height : 112,
                                                colorDark : "#000000",
                                                colorLight : "#ffffff",
                                                correctLevel : QRCode.CorrectLevel.H
                                            });
                                            qrcode.makeCode(url);
                                        });
                                        item.find(".qrcode").addClass('done');
                                    }
                                    item.addClass("done");
                                    done++;
                                }else if(info.status=="-1"){
                                    item.addClass("error");
                                    done++;
                                    item.find('.step-mask span').html('打包失败，请检查任务配置，并<a href="'+error_url+'">点击这里</a>重新打包。');
                                };
                            }
                        }
                        if(done == count){
                            clearInterval(timer);
                        }
                    }
                },
                error : function(data) {
//                    console.log(data);
//                    clearInterval(timer);
//                    $('#step-guide .step-mask span').html('任务处理失败，请<a href="'+error_url+'">点击这里</a>重新打包。');
                }
            });
        }
    })(jq);
</script>
