<?php
/**
* @file realtime.inc.php
* @Brief statistical page for admin center
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once dirname(__FILE__) . '/libs/env.inc.php';
require_once dirname(__FILE__) . '/libs/menu.inc.php';

/*
//add for plugin upgrading
if(isset($appInfo['remind']) && (1 == $appInfo['remind'] || 2 == $appInfo['remind'])){ 
	if(function_exists('iconv')){
		$msg = iconv('UTF-8', CHARSET . '//ignore', $appInfo['remind_message']);	
	}else{
		$msg = mb_convert_encoding($appInfo['remind_message'], CHARSET, 'UTF-8');
	}
	showtips($msg, '', true, lang('plugin/bigapp', 'plugin_interface_upgrade'));
	if(2 == $appInfo['remind']){
		die(0);
	}
}
*/
$keys = array_keys(BigAppConf::$statApis);
$url = rtrim($_G['siteurl'], '/') . '/plugin.php?id=bigapp:statapi';
$params = array();
foreach ($keys as $k){
	$params[$k] = $url . '&method=' . urlencode($k);
}
showtableheader(lang('plugin/bigapp', 'menu_stat_realtime'));
showtablefooter();
$tplVars = array("plugin_path"=>rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp');
Utils::loadTemplate(dirname(__FILE__) . '/view/realtime.tpl', $params ,$tplVars);
runlog('bigapp', 'show real time page succ');
?>
