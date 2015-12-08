<?php
/***************************************************************************
 * Copyright (c) 2015 youzu.com, Inc. All Rights Reserved
 **************************************************************************/
 
/**
 * @file conf.inc.php
 * @author bigapp(@youzu.com)
 * @date 2015/07/07 13:56:24
 *  
 **/
class BigAppConf{
    public static $debug = false;
    public static $app_prefix = 'yz_app';                               //yz 标识
    public static $route_prefix = 'api_route';                          //route
    public static $action_prefix = 'action';                            //action
    public static $thumbnail_support = true;                            //缩略图开关，可以提供给站长配置
    public static $thumb_width = 50;                                    //缩略图的宽
    public static $thumb_height = 50;
    public static $thumbnail_wp_version = '2.9';                        //缩略图要求的最低版本
    
    public static $languages_prefix = 'wp-bigapp';                //多语言的domain
    public static $page_alias = 'bigapp_admin';                         //页面的别名
    public static $img_size = array(                                    //图片要求限制
        "banner"=>array('width'=>750,'height'=>380,'size'=>0),
        );
    //一些数据库的key配置
    public static $option_menu_switch = "bigapp_menu_switch";           //菜单开关 0关闭 1 打开
    public static $option_menu_conf = "bigapp_menu_conf";               //菜单配置
    public static $option_banner_conf = "bigapp_banner_conf";           //banner配置
    public static $option_favorite_switch = "bigapp_favorite_switch";   //收藏开关0 关闭,1打开
    public static $option_ak_sk = "bigapp_ak_sk";                       //验证信息key

    //mc 配置
    public static $bigapp_home_url = "http://bigapp.youzu.com";                 //站长中心地址
    //获取线上发布的最新版本的信息
    public static $mc_url_app_version_info = "http://bigapp.youzu.com/mc/mcapi/getLineVersion";
    public static $taskInfoUrl = 'http://bigapp.youzu.com/mc/mcapi/get_latest';	
    public static $appInfoUrl = 'http://bigapp.youzu.com/mc/mcapi/get_basic';
    public static $releaseApis = array(
		 'latest_package' => 'http://bigapp.youzu.com/mc/mcapi/get_latest_package',
    );

}
class BigAppErr{
    public static $sys_err = array('code'=>1,
                    'msg'=>'sys err');          //系统错误
    public static $php_err= array('code'=>2,
                    'msg'=>'php err');          //代码错误

    public static $server = array('code'=>24,
                    'msg'=>'server faild');     //json-server部分的错误码
    public static $comment = array('code'=>25,
                    'msg'=>'comment faild');    //评论部分的错误码
    public static $post = array('code'=>26,
                    'msg'=>'post faild');       //文章部分的错误码
    public static $taxonomy = array('code'=>27,
                    'msg'=>'taxonomy faild');   //分类部分的错误码
    public static $favorite = array('code'=>28,
                    'msg'=>'favorite faild');   //收藏部分的错误码
    public static $user = array('code'=>29,
                    'msg'=>'user faild');       //用户部分的错误码
    public static $login = array('code'=>30,
                    'msg'=>'user need login');  //用户需要登录的码

}

/* vim: set ts=4 sw=4 sts=4 tw=100 @qiong*/
?>
