<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once dirname(__FILE__).'/libs/env.php';


if (isset($_REQUEST["ajax"])) {
    $result = C::t("#login_mobile#mobile_login_sms")->query();
    DzEnv::result($result);
}


/////////////////////////////////////////////////////////////
// show page
/////////////////////////////////////////////////////////////
require_once dirname(__FILE__).'/libs/menu.inc.php';

$params = array(
    "ajaxapi" => DzEnv::getSiteUrl()."/plugin.php?id=login_mobile:z_smslist&ajax=1",
);

/*
if (isset($_G['setting']['login_mobile_smsset'])){
	$appcfg = unserialize($_G['setting']['login_mobile_smsset']);
    isset($appcfg["username"]) && $params["username"]=$appcfg["username"];
    isset($appcfg["password"]) && $params["password"]=$appcfg["password"];
}*/

$tplVars = array(
    "plugin_path"=>DzEnv::getPluginPath(),
);
MobileLogin_Utils::loadTemplate(dirname(__FILE__).'/view/z_smslist.tpl', $params, $tplVars);

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
