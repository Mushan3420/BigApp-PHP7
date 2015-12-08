<?php
/***************************************************************************
 * Copyright (c) 2015 youzu.com, Inc. All Rights Reserved
 **************************************************************************/
 
/**
 * @file bigapp.class.php
 * @author bigapp(@youzu.com)
 * @date 2015/07/07 14:09:15
 *  
 **/
require_once dirname(__FILE__) . '/conf/conf.inc.php';  
require_once dirname(__FILE__) . '/lib/json/header-json.php';  
require_once dirname(__FILE__) . '/lib/common/util.inc.php';  
require_once dirname(__FILE__) . '/lib/common/common.class.php';  
require_once dirname(__FILE__) . '/lib/bigapp_core.class.php';  
require_once dirname(__FILE__) . "/lib/common/json_url.inc.php";
require_once dirname(__FILE__) . "/admin/bigapp-admin.class.php";
require_once dirname(__FILE__) . '/api/'.YZ_APP_API_VERSION.'/bigapp_api.php';  
class mobileplugin_bigapp {
    /**
     * init
     */
    public static function init(){
        global $wp;
        $wp->add_query_var( BigAppConf::$route_prefix );
        $wp->add_query_var( BigAppConf::$app_prefix );

        self::bigapp_register_rewrites();
    }

    /**
     * add rewrite rules
     */
    public static function bigapp_register_rewrites(){
        add_rewrite_rule( '^' . self::plugin_get_url_prefix() . '/?$','index.php?api_route=','top' );
        add_rewrite_rule( '^' . self::plugin_get_url_prefix() . '/(.*)?','index.php?api_route=$matches[1]','top' );
    }

    public static function plugin_get_url_prefix() {
        return apply_filters( 'plugin_get_url_prefix', BigAppConf::$app_prefix );
    }

    /**
     * Determine if the rewrite rules should be flushed.
     */
    public static function bigapp_json_api_maybe_flush_rewrites() {
        $version = get_option( 'bigapp_json_api_plugin_version', null );
        if ( empty( $version ) ||  $version !== YZ_BIG_APP_VERSION) {
            flush_rewrite_rules();
            update_option( 'bigapp_json_api_plugin_version', YZ_BIG_APP_VERSION);
        }
    }
    /**
     * Load the JSON API.
     * @todo Extract code that should be unit tested into isolated methods such as
     *       the wp_json_server_class filter and serving requests. This would also
     *       help for code re-use by `wp-json` endpoint. Note that we can't unit
     *       test any method that calls die().
     * /home/work/webroot/wordpress/wp-includes/template-loader.php:1
     * 在所有数据初始化完成时，给一个机会，让我们去获取数据
     * 在本插件中，直接获取所有数据，然后json，最后die，不再返回
     */
    public static function bigapp_json_api_loaded() {
        if ( empty( $GLOBALS['wp']->query_vars[BigAppConf::$app_prefix] ) )
            return;

        /**
         * Whether this is a XML-RPC Request.
         *
         * @var bool
         * @todo Remove me in favour of JSON_REQUEST
         */
        define( 'XMLRPC_REQUEST', false );

        /**
         * Whether this is a JSON Request.
         *
         * @var bool
         */
        define( 'JSON_REQUEST', true );
        //快速评论
        add_action('comment_flood_trigger',array('mobileplugin_bigapp','bigapp_json_api_flood_comment'));
        //重复评论的钩子
        add_action('comment_duplicate_trigger', array('mobileplugin_bigapp','bigapp_json_api_duplicate_comment') );

        //缩略图,初始化
        global $bigapp_support_thumb;
        if( version_compare($GLOBALS['wp_version'],BigAppConf::$thumbnail_wp_version) >= 0 && BigAppConf::$thumbnail_support == true ){
            $thumbnail_size_w = get_option('thumbnail_size_w');
            if( get_theme_support('post-thumbnails') && $thumbnail_size_w > 0 ){
                #add_theme_support( 'post-thumbnails' ,array('post','page')); 
                #add_image_size("list",BigAppConf::$thumb_width,BigAppConf::$thumb_height,true);
                #add_image_size("recom",BigAppConf::$thumb_width,BigAppConf::$thumb_height,true);
                #set_post_thumbnail_size( BigAppConf::$thumb_width,BigAppConf::$thumb_height,true);  //post-thumbnails
                $bigapp_support_thumb = true;
            }
        }

        //初始化
        global $bigapp_common;
        $bigapp_common = Bigapp_Common::getInstance();

        global $wp_json_server;


        // Allow for a plugin to insert a different class to handle requests.
        //允许替换json服务器
        $wp_json_server_class = apply_filters( 'wp_json_server_class', 'WP_JSON_Server' );

        $wp_json_server = new $wp_json_server_class;

        /**
         * Fires when preparing to serve an API request.
         *
         * Endpoint objects should be created and register their hooks on this
         * action rather than another action to ensure they're only loaded when
         * needed.
         *
         * @param WP_JSON_ResponseHandler $wp_json_server Response handler object.
         */
        //初始化所有默认的和各个类型相关的、json勾子
        do_action( 'wp_json_server_before_serve', $wp_json_server );
        // Fire off the request.
        $wp_json_server->serve_request( $GLOBALS['wp']->query_vars[BigAppConf::$route_prefix] );
        // We're done.
        die();
    }

