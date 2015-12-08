<div id="uz">
    <link rel="stylesheet" href="<%plugin_path%>/css/uz-style.css"/>
    <script src="<%plugin_path%>/js/jquery.js"></script>
    <script>
        var jq = jQuery.noConflict();
        var v = {
            "setBanner": "http://192.168.180.47:8080/discuz/plugin.php?id=bigapp:homeapi&method=setBanner",
            "getBanner": "http://192.168.180.47:8080/discuz/plugin.php?id=bigapp:homeapi&method=getBanner",
            "setFunc": "http://192.168.180.47:8080/discuz/plugin.php?id=bigapp:homeapi&method=setFunc",
            "getFunc": "http://192.168.180.47:8080/discuz/plugin.php?id=bigapp:homeapi&method=getFunc",
            "uploadBanner": "http://192.168.180.47:8080/discuz/plugin.php?id=bigapp:uploadpic&key=banner_image_s",
            "uploadFunc": "http://192.168.180.47:8080/discuz/plugin.php?id=bigapp:uploadpic&key=func_image_s",
            "imageSize":{
                "icon_image_s":{
                    "width":1024,
                    "height":1024,
                    "size":1048576
                },
                "startup_image_s":{
                    "width":1242,
                    "height":2208,
                    "size":1048576
                },
                "banner_image_s":{
                    "width":750,
                    "height":342,
                    "size":1048576
                },
                "func_image_s":{
                    "width":96,
                    "height":96,
                    "size":1048576
                },
                "func_forum_image_s":{
                    "width":88,
                    "height":88,
                    "size":1048576
                }
            }
        };
        var pageType = {
            key : 'uploadFunc',
            value : 'func_forum_image_s',
            imgSize: ['func_forum_image_s','func_forum_image_s','func_forum_image_s']
        };
        var mType = {
            get : 'getFunc',
            set : 'setFunc'
        }

        var descVisible = 1;

        var picTips = '<div class="tips_item tips_'+pageType.imgSize[0]+'">图片大小最小为'+ v.imageSize[pageType.imgSize[0]].width+'x'+v.imageSize[pageType.imgSize[0]].height+'</div>';

    </script>
	<%js_script%>
    <script src="<%plugin_path%>/js/ajaxfileupload.js" charset="utf-8"></script>
    <script src="<%plugin_path%>/js/uploadfile.js" charset="utf-8"></script>
    <script src="<%plugin_path%>/js/uz-common.js" charset="utf-8"></script>
    <script src="<%plugin_path%>/js/uz-i-custom.js" charset="utf-8"></script>
    <div id="container">
        <table class="tb tb2 ">
            <tr>
                <th colspan="15" class="partition">示例</th>
            </tr>
            <tr class="noborder">
                <td class="vtop rowform">
                    <img src="<%plugin_path%>/image/custom/set_banner_3.png"/>
                </td>
            </tr>
        </table>
        <table class="tb tb2 ">
            <tr>
                <th colspan="15" class="partition">功能板块设置</th>
            </tr>
            <tr><td colspan="2" class="td27">是否开启该板块:</td></tr>
            <tr class="noborder">
                <td class="vtop rowform">
                    <ul>
                        <li><label><input class="radio" type="radio" name="switchBlock" value="1">&nbsp;是</label></li>
                        <li><label><input class="radio" type="radio" name="switchBlock" value="0">&nbsp;否</label></li>
                    </ul>
                </td>
            </tr>
        </table>
        <table id="setList" class="tb tb2 ">
        </table>
    </div>
</div>