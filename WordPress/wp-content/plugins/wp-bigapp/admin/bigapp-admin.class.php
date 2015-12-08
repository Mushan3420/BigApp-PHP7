<?php
/***************************************************************************
 * Copyright (c) 2015 youzu.com, Inc. All Rights Reserved
 **************************************************************************/
 
/**
 * @file bigapp-admin.class.php
 * @author bigapp(@youzu.com)
 * @date 2015/07/14 20:00:40
 * bigapp 后台管理主入口
 **/
require_once dirname(dirname(__FILE__)) . '/api/'.YZ_APP_API_VERSION.'/admin_api.php';  
require_once dirname(__FILE__)."/admin_model.class.php";

class BigAppAdmin{
    private static $initiated = false; 
    public static function init(){
        if ( ! self::$initiated ) {
            self::load_menu();
            self::init_hooks();
            self::init_api_routes();
        }
        self::$initiated = true;
    }
    /**
     * init hooks
     */
    public static function init_hooks(){
        self::load_js();
        self::load_css();
        #add_action('admin_enqueue_scripts',array("bigapp_admin",'load_js' ) );  //另一种钩子
    }
    /**
     * 加载js资源
     */
    public static function load_js(){
        $js_dir = dirname(__FILE__)."/js/";
        $js_list = array();
        $deps = array();
        if(is_dir($js_dir)){
            if($dh = opendir($js_dir)){
                while( false !== ($file = readdir($dh))){
                    if($file == '.' || $file == ".."){
                        continue;
                    }
                    if(is_file($js_dir.$file) && stripos($file,'.js')){
                        $js_list[] = $file;
                    }
                }
            }
            $deps = array('jquery');
        }
        foreach($js_list as $js){
            self::_load('js',$js, plugins_url("js/$js", __FILE__),$deps);
        }
    }
    /**
     * 加载css 资源
     */
    public static function load_css(){
        $css_list = array();
        $css_dir = dirname(__FILE__)."/css/";
        if(is_dir($css_dir)){
            if($dh = opendir($css_dir)){
                while( false !== ($file = readdir($dh))){
                    if($file == '.' || $file == ".."){
                        continue;
                    }
                    if(is_file($css_dir.$file) && stripos($file,'.css')){
                        $css_list[] = $file;
                    }
                }
            }
        }
        foreach ($css_list as $css){
            self::_load('css',$css, plugins_url("css/$css", __FILE__));
        }
    }
    public static function _load($type,$flag,$src,$deps=array(),$ver=YZ_APP_API_RESOURCE,$in_footer=true){
        if(is_admin()){
            if($type == 'js'){
                wp_register_script($flag,$src,$deps,$ver,$in_footer);
                wp_enqueue_script($flag);
            }elseif($type == 'css'){
                wp_register_style($flag,$src,$deps,$ver);
                wp_enqueue_style($flag);
            }
        }
    }
    /**
     * 加载api路由
     */
    public static function init_api_routes(){
        $api = new bigapp_admin_api();
        add_filter( 'json_endpoints',   array( $api, 'register_routes'  ), 10 );
    }
    /**
     * 插件管理页面的主入口函数,通过action进行路由
     */
    public static function display_main(){
        $valid_actions = array('main','menu','banner','extend','mobile_page');
        $action =  isset($_REQUEST['action'])?$_REQUEST['action']:'main';
        if(!in_array($action,$valid_actions)){
            $action = 'main';
        }
	
        switch($action){
        case "menu":
            self::display_bigapp_admin_menu_page();
            break;
        case "banner":
            self::display_bigapp_admin_banner_page();
            break;
		 case "extend":
			 self::display_bigapp_admin_extend_page();
			 break;
        default:
            self::display_bigapp_admin_main_page();
        }

    }
    /**
     * 生成插件的管理首页
     */
    public static function display_bigapp_admin_main_page(){
        //plugin base info
        $base_info = BigAppAdminModel::get_plugin_base_info();
        //菜单管理模块信息
        $menu_info = BigAppAdminModel::get_menu_info();
        $extend_info = BigAppAdminModel::get_extend_info();
        //导航管理模块信息
        //todo
        //校验信息,如果为array() 则需要展开校验,否则前端收缩校验区域
        $data['data']['verify_info'] =  BigAppAdminModel::get_bigapp_ak_info();
        //公告
        $data['data']['common_info']['notice'] = BigAppAdminModel::get_notice_info();
        $data['data']['plugin_info'] = $base_info;
        $data['data']['menu_info'] = $menu_info;
        $data['data']['extend_info'] = $extend_info;
        $data['ajax_url']['opt_menu'] = get_bloginfo('siteurl')."/?yz_app=1&api_route=admin_api&action=update_menu_switch"; //设置菜单是否生效的url
        $data['ajax_url']['opt_verify'] = get_bloginfo('siteurl')."/?yz_app=1&api_route=admin_api&action=update_verify_info"; //设置验证信息 
        show_debug($data,__FILE__,__LINE__);
        echo_output( bigapp_get_html('admin_main.tpl',$data));
        ?>
        <?php  
    }
    /**
     * 生成菜单管理页面
     */
    public static function display_bigapp_admin_menu_page(){
        $data['data']['plugin_info'] = BigAppAdminModel::get_plugin_base_info();
        $data['data']['menu_conf'] = BigAppAdminModel::get_menu_conf();
        $data['ajax_url'] = get_bloginfo('siteurl')."/?yz_app=1&api_route=admin_api&action=update_menu_conf";
        show_debug($data,__FILE__,__LINE__);
        echo_output( bigapp_get_html('admin_menu.tpl',$data));
        ?>
        <?php  
    }
    /**
     * 生成banner区的管理页面
     */
    public static function display_bigapp_admin_banner_page(){
        $menu_id = 0;
        if (isset($_REQUEST['menu_id'])){
            $menu_id = $_REQUEST['menu_id'];
        }
        $data['data']['plugin_info'] = BigAppAdminModel::get_plugin_base_info();
        $data['data']['banner_conf'] = BigAppAdminModel::get_banner_conf($menu_id);
        $data['ajax_url'] = get_bloginfo('siteurl')."/?yz_app=1&api_route=admin_api&action=update_banner_conf";
        $data['upload_url'] = get_bloginfo('siteurl')."/?yz_app=1&api_route=admin_api&action=upload_img";
        show_debug($data,__FILE__,__LINE__);
        echo_output( bigapp_get_html('admin_banner.tpl',$data));
        ?>
        <?php  
    }
	/**
     * 推广页区的管理页面
     */
	public static function display_bigapp_admin_extend_page(){
		$js_data = BigAppAdminModel::get_extend_conf();
		
		$page_data['plugin_path'] = get_plugin_site_base().'/admin';
		$page_data['imgUrl'] = get_bloginfo('siteurl')."/?yz_app=1&api_route=admin_api&action=upload_img&key=" . urlencode('mobile_app_image_s');
		
        show_debug($page_data,__FILE__,__LINE__);
		echo_output(loadTemplate('admin_extend.tpl', $js_data, $page_data));
		?>
		<?php
	}
	
