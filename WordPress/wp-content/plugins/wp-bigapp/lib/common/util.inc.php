<?php
/***************************************************************************
 * Copyright (c) 2015 youzu.com, Inc. All Rights Reserved
 **************************************************************************/
 
/**
 * @file util.inc.php
 * @author bigapp(@youzu.com)
 * @date 2015/07/07 20:03:32
 *  
 **/
/**
 * Send Cross-Origin Resource Sharing headers with API requests
 *
 * @param mixed $value Response data
 * @return mixed Response data
 */
require_once dirname(__FILE__)."/bksvr.inc.php";
function bigapp_json_send_cors_headers( $value ) {
    $origin = get_http_origin();
    if ( $origin ) {
        header( 'Access-Control-Allow-Origin: ' . esc_url_raw( $origin ) );
        header( 'Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE' );
        header( 'Access-Control-Allow-Credentials: true' );
    }
    return $value;
}
/**
 * Handle {@see _deprecated_function()} errors.
 *
 * @param string $function    Function name.
 * @param string $replacement Replacement function name.
 * @param string $version     Version.
 */
function json_handle_deprecated_function( $function, $replacement, $version ) {
    if ( ! empty( $replacement ) ) {
        $string = sprintf( __('%1$s (since %2$s; use %3$s instead)'), $function, $version, $replacement );
    }
    else {
        $string = sprintf( __('%1$s (since %2$s; no alternative available)'), $function, $version );
    }

    header( sprintf( 'X-WP-DeprecatedFunction: %s', $string ) );
}

/**
 * Handle {@see _deprecated_function} errors.
 *
 * @param string $function    Function name.
 * @param string $replacement Replacement function name.
 * @param string $version     Version.
 */
function json_handle_deprecated_argument( $function, $message, $version ) {
    if ( ! empty( $message ) ) {
        $string = sprintf( __('%1$s (since %2$s; %3$s)'), $function, $version, $message );
    }
    else {
        $string = sprintf( __('%1$s (since %2$s; no alternative available)'), $function, $version );
    }

    header( sprintf( 'X-WP-DeprecatedParam: %s', $string ) );
}

if ( ! function_exists( 'json_last_error_msg' ) ):
/**
 * Returns the error string of the last json_encode() or json_decode() call
 *
 * @internal This is a compatibility function for PHP <5.5
 *
 * @return boolean|string Returns the error message on success, "No Error" if no error has occurred, or FALSE on failure.
 */
function json_last_error_msg() {
	// see https://core.trac.wordpress.org/ticket/27799
	if ( ! function_exists( 'json_last_error' ) ) {
		return false;
	}

	$last_error_code = json_last_error();

    show_debug($last_error_code,__FILE__,__LINE__);
	// just in case JSON_ERROR_NONE is not defined
	$error_code_none = defined( 'JSON_ERROR_NONE' ) ? JSON_ERROR_NONE : 0;

	switch ( true ) {
		case $last_error_code === $error_code_none:
			return 'No error';

		case defined( 'JSON_ERROR_DEPTH' ) && JSON_ERROR_DEPTH === $last_error_code:
			return 'Maximum stack depth exceeded';

		case defined( 'JSON_ERROR_STATE_MISMATCH' ) && JSON_ERROR_STATE_MISMATCH === $last_error_code:
			return 'State mismatch (invalid or malformed JSON)';

		case defined( 'JSON_ERROR_CTRL_CHAR' ) && JSON_ERROR_CTRL_CHAR === $last_error_code:
			return 'Control character error, possibly incorrectly encoded';

		case defined( 'JSON_ERROR_SYNTAX' ) && JSON_ERROR_SYNTAX === $last_error_code:
			return 'Syntax error';

		case defined( 'JSON_ERROR_UTF8' ) && JSON_ERROR_UTF8 === $last_error_code:
			return 'Malformed UTF-8 characters, possibly incorrectly encoded';

		case defined( 'JSON_ERROR_RECURSION' ) && JSON_ERROR_RECURSION === $last_error_code:
			return 'Recursion detected';

		case defined( 'JSON_ERROR_INF_OR_NAN' ) && JSON_ERROR_INF_OR_NAN === $last_error_code:
			return 'Inf and NaN cannot be JSON encoded';

		case defined( 'JSON_ERROR_UNSUPPORTED_TYPE' ) && JSON_ERROR_UNSUPPORTED_TYPE === $last_error_code:
			return 'Type is not supported';

		default:
			return 'An unknown error occurred';
	}
}
endif;


