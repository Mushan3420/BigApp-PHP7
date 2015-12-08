<?php
/**
* @file account.inc.php
* @Brief account setting for admin center
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
require_once dirname(__FILE__) . '/libs/menu.inc.php';
require_once dirname(__FILE__) . '/libs/verify.inc.php';
require_once dirname(__FILE__) . '/libs/appcfg.inc.php';



if(submitcheck('account')){
    ///////////////////////////////////////////
    // 暂时保留后台接口
	if(!empty($sk) && !empty($ak)){
		$obj = new BkSvr($ak, $sk, 30);
		$params = array('method' => 'set'); 
		$account['login_mod'] = $_REQUEST['account_login_mod']; 
		$account['login_url'] = $_REQUEST['account_login_url']; 
		$account['reg_mod'] = $_REQUEST['account_reg_mod']; 
		$account['reg_url'] = $_REQUEST['account_reg_url']; 
		$account['reg_switch'] = $_REQUEST['account_reg_switch']; 
		$account['allow_avatar_change'] = $_REQUEST['allow_avatar_change']; 
		$params['value'] = json_encode($account);
		$appInfo = $obj->getInfo(BigAppConf::$accountSetUrl, $params);
		runlog('bigapp', "post account info [ params: ".json_encode($params)." ]");
	}
    ///////////////////////////////////////////
    saveLoginConfigure($account);
	cpmsg('plugins_edit_succeed', 'action=plugins&operation=config&do=' . $pluginid . '&identifier=bigapp&pmod=account', 'succeed');
	die(0);
}

$account = getLoginConfigure();
/*
$account = array();
$account['login_mod'] = $account['reg_mod'] = 0; 
$account['login_url'] = $account['reg_url'] = ""; 
$account['reg_switch'] = 1;
if(!empty($sk) && !empty($ak)){
	$obj = new BkSvr($ak, $sk, 30);
	$params = array('method' => 'get'); 
	$accountInfo = $obj->getInfo(BigAppConf::$accountGetUrl, $params);
	$account = (isset($accountInfo['account']) && !empty($accountInfo['account']) ) ? $accountInfo['account']:$account;
	runlog('bigapp', "post account info [ account: ".json_encode($account)." ]");
}
if($account['login_mod'] == 0){
	$account['login_url'] = "";
}
if($account['reg_mod'] == 0){
	$account['reg_url'] = "";
}
$account['reg_switch'] = isset($account['reg_switch'])?$account['reg_switch']:1;
$account['allow_avatar_change'] = isset($account['allow_avatar_change'])?$account['allow_avatar_change']:1;
*/
showformheader('plugins&operation=config&do=' . $pluginid . '&identifier=bigapp&pmod=account', '', 'account');
showtableheader(lang('plugin/bigapp', 'menu_account'));
$loginvarname = array('account_login_mod',
				array(
					array(0, lang('plugin/bigapp', 'account_login_native')),
					array(1, lang('plugin/bigapp', 'account_login_webview')),
				)
			  );
showsetting(lang('plugin/bigapp', 'account_login_mod'), $loginvarname, $account['login_mod'], 'mradio', 0, 0, lang('plugin/bigapp', 'account_login_url_comment'));

showsetting(lang('plugin/bigapp', 'account_login_url'), 'account_login_url', $account['login_url'], 'text', 0, 0, lang('plugin/bigapp', 'account_login_url_comment'), '', '', true);


$switchvarname = array('account_reg_switch',
				array(
					array(0, lang('plugin/bigapp', 'account_reg_switch_off')),
					array(1, lang('plugin/bigapp', 'account_reg_switch_on')),
				)
			  );
showsetting(lang('plugin/bigapp', 'account_reg_switch'), $switchvarname, $account['reg_switch'], 'mradio', 0, 0, lang('plugin/bigapp', 'account_reg_switch_comment'));

$regvarname = array('account_reg_mod',
				array(
					array(0, lang('plugin/bigapp', 'account_reg_native')),
					array(1, lang('plugin/bigapp', 'account_reg_webview')),
				)
			  );
showsetting(lang('plugin/bigapp', 'account_reg_mod'), $regvarname, $account['reg_mod'], 'mradio', 0, 0, lang('plugin/bigapp', 'account_reg_url_comment'));
showsetting(lang('plugin/bigapp', 'account_reg_url'), 'account_reg_url', $account['reg_url'], 'text', 0, 0, lang('plugin/bigapp', 'account_reg_url_comment'), '', '', true);
$avatarChange = array('allow_avatar_change',
				array(
					array(0, lang('plugin/bigapp', 'no')),
					array(1, lang('plugin/bigapp', 'yes')),
				)
			  );
showsetting(lang('plugin/bigapp', 'allow_avatar_change'), $avatarChange, $account['allow_avatar_change'], 'mradio', 0, 0, lang('plugin/bigapp', 'allow_avatar_change_comment'));
showsubmit('account');
showtablefooter();
showformfooter();
?>
