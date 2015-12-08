<?php
/**
 * refer from :http://lesterchan.net 
 * Author:leste 'GaMerZ' rchan
 * 文章流览量统计.
 * 如果客户网站安装了 POSTVIEWS,则使用原版本的
 * 本功能,兼容 POSTVIEWS 功能
 * 提供功能列表:
 *	1,浏览量计数
 *	2,获取文章的浏览量
 */

class WP_JSON_PostViews{

	protected $server = null ;
	public function __construct(WP_JSON_ResponseHandler $server) {
		$this->server = $server;
	}
	/**
	 * 判断客户是否安装改插件 
	 */
	public function checkPostViewPlugin(){
        if(function_exists('the_views') && function_exists('process_postviews')){
            return false;
		}
		return false;
	}
	/**
	 *	增加文章的浏览量
	 */
	public function process_postviews($post_id) {
		if($this->checkPostViewPlugin()){
			process_postviews();
			return;
		}
		$post = get_post( $post_id );
		if( !wp_is_post_revision( $post ) ) {
			if(true ||  is_single() || is_page() ) {    //这边条件判断取消掉
				$id = intval( $post->ID );
				$views_options = get_option( 'views_options',array('count'=>1) );
				if ( !$post_views = get_post_meta( $post->ID, 'views', true ) ) {
					$post_views = 0;
				}
				$should_count = false;
				switch( intval( $views_options['count'] ) ) {           //基数的各种条件,everyone/guests only/registered Users Only
				case 0:
					$should_count = true;
					break;
				case 1:
					if(empty( $_COOKIE[USER_COOKIE] ) && intval( $user_ID ) === 0) {
						$should_count = true;
					}
					break;
				case 2:
					if( intval( $user_ID ) > 0 ) {
						$should_count = true;
					}
					break;
				}
				if(isset($views_options['exclude_bots']) && intval( $views_options['exclude_bots'] ) === 1 ) {
					$bots = array
						(
							'Google Bot' => 'googlebot'
							, 'Google Bot' => 'google'
							, 'MSN' => 'msnbot'
							, 'Alex' => 'ia_archiver'
							, 'Lycos' => 'lycos'
							, 'Ask Jeeves' => 'jeeves'
							, 'Altavista' => 'scooter'
							, 'AllTheWeb' => 'fast-webcrawler'
							, 'Inktomi' => 'slurp@inktomi'
							, 'Turnitin.com' => 'turnitinbot'
							, 'Technorati' => 'technorati'
							, 'Yahoo' => 'yahoo'
							, 'Findexa' => 'findexa'
							, 'NextLinks' => 'findlinks'
							, 'Gais' => 'gaisbo'
							, 'WiseNut' => 'zyborg'
							, 'WhoisSource' => 'surveybot'
							, 'Bloglines' => 'bloglines'
							, 'BlogSearch' => 'blogsearch'
							, 'PubSub' => 'pubsub'
							, 'Syndic8' => 'syndic8'
							, 'RadioUserland' => 'userland'
							, 'Gigabot' => 'gigabot'
							, 'Become.com' => 'become.com'
							, 'Baidu' => 'baiduspider'
							, 'so.com' => '360spider'
							, 'Sogou' => 'spider'
							, 'soso.com' => 'sosospider'
							, 'Yandex' => 'yandex'
						);
					$useragent = $_SERVER['HTTP_USER_AGENT'];
					foreach ( $bots as $name => $lookfor ) {
						if ( stristr( $useragent, $lookfor ) !== false ) {
							$should_count = false;
							break;
						}
					}
				}
				if( $should_count && ( ( isset( $views_options['use_ajax'] ) && intval( $views_options['use_ajax'] ) === 0 ) || ( !defined( 'WP_CACHE' ) || !WP_CACHE ) ) ) {
					update_post_meta( $id, 'views', ( $post_views + 1 ) );
					do_action( 'postviews_increment_views', ( $post_views + 1 ) );
				}
			}
		}
	}



	/**
	 * 判断是否需要展示浏览量
	 * return true/false
	 */
	public function should_views_be_displayed($views_options = null) {
		if($this->checkPostViewPlugin()){
			return should_views_be_displayed($views_options);
		}
		if ($views_options == null) {
			$views_options = get_option('views_options');
		}
        return true;
		$display_option = 0;
		if (is_home()) {
			if (array_key_exists('display_home', $views_options)) {
				$display_option = $views_options['display_home'];
			}
		} elseif (is_single()) {
			if (array_key_exists('display_single', $views_options)) {
				$display_option = $views_options['display_single'];
			}
		} elseif (is_page()) {
			if (array_key_exists('display_page', $views_options)) {
				$display_option = $views_options['display_page'];
			}
		} elseif (is_archive()) {
			if (array_key_exists('display_archive', $views_options)) {
				$display_option = $views_options['display_archive'];
			}
		} elseif (is_search()) {
			if (array_key_exists('display_search', $views_options)) {
				$display_option = $views_options['display_search'];
			}
		} else {
			if (array_key_exists('display_other', $views_options)) {
				$display_option = $views_options['display_other'];
			}
		}
		return (($display_option == 0) || (($display_option == 1) && is_user_logged_in()));
	}
	/**
	 * 获取某篇文章的浏览量
	 */
	public Function get_views_by_id($post_id){
		$post_views = intval( get_post_meta( $post_id, 'views', true ) );
		if(!$this->should_views_be_displayed()){
			$post_views = 0;
		}
		$post_views = $this->postviews_round_number($post_views);
		return apply_filters('the_views',$post_views);
	}

	/**
	 * 获取浏览次数最多的文章列表
     * 目前没有用
	 */
	public function get_most_viewed($mode = '', $limit = 10, $chars = 0, $display = true) {
		global $wpdb;
		$views_options = get_option('views_options');
		$where = '';
		$temp = '';
		$output = '';
		if(!empty($mode) && $mode != 'both') {
			if(is_array($mode)) {
				$mode = implode("','",$mode);
				$where = "post_type IN ('".$mode."')";
			} else {
				$where = "post_type = '$mode'";
			}
		} else {
			$where = '1=1';
		}
		$most_viewed = $wpdb->get_results("SELECT DISTINCT $wpdb->posts.*, (meta_value+0) AS views FROM $wpdb->posts LEFT JOIN $wpdb->postmeta ON $wpdb->postmeta.post_id = $wpdb->posts.ID WHERE post_date < '".current_time('mysql')."' AND $where AND post_status = 'publish' AND meta_key = 'views' AND post_password = '' ORDER BY views DESC LIMIT $limit");
		if($most_viewed) {
			foreach ($most_viewed as $post) {
				$post_views = intval($post->views);
				$post_title = get_the_title($post);
				if($chars > 0) {
					$post_title = snippet_text($post_title, $chars);
				}
				$post_excerpt = views_post_excerpt($post->post_excerpt, $post->post_content, $post->post_password, $chars);
			}
		} else {

		}
	}

	/**
	 *  格式化输出,添加单位.K(Thousand),M(Million),B(Billion)
	 */
	public function postviews_round_number( $number, $min_value = 1000, $decimal = 1 ) {
		if( $number < $min_value ) {
			return number_format_i18n( $number );
		}
		$alphabets = array( 1000000000 => 'B', 1000000 => 'M', 1000 => 'K' );
		foreach( $alphabets as $key => $value ){
			if( $number >= $key ) {
				return round( $number / $key, $decimal ) . '' . $value;
			}
		}
	}
}
