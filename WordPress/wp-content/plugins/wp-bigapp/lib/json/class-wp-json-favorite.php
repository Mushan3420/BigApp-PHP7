<?php
/**
 * refer from https://github.com/hberberoglu
 * 收藏文章功能,两种数据存储:登录存db/vistor 存cookie收藏
 * 如果客户网站安装了WP Favorite Posts,则使用安装的.
 * 如果没有安装,则提供默认的收藏功能.具体功能兼容WP Favorite Posts 插件
 */


//为了兼容依赖插件,取名一致
defined("WPFP_META_KEY")?'':define('WPFP_META_KEY', "wpfp_favorites");
defined('WPFP_USER_OPTION_KEY')?"":define('WPFP_USER_OPTION_KEY', "wpfp_useroptions");
defined('WPFP_COOKIE_KEY')?"":define('WPFP_COOKIE_KEY', "wp-favorite-posts");

// manage default privacy of users favorite post lists by adding this constant to wp-config.php
if ( !defined( 'WPFP_DEFAULT_PRIVACY_SETTING' ) )
    define( 'WPFP_DEFAULT_PRIVACY_SETTING', false );


class WP_JSON_Favorite {
    
	protected $server = null ;
	protected $route = 'favorite';
    protected $favroite_enabled = false;        //依赖的插件 true,可依赖,false不可依赖,使用默认的收藏

	/**
	 * Constructor
     */
	public function __construct(WP_JSON_ResponseHandler $server) {
		$this->server = $server;
        $this->favroite_enabled = $this->checkFavoritePlugin();
	}
    /**
     * 判断客户网站是否安装了WP Favorite Posts
     * return true/false
     */
    public function checkFavoritePlugin(){
        if(function_exists('wp_favorite_posts')){
            return true;
        }
        return false;
    }

	/**
	 * Register the favorite routes
     */
	public function register_routes( $routes ) {
        $favorite_routes = array(
            $this->route => array(
                "add" =>array( array($this,"add_favorite"),WP_JSON_Server::READABLE | WP_JSON_Server::CREATABLE ),
                "list" =>array( array($this,"list_favorites"), WP_JSON_Server::ACCEPT_JSON|WP_JSON_Server::READABLE),
                "remove" =>array( array($this,"remove_favorite"),WP_JSON_Server::CREATABLE| WP_JSON_Server::ACCEPT_JSON|WP_JSON_Server::READABLE),
                "clear" =>array( array($this,"clear_favorites"),WP_JSON_Server::CREATABLE| WP_JSON_Server::ACCEPT_JSON|WP_JSON_Server::READABLE),
            ),
        );
		return array_merge( $routes, $favorite_routes);
    }

    /**
     * 添加收藏接口
     * 参数:post_id
     */
    public function add_favorite($post_id ) {
        if ( empty($post_id) ){
            json_error(BigAppErr::$favorite['code'],BigAppErr::$favorite['msg'],__lan("post id is empty"));
        }
        if($this->favroite_enabled == true){
            wpfp_add_favorite($post_id);
        }else{
            if ( !is_user_logged_in() && get_option(BigAppConf::$option_favorite_switch,0) == 0 ) {       //默认,只要登录,就可以添加
                json_error(BigAppErr::$login['code'],BigAppErr::$login['msg'],__lan("need login"));
            }
            if ($this->do_add_to_list($post_id)) { // added, now?
                do_action('wpfp_after_add', $post_id);
                #if (wpfp_get_option('statistics')) wpfp_update_post_meta($post_id, 1);
            }else{
                json_error(BigAppErr::$favorite['code'],BigAppErr::$favorite['msg'],__lan("dup favorite"));
            }
        }
		$response   = new WP_JSON_Response();
        $response->set_data(true);
        return $response;
    }
    public function do_add_to_list($post_id) {
        if ($this->check_favorited($post_id)){
            return false;
        }
        if (is_user_logged_in()) {
            return $this->_add_to_usermeta($post_id);
        } else {
            return $this->_set_cookie($post_id, "added");
        }
    }

    protected function _add_to_usermeta($post_id) {
        $wpfp_favorites = $this->_get_user_meta();
        $wpfp_favorites[] = $post_id;
        $this->_update_user_meta($wpfp_favorites);
        return true;
    }

    /**
     * 检测用户之前是否收藏过
     * return true:已经收藏  false:未收藏
     */
    public function check_favorited($cid) {
        if (is_user_logged_in()) {
            $favorite_post_ids = $this->_get_user_meta();
            if ($favorite_post_ids){
                foreach ($favorite_post_ids as $fpost_id){
                    if ($fpost_id == $cid){
                        return true;
                    }
                }
            }
        } else {
            $cookies = $this->_get_cookie();
            if ($cookies):
                foreach ($cookies as $fpost_id => $val){
                    if ($fpost_id == $cid){
                        return true;
                    }
                }
            endif;
        }
        return false;
    }

    protected function _get_users_favorites($user = "") {
        $favorite_post_ids = array();

        if (!empty($user)):
            return $this->_get_user_meta($user);
        endif;

        # collect favorites from cookie and if user is logged in from database.
        if (is_user_logged_in()):
            $favorite_post_ids = $this->_get_user_meta();
        else:
            $cookie = $this->_get_cookie();
            if ($cookie):
                foreach ($cookie as $post_id => $post_title) {
                    array_push($favorite_post_ids, $post_id);
                }
            endif;
        endif;
        return $favorite_post_ids;
    }