/**
 * Parse an RFC3339 timestamp into a DateTime.
 *
 * @param string $date      RFC3339 timestamp.
 * @param bool   $force_utc Force UTC timezone instead of using the timestamp's TZ.
 * @return DateTime DateTime instance.
 */
function json_parse_date( $date, $force_utc = false ) {
	if ( $force_utc ) {
		$date = preg_replace( '/[+-]\d+:?\d+$/', '+00:00', $date );
	}

	$regex = '#^\d{4}-\d{2}-\d{2}[Tt ]\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}(?::\d{2})?)?$#';

	if ( ! preg_match( $regex, $date, $matches ) ) {
		return false;
	}

	return strtotime( $date );
}

/**
 * Get a local date with its GMT equivalent, in MySQL datetime format.
 *
 * @param string $date      RFC3339 timestamp
 * @param bool   $force_utc Whether a UTC timestamp should be forced.
 * @return array|null Local and UTC datetime strings, in MySQL datetime format (Y-m-d H:i:s),
 *                    null on failure.
 */
function json_get_date_with_gmt( $date, $force_utc = false ) {
	$date = json_parse_date( $date, $force_utc );

	if ( empty( $date ) ) {
		return null;
	}

	$utc = date( 'Y-m-d H:i:s', $date );
	$local = get_date_from_gmt( $utc );

	return array( $local, $utc );
}

/**
 * Parses and formats a MySQL datetime (Y-m-d H:i:s) for ISO8601/RFC3339
 *
 * Explicitly strips timezones, as datetimes are not saved with any timezone
 * information. Including any information on the offset could be misleading.
 *
 * @param string $date_string
 *
 * @return mixed
 */
function json_mysql_to_rfc3339( $date_string ) {
	$formatted = mysql2date( 'c', $date_string, false );

	// Strip timezone information
	return preg_replace( '/(?:Z|[+-]\d{2}(?::\d{2})?)$/', '', $formatted );
}

/**
 * Retrieve the avatar url for a user who provided a user ID or email address.
 *
 * {@see get_avatar()} doesn't return just the URL, so we have to
 * extract it here.
 *
 * @param string $email Email address.
 * @return string URL for the user's avatar, empty string otherwise.
*/
function json_get_avatar_url( $email ) {
    $show_avatars = get_option('show_avatars',0);
    if($show_avatars == 1){
        $avatar_html = get_avatar( $email );        //如果装了wp-user-avatar插件,会优先使用其插件
        if($avatar_html){
            // Strip the avatar url from the get_avatar img tag.
            preg_match('/src=["|\'](.+)[\&|"|\']/U', $avatar_html, $matches);
            if ( isset( $matches[1] ) && ! empty( $matches[1] ) ) {
                return esc_url_raw( $matches[1] );
            }
        }
    }
	return '';
}

/**
 * Get the timezone object for the site.
 *
 * @return DateTimeZone DateTimeZone instance.
 */
function json_get_timezone() {
	static $zone = null;

	if ( $zone !== null ) {
		return $zone;
	}

	$tzstring = get_option( 'timezone_string' );

	if ( ! $tzstring ) {
		// Create a UTC+- zone if no timezone string exists
		$current_offset = get_option( 'gmt_offset' );
		if ( 0 == $current_offset ) {
			$tzstring = 'UTC';
		} elseif ( $current_offset < 0 ) {
			$tzstring = 'Etc/GMT' . $current_offset;
		} else {
			$tzstring = 'Etc/GMT+' . $current_offset;
		}
	}
	$zone = new DateTimeZone( $tzstring );

	return $zone;
}



/**
 * Ensure a JSON response is a response object.
 *
 * This ensures that the response is consistent, and implements
 * {@see WP_JSON_ResponseInterface}, allowing usage of
 * `set_status`/`header`/etc without needing to double-check the object. Will
 * also allow {@see WP_Error} to indicate error responses, so users should
 * immediately check for this value.
 *
 * @param WP_Error|WP_JSON_ResponseInterface|mixed $response Response to check.
 * @return WP_Error|WP_JSON_ResponseInterface WP_Error if present, WP_JSON_ResponseInterface
 *                                            instance otherwise.
 */
