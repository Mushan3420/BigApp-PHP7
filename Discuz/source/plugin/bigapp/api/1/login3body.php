<?php
/**
 * @file forumnav.php
 * @Brief 
 * @author youzu
 * @version 1
 * @date 2015-04-03
 */
if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}
require_once dirname(dirname(dirname(__FILE__))) . '/bigappjson.class.php';
$_GET['mod'] = 'logging';
$_GET['action'] = !empty($_GET['action']) ? $_GET['action'] : 'login';

$params = BigAppAPI::parseParam();
$state = time();
if($params['is_first']){
	$_GET['code'] = BigAppAPI::getOauthCode($params['user_name'], $params['password'], $state);
	$_GET['state'] = $state;
	$_GET['t'] = 'yz';
}else{
	$_GET['nickname'] = $params['nickname'];
	$_GET['code'] = $params['code'];
	$_GET['t'] = 'yz';
}
require_once 'member.php';

class BigAppAPI {
	function common() {
		global $_G;
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
	}
	function output()
	{
		global $_G;
		if(true === BigAppConf::$debug){
			$_G['trace'][] = __CLASS__ . '::' . __FUNCTION__;
		}
		$variable = array();
		$result = bigapp_core::variable($variable);
        if(isset($result['Message']['messagestr'])){
            $result['error'] = $result['Message']['messageval'];
            $result['error_msg'] = $result['Message']['messagestr'];
        }   	
		bigapp_core::result($result);
	}
	function getOauthCode($userName, $password, $state)
	{
		$posts = BigAppConf::$login3BodyInfo;
		unset($posts['passport_url']);
		$posts['state'] = $state;
		$posts['LoginForm'] = array('username' => $userName, 'password' => $password);
		$ch = curl_init();
		$opt = array(
				CURLOPT_URL     => BigAppConf::$login3BodyInfo['passport_url'],
				CURLOPT_POST    => 1,
				CURLOPT_POSTFIELDS => http_build_query($posts),
				CURLOPT_HEADER  => 0,
				CURLOPT_RETURNTRANSFER  => 1,
				CURLOPT_TIMEOUT         => 2,
				CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_0,
				);
		curl_setopt_array($ch, $opt);
		$data = @curl_exec($ch);
		curl_close($ch);
		$ret = @BIGAPPJSON::decode($data, true);
		if(!is_array($ret) || !isset($ret['status'])){
			bigapp_core::result(array('error' => 'passport_error', 'error_msg' => 'access passport failed'));	
		}
		if(0 == $ret['status']){
			bigapp_core::result(array('error' => 'login_failed', 'error_msg' => $ret['message']));
		}
		$url = $ret['msg'];
		$arrUrl = parse_url($url);
		if(!is_array($arrUrl) || !isset($arrUrl['query'])){
			bigapp_core::result(array('error' => 'passport_error', 'error_msg' => 'no query string was found in returned msg'));
		}
		$tmp = explode('&', $arrUrl['query']);
		$code = null;
		foreach ($tmp as $v){
			if(false !== strpos($v, 'code=')){
				$code = str_replace('code=', '', $v);
				break;
			}
		}
		if(is_null($code)){
			bigapp_core::result(array('error' => 'passport_error', 'error_msg' => 'query string was invalid'));
		}
		return $code;
	}

	function parseParam()
	{
		$first = (isset($_REQUEST['is_first']) ? !!$_REQUEST['is_first'] : true);
		if($first){
			if(!isset($_REQUEST['username']) || !isset($_REQUEST['password'])){
				bigapp_core::result(array('error' => 'param_error'));
			}
			return array('is_first' => $first, 'user_name' => $_REQUEST['username'], 'password' => $_REQUEST['password']);
		}
		if(!isset($_REQUEST['code']) || !isset($_REQUEST['nickname'])){
			bigapp_core::result(array('error' => 'param_error'));
		}
		return array('is_first' => $first, 'code' => $_REQUEST['code'], 'nickname' => $_REQUEST['nickname']);
	}
}

?>