    public static function load_menu(){
        //二级菜单:父级菜单，页面的title信息，菜单标题，权限，别名(唯一)，执行函数
        $bigapp_page_alias = BigAppConf::$page_alias; 
        #$hook = add_submenu_page('options-general.php',__lan('bigapp config'),__lan('BigApp'),'manage_options' ,$bigapp_page_alias ,array("BigAppAdmin",'display_main'));
        //一级菜单:页面title，菜单标题，权限，别名，执行函数，菜单图标url，菜单位置(如果两个菜单属性是同一个值，则会发生覆盖)
        add_menu_page(__lan('BigApp'), __('BigApp'), 'manage_options', $bigapp_page_alias, array("BigAppAdmin", 'display_main'), BIGAPP_URL.'/data/images/bigapp_icon.png');
        #add_menu_page( 'title标题', '菜单标题', 'edit_themes', 'ashu_slug','display_function','',6); 
        if ( version_compare( $GLOBALS['wp_version'], '3.3', '>=' ) ) {
            #add_action( "load-$hook", array( 'BigAppAdmin', 'admin_help' ) );
        }             
    }


    /**
     * 头部的帮助菜单
     */
    public static function admin_help(){
        $current_screen = get_current_screen(); 
        if ( current_user_can( 'manage_options' ) ) { 
            $current_screen->add_help_tab(
                array(
                    'id'        => 'account',
                    'title'     => __lan( 'Account' ),
                    'content'   => '<p><strong>' . esc_html(__lan( 'bigapp Configuration' )) . '</strong></p>' ,
                )
            );  
        }
        //帮助的侧边菜单
        $current_screen->set_help_sidebar(
            '<p><strong>' . esc_html(__lan('For more information:')) . '</strong></p>'
        );
    }
}






/* vim: set ts=4 sw=4 sts=4 tw=100 @qiong*/
?>
