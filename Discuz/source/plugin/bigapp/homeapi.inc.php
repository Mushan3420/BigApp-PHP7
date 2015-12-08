<?php
/**
* @file homeapi.inc.php
* @Brief home configuration for admin center
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require_once dirname(__FILE__) . '/conf/conf.inc.php';
require_once dirname(__FILE__) . '/libs/utils.inc.php';
require_once dirname(__FILE__) . '/libs/bksvr.inc.php';
require_once dirname(__FILE__) . '/bigappjson.class.php';
require_once libfile('function_cache', 'function');
$paramRet =  array('request_id' => rand(1000000, 10000000000),'error_code' => 100802, 'error_msg' => 'invalid param',);
$authRet  =  array('request_id' => rand(1000000, 10000000000),'error_code' => 100803, 'error_msg' => 'auth failed',);
$svrRet   =  array('request_id' => rand(1000000, 10000000000),'error_code' => 100800, 'error_msg' => 'internal server error',);
$succRet  =  array('request_id' => rand(1000000, 10000000000),'error_code' => 0, 'error_msg' => 'succ');
$method = $_REQUEST["method"];
runlog('bigapp', "post homeapi info [ params:".json_encode($_REQUEST)."]");
$ret = $paramRet;
foreach (BigAppConf::$defaultHome as $key => &$value){
	foreach ($value as &$v){
		foreach (array('title', 'desc') as $k){
			if(isset($v[$k])){
				if(function_exists('iconv')){
					$v[$k] = iconv('UTF-8', CHARSET . '//ignore', $v[$k]);
				}else{
					$v[$k] = mb_convert_encoding($v[$k], CHARSET, 'UTF-8');
				}
			}
		}
	}
}
switch($method){
	case "getBanner":
		$succRet['data'] = C::t('common_setting')->fetch("bigapp_home_banner",true);
		if(isset($succRet['data'][0]) && empty($succRet['data'][0])){
			if(false === $succRet['data'][0]){
				$succRet['data'] = BigAppConf::$defaultHome['banner'];
			}else{
				$succRet['data'] = array();
			}
		}
		$ret = $succRet;
	break;
	
	case "setBanner":
		if(!empty($_REQUEST["settings"])){
			$_REQUEST['settings'] = urldecode($_REQUEST['settings']);
			runlog('bigapp', "post homeapi info [ settings:".$_REQUEST['settings']."]");
			if ($_REQUEST['settings'] == base64_encode(base64_decode($_REQUEST['settings']))){
				$banners = json_decode(base64_decode($_REQUEST['settings']),true);
				runlog('bigapp', "post homeapi info:request is base64_encode,banner:".json_encode($banners));
			}else{
				$banners = json_decode($_REQUEST['settings'],true);
			}
			$banners = Utils::filterPid($banners);
			$settings = array('bigapp_home_banner' => $banners);
			C::t('common_setting')->update_batch($settings);
			$succRet['data'] = $banners;
			$ret = $succRet;
			updatecache('setting');
		}
	break;
	
	case "getFunc":
		$fun = intval($_REQUEST['fun']);
		if($fun != 1 && $fun != 2){
			$fun = 1;
		}
		$succRet['data'] = C::t('common_setting')->fetch("bigapp_home_func".$fun,true);
		if(isset($succRet['data'][0]) && empty($succRet['data'][0])){
			if(false === $succRet['data'][0]){
				$succRet['data'] = BigAppConf::$defaultHome['func'.$fun];
			}else{
				$succRet['data'] = array();
			}
		}
		$ret = $succRet;
	break;
	
	case "setFunc":
		if(!empty($_REQUEST["settings"])){
			$fun = intval($_REQUEST['fun']);
			$_REQUEST['settings'] = urldecode($_REQUEST['settings']);
			if ($_REQUEST['settings'] == base64_encode(base64_decode($_REQUEST['settings']))){
				$funcs = json_decode(base64_decode($_REQUEST['settings']),true);
				runlog('bigapp', "post homeapi info:request is base64_encode,funcs:".json_encode($funcs));
			}else{
				$funcs = json_decode($_REQUEST['settings'],true);
			}
			$funcs  = Utils::filterPid($funcs);
			$settings = array('bigapp_home_func'.$fun => $funcs);
			C::t('common_setting')->update_batch($settings);
			$succRet['data'] = $funcs;
			$ret = $succRet;
			updatecache('setting');
		}
	break;
	
	case "getSwitch":
		$fun = intval($_REQUEST['fun']);
		$succRet['data'] = C::t('common_setting')->fetch("bigapp_home_switch".$fun,true);
		if(isset($succRet['data'][0]) && empty($succRet['data'][0])){
			if(false === $succRet['data'][0]){
				$succRet['data'] = BigAppConf::$defaultHome['switch'.$fun];
			}else{
				$succRet['data'] = array("switch"=>"1");
			}
		}
		$ret = $succRet;
	break;
	
	case "setSwitch":
		$fun = intval($_REQUEST['fun']);
		$_REQUEST['switch'] = intval($_REQUEST['switch']);
		$switch = array('switch'=>$_REQUEST['switch']);
		$settings = array('bigapp_home_switch'.$fun => $switch);
		C::t('common_setting')->update_batch($settings);
		$ret = $succRet;
		updatecache('setting');
	break;
	
case "setThreadSetting":
	    $params = array (
			"enable_new"   => $_POST["enable_new"],
			"sort_new"  => $_POST["sort_new"],
			"title_new"  => $_POST["title_new"],
			"enable_hot" => $_POST["enable_hot"],
			"sort_hot" => $_POST["sort_hot"],
			"title_hot"  => $_POST["title_hot"],
			"enable_fav"   => $_POST["enable_fav"],
			"sort_fav"  => $_POST["sort_fav"],
			"title_fav"  => $_POST["title_fav"],
			"portal" => $_POST["portal"],
		);
		
		$params = json_encode($params);
		runlog('bigapp', "post homeapi info:request is setThreadSetting portal:".json_encode($_POST));
		$settings = array('bigapp_home_threadsetting' => $params);
		C::t('common_setting')->update_batch($settings);
		$succRet['data'] = json_decode($params, true);
		$ret = $succRet;
		updatecache('setting');
	break;
	
	case "getThreadSetting":
		$setting = C::t('common_setting')->fetch("bigapp_home_threadsetting", false);
		
		$setting = json_decode($setting, true);
		if(is_array($setting)){
			$succRet['data'] = $setting;
		} else {
			$defaultsetting = array(
								"enable_new" => "true",
								"sort_new" => '1',
								"title_new" => '最新',
								"enable_hot" => "true",
								"sort_hot" => '2',
								"title_hot" => '热门',
								"enable_fav" => "true",
								"sort_fav" => '3',
								"title_fav" => '精华',
			);
			
			$succRet['data'] = $defaultsetting;
		}
		
		$ret = $succRet;
	break;
	
	case "setPortalSetting":
	    $params = array (
			"portal" => $_POST["portal"],
		);
		$params = json_encode($params);
		runlog('bigapp', "post homeapi info:request is setPortalSetting portal:".json_encode($_POST));
		$settings = array('bigapp_home_portalsetting' => $params);
		C::t('common_setting')->update_batch($settings);
		$succRet['data'] = json_decode($params, true);
		$ret = $succRet;
		updatecache('setting');
	break;
	
	default:
		$ret = $paramRet;
}
if(isset($_GET['callback']) && $_GET['callback'] == 'jsonp'){
	$data = BIGAPPJSON::encode($ret);
	echo "jsonp($data)";
}else{
	echo BIGAPPJSON::encode($ret);
}
die(0);
?>