function json_ensure_response( $response ) {
	if ( is_wp_error( $response ) ) {
		return $response;
	}

	if ( $response instanceof WP_JSON_ResponseInterface ) {
		return $response;
	}

	return new WP_JSON_Response( $response );
}

/**
 * Check if we have permission to interact with the post object.
 *
 * @param WP_Post $post Post object.
 * @param string $capability Permission to check.
 * @return boolean Can we interact with it?
 */
function json_check_post_permission( $post, $capability = 'read' ) {
	$permission = false;
	$post_type = get_post_type_object( $post['post_type'] );

	switch ( $capability ) {
		case 'read' :
			if ( ! $post_type->show_in_json ) {
                show_debug("post_type not show_in_json:post_id:".$post['ID']." post_type:".$post['post_type'],__FILE__,__LINE__);
				return false;
			}

			if ( 'publish' === $post['post_status'] || current_user_can( $post_type->cap->read_post, $post['ID'] ) ) {
				$permission = true;
			}

			// Can we read the parent if we're inheriting?
			if ( 'inherit' === $post['post_status'] && $post['post_parent'] > 0 ) {
				$parent = get_post( $post['post_parent'], ARRAY_A );
                show_debug($parent,__FILE__,__LINE__);

				if ( json_check_post_permission( $parent, 'read' ) ) {
					$permission = true;
				}
			}

			// If we don't have a parent, but the status is set to inherit, assume
			// it's published (as per get_post_status())
			if ( 'inherit' === $post['post_status'] ) {
				$permission = true;
			}
            show_debug("post_permisson:".$permission." post_id:".$post['ID']." post_type:".$post['post_type']." post_status:".$post['post_status'],__FILE__,__LINE__);
			break;

		case 'edit' :
			if ( current_user_can( $post_type->cap->edit_post, $post['ID'] ) ) {
				$permission = true;
			}
			break;

		case 'create' :
			if ( current_user_can( $post_type->cap->create_posts ) || current_user_can( $post_type->cap->edit_posts ) ) {
				$permission = true;
			}
			break;

		case 'delete' :
			if ( current_user_can( $post_type->cap->delete_post, $post['ID'] ) ) {
				$permission = true;
			}
			break;

		default :
			if ( current_user_can( $post_type->cap->$capability ) ) {
				$permission = true;
			}
	}

	return apply_filters( "json_check_post_{$capability}_permission", $permission, $post );
}
/**
 * 公共的出错函数，返回json格式数据，再退出
 */
function json_error($code,$msg='',$data=''){
    $result = array('error_code'=>$code,
            'error_msg'=>__lan($msg),
            'data'=>$data);
    $req = Bigapp_Common::getInstance();
    $req->setResponse($result);
}
/**
 * 根据id/email 检测是否有自定义头像
 */
function get_image_avatar_by_id_or_email($id_or_email){
    $img_file_prix = WP_CONTENT_DIR.'/uploads/';
    $myavatar_prix = get_home_url().'/wp-content/uploads/';
    $mymd5 = sign64($id_or_email);
    if ( ! file_exists($img_file_prix.$mymd5.'.jpg')){
        $mymd5 = intval($mymd5) % 20;
    }
    $myavatar = $myavatar_prix . $mymd5.".jpg";
    return $myavatar;

}
function sign64($value) {                   
    $str = md5 ( $value, true );
    $high1 = unpack ( "@0/L", $str );
    $high2 = unpack ( "@4/L", $str );
    $high3 = unpack ( "@8/L", $str );
    $high4 = unpack ( "@12/L", $str );
    if(!isset($high1[1]) || !isset($high2[1]) || !isset($high3[1]) || !isset($high4[1]) ) { 
        return false;
    }   
    $sign1 = $high1 [1] + $high3 [1];
    $sign2 = $high2 [1] + $high4 [1];
    $sign = ($sign1 & 0xFFFFFFFF) | ($sign2 << 32);
    return sprintf ( "%u", $sign );
}   
/**
 * 国际化语言包的封装
 */
