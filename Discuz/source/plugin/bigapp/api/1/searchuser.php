<?php
/**
* @file searchuser.php
* @Brief 
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/

if (!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$body = array(
		'mod' => 'user',
		'formhash' => $_GET['formhash'],
		'srchtype' => 'title',
		'srhfid' => 0,
		'srhlocality' => 'home::spacecp',
		'srchtxt' => $_GET['keyword'],
		'searchsubmit' => 'true',
		);
if(is_null($body['formhash']) || is_null($body['srchtxt'])){
	@bigapp_core::result(array('error' => 'param_error'));	
}
if(function_exists('iconv')){
	$body['srchtxt'] = iconv('UTF-8', BIGAPP_CHARSET . '//ignore', $body['srchtxt']);
}else{
	$body['srchtxt'] = mb_convert_encoding($body['srchtxt'], BIGAPP_CHARSET, 'UTF-8');
}
$proto = 'http://';
if(isset($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']){
	$proto = 'https://';
}
if(isset($_SERVER['SCRIPT_NAME'])){
	$url = $proto . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
}else{
	$url = $proto . $_SERVER['HTTP_HOST'];
}
$url = str_replace('api/mobile/iyz_index.php', '', $url);
$url = str_replace('source/plugin/bigapp/iyz_index.php', '', $url);
$url = rtrim($url, '/') . '/search.php?searchsubmit=yes';
$ch = curl_init();
$opt = array(
		CURLOPT_URL     => $url,
		CURLOPT_POST    => 1,
		CURLOPT_POSTFIELDS => http_build_query($body),
		CURLOPT_HEADER  => 1,
		CURLOPT_RETURNTRANSFER  => 1,
		CURLOPT_TIMEOUT         => 5,
		CURLOPT_NOBODY			=> 0,
		CURLOPT_HTTP_VERSION	=> CURL_HTTP_VERSION_1_0,
		);
$cookieStr = '';
foreach ($_COOKIE as $key => $value){
	if(empty($cookieStr)){
		$cookieStr = $key . '=' . urlencode($value);
	}else{
		$cookieStr = $cookieStr . ';' . $key . '=' . urlencode($value);
	}
}
curl_setopt($ch, CURLOPT_COOKIE, $cookieStr);
curl_setopt_array($ch, $opt);
$data = @curl_exec($ch);
curl_close($ch);
$start = stripos($data, 'location: ');
if(false === $start){
	@bigapp_core::result(array('error' => 'search_failed'));
}
$start += strlen('location: ');
$end = strpos($data, "\r\n", $start);
if(false === $end){
	@bigapp_core::result(array('error' => 'search_failed'));
}
$locUrl = substr($data, $start, $end - $start);
if(false === ($start2 = stripos($locUrl, 'home.php?'))){
	@bigapp_core::result(array('error' => 'search_failed'));
}
$queryString = substr($locUrl, $start2 + strlen('home.php?'));
parse_str($queryString, $arrParam);
foreach ($arrParam as $k => $v){
	$_GET[$k] = $v;
}
unset($_GET['charset']);
include_once 'home.php';

class BigAppAPI {
	function common() {
	}

	function output() {
		//最多展示100个
		global $_G;
		$ret['user_list']  = @bigapp_core::getvalues($GLOBALS['list'], array('/^\d+$/'), array('uid', 'username', 'adminid', 'groupid'));
		foreach ($ret['user_list'] as &$user){
			$user['avatar'] = avatar($user['uid'], 'big', true);
			$user['groupname'] = 'unknown';
			if(isset($_G['cache']['usergroups'][$user['groupid']]['grouptitle'])){
				$user['groupname'] = strip_tags($_G['cache']['usergroups'][$user['groupid']]['grouptitle']);
			}
		}
		$ret['user_list'] = array_values($ret['user_list']);
		unset($user);
		@bigapp_core::result(bigapp_core::variable($ret));
	}
}

?>