    /**
     * Register the default JSON API filters.
     *
     * @internal This will live in default-filters.php
     *
     * @global WP_JSON_Posts      $wp_json_posts
     * @global WP_JSON_Pages      $wp_json_pages
     * @global WP_JSON_Media      $wp_json_media
     * @global WP_JSON_Taxonomies $wp_json_taxonomies
     *
     * @param WP_JSON_ResponseHandler $server Server object.
     */
    //将所有默认的过滤器加入进去
    //在本文件json-api-loaded函数中调用
    public static function bigapp_json_api_default_filters( $server ) {
        global $wp_json_posts, $wp_json_pages, $wp_json_media, $wp_json_taxonomies, $wp_json_auth;

        // Posts.
        $wp_json_posts = new WP_JSON_Posts( $server );
        add_filter( 'json_endpoints', array( $wp_json_posts, 'register_routes' ), 0 );
        add_filter( 'json_prepare_taxonomy', array( $wp_json_posts, 'add_post_type_data' ), 10, 3 );

        // Users.
        $wp_json_users = new WP_JSON_Users( $server );
        add_filter( 'json_endpoints',       array( $wp_json_users, 'register_routes'         ), 0     );
        add_filter( 'json_prepare_post',    array( $wp_json_users, 'add_post_author_data'    ), 10, 3 );
        add_filter( 'json_prepare_comment', array( $wp_json_users, 'add_comment_author_data' ), 10, 3 );

        // Auth.
        $wp_json_auth = new WP_JSON_Auth( $server );
        add_filter( 'json_endpoints', array( $wp_json_auth, 'register_routes' ), 0 );

        // Pages.
        $wp_json_pages = new WP_JSON_Pages( $server );
        $wp_json_pages->register_filters();

        // Post meta.
        $wp_json_post_meta = new WP_JSON_Meta_Posts( $server );
        add_filter( 'json_endpoints',    array( $wp_json_post_meta, 'register_routes'    ), 0 );
        add_filter( 'json_prepare_post', array( $wp_json_post_meta, 'add_post_meta_data' ), 10, 3 );
        add_filter( 'json_insert_post',  array( $wp_json_post_meta, 'insert_post_meta'   ), 10, 2 );

        // Media.
        $wp_json_media = new WP_JSON_Media( $server );
        add_filter( 'json_endpoints',       array( $wp_json_media, 'register_routes'    ), 1     );
        add_filter( 'json_prepare_post',    array( $wp_json_media, 'add_thumbnail_data' ), 10, 3 );
        add_filter( 'json_pre_insert_post', array( $wp_json_media, 'preinsert_check'    ), 10, 3 );
        add_filter( 'json_insert_post',     array( $wp_json_media, 'attach_thumbnail'   ), 10, 3 );
        add_filter( 'json_post_type_data',  array( $wp_json_media, 'type_archive_link'  ), 10, 2 );

        // Posts.
        $wp_json_taxonomies = new WP_JSON_Taxonomies( $server );
        add_filter( 'json_endpoints',      array( $wp_json_taxonomies, 'register_routes'       ), 2 );
        add_filter( 'json_post_type_data', array( $wp_json_taxonomies, 'add_taxonomy_data' ), 10, 3 );
        add_filter( 'json_prepare_post',   array( $wp_json_taxonomies, 'add_term_data'     ), 10, 3 );

        //Favorite
        $wp_json_favorite = new WP_JSON_Favorite($server);
        add_filter( 'json_endpoints',   array($wp_json_favorite,'register_routes') ,3);

        // Deprecated reporting.
        add_action( 'deprecated_function_run',      'json_handle_deprecated_function', 10, 3 );
        add_filter( 'deprecated_function_trigger_error', '__return_false'                         );
        add_action( 'deprecated_argument_run',      'json_handle_deprecated_argument', 10, 3 );
        add_filter( 'deprecated_argument_trigger_error', '__return_false'                         );

        // Default serving
        add_filter( 'json_serve_request',   'bigapp_json_send_cors_headers' );
        add_filter( 'json_pre_dispatch',  array('mobileplugin_bigapp','bigapp_json_handle_options_request'), 10, 2 );
        //auth route
        $api = new bigapp_server_api();
        add_filter( 'json_endpoints',   array( $api, 'register_routes'  ), 10 );
    }
    /**
     * Handle OPTIONS requests for the server
     *
     * This is handled outside of the server code, as it doesn't obey normal route
     * mapping.
     *
     * @param mixed $response Current response, either response or `null` to indicate pass-through
     * @param WP_JSON_Server $handler ResponseHandler instance (usually WP_JSON_Server)
     * @return WP_JSON_ResponseHandler Modified response, either response or `null` to indicate pass-through
     */
    //接受OPTION HTTP METHOD，根据URI返回该URI接受什么HTTP请求
    public static function bigapp_json_handle_options_request( $response, $handler ) {
        if ( ! empty( $response ) || $handler->method !== 'OPTIONS' ) {
            return $response;
        }

        $response = new WP_JSON_Response();

        $accept = array();

        $handler_class = get_class( $handler );
        $class_vars = get_class_vars( $handler_class );
        $map = $class_vars['method_map'];

        foreach ( $handler->get_routes() as $route => $endpoints ) {
            $match = preg_match( '@^' . $route . '$@i', $handler->path, $args );

            if ( ! $match ) {
                continue;
            }

            foreach ( $endpoints as $endpoint ) {
                foreach ( $map as $type => $bitmask ) {
                    if ( $endpoint[1] & $bitmask ) {
                        $accept[] = $type;
                    }
                }
            }
            break;
        }
        $accept = array_unique( $accept );

        $response->header( 'Accept', implode( ', ', $accept ) );

        return $response;
    }
    //login
    public static function bigapp_auth_login($redirect_to, $requested_redirect_to, $user){
		
		if ( !isset($_GET[BigAppConf::$app_prefix]) )
            return $redirect_to;

        $route_prefix = BigAppConf::$route_prefix;
		
        if(isset($_GET[$route_prefix]) && $_GET[$route_prefix] == 'logout'){
            bigapp_core::yz_auth_logout();
        }

        if(isset($_GET[$route_prefix]) && $_GET[$route_prefix] == 'register'){
            bigapp_core::yz_auth_register();
        }

        if(isset($_GET[$route_prefix]) && $_GET[$route_prefix] == 'getcaptcha'){
            bigapp_core::yz_auth_getcaptcha();
        }

        $result = array("error_code"=>0,"error_msg"=>"success","data"=>array());
        if ( !is_wp_error($user) ){
            $result['data'] = array(
                'name' => $user->ID,
                'id' => $user->ID,
                'nice_name' => $user->user_nicename,
                'email' => $user->user_email,
                'reg_time' => strtotime($user->user_registered),
                'status' => $user->user_status,
                'display_name' => $user->display_name,
                'roles' => $user->roles,
				'logout_url' => wp_create_nonce('logout'),
            );
        }else{
            $error = $user->get_error_code();
            $result['error_code'] = $error;
            $result['error_msg']  = str_replace('<strong>', '', $user->get_error_messages());
            $result['error_msg']  = str_replace('</strong>', '', $result['error_msg']);
            $result['error_msg']  = preg_replace('/<a href="(.*?)">/', '', $result['error_msg']);
            $result['error_msg']  = str_replace('</a>', '', $result['error_msg']);
        }

        bigapp_core::set_response($result);
    }
	
