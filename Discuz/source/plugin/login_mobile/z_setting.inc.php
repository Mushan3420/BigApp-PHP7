<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once dirname(__FILE__).'/libs/env.php';


/* 保存设置 */
if (isset($_REQUEST["enable"])) {
    $params = array (
        "enable" => DzEnv::get_param("enable", "1"),
        "enable_mobile" => DzEnv::get_param("enable_mobile", "1"),
    );
    C::t('common_setting')->update_batch(array("login_mobile_setting"=>$params));
    updatecache('setting');
    $landurl = 'action=plugins&operation=config&do='.$pluginid.'&identifier=login_mobile&pmod=z_setting';
	cpmsg('plugins_edit_succeed', $landurl, 'succeed');
}


/////////////////////////////////////////////////////////////
// show page
/////////////////////////////////////////////////////////////
require_once dirname(__FILE__).'/libs/menu.inc.php';

$params = array(
    "enable" => 1,
    "enable_mobile" => 1,
);

if (isset($_G['setting']['login_mobile_setting'])){
	$setting = unserialize($_G['setting']['login_mobile_setting']);
    $params["enable"] = $setting["enable"];
    if(isset($setting["enable_mobile"])) $params["enable_mobile"] = $setting["enable_mobile"];
}

$tplVars = array(
    "plugin_path"=>DzEnv::getPluginPath(),
);
MobileLogin_Utils::loadTemplate(dirname(__FILE__).'/view/z_setting.tpl', $params, $tplVars);

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
