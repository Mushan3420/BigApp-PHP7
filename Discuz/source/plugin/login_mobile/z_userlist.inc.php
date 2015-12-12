<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once dirname(__FILE__).'/libs/env.php';

if (isset($_REQUEST["ajax"])) 
{
    $groupid = $_G["groupid"];
    if ($groupid!=1) {
        DzEnv::result(array("retcode"=>-1,"retmsg"=>"很抱歉，你没有权限进行此操作"));
    }

    switch($_GET["action"]) {
        case "query": 
            $res = C::t("#login_mobile#mobile_login_connection")->query(); 
            DzEnv::result($res);
            break;
        case "unbind": 
            $res = C::t("#login_mobile#mobile_login_connection")->unbind(); 
            DzEnv::result($res);
            break;
        case "bind": 
            $res = C::t("#login_mobile#mobile_login_connection")->bind(); 
            DzEnv::result($res);
            break;
        default: DzEnv::result(array("retcode"=>-1,"retmsg"=>"unknown action")); break;
    }
    die(0);
}

/*
if (isset($_REQUEST["username"])) {
    $params = array (
        "username" => DzEnv::get_param("username", ""),
        "password" => DzEnv::get_param("password", ""),
    );
    C::t('common_setting')->update_batch(array("login_mobile_smsset"=>$params));
    updatecache('setting');
    $landurl = 'action=plugins&operation=config&do='.$pluginid.'&identifier=login_mobile&pmod=z_smsset';
	cpmsg('plugins_edit_succeed', $landurl, 'succeed');
}
*/
/////////////////////////////////////////////////////////////
// show page
/////////////////////////////////////////////////////////////
require_once dirname(__FILE__).'/libs/menu.inc.php';

$params = array(
    "ajaxapi" => DzEnv::getSiteUrl()."/plugin.php?id=login_mobile:z_userlist&ajax=1&",
    "username"  => "",
    "password" => "",
    "list" => DzEnv::getinfo(),
);

if (isset($_G['setting']['login_mobile_smsset'])){
	$appcfg = unserialize($_G['setting']['login_mobile_smsset']);
    isset($appcfg["username"]) && $params["username"]=$appcfg["username"];
    isset($appcfg["password"]) && $params["password"]=$appcfg["password"];
}

$tplVars = array(
    "plugin_path"=>DzEnv::getPluginPath(),
);
MobileLogin_Utils::loadTemplate(dirname(__FILE__).'/view/z_userlist.tpl', $params, $tplVars);

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
