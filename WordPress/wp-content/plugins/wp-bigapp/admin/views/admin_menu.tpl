<script type="text/javascript"> var _bigapp_obj = {bigapp_data}; </script>
<div class="wrap control-section wp-admin wp-core-ui js  nav-menus-php auto-fold admin-bar branch-4-2 version-4-2-2 admin-color-fresh locale-zh-cn customize-support svg menu-max-depth-0" id="wpbody-content">
    <div id="uz">
        <h2>插件名称</h2>
        <ul class="subsubsub">
            <li>插件版本号：V<span class="tpl-version"></span> |</li>
            <li>最后更新日期：<span class="tpl-updatetime"></span> | </li>
            <li>内部版本号：<span class="tpl-innerversion"></span> | </li>
            <li><a href="http://bigapp.youzu.com" target="_blank">BigApp应用中心</a> | </li>
            <li><a href="javascript:history.back();">返回</a></li>
        </ul>
        <div class="clear"></div>
        <div id="nav-menus-frame">
            <div id="menu-settings-column" class="metabox-holder">
                <div class="accordion-container">
                    <div class="accordion-section">
                        <h3 class="accordion-section-title">添加菜单</h3>

                        <div class="accordion-section-content" style="display: block;">
                            <select name="menu">
                            </select>
                            <div id="taxonomy-category" class="taxonomydiv">
                                <!-- /.tabs-panel -->

                                <p class="button-controls">
                                    <span class="add-to-menu">
                                        <input type="submit" class="button-secondary submit-add-to-menu right" value="添加至菜单" name="add-taxonomy-menu-item" id="submit-taxonomy-category">
                                        <span class="spinner"></span>
                                    </span>
                                </p>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <div id="menu-management-liquid">
                <div id="menu-management">
                    <div class="menu-edit">

                        <div class="nav-menu-header">
                            <div class="major-publishing-actions">
                                <h3>APP菜单设置</h3>

                                <div class="publishing-action">
                                    <div class="button button-primary menu-save">保存菜单</div>
                                </div>
                                <!-- END .publishing-action -->
                            </div>
                            <!-- END .major-publishing-actions -->
                        </div>
                        <!-- END .nav-menu-header -->

                        <div id="post-body">
                            <div id="post-body-content">
                                <table class="wp-list-table widefat fixed striped posts">
                                    <thead>
                                    <tr>
                                        <th scope="col" class="manage-column check-column">删除</th>
                                        <th scope="col" class="manage-column">排序</th>
                                        <th scope="col" class="manage-column">名称</th>
                                        <th scope="col" class="manage-column">App菜单名称</th>
                                        <th scope="col" class="manage-column">操作</th>
                                    </tr>
                                    </thead>

                                    <tbody id="the-list">

                                    </tbody>

                                    <tfoot>
                                    <tr>
                                        <th scope="col" class="check-column">
                                            <input id="cb-select-all" type="checkbox">

                                        </th>
                                        <th><label class="" for="cb-select-all">全选</label></th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                        <th>&nbsp;</th>
                                    </tr>
                                    </tfoot>

                                </table>

                            </div>
                            <!-- /#post-body-content -->
                        </div>
                        <!-- /#post-body --><!-- /#update-nav-menu -->

                        <div class="nav-menu-header">
                            <div class="major-publishing-actions">
                                <h3>APP菜单设置</h3>

                                <div class="publishing-action">
                                    <div class="button button-primary menu-save">保存菜单</div>
                                </div>
                                <!-- END .publishing-action -->
                            </div>
                            <!-- END .major-publishing-actions -->
                        </div>
                        <!-- END .nav-menu-header -->

                    </div>
                </div>
                <!-- /#menu-management -->
            </div>
        </div>

    </div>
</div>
<script>
    jQuery(function () {
        uz.setMenu(_bigapp_obj.data, _bigapp_obj.ajax_url);
    });
</script>
