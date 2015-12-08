<?php

/**
* @file indexcfg.php
* @Brief 
* @author tangyy
* @version 1
* @date 2015-09-22
*/
//
if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}
require './source/class/class_core.php';

define("FILE_PATH", dirname(dirname(dirname(__FILE__))));
require_once FILE_PATH . '/libs/appdesign.inc.php';

$discuz = C::app();
$discuz->init();

//取5个有效的buttons的信息
$max_button_num = 5;
$button_configs = array();

for($i = 1; $i<=$max_button_num; $i++) {
	$key = "bigapp_button_id_" . $i . "_setting";
	
	global $_G;
	
	require_once libfile('function/cache');
	updatecache('setting');
	if(isset($_G['setting'][$key]) && !empty($_G['setting'][$key])){
		$succRet = unserialize($_G['setting'][$key]);
	} else {
		$succRet = C::t('common_setting')->fetch($key, true);
	}
	
	
	if(isset($succRet[0]) && empty($succRet[0])) {
		$succRet = AppDesign::getDefaultButtonSetting($i);
		$succRet= AppDesign::procFrontData($succRet);####################
	}
	
	$button_config = array(
		"id" => $succRet['id'],
		"button_type" => $succRet['button_type'],
		"button_name" => $succRet['name'],
		"icon_type" => $succRet['icon_type'],
		//"tab_cfg" => AppDesign::getTabCfgInfo($succRet),
	);

   $ret = AppDesign::getTabCfgInfo($succRet); #########################
   
	if(!empty($ret)) {
		$button_config['tab_cfg'] = AppDesign::getTabCfgInfo($succRet);
	}
	
	array_push($button_configs, $button_config);
}

$variable = array (
	"button_configs" => $button_configs,
);

bigapp_core::result(bigapp_core::variable($variable));
?>