function __lan($text,$param=null){
    $_text = __($text,BigAppConf::$languages_prefix);
    if($param == null){
        return $_text;
    }else{
        return sprintf($_text,$param); 
    }
}
/**
 * html 模板 解析提取
 * file 模板文件
 * data 模板数据
 * path 模板的路径
 */
function bigapp_get_html($file,$data,$path=''){
    if ($path == ''){
        $path = BIGAPP_ROOT."/admin/views";
    }
    $tpl = new Template($path);
    $tpl->set_file('bigapp_handle',$file);
    $tpl->set_var('bigapp_data',json_encode($data));
    return $tpl->parse("bigapp_out","bigapp_handle");
    #return $tpl->get("bigapp_out");
}

function loadTemplate($tpl, $vars, $tplVars = null, $path = '') {
	$json = json_encode($vars);
	$js_script = '<script type="text/javascript"> _bigapp_obj = eval(\'(' . $json . ")');</script>\n";
	
	if ($path == ''){
        $path = BIGAPP_ROOT."/admin/views/".$tpl;
    }
	
	$content = @file_get_contents($path);
	
	if(false === $content){
		return false;
	}
	
	$CHARSET = get_bloginfo('charset');
	
    if(is_string($content) && strtolower($CHARSET) != 'utf-8' && strtolower($CHARSET) != 'utf8'){
		if(function_exists('iconv')){
			$content = @iconv('UTF-8', 'GBK//ignore', $content);
		}else if(function_exists('mb_convert_encoding')){
			$content = @mb_convert_encoding($content, 'GBK', 'UTF-8');
		}
	}

	$tplVars['js_script'] = $js_script;
	$tplVars['app_charset'] = $CHARSET;
	if(is_array($tplVars)){
		foreach($tplVars as $key => $value){
			$content = str_replace("<%".$key."%>", $value, $content);
			$content = str_replace("<% ".$key." %>", $value, $content);
		}
	}
	
	return $content;
}

/**
 * 对输出的文件做编码
 */
function conver_string_to_output($content){
    global $bigapp_charset;
    $bigapp_charset = $bigapp_charset?$bigapp_charset:get_bloginfo('charset');
    if(is_string($content) && strtolower($bigapp_charset) != 'utf-8' && strtolower($bigapp_charset) != 'utf8'){
        if(function_exists('iconv')){   
            $content = @iconv('UTF-8', $bigapp_charset.'//ignore', $content);
        }else if(function_exists('mb_convert_encoding')){
            $content = @mb_convert_encoding($content, $bigapp_charset, 'UTF-8');
        }
    }
    return $content;
}
function echo_output($content){
    echo conver_string_to_output($content);
}
/**
 * 按照特定的key排序多维数组
 */
function sort_by_key($lists,$row,$sort=SORT_ASC){
    if(!is_array($lists) || empty($lists)){
        return $lists;
    }
    foreach($lists as $key=>$list){
        if (!isset($list[$row])){
            return $lists;
        }
        $param[$key] = $list[$row];
    }
    $st = array_multisort($param,$sort,$lists);
    return $lists;
}
/**
 * 获取图片上传路径
 */
function get_image_upload_path(){
    $upload_dir = wp_upload_dir();
    if($upload_dir['error'] != false){
        return "";
    }
    $upload_dir['basedir'] = rtrim($upload_dir['basedir'],DIRECTORY_SEPARATOR);        //服务器绝对路径

    $upload_dir['baseurl'] = rtrim($upload_dir['baseurl'],DIRECTORY_SEPARATOR);        //服务器绝对路径
    return $upload_dir;
}
/**
 * 图片上传,使用wp的api进行上传。
 * 优点：用db存储。最终可以和媒体打通
 */
function upload_img_by_wp($key='file',$image_size_type = 'banner',$prefix='banner'){
    $ret = array( 'status'=>1,
                'msg' =>'',);
	$name = $_FILES[$file_id]['name'];
    $overrides = array();
    $post_id = 0;
    $ret = check_img($img_size_type,$_FILES[$key]);
    if($ret['status'] == 0){
        $_FILES[$key]['name'] = "banner-".$_FILES[$key]['name'];
        $id = media_handle_upload($key, $post_id,array(),$overrides);
        if ( is_wp_error($id) ) {
            $ret['msg'] = "media_handle_upload failed";
            return $ret;
        }
        $url = '';
        if ( $thumb_url = wp_get_attachment_image_src( $id, 'medium', true ) ){
            $url = $thumb_url[0];
        }
        $ret['data'] = $url;
    }
    return $ret;
}
/**
 * 图片上传
 */