	public static function bigapp_auth_register(){
        if(isset($_GET['yz_app']) && $_GET['yz_app'] == 1){
		    bigapp_core::yz_auth_register();
        }
    }
	
	
    /**
     * Register routes and flush the rewrite rules on activation.
     *
     * @param bool $network_wide ?
     */
    //在启用勾子时使用，主要是将重写规则打入DB和缓存中
    public static function bigapp_json_api_activation( $network_wide ) {
        if ( version_compare( $GLOBALS['wp_version'], YZ_APP_MINIMUM_WP_VERSION, '<' ) ) {
            add_action('admin_notices', 'json_api_wp_version_warning');
            return;
        }
        if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {
            $mu_blogs = wp_get_sites();

            foreach ( $mu_blogs as $mu_blog ) {
                switch_to_blog( $mu_blog['blog_id'] );

                self::bigapp_register_rewrites();
                update_option( 'bigapp_json_api_plugin_version', YZ_BIG_APP_VERSION);
            }

            restore_current_blog();
        } else {
            self::bigapp_register_rewrites();
            update_option( 'bigapp_json_api_plugin_version', YZ_BIG_APP_VERSION);
        }
    }
    /**
     * Flush the rewrite rules on deactivation.
     *
     * @param bool $network_wide ?
     */
    //禁用插件，主要是删除重写规则
    public static function bigapp_json_api_deactivation( $network_wide ) {
        if ( function_exists( 'is_multisite' ) && is_multisite() && $network_wide ) {

            $mu_blogs = wp_get_sites();

            foreach ( $mu_blogs as $mu_blog ) {
                switch_to_blog( $mu_blog['blog_id'] );
                delete_option( 'bigapp_json_api_plugin_version' );
            }

            restore_current_blog();
        } else {
            delete_option( 'bigapp_json_api_plugin_version' );
        }
    }
    /**
     * Add 'show_in_json' {@see register_post_type()} argument.
     *
     * Adds the 'show_in_json' post type argument to {@see register_post_type()}.
     * This value controls whether the post type is available via API endpoints,
     * and defaults to the value of $publicly_queryable.
     *
     * @global array $wp_post_types Post types list.
     *
     * @param string   $post_type Post type to register.
     * @param stdClass $args      Post type arguments.
     */
    ///home/work/webroot/wordpress/wp-settings.php:354行开始
    //在初始化时，会调用本勾子，初始化wp_post_types
    //在每个具体的处理函数中会进行判断，如果不允许show_in_json，那么则报错，说明不允许json展示，直接拒绝
    //目前会传入的值恒定如下>>>>post>>>>page>>>>attachment>>>>revision>>>>nav_menu_item 
    /*
     * */
    public static function json_register_post_type( $post_type, $args ) {
        global $wp_post_types;

        $type = &$wp_post_types[ $post_type ];

        // Exception for pages.
        if ( $post_type === 'page' ) {
            $type->show_in_json = true;
        }

        // Exception for revisions.
        if ( $post_type === 'revision' ) {
            $type->show_in_json = true;
        }

        // Default to the value of $publicly_queryable.
        if ( ! isset( $type->show_in_json ) ) {
            $type->show_in_json = $type->publicly_queryable;
        }
    }
    /**
     * Check for errors when using cookie-based authentication.
     *
     * WordPress' built-in cookie authentication is always active
     * for logged in users. However, the API has to check nonces
     * for each request to ensure users are not vulnerable to CSRF.
     *
     * @global mixed $wp_json_auth_cookie
     *
     * @param WP_Error|mixed $result Error from another authentication handler,
     *                               null if we should handle it, or another
     *                               value if not
     * @return WP_Error|mixed|bool WP_Error if the cookie is invalid, the $result,
     *                             otherwise true.
     */
    public static function bigapp_json_cookie_check_errors( $result ) {
        //为空或false时，本函数才执行，否则原样返回
        if ( ! empty( $result ) ) {
            return $result;
        }

        global $wp_json_auth_cookie;

        /*
         * Is cookie authentication being used? (If we get an auth
         * error, but we're still logged in, another authentication
         * must have been used.)
         */
        //如果cookie校验未通过，但是用户已经处于登录状态了，那么证明result已经被处理过了，也原样返回
        if ( $wp_json_auth_cookie !== true && is_user_logged_in() ) {
            return $result;
        }

        // Is there a nonce?
        //执行原生的除cookie之外的校验，如果不存在，则返回true，用户设置为未登录
        //如果出错，则设置为出错
        $nonce = null;
        if ( isset( $_REQUEST['_wp_json_nonce'] ) ) {
            $nonce = $_REQUEST['_wp_json_nonce'];
        } elseif ( isset( $_SERVER['HTTP_X_WP_NONCE'] ) ) {
            $nonce = $_SERVER['HTTP_X_WP_NONCE'];
        }

        if ( $nonce === null ) {
            // No nonce at all, so act as if it's an unauthenticated request.
            #wp_set_current_user( 0 );
            return true;
        }

        // Check the nonce.
        $result = wp_verify_nonce( $nonce, 'wp_json' );
        if ( ! $result ) {
            return new WP_Error( 'json_cookie_invalid_nonce', __( 'Cookie nonce is invalid' ), array( 'status' => 403 ) );
        }

        return true;
    }

