<?php
/***************************************************************************
 * Copyright (c) 2015 youzu.com, Inc. All Rights Reserved
 **************************************************************************/
 
/**
 * @file admin_model.class.php
 * @author bigapp(@youzu.com)
 * @date 2015/07/16 14:01:04
 * 插件后台管理界面的逻辑部分 
 **/
class BigAppAdminModel{
    /**
     * 获取plugin的基本信息
     */
    public static function get_plugin_base_info(){
        $plugin_info['name'] = __lan("bigapp");
        $plugin_info['version'] = YZ_BIG_APP_VERSION;
        $inner_version = YZ_BIG_INNER_VERSION;
        if(YZ_BIG_INNER_VERSION == "__INNER_EDITION__"){
            $inner_version = '';
        }
        $plugin_info['inner_version'] = $inner_version;
        $modtime = YZ_BIG_LAST_MODIFICATION;
        if(YZ_BIG_LAST_MODIFICATION == "__LAST_MODIDICATION__"){
            $modtime = '';
        }
        $plugin_info['updatetime'] = $modtime;
        $plugin_info['bigapp_home_url'] = BigAppConf::$bigapp_home_url;
        $plugin_info['app_infos'] = array();
        $verify_info  = self::get_bigapp_ak_info();
        if(isset($verify_info['ak'])){
            $ak = $verify_info['ak'];
            $param['app_key'] = $ak;
            $param['timestamp'] = time();
            $param['sign'] = '';
            $param['expire'] = 30;
            $app_infos = curl_info(BigAppConf::$mc_url_app_version_info,$param);
            if($app_infos && isset($app_infos['data']['version_info'])){
                $version_infos = $app_infos['data']['version_info'];
                foreach($version_infos as $version_info){
                    $plugin_info['app_infos'][] = array(
                        'name'=>$version_info['app_name'],
                        'logo'=>$version_info['icon_image'],
                        'url'=>'http://',
                        'dcode_url'=>$version_info['downlink'],
                        'type'=>$version_info['os_type'],
                    );  //get info from api,参数array(url=>host,type=>wordpress)
                }
            }
        }
        return $plugin_info;
    }

    /**
     * 获取菜单的配置信息 
     */
    public static function get_menu_conf(){
        $data = array();
        $taxonomies = new WP_JSON_Taxonomies(); 
        //taxonomies
        $nav_menus_list = $taxonomies->get_nav_menus();
        if($nav_menus_list){ //taxonomies second level infos
            foreach ($nav_menus_list as &$menu){
                $menu['item_list'] = $taxonomies->get_nav_menu($menu['ID']);        //如果没有子菜单，前端可以显示不可选
            }
        }
        $data['nav_menus']['list'] = $nav_menus_list;
        //get conf from db
        $bigapp_page_alias = BigAppConf::$page_alias; 
        $menu_confs = get_option(BigAppConf::$option_menu_conf,array());
        if(!$menu_confs){
            $menu_confs = array();
        }else{ 
            $menu_confs = sort_by_key(json_decode($menu_confs ,true),'rank');
        }
        $data['menu_confs'] = $menu_confs;
        $data['opt_url'] = admin_url( "admin.php?page=$bigapp_page_alias&action=banner") ;    //跳转到banner设置页面的url
        return $data;
    }
    /**
     * 获取menu的基本信息
     */
    public static function get_menu_info(){
        $bigapp_page_alias = BigAppConf::$page_alias; 
        $data['menu_switch'] = intval(get_option(BigAppConf::$option_menu_switch));    //1:ON,0:OFF
        $data['opt_url'] = admin_url( "admin.php?page=$bigapp_page_alias&action=menu") ;
        $data['simple_img'] = get_plugin_site_base()."/admin/img/12.jpg";
        return $data;
    }
	 /**
     * 获取推广的基本信息
     */
    public static function get_extend_info(){
        $bigapp_page_alias = BigAppConf::$page_alias; 
        $data['opt_url'] = admin_url( "admin.php?page=$bigapp_page_alias&action=extend") ;
        return $data;
    }
	
	/**
     * 获取推广的配置信息
     */
    public static function get_extend_conf(){
        $bigapp_page_alias = BigAppConf::$page_alias; 
		
		 $svalue  = get_option('bigapp_extend_setting');
		 $data  = json_decode($svalue, true);
		 
		 if($data !== null) {
			 foreach ($data as &$item) {
				$item = str_replace("#u","\\u", $item);
			 }
		 }
		 
		 if (!$data["iosurl"]) $data["iosurl"] = "";
		 if (!$data["appdesc"]) $data["appdesc"] = "";
		 $data['ajax_url'] = get_bloginfo('siteurl').'/?yz_app=1&api_route=admin_api&action=mobile_api';
		 //$data["mobileurl"] = admin_url( "options-general.php?page=$bigapp_page_alias&action=mobile_page");
        $data["mobileurl"] = get_bloginfo('siteurl').'/?yz_app=1&api_route=admin_api&action=mobile_page';
        return $data;
    }
	
    /**
     * 获取banner基本信息
     */
    public static function get_banner_conf($menu_id){
        $bigapp_page_alias = BigAppConf::$page_alias; 
        $banner_conf = json_decode(get_option(BigAppConf::$option_banner_conf),true);
        $data['banner_list'] = array();
        if(isset($banner_conf[$menu_id])){
            $data['banner_list'] = sort_by_key($banner_conf[$menu_id],'rank');
        }
        $data['opt_url'] = admin_url( "admin.php?page=$bigapp_page_alias&action=menu");       //返回链接
        return $data;
    }
    /**
     * 获取公告内容
     */
    public static function get_notice_info(){
        $use_api_notice = false;
        $notice = __lan("欢迎使用BigApp，如果您在使用过程中遇到任何问题，请随时与我们联系，我们将终身提供免费技术支持服务，<strong>QQ: 2510709749；免费电话: 4006852216；</strong><br><br>
            请先前往<a href='http://bigapp.youzu.com' target='_blank'> BigApp应用中心 </a> 进行认证，并生成您的App！");
        if($use_api_notice == true){
            $notice_info = curl_info("http://bigapp.youzu.com/mc/mcapi/getNotice");
            if($notice_info && $notice_info['error_code'] == 0){
                $notice = $notice_info['data']['notice']?$notice_info['data']['notice']:$notice;
            }
        }
        return $notice;
    }
    /**
     * 获取ak sk
     */
    public static function get_bigapp_ak_info(){
        $st = array();
        $data  = get_option(BigAppConf::$option_ak_sk);
        if($data){
            $st = json_decode($data,true);
        }
        return $st;
    }


}



/* vim: set ts=4 sw=4 sts=4 tw=100 @qiong*/
?>
