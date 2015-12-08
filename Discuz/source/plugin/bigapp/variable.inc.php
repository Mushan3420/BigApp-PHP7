<?php
/**
* @file variable.inc.php
* @Brief variable setting page for admin center
* @author youzu
* @version 1.0.0
* @date 2015-07-07
*/

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
require_once dirname(__FILE__) . '/libs/menu.inc.php';
require_once dirname(__FILE__) . '/bigappjson.class.php';

if(submitcheck('variable')){  //必须和showsubmit参数相同
	$setting = array(
        'threadlist_image_mode' => $_GET['setting']['threadlist_image_mode'],
        'display_style' => $_GET['setting']['display_style'],
        'enable_pic_opt' => $_GET['setting']['enable_pic_opt']
    );
	$settings = array('bigapp_settings' => $setting);
	C::t('common_setting')->update_batch($settings);
	runlog('bigapp', 'save settings variable succ [ settings: ' . BIGAPPJSON::encode($settings) . ' ]');
	cpmsg('plugins_edit_succeed', 'action=plugins&operation=config&do=' . $pluginid . '&identifier=bigapp&pmod=variable', 'succeed');
	die(0);
}
updatecache('setting');
if(isset($_G['setting']['bigapp_settings'])){
	$_G['setting']['bigapp_settings'] = unserialize($_G['setting']['bigapp_settings']);
}
if(!isset($_G['setting']['bigapp_settings']['threadlist_image_mode'])){
    $_G['setting']['bigapp_settings']['threadlist_image_mode'] = 1;
}
if(!isset($_G['setting']['bigapp_settings']['display_style'])){
    $_G['setting']['bigapp_settings']['display_style'] = 0;
}
if(!isset($_G['setting']['bigapp_settings']['enable_pic_opt'])){
    $_G['setting']['bigapp_settings']['enable_pic_opt'] = 1;
}

showformheader('plugins&operation=config&do=' . $pluginid . '&identifier=bigapp&pmod=variable', '', 'variable');
showtableheader(lang('plugin/bigapp', 'style_config'));
showsetting(lang('plugin/bigapp', 'threadlist_image_mode'), 'setting[threadlist_image_mode]', 
		$_G['setting']['bigapp_settings']['threadlist_image_mode'], 'radio', 0, 0, lang('plugin/bigapp', 'threadlist_image_mode_comment'));
$displaystyle = array('setting[display_style]',
                array(
                    array(0, lang('plugin/bigapp', 'default')),
                    array(1, lang('plugin/bigapp', 'style1')),
                    array(2, lang('plugin/bigapp', 'style2')),
				)
              );
showsetting(lang('plugin/bigapp', 'display_style'), $displaystyle, $_G['setting']['bigapp_settings']['display_style'], 'mradio', 0, 0, lang('plugin/bigapp', 'display_style_comment'));
showsetting(lang('plugin/bigapp', 'enable_pic_opt'), 'setting[enable_pic_opt]', 
		$_G['setting']['bigapp_settings']['enable_pic_opt'], 'radio', 0, 0, lang('plugin/bigapp', 'enable_pic_opt_comment'));
showsubmit('variable', lang('plugin/bigapp', 'submit'));
showtablefooter();
showformfooter();
runlog('bigapp', 'show variable setting page succ');
?>
