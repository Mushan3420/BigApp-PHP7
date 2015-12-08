<?php
/**
* @file statapi.inc.php
* @Brief statistical apis transfer for admin center
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require_once dirname(__FILE__) . '/libs/env.inc.php';

$ak = $sk = $appInfo = null;
$aksk = BigappEnv::getAkSk();
if ($aksk!==false && isset($aksk['ak']) &&  isset($aksk["sk"])) {
    $ak = $aksk["ak"];
    $sk = $aksk["sk"];
    $appInfo = BigappEnv::getAppInfoFromBigstation();
}

$paramRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100802, 'error_msg' => 'invalid param',));
$authRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100803, 'error_msg' => 'auth failed',));
$svrRet = BIGAPPJSON::encode(array('request_id' => rand(1000000, 10000000000),'error_code' => 100800, 'error_msg' => 'internal server error',));
if(!isset($_G['groupid']) || 1 != $_G['groupid']){
	echo $authRet;
	die(0);
}
if(!isset($_GET['method']) || !isset(BigAppConf::$statApis[$_GET['method']])){
	echo $paramRet;
	die(0);
}
$url = BigAppConf::$statApis[$_GET['method']];
if('days_trend' == $_GET['method'] && (!isset($_GET['from_day']) || !isset($_GET['end_day']) || !isset($_GET['item']))){
	echo $paramRet;
	die(0);
}
$params = array();
if('days_trend' == $_GET['method']){
	$params = array(
		'from_day' => $_GET['from_day'],
		'end_day' => $_GET['end_day'],
		'item' => $_GET['item'],
	);
}
$obj = new BkSvr($ak, $sk, 30);
$ret = $obj->getInfo($url, $params, false);
if(false === $ret){
	echo $svrRet;
	die(0);
}
echo BIGAPPJSON::encode($ret);
die(0);
?>
