<?php
/***************************************************************************
 * Copyright (c) 2015 youzu.com, Inc. All Rights Reserved
 **************************************************************************/
 
/**
 * @file json_url.inc.php
 * @author bigapp(@youzu.com)
 * @date 2015/07/08 11:38:31
 *  
 **/
function get_json_url_posts_list($post_id =0,$param=array() ){
    if($post_id == 0){
        return json_url('posts','get_posts',$param);
    }
    return json_url('posts','get_post',array('id'=>$post_id));
}
function get_json_url_posts_create(){
    return json_url('posts','create_post');
}
function get_json_url_post_edit_by_id($post_id){
    return json_url('posts','edit_post',array('id'=>$post_id));
}
function get_json_url_post_delete_by_id($post_id){
    return json_url('posts','delete_post',array('id'=>$post_id));
}
function get_json_url_post_get_revisions($post_id){
    return json_url('posts','get_revisions',array('id'=>$post_id));
}
function get_json_url_post_get_post_type($type=null){
    if($type === null){
        return json_url('posts','get_post_types');
    }
    return json_url('posts','get_post_type',array("type"=>$type));
}
function get_json_url_post_get_post_statuses($status=null){
    if($status === null){
        return json_url('posts','get_post_statuses');
    }
    return json_url('posts','get_post_statuse',array('status'=>$status));
}
function get_json_url_taxonomies_get_taxonomies(){
    return json_url('taxonomies','get_taxonomies');
}
function get_json_url_users_get_user($user_id = 0){
    if($user_id == 0){
        return json_url('users','get_users');
    }
    return json_url('users','get_user',array("id"=>$user_id));
}
function get_json_url_users_get_posts($user_id){
    return json_url('users','get_posts',array("id"=>$user_id));
}
function get_json_url_comments_get_comments($post_id,$comment_id = 0){
    if($comment_id == 0){
        return json_url('comments','get_comments',array("id"=>$post_id));
    }
    return json_url('comments','get_comment',array("id"=>$post_id,"comment"=>$comment_id));
}
function get_json_url_comments_delete_comment($post_id,$comment_id ){
    return json_url('comments','delete_comment',array("id"=>$post_id,"comment"=>$comment_id));
}
function get_json_url_meta_get_meta($post_id,$mid=0){
    if($mid == 0){
        return json_url('meta','get_all_meta',array("id"=>$post_id));
    }
    return json_url('meta','get_all_meta',array("id"=>$post_id,'mid'=>$mid));
}
function get_json_url_meta_add_meta($post_id){
    return json_url('meta','add_meta',array("id"=>$post_id));
}
function get_json_url_meta_update_meta($post_id,$mid){
    return json_url('meta','add_meta',array("id"=>$post_id,"mid"=>$mid));
}
function get_json_url_meta_delete_meta($post_id,$mid){
    return json_url('meta','delete_meta',array("id"=>$post_id,"mid"=>$mid));
}
function get_json_url_media_get_posts($post_id = 0){
    if($post_id == 0){
        return json_url('media','get_posts');
    }
    return json_url('media','get_post',array("id"=>$post_id));
}
function get_json_url_media_edit_post($post_id){
    return json_url('media','edit_post',array("id"=>$post_id));
}
function get_json_url_media_delete_post($post_id){
    return json_url('media','delete_post',array("id"=>$post_id));
}
function get_json_url_media_get_comments($post_id){
    return json_url('media','get_comments',array("id"=>$post_id));
}
function get_json_url_media_get_revisions($post_id){
    return json_url('media','get_revisions',array("id"=>$post_id));
}
function get_json_url_taxonomy_term($action,$param= array()){
    return json_url('taxonomies',$action,$param);
}
/**
 * Get URL to a JSON endpoint.
 *
 * @param string $path   Optional. JSON route. Default empty.
 * @param string $scheme Optional. Sanitization scheme. Default 'json'.
 * @return string Full URL to the endpoint.
 */
function json_url( $path = '',$action='',$param=array(), $scheme = 'json' ) {
	return get_json_url( null, $path, $action,$param,$scheme );
}
/**
 * Get URL to a JSON endpoint on a site.
 *
 * @todo Check if this is even necessary
 *
 * @param int    $blog_id Blog ID.
 * @param string $path    Optional. JSON route. Default empty.
 * @param string $scheme  Optional. Sanitization scheme. Default 'json'.
 * @return string Full URL to the endpoint.
 */
//返回json的url，如果path为空的话，返回http://192.168.180.23:8080/wordpress/wp-json/
function get_json_url( $blog_id = null, $path = '',$action='',$param= array(), $scheme = 'json' ) {
	if ( false && get_option( 'permalink_structure' ) ) {        //如果用户自定义链接,该设置会返回值
		$url = get_home_url( $blog_id, mobileplugin_bigapp::plugin_get_url_prefix(), $scheme );
		if ( ! empty( $path ) && is_string( $path ) && strpos( $path, '..' ) === false )
			$url .= '/' . ltrim( $path, '/' );
	} else {
		$url = trailingslashit( get_home_url( $blog_id, '', $scheme ) );

		if ( empty( $path ) ) {
			$path = 'index';
		} else {
			$path = trim( $path, '/' );
		}
        $action = empty($action)?'index':trim($action,'/');
		$url = add_query_arg( BigAppConf::$app_prefix, 1, $url );
		$url = add_query_arg( BigAppConf::$route_prefix, $path, $url );
		$url = add_query_arg( BigAppConf::$action_prefix, $action, $url );
        if ( !empty($param)){
		    $url .= "&". http_build_query($param);
        }
	}
	

	/**
	 * Filter the JSON URL.
	 *
	 * @since 1.0
	 *
	 * @param string $url     JSON URL.
	 * @param string $path    JSON route.
	 * @param int    $blod_ig Blog ID.
	 * @param string $scheme  Sanitization scheme.
	 */
	return apply_filters( 'json_url', $url, $path, $blog_id, $scheme );
}






/* vim: set ts=4 sw=4 sts=4 tw=100 @qiong*/
?>
