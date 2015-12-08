<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

define("FILE_PATH", dirname(__FILE__));

require_once FILE_PATH . '/libs/env.inc.php';
require_once FILE_PATH . '/libs/menu.inc.php';


$keys = array_keys(BigAppConf::$releaseApis);
$url = rtrim($_G['siteurl'], '/') . '/plugin.php?id=bigapp:releaseapi';
$params = array();
foreach ($keys as $k){
	$params[$k] = $url . '&method=' . urlencode($k);
}
///////////////////////////////////////////////////////////////////
$latest_pkgurl = BigAppConf::$releaseApis["latest_package"];
$con = strpos($latest_pkgurl, "?")===false ? "?" : "&";
$latest_pkgurl.= $con."app_key=$ak&os=1";
$params["latest_pkgurl"] = $latest_pkgurl;
///////////////////////////////////////////////////////////////////

$tplVars = array("plugin_path"=>rtrim($_G['siteurl'], '/') . '/source/plugin/bigapp');
Utils::loadTemplate(FILE_PATH.'/view/release.tpl', $params, $tplVars);

runlog('bigapp', 'show release page succ');
// vim600: sw=4 ts=4 fdm=marker syn=php
?>
