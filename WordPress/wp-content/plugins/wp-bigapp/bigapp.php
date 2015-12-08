<?php
/**
 * Plugin Name: WP-BigApp
 * Description: JSON-based APP API for WordPress.
 * Version: 1.0.0
 * Author: YZ bigapp Team
 * Author URI: http://bigapp.mob.com/
 * Plugin URI: http://bigapp.mob.com/
 */

/*
    Copyright (c) 2015 youzu-bigapp (email:bigapp@youzu.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/
define( 'YZ_BIG_APP_VERSION', '1.0.0' );
define( 'YZ_BIG_INNER_VERSION', '6142' );
define( 'YZ_BIG_LAST_MODIFICATION', ' 2015-10-09 15:19' );
define( 'YZ_APP_API_VERSION', '1' );
define( 'YZ_APP_MINIMUM_WP_VERSION','2.0' );
define( 'YZ_APP_API_DEBUG', true );
define( 'YZ_APP_API_RESOURCE',false);       //资源版本号

if ( !function_exists( 'add_action' ) ) {
	echo __lan('Hi there!  I\'m just a plugin, not much I can do when called directly.');
	exit;
}


if(!defined('BIGAPP_ROOT')){
    define( 'BIGAPP_ROOT', dirname(__FILE__) );
}
if(!defined('BIGAPP_FOLDER')){
    define ('BIGAPP_FOLDER',basename(BIGAPP_ROOT));
}
if(!defined('BIGAPP_URL')){
    define ('BIGAPP_URL',plugin_dir_url(BIGAPP_FOLDER).BIGAPP_FOLDER.'/');
}

require_once( BIGAPP_ROOT.'/bigapp.class.php');

function wp_json_validate_auth_login(){
    add_filter( 'determine_current_user', 'wp_json_validate_auth_login',0);
}

register_activation_hook( __FILE__, array('mobileplugin_bigapp','bigapp_json_api_activation' ) );

register_deactivation_hook( __FILE__, array("mobileplugin_bigapp",'bigapp_json_api_deactivation' ) );

add_action( 'init', array('mobileplugin_bigapp','init' ) );

add_action( 'init', array('mobileplugin_bigapp','bigapp_json_api_maybe_flush_rewrites'), 999 );

add_action( 'wp_json_server_before_serve', array('mobileplugin_bigapp','bigapp_json_api_default_filters'), 10, 1 );


add_action( 'template_redirect', array("mobileplugin_bigapp",'bigapp_json_api_loaded'), -100 );

function json_api_wp_version_warning() {
    echo "<div id=\"json-api-warning\" class=\"updated fade\"><p>Sorry, YZ Json API requires WordPress version ".YZ_APP_MINIMUM_WP_VERSION." or greater.</p></div>";
}   

add_action( 'login_redirect', array('mobileplugin_bigapp','bigapp_auth_login'),10,3);
add_action( 'registration_redirect', array('mobileplugin_bigapp','bigapp_auth_register'));

//设置show_in_json 控制项
add_action( 'registered_post_type', array('mobileplugin_bigapp','json_register_post_type'), 10, 2 );

add_filter( 'json_authentication_errors', array('mobileplugin_bigapp','bigapp_json_cookie_check_errors'), 100 );

add_action( 'auth_cookie_malformed',    array('mobileplugin_bigapp','bigapp_json_cookie_collect_status' ) );
add_action( 'auth_cookie_expired',      array('mobileplugin_bigapp','bigapp_json_cookie_collect_status' ) );
add_action( 'auth_cookie_bad_username', array('mobileplugin_bigapp','bigapp_json_cookie_collect_status' ) );
add_action( 'auth_cookie_bad_hash',     array('mobileplugin_bigapp','bigapp_json_cookie_collect_status' ) );
add_action( 'auth_cookie_valid',        array('mobileplugin_bigapp','bigapp_json_cookie_collect_status' ) );
//头像
add_filter('get_avatar',array('mobileplugin_bigapp','bigapp_json_api_get_avatar') ) ;
//admin menu
if ( is_admin() ) {  
    require_once dirname(__FILE__) . "/lib/common/template.inc.php";
    add_action('admin_menu',array('BigAppAdmin','init'));
}
BigAppAdmin::init_api_routes();
//国际化部分
add_action( 'plugins_loaded', array('mobileplugin_bigapp','bigapp_localize'));