function upload_img($key='upload_img',$img_size_type='banner',$prefix='banner'){
    $ret = array(
        'status'=>1,
        'msg' =>'',);
    $path = '';
    $upload_path = get_image_upload_path();
    if(!$upload_path){
        $ret['msg'] = 'upload_path cant find';
        return $ret;
    }
    $img_base_dir = $upload_path['basedir'];
    $img_base_url = $upload_path['baseurl'];
    $ret = check_img($img_size_type,$_FILES[$key]);
    if($ret['status'] == 0){
        $new_file_name = $prefix.md5($_FILES[$key]['name']).".jpg";
        $path = $img_base_dir.DIRECTORY_SEPARATOR.$new_file_name;
        if(!move_uploaded_file($_FILES[$key]['tmp_name'],$path)){ 
            $ret['msg'] = "move failed";
        }
        $ret['data'] = $img_base_url.DIRECTORY_SEPARATOR.$new_file_name;
    }
    return $ret;
}
function check_img($image_size_type,$file){
    $ret = array( 'status'=>1,
                  'msg' =>'',);
    if (($file["type"] == "image/gif") || ($file["type"] == "image/jpeg")
        || ($file["type"] == "image/pjpeg") || ($file['type'] == 'image/png')){

        if($file['error'] != UPLOAD_ERR_OK ){
            $ret['msg'] = $file['error'];
        }
    }
    $size_info = BigAppConf::$img_size[$image_size];
    $fileSize = $file['size'];
    $tmpFile = $file['tmp_name'];
    if(!is_file($tmpFile) || !is_readable($tmpFile)){
        $ret['msg'] = 'upload_file_failed';
    }
    $info = @getimagesize($tmpFile);
    if(false === $info){
        $ret['msg'] = 'invalid_file_type';
    }
    $width = $info[0];         
    $height = $info[1];        
    $mimeType = $info['mime']; //以实际检查为准 
    if($width < $size_info['width'] || $height < $size_info['height']){
        $ret['msg'] = 'invalid_file_size';
    }
    if($size_info['size'] && $fileSize > $size['size']){  
        $ret['msg'] = 'invalid_file_too_big';
    }
    if($ret['msg'] == ''){
        $ret['status'] = 0;
        $ret['msg'] = 'OK';
    }
    return $ret;
}
/**
 * 发起curl请求
 */
function curl_info($url,$postData= null){
    $info = array();
    $data  = get_option(BigAppConf::$option_ak_sk);
    if($data){
        $ak_sk = json_decode($data,true);
        $bk = new BkSvr($ak_sk['ak'],$ak_sk['sk']);
        $info = $bk->curlInfo($url,$postData);
    }
    return $info;
}

/**
 * 获取后端服务信息
 */