    /**
     * 获取收藏列表
     * 参数:page 控制分页的
     *      posts_per_page
     */
    public function list_favorites( $filter = array() ) {

        if ( !is_user_logged_in() && get_option(BigAppConf::$option_favorite_switch,0) == 0 ) {       //默认,只要登录,就可以添加
            json_error(BigAppErr::$login['code'],BigAppErr::$login['msg'],__lan("need login"));
        }
        $favorite_post_ids = $this->_get_users_favorites();
        if($favorite_post_ids){
            $post = new WP_JSON_Posts($this->server);
            $filter['_bigapp_post_ids'] = $favorite_post_ids;

            $response = $post->get_posts($filter);
            $lists = $response->get_data();
            foreach($lists as $list){           // 过滤置顶文章
                if(in_array($list['ID'],$favorite_post_ids)){
                    $struct[] = $list;
                }
                $response->set_data($struct);
            }
            return $response;
        }
    }

    public function get_most_favorited_list($limit=5) {
        global $wpdb;
        $query = "SELECT post_id, meta_value, post_status FROM $wpdb->postmeta";
        $query .= " LEFT JOIN $wpdb->posts ON post_id=$wpdb->posts.ID";
        $query .= " WHERE post_status='publish' AND meta_key='".WPFP_META_KEY."' AND meta_value > 0 ORDER BY ROUND(meta_value) DESC LIMIT 0, $limit";
        $results = $wpdb->get_results($query);
    }

    /**
     * 清除所有的收藏
     */
    public function clear_favorites() {
        $status = true;
        if ($this->_get_cookie()){
            foreach ($this->_get_cookie() as $post_id => $val) {
                $this->_set_cookie($post_id, "");
                $this->_update_post_meta($post_id, -1);
            }
        }
        if (is_user_logged_in()) {
            $favorite_post_ids = $this->_get_user_meta();
            if ($favorite_post_ids){
                foreach ($favorite_post_ids as $post_id) {
                    $this->_update_post_meta($post_id, -1);
                }
            }
            if (!delete_user_meta(get_current_user_id(), WPFP_META_KEY)) {
                $status = false;
            }
        }
		$response   = new WP_JSON_Response();
        $response->set_data($status);
        return $response;
    }

    protected function _do_remove_favorite($post_id) {
        if (!$this->check_favorited($post_id))
            return true;

        $a = true;
        if (is_user_logged_in()) {
            $user_favorites = $this->_get_user_meta();
            $user_favorites = array_diff($user_favorites, array($post_id));
            $user_favorites = array_values($user_favorites);
            $this->_update_post_meta($post_id, -1);
            $a = $this->_update_user_meta($user_favorites);
        }
        if ($a) $a = $this->_set_cookie($postid, "");
        return $a;
    }


    protected function _update_user_meta($arr) {
        return update_user_meta(get_current_user_id(),WPFP_META_KEY,$arr);
    }

    protected function _update_post_meta($post_id, $val) {
        $oldval = $this->_get_post_meta($post_id);
        if ($val == -1 && $oldval == 0) {
            $val = 0;
        } else {
            $val = $oldval + $val;
        }
        return add_post_meta($post_id, WPFP_META_KEY, $val, true) or update_post_meta($post_id, WPFP_META_KEY, $val);
    }

    protected function _delete_post_meta($post_id) {
        return delete_post_meta($post_id, WPFP_META_KEY);
    }

    protected function _get_cookie() {
        if (!isset($_COOKIE[WPFP_COOKIE_KEY])) return;
        return $_COOKIE[WPFP_COOKIE_KEY];
    }

    protected function _get_options() {
        return get_option('wpfp_options');
    }

    protected function _get_user_meta($user = "") {
        if (!empty($user)):
            $userdata = get_user_by( 'login', $user );
            $user_id = $userdata->ID;
            return get_user_meta($user_id, WPFP_META_KEY, true);
        else:
            return get_user_meta(get_current_user_id(), WPFP_META_KEY, true);
        endif;
    }

    /**
     * 删除之前收藏的帖子
     */
    public function remove_favorite($post_id ) {
        $status = true;
        if ( empty($post_id) ){
            json_error(BigAppErr::$favorite['code'],BigAppErr::$favorite['msg'],__lan("post id is empty"));
        }
        if($this->favroite_enabled == true){
            wpfp_remove_favorite($post_id);
        }else{
            if ($this->_do_remove_favorite($post_id)) { // removed, now?
                do_action('wpfp_after_remove', $post_id);
                #if (wpfp_get_option('statistics')) wpfp_update_post_meta($post_id, -1);
            }
        }
		$response   = new WP_JSON_Response();
        $response->set_data($status);
        return $response;
    }

    protected function _get_post_meta($post_id) {
        $val = get_post_meta($post_id, WPFP_META_KEY, true);
        if ($val < 0) $val = 0;
        return $val;
    }

    protected function _set_cookie($post_id, $str) {
        $expire = time()+60*60*24*30;
        return setcookie("wp-favorite-posts[$post_id]", $str, $expire, "/");
    }

    protected function _is_user_favlist_public($user) {
        $user_opts = $this->_get_user_options($user);
        if (empty($user_opts)) return WPFP_DEFAULT_PRIVACY_SETTING;
        if ($user_opts["is_wpfp_list_public"])
            return true;
        else
            return false;
    }

    protected function _get_user_options($user) {
        $userdata = get_user_by( 'login', $user );
        $user_id = $userdata->ID;
        return get_user_meta($user_id, WPFP_USER_OPTION_KEY, true);
    }


    protected function _get_option($opt) {
        $wpfp_options = $this->_get_options();
        return htmlspecialchars_decode( stripslashes ( $wpfp_options[$opt] ) );
    }
}
