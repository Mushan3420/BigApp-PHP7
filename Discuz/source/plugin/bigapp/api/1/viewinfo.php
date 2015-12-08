<?php
/**
* @file viewinfo.php
* @Brief 
* @author tangyy
* @version 1
* @date 2015-10-29
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}
require './source/class/class_core.php';

define("FILE_PATH", dirname(dirname(dirname(__FILE__))));
require_once FILE_PATH . '/libs/appdesign.inc.php';
require_once libfile('function/cache');

$discuz = C::app();
$discuz->init();

$view_id = isset($_GET['vid'])? $_GET['vid'] : '0'; 
$key = 'bigapp_view_' . $view_id;

updatecache('setting');
if(isset($_G['setting'][$key]) && !empty($_G['setting'][$key])){
	$res = unserialize($_G['setting'][$key]);
} else {
	$res = C::t('common_setting')->fetch($key, true);
}

if(isset($res[0]) && empty($res[0])) {
	//拉取不到对应的视图信息
   $variable['code'] = '1';
	$variable['msg'] = 'fail';
} else {
	$tab_cfg = AppDesign::getViewTabCfgInfo($res);
	
	$variable["tab_cfg"] = $tab_cfg;
	$variable['code'] = '0';
	$variable['msg'] = 'succ';
}

bigapp_core::result(bigapp_core::variable($variable));	

?>