function get_info_from_api($url,$param,$checkRes=true){
    $ak_sk = json_decode(get_option(BigAppConf::$option_ak_sk),true);

    $bk = new BkSvr($ak_sk['ak'],$ak_sk['sk']);
    $info = $bk->getInfo($url,$param,false);
    return $info;
}
function get_post_info_from_api($url,$param,$checkRes=true){
    $ak_sk = json_decode(get_option(BigAppConf::$option_ak_sk),true);

    $bk = new BkSvr($ak_sk['ak'],$ak_sk['sk']);
    $info = $bk->getInfoByPost($url,$param,$checkRes);
    return $info;
}
function addUrlQueryString($inputUrl, $arrParam = array()){
    if(empty($arrParam)){
        return $inputUrl;
    }
    $arrUrl = parse_url($inputUrl);
    if(!$arrUrl || !isset($arrUrl['scheme']) || !isset($arrUrl['host'])){
        return false;
    }
    $url = $arrUrl['scheme'] . '://';
    if(isset($arrUrl['user'])){
        $url .= $arrUrl['user'];
        if(isset($arrUrl['pass'])){
            $url .= ':' . $arrUrl['pass'];
        }
        $url .= '@';
    }
    $url .= $arrUrl['host'];
    if(isset($arrUrl['port'])){
        $url .= ':' . $arrUrl['port'];
    }
    $split = '/?';
    if(isset($arrUrl['path'])){
        $url .= $arrUrl['path'];
        $split = '?';
    }
    $qs = http_build_query($arrParam);
    if(isset($arrUrl['query'])){
        parse_str($arrUrl['query'], $queryArr);
        if(!empty($arrParam)){
            $arrParam = array_merge($queryArr,$arrParam);
            $qs = http_build_query($arrParam);
        }else{
            $qs = $arrUrl['query'];
        }
    }
    $url .= $split . $qs;
    if(isset($arrUrl['fragment'])){
        $url .= '#' . $arrUrl['fragment'];
    }
    return $url;
}
/**
 * 链接转换
 * src:外链,直接pass
 *     内链,通过url路由规则,检测出具体参数
 * type:1,外链
 *      2,文章链接
 *      3,菜单链接
 * return :json api link
 */
function convert_link2json($src,$type=0){
    $dest = $src;
    switch($type){
        case 1:
            return $dest;
            break;
        case 2:
        case 3:
            global $wp_rewrite;
            $rewrite = $wp_rewrite->wp_rewrite_rules();
            if($rewrite != false){   //自定义链接 
                $home_path = trim( parse_url( home_url(), PHP_URL_PATH ), '/' );
                $req_uri = trim(parse_url($src,PHP_URL_PATH),'/');
                $req_uri = preg_replace("|^$home_path|i", '', $req_uri);
                $req_uri = trim($req_uri, '/');
                $matches = null;
                foreach ( (array) $rewrite as $match => $query ) {
                    if ( preg_match("#^$match#", $req_uri, $matches) || preg_match("#^$match#", urldecode($req_uri), $matches) ) {
                        break;
                    }
                }
                if($matches){
                    $query = preg_replace("!^.+\?!", '', $query);
                    $query = addslashes(WP_MatchesMapRegex::apply($query, $matches));
                    parse_str($query,$param);
                    $filter = array();
                    foreach($param as $key=>$value){
                        $filter["filter[$key]"] = $value;
                    }
                    $filter["filter[only_one]"] = 1;
                    $dest = get_json_url_posts_list(0,$filter);
                }
            }
            //默认链接
            $match = "p=([0-9]{1,})";
            if ( preg_match("#$match#", $src, $matches)){
                $dest = get_json_url_posts_list($matches[1]);
            }
            break;
        default:
    }
    return $dest;
}
function check_email($email){
    $status = false;
    if(strlen($email) > 100 ){
        return $status;
    }
    $local_part_pos = strpos ( $email, '@' );
    //找不到@，或者@开头，返回false
    if ($local_part_pos == false) {
        return $status;
    }
    $str_local_part = substr ( $email, 0, $local_part_pos );
    $str_len = strlen ( $str_local_part );
    if ($str_len > 64) {
        return $status;
    }
    $str_domain_name = substr ( $email, $local_part_pos + 1 );
    $str_len = strlen ( $str_domain_name );
    if ($str_len == 0 || $str_len > 100) {
        return $status;
    }
    if (filter_var ( $email, FILTER_VALIDATE_EMAIL ) === false) {
        return $status;
    }
    return true;
}
/**
 * 在浏览器展示debug信息
 */
function show_debug($msg,$file=__FILE__,$line=0){
    $debug = isset($_GET['_debug'])?$_GET['_debug']:0;
    if($debug == 1){
        $info['msg'] = $msg;
        $info['file'] = $file;
        $info['line'] = $line;
        echo json_encode($info);
    }
}
/**
 * 获取插件的url 访问方式
 */
function get_plugin_site_base(){
    $webroot = explode("/", BIGAPP_ROOT);
    $plugin_name = $webroot[sizeof($webroot) - 1];
    $site  = get_bloginfo('siteurl') . '/wp-content/plugins/'. $plugin_name ;
    return $site;
}
/* vim: set ts=4 sw=4 sts=4 tw=100 @qiong*/
?>