    /**
     * Collect cookie authentication status.
     *
     * Collects errors from {@see wp_validate_auth_cookie} for
     * use by {@see json_cookie_check_errors}.
     *
     * @see current_action()
     * @global mixed $wp_json_auth_cookie
     */
    //只有cookie校验正常，才会将wp_json_auth_cookie设置为true
    //其他情况，wp_json_auth_cookie会被设置成auth_cookie_XXXXX中的XXXXX
    public static function bigapp_json_cookie_collect_status() {
        global $wp_json_auth_cookie;

        $status_type = current_action();

        if ( $status_type !== 'auth_cookie_valid' ) {
            $wp_json_auth_cookie = substr( $status_type, 12 );
            return;
        }

        $wp_json_auth_cookie = true;
    }
    /**
     * 检测到重复评论的时候，会进入这个接口
     */
    public static function bigapp_json_api_duplicate_comment($comment_data){
        $result = array("error_code"=>BigAppErr::$comment['code'],
            "error_msg"=> __('Duplicate comment detected; it looks as though you&#8217;ve already said that!'),
            "data"=>__lan('dup comment'));
        bigapp_core::set_response($result);
    }
    /**
     * 检测到评论过快的时候,会进入该接口
     */
    public static function bigapp_json_api_flood_comment($time_lastcomment=0,$time_newcomment=0){
        $result = array("error_code"=>BigAppErr::$comment['code'],
            "error_msg"=> __('You are posting comments too quickly. Slow down.'),
            "data"=>__lan('dup comment'));
        bigapp_core::set_response($result);
    }
    /**
     * 定制化头像
     */
    public static function bigapp_json_api_get_avatar($avatar, $id_or_email='', $size=array(), $default='', $alt= array(), $args=array() ){
        //判定用户是否有特定的头像
        $myavatar = get_image_avatar_by_id_or_email($id_or_email);
        $avatar = sprintf(
            "<img alt='%s' src='%s'  class='%s' height='%d' width='%d' %s/>",
            esc_attr( $args['alt'] ),
            esc_url( $myavatar),
            "class",
            50,50,
            $args['extra_attr']
        );
        return apply_filters('bigapp_get_avatar_filter', $avatar, $id_or_email, $size, $default, $alt);
    }
    /**
     * 国际化,可自定义语言映射
     */
    public static function bigapp_json_api_languages($translations, $text='', $domain=''){
        if($domain == BigAppConf::$languages_prefix){
            return "琼爷";
        }
        return $translations;
    }
    /**
     * 本地化 
     */
    public static function bigapp_localize() {
        load_plugin_textdomain( BigAppConf::$languages_prefix,false, dirname(plugin_basename(__FILE__)).'/languages');
    }
}


/* vim: set ts=4 sw=4 sts=4 tw=100 @qiong*/
?>
