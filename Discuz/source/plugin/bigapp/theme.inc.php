<?php
/**
* @file theme.inc.php
* @Brief theme configuration for admin center
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

require_once dirname(__FILE__) . '/libs/menu.inc.php';
require_once dirname(__FILE__) . '/libs/env.inc.php';

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

if(submitcheck('theme_sumbit')){
	if(!empty($sk) && !empty($ak)){
		$obj = new BkSvr($ak, $sk, 30);
		$params = array('method' => 'set','key'=>'theme'); 
		$params['value'] = $_REQUEST['theme']; 
		$appInfo = $obj->getInfo(BigAppConf::$accountSetUrl, $params);
		runlog('bigapp', "post theme info [ params: ".json_encode($params)." ]");
	}
	cpmsg('plugins_edit_succeed', 'action=plugins&operation=config&do=' . $pluginid . '&identifier=bigapp&pmod=theme', 'succeed');
	die(0);
}

if(!empty($sk) && !empty($ak)){
	$obj = new BkSvr($ak, $sk, 30);
	$params = array('method' => 'get','key'=>'theme'); 
	$accountInfo = $obj->getInfo(BigAppConf::$accountGetUrl, $params);
	$theme = isset($accountInfo['theme'])?$accountInfo['theme']:$account;
	runlog('bigapp', "get theme info [ theme: ".($theme)." ]");
}

if(empty($theme)){
	$theme = 1;
}
showformheader('plugins&operation=config&do=' . $pluginid . '&identifier=bigapp&pmod=theme', '', 'theme');
showtableheader($lang['theme']);
$themevarname = array('theme',
				array(
					array(1, $lang['option_tab']),
					array(2, $lang['option_slide']),
				)
			  );
showsetting($lang['theme'], $themevarname, $theme, 'mradio', 0, 0, '');

showsubmit('theme_sumbit', lang('plugin/bigapp', 'submit'));
showtablefooter();
showformfooter();
?>
