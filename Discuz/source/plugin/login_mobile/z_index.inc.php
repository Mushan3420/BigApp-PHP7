<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

require_once dirname(__FILE__).'/libs/env.php';
require_once dirname(__FILE__).'/libs/menu.inc.php';
$params = array();
$tplVars = array(
    "plugin_path"=>DzEnv::getPluginPath(),
    "login_url" => DzEnv::getPluginPath()."/fe/login.html",
);
MobileLogin_Utils::loadTemplate(dirname(__FILE__).'/view/z_index.tpl', $params, $tplVars);

// vim600: sw=4 ts=4 fdm=marker syn=php
?>
