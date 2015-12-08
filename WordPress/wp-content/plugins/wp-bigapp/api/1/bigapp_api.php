<?php
/***************************************************************************
 * Copyright (c) 2015 youzu.com, Inc. All Rights Reserved
 **************************************************************************/
 
/**
 * @file bigapp_server_api.php
 * @author bigapp(@youzu.com)
 * @date 2015/07/15 19:51:29
 *  
 **/
class bigapp_server_api {
    protected $route = "bigapp_api";   
    //register routes
    public function register_routes( $routes ) { 
        $routes[ $this->route] = array(
            "get_auth" =>array( array($this,"get_bigapp_ak_info"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),  
            "get_env" =>array( array($this,"get_plugin_info"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),  
            "get_conf" =>array( array($this,"get_base_conf"),WP_JSON_Server::CREATABLE | WP_JSON_Server::READABLE),  

            );
       return $routes; 
    }

    /**
     * 获取已经发布的最后一个版本的信息。
     */
    public function get_app_info(){


    }
    /**
     * bigapp 站长中心，负责调用该接口。返回填写的ak sk信息
     */
    public function get_bigapp_ak_info(){
        $st = false;
        $data  = get_option("bigapp_ak_sk");
        if($data){
            $st['verify_info'] = md5($data);    //array('ak'=>,'sk'=>'') to json
        }
        return $st;
    }
    /**
     * bigapp 站长中心调用该接口.返回插件信息已经wordpress信息.
     * html:ture 返回html页面.false 返回json
     * return array()
     */
    public function get_plugin_info($html=false){
        $env = array();
        $env['name'] = get_bloginfo();
        $env['url'] = get_bloginfo('wpurl');
        $env['siteurl'] = get_bloginfo('siteurl');
        $env['charset'] = get_bloginfo('charset');
        $env['version'] = get_bloginfo('version');
        $env['os'] = PHP_OS;
        $env['plugin_version'] = YZ_BIG_APP_VERSION;
        $env['api_version'] = YZ_APP_API_VERSION;
        $env['is_plugin_active'] = is_plugin_active('wp-bigapp/bigapp.php');    //鸡肋,因为被停用,该路由也挂了.
        $env['php_version'] = PHP_VERSION;
        $curl_st = 'OK';
		if (!extension_loaded('curl')) {
			$curl_st = "curl extension close";
		} else {
			$func_str = '';
			if (!function_exists('curl_init')) {
				$func_str .= "curl_init() ";
			} 
			if (!function_exists('curl_setopt')) {
				$func_str .= "curl_setopt() ";
			} 
			if (!function_exists('curl_exec')) {
				$func_str .= "curl_exec()";
			} 
            if ($func_str){
                $curl_st = $func_str." 被禁用";
            }
        }
        $env['curl'] = $curl_st;      //是否打开CURL
        $tmp = function_exists('gd_info') ? gd_info() : array();
        $env['gdversion'] = isset($tmp['GD Version']) ? $tmp['GD Version']:'not install';       //gd 库版本

        $data['env_info'] = $env;
        if($html){
            echo json_encode($data);
            exit;
        }
        return $data;
    }
    /**
     * 获取基本配置
     * 供端上调用
     * 主要配置包括:
     * 1,是否需要注册
     * 2,是否需要升级,以及升级类型.
     */
    public function get_base_conf(){
        $conf = array();
        $conf['users_can_register'] = get_option("users_can_register"); //用户能否注册
        $thread_comments = get_option('thread_comments');
        $conf['thread_comments'] = $thread_comments?$thread_comments:0; //是否开启嵌套评论
        $data  = get_option("bigapp_ak_sk");
        $app_key = '';
        if($data){
            $verify_info = json_decode($data,true);
            $app_key = $verify_info['ak'];
        }
		//open socail login 
		$osop = get_option('osop');
		$conf['wechat_login'] = (isset($osop['WECHAT']) && $osop['WECHAT'] == 1)?"1":"1";//hack
        #$conf['version_api_url'] = BigAppConf::$mc_url_app_version_info;
        return $conf;
    }
}


/* vim: set ts=4 sw=4 sts=4 tw=100 @qiong*/
?>
