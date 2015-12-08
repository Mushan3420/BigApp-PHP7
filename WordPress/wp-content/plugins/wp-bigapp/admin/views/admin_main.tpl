<script type="text/javascript"> var _bigapp_obj = {bigapp_data}; </script>
<div class="wrap wp-admin wp-core-ui js  nav-menus-php auto-fold admin-bar branch-4-2 version-4-2-2 admin-color-fresh locale-zh-cn customize-support svg menu-max-depth-0" id="wpbody-content">
    <div id="uz">
            <h2>BigApp</h2>
            <ul class="subsubsub">
                <li>插件版本号：V<span class="tpl-version"></span> |</li>
                <li>最后更新日期：<span class="tpl-updatetime"></span> | </li>
                <li>内部版本号：<span class="tpl-innerversion"></span> | </li>
                <li><a href="http://bigapp.youzu.com" target="_blank">BigApp应用中心</a> | </li>
            </ul>
            <div class="clear"></div>
            <div class="uz-notice">
                <div class="title">公告</div>
                <p class="tpl-notice"></p>

                <p>
                </p>
            </div>
            <div class="app-base-info android">
                <div class="pic-view">
                    <div class="pic">
                        <img class="tpl-app-logo" src="" alt=""/>
                    </div>
                    <h4 class="tpl-app-name"></h4>
                </div>
                <div class="pic-view">
                    <div class="pic qrcode"></div>
                    <h4>二维码下载</h4>
                </div>
            </div>
            <div class="app-base-info ios">
                <div class="pic-view">
                    <div class="pic">
                        <img class="tpl-app-logo" src="" alt=""/>
                    </div>
                    <h4 class="tpl-app-name"></h4>
                </div>
                <div class="pic-view">
                    <div class="pic qrcode"></div>
                    <h4>二维码下载</h4>
                </div>
            </div>
            <div class="edit-form">
                <table class="form-table">
                    <tbody><tr>
                        <th scope="row"><label for="appKey">APP_KEY</label></th>
                        <td><input name="appKey" type="text" id="appKey" value="" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="appSecret">APP_SECRET</label></th>
                        <td><input name="appSecret" type="text" id="appSecret" value="" class="regular-text"></td>
                    </tr>
                    </tbody></table>
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="保存更改"></p>
            </div>
            <table class="wp-list-table widefat plugins">
                <thead>
                <tr>
                    <th scope="col" id="name" class="manage-column column-name" style="">功能</th>
                    <th scope="col" id="description" class="manage-column column-description" style="">功能描述</th>
                </tr>
                </thead>

                <tbody id="the-list">
                <tr class="active" id="menu_info">
                    <td class="plugin-title"><strong>菜单管理</strong>
                        <div class="row-actions visible">
                            <a class="tpl-link" href="javascript:;">设置</a> |
                            <a class="tpl-switch" href="javascript:;" title="停用该插件">停用</a>
                        </div>
                    </td>
                    <td class="column-description">
                        菜单管理可以帮助您设置在移动端APP上的菜单展现，如图所示:<br>
                        <img id="image-simple_img" src="" alt=""/>
                    </td>
                </tr>
                </tbody>

                <tbody>
			<tr class="active" id="extend_info">
                <td class="plugin-title"><strong>推广设置</strong>
                    <div class="row-actions visible">
                        <a class="tpl-link" href="javascript:;">设置</a> 
                    </div>
                </td>
                <td class="column-description">
                我们为您准备了一个推广页面的模板，只要经过简单的设置，就可以将该页面作为您站点应用的推广页面进行宣传，<br>页面上将会提供下载链接以及二维码，方便各类设备进行下载安装。
                    <img src="" alt=""/>
            </tr>
                </td>
            	</tr>
                </tbody>
            <!--<tr class="inactive" data-slug="akismet">-->
            <!--<td class="plugin-title"><strong>导航管理</strong>-->
            <!--<div class="row-actions visible">-->
            <!--<span class="0"><a href="javascript:;">设置</a> | </span>-->
            <!--<span class="deactivate"><a href="javascript:;" title="停用该插件">停用</a></span>-->
            <!--</div>-->
            <!--</td>-->
            <!--<td class="column-description desc">-->
            </table>

        </div>
</div>
<script>
    (function($){
        $(function(){
            $("#image-simple_img").attr('src',_bigapp_obj.data.menu_info.simple_img);
            uz.drawIndex(_bigapp_obj.data,_bigapp_obj.ajax_url);
        });
    })(jQuery)

</script>
